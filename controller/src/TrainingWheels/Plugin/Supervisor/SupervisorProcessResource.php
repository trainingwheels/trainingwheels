<?php

namespace TrainingWheels\Plugin\Supervisor;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Environment\Environment;
use Exception;

abstract class SupervisorProcessResource extends Resource {

  protected $program;
  protected $command;
  protected $directory;
  protected $conf_path;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $user_name, $course_name, $res_id, $data) {
    parent::__construct($env, $title, $user_name, $course_name, $res_id);
    $this->program = $res_id;
    $this->conf_path = "/etc/supervisor/conf.d/$this->program.conf";
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
   * Stop the process, remove the config.
   */
  public function delete() {
    $this->env->supervisorProgramStop($this->program);
    $this->env->fileDelete($this->conf_path);
    $this->env->supervisorUpdateConfig();
  }

  /**
   * Start the process.
   */
  public function create() {
    // Make the conf file.
    $this->env->fileCreate("\"[program:$this->program]\ncommand=$this->command\ndirectory=$this->directory\nuser=$this->user_name\nautostart=false\nautorestart=true\n\"", $this->conf_path, 'root');

    // Tell Supervisor to reload the config and start the program.
    $this->env->supervisorUpdateConfig();
    $this->env->supervisorProgramStart($this->program);
  }

  /**
   * Sync to a target. There's nothing to sync, just create the target's process.
   */
  public function syncTo($target) {
    if (!$target->getExists()) {
      $target->create();
    }
  }
}
