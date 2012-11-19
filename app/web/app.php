<?php

require_once __DIR__.'/../vendor/autoload.php';

use TrainingWheels\Course\CourseFactory;

$app = new Silex\Application();

$app->get('/hello/{name}', function ($name) use ($app) {
  $cf = new CourseFactory();
  $course = $cf->get(1);
  return $app->json(array('one' => 'Hello ' . $app->escape($name)));
});

$app->run();
