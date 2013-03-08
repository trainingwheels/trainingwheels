<?php

namespace TrainingWheels\Plugin\Supervisor;
use TrainingWheels\Plugin\PluginBase;

class Supervisor extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/supervisor.yml';
  }

  public function mixinEnvironment($env, $type) {
    if ($type == 'linux') {
      $supervisorLinuxEnv = new SupervisorLinuxEnv();
      $supervisorLinuxEnv->mixinLinuxEnv($env);
    }
  }
}
