<?php

namespace TrainingWheels\Plugin\Core;
use Exception;

class CoreLinuxEnv {

  public function mixinLinuxEnv($env) {
    $conn = $env->getConn();

    /**
     * Restart a service.
     */
    $env->serviceRestart = function($service) use ($conn) {
      $conn->exec_get("service $service restart");
    };

    /**
     * Check if a Linux user exists in the system.
     */
    $env->userExists = function($user) use ($conn) {
      $output = $conn->exec_get('grep "^' . $user . ':" /etc/passwd');
      return substr($output, 0, strlen($user) + 1) == $user . ':';
    };

    /**
     * Append a text string to the end of a file.
     */
    $env->fileAppendText = function($file_path, $text) use ($conn) {
      $commands = array(
        "test -f $file_path",
        // @TODO: Make the shell escaping consistent across all calls that take file content.
        "echo \"$text\" | sudo tee -a $file_path > /dev/null",
      );
      $conn->exec_success($commands);
    };

    /**
     * Get the contents of a text file.
     */
    $env->fileGetContents = function($file_path) use ($conn) {
      $out = $conn->exec_get("cat $file_path");
      if ($out == "cat: $file_path: No such file or directory") {
        throw new Exception("Trying to get contents of file $file_path that does not exist.");
      }
      return $out;
    };

    /**
     * Use rsync to sync two folders. Purposefully abstracts a lot from rsync,
     * as otherwise this could do a lot of damage to the server.
     */
    $env->fileSyncUserFolder = function($source_user, $target_user, $folder) use ($conn) {
      $source_path = "/twhome/$source_user/$folder";
      $target_path = "/twhome/$target_user/$folder";

      if ($source_path == $target_path) {
        throw new Exception("Source and target cannot be equal: $source_path");
      }

      $commands = array(
        "test -d $source_path",
        "mkdir -p $target_path",
        "rsync -a --delete $source_path $target_path",
        "chown -R $target_user: $target_path",
      );
      $conn->exec_success($commands);
    };

    /**
     * Replace text in a file, using sed.
     */
    $env->fileStrReplace = function($search, $replace, $file_path) use ($conn) {
      $commands = array(
        "test -f $file_path",
        "sed -i'' -e's/$search/$replace/' $file_path",
      );
      $conn->exec_success($commands);
    };

    /**
     * Create a text file.
     */
    $env->fileCreate = function($text, $file_path, $user = NULL) use ($conn) {
      $commands = array(
        // @TODO: Make the shell escaping consistent across all calls that take text files.
        "echo $text | sudo tee $file_path > /dev/null",
      );
      if ($user) {
        $commands[] = "chown $user: $file_path";
      }
      $conn->exec_success($commands);
    };

    /**
     * Delete a file.
     */
    $env->fileDelete = function($file_path) use ($conn) {
      $conn->exec_eq("rm $file_path");
    };

    /**
     * Copy a file.
     */
    $env->fileCopy = function($source, $target) use ($conn) {
      $conn->exec_eq("cp $source $target");
    };

    /**
     * Check if a file exists in the system.
     */
    $env->fileExists = function($file_path) use ($conn) {
      $commands = array(
        "test -f $file_path",
      );
      return $conn->exec_success($commands);
    };

    /**
     * Check if a directory exists in the file system.
     */
    $env->dirExists = function($dir_path) use ($conn) {
      $commands = array(
        "test -d $dir_path",
        "echo 'true'"
      );
      $result = $conn->exec_get($commands);
      if ($result == 'true') {
        return TRUE;
      }
      return FALSE;
    };

    /**
     * Delete a directory from a user's home folder.
     */
    $env->dirDelete = function($dir_path) use ($conn) {
      if (substr($dir_path, 0, 8) !== '/twhome/') {
        throw new Exception("Cannot delete a folder outside of /twhome, attempting to delete $dir_path");
      }
      $commands = array(
        "rm -rf $dir_path",
      );
      $result = $conn->exec_eq($commands);
    };

    /**
     * Recursively chmod a directory
     */
    $env->dirChmod = function($options, $dir_path, $recurse = TRUE) use ($conn) {
      if ($recurse) {
        $options = '-R ' . $options;
      }
      $commands = array(
        "test -d $dir_path",
        "chmod $options $dir_path",
      );
      $conn->exec_success($commands);
    };

    /**
     * Get all Linux users, just the ones with home directories.
     */
    $env->usersGetAll = function() use ($conn) {
      $output = $conn->exec_get('ls /twhome');
      if (!empty($output)) {
        return explode("\n", $output);
      }
      else {
        return FALSE;
      }
    };

    /**
     * Create a user.
     */
    $env->userCreate = function($user, $pass) use ($conn) {
      $commands = array(
        "TW_SKEL_TMP=`mktemp -d`",
        "groupadd $user",
        "rsync -ah --delete /etc/trainingwheels/skel/skel_user/ \$TW_SKEL_TMP/",
        "echo $pass | sudo tee \$TW_SKEL_TMP/.password > /dev/null",
        "useradd -m -p`openssl passwd -1 $pass` -d/twhome/$user -k\$TW_SKEL_TMP -s/bin/bash -g$user $user",
        "chmod o-rwx /twhome/$user",
        "chown root: /twhome/$user/.password",
        "chmod 400 /twhome/$user/.password",
        "rm -rf \$TW_SKEL_TMP",
      );
      $conn->exec_success($commands);
    };

    /**
     * Delete a user.
     */
    $env->userDelete = function($user) use ($conn) {
      $commands = array(
        "userdel $user",
        "groupdel $user",
        "rm -rf /twhome/$user",
      );
      $conn->exec_success($commands);
    };

    /**
     * Get a user id (Linux user id).
     */
    $env->userGetId = function($user) use ($conn) {
      $id = $conn->exec_get("id -u $user");
      if (!is_numeric($id)) {
        throw new Exception("The user '$user' does not exist, can't get id.");
      }
      return $id;
    };

    /**
     * Add a user to a group.
     */
    $env->userAddToGroup = function($user, $group) use ($conn) {
      $conn->exec_eq("gpasswd -a $user $group", "Adding user $user to group $group");
    };

    /**
     * Remove a user from a group.
     */
    $env->userRemoveFromGroup = function($user, $group) use ($conn) {
      $conn->exec_eq("gpasswd -d $user $group");
    };

    /**
     * Get a user's password.
     */
    $env->userPasswdGet = function($user) use ($env) {
      return $env->fileGetContents("/twhome/$user/.password");
    };

    /**
     * Is the user logged in?
     */
    $env->userIsLoggedIn = function($user) use ($conn) {
      $out = $conn->exec_get("users");
      $logged_in = explode(' ', $out);
      return in_array($user, $logged_in);
    };
  }
}
