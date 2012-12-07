<?php

namespace TrainingWheels\Store;
use Exception;
use MongoClient;

class DataStore {
  private $db = NULL;

  /**
   * Constructor.
   */
  public function __construct() {
    $connection = new MongoClient();
    $db = $connection->trainingwheels;
  }

  /**
   * Get a collection.
   */
  public function getCollection($c) {
    return $db->$c;
  }
}
