<?php

use Stripe\Stripe;
use Stripe\Checkout\Session;

class Customer extends Customer_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();

        $this->load->model('product_model');
        $this->load->model('order_model');
        $this->load->model('payment_model');
        $this->load->model('invoice_model');
        $this->load->model('receipt_model');
        $this->config->load('stripe');
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
        $cart_items = $this->validate_order();

        if ($cart_items === false) {
            redirect('customer/products');
            return;
        }

        $this->session->set_userdata(['cart' => $cart_items]);
        return $this->load->view('customer/checkout');
    }

    public function confirm_payment()
    {
        $cart_items = $this->validate_order();

        if ($cart_items === false) {
            $this->session->set_flashdata('error', 'Unable to create your order. Please try again.');

            redirect('customer/checkout');
            return;
        }

        $user_id = $this->session->userdata('user_id');

        $subtotal = array_sum(array_column($cart_items, 'line_total'));
        $order = $this->order_model->create_order($user_id, $subtotal, $subtotal, $cart_items);

        if (!empty($order)) {
            $payment = $this->payment_model->get_payment_by_order_id($order->id);

            $stripe_line_items = [];
            if ($payment && $payment->status === 'pending') {
                $currency = strtolower($payment->currency);
                foreach ($cart_items as $product_id => $cart_item) {
                    $stripe_line_items[] = [
                        'price_data' => [
                            'product_data' => [
                                'name' => $cart_item['name'],
                            ],
                            'currency' => $currency,
                            'unit_amount' => (int) ($cart_item['price'] * 100),
                        ],
                        'quantity' => $cart_item['quantity'],
                    ];
                }

                Stripe::setApiKey($this->config->item('secret_key'));

                try {
                    $stripe_session = Session::create([
                        'mode' => 'payment',
                        'currency' => $currency,
                        'line_items' => $stripe_line_items,
                        'success_url' => site_url('customer/show_payment_success') . '?session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => site_url('customer/show_payment_cancelled'),
                        'client_reference_id' => $order->order_number,
                        'metadata' => [
                            'order_id' => (string) $order->id,
                            'payment_id' => (string) $payment->id,
                        ],
                    ]);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    log_message('error', 'Stripe Checkout error: ' . $e->getMessage());

                    $this->session->set_flashdata('error', 'Unable to connect to the payment service. Please try again.');

                    redirect('customer/checkout');
                    return;
                }

                $updated_payment = $this->payment_model->update_checkout_session_id($payment->id, $stripe_session->id);

                if ($updated_payment) {
                    redirect($stripe_session->url, 'location', 303);
                    return;
                } else {
                    $this->session->set_flashdata('error', 'Unable to create your order. Please try again.');

                    redirect('customer/checkout');
                    return;
                }
            } else {
                $this->session->set_flashdata('error', 'Unable to create your order. Please try again.');

                redirect('customer/checkout');
                return;
            }
        } else {
            $this->session->set_flashdata('error', 'Unable to create your order. Please try again.');

            redirect('customer/checkout');
            return;
        }
    }

    public function validate_order()
    {
        $cart_items = $this->session->userdata('cart');

        if (empty($cart_items)) {
            $this->session->set_flashdata('info', 'Your shopping cart is empty. Please add products to your cart before proceeding to checkout.');
            return false;
        }

        $new_cart_items = [];
        foreach ($cart_items as $product_id => $cart_item) {
            if (filter_var($cart_item['quantity'], FILTER_VALIDATE_INT) === false || $cart_item['quantity'] <= 0) {
                $this->session->set_flashdata('warning', 'Invalid quantity for ' . $cart_item['name'] . '. Please enter a whole number greater than zero.');
                return false;
            }

            $product = $this->product_model->get_product($product_id);

            if ($product && $product->is_active) {
                if (filter_var($product->price, FILTER_VALIDATE_FLOAT) === false || $product->price < 0) {
                    $this->session->set_flashdata('warning', 'Invalid price for ' . $cart_item['name'] . '. Please enter a whole number greater than zero.');
                    return false;
                }

                if (filter_var($product->stock, FILTER_VALIDATE_INT) === false || $product->stock < 0) {
                    $this->session->set_flashdata('warning', 'Invalid stock for ' . $cart_item['name'] . '. Please enter a whole number greater than zero.');
                    return false;
                }

                if ($cart_item['quantity'] > $product->stock) {
                    $this->session->set_flashdata('warning', 'The requested quantity for ' . $product->name . ' exceeds the available stock. Please update your cart quantity and try again.');
                    return false;
                } else {
                    $line_total = $cart_item['quantity'] * $product->price;
                    $new_cart_items[$product_id]['name'] = $product->name;
                    $new_cart_items[$product_id]['price'] = $product->price;
                    $new_cart_items[$product_id]['quantity'] = $cart_item['quantity'];
                    $new_cart_items[$product_id]['line_total'] = $line_total;
                }
            } else {
                $this->session->set_flashdata('warning', $cart_item['name'] . ' is no longer available for purchase. Please remove it from your cart to continue.');
                return false;
            }
        }

        return $new_cart_items;
    }

    public function show_payment_success()
    {
        $session_id = $this->input->get('session_id');
        $user_id = $this->session->userdata('user_id');
        $payment = $this->payment_model->get_payment_by_session_id_and_user_id($session_id, $user_id);
        $order = $this->order_model->get_order($payment->order_id);
        if ($payment->status === 'succeeded') {
            return $this->load->view('customer/payment_success', [
                'order' => $order,
                'payment' => $payment,
            ]);
        } else {
            return $this->load->view('customer/payment_pending', [
                'order' => $order,
                'payment' => $payment,
            ]);
        }
    }

    public function show_payment_cancelled()
    {
        return $this->load->view('customer/payment_cancelled');
    }

    public function my_invoices()
    {
        $user_id = $this->session->userdata('user_id');

        $my_invoices = $this->invoice_model->get_user_invoices($user_id);

        if ($my_invoices === false) {
            $this->session->set_flashdata('error', 'Unable to load your invoices. Please try again.');

            redirect('customer/dashboard');
            return;
        }

        return $this->load->view('customer/invoices', ['invoices' => $my_invoices]);
    }

    public function my_invoice($invoice_id)
    {
        $user_id = $this->session->userdata('user_id');

        $invoice = $this->invoice_model->get_user_invoice($invoice_id, $user_id);
        $order_items = $this->invoice_model->get_user_invoice_items($invoice_id, $user_id);

        if ($invoice === false) {
            $this->session->set_flashdata('error', 'Unable to load your invoice. Please try again.');

            redirect('customer/my_invoices');
            return;
        }

        return $this->load->view('customer/invoice', ['invoice' => $invoice, 'order_items' => $order_items]);
    }

    public function my_receipts()
    {
        $user_id = $this->session->userdata('user_id');

        $my_receipts = $this->receipt_model->get_user_receipts($user_id);

        if ($my_receipts === false) {
            $this->session->set_flashdata('error', 'Unable to load your receipts. Please try again.');

            redirect('customer/dashboard');
            return;
        }

        return $this->load->view('customer/receipts', ['receipts' => $my_receipts]);
    }

    public function my_receipt($receipt_id)
    {
        $user_id = $this->session->userdata('user_id');

        $receipt = $this->receipt_model->get_user_receipt($receipt_id, $user_id);

        if ($receipt === false) {
            $this->session->set_flashdata('error', 'Unable to load your receipt. Please try again.');

            redirect('customer/my_receipts');
            return;
        }

        return $this->load->view('customer/receipt', ['receipt' => $receipt]);
    }
}
