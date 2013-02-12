<?php

namespace TrainingWheels\Plugin\MySQL;
use TrainingWheels\Plugin\PluginBase;

class MySQL extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/mysql.yml';
  }

  public function getAnsibleConfig() {
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
      $mySQLLinuxEnv = new mySQLLinuxEnv();
      $mySQLLinuxEnv->mixinLinuxEnv($env);
    }
  }

  public function getResourceClass() {
    return '\\TrainingWheels\\Plugin\\MySQL\\MySQLDatabaseResource';
  }
}
