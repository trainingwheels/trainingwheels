<?php

namespace TrainingWheels\Plugin\Drupal;
use TrainingWheels\Plugin\PluginBase;
use TrainingWheels\Log\Log;

class Drupal extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/drupal.yml';
  }

  public function getBundles() {
    return array(
      'drupal7' => array(
        'title' => 'Drupal 7',
        'plugins' => array(
          'MySQL' => array(),
          'ApacheHTTPD' => array(),
          'GitFiles' => array(),
          'PHP' => array(),
          'Drupal' => array(),
        ),
        'resources' => array(
          'drupal_files' => array(
            'title' => 'Drupal Code',
          ),
          'drupal_db' => array(
            'title' => 'Drupal Database',
          ),
        ),
      ),
    );
  }

  public function getPluginVars() {
    return array(
      'settings_path' => array(
        'val' => 'sites/default/settings.php',
      ),
      'files_path' => array(
        'val' => 'sites/default/files',
      )
    );
  }

  public function registerCourseObservers($course) {
    $settings_path = $this->getVar('settings_path');
    $files_path = $this->getVar('files_path');

    /**
     * After users are synced, replace the DB connection string in settings.php
     * with the right values. Since the MySQL user name and DB name are the same,
     * only one call will suffice for both.
     */
    $course->addObserver('afterUserResourcesSync', function($data) use ($settings_path) {
      Log::log('Patching settings.php', L_INFO, 'actions', array('layer' => 'app', 'source' => 'DrupalPlugin'));
      $course_name = $data['course']->course_name;
      $target_name = $data['target']->getName();

      $source_db = $data['source']->resourceGet('drupal_db');
      $target_db = $data['target']->resourceGet('drupal_db');

      // @TODO: Make this more flexible, perhaps hand off the replacing to the Gitfiles resource which knows
      // it's own location.
      $settings_file = "/twhome/$target_name/$course_name/" . $settings_path;
      $data['course']->env->fileStrReplace($source_db->getDBName(), $target_db->getDBName(), $settings_file);
      $data['course']->env->fileStrReplace($source_db->getPasswd(), $target_db->getPasswd(), $settings_file);
    });

    /**
     * Grant the group all access to files, which allows Apache to write.
     */
    $course->addObserver('afterUserResourcesCreate', function($data) use ($settings_path, $files_path) {
      Log::log('Allow apache to write files', L_INFO, 'actions', array('layer' => 'app', 'source' => 'DrupalPlugin'));
      $db = $data['user']->resourceGet('drupal_db');
      $gitfiles = $data['user']->resourceGet('drupal_files');

      if ($db && $gitfiles && $db->getExists() && $gitfiles->getExists()) {
        $user_name = $data['user']->getName();
        $course_name = $data['course']->course_name;

        // @TODO: Make this more flexible, perhaps hand off the replacing to the Gitfiles resource which knows
        // it's own location.
        $files_full_path = "/twhome/$user_name/$course_name/" . $files_path;
        $data['course']->env->dirChmod('g+rwx', $files_full_path);

        $settings = '\\' . "\$databases['default']['default']['database'] = '" . $db->getDBName() . "';\n";
        $settings .= '\\' . "\$databases['default']['default']['username'] = '" . $db->getUserName() . "';\n";
        $settings .= '\\' . "\$databases['default']['default']['password'] = '" . $db->getPasswd() . "';\n";
        $data['course']->env->fileAppendText("/twhome/$user_name/$course_name/sites/default/settings.php", $settings);
      }
    });
  }
}
