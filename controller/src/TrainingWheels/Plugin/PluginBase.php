<?php

namespace TrainingWheels\Plugin;
use Exception;

abstract class PluginBase {

  protected $location;
  protected $vars;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->vars = array();
  }

  /**
   * Get the resource object for this plugin.
   */
  public function resourceFactory($type, $env, $data, $title, $user_name, $course_name, $res_id, $config) {
    $classes = $this->getResourceClasses();
    if (!$classes) {
      $plugin_type = $this->getType();
      throw new Exception("The plugin type \"$plugin_type\" does not provide resources");
    }
    $class = $classes[$type];

    // Validate that the config is correct for this resource.
    $default_vars = $class::getResourceVars();
    foreach ($default_vars as $var) {
      $key = $var['key'];

      // A value in the database overrides the default.
      if (isset($config[$key])) {
        continue;
      }

      // We don't have anything in the database for this var, check whether this is a mistake.
      $required = isset($var['required']) ? $var['required'] : FALSE;
      if ($var['default'] === NULL && $required) {
        throw new Exception("The resource \"$title\" of type \"$type\" requires a value be set for variable \"$key\" but none was found");
      }

      // Else we're ok to use the default value.
      $config[$key] = $var['default'];
    }

    $obj = new $class($env, $data, $title, $user_name, $course_name, $res_id, $config);
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
   * Return the value of a variable.
   */
  public function getVar($name) {
    return $this->vars[$name];
  }

  /**
   * Given the data loaded from the DataStore, setup the instance
   * of this plugin correctly. This allows config in the database to
   * override the default config the plugin provides.
   */
  public function set($data) {
    $this->validateDefaultVarsConfig();
    $defaults = $this->getPluginVars();
    $type = $this->getType();

    if (!empty($defaults)) {
      foreach($defaults as $var) {
        $key = $var['key'];
        $default_value = isset($var['default']) ? $var['default'] : NULL;
        $data_value = isset($data[$key]) ? $data[$key] : NULL;

        if ($data_value === NULL && $default_value === NULL) {
          throw new Exception("The plugin \"$type\" requires a value for variable \"$key\".");
        }

        if ($data_value !== NULL) {
          $this->vars[$key] = $data_value;
        }
        else {
          $this->vars[$key] = $default_value;
        }
      }
    }
  }

  /**
   * Validate the plugin's default variable config is correctly structured.
   */
  public function validateDefaultVarsConfig() {
    $vars = $this->getPluginVars();
    $type = $this->getType();

    if (!empty($vars)) {
      foreach($vars as $settings) {
        foreach ($settings as $key => $value) {
          if (!in_array($key, array('key', 'default', 'help', 'hint', 'required'))) {
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
    foreach ($this->vars as $key => $value) {
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
   * Variable config. Override in subclass if you provide variables.
   */
  public function getPluginVars() {
    return array();
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

  /**
   * Bundles. Override in subclass if you provide.
   */
  public function getBundles() {
    return FALSE;
  }
}
