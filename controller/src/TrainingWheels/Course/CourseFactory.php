<?php

namespace TrainingWheels\Course;
use TrainingWheels\Common\Factory;
use TrainingWheels\Course\DevCourse;
use TrainingWheels\Course\DrupalCourse;
use TrainingWheels\Course\NodejsCourse;
use TrainingWheels\Store\DataStore;
use Exception;

class CourseFactory extends Factory {
  // Singleton instance.
  protected static $instance;

  /**
   * Return the singleton.
   */
  public static function singleton() {
    if (!isset(self::$instance)) {
      $className = get_called_class();
      self::$instance = new $className;
      self::$instance->data = new DataStore();
    }
    return self::$instance;
  }

  /**
   * Create Course object given a course id.
   */
  public function get($course_id) {
    $params = $this->data->find('course', $course_id);

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
}
