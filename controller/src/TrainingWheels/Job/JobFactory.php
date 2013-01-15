<?php

namespace TrainingWheels\Job;
use TrainingWheels\Common\Factory;
use TrainingWheels\Job\ResourceJob;
use TrainingWheels\Store\DataStore;
use Exception;

class JobFactory extends Factory {
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
   * Create a Job object given a job id.
   */
  public function get($job_id) {
    $params = $this->data->find('_id', $job_id);

    if ($params) {
      $job = new \stdClass;
      $job->_id = $params['_id'];
      $job->course_id = $params['course_id'];
      $job->type = $params['type'];
      $job->action = $params['action'];
      $job->params = $params['params'];

      $this->buildJob($job);
      return $job;
    }

    return FALSE;
  }

  /**
   * Save a job.
   */
  public function save($job) {
    return $this->buildJob($this->data->insert('job', $job, FALSE));
  }

  /**
   * Job builder.
   */
  protected function buildJob($job) {
    switch ($job->type) {
      case 'resource':
        $job = new ResourceJob($job->course_id, $job->action, $job->params);
      break;

      default:
        throw new Exception("Job type $type not found.");
      break;
    }

    return $job;
  }
}
