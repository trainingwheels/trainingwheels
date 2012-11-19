<?php

namespace TrainingWheels\Log;

/**
 * Log message severity
 */
define('L_NONE', 0);
define('L_DEBUG', 1);
define('L_VERBOSE', 2);

/**
 * Wrap the Logger so we don't have to call singleton() every time.
 */
class Log {
  private static $logger = NULL;

  /**
   * Need an empty constructor.
   */
  private function __construct() {
  }

  /**
   * Create the logger if it's not there yet, and log.
   */
  public static function log($message, $level, $color = FALSE) {
    if (self::$logger == NULL) {
      self::$logger = Logger::singleton(L_VERBOSE);
      self::$logger->log("+-----------------+", L_DEBUG, 'cyan');
      self::$logger->log("| Training Wheels |", L_DEBUG, 'cyan');
      self::$logger->log("+-----------------+", L_DEBUG, 'cyan');
    }
    self::$logger->log($message, $level, $color);
  }

  /**
   * Return the current debug level. Useful for figuring out whether to run
   * potentially expensive debugging calculations.
   */
  public function getLevel() {
    if (self::$logger == NULL) {
      self::$logger = Logger::singleton(L_VERBOSE);
    }
    return self::$logger->displayLevel;
  }
}
