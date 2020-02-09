--TEST--
bug 2780 regression
--FILE--
<?php
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
    $c1 = new Config_Container('section', 'root');
    $c2 = new Config_Container();

    $c1->addItem($c2);
    // Convert your ini file to a php array config
    echo $c1->toString('phparray', array('name' => 'php_ini'));
?>
--EXPECT--
