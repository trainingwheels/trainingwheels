<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use TrainingWheels\Conn\KeyManager;
use Exception;
use Net_SSH2;
use Crypt_RSA;

class SSHServerConn extends ServerConn {

  protected $host;
  protected $port;
  protected $user;
  protected $key_manager;
  protected $ssh_conn;

  /**
   * This is called by the Environment provisioner. We wrap the key manager so
   * that the Environment doesn't have to create it's own one or have knowledge of
   * it at all.
   */
  public function getKeyPath() {
    return $this->key_manager->getPrivateKeyPath();
  }

  public function getUser() {
    return $this->user;
  }

  public function getHost() {
    return $this->host;
  }

  public function __construct($host, $port, $user, KeyManager $key_manager) {
    $this->host = $host;
    $this->port = $port;
    $this->user = $user;
    $this->key_manager = $key_manager;
  }

  public function connect() {
    $this->ssh_conn = new Net_SSH2($this->host, $this->port);
    $key = new Crypt_RSA();
    $key->loadKey($this->key_manager->getPrivateKeyContents());
    return $this->ssh_conn->login($this->user, $key);
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
