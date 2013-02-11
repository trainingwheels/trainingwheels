<?php

namespace TrainingWheels\Environment;

class UbuntuEnv extends LinuxEnv {
  /**
   * Restart Apache webserver.
   */
  public function apacheHTTPDRestart() {
    $this->serviceRestart('apache2');
  }

  /**
   * Add a user to the web server group.
   */
  public function userAddToWebGroup($user_name) {
    $this->userAddToGroup('www-data', $user_name);
  }

  /**
   * Remove a user from the web server group.
   */
  public function userRemoveFromWebGroup($user_name) {
    $this->userRemoveFromGroup('www-data', $user_name);
  }
}
