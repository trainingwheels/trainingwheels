<?php

namespace TrainingWheels\Resource;
use Exception;

class GitFilesResource extends Resource {

  protected $subdir;
  protected $fullpath;
  protected $repo;
  protected $course_name;
  protected $default_branch;

  /**
   * Constructor.
   */
  public function __construct(\TrainingWheels\Environment\TrainingEnv $env, $res_id, $title, $user_name, $course_name, $subdir, $repo, $default_branch = 'master') {
    parent::__construct($env, $title, $user_name);
    $this->subdir = $subdir;
    $this->fullpath = "/home/$user_name/$subdir";
    $this->repo = $repo;
    $this->course_name = $course_name;
    $this->default_branch = $default_branch;

    $this->cacheBuild($res_id);
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = array(
      'type' => 'gitfiles',
      'exists' => $this->getExists(),
      'title' => $this->title,
    );
    if ($info['exists']) {
      $info['attribs']['branch'] = $this->currentBranch();
      $info['attribs']['changes'] = $this->localChanges();
      $info['attribs']['remote'] = $this->remote();
    }
    return $info;
  }

  /**
   * Get the current branch.
   */
  public function currentBranch() {
    return $this->env->gitBranchGet($this->fullpath);
  }

  /**
   * Get any local changes.
   */
  public function localChanges() {
    $changes = $this->env->gitLocalChanges($this->fullpath);
    return empty($changes) ? FALSE : explode("\n", $changes);
  }

  /**
   * Get the details of the remote.
   */
  public function remote() {
    $remote = $this->env->gitRemote($this->fullpath);
    $remote = str_replace("\t", ' ', $remote);
    return empty($remote) ? FALSE : explode("\n", $remote);
  }

  /**
   * Do the files exist in the environment?
   */
  public function getExists() {
    if (!$this->exists) {
      $this->exists = $this->env->dirExists($this->fullpath);
      $this->cacheSave();
    }
    return $this->exists;
  }

  /**
   * Delete the files.
   */
  public function delete() {
    if (!$this->getExists()) {
      throw new Exception("Attempting to delete a Git files resource that does not exist.");
    }
    $this->env->dirDelete($this->fullpath);
    $this->exists = FALSE;
    $this->cacheSave();
  }

  /**
   * Create the git clone in the correct place.
   */
  public function create() {
    if ($this->getExists()) {
      throw new Exception("Attempting to create a Git files resource that already exists.");
    }
    $this->exists = TRUE;
    $this->env->gitRepoClone($this->user_name, $this->repo, $this->fullpath, $this->default_branch);
    $this->cacheSave();
  }

  /**
   * Sync to a target.
   */
  public function syncTo(GitFilesResource $target) {
    $this->env->fileSyncUserFolder($this->user_name, $target->user_name, $this->subdir . '/');
  }
}
