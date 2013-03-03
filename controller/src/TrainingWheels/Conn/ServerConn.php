<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use Exception;

abstract class ServerConn {

  // For logging purposes.
  private $pretty_commands;

  /**
   * Process the commands into a single string.
   */
  protected function process($input) {
    $this->pretty_commands = array();

    if (is_string($input)) {
      $commands = array($input);
    }
    else if (is_array($input)) {
      $commands = $input;
    }
    else {
      throw new Exception("Invalid input to function LocalServerConn::process(), expecting string or array.");
    }

    foreach ($commands as $key => $command) {
      // Pretty printing without the redundant info.
      $this->pretty_commands[] = $command;

      // When we run locally, we're running as either the phpfpm user through
      // nginx, or the console. Therefore, we need to add sudo before each call.
      // I've played with many different strategies for chaining together commands
      // like this (for performance purposes), and this, despite it's limitations,
      // is still the fastest and works as long as you don't use pipe or redirections
      // incorrectly. We specifically cannot use sudo if we're doing a mktemp command,
      // as it does a variable assignment like TW_TMP=`mktemp` which fails through sudo.
      if (substr($command, 0, 5) != 'sudo ' && strpos($command, '`mktemp') === FALSE) {
        $commands[$key] = 'sudo ' . $command . ' 2>&1';
      }
    }

    return implode(' && ', $commands);
  }

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
    $message = 'Execute and success';
    $result = $this->do_exec($command, $message);
    $success = $result == 'training_wheels_success';

    if (!$success) {
      throw new Exception('exec_success: One of the commands failed.');
    }
    return $success;
  }

  /**
   * Execute commands and assert that the response equals the given $value.
   */
  public function exec_eq($commands, $value = '') {
    $command = $this->process($commands);
    $result = $this->do_exec($command, 'Execute and expect', $value);
    $success = $result == $value;

    if (!$success) {
      throw new Exception("exec_eq: The output of the commands failed to return the correct value '$value'.");
    }
    return $success;
  }

  /**
   * Execute commands and get the result.
   */
  public function exec_get($commands) {
    $command = $this->process($commands);
    $result = $this->do_exec($command, 'Execute and get');
    return $result;
  }

  /**
   * Execute commands and assert the result starts with $value.
   */
  public function exec_starts_with($commands, $value) {
    $command = $this->process($commands);
    $result = $this->do_exec($command, 'Execute starts with', $value);
    $success = substr($result, 0, strlen($value)) == $value;

    if (!$success) {
      throw new Exception("exec_starts_with: The output of the commands did not start with the value '$value'.");
    }
    return $success;
  }

  /**
   * Execute and get the amount of time it took, plus perform logging.
   */
  protected function do_exec($command, $message, $value = '') {
    $start_time = microtime(TRUE);
    $result = $this->exec($command);
    $end_time = microtime(TRUE);
    $time = $end_time - $start_time;

    $context = array(
      'layer' => 'env',
      'source' => 'ServerConn',
      'commands' => $this->pretty_commands,
      'result' => $result,
      'time' => $time,
      'params' => $value,
    );
    Log::log($message, L_DEBUG, 'actions', $context);

    $time = $end_time - $start_time;
    return $result;
  }
}
