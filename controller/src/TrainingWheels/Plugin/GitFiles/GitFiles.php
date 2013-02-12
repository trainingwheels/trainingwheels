<?php

namespace TrainingWheels\Plugin\GitFiles;
use TrainingWheels\Plugin\PluginBase;

class GitFiles extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/gitfiles.yml';
  }

  public function mixinEnvironment($env, $type) {
    if ($type == 'linux') {
      $gitFilesLinuxEnv = new GitFilesLinuxEnv();
      $gitFilesLinuxEnv->mixinLinuxEnv($env);
    }
  }

  public function getResourceClass() {
    return '\\TrainingWheels\\Plugin\\GitFiles\\GitFilesResource';
  }
}
