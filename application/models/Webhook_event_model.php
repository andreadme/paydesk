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

    public function create_event($stripe_event_id, $event_type)
    {
        return $this->db->insert('webhook_events', [
            'stripe_event_id' => $stripe_event_id,
            'event_type' => $event_type
        ]);
    }

    public function update_event($event_id, $event_data)
    {
        $this->db->where('id', $event_id);

        return $this->db->update('webhook_events', [
            'processing_status' => $event_data['processing_status'],
            'processed_at' => $event_data['processed_at'],
        ]);
    }
}
