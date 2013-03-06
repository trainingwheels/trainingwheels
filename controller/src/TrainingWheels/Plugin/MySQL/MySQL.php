<?php

namespace TrainingWheels\Plugin\MySQL;
use TrainingWheels\Plugin\PluginBase;

class MySQL extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/mysql.yml';
  }

  public function getPluginVars() {
    return array(
      'mysql_root_password' => array(
        'val' => NULL,
      ),
      'mysql_max_allowed_packet' => array(
        'val' => '128M',
      ),
      'mysql_character_set_server' => array(
        'val' => 'utf8',
      ),
      'mysql_collation_server' => array(
        'val' => 'utf8_general_ci',
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
