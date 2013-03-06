<?php

namespace TrainingWheels\Plugin\Nodejs;
use TrainingWheels\Plugin\PluginBase;

class Nodejs extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/nodejs.yml';
  }
}
