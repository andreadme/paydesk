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

    public function create_product($name, $description, $price, $stock)
    {
        return $this->db->insert('products', [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
        ]);
    }

    public function get_product($id)
    {
        $this->db->select('id, name, price, stock, is_active');
        $this->db->where('id', $id);
        $this->db->limit(1);
        $query = $this->db->get('products');

        if ($query->num_rows() === 1) {
            return $query->row();
        }

        return false;
    }
}
