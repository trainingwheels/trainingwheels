<?php

namespace TrainingWheels\Console;
use TrainingWheels\Job\ResourceJob;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceDelete extends Command
{
  protected function configure() {
    $this->setName('resource:delete')
         ->setDescription('Delete resource(s).')
         ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
         ->addArgument('user_names', InputArgument::REQUIRED,'The user names, comma-separated.')
         ->addArgument('resources', InputArgument::OPTIONAL,'The resource names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('CLI command: ResourceDelete', L_INFO);
    $resources = $input->getArgument('resources');
    $resources = ($resources == 'all' || empty($resources)) ? array() : explode(',', $resources);

    $job = new ResourceJob(
      $input->getArgument('course_id'),
      'resourceDelete',
      array(
        'user_names' => explode(',', $input->getArgument('user_names'),
        'resources' => $resources,
      )
    );
    $job->execute();

    $output->writeln('Resource(s) deleted.');
  }
}
