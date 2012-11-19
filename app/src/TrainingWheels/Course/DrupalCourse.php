<?php

namespace TrainingWheels\Course;
use TrainingWheels\User\LAMPUser;
use TrainingWheels\Resource\GitFilesResource;
use TrainingWheels\Resource\MySQLDatabaseResource;

class DrupalCourse extends TrainingCourse {

  // The repository that contains the source.
  public $repo;

  /**
   * Factory that creates new user objects for this course.
   */
  protected function userFactory($user_name) {
    $user_id = $this->courseid . '-' . $user_name;
    $files_res_id = $user_id . '-drupal_files';
    $db_res_id = $user_id . '-drupal_db';

    $user_obj = new LAMPUser($this->env, $user_name, $user_id);
    $user_obj->resources = array(
      'drupal_files' => new GitFilesResource($this->env, $files_res_id, 'Code', $user_name, $this->course_name, $this->course_name, $this->repo),
      'drupal_db' => new MySQLDatabaseResource($this->env, $db_res_id, 'Database', $user_name, $this->course_name, "/home/$user_name/$this->course_name/database.sql.gz"),
    );

    return $user_obj;
  }

  /**
   * Create users.
   */
  public function usersCreate($users) {
    parent::usersCreate($users);
    $this->env->apacheHTTPDRestart();
  }

  /**
   * Sync resources for a user.
   */
  public function usersResourcesSync($source_user, $target_users, $resources) {
    $target_users = $this->userNormalizeParam($target_users);

    // The source of the sync.
    $source_user_obj = $this->userFactory($source_user);

    foreach ($target_users as $user_name) {
      $target_user_obj = $this->userFactory($user_name);
      $source_user_obj->syncTo($target_user_obj, $resources);

      if ($resources == '*' || in_array('drupal_db', $resources)) {
        // Replace the DB connection string in settings.php with the right values.
        // Since the MySQL user name and DB name are the same, only one call will
        // suffice for both.
        $source_db = $source_user_obj->resourceGet('drupal_db');
        $target_db = $target_user_obj->resourceGet('drupal_db');
        $settings_file = "/home/$user_name/$this->course_name/sites/default/settings.php";
        $this->env->fileStrReplace($source_db->getDBName(), $target_db->getDBName(), $settings_file);
        $this->env->fileStrReplace($source_db->getPasswd(), $target_db->getPasswd(), $settings_file);
      }
    }
  }

  /**
   * Create resources for a user.
   */
  public function usersResourcesCreate($users, $resources) {
    $users = $this->userNormalizeParam($users);

    foreach ($users as $user_name) {
      $user_obj = $this->userFactory($user_name);
      $user_obj->resourcesCreate($resources);
      $db = $user_obj->resourceGet('drupal_db');

      // Grant the group all access to files, which allows Apache to write.
      $files_dir = "/home/$user_name/$this->course_name/sites/default/files";
      $this->env->dirChmod('g+rwx', $files_dir);

      if ($db && $db->getExists()) {
        $this->drupalDBSettingsAdd($db->db_name, $db->mysql_username, $db->getPasswd(), "/home/$user_name/$this->course_name/sites/default/settings.php");
      }
    }
  }

  /**
   * Add Drupal database settings.
   */
  protected function drupalDBSettingsAdd($db, $dbuser, $pass, $file_path) {
    twcore_assert_valid_strings(__FUNCTION__, func_get_args());
    $settings = '\\' . "\$databases['default']['default']['database'] = '" . $db . "';\n";
    $settings .= '\\' . "\$databases['default']['default']['username'] = '" . $dbuser . "';\n";
    $settings .= '\\' . "\$databases['default']['default']['password'] = '" . $pass . "';\n";
    $this->env->fileAppendText($file_path, $settings);
  }
}
