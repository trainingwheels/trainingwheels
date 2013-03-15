<?php

namespace TrainingWheels\Plugin\Cloud9IDE;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Plugin\Supervisor\SupervisorProcessResource;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Store\DataStore;
use Exception;

class Cloud9IDEResource extends SupervisorProcessResource {

  /**
   * Constructor.
   */
  public function __construct(Environment $env, DataStore $data, $title, $user_name, $course_name, $res_id, $config) {
    parent::__construct($env, $data, $title, $user_name, $course_name, $res_id, $config);
    $this->cacheBuild($res_id);
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = parent::get();
    if ($info['exists']) {
      $host = $this->env->getConn()->getHost();
      $host = $host == 'localhost' ? $_SERVER['SERVER_NAME'] : $host;
      $info['attribs'][0]['key'] = 'address';
      $info['attribs'][0]['title'] = 'Address';
      $info['attribs'][0]['value'] = 'http://' . $this->user_name . '.' . $this->course_name . '.' . $host . ':' . $this->getPort();
    }
    return $info;
  }

  /**
   * Start the process.
   */
  public function create() {
    if ($this->getExists()) {
      throw new Exception("Attempting to create a Cloud9IDEResource that is already running.");
    }
    $name = $this->user_name;
    $pass = $this->env->userPasswdGet($this->user_name);
    $port = $this->getPort();
    $dir_path = "/twhome/$name";

    $this->command = "/usr/bin/node server.js --username $name --password $pass -w $dir_path -l 0.0.0.0 -p $port -a x-www-browser";
    $this->directory = '/var/local/cloud9/cloud9-build';

    parent::create();
  }

  /**
   * Port numbering scheme based on user id.
   */
  public function getPort() {
    return '3' . $this->env->userGetId($this->user_name);
  }
}
