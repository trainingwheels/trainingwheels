<?php

namespace TrainingWheels\Environment;
use TrainingWheels\Log\Log;
use TrainingWheels\Conn\ServerConn;
use BadMethodCallException;
use ReflectionFunction;
use Exception;

class Environment {

  // Connection to the environment.
  protected $conn;

  // Debug setting.
  private $debug;

  /**
   * Constructor.
   */
  public function __construct(ServerConn $conn, $debug) {
    $this->conn = $conn;
    $this->debug = $debug;
    if (!$this->conn->exec_eq('sudo whoami', 'root')) {
      throw new Exception('The connection needs to have root or sudo access to the server.');
    }
  }

  /**
   * Return the connection instance.
   */
  public function getConn() {
    return $this->conn;
  }

  /**
   * Run the course ansible playbooks.
   */
  public function provision(array $plugins) {
    $ansible_args_array = array(
      '-c local',
      '--sudo',
    );
    $ansible_args = implode(' ', $ansible_args_array);

    // Get the playbooks that need to be run to configure this course.
    foreach ($plugins as $plugin) {
      $play = $plugin->getProvisionSteps();

      if ($play) {
        $type = $plugin->getType();
        $vars = '--extra-vars="' . $plugin->formatAnsibleVars() . '"';
        $command = 'ansible-playbook ' . $ansible_args . ' ' . $vars . ' ' . $play;
        $output = array();
        $return = FALSE;

        Log::log('=====================================================================', L_DEBUG);
        Log::log("$type::configure:exec: $command", L_DEBUG);
        exec($command, $output, $return);
        $output_nice = implode("\n", $output);
        Log::log("$type::configure:resp: $output_nice", L_DEBUG);

        if ($return != 0) {
          throw new Exception("Unable to run configuration for plugin \"$type\", see logs for more info.");
        }
      }
    }
  }

  /**
   * Allows us to dynamically add methods to the Environment, giving
   * mixin-like abilities to the plugins. They can also override or extend
   * other plugin's methods quite easily.
   */
  public function __call($method, $args) {
    if (isset($this->$method)) {
      $func = $this->$method;

      if (is_callable($func)) {
        // In debug mode, ensure that the arguments we're calling the function with
        // are sane. Calling these environment functions with empty strings can cause
        // damage and is almost certainly a bug. PHP isn't strict enough about
        // enforcing certain things, so we have to do it ourselves.
        if ($this->debug) {
          $refl = new ReflectionFunction($func);
          $namespace = $refl->getNamespaceName();
          $full_name = $namespace . '::' . $method;
          if ($refl->getNumberOfRequiredParameters() > count($args)) {
            throw new Exception("Too few parameters passed to \"$full_name\"");
          }
          if (is_array($args) && count($args) > 0) {
            foreach ($args as $arg) {
              if (!is_string($arg) || strlen($arg) < 2) {
                throw new Exception("Invalid string arg passed to \"$full_name\"");
              }
            }
          }
          Log::log($full_name, L_DEBUG);
        }

        return call_user_func_array($func, $args);
      }
      else {
        throw new BadMethodCallException("The method \"$method\" is not callable.");
      }
    }
    else {
      throw new BadMethodCallException("The method \"$method\" does not exist.");
    }
  }
}
