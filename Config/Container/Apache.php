<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Bertrand Mansion <bmansion@mamasam.com>                      |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config.php');

/**
* Config parser for apache httpd.conf files
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_Apache {

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @return mixed returns a PEAR_ERROR, if error occurs or true if ok
    */
    function &parseDatasrc($datasrc, &$obj)
    {
        if (!is_readable($datasrc)) {
            return PEAR::raiseError("Datasource file cannot be read.", null, PEAR_ERROR_RETURN);
        }
        $lines = file($datasrc);
        $n = 0;
        $lastline = '';
        $sections[0] =& $obj->container;
        foreach ($lines as $line) {
            $n++;
            if (preg_match('/^\s*[^#](.*)\s*\\$/', $line, $match)) {
                // directive on more than one line
                $lastline .= $match[1].' ';
                continue;
            }
            if ($lastline != '') {
                $line = $lastline.$line;
                $lastline = '';
            }
            if (preg_match('/^\s*#+\s*(.*?)\s*$/', $line, $match)) {
                // a comment
                $currentSection =& $sections[count($sections)-1];
                $currentSection->createComment($match[1]);
            } elseif (preg_match('/^\s*$/', $line)) {
                // a blank line
                $currentSection =& $sections[count($sections)-1];
                $currentSection->createBlank();
            } elseif (preg_match('/^\s*(\w+)(?:\s+(.*?)|)\s*$/', $line, $match)) {
                // a directive
                $currentSection =& $sections[count($sections)-1];
                $currentSection->createDirective($match[1], $match[2]);
            } elseif (preg_match('/^\s*<\s*(\w+)(?:\s+([^>]*)|\s*)>\s*$/', $line, $match)) {
                // a section opening
                if (!isset($match[2]))
                    $match[2] = '';
                $currentSection =& $sections[count($sections)-1];
                $sections[] =& $currentSection->createSection($match[1], $match[2]);
            } elseif (preg_match('/^\s*<\/\s*(\w+)\s*>\s*$/', $line, $match)) {
                // a section closing
                $currentSection =& $sections[count($sections)-1];
                if ($currentSection->name != $match[1]) {
                    return PEAR::raiseError("Syntax error in '$datasrc' at line $n.", null, PEAR_ERROR_RETURN);
                }
                array_pop($sections);
            } else {
                return PEAR::raiseError("Syntax error in '$datasrc' at line $n.", null, PEAR_ERROR_RETURN);
            }
        }
        return true;
    } // end func parseDatasrc

    /**
    * Returns a formatted string of the object
    * @access public
    * @return string
    */
    function toString($configType = 'apache', $options = array(), &$obj)
    {
        static $deep = -1;
        $ident = '';
        if (!$obj->isRoot()) {
            // no indent for root
            $deep++;
            $ident = str_repeat('  ', $deep);
        }
        if (!isset($string)) {
            $string = '';
        }
        switch ($obj->type) {
            case 'blank':
                $string = "\n";
                break;
            case 'comment':
                $string = $ident.'# '.$obj->content."\n";
                break;
            case 'directive':
                $string = $ident.$obj->name.' '.$obj->content."\n";
                break;
            case 'section':
                if (!$obj->isRoot()) {
                    $string = $ident.'<'.$obj->name;
                    $string .= ($obj->content != '') ? ' '.$obj->content.'>' : ' >';
                    $string .= "\n";
                }
                if (count($obj->children) > 0) {
                    for ($i = 0; $i < count($obj->children); $i++) {
                        $string .= $obj->children[$i]->toString($configType, $options);
                    }
                }
                if (!$obj->isRoot()) {
                    // object is not root
                    $string .= $ident.'</'.$obj->name.">\n";
                }
                break;
            default:
                $string = '';
        }
        if (!$obj->isRoot()) {
            $deep--;
        }
        return $string;
    } // end func toString
} // end class Config_Container_Apache
?>