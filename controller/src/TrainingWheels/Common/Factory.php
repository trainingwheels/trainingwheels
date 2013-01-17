<?php

namespace TrainingWheels\Common;

abstract class Factory {
  // Abstract functions.
  abstract public function get($id);
  abstract public function save($object);

  /**
   * Additionally each implementing class should contain
   * a protected static $instance variable and a public
   * static singleton function to return a static instance
   * of the class.
   */

  protected static $data;
}
