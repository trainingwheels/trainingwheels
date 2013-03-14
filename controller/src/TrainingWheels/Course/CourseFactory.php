<?php

namespace TrainingWheels\Course;
use TrainingWheels\Common\Factory;
use TrainingWheels\Conn\LocalServerConn;
use TrainingWheels\Conn\SSHServerConn;
use TrainingWheels\Conn\KeyManager;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Log\Log;
use Exception;

class CourseFactory extends Factory {

  /**
   * Helper function to format log entries eminating from this class.
   */
  private function log($message, $params, $level = L_DEBUG) {
    Log::log($message, $level, 'actions', array('layer' => 'app', 'source' => 'CourseFactory', 'params' => $params));
  }

  /**
   * Create Course object given a course id.
   */
  public function get($course_id) {
    $this->log('Building course object', $course_id, L_INFO);

    $params = $this->data->find('course', array('id' => (int)$course_id));

    if ($params) {
      // Create a Connection object.
      if ($params['host'] == 'localhost') {
        $conn = new LocalServerConn(TRUE);
        $this->log('Connection success', 'localhost');
      }
      else {
        $key_manager = new KeyManager($this->config['base_path']);
        if (!isset($params['port'])) {
          $params['port'] = 22;
        }
        $conn = new SSHServerConn($params['host'], $params['port'], $params['user'], $key_manager);
        if (!$conn->connect()) {
          throw new Exception("Unable to connect/login to server " . $params['host'] . " on port " . $params['port'] . " as user " . $params['user']);
        }
        $this->log('Connection success', $params['host']);
      }

      // Create a Course object.
      $course = new Course($this->data);
      $course->course_id = $course_id;
      $course->title = $params['title'];
      $course->description = $params['description'];
      $course->course_name = $params['course_name'];
      $course->uri = '/course/' . $params['id'];

      // Set the resources config for use by the user factory method.
      if (isset($params['resources'])) {
        $course->setResourceConfig($params['resources']);
      }

      // Create an Environment object.
      $course->env = new Environment($conn, $this->config['debug']);
      $course->env_type = $params['env_type'];

      if ($course->env_type != 'ubuntu') {
        throw new Exception("Only 'ubuntu' environments are supported right now");
      }
      $this->log('Building environment', $course->env_type);

      // Build the Plugins associated with this course.
      if (!isset($params['plugins'])) {
        throw new Exception("The course has no plugins associated with it and cannot be loaded.");
      }
      $this->buildPlugins($course, $params['plugins']);

      return $course;
    }
    else {
      throw new Exception("Course with id $course_id does not exist.");
    }
  }

  /**
   * Attach plugins.
   */
  protected function buildPlugins(&$course, $plugin_data) {
    $plugins = array();
    foreach ($plugin_data as $key => $data) {
      $class = '\\TrainingWheels\\Plugin\\' . $key . '\\' . $key;
      if (!class_exists($class)) {
        throw new Exception("The plugin type \"$key\" class cannot be loaded at \"$class\".");
      }
      $plugin = new $class();
      $plugin->set($data);
      $plugins[$key] = $plugin;

      // The env type could be 'ubuntu' for example. 'linux' is hardcoded as we only
      // support Linux environments right now. An Ubuntu environment inherits all
      // the commands from 'linux'.
      // @TODO: Make a more flexible way of specifying the environment type.
      $plugin->mixinEnvironment($course->env, 'linux');
      $plugin->mixinEnvironment($course->env, $course->env_type);

      $plugin->registerCourseObservers($course);

      $this->log('Building plugin', $key);
    }
    $course->setPlugins($plugins);
  }

  /**
   * Get all course summaries.
   */
  public function getAllSummaries() {
    return $this->data->findAll('course');
  }

  /**
   * Save a course.
   */
  public function save($course) {
    $params = $this->data->find('course', array('id' => 1));
    // This is just temporary until we have better course creation tools.
    $course['plugins'] = $params['plugins'];
    $course['resources'] = $params['resources'];
    $course['resources']['drupal_files']['subdir'] = $course['course_name'];
    return $this->data->insert('course', $course);
  }
}
