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
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            log_message('error', 'Signature Verification error: ' . $e->getMessage());
            $this->output->set_status_header(400);
            return;
        } catch (\UnexpectedValueException $e) {
            log_message('error', 'Unexpected value error: ' . $e->getMessage());
            $this->output->set_status_header(400);
            return;
        }

        $event_existing = $this->webhook_event_model->get_event_by_stripe_id($webhook_event->id);
        if ($event_existing) {
            if (in_array($event_existing->processing_status, ['processed', 'ignored'])) {
                return $this->output->set_status_header(200);
            }
        } else {
            $created_event = $this->webhook_event_model->create_event($webhook_event->id, $webhook_event->type);
            if ($created_event === false) {
                $event_existing = $this->webhook_event_model->get_event_by_stripe_id($webhook_event->id);
                if ($event_existing === false) {
                    log_message('error', 'Event creation error: ' . $webhook_event->id);
                    return $this->output->set_status_header(500);
                }
            }
        }

        $stored_event = $this->webhook_event_model->get_event_by_stripe_id($webhook_event->id);

        if ($stored_event === false) {
            log_message('error', 'Stored event not found error: ' . $webhook_event->id);
            return $this->output->set_status_header(500);
        }

        $now = gmdate('Y-m-d H:i:s');
        if ($webhook_event->type === 'checkout.session.completed') {
            $event_object = $webhook_event->data->object;
            $metadata = $event_object->metadata;

            if (!$metadata || empty($metadata->order_id) || empty($metadata->payment_id)) {
                log_message('error', 'Metadata missing: ' . $webhook_event->id);
                $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                    'processing_status' => 'failed',
                    'processed_at' => $now,
                ]);

                if ($updated_event === false) {
                    log_message('error', 'Event update error: ' . $stored_event->id);
                    return $this->output->set_status_header(500);
                }
                return $this->output->set_status_header(400);
            }

            $payment = $this->payment_model->get_payment($metadata->payment_id);
            if ($payment && $payment->stripe_checkout_session_id === $event_object->id && (int) $payment->order_id === (int) $metadata->order_id && (int) $payment->id === (int) $metadata->payment_id && in_array($payment->status, ['pending', 'succeeded']) && $event_object->payment_status === 'paid' && $this->convert_to_cents($payment->total) === $event_object->amount_total && strtolower($payment->currency) === strtolower($event_object->currency)) {
                $this->db->trans_begin();
                $has_error = false;
                if (!empty($event_object->payment_intent)) {
                    if ($payment->status === 'succeeded' && !empty($payment->stripe_payment_intent_id) && $payment->stripe_payment_intent_id === $event_object->payment_intent) {
                    } elseif ($payment->status === 'pending') {
                        $payment_updated = $this->payment_model->update_payment($payment->id, [
                            'status' => 'succeeded',
                            'stripe_payment_intent_id' => $event_object->payment_intent,
                            'paid_at' => $now,
                        ]);
                        if ($payment_updated === false) {
                            log_message('error', 'Payment update error: ' . $payment->id);
                            $has_error = true;
                        }
                    } else {
                        log_message('error', 'Payment intent check error: ' . $payment->id);
                        $has_error = true;
                    }

                    if ($has_error === false) {
                        $receipt_existing = $this->receipt_model->get_receipt_by_payment_id($payment->id);

                        if ($receipt_existing === false) {
                            log_message('error', 'Receipt lookup query error: ' . $payment->id);
                            $has_error = true;
                        } elseif ($receipt_existing === null) {
                            $receipt_created = $this->receipt_model->create_receipt($payment->id, $now);

                            if ($receipt_created === false) {
                                log_message('error', 'Receipt creation error: ' . $payment->id);
                                $has_error = true;
                            }
                        }
                    }

                    if ($has_error === false) {
                        $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                            'processing_status' => 'processed',
                            'processed_at' => $now,
                        ]);
                        if ($updated_event === false) {
                            log_message('error', 'Webhook event update error: ' . $stored_event->id);
                            $has_error = true;
                        }
                    }
                } else {
                    log_message('error', 'Payment intent empty error: ' . $payment->id);
                    $has_error = true;
                }
                if ($has_error === false && $this->db->trans_status() !== false) {
                    $this->db->trans_commit();
                    return $this->output->set_status_header(200);
                }

                $this->db->trans_rollback();
                return $this->output->set_status_header(500);
            } else {
                log_message('error', 'Payment and session data check error: ' . $webhook_event->id);
                $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                    'processing_status' => 'failed',
                    'processed_at' => $now,
                ]);

                if ($updated_event === false) {
                    log_message('error', 'Event update error: ' . $stored_event->id);
                    return $this->output->set_status_header(500);
                }
                return $this->output->set_status_header(400);
            }
        } else {
            $updated_event = $this->webhook_event_model->update_event($stored_event->id, [
                'processing_status' => 'ignored',
                'processed_at' => $now,
            ]);

            if ($updated_event === false) {
                log_message('error', 'Event update error: ' . $stored_event->id);
                return $this->output->set_status_header(500);
            }
            return $this->output->set_status_header(200);
        }
    }

    public function convert_to_cents($payment_total)
    {
        $parts = explode('.', $payment_total);

        $whole_number = $parts[0];
        $fractional = $parts[1];

        $result = $whole_number * 100 + $fractional;

        return (int) $result;
    }
}
