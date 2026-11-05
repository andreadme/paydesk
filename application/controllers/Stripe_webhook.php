<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Webhook;

class Stripe_webhook extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->load('stripe');

        $this->load->model('webhook_event_model');
        $this->load->model('payment_model');
        $this->load->model('receipt_model');
    }

    public function index()
    {
        $request_body = $this->input->raw_input_stream;
        $request_header = $this->input->get_request_header('Stripe-Signature');

        try {
            $webhook_event = Webhook::constructEvent($request_body, $request_header, $this->config->item('webhook_secret'));
            $event_object = $webhook_event->data->object;
            $metadata = $event_object->metadata;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            log_message('error', 'Signature Verification error: ' . $e->getMessage());
            $this->output->set_status_header(400);
            return;
        } catch (\UnexpectedValueException $e) {
            log_message('error', 'Unexpected value error: ' . $e->getMessage());
            $this->output->set_status_header(400);
            return;
        }

        $now = gmdate('Y-m-d H:i:s');
        $event_existing = $this->webhook_event_model->get_event_by_stripe_id($webhook_event->id);
        if ($event_existing) {
            if (in_array($event_existing->processing_status, ['processed', 'ignored'])) {
                return $this->output->set_status_header(200);
            }
        } elseif ($event_existing === null) {
            $created_event = $this->webhook_event_model->create_event([
                'stripe_event_id' => $webhook_event->id,
                'event_type' => $webhook_event->type,
            ]);
            if ($created_event === false) {
                $event_existing = $this->webhook_event_model->get_event_by_stripe_id($webhook_event->id);
                if ($event_existing === false || $event_existing === null) {
                    log_message('error', 'Event creation error: ' . $webhook_event->id);
                    return $this->output->set_status_header(500);
                }
            }
        } else {
            log_message('error', 'Event fetch error: ' . $webhook_event->id);
            return $this->output->set_status_header(500);
        }

        $stored_event = $this->webhook_event_model->get_event_by_stripe_id($webhook_event->id);

        if ($stored_event === false || $stored_event === null) {
            log_message('error', 'Stored event not found error: ' . $webhook_event->id);
            return $this->output->set_status_header(500);
        }

        if (in_array($stored_event->processing_status, ['processed', 'ignored'])) {
            return $this->output->set_status_header(200);
        }

        $this->db->trans_begin();
        $has_error = false;
        $validation_failed = false;
        $event_claimed = $this->webhook_event_model->claim_event($stored_event->id);

        if ($event_claimed === false) {
            log_message('error', 'Claim event error: ' . $stored_event->id);
            $this->db->trans_rollback();
            return $this->output->set_status_header(500);
        } else {
            if ($event_claimed === 1) {
                if ($webhook_event->type === 'checkout.session.completed') {
                    if (!$metadata || empty($metadata->order_id) || empty($metadata->payment_id)) {
                        log_message('error', 'Metadata missing: ' . $webhook_event->id);
                        $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                            'processing_status' => 'failed',
                            'processed_at' => $now,
                        ]);

                        if ($updated_event === false) {
                            $has_error = true;
                            log_message('error', 'Event update error: ' . $stored_event->id);
                        }
                        $validation_failed = true;
                    }

                    if ($has_error === false && $validation_failed === false) {
                        $payment = $this->payment_model->get_payment($metadata->payment_id);
                        if ($payment === false) {
                            $has_error = true;
                            log_message('error', 'Payment get error: ' . $metadata->payment_id);
                        } elseif ($payment === null) {
                            $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                'processing_status' => 'failed',
                                'processed_at' => $now,
                            ]);

                            if ($updated_event === false) {
                                $has_error = true;
                                log_message('error', 'Event update error: ' . $stored_event->id);
                            }
                        } else {
                            if ($payment->stripe_checkout_session_id === $event_object->id && (int) $payment->order_id === (int) $metadata->order_id && (int) $payment->id === (int) $metadata->payment_id && in_array($payment->status, ['pending', 'succeeded']) && $event_object->payment_status === 'paid' && $this->convert_to_cents($payment->total) === $event_object->amount_total && strtolower($payment->currency) === strtolower($event_object->currency)) {
                                if (!empty($event_object->payment_intent)) {
                                    if ($payment->status === 'succeeded' && !empty($payment->stripe_payment_intent_id) && $payment->stripe_payment_intent_id === $event_object->payment_intent) {
                                    } elseif ($payment->status === 'pending') {
                                        if ($payment->stripe_payment_intent_id === null || ($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id === $event_object->payment_intent)) {
                                            $payment_updated = $this->payment_model->update_payment($payment->id, [
                                                'status' => 'succeeded',
                                                'stripe_payment_intent_id' => $event_object->payment_intent,
                                                'paid_at' => $now,
                                            ]);
                                            if ($payment_updated === false) {
                                                $has_error = true;
                                                log_message('error', 'Payment update error: ' . $payment->id);
                                            }
                                            if ($has_error === false && $validation_failed === false) {
                                                $payment_log_created = $this->payment_model->create_payment_log([
                                                    'payment_id' => $payment->id,
                                                    'status' => 'succeeded',
                                                    'description' => 'Stripe payment successfully completed',
                                                ]);
                                                if ($payment_log_created === false) {
                                                    $has_error = true;
                                                    log_message('error', 'Create payment log error: ' . $payment->id);
                                                }
                                            }
                                        } else {
                                            $validation_failed = true;
                                            log_message('error', 'Payment Intent ID mismatch: ' . $payment->id);
                                            $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                                'processing_status' => 'failed',
                                                'processed_at' => $now,
                                            ]);

                                            if ($updated_event === false) {
                                                $has_error = true;
                                                log_message('error', 'Event update error: ' . $stored_event->id);
                                            }
                                        }
                                    } else {
                                        $has_error = true;
                                        log_message('error', 'Payment intent check error: ' . $payment->id);
                                    }

                                    if ($has_error === false && $validation_failed === false) {
                                        $receipt_existing = $this->receipt_model->get_receipt_by_payment_id($payment->id);

                                        if ($receipt_existing === false) {
                                            $has_error = true;
                                            log_message('error', 'Receipt lookup query error: ' . $payment->id);
                                            $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                                'processing_status' => 'failed',
                                                'processed_at' => $now,
                                            ]);

                                            if ($updated_event === false) {
                                                $has_error = true;
                                                log_message('error', 'Event update error: ' . $stored_event->id);
                                            }
                                        } elseif ($receipt_existing === null) {
                                            $receipt_created = $this->receipt_model->create_receipt([
                                                'payment_id' => $payment->id,
                                                'issued_at' => $now,
                                            ]);

                                            if ($receipt_created === false) {
                                                $has_error = true;
                                                log_message('error', 'Receipt creation error: ' . $payment->id);
                                            }

                                            $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                                'processing_status' => 'processed',
                                                'processed_at' => $now,
                                            ]);

                                            if ($updated_event === false) {
                                                $has_error = true;
                                                log_message('error', 'Event update error: ' . $stored_event->id);
                                            }
                                        }
                                    }
                                } else {
                                    $has_error = true;
                                    log_message('error', 'Payment intent empty error: ' . $payment->id);
                                    $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                        'processing_status' => 'failed',
                                        'processed_at' => $now,
                                    ]);

                                    if ($updated_event === false) {
                                        $has_error = true;
                                        log_message('error', 'Event update error: ' . $stored_event->id);
                                    }
                                }
                            } else {
                                log_message('error', 'Payment and session data check error: ' . $webhook_event->id);
                                $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                    'processing_status' => 'failed',
                                    'processed_at' => $now,
                                ]);

                                if ($updated_event === false) {
                                    $has_error = true;
                                    log_message('error', 'Event update error: ' . $stored_event->id);
                                }
                            }
                        }
                    }
                } elseif ($webhook_event->type === 'payment_intent.payment_failed') {
                    if (!$metadata || empty($metadata->order_id) || empty($metadata->payment_id)) {
                        log_message('error', 'Metadata missing: ' . $webhook_event->id);
                        $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                            'processing_status' => 'failed',
                            'processed_at' => $now,
                        ]);

                        if ($updated_event === false) {
                            $has_error = true;
                            log_message('error', 'Event update error: ' . $stored_event->id);
                        }
                        $validation_failed = true;
                    }

                    if ($has_error === false && $validation_failed === false) {
                        $payment = $this->payment_model->get_payment($metadata->payment_id);

                        if ($payment === false) {
                            $has_error = true;
                            log_message('error', 'Payment get error: ' . $metadata->payment_id);
                        } elseif ($payment === null) {
                            $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                                'processing_status' => 'failed',
                                'processed_at' => $now,
                            ]);

                            if ($updated_event === false) {
                                $has_error = true;
                                log_message('error', 'Event update error: ' . $stored_event->id);
                            }
                        } else {
                            if ($payment->status === 'pending' && (int) $payment->order_id === (int) $metadata->order_id && (int) $payment->id === (int) $metadata->payment_id && $this->convert_to_cents($payment->total) === $event_object->amount && strtolower($payment->currency) === strtolower($event_object->currency)) {
                                if (!empty($event_object->id) && (($payment->stripe_payment_intent_id && $payment->stripe_payment_intent_id === $event_object->id) || $payment->stripe_payment_intent_id === null)) {
                                    if ($payment->stripe_payment_intent_id === null) {
                                        $payment_updated = $this->payment_model->update_payment($payment->id, [
                                            'stripe_payment_intent_id' => $event_object->id,
                                        ]);

                                        if ($payment_updated === false) {
                                            $has_error = true;
                                            log_message('error', 'Payment update error: ' . $payment->id);
                                        }
                                    }

                                    if ($has_error === false && $validation_failed === false) {
                                        $error_message = isset($event_object->last_payment_error) ? (isset($event_object->last_payment_error->message) ? $event_object->last_payment_error->message : 'Last payment error message is empty.') : 'Last payment error not found.';
                                        $payment_log_created = $this->payment_model->create_payment_log([
                                            'payment_id' => $payment->id,
                                            'status' => 'failed',
                                            'description' => 'Payment failed: ' . $error_message,
                                        ]);

                                        if ($payment_log_created === false) {
                                            $has_error = true;
                                            log_message('error', 'Create payment log error: ' . $payment->id);
                                        }

                                        $event_updated = $this->webhook_event_model->update_event($stored_event->id, [
                                            'processing_status' => 'processed',
                                            'processed_at' => $now,
                                        ]);

                                        if ($event_updated === false) {
                                            $has_error = true;
                                            log_message('error', 'Webhook event update error: ' . $stored_event->id);
                                        }
                                    }
                                } else {
                                    $validation_failed = true;
                                    log_message('error', 'No payment match found.' . $payment->id);
                                    $event_updated = $this->webhook_event_model->update_event($stored_event->id, [
                                        'processing_status' => 'failed',
                                        'processed_at' => $now,
                                    ]);

                                    if ($event_updated === false) {
                                        $has_error = true;
                                        log_message('error', 'Webhook event update error: ' . $stored_event->id);
                                    }
                                }
                            } elseif ($payment->status === 'succeeded' && $event_object->id === $payment->stripe_payment_intent_id && $this->convert_to_cents($payment->total) === $event_object->amount && strtolower($payment->currency) === strtolower($event_object->currency)) {
                                $event_updated = $this->webhook_event_model->update_event($stored_event->id, [
                                    'processing_status' => 'processed',
                                    'processed_at' => $now,
                                ]);

                                if ($event_updated === false) {
                                    $has_error = true;
                                    log_message('error', 'Webhook event update error: ' . $stored_event->id);
                                }
                            } elseif ($payment->status === 'failed' && $event_object->id === $payment->stripe_payment_intent_id && $this->convert_to_cents($payment->total) === $event_object->amount && strtolower($payment->currency) === strtolower($event_object->currency)) {
                                $event_updated = $this->webhook_event_model->update_event($stored_event->id, [
                                    'processing_status' => 'processed',
                                    'processed_at' => $now,
                                ]);

                                if ($event_updated === false) {
                                    $has_error = true;
                                    log_message('error', 'Event update error on failed status payment. ' . $stored_event->id);
                                }
                                log_message('info', 'Payment already failed.' . $metadata->payment_id);
                            } else {
                                $has_error = true;
                                log_message('error', 'Payment status not found. ' . $metadata->payment_id);
                            }
                        }
                    }
                } else {
                    $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                        'processing_status' => 'ignored',
                        'processed_at' => $now,
                    ]);

                    if ($updated_event === false) {
                        $has_error = true;
                        log_message('error', 'Event update error: ' . $stored_event->id);
                    }
                }
            } elseif ($event_claimed === 0) {
                $this->db->trans_rollback();
                return $this->output->set_status_header(200);
            }

            if ($has_error === false && $this->db->trans_status() !== false) {
                if ($this->db->trans_commit()) {
                    return $this->output->set_status_header(200);
                } else {
                    log_message('error', 'Error on commit');
                    return $this->output->set_status_header(500);
                }
            }

            $this->db->trans_rollback();
            return $this->output->set_status_header(500);
        }
    }

    private function convert_to_cents($payment_total)
    {
        $parts = explode('.', $payment_total);

        $whole_number = $parts[0];
        $fractional = $parts[1];

        $result = $whole_number * 100 + $fractional;

        return (int) $result;
    }

    public function process_checkout_completed($event) {}
}
