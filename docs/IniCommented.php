<?php
/**
* Config.php example with IniCommented container
* This container is for PHP .ini files, when you want
* to keep your comments. If you don't use comments, you'd rather
* use the IniFile.php container.
* @author 	Bertrand Mansion <bmansion@mamasam.com>
* @package	Config
*/
// $Id$

require_once('Config.php');

$datasrc = '/path/to/php.ini';

$conf =& new Config('inicommented');
$content =& $conf->parseConfig($datasrc);
if (PEAR::isError($content)) {
	die($content->getMessage());
}

echo '<pre>'.htmlspecialchars($content->toString('phparray')).'</pre>';
?>