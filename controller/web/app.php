<?php

require_once __DIR__.'/../vendor/autoload.php';

use TrainingWheels\Log\Log;
use Monolog\Logger;

$app = new Silex\Application();
$app['debug'] = TRUE;

/**
 * Use Twig for templating, although the majority is done client-side.
 */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__ . '/',
));

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
Log::$instance = new Log($app['monolog']);
Log::log('Initializing web application', L_INFO);

/**
 * The REST service endpoints.
 */
$app->mount('/rest', new TrainingWheels\Controller\REST());

/**
 * Client-side JavaScript includes.
 */
$jsGet = function($debug) {
  $js = array(
    '/js/src/app.js?v=' . time(),
    '/js/src/jquery_plugins.js',
  );
  if (!$debug) {
    $js_min = array(
      '/js/vendor/jquery/jquery-1.8.3.min.js',
      '/js/vendor/underscore/underscore-min.js',
      '/js/vendor/alertify/alertify.min.js',
      '/js/vendor/handlebars/handlebars-1.0.rc.1.min.js',
      '/js/vendor/ember/ember-1.0.0-pre.2.min.js',
      '/js/vendor/ember-data/ember-data.min.js',
    );
    $js = array_merge($js_min, $js);
  }
  else {
    $js_full = array(
      '/js/vendor/jquery/jquery-1.8.3.js',
      '/js/vendor/underscore/underscore.js',
      '/js/vendor/alertify/alertify.js',
      '/js/vendor/handlebars/handlebars-1.0.rc.1.js',
      '/js/vendor/ember/ember-1.0.0-pre.2.js',
      '/js/vendor/ember-data/ember-data.js',
    );
    $js = array_merge($js_full, $js);
  }
  return $js;
};

/**
 * Client-side Handlebars templates.
 */
$tplGet = function() {
  $tpl_files = scandir(__DIR__ . '/tpl');
  $templates = array();
  foreach ($tpl_files as $tpl) {
    if ($tpl != '.' && $tpl != '..') {
      $templates[basename($tpl, '.tpl')] = file_get_contents(__DIR__ . '/tpl/' . $tpl);
    }
  }
  return $templates;
};

/**
 * Main entry point for the application.
 */
$app->get('/', function () use ($app, $jsGet, $tplGet) {
  $vars = array(
    'js' => $jsGet($app['debug']),
    'tpl' => $tplGet(),
  );
  return $app['twig']->render('index.twig', $vars);
});

$app->run();
