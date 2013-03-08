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
        'val' => '90',
      ),
      array(
        'key' => 'php_display_errors',
        'val' => 'On',
      ),
      array(
        'key' => 'php_display_startup_errors',
        'val' => 'On',
      ),
      array(
        'key' => 'php_html_errors',
        'val' => 'On',
      ),
      array(
        'key' => 'php_post_max_size',
        'val' => '32M',
      ),
      array(
        'key' => 'php_upload_max_filesize',
        'val' => '32M',
      ),
      array(
        'key' => 'php_date_timezone',
        'val' => 'America/Chicago',
      ),
      array(
        'key' => 'php_short_open_tag',
        'val' => 'Off',
      ),
      array(
        'key' => 'apc_rfc1867',
        'val' => '1',
      ),
      array(
        'key' => 'apc_shm_size',
        'val' => '96M',
      ),
      array(
        'key' => 'apc_shm_segments',
        'val' => '1',
      ),
      array(
        'key' => 'apc_num_files_hint',
        'val' => '0',
      ),
    );
  }
}
