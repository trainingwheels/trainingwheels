<?php

namespace TrainingWheels\Course;
use TrainingWheels\Log\Log;
use Exception;

abstract class TrainingCourse {
  // An instance of course TrainingEnv.
  public $env;

  // The course name.
  public $course_name;

  // The course id, which can be the node id.
  public $courseid;

  // Factory method for creating user objects, needs to be provided by subclass.
  abstract protected function userFactory($user_name);

  /**
   * Used by various functions to normalize the $users parameter.
   */
  protected function userNormalizeParam($users) {
    if ($users === '*') {
      // TODO: Only get the users associated with this course, not all of them.
      return $this->env->usersGetAll();
    }
    else if (is_string($users) && !empty($users)) {
      return array($users);
    }
    else if (is_array($users) && !empty($users)) {
      return $users;
    }
    else {
      throw new Exception('Invalid parameter passed to studentsNormalizeParam');
    }
  }

  /**
   * Get multiple users in this course.
   */
  public function usersGet($users = '*') {
    $users = $this->userNormalizeParam($users);
    $output = array();
    if (!empty($users)) {
      foreach ($users as $user_name) {
        $user_obj = $this->userFactory($user_name);
        if ($user_obj->getExists()) {
          $output[$user_name] = $this->userGet($user_name);
        }
      }
    }
    return $output;
  }

  /**
   * Create users.
   */
  public function usersCreate($users) {
    $users = $this->userNormalizeParam($users);
    foreach ($users as $user) {
      $user_obj = $this->userFactory($user);
      $user_obj->create();
    }
  }

  /**
   * Delete users.
   */
  public function usersDelete($users) {
    $users = $this->userNormalizeParam($users);
    foreach ($users as $user) {
      $user_obj = $this->userFactory($user);
      $user_obj->delete();
    }
  }

  /**
   * Get info on a single user.
   */
  public function userGet($user_name) {
    $user_obj = $this->userFactory($user_name);
    $user_info = $user_obj->get();
    $user_info['course_id'] = $this->courseid;
    $user_info['uri'] = '/user/' . $user_info['user_id'];
    return $user_info;
  }

  /**
   * Sync resources for a user.
   */
  public function usersResourcesSync($source_user, $target_users, $resources) {
    $target_users = $this->userNormalizeParam($target_users);

    // The source of the sync.
    $source_user_obj = $this->userFactory($source_user);

    foreach ($target_users as $user_name) {
      $target_user_obj = $this->userFactory($user_name);
      $source_user_obj->syncTo($target_user_obj, $resources);
    }
  }

  /**
   * Create resources for a user.
   */
  public function usersResourcesCreate($users, $resources) {
    $users = $this->userNormalizeParam($users);

    foreach ($users as $user_name) {
      $user_obj = $this->userFactory($user_name);
      $user_obj->resourcesCreate($resources);
    }
  }

  /**
   * Delete resources for a user.
   */
  public function usersResourcesDelete($users, $resources) {
    $users = $this->userNormalizeParam($users);

    foreach ($users as $user_name) {
      $user_obj = $this->userFactory($user_name);
      $user_obj->resourcesDelete($resources);
    }
  }
}
