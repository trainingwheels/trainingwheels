<?php

namespace TrainingWheels\Job;
use TrainingWheels\Job\Job;
use TrainingWheels\Course\CourseFactory;
use Exception;

class ClassroomJob extends Job {
  private $courseFactory;

  public function __construct(CourseFactory $courseFactory, $id, $course_id, $action, $params) {
    parent::__construct($id, $course_id, $action, $params);
    $this->courseFactory = $courseFactory;
  }

  /**
   * Configure the course by running the playbooks.
   */
  protected function classroomConfigure() {
    $course = $this->courseFactory->get($this->course_id);
    $course->provision();
  }
}
