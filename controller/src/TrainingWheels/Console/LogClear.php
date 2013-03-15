<?php

namespace TrainingWheels\Console;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class LogClear extends Command {
  private $log;

  public function __construct($log) {
    parent::__construct();
    $this->log = $log;
  }

  protected function configure() {
    $this->setName('log:clear')
         ->setDescription('Clear the MongoDB logs.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('LogClear', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI'));
    $this->log->removeDBLogs();
    $output->writeln('<info>Logs cleared.</info>');
  }
}
