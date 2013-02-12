<?php

namespace TrainingWheels\Course;
use TrainingWheels\Log\Log;
use TrainingWheels\Common\Observable;
use TrainingWheels\User\User;
use Exception;

class Course extends Observable {
  // An instance of course TrainingEnv.
  public $env;

  // The course name.
  public $course_name;

  // The course id.
  public $course_id;

  // Plugins associated with this course.
  protected $plugins;

  // Resource information.
  protected $resources;

  public function setPlugins(array $plugins) {
    $this->plugins = $plugins;
  }

  public function getPlugins(array $plugins) {
    return $this->plugins;
  }

  public function setResources(array $resources) {
    $this->resources = $resources;
  }

  /**
   * Factory that creates new user objects for this course.
   */
  protected function userFactory($user_name) {
    $user_id = $this->course_id . '-' . $user_name;
    $user_obj = new User($this->env, $user_name, $user_id);

    $user_res = array();
    foreach ($this->resources as $key => $res) {
      $user_res_id = $user_id . '-' . $key;
      $user_res[$key] = $this->plugins[$res['type']]->resourceFactory($this->env, $res['title'], $user_res_id, $user_name, $this->course_name, $res);
    }
    $user_obj->resources = $user_res;

    return $user_obj;
  }

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
   * Provision the environment. Typically runs the playbooks.
   */
  public function provision() {
    $this->env->provision($this->plugins);
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
      $this->fireEvent('afterOneUserCreate', array('course' => $this, 'user' => $user_obj));
    }
    $this->fireEvent('afterUsersCreate', array('course' => $this));
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
   * Sync resources for users.
   */
  public function usersResourcesSync($source_user, $target_users, $resources) {
    $target_users = $this->userNormalizeParam($target_users);

    // The source of the sync.
    $source_user_obj = $this->userFactory($source_user);

    foreach ($target_users as $user_name) {
      $target_user_obj = $this->userFactory($user_name);
      $source_user_obj->syncTo($target_user_obj, $resources);

      $this->fireEvent('afterUserResourcesSync', array('course' => $this, 'source' => $source_user_obj, 'target' => $target_user_obj));
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

      $this->fireEvent('afterUserResourcesCreate', array('course' => $this, 'user' => $user_obj, 'resources' => $resources));
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
