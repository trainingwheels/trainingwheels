<?php

namespace TrainingWheels\Job;
use TrainingWheels\Log\Log;
use Exception;

abstract class Job {
  // The course id.
  protected $course_id;

  // The action to take for this job.
  protected $action;

  // Parameters related to the job.
  protected $params;

  /**
   * Constructor.
   */
  function __construct($course_id, $action, $params) {
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
}
