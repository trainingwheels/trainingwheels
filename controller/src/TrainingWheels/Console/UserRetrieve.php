<?php

namespace TrainingWheels\Console;
use TrainingWheels\Course\CourseFactory;
use TrainingWheels\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserRetrieve extends Command {
  private $courseFactory;

  public function __construct(CourseFactory $courseFactory) {
    parent::__construct();

    $this->courseFactory = $courseFactory;
  }

  protected function configure() {
    $this->setName('user:retrieve')
      ->setDescription('Retrieve user information as JSON')
      ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
      ->addArgument('user_names', InputArgument::REQUIRED,'The user names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $course_id = $input->getArgument('course_id');
    $user_names = $input->getArgument('user_names');

    Log::log('UserRetrieve', L_INFO, 'actions', array('layer' => 'user', 'source' => 'CLI', 'params' => 'course_id=' . $course_id . ' users=' . $user_names));

    $course = $this->courseFactory->get($course_id);
    if ($user_names != 'all') {
      $user_names = explode(',', $user_names);
    }
    else {
      $user_names = '*';
    }
    $users = $course->usersGet($user_names);
    if ($users) {
      $output->writeln(print_r($users, TRUE));
    }
    else {
      $output->writeln("<info>User(s) not found.</info>");
    }
  }
}
