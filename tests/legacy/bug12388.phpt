--TEST--
Test for request #12388: Allow spaces after the key
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$datasrc = dirname(__FILE__) . '/bug12388.ini';
$root = $config->parseConfig($datasrc, 'genericconf');
var_export($root->toArray());
?>
--EXPECT--
array (
  'root' => 
  array (
    'nospace' => 'value',
    'space_before' => 'value',
    'space_after' => 'value',
    'two_spaces_after' => 'value',
  ),
)
