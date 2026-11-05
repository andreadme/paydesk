<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_auth
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->config->load('api');
    }

    public function get_token()
    {
        $header = $this->CI->input->get_request_header('Authorization');

        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function authenticate()
    {
        $token = $this->get_token();

        if (!$token) {
            return false;
        }

        $table = $this->CI->config->item('api_token_table');

        $record = $this->CI->db
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get($table)
            ->row();

        return $record ? $record->user_id : false;
    }

    public function revoke_token()
    {
        $token = $this->get_token();

        if (!$token) {
            return false;
        }

        $table = $this->CI->config->item('api_token_table');

        return $this->CI->db
            ->where('token_hash', hash('sha256', $token))
            ->delete($table);
    }

    public function generate_token()
    {
        return bin2hex(random_bytes(32));
    }
}