<?php

namespace TrainingWheels\Environment;

class CentosEnv extends LinuxEnv {
  /**
   * Restart Apache webserver.
   */
  public function apacheHTTPDRestart() {
    $this->serviceRestart('httpd');
  }

  /**
   * Add a user to the web server group.
   */
  public function userAddToWebGroup($user_name) {
    $this->userAddToGroup('apache', $user_name);
  }

  /**
   * Remove a user from the web server group.
   */
  public function userRemoveFromWebGroup($user_name) {
    $this->userRemoveFromGroup('apache', $user_name);
  }
}