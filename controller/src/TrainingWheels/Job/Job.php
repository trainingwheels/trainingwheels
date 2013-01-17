<?php

namespace TrainingWheels\Job;
use TrainingWheels\Log\Log;
use Exception;

abstract class Job {
  // The internal job id.
  protected $id;

  // The course id.
  protected $course_id;

  // The action to take for this job.
  protected $action;

  // Parameters related to the job.
  protected $params;

  /**
   * Constructor.
   */
  function __construct($id, $course_id, $action, $params) {
    $this->id = $id;
    $this->course_id = $course_id;
    $this->action = $action;
    $this->params = $params;
  }

  /**
   * Executes the job and returns a success/failure value.
   */
  public function execute() {
    if (method_exists($this, $this->action)) {
      $this->{$this->action}();
    }
    else {
      throw new Exception("Unknown job: $this->action.");
    }
  }

  /**
   * Helper function to serialize the job data for return
   * to the client.
   */
  public function serialize() {
    $ret = new \stdClass;
    $ret->id = $this->id;
    $ret->course_id = $this->course_id;
    $ret->action = $this->action;
    $ret->params = json_encode($this->params);

    return $ret;
  }

  /**
   * Simple getter function.
   */
  public function get($property) {
    if (isset($this->{$property})) {
      return $this->{$property};
    }
    throw new Exception("Unknown property: $property");
  }
}
