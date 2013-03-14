<?php

namespace TrainingWheels\Plugin\MySQL;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Common\Util;
use TrainingWheels\Environment\Environment;
use Exception;

class MySQLDatabaseResource extends Resource {

  protected $db_name;
  protected $mysql_username;
  protected $mysql_password;
  protected $course_name;
  protected $dump_path;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, $title, $user_name, $course_name, $res_id, $data) {
    parent::__construct($env, $title, $user_name, $course_name, $res_id);
    $this->course_name = $course_name;
    $this->dump_path = "/twhome/$user_name/$course_name/" . $data['dump_path'];

    $this->cachePropertiesAdd(array('db_name', 'mysql_username', 'mysql_password'));
    $this->cacheBuild($res_id);
  }

  /**
   * Get the configuration options for instances of this resource.
   */
  public static function getResourceVars() {
    return array(
      array(
        'key' => 'dump_path',
        'val' => 'database.sql.gz',
        'help' => 'The database dump filename relative to the user\'s course directory',
      ),
    );
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = parent::get();
    if ($info['exists']) {
      $info['attribs'][0]['key'] = 'db_name';
      $info['attribs'][0]['title'] = 'Database name';
      $info['attribs'][0]['value'] = $this->getDBName();
      $info['attribs'][1]['key'] = 'mysql_username';
      $info['attribs'][1]['title'] = 'MySQL user';
      $info['attribs'][1]['value'] = $this->getUserName();
      $info['attribs'][2]['key'] = 'mysql_password';
      $info['attribs'][2]['title'] = 'MySQL password';
      $info['attribs'][2]['value'] = $this->getPasswd();
    }
    return $info;
  }

  /**
   * Return bool for whether the database exists in the environment.
   */
  public function getExists() {
    if (!$this->exists) {
      $this->exists = $this->env->mySQLDBExists($this->genSafeDBName());
      $this->cacheSave();
    }
    return $this->exists;
  }

  /**
   * Create the database.
   */
  public function create() {
    parent::create();
    if ($this->getExists()) {
      throw new Exception("Attempting to create a MySQLDatabaseResource that already exists.");
    }
    $this->mysql_password = Util::passwdGen();
    $this->mysql_username = $this->getUserName();
    $this->db_name = $this->mysql_username;
    $this->exists = TRUE;

    $this->env->mySQLUserDBCreate($this->mysql_username, $this->mysql_password, $this->db_name, $this->dump_path);
    $this->credentialsCreate();
    $this->cacheSave();
  }

  /**
   * Delete the database.
   */
  public function delete() {
    parent::delete();
    if (!$this->getExists()) {
      throw new Exception("Attempting to delete a MySQLDatabaseResource that does not exist.");
    }
    $this->env->mySQLUserDBDelete($this->getUserName(), $this->getDBName());

    $this->mysql_password = FALSE;
    $this->mysql_username = FALSE;
    $this->db_name = FALSE;
    $this->exists = FALSE;
    $this->cacheSave();
  }

  /**
   * Make a .my.cnf file for easy access to DB.
   */
  protected function credentialsCreate() {
    $dbuser = $this->getUserName();
    $pass = $this->getPasswd();
    $this->env->fileCreate("\"[client]\nuser=$dbuser\npass=$pass\n\"", "/twhome/$this->user_name/.my.cnf", $this->user_name);
  }

  /**
   * Create a name, could be used for either a user name or DB name.
   */
  protected function genSafeDBName() {
    // Since MySQL has a limit of 16 chars for user names, we use the Linux
    // user id instead.
    $user_id = $this->env->userGetId($this->user_name);

    $name = $this->course_name . '_' . $user_id;

    // We turn all dashes into underscores, as dashes are illegal in DB names.
    $name = str_replace('-', '_', $name);

    if (preg_match('/^[0-9a-zA-Z_]+$/', $name) === 0) {
      throw new Exception("MySQL object name will contain an invalid char due to the course name: '$name'");
    }
    if (strlen($name) > 16) {
      throw new Exception("MySQL object name cannot be longer than 16 characters, '$name'");
    }

    return $name;
  }

  /**
   * Lazy generate the db name, which requires a linux user exist so can't
   * be done on construction.
   */
  public function getDBName() {
    if (!isset($this->db_name)) {
      $this->db_name = $this->genSafeDBName();
      $this->cacheSave();
    }
    return $this->db_name;
  }

  /**
   * Lazy generate the username, which requires a linux user exist so can't
   * be done on construction.
   */
  public function getUserName() {
    if (empty($this->mysql_username)) {
      $this->mysql_username = $this->genSafeDBName();
      $this->cacheSave();
    }
    return $this->mysql_username;
  }

  /**
   * Lazy load the password from the credentials.
   */
  public function getPasswd() {
    if (empty($this->mysql_password)) {
      if ($this->env->fileExists("/twhome/$this->user_name/.my.cnf")) {
        $cnf = $this->env->fileGetContents("/twhome/$this->user_name/.my.cnf");

        if (!empty($cnf)) {
          $ini = parse_ini_string($cnf);
          $this->mysql_password = $ini['pass'];
          $this->cacheSave();
        }
      }
    }

    return $this->mysql_password;
  }

  /**
   * Dump contents to file.
   */
  public function dumpTo($file_folder) {
    $file_folder = trim($file_folder, '/');
    $db = $this->getDBName();
    $file = "/$file_folder/$db.sql";
    $this->env->mySQLDumpToFile($db, $file);
    return $file . '.gz';
  }

  /**
   * Sync to a target.
   */
  public function syncTo(MySQLDatabaseResource $target) {
    parent::syncTo();
    // Create a backup.
    if ($target->getExists()) {
      $target->dumpTo('tmp');
      $target->delete();
    }

    static $source_dump = FALSE;
    if (!$source_dump) {
      $source_dump = $this->dumpTo('tmp');
    }

    $target->dump_path = $source_dump;
    $target->create();
  }
}
