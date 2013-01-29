<?php

namespace TrainingWheels\Common;
use TrainingWheels\Store\DataStore;

abstract class Factory {
  // Abstract functions.
  abstract public function get($id);
  abstract public function save($object);

  protected $data;

  /**
   * Constructor.
   */
  public function __construct($dbUrl) {
    $this->data = new DataStore($dbUrl);
  }
}
