<?php

$whoami = trim(shell_exec('sudo whoami'));
if ($whoami != 'root') {
  print 'Please run this as root or a user with password-less sudo access.' . "\n";
  exit(1);
}

$autoloader_path = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader_path)) {
  print 'Unable to find the class autoloader, are you sure you have run "composer install"? See README.md for more information.' . "\n";
  exit(1);
}
require_once $autoloader_path;

use TrainingWheels\Common\BootstrapServiceProvider;

/**
 * Create the main Silex application.
 */
$app = new Silex\Application();

/**
 * Inject the Training Wheels application as a provider in Silex.
 */
try {
  $app->register(new BootstrapServiceProvider());
}
catch (Exception $e) {
  print $e->getMessage();
  exit(1);
}
TrainingWheels\Log\Log::log('Initialized console application', L_INFO);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TrainingWheels\Console\CourseProvision;
use TrainingWheels\Console\ResourceCreate;
use TrainingWheels\Console\ResourceDelete;
use TrainingWheels\Console\ResourceSync;
use TrainingWheels\Console\UserCreate;
use TrainingWheels\Console\UserDelete;
use TrainingWheels\Console\UserRetrieve;
use TrainingWheels\Console\KeyCreate;
use TrainingWheels\Console\LogClear;
use TrainingWheels\Console\MongoCLI;
use TrainingWheels\Console\ObjectCacheClear;

$console = new Application();
$console->add(new CourseProvision($app['tw.job_factory']));
$console->add(new UserRetrieve($app['tw.course_factory']));
$console->add(new UserCreate($app['tw.course_factory']));
$console->add(new UserDelete($app['tw.course_factory']));
$console->add(new ResourceCreate($app['tw.job_factory']));
$console->add(new ResourceDelete($app['tw.job_factory']));
$console->add(new ResourceSync($app['tw.job_factory']));
$console->add(new KeyCreate($app['tw.config']));
$console->add(new LogClear($app['tw.log']));
$console->add(new MongoCLI($app['tw.config']));
$console->add(new ObjectCacheClear($app['tw.datastore']));

$console->run();
