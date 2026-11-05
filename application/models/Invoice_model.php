<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_model extends CI_Model
{
    public function get_invoices()
    {
        $this->db->select('invoices.id, orders.user_id, invoices.invoice_number, invoices.issued_at, orders.order_number, orders.total, orders.currency, users.name, users.email');
        $this->db->join('orders', 'orders.id = invoices.order_id', 'inner');
        $this->db->join('users', 'users.id = orders.user_id', 'inner');
        $this->db->order_by('invoices.issued_at', 'DESC');
        $query = $this->db->get('invoices');

        if ($query === false) {
            return false;
        }

        return $query->result();
    }

    public function get_invoice($invoice_id)
    {
        $this->db->select('invoices.id, orders.user_id, invoices.order_id, invoices.invoice_number, invoices.issued_at, orders.order_number, orders.subtotal, orders.total, orders.currency, users.name, users.email');
        $this->db->join('orders', 'orders.id = invoices.order_id', 'inner');
        $this->db->join('users', 'users.id = orders.user_id', 'inner');
        $this->db->where('invoices.id', $invoice_id);
        $query = $this->db->get('invoices');

        if ($query === false) {
            return false;
        }

        return $query->row();
    }

    public function get_user_invoices($user_id)
    {
        if (empty($user_id) || filter_var($user_id, FILTER_VALIDATE_INT) === false || $user_id <= 0) {
            return false;
        }

        $this->db->select('invoices.id, orders.user_id, invoices.invoice_number, invoices.issued_at, orders.order_number, orders.total, orders.currency');
        $this->db->join('orders', 'orders.id = invoices.order_id', 'inner');
        $this->db->where('orders.user_id', $user_id);
        $query = $this->db->get('invoices');

        if ($query === false) {
            return false;
        }

        return $query->result();
    }

    public function get_user_invoice($invoice_id, $user_id)
    {
        if (empty($user_id) || filter_var($user_id, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        $this->db->select('invoices.id, orders.user_id, invoices.invoice_number, invoices.issued_at, users.name, users.email, orders.order_number, orders.subtotal, orders.total');
        $this->db->join('orders', 'orders.id = invoices.order_id', 'inner');
        $this->db->join('users', 'users.id = orders.user_id', 'inner');
        $this->db->where('users.id', $user_id);
        $this->db->where('invoices.id', $invoice_id);
        $query = $this->db->get('invoices');

        if ($query === false) {
            return false; // db query failed
        }

        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }

        return $query->row(); // exists
    }

    public function get_invoice_items($order_id)
    {
        if (empty($order_id) || filter_var($order_id, FILTER_VALIDATE_INT) === false || $order_id <= 0) {
            return false;
        }

        $this->db->select('order_items.id, order_items.product_name, order_items.quantity, order_items.unit_price, order_items.line_total');
        $this->db->join('orders', 'orders.id = order_items.order_id', 'inner');
        $this->db->where('order_items.order_id', $order_id);

        $query = $this->db->get('order_items');
        if ($query === false) {
            return false;
        }

        return $query->result();
    }

    public function get_user_invoice_items($order_id, $user_id)
    {
        if (empty($user_id) || filter_var($user_id, FILTER_VALIDATE_INT) === false || $user_id <= 0) {
            return false;
        }

        if (empty($order_id) || filter_var($order_id, FILTER_VALIDATE_INT) === false || $order_id <= 0) {
            return false;
        }

        $this->db->select('order_items.id, order_items.product_name, order_items.quantity, order_items.unit_price, order_items.line_total');
        $this->db->join('orders', 'orders.id = order_items.order_id', 'inner');
        $this->db->where('order_items.order_id', $order_id);
        $this->db->where('orders.user_id', $user_id);

        $query = $this->db->get('order_items');
        if ($query === false) {
            return false;
        }

        return $query->result();
    }
}
