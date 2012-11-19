<?php

namespace TrainingWheels\Environment;
use \TrainingWheels\Conn\ServerConn;
use \TrainingWheels\Environment\TrainingEnv;
use \TrainingWheels\Common\Util;
use Exception;

class LinuxEnv implements TrainingEnv {
  protected $conn;

  public function __construct(ServerConn $conn) {
    $this->conn = $conn;
    if (!$this->conn->exec_eq('sudo whoami', 'root')) {
      throw new Exception('The connection needs to have root or sudo access to the server.');
    }
  }

  /**
   * Append a text string to the end of a file.
   */
  public function fileAppendText($file_path, $text) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "test -f $file_path",
      "echo \"$text\" >> $file_path"
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Get the contents of a text file.
   */
  public function fileGetContents($file_path) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $out = $this->conn->exec_get("cat $file_path");
    if ($out == "cat: $file_path: No such file or directory") {
      throw new Exception("Trying to get contents of file $file_path that does not exist.");
    }
    return $out;
  }

  /**
   * Put the contents of a text file, overwriting if one exists.
   */
  public function filePutContents($file_path, $contents) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $out = $this->conn->exec_eq("echo \"$contents\" > $file_path");
    return $out;
  }

  /**
   * Use rsync to sync two folders. Purposefully abstracts a lot from rsync,
   * as otherwise this could do a lot of damage to the server.
   */
  public function fileSyncUserFolder($source_user, $target_user, $folder) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());

    $source_path = "/home/$source_user/$folder";
    $target_path = "/home/$target_user/$folder";

    if ($source_path == $target_path) {
      throw new Exception("Source and target cannot be equal: $source_path");
    }

    $commands = array(
      "test -d $source_path",
      "mkdir -p $target_path",
      "rsync -a --delete $source_path $target_path",
      "chown -R $target_user: $target_path",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Replace text in a file, using sed.
   */
  public function fileStrReplace($search, $replace, $file_path) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "test -f $file_path",
      "sed -i'' -e's/$search/$replace/' $file_path",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Create a text file.
   */
  public function fileCreate($text, $file_path, $user = NULL) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $file = basename($file_path);
    $commands = array(
      "echo $text > ~/tmp/$file",
      "cp ~/tmp/$file $file_path",
    );
    if ($user) {
      $commands[] = "chown $user: $file_path";
    }
    $this->conn->exec_success($commands);
  }

  /**
   * Delete a file.
   */
  public function fileDelete($file_path) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $this->conn->exec_eq("rm $file_path");
  }

  /**
   * Copy a file.
   */
  public function fileCopy($source, $target) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $this->conn->exec_eq("cp $source $target");
  }

  /**
   * Check if a Linux user exists in the system.
   */
  public function userExists($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $output = $this->conn->exec_get('grep "^' . $user . ':" /etc/passwd');
    return substr($output, 0, strlen($user) + 1) == $user . ':';
  }

  /**
   * Get all Linux users, just the ones with home directories.
   */
  public function usersGetAll() {
    $output = $this->conn->exec_get('ls /home');
    if (!empty($output)) {
      return explode("\n", $output);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Check if a file exists in the system.
   */
  public function fileExists($file_path) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "test -f $file_path",
    );
    return $this->conn->exec_success($commands);
  }

  /**
   * Check if a directory exists in the file system.
   */
  public function dirExists($dir_path) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "test -d $dir_path",
      "echo 'true'"
    );
    $result = $this->conn->exec_get($commands);
    if ($result == 'true') {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Delete a directory from a user's home folder.
   */
  public function dirDelete($dir_path) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    if (substr($dir_path, 0, 6) !== '/home/') {
      throw new Exception("Cannot delete a folder outside of /home");
    }
    $commands = array(
      "rm -rf $dir_path",
    );
    $result = $this->conn->exec_eq($commands);
  }

  /**
   * Recursively chmod a directory
   */
  public function dirChmod($options, $dir_path, $recurse = TRUE) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    if ($recurse) {
      $options = '-R ' . $options;
    }
    $commands = array(
      "test -d $dir_path",
      "chmod $options $dir_path",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Create a user.
   */
  public function userCreate($user, $pass) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "groupadd $user",
      "rsync -ah --delete /var/trainingwheels/skel/skel_user/ /tmp/skel_user/",
      // "sudo echo 'hello' > /tmp/filename" doesn't work if the file is owned by root, need to
      // do a 2 step process.
      "echo $pass > ~/tmp/.password",
      "cp ~/tmp/.password /tmp/skel_user/.password",
      "useradd -m -p`openssl passwd -1 $pass` -d/home/$user -k/tmp/skel_user -s/bin/bash -g$user $user",
      "chmod o-rwx /home/$user",
      "chown root: /home/$user/.password",
      "chmod 400 /home/$user/.password",
      "rm -rf /tmp/skel_user",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Delete a user.
   */
  public function userDelete($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "userdel $user",
      "groupdel $user",
      "rm -rf /home/$user",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Get a user id (Linux user id).
   */
  public function userGetId($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $id = $this->conn->exec_get("id -u $user");
    if (!is_numeric($id)) {
      throw new Exception("The user '$user' does not exist, can't get id.");
    }
    return $id;
  }

  /**
   * Add a user to a group.
   */
  public function userAddToGroup($user, $group) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $this->conn->exec_eq("gpasswd -a $user $group", "Adding user $user to group $group");
  }

  /**
   * Remove a user from a group.
   */
  public function userRemoveFromGroup($user, $group) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $this->conn->exec_eq("gpasswd -d $user $group");
  }

  /**
   * Get a user's password.
   */
  public function userPasswdGet($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    return $this->fileGetContents("/home/$user/.password");
  }

  /**
   * Is the user logged in?
   */
  public function userIsLoggedIn($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $out = $this->conn->exec_get("users");
    $logged_in = explode(' ', $out);
    return in_array($user, $logged_in);
  }

  /**
   * Create MySQL user, database and import from dump if given.
   */
  public function mySQLUserDBCreate($user, $pass, $db, $dump_path = 'none') {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "echo \"CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';\" | mysql",
      "echo \"CREATE DATABASE $db;\" | mysql",
      "echo \"GRANT ALL PRIVILEGES on $db.* to '$user'@'localhost';\" | mysql",
    );

    if (!empty($dump_path) && $dump_path !== 'none') {
      $commands = array_merge($commands, array(
        "test -f $dump_path",
        "zcat $dump_path | mysql $db",
      ));
    }
    $this->conn->exec_success($commands);
  }

  /**
   * Delete a MySQL database.
   */
  public function mySQLUserDBDelete($user, $db) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "echo \"DROP DATABASE $db;\" | mysql",
      "echo \"DROP USER '$user'@'localhost';\" | mysql",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Dump a db to a file.
   */
  public function mySQLDumpToFile($db, $target_file) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $commands = array(
      "mysqldump --result-file=$target_file $db",
      "gzip -f $target_file",
    );
    $this->conn->exec_success($commands);
  }

  /**
   * Does a database exist?
   */
  public function mySQLDBExists($db) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $cmd = "echo \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db';\" | mysql -s";
    $output = $this->conn->exec_get($cmd);
    return $output === $db;
  }

  /**
   * Restart a service.
   */
  protected function serviceRestart($service) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    if ($service == 'apache2') {
      $expect = "* Restarting web server apache2";
    }
    $this->conn->exec_starts_with("service $service restart", $expect);
  }

  /**
   * Clone the repo for a user.
   */
  public function gitRepoClone($user, $repo, $target, $branch) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $this->conn->exec_eq("git clone -q --branch $branch $repo $target");
    $this->conn->exec_eq("chown -R $user: $target");
  }

  /**
   * Get the current branch of a git repo.
   */
  public function gitBranchGet($dir) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $git_path_opts = "--work-tree=$dir --git-dir=$dir/.git";
    return $this->conn->exec_get("git $git_path_opts rev-parse --abbrev-ref HEAD");
  }

  /**
   * Check if there are local changes.
   */
  public function gitLocalChanges($dir) {
    $git_path_opts = "--work-tree=$dir --git-dir=$dir/.git";
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    return $this->conn->exec_get("git $git_path_opts status -s");
  }

  /**
   * Check what the remote is.
   */
  public function gitRemote($dir) {
    $git_path_opts = "--work-tree=$dir --git-dir=$dir/.git";
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    return $this->conn->exec_get("git $git_path_opts remote -v");
  }
}
