<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config/Container.php');

/**
* Config parser for  generic .conf files like
* htdig.conf...
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_GenericConf extends Config_Container {

	/**
	* Use options to specify your .conf specificities
	* ie, comment char, equal sign, config line feed.
	* @var array
	*/
	var $options = array(
					'comment' => '#',
					'equals'  => ':',
					'newline' => '\\');
	
	/**
	* Parses the data of the given configuration file
	*
	* @access public
	* @param string $datasrc	path to the configuration file
	* @return mixed	returns a PEAR_ERROR, if error occurs or the container itsef
	*/
	function &parseDatasrc($datasrc)
	{
		if (is_null($datasrc) || !is_readable($datasrc)) {
			return PEAR::raiseError("Datasource file cannot be read.", null, PEAR_ERROR_RETURN);
		}
		$lines = file($datasrc);
		$n = 0;
		$lastline = '';
		$sections[0] =& $this;
		foreach ($lines as $line) {
       		$n++;
        	if (preg_match('/^\s*(.*)\s+'.$this->options['newline'].'\s*$/', $line, $match)) {
            	// directive on more than one line
            	echo $match[1].'<br>';
            	$lastline .= $match[1].' ';
            	continue;
        	}
        	if ($lastline != '') {
        		$line = $lastline.$line;
        		$lastline = '';
        	}
			if (preg_match('/^\s*'.$this->options['comment'].'+\s*(.*?)\s*$/', $line, $match)) {
				// a comment
				$currentSection =& $sections[count($sections)-1];
				$currentSection->addItem('comment', '', $match[1]);
			} elseif (preg_match('/^\s*$/', $line)) {
				// a blank line
				$currentSection =& $sections[count($sections)-1];
				$currentSection->addItem('blank', '', '');
			} elseif (preg_match('/^\s*(\w+)'.$this->options['equals'].'\s*((.*?)|)\s*$/', $line, $match)) {
				// a directive
				$currentSection =& $sections[count($sections)-1];
				$currentSection->addItem('directive', $match[1], $match[2]);
			} else {
				return PEAR::raiseError("Syntax error in '$datasrc' at line $n.", null, PEAR_ERROR_RETURN);
			}
		}
		return $this;
	} // end func parseDatasrc

	/**
	* Returns a formatted string of the object
	* @access public
	* @return string
	*/
	function toString()
	{
        static $deep = -1;
        $ident = '';
        if (!is_null($this->parent)) {
        	// no indent for root
        	$deep++;
			$ident = str_repeat('  ', $deep);
        }
		if (!isset($string)) {
			$string = '';
		}
		switch ($this->type) {
			case 'blank':
				$string = "\n";
				break;
			case 'comment':
				$string = $ident.$this->options['comment'].' '.$this->content."\n";
				break;
			case 'directive':
				$string = $ident.$this->name.$this->options['equals'].' '.$this->content."\n";
				break;
			case 'section':
				if (!is_null($this->parent)) {
					$string = $ident.'<'.$this->name;
					$string .= ($this->content != '') ? ' '.$this->content.'>' : ' >';
					$string .= "\n";
				}
				if (count($this->children) > 0) {
					for ($i = 0; $i < count($this->children); $i++) {
						$string .= $this->children[$i]->toString();
					}
				}
				if (!is_null($this->parent)) {
					// object is not root
					$string .= $ident.'</'.$this->name.">\n";
				}
				break;
			default:
				$string = '';
		}
        if (!is_null($this->parent)) {
        	$deep--;
        }
		return $string;
	} // end func toString
} // end class Config_Container_GenericConf
?>