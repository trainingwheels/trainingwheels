<?php

namespace TrainingWheels\Environment;

class DevEnv extends LinuxEnv {

  /**
   * Override the constructor as we don't need to enforce sudo.
   */
  public function __construct($conn, $base_path) {
    $this->conn = $conn;
    $this->base_path = $base_path;
  }

  /**
   * Create a user.
   */
  public function userCreate($user, $pass) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $dir = $this->base_path . '/' . $user;
    $this->conn->exec_eq("mkdir $dir");
  }

  /**
   * Check if a user exists in the system, just a directory.
   */
  public function userExists($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $dir = $this->base_path . '/' . $user;
    return $this->dirExists($dir);
  }

  /**
   * Delete a user.
   */
  public function userDelete($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    $dir = $this->base_path . '/' . $user;
    $this->conn->exec_eq("rmdir $dir");
  }

  /**
   * Is the user logged in?
   */
  public function userIsLoggedIn($user) {
    Util::assertValidStrings(__CLASS__ . '::' . __FUNCTION__, func_get_args());
    return FALSE;
  }

  /**
   * Get all Linux users, i.e. the directories.
   */
  public function usersGetAll() {
    $dir = $this->base_path;
    $output = $this->conn->exec_get("ls $dir");
    if (!empty($output)) {
      return explode("\n", $output);
    }
    else {
      return FALSE;
    }
  }
}
