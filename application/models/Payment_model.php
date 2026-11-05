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

    public function get_payment_by_intent_id($intent_id)
    {
        $this->db->select('id, order_id, stripe_checkout_session_id, total, currency, status');
        $this->db->where('stripe_checkout_session_id', $intent_id);
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

        return $this->db->update('payments', $payment_data);
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

    public function get_payment_logs()
    {
        $this->db->select('payment_logs.id, payment_logs.payment_id, orders.order_number, payment_logs.status, payment_logs.description, payment_logs.created_at, users.name, users.email');
        $this->db->join('payments', 'payments.id = payment_logs.payment_id', 'inner');
        $this->db->join('orders', 'orders.id = payments.order_id', 'inner');
        $this->db->join('users', 'users.id = orders.user_id', 'inner');
        $this->db->order_by('payment_logs.created_at', 'DESC');
        $query = $this->db->get('payment_logs');
        if ($query === false) {
            return false;
        }

        return $query->result();
    }

    public function create_payment_log($data)
    {
        if (empty($data['payment_id']) || filter_var($data['payment_id'], FILTER_VALIDATE_INT) === false || $data['payment_id'] <= 0) {
            return false;
        }

        if (!in_array($data['status'], ['pending', 'succeeded', 'failed'])) {
            return false;
        }

        return $this->db->insert('payment_logs', $data);
    }
}
