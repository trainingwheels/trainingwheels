<?php

namespace TrainingWheels\Console;
use TrainingWheels\Job\ResourceJob;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceSync extends Command
{
  protected function configure() {
    $this->setName('resource:sync')
         ->setDescription('Sync resource(s).')
         ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
         ->addArgument('source_user', InputArgument::REQUIRED,'The source user name.')
         ->addArgument('target_users', InputArgument::REQUIRED,'The target user names, comma-separated.')
         ->addArgument('resources', InputArgument::OPTIONAL,'The resource names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('CLI command: ResourceSync', L_INFO);
    $resources = $input->getArgument('resources');
    $resources = ($resources == 'all' || empty($resources)) ? array() : explode(',', $resources);
    $job = new ResourceJob(
      $input->getArgument('course_id'),
      'resourceSync',
      array(
        'source_user' => $input->getArgument('source_user'),
        'target_users' => explode(',', $input->getArgument('target_users')),
        'resources' => $resources,
      )
    );;
    $job->execute();
    $output->writeln('User(s) synced.');
  }
}
