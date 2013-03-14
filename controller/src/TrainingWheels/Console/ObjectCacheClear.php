<?php

namespace TrainingWheels\Console;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class ObjectCacheClear extends Command {
  private $data;

  public function __construct($data) {
    parent::__construct();
    $this->data = $data;
  }

  protected function configure() {
    $this->setName('objectcache:clear')
         ->setDescription('Clear the object cache.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('ObjectCacheClear', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI'));
    $this->data->remove('cache', array());
    $output->writeln('<info>Object cache cleared.</info>');
  }
}
