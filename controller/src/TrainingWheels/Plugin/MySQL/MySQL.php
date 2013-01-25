<?php

namespace TrainingWheels\Plugin\MySQL;
use TrainingWheels\Plugin\PluginBase;

class MySQL extends PluginBase {
  public function getConfig() {
    return array(
      'name' => 'MySQL',
      'location' => __DIR__,
      'playbook' => __DIR__ . '/ansible/mysql.yml',
    );
  }
}
