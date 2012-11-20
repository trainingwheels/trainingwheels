<?php

namespace TrainingWheels\Controller;
use TrainingWheels\Course\CourseFactory;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_NOT_FOUND', 404);
define('HTTP_CONFLICT', 409);

class REST implements ControllerProviderInterface {

  /**
   * Main entry point for REST routing.
   */
  public function connect(Application $app) {
    $controllers = $app['controllers_factory'];
    $self = $this;

    /**
     * Conversion function to convert ids from the format '1-name' to a Course
     * object and a user name.
     */
    $parseID = function ($id) {
      $parts = explode('-', $id);
      if (isset($parts[0]) && isset($parts[1])) {
        $cf = new CourseFactory();
        return array(
          'course' => $cf->get($parts[0]),
          'user_name' => $parts[1],
        );
      }
      return FALSE;
    };

    /**
     * Handle JSON automatically.
     */
    $app->before(function (Request $request) {
      if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
        $data = json_decode($request->getContent(), TRUE);
        $request->request->replace(is_array($data) ? $data : array());
      }
    });

    /**
     * Retrieve a user.
     */
    $controllers->get('/user/{user}', function ($user) use ($app, $self) {
      if (!$user) {
        return $app->json(array('messages' => 'Invalid user ID requested, ensure format is courseid-username, e.g. 1-instructor.'), HTTP_BAD_REQUEST);
      }
      $output = $user['course']->userGet($user['user_name']);
      if (!$output) {
        return $app->json(array('messages' => 'User ' . $user['user_name'] . 'does not exist.'), HTTP_NOT_FOUND);
      }
      return $app->json($output, HTTP_OK);
    })
    ->convert('user', $parseID);

    /**
     * Create a user.
     */
    $controllers->post('/user', function (Request $request) use ($app) {
      $course_id = $request->request->get('courseid');
      $user_name = $request->request->get('user_name');
      if (!$course_id || !$user_name) {
        return $app->json(array('messages' => 'Invalid parameters passed, check JSON formatting is strict.'), HTTP_BAD_REQUEST);
      }

      $cf = new CourseFactory();
      $course = $cf->get($course_id);
      $result = $course->usersCreate($user_name);
      if (!$result) {
        return $app->json(array('messages' => 'User already exists.'), HTTP_CONFLICT);
      }
      $output = $course->userGet($user_name);
      return $app->json($output, HTTP_CREATED);
    });

    /**
     * Index of courses.
     */
    $controllers->get('/course', function() use ($app) {
      $courses = array();
      $ids = array(1);
      foreach ($ids as $id) {
        $cf = new CourseFactory();
        $course = $cf->get($id);
        unset($course->env);
        $courses[] = $course;
      }
      return $app->json($courses, HTTP_OK);
    });



    return $controllers;
  }
}
