<?php

namespace TrainingWheels\Plugin\Core;
use TrainingWheels\Plugin\PluginBase;

class Core extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/core.yml';
  }

  public function getProvisionConfig() {
    return array(
      'vars' => array(
        'twskel' => '/etc/trainingwheels/skel/skel_user',
      ),
    );
  }

  public function mixinEnvironment($env, $type) {
    $coreLinuxEnv = new CoreLinuxEnv();
    if ($type == 'linux') {
      $coreLinuxEnv->mixinLinuxEnv($env);
    }
    if ($type == 'ubuntu') {
      $coreLinuxEnv->mixinUbuntuEnv($env);
    }
  }
}
