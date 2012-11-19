<?php

namespace TrainingWheels\Course;
use TrainingWheels\User\LAMPUser;
use TrainingWheels\Resource\GitFilesResource;
use TrainingWheels\Resource\MySQLDatabaseResource;

class NodejsCourse extends TrainingCourse {

  // The repository that contains the source.
  public $repo;

  /**
   * Factory that creates new user objects for this course.
   */
  protected function userFactory($user_name) {
    $user_obj = new LAMPUser($this->env, $user_name, $this->course_name);

    $user_obj->resources = array(
      'nodejs_files' => new GitFilesResource($this->env, 'Code', $user_name, $this->course_name, $this->course_name, $this->repo, 'master'),
      'drupal_db' => new MySQLDatabaseResource($this->env, 'MySQLDatabase', $user_name, $this->course_name, "/home/$user_name/$this->course_name/exercises/drupal/database.sql.gz"),
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
   * Create a single user.
   */
  protected function userCreate($user_name) {
    parent::userCreate($user_name);
    // Drop a config file with port for this user's nodejs instance.
    $this->nodejsConfigAdd($user_name);
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
        $settings_file = "/home/$user_name/$this->course_name/exercises/drupal/sites/default/settings.php";
        $this->env->fileStrReplace($source_db->dbNameGet(), $target_db->dbNameGet(), $settings_file);
        $this->env->fileStrReplace($source_db->passwdGet(), $target_db->passwdGet(), $settings_file);
      }

      $this->nodejsClientConfigAdd($user_name);
    }
  }

  /**
   * Create resources for a user.
   */
  public function usersResourcesCreate($users, $resources) {
    $users = $this->userNormalizeParam($users);

    foreach ($users as $user_name) {
      // Create the actual resources using the standard calls.
      $user_obj = $this->userFactory($user_name);
      $user_obj->resourcesCreate($resources);
      $uid = $this->env->userGetId($user_name);

      // Now customize for this course.
      $db = $user_obj->resourceGet('drupal_db');
      if ($db && $db->exists()) {
        $this->drupalDBSettingsAdd($db->db_name, $db->mysql_username, $db->passwdGet(), "/home/$user_name/$this->course_name/exercises/drupal/sites/default/settings.php");
      }

      // Grant the group all access to files, which allows Apache to write.
      $files_dir = "/home/$user_name/$this->course_name/exercises/drupal/sites/default/files";
      $this->env->dirChmod('g+rwx', $files_dir);

      $this->nodejsClientConfigAdd($user_name);
    }
  }

  /**
   * Generate a port number.
   */
  protected function genPortNum($user) {
    $uid = $this->env->userGetId($user);
    return 20000 + $uid;
  }

  /**
   * Drop the node.js client config file.
   */
  protected function nodejsClientConfigAdd($user) {
    $port_num = $this->genPortNum($user);

    // The client config.
    $contents = "define({\n  url: 'http://$user.4ktraining.com:$port_num'\n});\n";
    $this->env->filePutContents("/home/$user/$this->course_name/client/app/training-config.js", $contents);
  }

  /**
   * Drop the node.js config files for the user home dir, mostly the port number is dynamic.
   */
  protected function nodejsConfigAdd($user) {
    $port_num = $this->genPortNum($user);

    // The server config.
    $file = "\"module.exports = {\n  port: " . $port_num . ",\n  feed: 'http://localhost:3001'\n};\n\"";
    $this->env->fileCreate($file, "/home/$user/config.json", $user);

    // Useful file in the home directory, name is the port number.
    $this->env->fileCreate("\"$port_num\"", "/home/$user/port-$port_num", $user);
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
