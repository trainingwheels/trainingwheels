<?php

namespace TrainingWheels\Resource;
use TrainingWheels\Environment\Environment;
use Exception;

class TextFileResource extends Resource {

  public $file_name;
  public $full_path;
  public $contents;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $res_id, $user_name, $course_name, $data) {
    parent::__construct($env, $title, $user_name);

    $this->file_name = $data['file_name'];
    $this->base_path = $data['base_path'];
    $this->full_path = $data['base_path'] . '/' . $user_name . '/' . $data['file_name'];
    $this->contents = $data['contents'];
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = array(
      'type' => 'textfile',
      'exists' => $this->exists(),
      'title' => $this->title,
    );
    if ($info['exists']) {
      $info['attribs']['contents'] = $this->getContents();
      $info['attribs']['changed'] = $this->changed();
    }
    return $info;
  }

  /**
   * Get the contents.
   */
  public function getContents() {
    return $this->env->fileGetContents($this->full_path);
  }

  /**
   * Has the file been modified?
   */
  public function changed() {
    return $this->getContents() != $this->contents;
  }

  /**
   * Do the files exist in the environment?
   */
  public function exists() {
    return $this->env->fileExists($this->full_path);
  }

  /**
   * Delete the files.
   */
  public function delete() {
    return $this->env->fileDelete($this->full_path);
  }

  /**
   * Create the file in the correct place.
   */
  public function create() {
    if (!$this->exists()) {
      $this->env->fileCreate($this->contents, $this->full_path);
    }
    else {
      throw new Exception("The file $this->file_name already exists.");
    }
  }

  /**
   * Sync to a target.
   */
  public function syncTo(TextFileResource $target) {
    $this->env->fileCopy($this->full_path, $target->full_path);
  }
}
