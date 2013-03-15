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
        'default' => 'NO',
      ),
      array(
        'key' => 'vsftpd_local_enable',
        'default' => 'YES',
      ),
      array(
        'key' => 'vsftpd_write_enable',
        'default' => 'YES',
      ),
      array(
        'key' => 'vsftpd_local_umask',
        'default' => '002',
      ),
    );
  }
}
