<?php

namespace TrainingWheels\User;

class DevUser extends User {
  /**
   * No passwords for users.
   */
  public function passwdGet() {
    return 'none';
  }
}