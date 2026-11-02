<?php

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        $this->load->model('user_model');
    }

    public function index()
    {
        if ($this->session->userdata('logged_in')) {
            if ($this->session->userdata('role') === 'admin') {
                redirect('admin/dashboard');
            } elseif ($this->session->userdata('role') === 'customer') {
                redirect('customer/dashboard');
            }
        } else {
            return $this->load->view('auth/login');
        }
    }

    public function login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->user_model->get_user_by_email($email);

        if ($user && password_verify($password, $user->password_hash)) {
            $this->session->sess_regenerate();
            $this->session->set_userdata([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'logged_in' => true,
            ]);

            if ($user->role === 'admin') {
                redirect('admin/dashboard');
            } elseif ($user->role === 'customer') {
                redirect('customer/dashboard');
            }
        } else {
            $this->session->set_flashdata('error', 'Invalid email or password.');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();

        redirect('auth');
    }
}
