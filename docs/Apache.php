<?php
/**
* Config.php example with Apache container
* @author 	Bertrand Mansion <bmansion@mamasam.com>
* @package	Config
*/
// $Id$

require_once('Config.php');

$datasrc = '/path/to/httpd.conf';
$conf =& new Config('apache');
$content =& $conf->parseConfig($datasrc);
if (PEAR::isError($content)) {
	die($content->getMessage());
}

// adding a new virtual-host

$content->addItem('blank');
$content->addItem('comment', '', 'My virtual host');
$content->addItem('blank');

$vhost =& $content->addItem('section', 'VirtualHost', '127.0.0.1');
$vhost->addItem('directive', 'DocumentRoot', '/usr/share/www');
$vhost->addItem('directive', 'ServerName', 'www.mamasam.com');
$location =& $vhost->addItem('section', 'Location', '/admin');
$location->addItem('directive', 'AuthType', 'basic');
$location->addItem('directive', 'Require', 'group admin');

// adding some directives Listen

if ($listen =& $content->getItem('directive', 'Listen')) {
	$content->insertItem('directive', 'Listen', '82', 'after', $listen);
} else {
	$listen =& $content->insertItem('directive', 'Listen', '81', 'bottom');
	if (PEAR::isError($listen)) {
		die($listen->getMessage());
	}
	$content->insertItem('directive', 'Listen', '82', 'after', $listen);
}

echo '<pre>'.htmlspecialchars($content->toString()).'</pre>';

// Writing the files
/*
if (!PEAR::isError($write = $conf->writeConfig('/tmp/httpd.conf'))) {
	echo 'done writing config<br>';
} else {
	die($write->getMessage());
}

if ($vhost->writeDatasrc('/tmp/vhost.conf')) {
	echo 'done writing vhost<br>';
}
*/
?>