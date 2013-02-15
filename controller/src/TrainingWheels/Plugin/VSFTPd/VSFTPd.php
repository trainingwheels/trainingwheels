<?php

namespace TrainingWheels\Plugin\VSFTPd;
use TrainingWheels\Plugin\PluginBase;

class VSFTPd extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/vsftpd.yml';
  }

  public function getProvisionConfig() {
    return array(
      'vars' => array(
        'vsftpd_anonymous_enable' => array(
          'val' => 'NO',
        ),
        'vsftpd_local_enable' => array(
          'val' => 'YES',
        ),
        'vsftpd_write_enable' => array(
          'val' => 'YES',
        ),
        'vsftpd_local_umask' => array(
          'val' => '002',
        ),
      ),
    );
  }
}
