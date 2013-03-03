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
    $this->validateVarsConfig();
    $vars = $this->getPluginVars();
    $type = $this->getType();

    if ($vars) {
      foreach($vars as $key => $var) {
        $default_value = isset($var['val']) ? $var['val'] : NULL;
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
   * Validate the plugin's variable config is correctly structured.
   */
  public function validateVarsConfig() {
    $vars = $this->getPluginVars();
    $type = $this->getType();

    if ($vars) {
      foreach($vars as $var_name => $settings) {
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

  /**
   * Bundles. Override in subclass if you provide.
   */
  public function getBundles() {
    return FALSE;
  }
}
