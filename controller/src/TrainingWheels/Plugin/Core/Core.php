<?php

namespace TrainingWheels\Plugin\Core;
use TrainingWheels\Plugin\PluginBase;

class Core extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/core.yml';
  }

  public function getPluginVars() {
    return array(
      array(
        'key' => 'twskel',
        'val' => '/etc/trainingwheels/skel/skel_user',
      ),
    );
  }

  public function mixinEnvironment($env, $type) {
    $coreLinuxEnv = new CoreLinuxEnv();
    if ($type == 'linux') {
      $coreLinuxEnv->mixinLinuxEnv($env);
    }
  }
}
