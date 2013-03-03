<?php

namespace TrainingWheels\User;
use TrainingWheels\Common\CachedObject;
use TrainingWheels\Common\Util;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Log\Log;
use TrainingWheels\Store\DataStore;

use Exception;

class User extends CachedObject {

  // User name.
  protected $user_name;

  // Password.
  protected $password;

  // Reference to the training environment.
  protected $env;

  // User id that identifies this user uniquely in Training Wheels.
  protected $user_id;

  // Resources that this user needs.
  public $resources;

  // Does this user exist yet?
  protected $exists;

  /**
   * Constructor.
   *
   * We create this object using the passed parameters which specify the environment,
   * user name and user id. Since running commands in the environment is assumed to
   * be expensive, we don't automatically attempt to discover whether the user exists
   * by querying the enviornment - instead, we load that kind of data from the cache.
   *
   * If the cache doesn't exist, that may be because the user doesn't exist yet. It
   * may also be because someone has cleared the cache.
   */
  public function __construct(Environment $env, DataStore $data, $user_name, $user_id) {
    // Save the data that is passed to this object.
    $this->env = $env;
    $this->user_name = $user_name;
    $this->user_id = $user_id;

    parent::__construct($data);
    $this->cachePropertiesAdd(array('exists', 'password', 'env_user_id'));
    $this->cacheBuild($user_id);
  }

  /**
   * Helper to log messages from this class.
   */
  private function log($message, $params = '') {
    Log::log($message, L_INFO, 'actions', array('layer' => 'app', 'source' => 'User', 'params' => "user=" . $this->getName() . ' ' . $params));
  }

  /**
   * Get the name.
   */
  public function getName() {
    return $this->user_name;
  }

  /**
   * Create this user.
   */
  public function create() {
    if ($this->getExists()) {
      throw new Exception("Attempting to create a user that already exists");
    }
    $this->log('create()');
    $this->exists = TRUE;
    $this->password = Util::passwdGen();
    $this->env->userCreate($this->user_name, $this->password);
  }

  /**
   * Delete this user.
   */
  public function delete() {
    if (!$this->getExists()) {
      throw new Exception("Attempting to delete a user that does not exist.");
    }
    // Remove the resources associated with this user first, as some may rely
    // on the user still existing in the system.
    foreach ($this->resources as $res) {
      if ($res->getExists()) {
        $res->delete();
      }
    }
    $this->log('delete()');
    $this->env->userDelete($this->user_name);
    $this->exists = FALSE;
    $this->password = FALSE;
  }

  /**
   * Return bool for whether the user exists in the environment.
   */
  public function getExists() {
    $this->log('getExists()');
    if (!isset($this->exists)) {
      $this->exists = $this->env->userExists($this->user_name);
    }
    return $this->exists;
  }

  /**
   * Get the resources.
   */
  public function resourcesGetAll() {
    $this->log('resourcesGetAll()');
    return $this->resources;
  }

  /**
   * Get a resource.
   */
  public function resourceGet($name) {
    $this->log('resourceGet', "res=$name");
    return isset($this->resources[$name]) ? $this->resources[$name] : FALSE;
  }

  /**
   * Create the resources.
   */
  public function resourcesCreate($resources) {
    $this->log('resourcesCreate', "res=$resources");
    if ($resources == '*' || $resources == array('*')) {
      foreach ($this->resources as $res) {
        $res->create();
      }
    }
    else {
      foreach ($resources as $res) {
        if (isset($this->resources[$res])) {
          $this->resources[$res]->create();
        }
        else {
          throw new Exception("Attempting to create resource '$res' which is not a recognized resource for this user");
        }
      }
    }
  }

  /**
   * Delete the resources.
   */
  public function resourcesDelete($resources) {
    $this->log('resourcesDelete');
    if ($resources == '*' || $resources == array('*')) {
      foreach ($this->resources as $res) {
        $res->delete();
      }
    }
    else {
      foreach ($resources as $res) {
        if (isset($this->resources[$res])) {
          $this->resources[$res]->delete();
        }
        else {
          throw new Exception("Attempting to delete resource '$res' which is not a recognized resource for this user");
        }
      }
    }
  }

  /**
   * Sync resources to another user.
   */
  public function syncTo(User $target, $resources) {
    $this->log('syncTo', "target=" . $target->getName());
    $target_resources = $target->resourcesGetAll();

    foreach ($this->resources as $key => $res) {
      if ($resources == '*' || in_array($key, $resources)) {
        $res->syncTo($target_resources[$key]);
      }
    }
  }

  /**
   * Return the current user's password.
   */
  public function getPasswd() {
    if (empty($this->password)) {
      // No password stored with the current user object, so attempt to get one
      // from the environment.
      $password = $this->env->userPasswdGet($this->user_name);
      if ($password) {
        $this->password = $password;
      }
      else {
        throw new Exception("Attempting to get the password for a user that doesn't exist");
      }
    }
    return $this->password;
  }

  /**
   * Login status.
   */
  public function isLoggedIn() {
    return $this->env->userIsLoggedIn($this->user_name);
  }

  /**
   * Gather this user's status into an array representation.
   *
   * @param $full
   *   It can be expensive to compute the resource's status, so
   *   if $full is FALSE, skip resources.
   */
  public function get($full = TRUE) {
    $this->log('get()');
    if ($this->getExists()) {
      $user = array(
        'user_name' => $this->user_name,
        'password' => $this->getPasswd(),
        'logged_in' => $this->isLoggedIn(),
        'id' => $this->user_id,
      );
      if ($full) {
        foreach ($this->resources as $name => $resource) {
          $res = $resource->get();
          $res['key'] = $name;
          $res['user_id'] = $this->user_id;
          $res['id'] = $this->user_id . '-' . $name;
          $user['resources'][] = $res;
        }
      }
      // An overview of the resource status.
      $resource_count = count($this->resources);
      $exist_count = 0;

      foreach ($this->resources as $name => $resource) {
        if ($resource->getExists()) {
          $exist_count++;
        }
      }
      if ($exist_count == 0) {
        $user['resource_status'] = 'resource-missing';
      }
      else if ($exist_count == $resource_count) {
        $user['resource_status'] = 'resource-ready';
      }
      else {
        $user['resource_status'] = 'resource-partial';
      }

      return $user;
    }
    else {
      return FALSE;
    }
  }
}
