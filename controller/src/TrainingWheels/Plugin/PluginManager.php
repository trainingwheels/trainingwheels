<?php

namespace TrainingWheels\Plugin;

class PluginManager {
  // Singleton instance.
  protected static $instance;

  // Plugins.
  protected $plugins;

  /**
   * Return the singleton.
   */
  public static function singleton() {
    if (!isset(self::$instance)) {
      $className = get_called_class();
      self::$instance = new $className;
    }
    return self::$instance;
  }

  /**
   * Constructor.
   */
  public function __construct() {
    $this->plugins = $this->loadPlugins();
  }

  /**
   * Possibly add more custom locations.
   */
  public function getPluginDirs() {
    return array(__DIR__);
  }

  /**
   * Load all plugins from disk.
   */
  protected function loadPlugins() {
    $plugins = array();

    foreach ($this->getPluginDirs() as $dir) {
      $items = scandir($dir);
      foreach ($items as $item) {
        $plugin_dir = $dir . '/' . $item;
        if (!in_array($item, array('.', '..')) && is_dir($plugin_dir)) {
          $class = '\TrainingWheels\Plugin\\' . $item . '\\' . $item;
          $plugins[] = new $class();
        }
      }
    }

    return $plugins;
  }

  /**
   * Return the playbooks for each plugin.
   */
  public function getPlaybooks() {
    $plays = array();
    foreach ($this->plugins as $plugin) {
      $config = $plugin->getConfig();
      if (isset($config['playbook'])) {
        $play = $config['playbook'];
        $plays[] = $play;
      }
    }

    return $plays;
  }
}
