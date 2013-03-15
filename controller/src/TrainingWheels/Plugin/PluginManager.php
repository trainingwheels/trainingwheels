<?php

namespace TrainingWheels\Plugin;
use Exception;
use stdClass;

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
   * Get the loaded plugins's default vars for use in building a course.
   */
  public function getFormBuild() {
    $output_json = array();
    $plugins_json = array();
    $resources_json = array();
    $bundles_json = array();
    foreach($this->plugins as $plugin_key => $plugin) {
      // Get the plugin provision variables.
      $plugin->validateVarsConfig();
      $plugin_vars = $plugin->getPluginVars();
      $plugins_json[] = array(
        'key' => $plugin_key,
        'vars' => $plugin_vars,
      );

      // Get the resource definitions.
      $resource_classes = $plugin->getResourceClasses();
      if ($resource_classes) {
        foreach ($resource_classes as $res_key => $resource_class) {
          $res_vars = $resource_class::getResourceVars();
          $resources_json[] = array(
            'type' => $res_key,
            'plugin' => $plugin_key,
            'vars' => $res_vars,
          );
        }
      }

      // Get the available bundles.
      $bundles = $plugin->getBundles();
      if ($bundles) {
        $bundles_json = array_merge($bundles_json, $bundles);
      }
    }
    $output_json['plugins'] = $plugins_json;
    $output_json['resources'] = $resources_json;
    $output_json['bundles'] = $bundles_json;
    return $output_json;
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
          $plugins[$item] = new $class();
        }
      }
    }

    return $plugins;
  }
}
