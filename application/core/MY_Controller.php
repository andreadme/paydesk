<?php

class MY_Controller extends CI_Controller
{
    protected $current_user;

    public function __construct()
    {
        parent::__construct();

        if ($this->is_authenticated()) {            
            $this->current_user['user_id'] = $this->session->userdata('user_id');
            $this->current_user['name'] = $this->session->userdata('name');
            $this->current_user['email'] = $this->session->userdata('email');
            $this->current_user['role'] = $this->session->userdata('role');
        } else {
            $this->current_user = NULL;
        }
    }

    protected function is_authenticated()
    {
        return $this->session->userdata('logged_in');
    }

    protected function has_role($role)
    {
        return $this->session->userdata('role') === $role;
    }

    protected function require_authentication()
    {
        if (!$this->is_authenticated()) {
            redirect('auth/login');
        }
    }

    protected function require_role($role)
    {
        if (!$this->has_role($role)) {
            show_error('Forbidden', 403);
        }
    }
}

class Admin_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->require_authentication();
        $this->require_role('admin');
    }
}

class Customer_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->require_authentication();
        $this->require_role('customer');
    }
}
