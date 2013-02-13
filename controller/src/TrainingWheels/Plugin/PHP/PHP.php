<?php

namespace TrainingWheels\Plugin\PHP;
use TrainingWheels\Plugin\PluginBase;

class PHP extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/php.yml';
  }

  public function getProvisionConfig() {
    return array(
      'vars' => array(
        'php_max_execution_time' => '90',
        'php_display_errors' => 'On',
        'php_display_startup_errors' => 'On',
        'php_html_errors' => 'On',
        'php_post_max_size' => '32M',
        'php_upload_max_filesize' => '32M',
        'php_date_timezone' => 'America/Chicago',
        'php_short_open_tag' => 'Off',
        'apc_rfc1867' => '1',
        'apc_shm_size' => '96M',
        'apc_shm_segments' => '1',
        'apc_num_files_hint' => '0',
      ),
    );
  }
}
