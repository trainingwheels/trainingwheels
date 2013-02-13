<?php

namespace TrainingWheels\Plugin\MySQL;
use TrainingWheels\Plugin\PluginBase;

class MySQL extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/mysql.yml';
  }

  public function getProvisionConfig() {
    return array(
      'vars' => array(
        'mysql_root_password' => NULL,
        'mysql_max_allowed_packet' => '128M',
        'mysql_character_set_server' => 'utf8',
        'mysql_collation_server' => 'utf8_general_ci',
      ),
    );
  }

  public function mixinEnvironment($env, $type) {
    if ($type == 'linux') {
      $mySQLLinuxEnv = new MySQLLinuxEnv();
      $mySQLLinuxEnv->mixinLinuxEnv($env);
    }
  }

  public function getResourceClasses() {
    return array(
      'MySQLDatabaseResource' => '\\TrainingWheels\\Plugin\\MySQL\\MySQLDatabaseResource',
    );
  }
}
