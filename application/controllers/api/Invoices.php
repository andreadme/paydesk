<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoices extends CI_Controller
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
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Unauthorized'
                ]));

            return false;
        }

        return $user_id;
    }

    // GET /api/invoices
    public function index()
    {
        $user_id = $this->authenticate();

        if (!$user_id) return;

        $invoices = $this->db
            ->where('user_id', $user_id)
            ->get('invoices')
            ->result();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $invoices
            ]));
    }

    // GET /api/invoices/{id}
    public function view($id)
    {
        $user_id = $this->authenticate();

        if (!$user_id) return;

        $invoice = $this->db
            ->where('id', $id)
            ->where('user_id', $user_id)
            ->get('invoices')
            ->row();

        if (!$invoice) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Invoice not found'
                ]));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $invoice
            ]));
    }
}
