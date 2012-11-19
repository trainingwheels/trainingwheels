<?php

namespace TrainingWheels\Course;
use TrainingWheels\User\DevUser;
use TrainingWheels\Resource\TextFileResource;

class DevCourse extends TrainingCourse {

  public $base_path;

  /**
   * Constructor.
   */
  public function __construct($base_path) {
    $this->base_path = $base_path;
  }

  /**
   * Factory that creates new user objects for this course.
   */
  protected function userFactory($user_name) {
    $user_obj = new DevUser($this->env, $user_name, $this->course_name);

    $code = <<<'EOT'
(function($) {
  alert('awesome js');
})(jQuery);
EOT;

    $readme = <<<'EOT'
This is a piece of Javascript that you should study intently.
EOT;

    $user_obj->resources = array(
      'code_file' => new TextFileResource($this->env, 'Code', $user_name, 'mycode.js', $this->base_path, $code),
      'readme_file' => new TextFileResource($this->env, 'Readme', $user_name, 'README.txt', $this->base_path, $readme),
    );

    return $user_obj;
  }
}