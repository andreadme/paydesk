<?php

class Admin extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('user_model');
        $this->load->model('product_model');
        $this->load->library('form_validation');
    }

    public function dashboard()
    {
        return $this->load->view('admin/dashboard');
    }

    public function users()
    {
        $users = $this->user_model->get_users();
        return $this->load->view('admin/users', ['users' => $users]);
    }

    public function create_user()
    {
        return $this->load->view('admin/create_user');
    }

    public function store_user()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[5]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[5]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() == false) {
            return $this->load->view('admin/create_user');
        }

        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $data = $this->user_model->create_user($name, $email, $hashed_password);

        if ($data) {
            $this->session->set_flashdata('success', 'User created successfully!');
        } else {
            $this->session->set_flashdata('error', 'Unable to create user. Please try again.');
        }

        redirect('admin/users');
    }

    public function products()
    {
        $products = $this->product_model->get_products();
        return $this->load->view('admin/products', ['products' => $products]);
    }

    public function create_product()
    {
        return $this->load->view('admin/create_product');
    }

    public function store_product()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[5]');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('stock', 'Stock', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric|greater_than_equal_to[0]');

        if ($this->form_validation->run() == false) {
            return $this->load->view('admin/create_product');
        }

        $name = $this->input->post('name');
        $description = $this->input->post('description');
        $stock = $this->input->post('stock');
        $price = $this->input->post('price');

        $data = $this->product_model->create_product($name, $description, $stock, $price);

        if ($data) {
            $this->session->set_flashdata('success', 'Product created successfully!');
        } else {
            $this->session->set_flashdata('error', 'Unable to create product. Please try again.');
        }

        redirect('admin/products');
    }
}
