<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use DateTime;
use Exception;

class KeyManager {
  private $keydir;

  public function __construct($base_path) {
    $this->keydir = $base_path . '/keypairs';
  }

  public function getPrivateKeyPath() {
    return $this->keydir . '/tw.key';
  }

  public function getPrivateKeyContents() {
    if (!is_file($this->getPrivateKeyPath())) {
      throw new Exception("The private key was not found, are you sure the keypair has been generated?");
    }
    return file_get_contents($this->getPrivateKeyPath());
  }

  public function getPublicKeyContents() {
    return file_get_contents($this->keydir . '/tw.key.pub');
  }

  public function createKey() {
    // Create directories in case they don't exist already.
    shell_exec("sudo mkdir -p $this->keydir");
    shell_exec("sudo mkdir -p $this->keydir/backup");
    $processUser = posix_getpwuid(posix_geteuid());

    // We can't change ownership of vagrant shared folders, so ignore errors from this.
    shell_exec("sudo chown -R " . $processUser['name'] . ": $this->keydir 2>&1");

    // Backup old keys.
    $stamp = new DateTime();
    $stamp = $stamp->getTimeStamp();
    shell_exec("test -f $this->keydir/tw.key && mv $this->keydir/tw.key $this->keydir/backup/tw.key.$stamp");
    shell_exec("test -f $this->keydir/tw.key.pub && mv $this->keydir/tw.key.pub $this->keydir/backup/tw.key.pub.$stamp");

    $args_array = array(
      '-q',
      '-t rsa',
      '-f ' . $this->keydir . '/tw.key',
      "-N ''", // No passphrase.
      "-C 'training-wheels-controller'",
    );
    shell_exec('ssh-keygen ' . implode(' ', $args_array));

    return $this->getPublicKeyContents();
  }
}
