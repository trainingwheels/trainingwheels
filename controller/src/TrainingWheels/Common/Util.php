<?php

namespace TrainingWheels\Common;
use TrainingWheels\Log\Log;
use Exception;

class Util {
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
