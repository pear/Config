--TEST--
bug 2742 regression
--FILE--
<?php
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());

    require_once '../Config.php' ;

    $datasrc = dirname(__FILE__) . '/bug2742.ini';
    $phpIni = new Config();
    $root =& $phpIni->parseConfig($datasrc, 'inicommented');
    if (PEAR::isError($root)) {
    	die($root->getMessage());
    }

    // Convert your ini file to a php array config

    echo $root->toString('phparray', array('name' => 'php_ini'));
?>
--EXPECT--
$php_ini['var'] = '1234';