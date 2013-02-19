<?php

namespace TrainingWheels\Plugin\MongoDB;
use TrainingWheels\Plugin\PluginBase;

class MongoDB extends PluginBase {

  const name = 'MongoDB';

  public function getProvisionSteps() {
    return __DIR__ . '/provision/mongodb.yml';
  }
}
