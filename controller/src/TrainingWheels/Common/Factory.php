<?php

namespace TrainingWheels\Common;
use TrainingWheels\Store\DataStore;

abstract class Factory {
  // Abstract functions.
  abstract public function get($id);
  abstract public function save($object);

  // The DataStore object.
  protected $data;

  /**
   * Constructor.
   */
  public function __construct(DataStore $data) {
    $this->data = $data;
  }
}
