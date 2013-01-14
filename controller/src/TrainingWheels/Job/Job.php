<?php

namespace TrainingWheels\Job;
use TrainingWheels\Log\Log;
use Exception;

abstract class Job {
  // An instance of course TrainingEnv.
  public $enf;

  // The course name.
  public $course_name;

  // The course id.
  public $course_id;
}
