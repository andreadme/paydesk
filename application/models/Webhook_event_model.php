<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Webhook_event_model extends CI_Model
{
    public function get_event_by_stripe_id($stripe_event_id)
    {
        $this->db->select('id, payment_id, stripe_event_id, event_type, processing_status, processed_at');
        $this->db->where('stripe_event_id', $stripe_event_id);
        $this->db->limit(1);
        $query = $this->db->get('webhook_events');

        if ($query === false) {
            return false; // db query failed
        }

        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }

        return $query->row(); // exists
    }

    public function create_event($data)
    {
        return $this->db->insert('webhook_events', $data);
    }

    public function update_event($event_id, $data)
    {
        $this->db->where('id', $event_id);
        return $this->db->update('webhook_events', $data);
    }

    public function claim_event($id)
    {
        $this->db->where('id', $id);
        $this->db->where('processing_status', 'pending');
        $result = $this->db->update('webhook_events', ['processing_status' => 'processing']);

        if ($result === false) {
            return false;
        } else {
            $affected_rows = $this->db->affected_rows();

            if ($affected_rows === 1) {
                return 1;
            } elseif ($affected_rows === 0) {
                return 0;
            }
            
            return false;
        }
    }
}
