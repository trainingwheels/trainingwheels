<?php

namespace TrainingWheels\Console;
use TrainingWheels\Course\CourseFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceSync extends Command
{
  protected function configure() {
    $this->setName('resource:sync')
         ->setDescription('Sync resource(s).')
         ->addArgument('course_id', InputArgument::REQUIRED,'The course id.')
         ->addArgument('source_user', InputArgument::REQUIRED,'The source user name.')
         ->addArgument('target_users', InputArgument::REQUIRED,'The target user names, comma-separated.')
         ->addArgument('resources', InputArgument::OPTIONAL,'The resource names, comma-separated.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $course = CourseFactory::singleton()->get($input->getArgument('course_id'));
    $source_user = $input->getArgument('source_user');
    $target_users = $input->getArgument('target_users');

    $resources = $input->getArgument('resources');
    $resources = ($resources == 'all' || empty($resources)) ? '*' : explode(',', $resources);

    $course->usersResourcesSync($source_user, explode(',', $target_users), $resources);
    $output->writeln('User(s) synced.');
  }
}
