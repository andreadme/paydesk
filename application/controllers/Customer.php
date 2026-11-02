<?php

class Customer extends Customer_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();

        $this->load->model('product_model');
    }

    public function dashboard()
    {
        return $this->load->view('customer/dashboard');
    }
}
