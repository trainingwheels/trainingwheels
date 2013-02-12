<?php

namespace TrainingWheels\Plugin\ApacheHTTPD;
use TrainingWheels\Plugin\PluginBase;
use TrainingWheels\Common\Util;

class ApacheHTTPD extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/apachehttpd.yml';
  }

  public function getAnsibleConfig() {
    return array(
      'vars' => array(
        # apache virtual document roots, these two are closely related.
        # Note that the -4 and -3 correspond to the number of segments
        # in the domain name you choose above. So /twhome/%-4/%-3 results
        # in /twhome/mark/course when visiting mark.course.training.wheels.
        # You may have to change these if you use a longer or shorter base
        # domain.
        'apache_virtual_docroot' => '/twhome/%-4/%-3',
        'apache_directory' => '/twhome/*/*',
      ),
    );
  }

  public function mixinEnvironment($env, $type) {
    $apacheLinuxEnv = new ApacheHTTPDLinuxEnv();
    if ($type == 'centos') {
      $apacheLinuxEnv->mixinCentosEnv($env);
    }
    if ($type == 'ubuntu') {
      $apacheLinuxEnv->mixinUbuntuEnv($env);
    }
  }

  public function registerCourseObservers($course) {
    // After users are added, restart Apache.
    $course->addObserver('afterUsersCreate', function($data) {
      $data['course']->env->apacheHTTPDRestart();
    });
  }
}
