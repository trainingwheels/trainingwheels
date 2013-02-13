<?php

namespace TrainingWheels\Plugin\ApacheLandingPage;
use TrainingWheels\Plugin\PluginBase;
use TrainingWheels\Common\Util;

class ApacheLandingPage extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/apache_landing_page.yml';
  }

  public function getProvisionConfig() {
    return array(
      'vars' => array(
        'git_repo' => NULL,
      ),
    );
  }
}
