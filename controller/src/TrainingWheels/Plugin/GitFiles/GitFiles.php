<?php

namespace TrainingWheels\Plugin\GitFiles;
use TrainingWheels\Plugin\PluginBase;

class GitFiles extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->name = 'GitFiles';
  }
}
