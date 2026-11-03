<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function get_user_by_email($email)
    {
        $this->db->select('id, name, email, password_hash, role');
        $this->db->where('email', $email);
        $this->db->limit(1);
        $query = $this->db->get('users');

        if ($query->num_rows() === 1) {
            return $query->row();
        }

        return false;
    }

    public function get_users()
    {
        $this->db->select('id, name, email, role');
        $query = $this->db->get('users');

        return $query->result();
    }

    public function create_user($name, $email, $hashed_password)
    {
        return $this->db->insert('users', [
            'name' => $name,
            'email' => $email,
            'role' => 'customer',
            'password_hash' => $hashed_password
        ]);
    }
}
