<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use Exception;
use Net_SSH2;
use Crypt_RSA;

class SSHServerConn extends ServerConn {

  protected $host;
  protected $port;
  protected $user;
  protected $key_path;
  protected $ssh_conn;

  public function getKeyPath() {
    if ($this->key_path) {
      return $this->key_path;
    }
    else {
      return getenv('HOME') . '/.ssh/id_rsa';
    }
  }

  public function getUser() {
    return $this->user;
  }

  protected function getKeyContents() {
    $key_data = file_get_contents($this->getKeyPath());
    if (empty($key_data)) {
      throw new Exception('Cannot find a private ssh key to connect to remote server with');
    }
    return $key_data;
  }

  public function __construct($host, $port, $user, $key_path = NULL) {
    $this->host = $host;
    $this->port = $port;
    $this->user = $user;
    $this->key_path = $key_path;
  }

  public function connect() {
    $this->ssh_conn = new Net_SSH2($this->host, $this->port);
    $key = new Crypt_RSA();
    $key->loadKey($this->getKeyContents());
    return $this->ssh_conn->login($this->user, $key);
  }

  protected function exec($command) {
    if ($this->ssh_conn) {
      Log::log('SSHServerConn::exec: ' . "\n" . $command, L_DEBUG);
      $result = trim($this->ssh_conn->exec($command));
      Log::log('SSHServerConn::resp: ' . $result, L_DEBUG);
      Log::log('=====================================================================', L_DEBUG);
      return $result;
    }
    else {
      throw new Exception('SSH connection has not been established');
    }
  }
}
