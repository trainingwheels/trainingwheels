<?php

namespace TrainingWheels\Plugin\MySQL;

class mySQLLinuxEnv {

  public function mixinLinuxEnv($env) {
    $conn = $env->getConn();

    /**
     * Create MySQL user, database and import from dump if given.
     */
    $env->mySQLUserDBCreate = function($user, $pass, $db, $dump_path = 'none') use ($conn) {
      $commands = array(
        "echo \"CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';\" | sudo -i mysql",
        "echo \"CREATE DATABASE $db;\" | sudo -i mysql",
        "echo \"GRANT ALL PRIVILEGES on $db.* to '$user'@'localhost';\" | sudo -i mysql",
      );

      if (!empty($dump_path) && $dump_path !== 'none') {
        $commands = array_merge($commands, array(
          "test -f $dump_path",
          "zcat $dump_path | sudo -i mysql $db",
        ));
      }

      $conn->exec_success($commands);
    };

    /**
     * Delete a MySQL database.
     */
    $env->mySQLUserDBDelete = function($user, $db) use ($conn) {
      $commands = array(
        "echo \"DROP DATABASE $db;\" | sudo -i mysql",
        "echo \"DROP USER '$user'@'localhost';\" | sudo -i mysql",
      );
      $conn->exec_success($commands);
    };

    /**
     * Dump a db to a file.
     */
    $env->mySQLDumpToFile = function($db, $target_file) use ($conn) {
      $commands = array(
        "sudo -i mysqldump --result-file=$target_file $db",
        "gzip -f $target_file",
      );
      $conn->exec_success($commands);
    };

    /**
     * Does a database exist?
     */
    $env->mySQLDBExists = function($db) use ($conn) {
      $cmd = "echo \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db';\" | sudo -i mysql -s";
      $output = $conn->exec_get($cmd);
      if (!empty($output) && $output !== $db) {
        throw new Exception("MySQLDBExists command returned invalid data \"$output\".");
      }
      return $output === $db;
    };
  }
}
