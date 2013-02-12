<?php

namespace TrainingWheels\Course;
use TrainingWheels\User\LAMPUser;
use TrainingWheels\Common\Util;
use TrainingWheels\Resource\GitFilesResource;
use TrainingWheels\Resource\MySQLDatabaseResource;

class DrupalCourse extends TrainingCourse {

  // The repository that contains the source.
  public $repo;

  /**
   * Factory that creates new user objects for this course.
   */
  protected function userFactory($user_name) {
    $user_id = $this->course_id . '-' . $user_name;
    $files_res_id = $user_id . '-drupal_files';
    $db_res_id = $user_id . '-drupal_db';

    $user_obj = new LAMPUser($this->env, $user_name, $user_id);
    $user_obj->resources = array(
      'drupal_files' => new GitFilesResource($this->env, $files_res_id, 'Code', $user_name, $this->course_name, $this->course_name, $this->repo),
      'drupal_db' => new MySQLDatabaseResource($this->env, $db_res_id, 'Database', $user_name, $this->course_name, "/twhome/$user_name/$this->course_name/database.sql.gz"),
    );

    return $user_obj;
  }
}
