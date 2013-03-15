<?php

namespace TrainingWheels\Resource;
use TrainingWheels\Common\CachedObject;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Log\Log;
use TrainingWheels\Store\DataStore;

abstract class Resource extends CachedObject {

  // The environment.
  protected $env;

  // Title.
  protected $title;

  // The user name.
  protected $user_name;

  // The course name.
  protected $course_name;

  // The resource id.
  protected $res_id;

  // Whether it exists yet.
  protected $exists;

  // The type of resource.
  private $type;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, DataStore $data, $title, $user_name, $course_name, $res_id) {
    $this->env = $env;
    $this->title = $title;
    $this->user_name = $user_name;
    $this->course_name = $course_name;
    $this->res_id = $res_id;

    parent::__construct($data);
    $this->cachePropertiesAdd(array('exists'));
  }

  /**
   * Helper to log messages from this class.
   */
  private function log($message, $level = L_INFO) {
    Log::log($message, $level, 'actions', array('layer' => 'app', 'source' => $this->getType(), 'params' => $this->res_id));
  }

  /**
   * Set whether this resource exists.
   */
  public function setExists($exists) {
    $this->log('setExists()', L_DEBUG);
    $this->exists = $exists;
  }

  /**
   * Create resource.
   */
  public function create() {
    $this->log('create()');
  }

  /**
   * Delete resource.
   */
  public function delete() {
    $this->log('delete()');
  }

  /**
   * Sync resource to a target.
   */
  public function syncTo() {
    $this->log('syncTo()');
  }

  /**
   * Return the short type of this plugin, e.g. 'MySQL'
   */
  public function getType() {
    if (!isset($this->type)) {
      $pieces = explode('\\', get_class($this));
      $this->type = $pieces[count($pieces)-1];
    }
    return $this->type;
  }

  /**
   * Get the information about the state of this resource.
   */
  public function get() {
    $this->log('get()');
    $info = array(
      'type' => $this->getType(),
      'exists' => $this->getExists(),
      'title' => $this->title,
      // In the future, we may have more statuses than just ready or missing.
      'status' => $this->getExists() ? 'resource-ready' : 'resource-missing',
    );
    return $info;
  }

  /**
   * Get the configuration options for instances of this resource.
   */
  public static function getResourceVars() {
    return array();
  }
}
