<?php

namespace TrainingWheels\Plugin\MongoDB;
use TrainingWheels\Plugin\PluginBase;

class MongoDB extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/mongodb.yml';
  }
}
