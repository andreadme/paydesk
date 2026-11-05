<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('payment_model');
    }

    public function create_order($user_id, $subtotal, $total, $cart_items)
    {
        $this->db->trans_begin();
        $now = gmdate('Y-m-d H:i:s');
        $has_error = false;
        $order_number = $this->generate_reference_number('ORD-');
        $order = $this->db->insert('orders', [
            'user_id' => $user_id,
            'order_number' => $order_number,
            'subtotal' => $subtotal,
            'total' => $total,
        ]);

        $order_id = null;
        if ($order === false) {
            $has_error = true;
        }

        if ($has_error === false) {
            $order_id = $this->db->insert_id();

            $items = [];
            foreach ($cart_items as $product_id => $cart_item) {
                $items[] = [
                    'order_id' => $order_id,
                    'product_id' =>$product_id,
                    'product_name' => $cart_item['name'],
                    'unit_price' => $cart_item['price'],
                    'quantity' => $cart_item['quantity'],
                    'line_total' => $cart_item['line_total']
                ];
            }
    
            $batch_created = $this->db->insert_batch('order_items', $items);
    
            $invoice_created = $this->db->insert('invoices', [
                'order_id' => $order_id,
                'invoice_number' => $this->generate_reference_number('INV-'),
                'issued_at' => $now,
            ]);
    
            if ($batch_created === false || $invoice_created === false) {
                $has_error = true;
            }
        }

        if ($has_error === false) {
            $payment = $this->db->insert('payments', [
                'order_id' => $order_id,
                'total' => $total
            ]);
    
            if ($payment === false) {
                $has_error = true;
            }
        }

        if ($has_error === false) {
            $payment_id = $this->db->insert_id();
            $payment_log_created = $this->payment_model->create_payment_log([
                'payment_id' => $payment_id,
                'status' => 'pending',
                'description' => 'Stripe Checkout payment created',
            ]);
            if ($payment_log_created === false) {
                $has_error = true;
            }
        }


        if ($has_error === false && $this->db->trans_status() !== false) {
            $this->db->trans_commit();
            return (object) [
                'id' => $order_id,
                'order_number' => $order_number,
            ];
        }

        $this->db->trans_rollback();
        return false;
    }

    public function get_order($order_id)
    {
        $this->db->select('id, order_number');
        $this->db->where('id', $order_id);
        $this->db->limit(1);
        $query = $this->db->get('orders');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function get_order_by_order_number($order_number)
    {
        $this->db->select('id, order_number');
        $this->db->where('order_number', $order_number);
        $this->db->limit(1);
        $query = $this->db->get('orders');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function get_invoice_by_invoice_number($invoice_number)
    {
        $this->db->select('id, invoice_number');
        $this->db->where('invoice_number', $invoice_number);
        $this->db->limit(1);
        $query = $this->db->get('invoices');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }

    public function generate_reference_number($prefix)
    {
        $max_attempt = 5;
        if (!in_array($prefix, ['ORD-', 'INV-'], true)) {
            throw new InvalidArgumentException('Unsupported reference number prefix.');
        }

        for ($attempt_count = 0; $attempt_count < $max_attempt; $attempt_count++) {
            $reference_number = $prefix . strtoupper(bin2hex(random_bytes(6)));
            $is_existing = true;
            if ($prefix === 'ORD-') {
                $is_existing = $this->get_order_by_order_number($reference_number);
            } elseif ($prefix === 'INV-') {
                $is_existing = $this->get_invoice_by_invoice_number($reference_number);
            }
            if ($is_existing === null) {
                return $reference_number;
            } elseif ($is_existing === false) {
                throw new RuntimeException("Unable to check order/invoice reference number.");
            } else {
                continue;
            }
        }

        throw new RuntimeException("Unable to generate a unique order/invoice reference number after multiple attempts.");
    }
}
