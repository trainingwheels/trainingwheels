<?php

namespace TrainingWheels\Plugin\MySQL;

class mySQLLinuxEnv {

  public function mixinLinuxEnv($env) {
    $conn = $env->getConn();

    /**
     * Create MySQL user, database and import from dump if given.
     */
    $env->mySQLUserDBCreate = function($user, $pass, $db, $dump_path = 'none') use ($conn) {
      // Because of the way the 'sudo ' part is added to each command, we can't do
      // 'zcat db | mysql' directly as mysql will then try read credentials from whatever the
      // current user's account it, and fail. Instead grab the credentials from root and save
      // them temporarily. This is faster than the alternative which is to not use zcat and
      // instead copy/move the database dump before unzipping it.
      $commands = array(
        "TW_MYSQL_CREDS=`mktemp`",
        "cat /root/.my.cnf > \$TW_MYSQL_CREDS",
        "mysql --defaults-extra-file=\$TW_MYSQL_CREDS -e \"CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';\"",
        "mysql --defaults-extra-file=\$TW_MYSQL_CREDS -e \"CREATE DATABASE $db;\"",
        "mysql --defaults-extra-file=\$TW_MYSQL_CREDS -e \"GRANT ALL PRIVILEGES on $db.* to '$user'@'localhost';\"",
      );

      if (!empty($dump_path) && $dump_path !== 'none') {
        $commands = array_merge($commands, array(
          "test -f $dump_path",
          "zcat $dump_path | mysql --defaults-extra-file=\$TW_MYSQL_CREDS $db",
        ));
      }

      $commands = array_merge($commands, array(
        "rm \$TW_MYSQL_CREDS",
      ));

      $conn->exec_success($commands);
    };

    /**
     * Delete a MySQL database.
     */
    $env->mySQLUserDBDelete = function($user, $db) use ($conn) {
      $commands = array(
        "sudo -i mysql -e \"DROP DATABASE $db;\"",
        "sudo -i mysql -e \"DROP USER '$user'@'localhost';\"",
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
      $cmd = "sudo -i mysql -s -e \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db';\"";
      $output = $conn->exec_get($cmd);
      if (!empty($output) && $output !== $db) {
        throw new Exception("MySQLDBExists command returned invalid data \"$output\".");
      }
      return $output === $db;
    };
  }
}
