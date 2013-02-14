<?php

namespace TrainingWheels\Conn;
use TrainingWheels\Log\Log;
use DateTime;
use Exception;

class KeyGenerator {
  private $keydir;

  public function __construct($base_path) {
    $this->keydir = $base_path . '/keypairs';
  }

  public function createKey() {
    // Create directories in case they don't exist already.
    shell_exec("sudo mkdir -p $this->keydir");
    shell_exec("sudo mkdir -p $this->keydir/backup");

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

    $pub_key = file_get_contents($this->keydir . '/tw.key.pub');
    return $pub_key;
  }
}
