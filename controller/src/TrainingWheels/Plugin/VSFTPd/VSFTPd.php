<?php

namespace TrainingWheels\Plugin\VSFTPd;
use TrainingWheels\Plugin\PluginBase;

class VSFTPd extends PluginBase {

  public function __construct() {
    parent::__construct();
    $this->ansible_play = __DIR__ . '/ansible/vsftpd.yml';
  }

  public function getAnsibleConfig() {
    return array(
      'vars' => array(
        'vsftpd_anonymous_enable' => 'NO',
        'vsftpd_local_enable' => 'YES',
        'vsftpd_write_enable' => 'YES',
        'vsftpd_local_umask' => '002',
      ),
    );
  }
}
