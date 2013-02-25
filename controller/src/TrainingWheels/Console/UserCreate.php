<?php

namespace TrainingWheels\Console;
use TrainingWheels\Course\CourseFactory;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserCreate extends Command {
  private $courseFactory;

  public function __construct(CourseFactory $courseFactory) {
    parent::__construct();

    $this->courseFactory = $courseFactory;
  }

  protected function configure() {
    $this->setName('user:create')
      ->setDescription('Create user(s).')
      ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
      ->addArgument('user_names', InputArgument::REQUIRED,'The user names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $course_id = $input->getArgument('course_id');
    $user_names = $input->getArgument('user_names');

    Log::log('UserCreate', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI', 'params' => 'course_id=' . $course_id . ' users=' . $user_names));

    $course = $this->courseFactory->get($course_id);
    $result = $course->usersCreate(explode(',', $user_names));
    if (!$result) {
      return $output->writeln('<comment>User already exists.</comment>');
    }
    $output->writeln("<info>User(s) created.</info>");
  }
}
