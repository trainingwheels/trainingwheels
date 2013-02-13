<?php

namespace TrainingWheels\Plugin;

abstract class PluginBase {

  protected $location;
  protected $provision_vars;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->provision_vars = array();
  }

  /**
   * Get the resource object for this plugin.
   */
  public function resourceFactory($type, $env, $title, $res_id, $user_name, $course_name, $data) {
    $classes = $this->getResourceClasses();
    $class = $classes[$type];
    $obj = new $class($env, $title, $res_id, $user_name, $course_name, $data);
    return $obj;
  }

  /**
   * Return the short type of this plugin, e.g. 'MySQL'
   */
  public function getType() {
    $pieces = explode('\\', get_class($this));
    return $pieces[count($pieces)-1];
  }

  /**
   * Given the data loaded from the DataStore, setup the instance
   * of this plugin correctly. This allows config in the database to
   * override the default config the plugin provides.
   */
  public function set($data) {
    $provision_config = $this->getProvisionConfig();

    if ($provision_config) {
      foreach($provision_config['vars'] as $key => $var) {

        // If the default config has the value NULL then we
        // must obtain the value from the passed $data.
        if (!isset($var)) {
          $this->provision_vars[$key] = $data[$key];
        }
        // If the default config provides a value, then check
        // if an override is provided.
        else if (isset($data[$key])) {
          $this->provision_vars[$key] = $data[$key];
        }
        // Else use the default.
        else {
          $this->provision_vars[$key] = $var;
        }
      }
    }
  }

  /**
   * Format the Ansible variables for inclusion in the play.
   */
  public function formatAnsibleVars() {
    $output = '';
    foreach ($this->provision_vars as $key => $value) {
      $output .= "$key=$value ";
    }
    return trim($output);
  }

  /**
   * Provisioning steps. Override in subclass if you provide provisioning
   */
  public function getProvisionSteps() {
    return FALSE;
  }

  /**
   * Provisioning config. Override in subclass if you provide provisioning
   */
  public function getProvisionConfig() {
    return FALSE;
  }

  /**
   * Environment mixins. Override in subclass if you provide these.
   */
  public function mixinEnvironment($env, $type) {
    return FALSE;
  }

  /**
   * Course observers. Override in subclass if you provide.
   */
  public function registerCourseObservers($course) {
    return FALSE;
  }
}
