<?php

namespace TrainingWheels\Plugin\Supervisor;

class SupervisorLinuxEnv {

  public function mixinLinuxEnv($env) {
    $conn = $env->getConn();

    /**
     * Is a supervisor program running?
     */
    $env->supervisorProgramIsRunning = function($program) use ($conn) {
      $running = $conn->exec_get("supervisorctl status $program | grep -q 'RUNNING' && echo 'program_running'");
      if ($running == 'program_running') {
        return TRUE;
      }
      return FALSE;
    };

    /**
     * Update the config.
     */
    $env->supervisorUpdateConfig = function() use ($conn) {
      $conn->exec_get("supervisorctl reread");
      $conn->exec_get("supervisorctl update");
    };

    /**
     * Start a program.
     */
    $env->supervisorProgramStart = function($program) use ($conn) {
      $conn->exec_get("supervisorctl start $program");
    };

    /**
     * Stop a program.
     */
    $env->supervisorProgramStop = function($program) use ($conn) {
      $conn->exec_get("supervisorctl stop $program");
    };
  }
}
