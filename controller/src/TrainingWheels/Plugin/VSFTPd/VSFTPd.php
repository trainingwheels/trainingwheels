<?php

namespace TrainingWheels\Plugin\VSFTPd;
use TrainingWheels\Plugin\PluginBase;

class VSFTPd extends PluginBase {

  public function getProvisionSteps() {
    return __DIR__ . '/provision/vsftpd.yml';
  }

  public function getPluginVars() {
    return array(
      array(
        'key' => 'vsftpd_anonymous_enable',
        'val' => 'NO',
      ),
      array(
        'key' => 'vsftpd_local_enable',
        'val' => 'YES',
      ),
      array(
        'key' => 'vsftpd_write_enable',
        'val' => 'YES',
      ),
      array(
        'key' => 'vsftpd_local_umask',
        'val' => '002',
      ),
    );
  }
}
