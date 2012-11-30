<?php

namespace TrainingWheels\Controller;
use TrainingWheels\Course\CourseFactory;
use TrainingWheels\Log\Log;
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

    /**
     * Conversion function to convert ids from the format '1-name' to a Course
     * object and a user name.
     */
    $parseID = function ($id) {
      $parts = explode('-', $id);
      if (isset($parts[0]) && isset($parts[1])) {
        $course = CourseFactory::singleton()->get($parts[0]);
        return array(
          'course' => $course,
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
    $controllers->get('/user/{user}', function ($user) use ($app) {
      if (!$user) {
        return $app->json(array('messages' => 'Invalid user ID requested, ensure format is courseid-username, e.g. 1-instructor.'), HTTP_BAD_REQUEST);
      }
      $output = $user['course']->userGet($user['user_name']);
      if (!$output) {
        return $app->json(array('messages' => 'User ' . $user['user_name'] . ' does not exist.'), HTTP_NOT_FOUND);
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

      $course = CourseFactory::singleton()->get($course_id);
      $result = $course->usersCreate($user_name);
      if (!$result) {
        return $app->json(array('messages' => 'User already exists.'), HTTP_CONFLICT);
      }
      return $app->json(array('messages' => 'success'), HTTP_CREATED);
    });

    /**
     * Delete a user.
     */
    $controllers->delete('/user/{user}', function ($user) use ($app) {
      if (!$user) {
        return $app->json(array('messages' => 'Invalid user ID requested, ensure format is courseid-username, e.g. 1-instructor.'), HTTP_BAD_REQUEST);
      }
      $output = $user['course']->usersDelete($user['user_name']);
      if (!$output) {
        return $app->json(array('messages' => 'User ' . $user['user_name'] . ' does not exist.'), HTTP_NOT_FOUND);
      }
      return $app->json(array('messages' => 'success'), HTTP_OK);
    })
    ->convert('user', $parseID);

    /**
     * Update a user, or perform an action on a user.
     */
    $controllers->put('/user/{user}', function ($user, Request $request) use ($app) {
      if (!$user) {
        return $app->json(array('messages' => 'Invalid user ID requested, ensure format is courseid-username, e.g. 1-instructor.'), HTTP_BAD_REQUEST);
      }
      $action = $request->request->get('action');
      $target_resources = $request->request->get('target_resources');

      if (!empty($action) && !empty($target_resources)) {
        switch ($action) {
          case 'resources-sync':
            $sync_from = $request->request->get('sync_from');
            if (!empty($sync_from)) {
              $user['course']->usersResourcesSync($sync_from, $user['user_name'], $target_resources);
              return $app->json(array('messages' => 'User resources synced'), HTTP_OK);
            }
            break;

          case 'resources-create':
            $user['course']->usersResourcesCreate($user['user_name'], $target_resources);
            return $app->json(array('messages' => 'User resources created'), HTTP_OK);
            break;
        }
      }

      $output = $user['course']->userGet($user['user_name']);
      return $app->json($output, HTTP_OK);
    })
    ->convert('user', $parseID);

    /**
     * Course summaries
     */
    $controllers->get('/course_summaries', function() use ($app) {
      $courses = array();
      $ids = array(1);
      foreach ($ids as $id) {
        $course = CourseFactory::singleton()->get($id);
        unset($course->env);
        $course->id = $course->course_id;
        unset($course->course_id);
        $courses[] = $course;
      }
      $return = new \stdClass;
      $return->course_summaries = $courses;
      return $app->json($return, HTTP_OK);
    });

    /**
     * Retrieve a course.
     */
    $controllers->get('/courses/{id}', function ($id) use ($app) {
      $course = CourseFactory::singleton()->get($id);

      // Ember data expects an 'id' parameter.
      $course->id = $course->course_id;

      // Get all the users, then split the instructor and
      // the rest into two adjacent properties on the
      // returned object. Ember doesn't support embedded
      // properties right now.
      $users = $course->usersGet('*');
      $instructor = $users['instructor'];
      unset($users['instructor']);

      $return = new \stdClass;
      $return->courses = array($course);
      $return->users = array_values($users);
      $return->instructor = array($instructor);

      unset($course->course_id);
      unset($course->env);
      return $app->json($return, HTTP_OK);
    })
    ->assert('id', '\d+');

    return $controllers;
  }
}
