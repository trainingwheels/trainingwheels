<?php

$whoami = trim(shell_exec('sudo whoami'));
if ($whoami != 'root') {
  print 'Please run this as root or a user with password-less sudo access.' . "\n";
  exit;
}

$autoloader_path = __DIR__.'/../vendor/autoload.php';

if (!file_exists($autoloader_path)) {
  print 'Unable to find the class autoloader, are you sure you have run "composer install"? See README.md for more information.' . "\n";
  exit;
}

require_once $autoloader_path;

use TrainingWheels\Console\ClassroomConfigure;
use TrainingWheels\Console\UserRetrieve;
use TrainingWheels\Console\UserCreate;
use TrainingWheels\Console\UserDelete;
use TrainingWheels\Console\ResourceCreate;
use TrainingWheels\Console\ResourceDelete;
use TrainingWheels\Console\ResourceSync;
use TrainingWheels\Log\Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Symfony\Component\Console\Application;

$formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message% \n");
$stream = new StreamHandler(__DIR__.'/../log/cli.log', Logger::DEBUG);
$stream->setFormatter($formatter);
$log = new Logger('tw');
$log->pushHandler($stream);

Log::$instance = new Log($log);
Log::log('Initializing CLI application', L_INFO);

$application = new Application();
$application->add(new ClassroomConfigure);
$application->add(new UserRetrieve);
$application->add(new UserCreate);
$application->add(new UserDelete);
$application->add(new ResourceCreate);
$application->add(new ResourceDelete);
$application->add(new ResourceSync);
$application->run();
