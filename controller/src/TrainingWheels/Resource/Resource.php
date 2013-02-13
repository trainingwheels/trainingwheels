<?php

namespace TrainingWheels\Resource;
use TrainingWheels\Common\CachedObject;
use TrainingWheels\Environment\Environment;

abstract class Resource extends CachedObject {

  // The user name.
  public $user_name;

  // The environment.
  public $env;

  // Title.
  public $title;

  // Whether it exists yet.
  protected $exists;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $user_name) {
    $this->env = $env;
    $this->user_name = $user_name;
    $this->title = $title;

    parent::__construct();
    $this->cachePropertiesAdd(array('exists'));
  }

  abstract public function create();
  abstract public function delete();
  abstract public function get();
}
