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
        'host' => 'localhost',
        'user' => 'root',
        'passwd' => 'root',
        'name' => 'micarshop',
      ),
    ),
    'write' => 
    array (
      'host' => 'localhost',
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
  'langPath' => 'language',
  'viewPath' => 'views',
  'skinPath' => 'skin',
  'classes' => 'classes.*',
  'rewriteRule' => 'pathinfo',
  'theme' => 
  array (
    'pc' => 
    array (
      'sysdefault' => 'green',
      'xiaomi' => 'default',
      'sysseller' => 'green',
    ),
    'mobile' => 
    array (
      'sysdefault' => 'default',
      'mobile' => 'default',
      'sysseller_mobile' => 'default',
    ),
  ),
  'timezone' => 'Etc/GMT-8',
  'upload' => 'upload',
  'dbbackup' => 'backup/database',
  'safe' => 'cookie',
  'lang' => 'zh_sc',
  'debug' => '1',
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
  ),
  'encryptKey' => 'e739bcfd0615dfe25002c64a4fe4c518',
  'authorizeCode' => '2017021498932145',
  'jifencon' => '1000',
)?>