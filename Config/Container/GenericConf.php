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
// | Author: Bertrand Mansion <bmansion@mamasam.com>                      |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config.php');

/**
* Config parser for  generic .conf files like
* htdig.conf...
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_GenericConf {

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @return mixed returns a PEAR_ERROR, if error occurs or true if ok
    */
    function &parseDatasrc($datasrc, &$obj)
    {
        if (is_null($datasrc) || !is_readable($datasrc)) {
            return PEAR::raiseError("Datasource file cannot be read.", null, PEAR_ERROR_RETURN);
        }
        
        // Set default options for parser
        
        if (empty($obj->parserOptions['comment'])) {
            $obj->parserOptions['comment'] = '#';
        }
        if (empty($obj->parserOptions['equals'])) {
            $obj->parserOptions['equals'] = ':';
        }
        if (empty($obj->parserOptions['newline'])) {
            $obj->parserOptions['newline'] = '\\';
        }

        $lines = file($datasrc);
        $n = 0;
        $lastline = '';
        $root =& $obj->container;
        foreach ($lines as $line) {
            $n++;
            if (preg_match('/^\s*(.*)\s+'.$obj->parserOptions['newline'].'\s*$/', $line, $match)) {
                // directive on more than one line
                $lastline .= $match[1].' ';
                continue;
            }
            if ($lastline != '') {
                $line = $lastline.$line;
                $lastline = '';
            }
            if (preg_match('/^\s*'.$obj->parserOptions['comment'].'+\s*(.*?)\s*$/', $line, $match)) {
                // a comment
                $root->addComment($match[1]);
            } elseif (preg_match('/^\s*$/', $line)) {
                // a blank line
                $root->addBlank();
            } elseif (preg_match('/^\s*(\w+)'.$obj->parserOptions['equals'].'\s*((.*?)|)\s*$/', $line, $match)) {
                // a directive
                $root->addDirective($match[1], $match[2]);
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
    function toString($configType = 'genericconf', $options = array(), &$obj)
    {
        if (empty($string)) {
            $string = '';
            if (empty($options['comment'])) {
                $options['comment'] = '#';
            }
            if (empty($options['equals'])) {
                $options['equals'] = ':';
            }
        }
        switch ($obj->type) {
            case 'blank':
                $string = "\n";
                break;
            case 'comment':
                $string = $options['comment'].' '.$obj->content."\n";
                break;
            case 'directive':
                $string = $obj->name.$options['equals'].' '.$obj->content."\n";
                break;
            case 'section':
                if (count($obj->children) > 0) {
                    for ($i = 0; $i < count($obj->children); $i++) {
                        $string .= $obj->children[$i]->toString($configType, $options);
                    }
                }
                break;
            default:
                $string = '';
        }
        return $string;
    } // end func toString
} // end class Config_Container_GenericConf
?>