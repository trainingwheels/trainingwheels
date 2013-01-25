<?php

namespace TrainingWheels\Plugin\Core;
use TrainingWheels\Plugin\PluginBase;

class Core extends PluginBase {
  public function getConfig() {
    return array(
      'name' => 'Core',
      'location' => __DIR__,
    );
  }
}
