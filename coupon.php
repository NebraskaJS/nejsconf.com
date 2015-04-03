<?php
  define('VALID_ENTRY_POINT', true);
  require 'vendor/autoload.php';
  require 'util.php';
  $config = require 'config.php';

  \Stripe\Stripe::setApiKey($config['stripe']['secret_key']);

  header("Content-Type: application/json; charset=utf-8");

  $code = $_GET['coupon_code'];
  $price = arr_get($config['checkout']['coupons'], $code);
  if($price !== null) {
    die(json_encode(array("code" => $code, "price" => $price)));
  }
  else {
    die(json_encode(array("code" => false, "price" => $config['checkout']['ticket_price'])));
  }
