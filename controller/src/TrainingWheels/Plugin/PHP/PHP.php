<?php

namespace TrainingWheels\Plugin\PHP;
use TrainingWheels\Plugin\PluginBase;

class PHP extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/php.yml';
  }

  public function getPluginVars() {
    return array(
      array(
        'key' => 'php_max_execution_time',
        'default' => '90',
      ),
      array(
        'key' => 'php_display_errors',
        'default' => 'On',
      ),
      array(
        'key' => 'php_display_startup_errors',
        'default' => 'On',
      ),
      array(
        'key' => 'php_html_errors',
        'default' => 'On',
      ),
      array(
        'key' => 'php_post_max_size',
        'default' => '32M',
      ),
      array(
        'key' => 'php_upload_max_filesize',
        'default' => '32M',
      ),
      array(
        'key' => 'php_date_timezone',
        'default' => 'America/Chicago',
      ),
      array(
        'key' => 'php_short_open_tag',
        'default' => 'Off',
      ),
      array(
        'key' => 'apc_rfc1867',
        'default' => '1',
      ),
      array(
        'key' => 'apc_shm_size',
        'default' => '96M',
      ),
      array(
        'key' => 'apc_shm_segments',
        'default' => '1',
      ),
      array(
        'key' => 'apc_num_files_hint',
        'default' => '0',
      ),
    );
  }
}
