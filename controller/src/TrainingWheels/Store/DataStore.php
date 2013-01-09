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
    $this->db = $connection->trainingwheels;
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
  public function insert($collection, $object) {
    $object['id'] = $this->getNextSequence($collection . '_id');
    $this->db->$collection->insert($object);
    return $object;
  }

  /**
   * Get a document.
   */
  public function find($collection, $id) {
    return $this->db->$collection->findOne(array('id' => (int)$id));
  }

  /**
   * Get all documents from a collection.
   */
  public function findAll($collection) {
    $output = array();
    $cursor = $this->db->$collection->find();
    foreach ($cursor as $id => $value) {
      unset($value['_id']);
      $output[] = (object)$value;
    }
    return $output;
  }
}






