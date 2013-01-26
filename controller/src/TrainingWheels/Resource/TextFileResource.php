<?php

namespace TrainingWheels\Resource;
use Exception;

class TextFileResource extends Resource {

  public $file_name;
  public $full_path;
  public $contents;

  /**
   * Constructor.
   */
  public function __construct(\TrainingWheels\Environment\TrainingEnv $env, $title, $user_name, $file_name, $base_path, $contents) {
    parent::__construct($env, $title, $user_name);
    $this->file_name = $file_name;
    $this->base_path = $base_path;
    $this->full_path = $base_path . '/' . $user_name . '/' . $file_name;
    $this->contents = $contents;
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
      $this->env->filePutContents($this->full_path, $this->contents);
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
