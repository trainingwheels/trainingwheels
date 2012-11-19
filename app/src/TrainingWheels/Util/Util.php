<?php

namespace TrainingWheels\Util;

class Util {
  /**
   * Ensure parameters are passed as actual strings.
   */
  public static function assertValidStrings($function, $params) {
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
}
