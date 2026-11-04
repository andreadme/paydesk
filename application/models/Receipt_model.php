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
}
