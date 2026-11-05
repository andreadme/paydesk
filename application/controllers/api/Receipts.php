<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receipts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('api_auth');
    }

    private function authenticate()
    {
        $user_id = $this->api_auth->authenticate();

        if (!$user_id) {
            $this->output->set_status_header(401)->set_content_type('application/json')->set_output(
                json_encode([
                    'status' => false,
                    'message' => 'Unauthorized',
                ]),
            );

            return false;
        }

        return $user_id;
    }

    // GET /api/receipts
    public function index()
    {
        $user_id = $this->authenticate();

        if (!$user_id) {
            return;
        }

        $receipts = $this->db->where('user_id', $user_id)->get('receipts')->result();

        return $this->output->set_content_type('application/json')->set_output(
            json_encode([
                'status' => true,
                'data' => $receipts,
            ]),
        );
    }

    // GET /api/receipts/{id}
    public function view($id)
    {
        $user_id = $this->authenticate();

        if (!$user_id) {
            return;
        }

        $receipt = $this->db->where('id', $id)->where('user_id', $user_id)->get('receipts')->row();

        if (!$receipt) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(
                json_encode([
                    'status' => false,
                    'message' => 'Receipt not found',
                ]),
            );
        }

        return $this->output->set_content_type('application/json')->set_output(
            json_encode([
                'status' => true,
                'data' => $receipt,
            ]),
        );
    }
}
