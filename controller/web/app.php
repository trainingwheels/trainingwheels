<?php

$autoloader_path = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoloader_path)) {
  print 'Unable to find the class autoloader, are you sure you have run "composer install"? See README.md for more information.';
  exit(1);
}
require_once $autoloader_path;

use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use TrainingWheels\Common\BootstrapServiceProvider;
use TrainingWheels\Controller\RESTControllerProvider;
use TrainingWheels\Log\Log;

/**
 * Create the main Silex application.
 */
$app = new Application();

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

/**
 * Session provider.
 * Sessions are only needed for the web endpoints, so register
 * the provider here rather than in bootstrap.
 */
$app->register(new Silex\Provider\SessionServiceProvider());

/**
 * Form service provider.
 */
$app->register(new Silex\Provider\FormServiceProvider());

/**
 * Use Twig for templating, although the majority is done client-side.
 */
$app->register(new TwigServiceProvider(), array(
  'twig.path' => __DIR__ . '/',
));

/**
 * The REST service endpoints.
 */
$app->mount('/rest', new RESTControllerProvider());

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
$app->get('/', function () use ($app, $tplGet) {
  $vars = array(
    'js' => array(),
    'debug' => $app['debug'],
    'path' => $app['debug'] ? 'debug' : 'release',
    'tpl' => $tplGet(),
  );
  return $app['twig']->render('index.twig', $vars);
});

/**
 * Debug log viewer
 */
$app->get('/logs', function () use ($app, $tplGet) {
  if ($app['debug'] !== TRUE) {
    return $app->json('Debug mode is disabled', 401);
  }
  $vars = array(
    'logs' => $app['tw.log']->renderHTML('actions'),
  );
  return $app['twig']->render('logs.twig', $vars);
});

/**
 * Login page for the application.
 */
$app->match('/login', function (Request $request) use ($app) {
  // No need to log in if we're already authenticated.
  if ($app['session']->get('user') !== NULL) {
    return $app->redirect('/');
  }

  $messages = array();
  $form = $app['form.factory']->createBuilder('form')
    ->add('name', 'text', array('required' => TRUE))
    ->add('pass', 'password', array('required' => TRUE))
    ->getForm();

  if ($request->getMethod() == 'POST') {
    $form->bind($request);

    $data = $form->getData();
    if ($data['name'] === $app['tw.config']['user']['name'] && $data['pass'] === $app['tw.config']['user']['pass']) {
      $app['session']->set('user', array('username' => $username));
      return $app->redirect('/');
    }
    else {
      $messages[] = 'Invalid user name or password.';
    }
  }

  $vars = array(
    'js' => array(
      '/js/vendor/alertify/alertify.min.js',
    ),
    'debug' => $app['debug'],
    'messages' => $messages,
    'form' => $form->createView()
  );
  return $app['twig']->render('login.twig', $vars);
});

/**
 * Logout.
 */
$app->match('/logout', function(Request $request) use ($app) {
  $app['session']->invalidate();
  return $app->redirect('/login');
});

/**
 * Bail on non-authenticated requests.
 */
$app->before(function (Request $request) use ($app) {
  Log::log($request->getMethod(), L_INFO, 'actions', array('layer' => 'user', 'source' => 'Web', 'params' => $request->getPathInfo()));

  // Developers can set authentication bypass for REST testing.
  if (isset($app['tw.config']['bypass_auth']) && $app['tw.config']['bypass_auth'] === TRUE) {
    return;
  }

  if ($app['session']->get('user') === NULL) {
    $path = $request->getPathInfo();

    // Anonymous requests to REST backend get 401.
    if (strpos($path, '/rest/') === 0) {
      return $app->json('Unauthorized', 401);
    }

    // For user facing pages, redirect to the login page.
    if ($path !== '/login') {
      return $app->redirect('/login');
    }
  }
});

$app->run();
