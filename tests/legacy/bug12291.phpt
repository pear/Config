--TEST--
regression test for bug #12291: do not require space before newline
--FILE--
<?php
//Config previously required at least one space before the newline
// separator. This is not always needed, and so we broke backward compatibility
// by not collapsing all trailing spaces into one - but this is
// something nobody should have relied on or found to be a good idea :)

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$datasrc = dirname(__FILE__) . '/bug12291.ini';
$root = $config->parseConfig($datasrc, 'genericconf');
var_export($root->toArray());
?>
--EXPECT--
array (
  'root' => 
  array (
    'keyword' => 'foo,bar,baz',
    'keyword_with_space' => 'foo, bar, baz',
    'keyword_with_two_spaces' => 'foo,  bar, baz',
  ),
)
