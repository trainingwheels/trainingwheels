<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = TRUE;
$app->mount('/tw/rest', new TrainingWheels\Controller\REST());
$app->run();
