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

class ClassroomConfigure extends Command {
  private $jobFactory;

  public function __construct(JobFactory $jobFactory) {
    parent::__construct();

    $this->jobFactory = $jobFactory;
  }

  protected function configure() {
    $this->setName('classroom:configure')
         ->setDescription('Configure classroom.')
         ->addArgument('course_id', InputArgument::REQUIRED,'The course id.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    Log::log('CLI command: ClassroomConfigure', L_INFO);

    $job = new \stdClass;
    $job->type = 'classroom';
    $job->course_id = $input->getArgument('course_id');
    $job->action = 'classroomConfigure';
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
    $output->writeln('<info>Classroom configured.</info>');
  }
}
