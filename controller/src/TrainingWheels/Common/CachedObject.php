<?php

namespace TrainingWheels\Common;
use TrainingWheels\Store\DataStore;
use TrainingWheels\Log\Log;
use Exception;

abstract class CachedObject {

  private $id;
  private $properties;

  // Reference to the data store.
  protected $data;

  /**
   * Helper to log messages from this class.
   */
  private function log($message, $params, $level = L_DEBUG) {
    Log::log($message, $level, 'actions', array('layer' => 'app', 'source' => 'CachedObject', 'params' => $params));
  }

  /**
   * Constructor.
   */
  public function __construct(DataStore $data) {
    $this->data = $data;
    $this->properties = array();
  }

  /**
   * Destructor. Save this object to the cache if the ID has been set.
   */
  public function __destruct() {
    if ($this->id) {
      $this->cacheSave();
    }
    else {
      $this->log('Unsaved cache object', "id=$this->id", L_WARNING);
    }
  }

  /**
   * Build this object. This should be called from the child class' constructor,
   * after registering the required properties.
   */
  protected function cacheBuild($id) {
    if (empty($id)) {
      throw new Exception("Cannot build cache object without a provided ID.");
    }
    $this->id = $id;
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
  private function cacheSave() {
    $cache_entry = array(
      'id' => $this->id,
      'data' => array(),
    );

    foreach ($this->properties as $prop) {
      if (isset($this->$prop)) {
        $cache_entry['data'][$prop] = $this->$prop;
      }
    }

    $this->log('Cache save', json_encode($cache_entry));
    $this->data->remove('cache', array('id' => $this->id));
    $this->data->insert('cache', $cache_entry, FALSE);
  }

  /**
   * Load properties from the cache and apply them to this object.
   */
  private function cacheFetch() {
    $cache_entry = $this->data->find('cache', array('id' => $this->id));
    if ($cache_entry) {
      unset($cache_entry['_id']);
      $this->log('Item hit', json_encode($cache_entry));

      foreach ($this->properties as $prop) {
        $this->log('Entry lookup', $prop);
        if (isset($cache_entry['data'][$prop])) {
          $this->$prop = $cache_entry['data'][$prop];
          $this->log('Entry hit', json_encode(array($prop => $this->$prop)));
        }
        else {
          $this->log('Entry miss', $prop);
        }
      }
    }
    else {
      $this->log('Item miss', "id=$this->id");
    }
  }
}
