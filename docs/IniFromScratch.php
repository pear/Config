<?php
/**
* Config.php example with IniCommented container
* This time, we will create a new config file from scratch.
* @author 	Bertrand Mansion <bmansion@mamasam.com>
* @package	Config
*/
// $Id$

require_once('Config.php');

$file = '/tmp/myconf.ini';

// 1. We create a new config container

$conf =& new Config('inicommented');
$root =& $conf->getRoot();

// 2. We use addItem() helper methods to fill the container

$root->addComment('Test for Config with IniCommented container');
$root->addBlank();

// 3. We create a new section called 'DBConf' in our container

$DBConf =& $root->addSection('DBConf');
$DBConf->addComment('DB configuration options');
$DBConf->addDirective('type', 'mysql');
$DBConf->addDirective('login', 'root');
$DBConf->addDirective('db', 'test');
$DBConf->addDirective('host', 'localhost');
$DBConf->addBlank();

// 4. We create another section 'colors' from scratch this time

$colors =& new Config_Container_IniCommented('section', 'colors');
$colors->addComment('Color configuration');
$colors->addDirective('bgcolor', '#FFFFFF');
$colors->addDirective('rows', '#CCCCCC');
$colors->addDirective('lines', '#FF0000');
$colors->addBlank();

// 4. We add the new 'colors' section to our current config

$ConfColors =& $root->addSection($colors);

// 5. Oops, our 'DBConf' login has changed !

$DBConf_login =& $DBConf->setDirective('login', 'mysql_user');

// 6. Oops, we forgot to set the 'DBConf' password directive after 'login' !

$DBConf->insertItem('directive', 'password', 'mysql_pass', 'after', $DBConf_login);

// 7. Will add a new bgcolor color after the first one

$bgcolor =& $ConfColors->getItem('directive', 'bgcolor');
$ConfColors->insertItem('directive', 'bgcolor2', '#00FF00', 'after', $bgcolor);

// 8. That's it. Now save and display

echo '<pre>'.htmlspecialchars($root->toString()).'</pre>';

if (!PEAR::isError($write = $conf->writeConfig($file))) {
	echo "Done writing config in $file.<br />";
} else {
	die($write->getMessage());
}
?>