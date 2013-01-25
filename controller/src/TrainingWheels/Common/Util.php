<?php

namespace TrainingWheels\Common;
use TrainingWheels\Log\Log;

class Util {
  /**
   * Ensure parameters are passed as actual strings.
   */
  public static function assertValidStrings($function, $params) {
    Log::log($function, L_DEBUG);

    if (is_string($params)) {
      $params = array($params);
    }

    if (isset($params) && is_array($params) && count($params) > 0) {
      foreach ($params as $param) {
        if (!is_string($param) || strlen($param) < 2) {
          throw new Exception("Invalid string parameter passed to $function function.");
        }
      }
    }
    else {
      throw new Exception("No parameters passed to $function function.");
    }
  }

  /**
   * Password generator.
   */
  function passwdGen() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array();
    for ($i = 0; $i < 8; $i++) {
      $n = mt_rand(0, strlen($alphabet) - 1);
      $pass[$i] = $alphabet[$n];
    }
    return implode($pass);
  }
}
