<?php

namespace TrainingWheels\Plugin;

abstract class PluginBase {
  protected $name;
  protected $location;
  protected $title;

  protected $ansible_play;
  protected $ansible_vars;

  public function __construct() {
    $this->ansible_vars = array();
    $this->ansible_play = FALSE;
  }

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

  public function getAnsiblePlay() {
    return $this->ansible_play;
  }

  public function formatAnsibleVars() {
    $output = '';
    foreach ($this->ansible_vars as $key => $value) {
      $output .= "$key=$value ";
    }
    return trim($output);
  }

  public function getAnsibleConfig() {
    return FALSE;
  }
}
