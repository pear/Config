<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
//try out all the different cases
$config->getRoot()->createDirective('STRING', 'this is a string');
$config->getRoot()->createDirective('STRING_WITH_QUOTES', 'double: " single:\' end');
$config->getRoot()->createDirective('NUMBER', 12345);
$config->getRoot()->createDirective('NUMERIC_STRING', '67890');
$config->getRoot()->createDirective('CONSTANT_NAME', 'NUMERIC_STRING');
$config->getRoot()->createDirective('TRUE', true);
$config->getRoot()->createDirective('FALSE', false);
$config->getRoot()->createDirective('TRUE_STRING', 'true');
$config->getRoot()->createDirective('FALSE_STRING', 'false');
$config->getRoot()->createDirective('lowercase_name', 'gets uppercased');

$config->getRoot()->createBlank();
$config->getRoot()->createDirective('AFTER_BLANK', '1');

$config->getRoot()->createSection('Comments');

$config->getRoot()->createComment('Some comment');
$config->getRoot()->createDirective('AFTER_COMMENT', '1');

echo $config->getRoot()->toString('phpconstants');
?>