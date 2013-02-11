<?php

namespace TrainingWheels\Plugin;

abstract class PluginBase {
  protected $location;
  protected $title;

  protected $ansible_play;
  protected $ansible_vars;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->ansible_vars = array();
    $this->ansible_play = FALSE;
  }

  /**
   * Mixin the environment functions.
   */
  public function mixinEnvironment($env, $type) {
    $funcs = $this->getEnvMixins($type);
    if ($funcs) {
      foreach ($funcs as $key => $func) {
        $env->$key = $func;
      }
    }
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
    $this->title = $data['title'];

    $ansible_config = $this->getAnsibleConfig();

    if ($ansible_config) {
      foreach($ansible_config['vars'] as $key => $var) {

        // If the default config has the value NULL then we
        // must obtain the value from the passed $data.
        if (!isset($var)) {
          $this->ansible_vars[$key] = $data[$key];
        }
        // If the default config provides a value, then check
        // if an override is provided.
        else if (isset($data[$key])) {
          $this->ansible_vars[$key] = $data[$key];
        }
        // Else use the default.
        else {
          $this->ansible_vars[$key] = $var;
        }
      }
    }
  }

  /**
   * Return the Ansible playbook.
   */
  public function getAnsiblePlay() {
    return $this->ansible_play;
  }

  /**
   * Format the Ansible variables for inclusion in the play.
   */
  public function formatAnsibleVars() {
    $output = '';
    foreach ($this->ansible_vars as $key => $value) {
      $output .= "$key=$value ";
    }
    return trim($output);
  }

  /**
   * Override in sub class if you provide Ansible playbook.
   */
  public function getAnsibleConfig() {
    return FALSE;
  }

  /**
   * Override in sub class if you provide Env mixins.
   */
  public function getEnvMixins($type) {
    return FALSE;
  }
}
