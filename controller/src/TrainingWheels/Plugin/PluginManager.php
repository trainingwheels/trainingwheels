<?php

namespace TrainingWheels\Plugin;

class PluginManager {
  // Plugins.
  protected $plugins;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->plugins = $this->loadPlugins();
  }

  /**
   * TODO: Custom user location supported for plugins.
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
          if (!class_exists($class)) {
            throw new Exception("The directory \"$plugin_dir\" does not contain a properly defined plugin class \"$class\".");
          }
          $plugins[] = new $class();
        }
      }
    }

    return $plugins;
  }
}
