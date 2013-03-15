<?php

namespace TrainingWheels\Store;
use Exception;
use MongoClient;

class DataStore {
  private $db = NULL;
  private $connection = NULL;

  /**
   * Constructor.
   */
  public function __construct($dbUrl) {
    $connection = new MongoClient($dbUrl);
    $this->connection = $connection;
    $this->db = $connection->trainingwheels;
  }

  /**
   * Get the MongoClient instance.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * Auto increment an ID for the courses.
   * Maintain counters in Mongo to generate ids.
   *
   * @see http://docs.mongodb.org/manual/tutorial/create-an-auto-incrementing-field/
   */
  protected function getNextSequence($name) {
    $ret = $this->db->counters->findAndModify(
      array('_id' => $name),
      array('$inc' => array('seq' => 1)),
      NULL,
      array('new' => TRUE)
    );
    if (!isset($ret['seq'])) {
      throw new Exception('The sequence was not found, so cannot generate a new ID.');
    }
    return $ret['seq'];
  }

  /**
   * Insert a document.
   */
  public function insert($collection, $object, $auto_increment = TRUE) {
    if ($auto_increment) {
      $object['id'] = $this->getNextSequence($collection . '_id');
    }
    $this->db->$collection->insert($object);
    return $object;
  }

  /**
   * Get a document.
   */
  public function find($collection, $query) {
    return $this->db->$collection->findOne($query);
  }

  /**
   * Get all documents from a collection.
   */
  public function findAll($collection, $sort = NULL) {
    $output = array();
    $cursor = $this->db->$collection->find();
    if ($sort) {
      $cursor = $cursor->sort(array($sort => '1'));
    }
    foreach ($cursor as $id => $value) {
      unset($value['_id']);
      $output[] = (object)$value;
    }
    return $output;
  }

  /**
   * Delete a document.
   *
   * @see http://php.net/manual/en/mongocollection.remove.php
   */
  public function remove($collection, array $criteria, array $options = array()) {
    return $this->db->$collection->remove($criteria, $options);
  }
}
