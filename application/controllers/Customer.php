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

    public function products()
    {
        $products = $this->product_model->get_products();
        return $this->load->view('customer/products', ['products' => $products]);
    }

    public function increase_cart_quantity()
    {
        $product_id = $this->input->post('product_id');
        $cart = $this->session->userdata('cart') ?? [];
        $product = $this->product_model->get_product($product_id);

        if (isset($cart[$product_id])) {
            if ($cart[$product_id]['quantity'] > $product->stock) {
                $this->session->set_flashdata('warning', "You've reached the available stock limit for this product.");
                redirect('customer/products');
            }
            $cart[$product_id]['name'] = $product->name;
            $cart[$product_id]['price'] = $product->price;
            $cart[$product_id]['quantity'] += 1;
            $cart[$product_id]['line_total'] = $product->price * $cart[$product_id]['quantity'];
        } else {
            $cart[$product_id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'line_total' => $product->price,
            ];
        }

        $this->session->set_userdata(['cart' => $cart]);
        redirect('customer/products');
    }

    public function decrease_cart_quantity()
    {
        $product_id = $this->input->post('product_id');
        $cart = $this->session->userdata('cart');

        $product = $this->product_model->get_product($product_id);

        if (isset($cart[$product_id])) {
            if ($cart[$product_id]['quantity'] === 1) {
                $this->session->set_flashdata('warning', 'You must keep at least 1 item in your cart. Use the remove button if you no longer need it.');
                redirect('customer/products');
            }

            $cart[$product_id]['name'] = $product->name;
            $cart[$product_id]['price'] = $product->price;
            $cart[$product_id]['quantity'] -= 1;
            $cart[$product_id]['line_total'] = $product->price * $cart[$product_id]['quantity'];
        } else {
            $cart[$product_id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 0,
                'line_total' => 0,
            ];
        }

        $this->session->set_userdata(['cart' => $cart]);
        redirect('customer/products');
    }

    public function clear_cart()
    {
        $this->session->unset_userdata('cart');

        $this->session->set_flashdata('success', 'Your cart has been cleared.');

        redirect('customer/products');
    }

    public function clear_cart_item()
    {
        $product_id = $this->input->post('product_id');
        $cart = $this->session->userdata('cart');
        unset($cart[$product_id]);

        $this->session->set_userdata('cart', $cart);

        redirect('customer/products');
    }

    public function checkout()
    {
        $cart_items = $this->session->userdata('cart');

        if (empty($cart_items)) {
            $this->session->set_flashdata('info', 'Your shopping cart is empty. Please add products to your cart before proceeding to checkout.');
            redirect('customer/products');

            return;
        }

        $new_cart_items = [];
        foreach ($cart_items as $product_id => $cart_item) {
            $product = $this->product_model->get_product($product_id);

            if ($product && $product->is_active) {
                if ($cart_item['quantity'] > $product->stock) {
                    $this->session->set_flashdata('warning', 'The requested quantity for '.$product->name.' exceeds the available stock. Please update your cart quantity and try again.');
                    redirect('customer/products');
                    return;
                } else {
                    $line_total = $cart_item['quantity'] * $product->price;
                    $new_cart_items[$product_id]['name'] = $product->name;
                    $new_cart_items[$product_id]['price'] = $product->price;
                    $new_cart_items[$product_id]['quantity'] = $cart_item['quantity'];
                    $new_cart_items[$product_id]['line_total'] = $line_total;
                }
            } else {
                $this->session->set_flashdata('warning', $cart_item['name'].' is no longer available for purchase. Please remove it from your cart to continue.');
                redirect('customer/products');
                return;
            }
        }

        $this->session->set_userdata(['cart' => $new_cart_items]);
        return $this->load->view('customer/checkout');
    }
}
