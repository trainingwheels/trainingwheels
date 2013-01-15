<?php

namespace TrainingWheels\Common;
use TrainingWheels\Conn\LocalServerConn;
use TrainingWheels\Conn\SSHServerConn;
use TrainingWheels\Environment\DevEnv;
use TrainingWheels\Environment\CentosEnv;
use TrainingWheels\Environment\UbuntuEnv;
use Exception;

abstract class Factory {
  // Abstract functions.
  abstract public function get($id);
  abstract public function save($object);

  /**
   * Additionally each implementing class should contain
   * a protected static $instance variable and a public
   * static singleton function to return a static instance
   * of the class.
   */

  protected static $data;

  /**
   * Environment buider.
   */
  protected function buildEnv(&$object, $type, $host, $user, $pass) {
    switch ($type) {
      case 'ubuntu':
        if ($host == 'localhost') {
          $conn = new LocalServerConn(TRUE);
        }
        else {
          $conn = new SSHServerConn($host, 22, $user, $pass, TRUE);
          if (!$conn->connect()) {
            throw new Exception("Unable to connect/login to server $host on port 22");
          }
        }
        $object->env = new UbuntuEnv($conn);
        $object->env_type = 'ubuntu';
      break;

      case 'ubuntu-local':
        $conn = new LocalServerConn(TRUE);
        $object->env = new UbuntuEnv($conn);
        $object->env_type = 'ubuntu';
      break;

      case 'centos':
        $conn = new SSHServerConn($host, 22, $user, $pass, TRUE);
        if (!$conn->connect()) {
          throw new Exception("Unable to connect/login to server $host on port 22");
        }
        $object->env = new CentosEnv($conn);
        $object->env_type = 'centos';
      break;

      case 'dev':
        $conn = new LocalServerConn(TRUE);
        $base_path = '/root/tw';
        $object->env = new DevEnv($conn, $base_path);
        $object->env_type = 'dev';
      break;

      default:
        throw new Exception("Environment type $type not found.");
      break;
    }
  }
}
