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
    $success = $result == 'training_wheels_success';
    if (!$success) {
      throw new Exception('exec_success: One of the commands failed with an exit code of 0.');
    }
    return $success;
  }

  /**
   * Execute commands and assert that the response equals the given $value.
   */
  public function exec_eq($commands, $value = '') {
    $command = $this->process($commands);
    $result = $this->exec($command);
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
    $result = $this->exec($command);
    return $result;
  }

  /**
   * Execute commands and assert the result starts with $value.
   */
  public function exec_starts_with($commands, $value) {
    $command = $this->process($commands);
    $result = $this->exec($command);
    $success = substr($result, 0, strlen($value)) == $value;
    if (!$success) {
      throw new Exception("exec_starts_with: The output of the commands did not start with the value '$value'.");
    }
    return $success;
  }
}
