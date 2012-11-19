<?php

namespace TrainingWheels\Course;

class DrupalMultiSiteCourse extends TrainingCourse {

  protected function userFactory($user_name) {
    return array();
  }
}