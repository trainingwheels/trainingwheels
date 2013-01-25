<?php

namespace TrainingWheels\Job;
use TrainingWheels\Job\Job;
use TrainingWheels\Course\CourseFactory;
use Exception;

class ClassroomJob extends Job {
  /**
   * Configure the course by running the playbooks.
   */
  protected function classroomConfigure() {
    $course = CourseFactory::singleton()->get($this->course_id);
    $course->configure();
  }
}
