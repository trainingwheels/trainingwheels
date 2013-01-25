<?php

namespace TrainingWheels\Conn;
use Exception;

set_include_path(get_include_path() . PATH_SEPARATOR . 'sites/all/libraries/phpseclib');

require_once('Net/SSH2.php');

class SSHServerConn implements ServerConn {

  // TODO: Switch to using keys.
  protected $ip;
  protected $port;
  protected $user;
  protected $pass;
  protected $ssh_conn;

  public function __construct($ip, $port, $user, $pass) {
    $this->ip = $ip;
    $this->port = $port;
    $this->user = $user;
    $this->pass = $pass;
  }

  protected function process($input) {
    if (is_string($input)) {
      return $input;
    }
    else if (is_array($input)) {
      return implode(' && ', $input);
    }
    else {
      throw new Exception("Invalid input given to SSHServerConn");
    }
  }

  public function connect() {
    $this->ssh_conn = new \Net_SSH2($this->ip, $this->port);
    return $this->ssh_conn->login($this->user, $this->pass);
  }

  protected function exec($command) {
    if ($this->ssh_conn) {
      $result = trim($this->ssh_conn->exec($command));
      return $result;
    }
    else {
      throw new Exception('SSH connection has not been established');
    }
  }
}
