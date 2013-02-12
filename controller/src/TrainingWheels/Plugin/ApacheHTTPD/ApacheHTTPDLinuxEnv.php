<?php

namespace TrainingWheels\Plugin\ApacheHTTPD;

class ApacheHTTPDLinuxEnv {

  public function mixinUbuntuEnv($env) {
    /**
     * Restart Apache webserver.
     */
    $env->apacheHTTPDRestart = function() use ($env) {
      $env->serviceRestart('apache2');
    };

    /**
     * Add a user to the web server group.
     */
    $env->userAddToWebGroup = function($user_name) use ($env) {
      $env->userAddToGroup('www-data', $user_name);
    };

    /**
     * Remove a user from the web server group.
     */
    $env->userRemoveFromWebGroup = function($user_name) use ($env) {
      $env->userRemoveFromGroup('www-data', $user_name);
    };
  }

  public function mixinCentosEnv($env) {
    /**
     * Restart Apache webserver.
     */
    $env->apacheHTTPDRestart = function() use ($env) {
      $env->serviceRestart('httpd');
    };

    /**
     * Add a user to the web server group.
     */
    $env->userAddToWebGroup = function($user_name) use ($env) {
      $env->userAddToGroup('apache', $user_name);
    };

    /**
     * Remove a user from the web server group.
     */
    $env->userRemoveFromWebGroup = function($user_name) use ($env) {
      $env->userRemoveFromGroup('apache', $user_name);
    };
  }
}
