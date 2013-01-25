<?php

namespace TrainingWheels\Plugin\VSFTPd;
use TrainingWheels\Plugin\PluginBase;

class VSFTPd extends PluginBase {
  public function getConfig() {
    return array(
      'name' => 'VSFTPd',
      'location' => __DIR__,
    );
  }
}
