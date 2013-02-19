<?php

namespace TrainingWheels\Plugin\GitFiles;
use TrainingWheels\Plugin\PluginBase;

class GitFiles extends PluginBase {

  const name = 'Git';

  public function getProvisionSteps() {
    return __DIR__ . '/provision/gitfiles.yml';
  }

  public function mixinEnvironment($env, $type) {
    if ($type == 'linux') {
      $gitFilesLinuxEnv = new GitFilesLinuxEnv();
      $gitFilesLinuxEnv->mixinLinuxEnv($env);
    }
  }

  public function getResourceClasses() {
    return array(
      'GitFilesResource' => '\\TrainingWheels\\Plugin\\GitFiles\\GitFilesResource',
    );
  }
}
