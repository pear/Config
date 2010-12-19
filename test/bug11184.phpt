--TEST--
regression test for bug #11184
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$path = "bug11184-output.php";
$type = "phparray";

$options = array (
		"name" => "actualValues",
		);

$expectedValues = array (
		"single-quotes-test" => "Text with 'simple quotes'",
		"double-quotes-test" => 'Text with "double quotes"',
		);

echo "Expected values: ";
print_r($expectedValues);

$config = new Config;
$config->parseConfig($expectedValues, $type, $options);
$config->writeConfig($path, $type, $options);

require_once($path);

echo "Actual values: ";
print_r($actualValues);

?>
--CLEAN--
<?php
unlink('bug11184-output.php');
?>
--EXPECT--
Expected values: Array
(
    [single-quotes-test] => Text with 'simple quotes'
    [double-quotes-test] => Text with "double quotes"
)
Actual values: Array
(
    [single-quotes-test] => Text with 'simple quotes'
    [double-quotes-test] => Text with "double quotes"
)

