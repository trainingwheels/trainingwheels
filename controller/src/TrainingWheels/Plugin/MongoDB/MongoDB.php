<?php

namespace TrainingWheels\Plugin\MongoDB;
use TrainingWheels\Plugin\PluginBase;

class MongoDB extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/mongodb.yml';
  }
}
