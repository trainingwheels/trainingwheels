<?php

namespace TrainingWheels\Plugin\Nodejs;
use TrainingWheels\Plugin\PluginBase;

class Nodejs extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->name = 'Nodejs';
    $this->ansible_play = __DIR__ . '/ansible/nodejs.yml';
  }
}
