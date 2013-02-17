<?php

namespace TrainingWheels\Plugin\Supervisor;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Environment\Environment;
use Exception;

abstract class SupervisorProcessResource extends Resource {

  protected $program;
  protected $command;
  protected $directory;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $user_name, $course_name, $res_id, $data) {
    parent::__construct($env, $title, $user_name, $course_name, $res_id);
    $this->program = $res_id;
  }

  /**
   * Is the process already running?
   */
  public function getExists() {
    if (!$this->exists) {
      $this->exists = $this->env->supervisorProgramIsRunning($this->program);
      $this->cacheSave();
    }
    return $this->exists;
  }

  /**
   * Stop the process.
   */
  public function delete() {
    // if (!$this->getExists()) {
    //   throw new Exception("Attempting to delete a SupervisorProcessResource that does not exist.");
    // }
    // $this->env->dirDelete($this->fullpath);
    // $this->exists = FALSE;
    // $this->cacheSave();
  }

  /**
   * Start the process.
   */
  public function create() {
    // Make the conf file.
    $this->env->fileCreate("\"[program:$this->program]\ncommand=$this->command\ndirectory=$this->directory\nuser=$this->user_name\nautostart=false\nautorestart=true\n\"", "/etc/supervisor/conf.d/$this->program.conf", 'root');

    // Tell Supervisor to reload the config and start the program.
    $this->env->supervisorUpdateConfig();
    $this->env->supervisorProgramStart($this->program);
  }

  /**
   * Sync to a target - noop by default.
   */
  public function syncTo($target) {
    return TRUE;
  }
}
