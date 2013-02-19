<?php

namespace TrainingWheels\Plugin\PHP;
use TrainingWheels\Plugin\PluginBase;

class PHP extends PluginBase {

  const name = 'PHP';

  public function getProvisionSteps() {
    return __DIR__ . '/provision/php.yml';
  }

  public function getPluginVars() {
    return array(
      'php_max_execution_time' => array(
        'val' => '90',
      ),
      'php_display_errors' => array(
        'val' => 'On',
      ),
      'php_display_startup_errors' => array(
        'val' => 'On',
      ),
      'php_html_errors' => array(
        'val' => 'On',
      ),
      'php_post_max_size' => array(
        'val' => '32M',
      ),
      'php_upload_max_filesize' => array(
        'val' => '32M',
      ),
      'php_date_timezone' => array(
        'val' => 'America/Chicago',
      ),
      'php_short_open_tag' => array(
        'val' => 'Off',
      ),
      'apc_rfc1867' => array(
        'val' => '1',
      ),
      'apc_shm_size' => array(
        'val' => '96M',
      ),
      'apc_shm_segments' => array(
        'val' => '1',
      ),
      'apc_num_files_hint' => array(
        'val' => '0',
      ),
    );
  }
}
