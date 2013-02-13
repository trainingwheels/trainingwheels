<?php

namespace TrainingWheels\Console;
use TrainingWheels\Job\JobFactory;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class CourseProvision extends Command {
  private $jobFactory;

  public function __construct(JobFactory $jobFactory) {
    parent::__construct();

    $this->jobFactory = $jobFactory;
  }

  protected function configure() {
    $this->setName('course:provision')
         ->setDescription('Provision course.')
         ->addArgument('course_id', InputArgument::REQUIRED,'The course id.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('CLI command: CourseProvision', L_INFO);

    $job = new \stdClass;
    $job->type = 'course';
    $job->course_id = $input->getArgument('course_id');
    $job->action = 'courseProvision';
    $job->params = array();
    $job = $this->jobFactory->save($job);
    try {
      $job->execute();
      $this->jobFactory->remove($job->get('id'));
    }
    catch (Exception $e) {
      $this->jobFactory->remove($job->get('id'));
      throw $e;
    }
    $output->writeln('<info>Course provisioned.</info>');
  }
}
