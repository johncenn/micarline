<?php return array (
  'logs' => 
  array (
    'path' => 'backup/logs',
    'type' => 'file',
  ),
  'DB' => 
  array (
    'type' => 'mysqli',
    'tablePre' => 'micarshop_',
    'read' => 
    array (
      0 => 
      array (
        'host' => 'localhost:3306',
        'user' => 'root',
        'passwd' => 'root',
        'name' => 'micarshop',
      ),
    ),
    'write' => 
    array (
      'host' => 'localhost:3306',
      'user' => 'root',
      'passwd' => 'root',
      'name' => 'micarshop',
    ),
  ),
  'interceptor' => 
  array (
    0 => 'themeroute@onCreateController',
    1 => 'layoutroute@onCreateView',
    2 => 'plugin',
  ),
  'theme' => 
  array (
    'pc' => 
    array (
      'sysdefault' => 'green',
      'sysseller' => 'green',
      'xiaomi' => 'default',
    ),
    'mobile' => 
    array (
      'sysdefault' => 'default',
      'sysseller' => 'default',
      'mobile' => 'default',
    ),
  ),
  'timezone' => 'Etc/GMT-8',
  'upload' => 'upload',
  'dbbackup' => 'backup/database',
  'safe' => 'cookie',
  'lang' => 'zh_sc',
  'debug' => '2',
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
  ),
  'encryptKey' => '86ce111530a16dcccae92257c2e04a0e',
  'authorizeCode' => '2017021498932145',
)?>