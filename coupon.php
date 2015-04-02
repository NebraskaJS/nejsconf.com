<?php
  require 'vendor/autoload.php';
  $config = require 'config.php';

  \Stripe\Stripe::setApiKey($config['stripe']['secret_key']);

  header("Content-Type: application/json; charset=utf-8");

  $code = $_GET['coupon_code'];
  if( isset($config['checkout']['coupons'][$code]) ) {
    die(json_encode(array("code" => $code, "price" => $config['checkout']['coupons'][$code])));
  }
  else {
    die(json_encode(array("code" => false, "price" => $config['checkout']['ticket_price'])));
  }
