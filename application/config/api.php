<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['api_token_expiry'] = 7 * 24 * 60 * 60; // 7 days
$config['api_token_type'] = 'Bearer';
$config['api_token_table'] = 'api_tokens';
$config['api_user_table'] = 'users';