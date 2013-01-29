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
use TrainingWheels\Store\DataStore;
use Exception;

class CourseFactory extends Factory {
  /**
   * Constructor.
   */
  public function __construct($dbUrl) {
    parent::__construct($dbUrl);
  }

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

      return $course;
    }

    return FALSE;
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
    return $this->data->insert('course', $course);
  }

  /**
   * Environment buider.
   */
  protected function buildEnv(&$object, $type, $host, $user, $pass) {
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
        $object->env = new UbuntuEnv($conn);
        $object->env_type = 'ubuntu';
      break;

      case 'centos':
        $conn = new SSHServerConn($host, 22, $user, $pass, TRUE);
        if (!$conn->connect()) {
          throw new Exception("Unable to connect/login to server $host on port 22");
        }
        $object->env = new CentosEnv($conn);
        $object->env_type = 'centos';
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
