<?php

namespace TrainingWheels\Console;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class MongoCLI extends Command {
  private $config;

  public function __construct($config) {
    parent::__construct();
    $this->config = $config;
  }

  protected function configure() {
    $this->setName('mongo:cli')
         ->setDescription('Drop into a MongoDB CLI authenticated to the TrainingWheels DB.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('MongoCLI', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI'));

    $url_parts = parse_url($this->config['connections']['mongo']);
    $cmd = 'mongo -u ' . $url_parts['user'] . ' -p ' . $url_parts['pass'] . ' ' . ltrim($url_parts['path'], '/');

    // This code courtesy of drush_shell_proc_open().
    $process = proc_open($cmd, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes);
    $proc_status = proc_get_status($process);
    $exit_code = proc_close($process);
    $out = $proc_status["running"] ? $exit_code : $proc_status["exitcode"];

    $output->writeln('<info>MongoDB CLI session ended.</info>');
  }
}
