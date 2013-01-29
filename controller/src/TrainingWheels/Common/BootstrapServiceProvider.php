<?php

namespace TrainingWheels\Common;

use Silex\Application;
use Silex\ServiceProviderInterface;
use TrainingWheels\Course\CourseFactory;
use TrainingWheels\Job\JobFactory;

class BootstrapServiceProvider implements ServiceProviderInterface {
  public function register(Application $app) {
    $app['course_factory'] = new CourseFactory($app['connections']['mongo']);
    $app['job_factory'] = new JobFactory($app['connections']['mongo'], $app['course_factory']);
  }

  public function boot(Application $app) {
  }
}

