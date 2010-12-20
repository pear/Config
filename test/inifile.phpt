--TEST--
inifile container tests
--FILE--
<?php
//since IniFile uses parse_ini_file, comments and blank lines are not
// preserved
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$datasrc = dirname(__FILE__) . '/inifile.ini';
$root = $config->parseConfig($datasrc, 'inifile');
echo $root->toString('inifile');
?>
--EXPECT--
preferredLanguage=PHP
[simpleValues]
versions=50
bugs=10
[ArrayValues]
bug[]=1234
bug[]=4390
[KeyValueArrays]
bug[foo]=431
bug[bar]=done
[mixedStringNumberKeys]
mixed[]=one
mixed[two]=two
mixed[three]=three
mixed[]=four
[quotedValues]
exclamation="ö!"
equals="="
equals2="="
semicolon=";"

