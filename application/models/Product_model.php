<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public function get_products()
    {
        $this->db->select('id, name, description, price, stock, is_active');
        $this->db->where('is_active', 1);
        $query = $this->db->get('products');

        return $query->result();
    }

    public function create_product($data)
    {
        return $this->db->insert('products', $data);
    }

    public function get_product($id)
    {
        $this->db->select('id, name, price, stock, is_active');
        $this->db->where('id', $id);
        $this->db->limit(1);
        $query = $this->db->get('products');

        if ($query === false) {
            return false; // db query failed
        }
    
        if ($query->num_rows() === 0) {
            return null; // doesn't exist
        }
    
        return $query->row(); // exists
    }
}
