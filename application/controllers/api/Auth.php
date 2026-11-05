<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('api_auth');
        $this->config->load('api');
    }

    public function login()
    {
        $data = json_decode($this->input->raw_input_stream, true);

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->db
            ->where('email', $email)
            ->get($this->config->item('api_user_table'))
            ->row();

        if (!$user || !password_verify($password, $user->password)) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ]));
        }

        $token = $this->api_auth->generate_token();

        $expiry = $this->config->item('api_token_expiry');

        $this->db->insert($this->config->item('api_token_table'), [
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $expiry)
        ]);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'token' => $token,
                'token_type' => $this->config->item('api_token_type'),
                'expires_in' => $expiry
            ]));
    }

    public function logout()
    {
        $user_id = $this->api_auth->authenticate();

        if (!$user_id) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Unauthorized'
                ]));
        }

        $this->api_auth->revoke_token();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Logged out successfully'
            ]));
    }
}