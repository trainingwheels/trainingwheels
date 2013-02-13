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
        'vsftpd_anonymous_enable' => 'NO',
        'vsftpd_local_enable' => 'YES',
        'vsftpd_write_enable' => 'YES',
        'vsftpd_local_umask' => '002',
      ),
    );
  }
}
