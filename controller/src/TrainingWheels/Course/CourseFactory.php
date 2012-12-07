<?php

namespace TrainingWheels\Course;
use TrainingWheels\Course\DevCourse;
use TrainingWheels\Course\DrupalCourse;
use TrainingWheels\Course\NodejsCourse;
use TrainingWheels\Conn\LocalServerConn;
use TrainingWheels\Conn\SSHServerConn;
use TrainingWheels\Environment\DevEnv;
use TrainingWheels\Environment\CentosEnv;
use TrainingWheels\Environment\UbuntuEnv;
use TrainingWheels\Store\DataStore;
use Exception;

class CourseFactory {
  // Singleton instance.
  protected static $instance;
  protected static $data;

  /**
   * Return the singleton.
   */
  public static function singleton() {
    if (!isset(self::$instance)) {
      $className = __CLASS__;
      self::$instance = new $className;
      self::$instance->data = new DataStore();
    }
    return self::$instance;
  }

  /**
   * Create Course object given a course id.
   */
  public function get($course_id) {
    $params = $this->data->get('course', $course_id);

    if ($params) {
      $course = $this->buildCourse($params['course']);
      $this->buildEnv($course, $params['env'], $params['host'], $params['user'], $params['pass']);

      $course->course_id = $course_id;
      $course->title = $params['title'];
      $course->description = $params['description'];
      $course->repo = $params['repo'];
      $course->course_name = $params['course_name'];
      $course->uri = '/course/' . $params['id'];

      return $course;
    }

    return FALSE;
  }

  /**
   * Get all course summaries.
   */
  public function getAll() {

  }

  /**
   * Save a course.
   */
  public function save($course) {
    $this->data->save('course', $course);
  }

  /**
   * Dummy data.
   */
  protected function dummyCourse($course_id) {
    return array(
      'id' => 1,
      'title' => 'My Course',
      'description' => 'This is a dummy course called mycourse',
      'course' => 'drupal',
      'env' => 'ubuntu',
      'repo' => 'https://github.com/fourkitchens/trainingwheels-drupal-files-example.git',
      'course_name' => 'mycourse',
      'host' => 'localhost',
      'user' => '',
      'pass' => '',
    );
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

      case 'drupal-multisite':
        $course = new DrupalMultiSiteCourse();
        $course->course_type = 'drupal-multisite';
      break;

      case 'dev':
        $base_path = '/root/tw';
        $course = new DevCourse($base_path);
        $course->course_type = 'dev';
      break;

      default:
        throw new Exception("Course type $type not found.");
      break;
    }
    return $course;
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

      case 'ubuntu-local':
        $conn = new LocalServerConn(TRUE);
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

      case 'dev':
        $conn = new LocalServerConn(TRUE);
        $base_path = '/root/tw';
        $course->env = new DevEnv($conn, $base_path);
        $course->env_type = 'dev';
      break;

      default:
        throw new Exception("Environment type $type not found.");
      break;
    }
  }
}
