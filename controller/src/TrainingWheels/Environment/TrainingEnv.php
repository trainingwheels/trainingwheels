<?php

namespace TrainingWheels\Environment;
use TrainingWheels\Log\Log;
use BadMethodCallException;
use ReflectionFunction;
use Exception;

abstract class TrainingEnv {

  // Debug setting.
  private $debug;

  /**
   * Constructor.
   */
  public function __construct($debug) {
    $this->debug = $debug;
  }

  /**
   * Allows us to dynamically add methods to any subclass of TrainingEnv, giving
   * mixin-like abilities to the plugins.
   */
  public function __call($method, $args) {
    if (isset($this->$method)) {
      $func = $this->$method;

      if (is_callable($func)) {
        // In debug mode, ensure that the arguments we're calling the function with
        // are sane. Calling these environment functions with empty strings can cause
        // damage and is almost certainly a bug. PHP isn't strict enough about
        // enforcing certain things, so we have to do it ourselves.
        if ($this->debug) {
          $refl = new ReflectionFunction($func);
          $namespace = $refl->getNamespaceName();
          $full_name = $namespace . '::' . $method;

          // count(args)+1 because we add $this just before actually calling it, below.
          if ($refl->getNumberOfRequiredParameters() > count($args) + 1) {
            throw new Exception("Too few parameters passed to \"$full_name\"");
          }

          if (is_array($args) && count($args) > 0) {
            foreach ($args as $arg) {
              if (!is_string($arg) || strlen($arg) < 2) {
                throw new Exception("Invalid string arg passed to \"$full_name\"");
              }
            }
          }

          Log::log($full_name, L_DEBUG);
        }

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
