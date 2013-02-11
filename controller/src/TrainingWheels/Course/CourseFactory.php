<?php

namespace TrainingWheels\Course;
use TrainingWheels\Common\Factory;
use TrainingWheels\Conn\LocalServerConn;
use TrainingWheels\Conn\SSHServerConn;
use TrainingWheels\Course\DevCourse;
use TrainingWheels\Course\DrupalCourse;
use TrainingWheels\Course\NodejsCourse;
use TrainingWheels\Environment\DevEnv;
use TrainingWheels\Environment\CentosEnv;
use TrainingWheels\Environment\UbuntuEnv;
use Exception;

class CourseFactory extends Factory {

  /**
   * Create Course object given a course id.
   */
  public function get($course_id) {
    $params = $this->data->find('course', array('id' => (int)$course_id));

    if ($params) {
      $course = $this->buildCourse($params['course_type']);
      $this->buildEnv($course, $params['env_type'], $params['host'], $params['user'], $params['pass']);

      $course->course_id = $course_id;
      $course->title = $params['title'];
      $course->description = $params['description'];
      $course->repo = $params['repo'];
      $course->course_name = $params['course_name'];
      $course->uri = '/course/' . $params['id'];

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
        $plugins[] = $plugin;

        $plugin->mixinEnvironment($course->env, 'ubuntu');
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

  /**
   * Environment buider.
   */
  protected function buildEnv(&$course, $type, $host, $user, $pass) {
    switch ($type) {
      case 'ubuntu':
        if ($host == 'localhost') {
          $conn = new LocalServerConn(TRUE);
        }
        else {
          $conn = new SSHServerConn($host, 22, $user, $pass, TRUE);
          if (!$conn->connect()) {
            throw new Exception("Unable to connect/login to server $host on port 22");
          }
        }
        $course->env = new UbuntuEnv($conn);
        $course->env_type = 'ubuntu';
      break;

      case 'centos':
        $conn = new SSHServerConn($host, 22, $user, $pass, TRUE);
        if (!$conn->connect()) {
          throw new Exception("Unable to connect/login to server $host on port 22");
        }
        $course->env = new CentosEnv($conn);
        $course->env_type = 'centos';
      break;

      default:
        throw new Exception("Environment type $type not found.");
      break;
    }
  }

  /**
   * Course builder.
   */
  protected function buildCourse($type) {
    switch ($type) {
      case 'drupal':
        $course = new DrupalCourse();
        $course->course_type = 'drupal';
      break;

      case 'nodejs':
        $course = new NodejsCourse();
        $course->course_type = 'nodejs';
      break;

      default:
        throw new Exception("Course type $type not found.");
      break;
    }
    return $course;
  }
}
