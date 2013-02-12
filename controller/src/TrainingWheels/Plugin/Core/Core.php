<?php

namespace TrainingWheels\Plugin\Core;
use TrainingWheels\Plugin\PluginBase;

class Core extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/core.yml';
  }

  public function getAnsibleConfig() {
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
