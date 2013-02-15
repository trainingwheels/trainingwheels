<?php

namespace TrainingWheels\Plugin\ApacheHTTPD;
use TrainingWheels\Plugin\PluginBase;
use TrainingWheels\Common\Util;

class ApacheHTTPD extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/apachehttpd.yml';
  }

  public function getProvisionConfig() {
    return array(
      'vars' => array(
        'apache_virtual_docroot' => array(
          'val' => '/twhome/%-4/%-3',
          'help' => "Apache VirtualDocument root, note that the -4 and -3 correspond to the index of segments in the server host name. So '/twhome/%-4/%-3' results in '/twhome/mark/course' when visiting 'mark.course.training.wheels'.",
        ),
        'apache_directory' => array(
          'val' => '/twhome/*/*',
          'help' => 'Apache VirtualDocument target, this is related to the apache_virtual_docroot setting.',
        ),
        'landing_repo_url' => array(
          'help' => 'A repository containing HTML that is served as the default Apache site and on 404 error when mistyping a VirtualDocument root',
          'val' => NULL,
          'hint' => 'https://github.com/trainingwheels/sample-landing-page.git',
        ),
        'landing_repo_branch' => array(
          'help' => 'The branch to checkout when cloning the landing page repository.',
          'val' => 'gh-pages',
        ),
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
    /**
     * After users are added, restart Apache.
     */
    $course->addObserver('afterUsersCreate', function($data) {
      $data['course']->env->apacheHTTPDRestart();
    });

    /**
     * Add user to web group after creation.
     */
    $course->addObserver('afterOneUserCreate', function($data) {
      $data['course']->env->userAddToWebGroup($data['user']->getName());
    });
  }
}
