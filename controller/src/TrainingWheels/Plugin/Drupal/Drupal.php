<?php

namespace TrainingWheels\Plugin\Drupal;
use TrainingWheels\Plugin\PluginBase;

class Drupal extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/drupal.yml';
  }

  public function registerCourseObservers($course) {
    /**
     * After users are synced, replace the DB connection string in settings.php
     * with the right values. Since the MySQL user name and DB name are the same,
     * only one call will suffice for both.
     */
    $course->addObserver('afterUserResourcesSync', function($data) {
      $course_name = $data['course']->course_name;
      $target_name = $data['target']->getName();

      $source_db = $data['source']->resourceGet('drupal_db');
      $target_db = $data['target']->resourceGet('drupal_db');

      $settings_file = "/twhome/$target_name/$course_name/sites/default/settings.php";
      $data['course']->env->fileStrReplace($source_db->getDBName(), $target_db->getDBName(), $settings_file);
      $data['course']->env->fileStrReplace($source_db->getPasswd(), $target_db->getPasswd(), $settings_file);
    });

    /**
     * Grant the group all access to files, which allows Apache to write.
     */
    $course->addObserver('afterUserResourcesCreate', function($data) {
      $db = $data['user']->resourceGet('drupal_db');
      $user_name = $data['user']->getName();
      $course_name = $data['course']->course_name;

      $files_dir = "/twhome/$user_name/$course_name/sites/default/files";
      $data['course']->env->dirChmod('g+rwx', $files_dir);

      if ($db && $db->getExists()) {
        $settings = '\\' . "\$databases['default']['default']['database'] = '" . $db->db_name . "';\n";
        $settings .= '\\' . "\$databases['default']['default']['username'] = '" . $db->mysql_username . "';\n";
        $settings .= '\\' . "\$databases['default']['default']['password'] = '" . $db->getPasswd() . "';\n";
        $data['course']->env->fileAppendText("/twhome/$user_name/$course_name/sites/default/settings.php", $settings);
      }
    });
  }
}
