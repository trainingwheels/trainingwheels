<?php

namespace TrainingWheels\Console;
use TrainingWheels\Job\JobFactory;
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

    $job = new \stdClass;
    $job->type = 'resource';
    $job->course_id = $input->getArgument('course_id');
    $job->action = 'resourceDelete';
    $job->params = array(
      'user_names' => explode(',', $input->getArgument('user_names')),
      'resources' => $resources,
    );
    $job = JobFactory::singleton()->save($job);
    try {
      $job->execute();
      JobFactory::singleton()->remove($job->get('id'));
    }
    catch (Exception $e) {
      JobFactory::singleton()->remove($job->get('id'));
      throw $e;
    }

    $output->writeln('<info>Resource(s) deleted.</info>');
  }
}
