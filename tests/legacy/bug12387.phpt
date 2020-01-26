--TEST--
Test for request #12387: Allow hyphens in the key
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$datasrc = dirname(__FILE__) . '/bug12387.ini';
$root = $config->parseConfig($datasrc, 'genericconf');
var_export($root->toArray());
?>
--EXPECT--
array (
  'root' => 
  array (
    'hy-phen' => 'value',
  ),
)
