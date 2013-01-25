<?php

namespace TrainingWheels\Console;
use TrainingWheels\Job\JobFactory;
use TrainingWheels\Log\Log;
use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceCreate extends Command {
  private $app;

  public function __construct(Application $app) {
    parent::__construct();

    $this->app = $app;
  }

  protected function configure() {
    $this->setName('resource:create')
      ->setDescription('Create resource(s).')
      ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
      ->addArgument('user_names', InputArgument::REQUIRED,'The user names, comma-separated.')
      ->addArgument('resources', InputArgument::OPTIONAL,'The resource names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('CLI command: ResourceCreate', L_INFO);
    $resources = $input->getArgument('resources');
    $resources = ($resources == 'all' || empty($resources)) ? array() : explode(',', $resources);

    $job = new \stdClass;
    $job->type = 'resource';
    $job->course_id = $input->getArgument('course_id');
    $job->action = 'resourceCreate';
    $job->params = array(
      'user_names' => explode(',', $input->getArgument('user_names')),
      'resources' => $resources,
    );
    $job = JobFactory::singleton($this->app['connections']['mongo'])->save($job);
    $job->execute();
    JobFactory::singleton($this->app['connections']['mongo'])->remove($job->get('id'));

    $output->writeln('Resource(s) created.');
  }
}
