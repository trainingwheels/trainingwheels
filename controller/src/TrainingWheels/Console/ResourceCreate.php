<?php

namespace TrainingWheels\Console;
use TrainingWheels\Job\JobFactory;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use stdClass;

class ResourceCreate extends Command {
  private $jobFactory;

  public function __construct(JobFactory $jobFactory) {
    parent::__construct();

    $this->jobFactory = $jobFactory;
  }

  protected function configure() {
    $this->setName('resource:create')
      ->setDescription('Create resource(s).')
      ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
      ->addArgument('user_names', InputArgument::REQUIRED,'The user names, comma-separated.')
      ->addArgument('resources', InputArgument::OPTIONAL,'The resource names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $resources = $input->getArgument('resources');
    $course_id = $input->getArgument('course_id');
    $user_names = $input->getArgument('user_names');
    $res_pretty = empty($resources) ? 'all' : $resources;
    $params = "course_id=$course_id user_names=$user_names resources=" . $res_pretty;

    Log::log('ResourceCreate', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI', 'params' => $params));

    $resources = ($resources == 'all' || empty($resources)) ? array() : explode(',', $resources);
    $job = new stdClass;
    $job->type = 'resource';
    $job->course_id = $input->getArgument('course_id');
    $job->action = 'resourceCreate';
    $job->params = array(
      'user_names' => explode(',', $input->getArgument('user_names')),
      'resources' => $resources,
    );
    $job = $this->jobFactory->save($job);
    try {
      $job->execute();
      $this->jobFactory->remove($job->get('id'));
    }
    catch (Exception $e) {
      $this->jobFactory->remove($job->get('id'));
      throw $e;
    }

    $output->writeln('<info>Resource(s) created.</info>');
  }
}
