<?php

class Admin extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dashboard()
    {
        return $this->load->view('admin/dashboard');
    }
}
