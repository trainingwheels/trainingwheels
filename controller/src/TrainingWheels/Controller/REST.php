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
    $controllers->get('/users/{user}', function ($user) use ($app) {
      if (!$user) {
        return $app->json(array('messages' => 'Invalid user ID requested, ensure format is courseid-username, e.g. 1-instructor.'), HTTP_BAD_REQUEST);
      }
      $output = $user['course']->userGet($user['user_name']);
      if (!$output) {
        return $app->json(array('messages' => 'User ' . $user['user_name'] . ' does not exist.'), HTTP_NOT_FOUND);
      }
      $return = new \stdClass;

      // Encode the resource attributes so that they get parsed as strings on the client.
      foreach ($output['resources'] as $key => $res) {
        if (isset($output['resources'][$key]['attribs'])) {
          $output['resources'][$key]['attribs'] = json_encode($res['attribs']);
        }
      }
      $return->resources = $output['resources'];
      unset($output['resources']);
      $return->user = $output;

      return $app->json($return, HTTP_OK);
    })
    ->convert('user', $parseID);

    /**
     * Create a user.
     */
    $controllers->post('/user_summaries', function (Request $request) use ($app) {
      $user = $request->request->get('user_summary');
      $course_id = $user['course_id'];
      $user_name = $user['user_name'];
      if (!$course_id || !$user_name) {
        return $app->json(array('messages' => 'Invalid parameters passed, check JSON formatting is strict.'), HTTP_BAD_REQUEST);
      }

      $course = CourseFactory::singleton()->get($course_id);
      $result = $course->usersCreate($user_name);
      if (!$result) {
        return $app->json(array('messages' => 'User already exists.'), HTTP_CONFLICT);
      }
      // Get only the summary, by passing FALSE as second param.
      $user_obj = $course->userGet($user_name, FALSE);
      return $app->json(array('user_summary' => $user_obj), HTTP_CREATED);
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
     * Get course summaries
     */
    $controllers->get('/course_summaries', function() use ($app) {
      $courses = CourseFactory::singleton()->getAllSummaries();
      $return = new \stdClass;
      $return->course_summaries = $courses;
      return $app->json($return, HTTP_OK);
    });

    /**
     * Create a course.
     */
    $controllers->post('/course_summaries', function (Request $request) use ($app) {
      $newCourse = $request->request->get('course_summary');
      $savedCourse = CourseFactory::singleton()->save($newCourse);

      $return = new \stdClass;
      $return->course_summary = $savedCourse;

      return $app->json($return, HTTP_CREATED);
    });

    /**
     * Retrieve a course.
     */
    $controllers->get('/courses/{id}', function ($id) use ($app) {
      $course = CourseFactory::singleton()->get($id);
      if (!$course) {
        return $app->json(array('messages' => 'Course with id ' . $id . ' does not exist.'), HTTP_NOT_FOUND);
      }

      // Ember data expects an 'id' parameter.
      $course->id = $course->course_id;

      // Get all the users and add them to the return.
      $users = $course->usersGet('*');

      $return = new \stdClass;
      $return->course = $course;
      $return->users = array_values($users);

      unset($course->course_id);
      unset($course->env);
      return $app->json($return, HTTP_OK);
    })
    ->assert('id', '\d+');

    return $controllers;
  }
}
