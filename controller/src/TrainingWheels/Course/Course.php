<?php

namespace TrainingWheels\Course;
use TrainingWheels\Log\Log;
use TrainingWheels\Common\Observable;
use TrainingWheels\User\User;
use TrainingWheels\Store\DataStore;
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

  // Resource config.
  protected $resource_config;

  // Reference to the DataStore.
  protected $data;

  public function __construct(DataStore $data) {
    $this->data = $data;
  }

  public function setPlugins(array $plugins) {
    $this->plugins = $plugins;
  }

  public function getPlugins(array $plugins) {
    return $this->plugins;
  }

  public function getID() {
    return $this->course_id;
  }

  public function setResourceConfig(array $resource_config) {
    $this->resource_config = $resource_config;
  }

  /**
   * Helper to log messages from this class.
   */
  private function log($message, $params, $source = 'Course', $level = L_INFO) {
    Log::log($message, $level, 'actions', array('layer' => 'app', 'source' => $source, 'params' => $params));
  }

  /**
   * Factory that creates new user objects for this course.
   */
  protected function userFactory($user_name) {
    $user_id = $this->course_id . '-' . $user_name;
    $user_obj = new User($this->env, $this->data, $user_name, $user_id);
    $this->log('Building user', $user_id, 'UserFactory', L_DEBUG);

    // Each user object must receive resource objects too. These are created
    // based on the course's resources config.
    $user_res = array();
    foreach ($this->resource_config as $key => $res) {
      $user_res_id = $user_id . '-' . $key;
      if (!isset($this->plugins[$res['plugin']])) {
        throw new Exception('The resource "' . $res['title'] . '" requires Plugin "' . $res['plugin'] . '" but the course does not include it.');
      }
      $plugin = $this->plugins[$res['plugin']];

      $user_res[$key] = $plugin->resourceFactory($res['type'], $this->env, $this->data, $res['title'], $user_name, $this->course_name, $user_res_id, $res);
      $this->log('Building resource', $user_res_id, 'UserFactory', L_DEBUG);
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
    if (!$users) {
      throw new Exception("No users found");
    }
    $this->log('usersGet()', 'users=' . implode(',', $users));
    $output = array();
    if (!empty($users)) {
      foreach ($users as $user_name) {
        $user = $this->userGet($user_name, FALSE);
        if ($user) {
          $output[$user_name] = $user;
        }
      }
    }
    return $output;
  }

  /**
   * Get info on a single user.
   */
  public function userGet($user_name, $full = TRUE) {
    $this->log('userGet()', 'user=' . $user_name);
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
    $this->log('usersCreate()', implode(',', $users));
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
    $this->log('usersDelete()', implode(',', $users));
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
    $this->log('usersResourcesSync()', 'source_user=' . $source_user . ' target_users=' . implode(',', $target_users) . ' resources=' . $resources);

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
    $this->log('usersResourcesCreate()', 'users=' . implode(',', $users) . ' resources=' . $resources);

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
    $this->log('usersResourcesDelete()', 'users=' . implode(',', $users) . ' resources=' . $resources);

    foreach ($users as $user_name) {
      $user_obj = $this->userFactory($user_name);
      $user_obj->resourcesDelete($resources);
    }
  }
}
