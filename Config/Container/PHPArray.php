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
// | Authors: Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config/Container.php');

/**
* Config parser for common PHP configuration array
* such as found in the horde project.
*
* Options expected is:
* 'name' => 'conf'
* Name of the configuration array.
* Default is $conf[].
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_PHPArray {

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @return mixed    returns a PEAR_ERROR, if error occurs or true if ok
    */
    function &parseDatasrc($datasrc, &$obj)
    {
        if (is_null($datasrc)) {
            return PEAR::raiseError("Datasource file path cannot be null.", null, PEAR_ERROR_RETURN);
        }
        if (!file_exists($datasrc)) {
            return PEAR::raiseError("Datasource file does not exist.", null, PEAR_ERROR_RETURN);        
        } else {
            if (empty($obj->parserOptions['name'])) {
                $obj->parserOptions['name'] = 'conf'; // default array is $conf
            }
            include($datasrc);
            if (!isset(${$obj->parserOptions['name']}) || !is_array(${$obj->parserOptions['name']})) {
                return PEAR::raiseError("File '$datasrc' does not contain a required '".$obj->parserOptions['name']."' array.", null, PEAR_ERROR_RETURN);
            }
            $datasrc = ${$obj->parserOptions['name']};
        }
        $root =& $obj->container;
        Config_Container_PHPArray::_parseArray($datasrc, $root);
        return true;
    } // end func parseDatasrc

    /**
    * Parses the PHP array recursively
    * @param array $array    array values from the config file
    * @param object $container    reference to the container object
    * @access public
    * @return void
    */
    function _parseArray($array, &$container)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $isArrayCnt = 0;
                foreach ($value as $k => $v) {
                    if (is_int($k) && (($k == 0 && $isArrayCnt == 0) || $isArrayCnt > 0)) {
                        // some directives can be integers not starting from 0
                        // this test will keep their current values. ex: $conf[11] = 'abc'
                        $isArrayCnt++;
                    }
                }
                if ($isArrayCnt == count($value)) {
                    // 1 or more directives with the same name
                    foreach ($value as $k => $v) {
                        $container->createDirective("$key", $v);
                    }
                } else {
                    // new section
                    $section =& $container->createSection("$key");
                    Config_Container_PHPArray::_parseArray($value, $section);
                }
            } else {
                // new directive
                $container->createDirective("$key", $value);
            }
        }
    }

    /**
    * Returns a formatted string of the object
    * @access public
    * @return string
    */
    function toString($configType = 'phparray', $options = array(), &$obj)
    {
        static $childrenCount;

        if (!isset($string)) {
            $string = '';
            if (empty($options['name'])) {
                $options['name'] = 'conf';
            }
        }
        switch ($obj->type) {
            case 'directive':
                $string .= '$'.$options['name'];
                $string .= Config_Container_PHPArray::_getParentString($obj);
                if ($obj->parent->countChildren('directive', $obj->name) > 1) {
                    // we need to take care of directive set more than once
                    if (isset($childrenCount[$obj->name])) {
                        $childrenCount[$obj->name]++;
                    } else {
                        $childrenCount[$obj->name] = 0;
                    }
                    $string .= '['.$childrenCount[$obj->name].']';
                }
                $string .= ' = ';
                if (is_string($obj->content)) {
                    $string .= "'".$obj->content."';\n";
                } elseif (is_int($obj->content)) {
                    $string .= $obj->content.";\n";
                }
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

    /**
    * Returns a formatted string of the object parents
    * @access private
    * @return string
    */
    function _getParentString(&$cont)
    {
        $string = '['.$cont->name.']';
        if (!$cont->parent->isRoot()) {
            $string = Config_Container_PHPArray::_getParentString($cont->parent).$string;
        }
        return $string;
    } // end func _getParentString
} // end class Config_Container_PHPArray
?>