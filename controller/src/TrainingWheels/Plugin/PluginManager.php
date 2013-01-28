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
   * Invoke a method on all plugins that support it.
   */
  public function invokeAll($method, $plugins) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    foreach ($plugins as $plugin) {
      if (method_exists($plugin, $method)) {
        $plugin->$method($args);
      }
    }
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
