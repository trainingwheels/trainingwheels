<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = TRUE;

/**
 * Use Twig for templating, although the majority is done client-side.
 */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__ . '/views',
));

/**
 * The REST service endpoints.
 */
$app->mount('/rest', new TrainingWheels\Controller\REST());

/**
 * Client-side JavaScript includes.
 */
$jsGet = function($debug) {
  $js = array(
    '/js/vendor/backbone-relational/backbone-relational.js',
    '/js/src/jquery.spin.js',
    '/js/src/handlebars_helpers.js',
    '/js/src/app.js',
  );
  if (!$debug) {
    $js_min = array(
      '/js/vendor/jquery/jquery-1.8.3.min.js',
      '/js/vendor/underscore/underscore-min.js',
      '/js/vendor/spin/spin.min.js',
      '/js/vendor/handlebars/handlebars-1.0.rc.1.min.js',
      '/js/vendor/backbone/backbone-min.js',
    );
    $js = array_merge($js_min, $js);
  }
  else {
    $js_full = array(
      '/js/vendor/jquery/jquery-1.8.3.js',
      '/js/vendor/underscore/underscore.js',
      '/js/vendor/spin/spin.js',
      '/js/vendor/handlebars/handlebars-1.0.rc.1.js',
      '/js/vendor/backbone/backbone.js',
    );
    $js = array_merge($js_full, $js);
  }
  return $js;
};

/**
 * Client-side JavaScript templates.
 */
$tplGet = function() {
  $tpl_files = scandir(__DIR__ . '/tpl');
  $templates = array();
  foreach ($tpl_files as $tpl) {
    if ($tpl != '.' && $tpl != '..') {
      $templates[basename($tpl, '.tpl') . '-tpl'] = file_get_contents(__DIR__ . '/tpl/' . $tpl);
    }
  }
  return $templates;
};

/**
 * Main entry point for the application. It's a one-page
 * frontend app but we still need to handle routes here.
 */
$main = function () use ($app, $jsGet, $tplGet) {
  $vars = array(
    'js' => $jsGet($app['debug']),
    'tpl' => $tplGet(),
  );
  return $app['twig']->render('home.twig', $vars);
};
$app->get('/', $main);
$app->get('/course/{id}', $main);

$app->run();
