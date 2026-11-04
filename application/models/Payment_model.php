<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_model extends CI_Model
{
    public function get_payment($payment_id)
    {
        $this->db->select('id, order_id, stripe_checkout_session_id, stripe_payment_intent_id, total, currency, status');
        $this->db->where('id', $payment_id);
        $this->db->limit(1);
        $query = $this->db->get('payments');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function get_payment_by_order_id($order_id)
    {
        $this->db->select('id, order_id, total, currency, status');
        $this->db->where('order_id', $order_id);
        $this->db->limit(1);
        $query = $this->db->get('payments');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function get_payment_by_session_id($session_id)
    {
        $this->db->select('id, order_id, stripe_checkout_session_id, total, currency, status');
        $this->db->where('stripe_checkout_session_id', $session_id);
        $this->db->limit(1);
        $query = $this->db->get('payments');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function update_checkout_session_id($payment_id, $session_id)
    {
        $this->db->where('id', $payment_id);

        return $this->db->update('payments', [
            'stripe_checkout_session_id' => $session_id,
        ]);
    }

    public function update_payment($payment_id, $payment_data)
    {
        $this->db->where('id', $payment_id);

        return $this->db->update('payments', [
            'status' => $payment_data['status'],
            'stripe_payment_intent_id' => $payment_data['stripe_payment_intent_id'],
            'paid_at' => $payment_data['paid_at'],
        ]);
    }

    public function get_payment_by_session_id_and_user_id($session_id, $user_id)
    {
        $this->db->select('payments.id, orders.user_id, payments.order_id, payments.status, payments.total, payments.currency');
        $this->db->join('orders', 'orders.id = payments.order_id', 'inner');
        $this->db->where('payments.stripe_checkout_session_id', $session_id);
        $this->db->where('orders.user_id', $user_id);
        $this->db->limit(1);
        $query = $this->db->get('payments');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }
}
