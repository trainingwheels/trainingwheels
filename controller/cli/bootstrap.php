<?php

$autoloader_path = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloader_path)) {
  print 'Unable to find the class autoloader, are you sure you have run "composer install"? See README.md for more information.';
  print "\n";
  exit(1);
}

require_once $autoloader_path;

use TrainingWheels\Log\Log;
use Monolog\Logger;

$app = new Silex\Application();

/**
 * Include config.
 */
$app->register(New Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/config.yml'));

/**
 * Currently we have monolog logging from both a Silex Provider for
 * the web app messages, and internally in Training Wheels from the
 * Log class.
 */
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../log/messages.log',
    'monolog.name' => 'tw',
    'monolog.level' => $app['debug'] ? Logger::DEBUG : Logger::INFO,
));

return $app;
