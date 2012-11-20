<?php

namespace TrainingWheels\Log;

class Logger {
  // The singleton.
  private static $instance;

  // For logging, we save a display function for either drush or Drupal.
  protected $displayFunction;

  // Which level to display.
  public $display_level;

  /**
   * Return the singleton.
   */
  public static function singleton($display_level = L_NONE) {
    if (!isset(self::$instance)) {
      $className = __CLASS__;
      self::$instance = new $className;
      $s = self::$instance;
      $s->display_level = $display_level;
    }
    return self::$instance;
  }

  /**
   * Detect whether we're in drush or not, and set the function appropriately.
   */
  public function detectDisplayFunction() {
    $this->displayFunction = 'print_r';
  }

  /**
   * Output the given message appropriately (drush_print/drupal_set_message/etc.)
   */
  public function log($message, $level = L_DEBUG, $color = FALSE) {
    if (!$this->displayFunction) {
      $this->detectDisplayFunction();
    }
    if ($level <= $this->display_level) {
      if ($this->displayFunction == 'drush_log' && $color) {
        switch ($color) {
          case 'green':
            $cc = "\033[01;32m";
            break;
          case 'red':
            $cc = "\033[01;31m";
            break;
          case 'cyan':
            $cc = "\033[01;36m";
            break;
        }
        if ($cc) {
          $message = $cc . $message . "\033[0m";
        }
      }
      return call_user_func($this->displayFunction, $message, 'status');
    }
  }

  /**
   * Prevent people creating objects of this type instead of using singleton.
   */
  public function __clone() {
    trigger_error('Clone is not allowed.', E_USER_ERROR);
  }

  /**
   * Prevent people serializing which would be another way to clone the object.
   */
  public function __wakeup() {
    trigger_error('Unserializing is not allowed.', E_USER_ERROR);
  }
}
