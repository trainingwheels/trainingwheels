<?php

namespace TrainingWheels\Resource;
use TrainingWheels\Common\CachedObject;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Log\Log;

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

  abstract public function getExists();

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $user_name, $course_name, $res_id) {
    $this->env = $env;
    $this->title = $title;
    $this->user_name = $user_name;
    $this->course_name = $course_name;
    $this->res_id = $res_id;

    parent::__construct();
    $this->cachePropertiesAdd(array('exists'));
  }

  /**
   * Helper to log messages from this class.
   */
  private function log($message, $level = L_INFO) {
    Log::log($message, $level, 'actions', array('layer' => 'app', 'source' => $this->getType(), 'params' => $this->res_id));
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
    $pieces = explode('\\', get_class($this));
    return $pieces[count($pieces)-1];
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
