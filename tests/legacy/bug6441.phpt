--TEST--
regression test for bug #6441
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$datasrc = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bug6441.ini';

$configLoader = new Config();
$conf_obj = $configLoader->parseConfig($datasrc, 'inicommented');
$temp = $conf_obj->toArray();
$conf = $temp['root'];
var_export($conf);

?>
--EXPECT--
array (
  'val1' => '1',
  'val2' => '',
  'val3' => '1',
  'val4' => '',
  'val5' => '1',
  'val6' => '',
  'val7' => 'true',
  'val8' => 'false',
)
