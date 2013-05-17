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
  protected $serverJsPath;
  protected $clientJsPath;

  /**
   * Constructor.
   */
  public function __construct(Environment $env, DataStore $data, $title, $user_name, $course_name, $res_id, $config) {
    parent::__construct($env, $data, $title, $user_name, $course_name, $res_id);

    $this->hostname = $config['hostname'];
    $this->fullpath = "/twhome/$user_name/$course_name";
    $this->clientJsPath = $this->fullpath . "/client/app/training-config.js";
    $this->serverJsPath = "/twhome/$user_name/config.js";
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
      $info['attribs'][0]['title'] = 'Port number';
      $info['attribs'][0]['value'] = $this->env->fileGetContents("/twhome/$this->user_name/port-" . $this->genPortNum());
      $info['attribs'][1]['key'] = 'serverconfig';
      $info['attribs'][1]['title'] = '/config.js';
      $info['attribs'][1]['value'] = $this->env->fileGetContents($this->serverJsPath);
      $info['attribs'][2]['key'] = 'clientconfig';
      $info['attribs'][2]['title'] = '/client/app/training-config.js';
      $info['attribs'][2]['value'] = $this->env->fileGetContents($this->clientJsPath);
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
   * Drop the config files.
   */
  protected function nodejsConfigAdd() {
    $user_name = $this->user_name;
    $hostname = $this->hostname;
    $port_num = $this->genPortNum();

    // The client config.
    $clientfile = <<<EOJS
"define({
  url: 'http://$user_name.$this->course_name.$hostname:$port_num'
});"
EOJS;
    // The server config.
    $serverfile = <<<EOJS
"module.exports = {
  port: $port_num,
  pollInterval: 10000,
  drupalNodesUrl: 'http://$user_name.$this->course_name.$hostname/exercises/drupal/rest/node.json'
};"
EOJS;
    $this->env->fileCreate($clientfile, $this->clientJsPath, $user_name);
    $this->env->fileCreate($serverfile, $this->serverJsPath, $user_name);

    // Useful file in the home directory, name is the port number.
    $this->env->fileCreate("\"$port_num\"", "/twhome/$this->user_name/port-$port_num", $user_name);
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
      $this->clientJsPath,
      $this->serverJsPath
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
    $this->exists = TRUE;
    $this->nodejsConfigAdd();
  }

  /**
   * Sync to a target.
   */
  public function syncTo(NodejsResource $target) {
    parent::syncTo();
    $target->create();
  }
}
