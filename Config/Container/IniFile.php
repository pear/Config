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
    * @return mixed    returns a PEAR_ERROR, if error occurs or false if ok
    */
    function &parseDatasrc($datasrc)
    {
        if (!file_exists($datasrc)) {
            return PEAR::raiseError("Datasource file does not exist.", null, PEAR_ERROR_RETURN);
        }
        $currentSection =& $this->container;
        $confArray = parse_ini_file($datasrc, true);
        if (!$confArray) {
            return PEAR::raiseError("File '$datasrc' does not contain configuration data.", null, PEAR_ERROR_RETURN);
        }
        foreach ($confArray as $key => $value) {
            if (is_array($value)) {
                $currentSection =& $this->container->createSection($key);
                foreach ($value as $directive => $content) {
                    $currentSection->createDirective($directive, $content);
                }
            } else {
                $currentSection->createDirective($key, $value);
            }
        }
        return false;
    } // end func parseDatasrc

    /**
    * Returns a formatted string of the object
    * @access public
    * @return string
    */
    function toString($configType = 'inifile')
    {
        if (!isset($string)) {
            $string = '';
        }
        switch ($this->type) {
            case 'directive':
                $string = $this->name.' = '.$this->content."\n";
                break;
            case 'section':
                if (!is_null($this->parent)) {
                    $string = '['.$this->name."]\n";
                }
                if (count($this->children) > 0) {
                    for ($i = 0; $i < count($this->children); $i++) {
                        $string .= $this->children[$i]->toString($configType);
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