<?php

namespace TrainingWheels\Job;
use TrainingWheels\Common\Factory;
use TrainingWheels\Job\ResourceJob;
use TrainingWheels\Store\DataStore;
use MongoId;
use Exception;

class JobFactory extends Factory {
  // Singleton instance.
  protected static $instance;

  /**
   * Return the singleton.
   */
  public static function singleton($dbUrl) {
    if (!isset(self::$instance)) {
      $className = get_called_class();
      self::$instance = new $className;
      self::$instance->data = new DataStore($dbUrl);
    }
    return self::$instance;
  }

  /**
   * Create a Job object given a job id.
   */
  public function get($job_id) {
    $params = $this->data->find('job', array('_id' => new MongoId($job_id)));

    if ($params) {
      $job = new \stdClass;
      $job->job_id = $params['job_id'];
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
    $job = $this->data->insert('job', $job, FALSE);
    $job->job_id = $job->_id->__toString();
    unset($job->_id);
    return $this->buildJob($job);
  }

  /**
   * Delete a job.
   */
  public function remove($job_id) {
    $this->data->remove('job', array('_id' => new MongoId($job_id)));
  }

  /**
   * Job builder.
   */
  protected function buildJob($job) {
    switch ($job->type) {
      case 'resource':
        $job = new ResourceJob($job->job_id, $job->course_id, $job->action, $job->params);
      break;

      default:
        throw new Exception("Job type $type not found.");
      break;
    }

    return $job;
  }
}
