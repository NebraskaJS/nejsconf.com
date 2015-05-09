<?php
  if( ! defined('VALID_ENTRY_POINT') ) {
    header("HTTP/1.0 404 Not Found");
    die("404 Not Found");
  }

  function arr_get($array, $key, $default=null) {
    if(isset($array[$key])) {
      return $array[$key];
    }
    return $default;
  }

  function strtolower_keys($array) {
    $insensitive = array();
    foreach($array as $key => $val) {
      $insensitive[strtolower($key)] = $val;
    }
    return $insensitive;
  }

  function arr_iget($array, $key, $default=null) {
    $insensitive = strtolower_keys($array);
    if(isset($insensitive[strtolower($key)])) {
      return $insensitive[strtolower($key)];
    }
    return $default;
  }
