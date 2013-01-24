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
      // nginx, or the console. Therefore, we need to add sudo before each call.
      // I've played with many different strategies for chaining together commands
      // like this (for performance purposes), and this, despite it's limitations,
      // is still the fastest and works as long as you don't use pipe or redirections
      // incorrectly. We specifically cannot use sudo if we're doing a mktemp command,
      // as it does a variable assignment like TW_TMP=`mktemp` which fails through sudo.
      // TODO: We should have an API for creating temporary files and directories.
      if (substr($command, 0, 5) != 'sudo ' && strpos($command, '`mktemp') === FALSE) {
        $commands[$key] = 'sudo ' . $command . ' 2>&1';
      }
    }

    return implode(' && ' . "\n", $commands);
  }

  protected function exec($command) {
    // It would be awesome to use the actual return codes, but the SSH server
    // connection plugin doesn't give us those codes, so for compatibility
    // with that plugin, we don't either.
    Log::log('LocalServerConn::exec: ' . $command, L_DEBUG);
    $result = trim(shell_exec($command));
    Log::log('LocalServerConn::resp: ' . $result, L_DEBUG);
    Log::log('=====================================================================', L_DEBUG);

    if (substr($result, 0, 20) == 'sudo: no tty present') {
      throw new Exception("The current user does not have sudo permission. Check server config or run from console as root");
    }

    return $result;
  }
}
