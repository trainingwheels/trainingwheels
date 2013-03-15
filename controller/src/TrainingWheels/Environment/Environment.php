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
   * Provision the course.
   */
  public function provision(array $plugins) {
    $tmp = '';
    $ansible_args_array = array('--sudo');
    if (get_class($this->conn) == 'TrainingWheels\Conn\LocalServerConn') {
      $ansible_args_array[] = '-c local';
    }
    else {
      $tmp = trim(shell_exec('mktemp'));
      $host = $this->conn->getHost();
      shell_exec("echo $host > $tmp");
      $ansible_args_array[] = '--inventory-file=' . $tmp;
      $ansible_args_array[] = '--private-key=' . $this->conn->getKeyPath();
      $ansible_args_array[] = '--user=' . $this->conn->getUser();
    }
    $ansible_args = implode(' ', $ansible_args_array);

    // Get the playbooks that need to be run to configure this course.
    foreach ($plugins as $plugin) {
      $play = $plugin->getProvisionSteps();

      if ($play) {
        $type = $plugin->getType();
        $vars = '--extra-vars="' . $plugin->formatVarsString() . '"';
        $command = 'ansible-playbook ' . $ansible_args . ' ' . $vars . ' ' . $play;
        $output = array();
        $return = FALSE;
        $start_time = microtime(TRUE);
        exec($command, $output, $return);
        $end_time = microtime(TRUE);

        $vars_string = $plugin->formatVarsString();
        $args_array = $vars_string == '' ? array() : explode(' ', $vars_string);
        $context = array(
          'layer' => 'env',
          'source' => $type,
          'commands' => array_merge(array('ansible-playbook', $ansible_args), $args_array),
          'result' => implode('<br />', $output),
          'time' => $end_time - $start_time,
        );
        Log::log('Provision', L_DEBUG, 'actions', $context);

        if ($return != 0) {
          throw new Exception("Unable to run configuration for plugin \"$type\": \n$output_nice");
        }
      }
    }
    if (!empty($tmp)) {
      shell_exec("rm $tmp");
    }
  }

  /**
   * Allows us to dynamically add methods to the Environment, giving
   * mixin-like abilities to the plugins. They can also override or extend
   * other plugin's methods quite easily.
   */
  public function __call($method, $args) {
    if (isset($this->$method) && is_callable($this->$method)) {
      $func = $this->$method;
      // In debug mode, ensure that the arguments we're calling the function with
      // are sane. Calling these environment functions with empty strings can cause
      // damage and is almost certainly a bug. PHP isn't strict enough about
      // enforcing certain things, so we have to do it ourselves.
      if ($this->debug) {
        $refl = new ReflectionFunction($func);
        $namespace = $refl->getNamespaceName();
        $plugin_name_pieces = explode('\\', $namespace);
        $plugin_name = $plugin_name_pieces[2];
        $full_name = $plugin_name . '::' . $method;
        if ($refl->getNumberOfRequiredParameters() > count($args)) {
          throw new Exception("Too few parameters passed to \"$full_name\"");
        }
        if (is_array($args) && count($args) > 0) {
          foreach ($args as $arg) {
            if (!is_string($arg)) {
              throw new Exception("Argument passed to \"$full_name\" is not a string.");
            }
            else if (strlen($arg) < 2) {
              throw new Exception("Argument passed to \"$full_name\" is less than 2 chars in length");
            }
          }
        }

        Log::log('Call env function', L_DEBUG, 'actions', array('layer' => 'app', 'source' => 'Environment', 'params' => $full_name));
      }

      return call_user_func_array($func, $args);
    }
    else {
      throw new BadMethodCallException("The method \"$method\" does not exist or is not callable.");
    }
  }
}
