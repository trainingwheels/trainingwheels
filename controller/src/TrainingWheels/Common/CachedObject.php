<?php

namespace TrainingWheels\Common;
use Exception;

abstract class CachedObject {

  private $id;
  private $properties;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->properties = array();
  }

  /**
   * Build this object.
   */
  protected function cacheBuild($id) {
    $this->id = 'tw-' . $id;
    $this->cacheFetch();
  }

  /**
   * Add cached properties.
   */
  protected function cachePropertiesAdd($properties) {
    $this->properties = array_merge($this->properties, $properties);
  }

  /**
   * Save the current object back to the cache.
   */
  protected function cacheSave() {
    $data = array();
    foreach ($this->properties as $prop) {
      $data[$prop] = $this->$prop;
    }
    //cache_set($this->id, $data, 'cache');
  }

  /**
   * Load properties from the cache.
   */
  private function cacheFetch() {
    //$data = cache_get($this->id, 'cache');;
    $data = NULL;
    if ($data) {
      foreach ($this->properties as $prop) {
        if (isset($data->data[$prop]) && !empty($data->data[$prop])) {
          $this->$prop = $data->data[$prop];
        }
      }
    }
  }
}
