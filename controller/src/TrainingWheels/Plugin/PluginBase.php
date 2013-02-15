<?php

namespace TrainingWheels\Plugin;
use Exception;

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
    if (!$classes) {
      $type = $this->getType();
      throw new Exception("The plugin type \"$type\" does not provide resources");
    }
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
    $this->validateProvisionConfig();
    $provision_config = $this->getProvisionConfig();
    $type = $this->getType();

    if ($provision_config) {
      foreach($provision_config['vars'] as $key => $var) {
        $default_value = isset($var['val']) ? $var['val'] : NULL;
        $data_value = isset($data[$key]) ? $data[$key] : NULL;

        if ($data_value === NULL && $default_value === NULL) {
          throw new Exception("The plugin \"$type\" requires a value for variable \"$key\".");
        }

        if ($data_value !== NULL) {
          $this->provision_vars[$key] = $data_value;
        }
        else {
          $this->provision_vars[$key] = $default_value;
        }
      }
    }
  }

  /**
   * Validate the plugin's provision config is correctly structured.
   */
  public function validateProvisionConfig() {
    $provision_config = $this->getProvisionConfig();
    $type = $this->getType();

    if ($provision_config) {
      if (!isset($provision_config['vars'])) {
        throw new Exception("The plugin \"$type\" must provide an array with a key 'vars' in 'getProvisionConfig'");
      }
      foreach($provision_config['vars'] as $var_name => $settings) {
        foreach ($settings as $key => $value) {
          if (!in_array($key, array('val', 'help', 'hint'))) {
            throw new Exception("The plugin \"$type\" has a variable with unrecognized key \"$key\"");
          }
        }
      }
    }
    return TRUE;
  }

  /**
   * Turn the vars into a string of key=value entries.
   */
  public function formatVarsString() {
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

  /**
   * Resources. Override in subclass if you provide.
   */
  public function getResourceClasses() {
    return FALSE;
  }
}
