<?php

namespace TrainingWheels\Plugin\Core;
use TrainingWheels\Plugin\PluginBase;

class Core extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->name = 'Core';
    $this->ansible_play = __DIR__ . '/ansible/core.yml';
  }

  public function getAnsibleConfig() {
    return array(
      'vars' => array(
        'twskel' => '/etc/trainingwheels/skel/skel_user',
      ),
    );
  }
}
