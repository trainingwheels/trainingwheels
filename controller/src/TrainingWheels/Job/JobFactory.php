<?php

namespace TrainingWheels\Job
use TrainingWheels\Common\Factory;
use TrainingWheels\Job\UserJob;
use Exception;

class JobFactory extends Factory {
  /**
   * Create a Job object given a job id.
   */
  public function get($job_id) {
    $params = $this->data->find('_id', $job_id);

    if ($params) {
      $job = $this->buildJob($params['type']);
      $this->buildEnv($job, $params['env_type'], $params['host'], $params['user'], $params['pass']);

      $job->_id = $params['_id'];
      $job->action = $params['action'];
      $job->course_id = $params['course_id'];
      $job->course_name = $params['course_name'];
      $job->params = $params['params'];

      return $job;
    }

    return FALSE;
  }

  /**
   * Save a job.
   */
  public function save($job) {
    return $this->data->insert('job', $job);
  }

  /**
   * Job builder.
   */
  protected function buildJob($type) {
    switch ($type) {
      case 'user':
        $job = new UserJob();
        $job->type = 'user';
      break;

      default:
        throw new Exception("Job type $type not found.");
      break;
    }

    return $job;
  }
}
