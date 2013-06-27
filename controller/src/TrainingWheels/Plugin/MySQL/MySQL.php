<?php

namespace TrainingWheels\Plugin\MySQL;
use TrainingWheels\Plugin\PluginBase;

class MySQL extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/mysql.yml';
  }

  public function getPluginVars() {
    return array(
      array(
        'key' => 'mysql_root_password',
        'default' => NULL,
      ),
      array(
        'key' => 'mysql_max_allowed_packet',
        'default' => '128M',
      ),
      array(
        'key' => 'mysql_character_set_server',
        'default' => 'utf8',
      ),
      array(
        'key' => 'mysql_collation_server',
        'default' => 'utf8_general_ci',
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
