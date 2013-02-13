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
