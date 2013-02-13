<?php

namespace TrainingWheels\Common;
use TrainingWheels\Store\DataStore;

abstract class Factory {
  // Abstract functions.
  abstract public function get($id);
  abstract public function save($object);

  // The DataStore object.
  protected $data;

  // The configuration settings.
  protected $config;

  /**
   * Constructor.
   */
  public function __construct(DataStore $data, array $config = array()) {
    $this->data = $data;
    $this->config = $config;
  }
}
