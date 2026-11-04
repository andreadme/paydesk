<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipt_model extends CI_Model
{
    public function get_receipt_by_payment_id($payment_id)
    {
        $this->db->select('id, payment_id, receipt_number, issued_at');
        $this->db->where('payment_id', $payment_id);
        $this->db->limit(1);
        $query = $this->db->get('receipts');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function get_receipt_by_receipt_number($receipt_number)
    {
        $this->db->select('id, payment_id, receipt_number, issued_at');
        $this->db->where('receipt_number', $receipt_number);
        $this->db->limit(1);
        $query = $this->db->get('receipts');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function create_receipt($payment_id, $issued_at)
    {
        return $this->db->insert('receipts', [
            'payment_id' => $payment_id,
            'receipt_number' => $this->generate_reference_number('REC-'),
            'issued_at' => $issued_at
        ]);
    }

    public function generate_reference_number($prefix)
    {
        if (!in_array($prefix, ['REC-'], true)) {
            throw new InvalidArgumentException('Unsupported reference number prefix.');
        }

        $reference_number = $prefix . strtoupper(bin2hex(random_bytes(6)));

        $is_existing = true;
        if ($prefix === 'REC-') {
            $is_existing = $this->get_receipt_by_receipt_number($reference_number);
        }

        if ($is_existing === null) {
            return $reference_number;
        }

        return $this->generate_reference_number($prefix);
    }

    public function get_user_receipts($user_id)
    {
        if (empty($user_id) || filter_var($user_id, FILTER_VALIDATE_INT) === false || $user_id <= 0) {
            return false;
        }

        $this->db->select('receipts.id, orders.user_id, receipts.receipt_number, receipts.issued_at, payments.status, invoices.invoice_number, orders.order_number, orders.total, orders.currency');
        $this->db->join('payments', 'payments.id = receipts.payment_id', 'inner');
        $this->db->join('orders', 'orders.id = payments.order_id', 'inner');
        $this->db->join('invoices', 'invoices.order_id = orders.id', 'inner');
        $this->db->where('orders.user_id', $user_id);
        $this->db->where('payments.status', 'succeeded');
        $this->db->order_by('receipts.issued_at', 'DESC');
        $query = $this->db->get('receipts');

        if ($query === false) {
            return false;
        }

        return $query->result();
    }

    public function get_user_receipt($receipt_id, $user_id)
    {
        if (empty($user_id) || filter_var($user_id, FILTER_VALIDATE_INT) === false || $user_id <= 0) {
            return false;
        }

        if (empty($receipt_id) || filter_var($receipt_id, FILTER_VALIDATE_INT) === false || $receipt_id <= 0) {
            return false;
        }

        $this->db->select('receipts.id, orders.user_id, receipts.receipt_number, receipts.issued_at, payments.stripe_payment_intent_id, payments.status, invoices.invoice_number, orders.order_number, orders.subtotal, orders.total, orders.currency, users.name, users.email');
        $this->db->join('payments', 'payments.id = receipts.payment_id', 'inner');
        $this->db->join('orders', 'orders.id = payments.order_id', 'inner');
        $this->db->join('invoices', 'invoices.order_id = orders.id', 'inner');
        $this->db->join('users', 'users.id = orders.user_id', 'inner');
        $this->db->where('users.id', $user_id);
        $this->db->where('receipts.id', $receipt_id);
        $query = $this->db->get('receipts');

        if ($query === false) {
            return false; // db query failed
        }

        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }

        return $query->row(); // exists
    }
}
