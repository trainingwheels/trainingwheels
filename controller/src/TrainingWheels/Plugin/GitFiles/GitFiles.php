<?php

namespace TrainingWheels\Plugin\GitFiles;
use TrainingWheels\Plugin\PluginBase;

class GitFiles extends PluginBase {
  public function getConfig() {
    return array(
      'name' => 'GitFiles',
      'location' => __DIR__,
      //'playbook' => __DIR__ . '/ansible/gitfiles.yml',
    );
  }
}
