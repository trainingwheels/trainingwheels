<?php

namespace TrainingWheels\Job;
use TrainingWheels\Job\Job;
use TrainingWheels\Course\CourseFactory;
use Exception;

class ResourceJob extends Job {
  private $dbUrl;

  public function __construct($dbUrl, $id, $course_id, $action, $params) {
    parent::__construct($id, $course_id, $action, $params);
    $this->dbUrl = $dbUrl;
  }

  /**
   * Creates given resources.
   *
   * $this->params should contain:
   *   user_names - an array of user names to create the resources for.
   *   resources - an array of resources to sync.
   */
  protected function resourceCreate() {
    $course = CourseFactory::singleton($this->dbUrl)->get($this->course_id);
    if (!$course) {
      throw new Exception("Course with id $this->course_id does not exist.");
    }
    $resources = empty($this->params['resources']) ? '*' : $this->params['resources'];
    $course->usersResourcesCreate($this->params['user_names'], $resources);
  }

  /**
   * Deletes given resources.
   *
   * $this->params should contain:
   *   user_names - an array of user names to create the resources for.
   *   resources - an array of resources to sync.
   */
  protected function resourceDelete() {
    $course = CourseFactory::singleton($this->dbUrl)->get($this->course_id);
    if (!$course) {
      throw new Exception("Course with id $this->course_id does not exist.");
    }
    $resources = empty($this->params['resources']) ? '*' : $this->params['resources'];
    $course->usersResourcesDelete($this->params['user_names'], $resources);
  }

  /**
   * Syncs resources from a source user to one or more target users.
   *
   * $this->params should contain:
   *   source_user - the name of the source user to sync from.
   *   target_users - an array of target user names to sync to.
   *   resources - an array of resources to sync.
   */
  protected function resourceSync() {
    $course = CourseFactory::singleton($this->dbUrl)->get($this->course_id);
    if (!$course) {
      throw new Exception("Course with id $this->course_id does not exist.");
    }
    $resources = empty($this->params['resources']) ? '*' : $this->params['resources'];
    $course->usersResourcesSync($this->params['source_user'], $this->params['target_users'], $resources);
  }
}
