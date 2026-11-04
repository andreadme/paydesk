<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['public_key'] = getenv('STRIPE_PUBLIC_KEY');
$config['secret_key'] = getenv('STRIPE_SECRET_KEY');
$config['webhook_secret'] = getenv('STRIPE_WEBHOOK_SECRET');