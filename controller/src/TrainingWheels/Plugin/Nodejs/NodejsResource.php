<?php

namespace TrainingWheels\Plugin\Nodejs;
use TrainingWheels\Resource\Resource;
use TrainingWheels\Environment\Environment;
use TrainingWheels\Store\DataStore;
use Exception;

class NodejsResource extends Resource {

  protected $hostname;
  protected $fullpath;
  protected $course_name;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, DataStore $data, $title, $user_name, $course_name, $res_id, $config) {
    parent::__construct($env, $data, $title, $user_name, $course_name, $res_id);

    $this->hostname = $config['hostname'];
    $this->fullpath = "/twhome/$user_name/$course_name";
    $this->course_name = $course_name;

    $this->cacheBuild($res_id);
  }

  /**
   * Get the configuration options for instances of this resource.
   */
  public static function getResourceVars() {
    return array(
      'hostname' => array(
        'val' => '4kclass.com',
        'help' => 'The name of the host.',
      ),
    );
  }

  /**
   * Get the info on this resource.
   */
  public function get() {
    $info = parent::get();
    if ($info['exists']) {
      $info['attribs'][0]['key'] = 'port';
      $info['attribs'][0]['title'] = 'Port num';
      $info['attribs'][0]['value'] = $this->genPortNum();
      $info['attribs'][1]['key'] = 'configjson';
      $info['attribs'][1]['title'] = 'config.json';
      $info['attribs'][1]['value'] = $this->env->fileGetContents("/twhome/$this->user_name/config.json");
      $info['attribs'][2]['key'] = 'client_configjson';
      $info['attribs'][2]['title'] = 'training-config.json';
      $info['attribs'][2]['value'] = $this->env->fileGetContents("/twhome/$this->user_name/$this->course_name/client/app/training-config.js");
    }
    return $info;
  }

  /**
   * Generate a port number.
   */
  protected function genPortNum() {
    $uid = $this->env->userGetId($this->user_name);
    return 20000 + $uid;
  }

  /**
   * Drop the node.js client config file.
   */
  protected function nodejsClientConfigAdd() {
    $user_name = $this->user_name;
    $hostname = $this->hostname;
    $port_num = $this->genPortNum();

    // The client config.
    $contents = "\"define({\n  url: 'http://$user_name.$this->course_name.$hostname:$port_num'\n});\n\"";
    $this->env->fileCreate($contents, "/twhome/$user_name/$this->course_name/client/app/training-config.js");
  }

  /**
   * Drop the node.js config files for the user home dir, mostly the port number is dynamic.
   */
  protected function nodejsConfigAdd() {
    $user_name = $this->user_name;
    $port_num = $this->genPortNum();

    // The server config.
    $file = "\"module.exports = {\n  port: " . $port_num . ",\n  feed: 'http://localhost:$port_num'\n};\n\"";
    $this->env->fileCreate($file, "/twhome/$user_name/config.json", $user_name);

    // Useful file in the home directory, name is the port number.
    $this->env->fileCreate("\"$port_num\"", "/twhome/$user_name/port-$port_num", $user_name);
  }

  /**
   * Do the files exist in the environment?
   */
  public function getExists() {
    if (!isset($this->exists)) {
      $port_num = $this->genPortNum();
      $this->exists = $this->env->fileExists("/twhome/$this->user_name/port-$port_num");
    }
    return $this->exists;
  }

  /**
   * Delete the files.
   */
  public function delete() {
    parent::delete();
    if (!$this->getExists()) {
      throw new Exception("Attempting to delete a NodejsResource that does not exist.");
    }
    $port_num = $this->genPortNum();
    $files = array(
      "/twhome/$this->user_name/port-$port_num",
      "/twhome/$this->user_name/config.json",
      "/twhome/$this->user_name/$this->course_name/client/app/training-config.js"
    );
    foreach ($files as $file) {
      if ($this->env->fileExists($file)) {
        $this->env->fileDelete($file);
      }
    }
    $this->exists = FALSE;
  }

  /**
   * Create the files in the correct place.
   */
  public function create() {
    parent::create();
    if ($this->getExists()) {
      throw new Exception("Attempting to create a NodejsResource that already exists.");
    }
    $this->exists = TRUE;
    $this->nodejsConfigAdd();
    $this->nodejsClientConfigAdd();
  }

  /**
   * Sync to a target.
   */
  public function syncTo(NodejsResource $target) {
    parent::syncTo();
    if (!$target->getExists()) {
      $target->create();
    }
  }
}
