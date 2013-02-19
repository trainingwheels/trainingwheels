<?php

namespace TrainingWheels\Plugin\Cloud9IDE;
use TrainingWheels\Plugin\PluginBase;

class Cloud9IDE extends PluginBase {

  const name = 'IDE';

  public function getProvisionSteps() {
    return __DIR__ . '/provision/cloud9ide.yml';
  }

  public function getPluginVars() {
    return array(
      'path' => array(
        'val' => '/var/local/cloud9',
      ),
      'version' => array(
        'val' => 'hide-password',
      ),
      'session_type' => array(
        'val' => 'connect.session.memory'
      )
    );
  }

  public function getResourceClasses() {
    return array(
      'Cloud9IDEResource' => '\\TrainingWheels\\Plugin\\Cloud9IDE\\Cloud9IDEResource',
    );
  }
}
