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
// | Authors: Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config.php');

/**
* Config parser for PHP .ini files
* Faster because it uses parse_ini_file() but get rid of comments.
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_IniFile {

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @return mixed    returns a PEAR_ERROR, if error occurs or true if ok
    */
    function &parseDatasrc($datasrc, &$obj)
    {
        if (!file_exists($datasrc)) {
            return PEAR::raiseError("Datasource file does not exist.", null, PEAR_ERROR_RETURN);
        }
        $currentSection =& $obj->container;
        $confArray = parse_ini_file($datasrc, true);
        if (!$confArray) {
            return PEAR::raiseError("File '$datasrc' does not contain configuration data.", null, PEAR_ERROR_RETURN);
        }
        foreach ($confArray as $key => $value) {
            if (is_array($value)) {
                $currentSection =& $obj->container->createSection($key);
                foreach ($value as $directive => $content) {
                    $currentSection->createDirective($directive, $content);
                }
            } else {
                $currentSection->createDirective($key, $value);
            }
        }
        return true;
    } // end func parseDatasrc

    /**
    * Returns a formatted string of the object
    * @access public
    * @return string
    */
    function toString($configType = 'inifile', $options = array(), &$obj)
    {
        if (!isset($string)) {
            $string = '';
        }
        switch ($obj->type) {
            case 'directive':
                $string = $obj->name.' = '.$obj->content."\n";
                break;
            case 'section':
                if (!is_null($obj->parent)) {
                    $string = '['.$obj->name."]\n";
                }
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
} // end class Config_Container_IniFile
?>