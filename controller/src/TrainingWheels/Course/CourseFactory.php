<?php

namespace TrainingWheels\Course;
use TrainingWheels\Common\Factory;
use TrainingWheels\Conn\LocalServerConn;
use TrainingWheels\Conn\SSHServerConn;
use TrainingWheels\Environment\Environment;
use Exception;

class CourseFactory extends Factory {

  /**
   * Create Course object given a course id.
   */
  public function get($course_id) {
    $params = $this->data->find('course', array('id' => (int)$course_id));

    if ($params) {
      // Create a Connection object.
      if ($params['host'] == 'localhost') {
        $conn = new LocalServerConn(TRUE);
      }
      else {
        $conn = new SSHServerConn($params['host'], 22,  $params['user'],  $params['pass'], TRUE);
        if (!$conn->connect()) {
          throw new Exception("Unable to connect/login to server $host on port 22");
        }
      }

      // Create a Course object.
      $course = new Course();
      $course->course_type = $params['course_type'];
      $course->course_id = $course_id;
      $course->title = $params['title'];
      $course->description = $params['description'];
      $course->repo = $params['repo'];
      $course->course_name = $params['course_name'];
      $course->uri = '/course/' . $params['id'];

      // Set the resources information for use by the factory method.
      $course->setResources($params['resources']);

      // Create an Environment object.
      $course->env = new Environment($conn, $this->config['debug']);
      $course->env_type = $params['env_type'];

      // Build the Plugins associated with this course.
      if (!isset($params['plugin_ids'])) {
        throw new Exception("The course has no plugins associated with it and cannot be loaded.");
      }
      $this->buildPlugins($course, $params['plugin_ids']);

      return $course;
    }
    else {
      throw new Exception("Course with id $course_id does not exist.");
    }
  }

  /**
   * Attach plugins.
   */
  protected function buildPlugins(&$course, array $plugin_ids = array()) {
    if (!empty($plugin_ids)) {
      $plugins = array();
      foreach ($plugin_ids as $plugin_id) {
        $plugin_data = $this->data->find('plugin', array('_id' => $plugin_id));
        if (!$plugin_data) {
          throw new Exception("The course references a plugin with id \"$plugin_id\" that doesn't exist in the data store.");
        }

        $type = $plugin_data['type'];
        $class = '\TrainingWheels\Plugin\\' . $type . '\\' . $type;
        if (!class_exists($class)) {
          throw new Exception("The plugin with id \"$plugin_id\" has type \"$type\", but this class cannot be loaded at \"$class\".");
        }
        $plugin = new $class();
        $plugin->set($plugin_data);
        $plugins[$type] = $plugin;

        $plugin->mixinEnvironment($course->env, 'linux');
        $plugin->mixinEnvironment($course->env, $course->env_type);

        $plugin->registerCourseObservers($course);
      }
      $course->setPlugins($plugins);
    }
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
    $course['plugin_ids'] = $params['plugin_ids'];
    return $this->data->insert('course', $course);
  }
}
