<?php

namespace TrainingWheels\Plugin\Drupal;
use TrainingWheels\Plugin\PluginBase;

class Drupal extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/drupal.yml';
  }
}
