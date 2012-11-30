<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use Exception;

class LocalServerConn extends ServerConn {

  protected function process($input) {
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
      // When we run locally, we're running as either the phpfpm user through
      // nginx, or the console. Therefore, we need to add sudo before each call and
      // check the results specifically for the 'you're not allowed' message.
      // In the event we get this error, permission for this user to execute
      // the command as root needs to be added to sudoers or /etc/sudoers.d/
      if (substr($command, 0, 5) != 'sudo ') {
        $commands[$key] = 'sudo ' . $command;
      }
    }

    return implode(' && ', $commands);
  }

  protected function exec($command) {
    // It would be awesome to use the actual return codes, but the SSH server
    // connection plugin doesn't give us those codes, so for compatibility
    // with that plugin, we don't either.
    Log::log('LocalServerConn::exec: ' . $command, L_DEBUG);
    $result = trim(shell_exec($command . ' 2>&1'));
    Log::log('LocalServerConn::resp: ' . $result, L_DEBUG);

    if (substr($result, 0, 20) == 'sudo: no tty present') {
      throw new Exception("The current user does not have sudo permission to execute " . $command);
    }

    return $result;
  }
}
