<?php

namespace TrainingWheels\Plugin\GitFiles;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Environment\Environment;
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
  public function __construct(Environment $env, $title, $user_name, $course_name, $res_id, $data) {
    parent::__construct($env, $title, $user_name, $course_name, $res_id);

    $this->subdir = $data['subdir'];
    $this->fullpath = "/twhome/$user_name/$course_name";
    if ($this->subdir) {
      $this->fullpath = $this->fullpath . '/' . $this->subdir;
    }
    $this->repo = $data['repo_url'];
    $this->course_name = $course_name;
    $this->default_branch = $data['default_branch'];

    $this->cacheBuild($res_id);
  }

  /**
   * Get the configuration options for instances of this resource.
   */
  public static function getResourceVars() {
    return array(
      array(
        'key' => 'default_branch',
        'val' => 'master',
        'help' => 'The branch that will be automatically checked out when the repository is cloned.',
      ),
      array(
        'key' => 'subdir',
        'val' => '',
        'help' => 'The subdirectory into which the clone is created, leaving this blank will result in home/user/course being the clone directory',
      ),
      array(
        'key' => 'repo_url',
        'val' => NULL,
        'help' => 'The Github URL to clone',
        'hint' => 'https://github.com/fourkitchens/trainingwheels-drupal-files-example.git',
      ),
    );
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = parent::get();
    if ($info['exists']) {
      $info['attribs'][0]['key'] = 'branch';
      $info['attribs'][0]['title'] = 'Branch';
      $info['attribs'][0]['value'] = $this->currentBranch();
      $info['attribs'][1]['key'] = 'changes';
      $info['attribs'][1]['title'] = 'Local changes';
      $info['attribs'][1]['value'] = $this->localChanges();
      $info['attribs'][2]['key'] = 'remote';
      $info['attribs'][2]['title'] = 'Remote repositories';
      $info['attribs'][2]['value'] = $this->remote();
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
      throw new Exception("Attempting to delete a GitFilesResource that does not exist.");
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
      throw new Exception("Attempting to create a GitFilesResource that already exists.");
    }
    $this->exists = TRUE;
    $this->env->gitRepoClone($this->user_name, $this->repo, $this->fullpath, $this->default_branch);
    $this->cacheSave();
  }

  /**
   * Sync to a target.
   */
  public function syncTo(GitFilesResource $target) {
    $this->env->fileSyncUserFolder($this->user_name, $target->user_name, $this->course_name . '/');
  }
}
