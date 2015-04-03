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
