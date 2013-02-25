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

class ResourceSync extends Command {
  private $jobFactory;

  public function __construct(JobFactory $jobFactory) {
    parent::__construct();

    $this->jobFactory = $jobFactory;
  }

  protected function configure() {
    $this->setName('resource:sync')
      ->setDescription('Sync resource(s).')
      ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
      ->addArgument('source_user', InputArgument::REQUIRED,'The source user name.')
      ->addArgument('target_users', InputArgument::REQUIRED,'The target user names, comma-separated.')
      ->addArgument('resources', InputArgument::OPTIONAL,'The resource names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $resources = $input->getArgument('resources');
    $course_id = $input->getArgument('course_id');
    $source_user = $input->getArgument('source_user');
    $target_users = $input->getArgument('target_users');
    $res_pretty = empty($resources) ? 'all' : $resources;
    $params = "course_id=$course_id source_user=$source_user target_users=$target_users resources=" . $res_pretty;

    Log::log('ResourceSync', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI', 'params' => $params));

    $resources = ($resources == 'all' || empty($resources)) ? array() : explode(',', $resources);
    $job = new stdClass;
    $job->type = 'resource';
    $job->course_id = $course_id;
    $job->action = 'resourceSync';
    $job->params = array(
      'source_user' => $source_user,
      'target_users' => explode(',', $target_users),
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

    $output->writeln('<info>Resource(s) synced.</info>');
  }
}
