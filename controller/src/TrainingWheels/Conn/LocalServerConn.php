<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use Exception;

class LocalServerConn extends ServerConn {

  protected function exec($command) {
    // It would be awesome to use the actual return codes, but the SSH server
    // connection plugin doesn't give us those codes, so for compatibility
    // with that plugin, we don't either.
    $result = trim(shell_exec($command));
    if (substr($result, 0, 20) == 'sudo: no tty present') {
      throw new Exception("The current user does not have sudo permission. Check server config or run from console as root");
    }
    return $result;
  }

  public function getHost() {
    return 'localhost';
  }
}
