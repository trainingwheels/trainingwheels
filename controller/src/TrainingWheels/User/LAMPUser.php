<?php

namespace TrainingWheels\User;

class LAMPUser extends User {
  /**
   * Create this user.
   */
  public function create() {
    parent::create();
    $this->env->userAddToWebGroup($this->user_name);
  }
}
