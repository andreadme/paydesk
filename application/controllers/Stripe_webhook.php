<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Webhook;

class Stripe_webhook extends CI_Controller
{
    private $now;

    public function __construct()
    {
        parent::__construct();

        $this->config->load('stripe');
        $this->load->model('payment_model');
        $this->load->model('receipt_model');
        $this->load->model('webhook_event_model');

        $this->now = gmdate('Y-m-d H:i:s');
    }

    /**
     * Stripe webhook entry point.
     */
    public function index()
    {
        $event = $this->verify_webhook();

        if ($event === false) {
            return $this->respond(400);
        }

        $stored_event = $this->register_event($event);

        if ($stored_event === false) {
            return $this->respond(500);
        }

        if ($this->is_event_completed($stored_event)) {
            return $this->respond(200);
        }

        if ($this->db->trans_begin() === false) {
            log_message('error', 'Unable to begin webhook transaction: ' . $event->id);

            return $this->respond(500);
        }

        $claimed = $this->webhook_event_model->claim_event($stored_event->id);

        if ($claimed === false) {
            log_message('error', 'Unable to claim webhook event: ' . $stored_event->id);

            $this->db->trans_rollback();

            return $this->respond(500);
        }

        if ($claimed === 0) {
            $this->db->trans_rollback();

            return $this->respond(200);
        }

        $result = $this->process_event($event);

        if ($result === 'error') {
            $this->db->trans_rollback();

            return $this->respond(500);
        }

        $updated = $this->webhook_event_model->update_event($stored_event->id, [
            'processing_status' => $result,
            'processed_at' => $this->now,
        ]);

        if ($updated === false) {
            log_message('error', 'Webhook event update error: ' . $stored_event->id);

            $this->db->trans_rollback();

            return $this->respond(500);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return $this->respond(500);
        }

        if ($this->db->trans_commit() === false) {
            log_message('error', 'Webhook transaction commit failed: ' . $event->id);

            return $this->respond(500);
        }

        return $this->respond(200);
    }

    /**
     * Verify Stripe webhook signature.
     */
    private function verify_webhook()
    {
        $request_body = $this->input->raw_input_stream;
        $signature = $this->input->get_request_header('Stripe-Signature');
        log_message('error', 'Webhook diagnostic: secret_loaded=' . (empty($this->config->item('webhook_secret')) ? 'no' : 'yes') . ', signature_header=' . (empty($signature) ? 'missing' : 'present') . ', payload_length=' . strlen($request_body));

        try {
            return Webhook::constructEvent($request_body, $signature, $this->config->item('webhook_secret'));
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            log_message('error', 'Stripe signature verification error: ' . $e->getMessage());
        } catch (\UnexpectedValueException $e) {
            log_message('error', 'Invalid webhook payload: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Register or retrieve Stripe event.
     */
    private function register_event($event)
    {
        $existing = $this->webhook_event_model->get_event_by_stripe_id($event->id);

        if ($existing === false) {
            log_message('error', 'Webhook event lookup error: ' . $event->id);

            return false;
        }

        if ($existing !== null) {
            return $existing;
        }

        $created = $this->webhook_event_model->create_event([
            'stripe_event_id' => $event->id,
            'event_type' => $event->type,
        ]);

        if ($created === false) {
            $existing = $this->webhook_event_model->get_event_by_stripe_id($event->id);

            if ($existing === false || $existing === null) {
                log_message('error', 'Webhook event creation error: ' . $event->id);

                return false;
            }

            return $existing;
        }

        $stored = $this->webhook_event_model->get_event_by_stripe_id($event->id);

        if ($stored === false || $stored === null) {
            log_message('error', 'Stored webhook event not found: ' . $event->id);

            return false;
        }

        return $stored;
    }

    /**
     * Check whether event has already been completed.
     */
    private function is_event_completed($event)
    {
        return in_array($event->processing_status, ['processed', 'ignored'], true);
    }

    /**
     * Dispatch Stripe event.
     */
    private function process_event($event)
    {
        $object = $event->data->object;

        switch ($event->type) {
            case 'checkout.session.completed':
                return $this->handle_checkout_completed($object, $event->id);

            case 'payment_intent.payment_failed':
                return $this->handle_payment_failed($object, $event->id);

            default:
                return 'ignored';
        }
    }

    /**
     * Retrieve payment using Stripe metadata.
     *
     * success = payment found
     * failed  = invalid metadata or payment not found
     * error   = database error
     */
    private function find_webhook_payment($metadata, $event_id)
    {
        if (empty($metadata) || empty($metadata->order_id) || empty($metadata->payment_id)) {
            log_message('error', 'Missing webhook metadata: ' . $event_id);

            return [
                'status' => 'failed',
                'payment' => null,
            ];
        }

        $payment = $this->payment_model->get_payment($metadata->payment_id);

        if ($payment === false) {
            log_message('error', 'Payment lookup failed: ' . $metadata->payment_id);

            return [
                'status' => 'error',
                'payment' => null,
            ];
        }

        if ($payment === null) {
            log_message('error', 'Payment not found: ' . $metadata->payment_id);

            return [
                'status' => 'failed',
                'payment' => null,
            ];
        }

        return [
            'status' => 'success',
            'payment' => $payment,
        ];
    }

    /**
     * Validate checkout session against payment record.
     */
    private function validate_checkout_payment($payment, $session, $metadata)
    {
        if ($payment->stripe_checkout_session_id !== $session->id) {
            return false;
        }

        if ((int) $payment->order_id !== (int) $metadata->order_id) {
            return false;
        }

        if ((int) $payment->id !== (int) $metadata->payment_id) {
            return false;
        }

        if (!in_array($payment->status, ['pending', 'succeeded'], true)) {
            return false;
        }

        if ($session->payment_status !== 'paid') {
            return false;
        }

        $amount = $this->convert_to_cents($payment->total);

        if ($amount === false || $amount !== $session->amount_total) {
            return false;
        }

        if (strtolower($payment->currency) !== strtolower($session->currency)) {
            return false;
        }

        return true;
    }

    /**
     * Validate payment intent against payment record.
     */
    private function validate_payment_intent($payment, $intent, $metadata)
    {
        if ((int) $payment->order_id !== (int) $metadata->order_id) {
            return false;
        }

        if ((int) $payment->id !== (int) $metadata->payment_id) {
            return false;
        }

        $amount = $this->convert_to_cents($payment->total);

        if ($amount === false || $amount !== $intent->amount) {
            return false;
        }

        if (strtolower($payment->currency) !== strtolower($intent->currency)) {
            return false;
        }

        return true;
    }

    /**
     * Handle checkout.session.completed.
     */
    private function handle_checkout_completed($session, $event_id)
    {
        $lookup = $this->find_webhook_payment($session->metadata, $event_id);

        if ($lookup['status'] !== 'success') {
            return $lookup['status'] === 'error' ? 'error' : 'failed';
        }

        $payment = $lookup['payment'];

        if (!$this->validate_checkout_payment($payment, $session, $session->metadata)) {
            log_message('error', 'Checkout payment validation failed: ' . $event_id);

            return 'failed';
        }

        if (empty($session->payment_intent)) {
            log_message('error', 'PaymentIntent missing: ' . $payment->id);

            return 'failed';
        }

        if ($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id !== $session->payment_intent) {
            log_message('error', 'PaymentIntent mismatch: ' . $payment->id);

            return 'failed';
        }

        if ($payment->status === 'pending') {
            $updated = $this->payment_model->update_payment($payment->id, [
                'status' => 'succeeded',
                'stripe_payment_intent_id' => $session->payment_intent,
                'paid_at' => $this->now,
            ]);

            if ($updated === false) {
                log_message('error', 'Payment update error: ' . $payment->id);

                return 'error';
            }

            $logged = $this->payment_model->create_payment_log([
                'payment_id' => $payment->id,
                'status' => 'succeeded',
                'description' => 'Stripe payment successfully completed',
            ]);

            if ($logged === false) {
                log_message('error', 'Payment log creation error: ' . $payment->id);

                return 'error';
            }
        }

        if (!$this->ensure_receipt($payment->id)) {
            return 'error';
        }

        return 'processed';
    }

    /**
     * Ensure receipt exists.
     */
    private function ensure_receipt($payment_id)
    {
        $receipt = $this->receipt_model->get_receipt_by_payment_id($payment_id);

        if ($receipt === false) {
            log_message('error', 'Receipt lookup error: ' . $payment_id);

            return false;
        }

        if ($receipt !== null) {
            return true;
        }

        $created = $this->receipt_model->create_receipt([
            'payment_id' => $payment_id,
            'issued_at' => $this->now,
        ]);

        if ($created === false) {
            log_message('error', 'Receipt creation error: ' . $payment_id);

            return false;
        }

        return true;
    }

    /**
     * Handle payment_intent.payment_failed.
     */
    private function handle_payment_failed($intent, $event_id)
    {
        $lookup = $this->find_webhook_payment($intent->metadata, $event_id);

        if ($lookup['status'] !== 'success') {
            return $lookup['status'] === 'error' ? 'error' : 'failed';
        }

        $payment = $lookup['payment'];

        if (!$this->validate_payment_intent($payment, $intent, $intent->metadata)) {
            log_message('error', 'Failed payment validation error: ' . $payment->id);

            return 'failed';
        }

        if (in_array($payment->status, ['succeeded', 'failed'], true)) {
            if ($payment->stripe_payment_intent_id !== $intent->id) {
                log_message('error', 'PaymentIntent mismatch: ' . $payment->id);

                return 'failed';
            }

            log_message('info', 'Payment already finalized: ' . $payment->id);

            return 'processed';
        }

        if ($payment->status !== 'pending') {
            log_message('error', 'Unexpected payment status: ' . $payment->id);

            return 'failed';
        }

        if (empty($intent->id)) {
            log_message('error', 'PaymentIntent ID missing: ' . $payment->id);

            return 'failed';
        }

        if ($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id !== $intent->id) {
            log_message('error', 'PaymentIntent mismatch: ' . $payment->id);

            return 'failed';
        }

        if ($payment->stripe_payment_intent_id === null) {
            $updated = $this->payment_model->update_payment($payment->id, [
                'stripe_payment_intent_id' => $intent->id,
            ]);

            if ($updated === false) {
                log_message('error', 'Payment update error: ' . $payment->id);

                return 'error';
            }
        }

        $error_message = 'Last payment error message is empty.';

        if (isset($intent->last_payment_error) && isset($intent->last_payment_error->message)) {
            $error_message = $intent->last_payment_error->message;
        } elseif (!isset($intent->last_payment_error)) {
            $error_message = 'Last payment error not found.';
        }

        $logged = $this->payment_model->create_payment_log([
            'payment_id' => $payment->id,
            'status' => 'failed',
            'description' => 'Payment failed: ' . $error_message,
        ]);

        if ($logged === false) {
            log_message('error', 'Payment log creation error: ' . $payment->id);

            return 'error';
        }

        return 'processed';
    }

    /**
     * Safely convert DECIMAL amount to cents.
     *
     * Assumes a two-decimal currency.
     */
    private function convert_to_cents($amount)
    {
        $amount = trim((string) $amount);

        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches)) {
            return false;
        }

        $whole = $matches[1];
        $decimal = isset($matches[2]) ? $matches[2] : '';

        $decimal = str_pad($decimal, 2, '0');

        $cents = ltrim($whole . $decimal, '0');

        if ($cents === '') {
            return 0;
        }

        $max = (string) PHP_INT_MAX;

        if (strlen($cents) > strlen($max)) {
            return false;
        }

        if (strlen($cents) === strlen($max) && strcmp($cents, $max) > 0) {
            return false;
        }

        return (int) $cents;
    }

    /**
     * Return HTTP response.
     */
    private function respond($status)
    {
        return $this->output->set_status_header($status);
    }
}
