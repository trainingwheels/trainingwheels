<?php

namespace TrainingWheels\Course;
use TrainingWheels\Log\Log;
use Exception;

abstract class TrainingCourse {
  // An instance of course TrainingEnv.
  public $env;

  // The course name.
  public $course_name;

  // The course id.
  public $course_id;

  // Plugins associated with this course.
  protected $plugins;

  public function setPlugins(array $plugins) {
    $this->plugins = $plugins;
  }

  public function getPlugins(array $plugins) {
    return $this->plugins;
  }

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
   * Configure the environment. Typically runs the playbooks.
   */
  public function configure() {
    $this->env->configure($this->plugins);
  }

  /**
   * Get multiple users in this course. Will return summarized versions of users.
   */
  public function usersGet($users = '*') {
    $users = $this->userNormalizeParam($users);
    $output = array();
    if (!empty($users)) {
      foreach ($users as $user_name) {
        $user_obj = $this->userFactory($user_name);
        if ($user_obj->getExists()) {
          $output[$user_name] = $this->userGet($user_name, FALSE);
        }
      }
    }
    return $output;
  }

  /**
   * Get info on a single user.
   */
  public function userGet($user_name, $full = TRUE) {
    $user_obj = $this->userFactory($user_name);
    $user_info = $user_obj->get($full);
    if ($user_info) {
      $user_info['course_id'] = $this->course_id;
      $user_info['uri'] = '/users/' . $user_info['id'];
    }
    return $user_info;
  }

  /**
   * Create users.
   */
  public function usersCreate($users) {
    $users = $this->userNormalizeParam($users);
    foreach ($users as $user) {
      $user_obj = $this->userFactory($user);
      if ($user_obj->getExists()) {
        return FALSE;
      }
      $user_obj->create();
    }
    return TRUE;
  }

  /**
   * Delete users.
   */
  public function usersDelete($users) {
    $users = $this->userNormalizeParam($users);
    foreach ($users as $user) {
      $user_obj = $this->userFactory($user);
      if (!$user_obj->getExists()) {
        return FALSE;
      }
      $user_obj->delete();
    }
    return TRUE;
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
