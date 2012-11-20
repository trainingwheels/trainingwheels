<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use Exception;

class ServerConn {

  /**
   * Execute commands, appending an extra "&& echo ..." to test it
   * succeeds. It would be nice to get actual return codes, but we can't
   * because phpseclib (used for SSH connections) can't provide them.
   */
  public function exec_success($commands) {
    if (is_string($commands)) {
      $commands = array($commands);
    }
    $commands[] = "echo 'training_wheels_success'";

    $command = $this->process($commands);
    $result = $this->exec($command);

    // Get the calling function for debugging.
    $e = new Exception();
    $trace = $e->getTrace();
    $function = $trace[1]['function'];
    $file = basename($trace[1]['file']);

    $success = $result == 'training_wheels_success';
    $this->log($success, $function, $file, $command, $result);
    if (!$success) {
      throw new Exception('Unexpected exec() result, see debug log for details.');
    }
    return $success;
  }

  /**
   * Execute commands and assert that the response equals the given $value.
   */
  public function exec_eq($commands, $value = '') {
    $command = $this->process($commands);
    $result = $this->exec($command);

    // Get the calling function for debugging.
    $e = new Exception();
    $trace = $e->getTrace();
    $function = $trace[1]['function'];
    $file = basename($trace[1]['file']);

    $success = $result == $value;
    $this->log($success, $function, $file, $command, $result);
    if (!$success) {
      throw new Exception('Unexpected exec() result, see debug log for details.');
    }
    return $success;
  }

  /**
   * Execute commands and get the result.
   */
  public function exec_get($commands) {
    $command = $this->process($commands);
    $result = $this->exec($command);

    // Get the calling function for debugging.
    $e = new Exception();
    $trace = $e->getTrace();
    $function = $trace[1]['function'];
    $file = basename($trace[1]['file']);

    $this->log(TRUE, $function, $file, $command, $result);
    return $result;
  }

  /**
   * Execute commands and assert the result starts with $value.
   */
  public function exec_starts_with($commands, $value) {
    $command = $this->process($commands);
    $result = $this->exec($command);

    // Get the calling function for debugging.
    $e = new Exception();
    $trace = $e->getTrace();
    $function = $trace[1]['function'];
    $file = basename($trace[1]['file']);

    $success = substr($result, 0, strlen($value)) == $value;
    $this->log($success, $function, $file, $command, $result);
    if (!$success) {
      throw new Exception('Unexpected exec() result, see debug log for details.');
    }
    return $success;
  }

  /**
   * Log result or log + throw exception.
   */
  protected function log($success, $function, $file, $command, $result) {
    $caller = $function . ' in ' . $file;
    if ($success) {
      Log::log('>>> SUCCESS >>> ' . $caller, L_DEBUG, 'green');
      // Log::log('exec: ' . $command, L_DEBUG);
      // Log::log('outp: ' . $result, L_DEBUG);
    }
    else {
      Log::log('<<< UNEXPECTED <<< ' . $caller, L_DEBUG, 'red');
      Log::log('exec: ' . $command, L_DEBUG);
      Log::log('outp: ' . $result, L_DEBUG);
    }
  }
}
