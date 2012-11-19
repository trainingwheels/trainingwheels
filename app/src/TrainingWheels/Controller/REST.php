<?php

namespace TrainingWheels\Controller;
use TrainingWheels\Course\CourseFactory;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;

class REST implements ControllerProviderInterface {

  public function connect(Application $app) {
    $controllers = $app['controllers_factory'];

    $self = $this;
    $controllers->get('/user/{id}', function ($id) use ($app, $self) {
      $params = $self->parseID($id);
      $output = $params['course']->userGet($params['user_name']);
      return $app->json($output);
    });

    return $controllers;
  }

  public function parseID($id) {
    $parts = explode('-', $id);
    $cf = new CourseFactory();
    return array(
      'course' => $cf->get($parts[0]),
      'user_name' => $parts[1],
    );
  }
}
