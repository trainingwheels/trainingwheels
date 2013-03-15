<?php

namespace TrainingWheels\Plugin\Supervisor;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Store\DataStore;
use Exception;

abstract class SupervisorProcessResource extends Resource {

  protected $program;
  protected $command;
  protected $directory;
  protected $conf_path;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, DataStore $data, $title, $user_name, $course_name, $res_id, $config) {
    parent::__construct($env, $data, $title, $user_name, $course_name, $res_id);
    $this->program = $res_id;
    $this->conf_path = "/etc/supervisor/conf.d/$this->program.conf";
  }

  /**
   * Is the process already running?
   */
  public function getExists() {
    if (!isset($this->exists)) {
      $this->exists = $this->env->supervisorProgramIsRunning($this->program);
    }
    return $this->exists;
  }

  /**
   * Stop the process, remove the config.
   */
  public function delete() {
    parent::delete();
    if (!$this->getExists()) {
      throw new Exception("Attempting to delete a SupervisorProcessResource that does not exist.");
    }
    $this->env->supervisorProgramStop($this->program);
    $this->env->fileDelete($this->conf_path);
    $this->env->supervisorUpdateConfig();
    $this->exists = FALSE;
  }

  /**
   * Start the process.
   */
  public function create() {
    parent::create();

    // Make the conf file.
    $this->env->fileCreate("\"[program:$this->program]\ncommand=$this->command\ndirectory=$this->directory\nuser=$this->user_name\nautostart=true\nautorestart=true\n\"", $this->conf_path, 'root');

    // Tell Supervisor to reload the config and start the program. Since it's
    // defined with autostart=true above, it will start as soon as the config
    // is read in.
    $this->env->supervisorUpdateConfig();
    $this->exists = TRUE;
  }

  /**
   * Sync to a target. There's nothing to sync, just create the target's process.
   */
  public function syncTo($target) {
    parent::syncTo();
    if (!$target->getExists()) {
      $target->create();
    }
  }

  /**
   * Get info.
   */
  public function get() {
    return parent::get();
  }
}
