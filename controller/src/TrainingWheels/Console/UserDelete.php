<?php

namespace TrainingWheels\Console;
use TrainingWheels\Course\CourseFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserDelete extends Command
{
  protected function configure() {
    $this->setName('user:delete')
         ->setDescription('Delete user(s).')
         ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
         ->addArgument('user_names', InputArgument::REQUIRED,'The user names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $course = CourseFactory::singleton()->get($input->getArgument('course_id'));
    $user_names = $input->getArgument('user_names');

    $result = $course->usersDelete(explode(',', $user_names));
    $output->writeln("User(s) deleted.");
  }
}
