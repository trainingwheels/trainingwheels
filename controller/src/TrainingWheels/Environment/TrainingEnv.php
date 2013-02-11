<?php

namespace TrainingWheels\Environment;
use BadMethodCallException;

abstract class TrainingEnv {

  /**
   * Allows us to dynamically add methods to any subclass of TrainingEnv, giving
   * mixin-like abilities to the plugins.
   */
  public function __call($method, $args) {
    if (isset($this->$method)) {
      $func = $this->$method;
      if (is_callable($func)) {
        array_unshift($args, $this);
        return call_user_func_array($func, $args);
      }
      else {
        throw new BadMethodCallException("The method \"$method\" is not callable.");
      }
    }
    else {
      throw new BadMethodCallException("The method \"$method\" does not exist.");
    }
  }
}
