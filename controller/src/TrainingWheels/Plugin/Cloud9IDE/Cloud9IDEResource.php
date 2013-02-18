<?php

namespace TrainingWheels\Plugin\Cloud9IDE;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Plugin\Supervisor\SupervisorProcessResource;
use TrainingWheels\Environment\Environment;
use Exception;

class Cloud9IDEResource extends SupervisorProcessResource {

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $user_name, $course_name, $res_id, $data) {
    parent::__construct($env, $title, $user_name, $course_name, $res_id, $data);
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = parent::get();
    if ($info['exists']) {
      $info['attribs'][0]['key'] = 'address';
      $info['attribs'][0]['title'] = 'Address';
      $info['attribs'][0]['value'] = 'http://' . $this->user_name . '.' . $this->course_name . '.' . $_SERVER['SERVER_NAME'] . ':' . $this->getPort();
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
    $this->directory = '/var/local/cloud9';

    parent::create();
  }

  /**
   * Port numbering scheme based on user id.
   */
  public function getPort() {
    return '3' . $this->env->userGetId($this->user_name);
  }
}
