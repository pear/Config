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

require_once('Config/Container.php');

/**
* Config parser for common PHP configuration array
* such as found in the horde project.
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_PHPArray extends Config_Container {

    /**
    * Use options to specify the name of your configuration array.
    * It defaults to $conf.
    * @var array
    */
    var $options = array('name' => 'conf');

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @return mixed    returns a PEAR_ERROR, if error occurs or the container itsef
    */
    function &parseDatasrc($datasrc, $first = true)
    {
        if (is_null($datasrc)) {
            return PEAR::raiseError("Datasource file path cannot be null.", null, PEAR_ERROR_RETURN);
        }
        if ($first) {
            if (!file_exists($datasrc)) {
                return PEAR::raiseError("Datasource file does not exist.", null, PEAR_ERROR_RETURN);        
            } else {
                include($datasrc);
                if (!isset(${$this->options['name']}) || !is_array(${$this->options['name']})) {
                    return PEAR::raiseError("File '$datasrc' does not contain a required '".$this->options['name']."' array.", null, PEAR_ERROR_RETURN);
                }
                $datasrc = ${$this->options['name']};
            }
        }
        foreach ($datasrc as $key => $value) {
            $currentSection =& $this;
            if (is_array($value)) {
                $isArrayCnt = 0;
                foreach ($value as $k => $v) {
                    if (is_int($k)) {
                        $isArrayCnt++;
                    }
                }
                if ($isArrayCnt == count($value)) {
                    $currentSection->addItem('directive', $key, $value);
                } else {
                    $section =& $currentSection->addItem('section', $key, '');
                    $section->parseDatasrc($value, false);
                }
            } else {
                // a directive
                $currentSection->addItem('directive', "$key", $value);
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
        if (!isset($string)) {
            $string = '';
        }
        switch ($this->type) {
            case 'directive':
                $string .= '$'.$this->options['name'];
                $string .= $this->_getParentString();
                $string .= ' = ';
                if (is_string($this->content)) {
                    $string .= "'".$this->content."';\n";
                } elseif (is_array($this->content)) {
                    $string .= 'array(';
                    foreach ($this->content as $value) {
                        $string .= "'".$value."', ";
                    }
                    $string = substr($string, 0, -2);
                    $string .= ");\n";
                } elseif (is_int($this->content)) {
                    $string .= $this->content.";\n";
                }
                break;
            case 'section':
                if (count($this->children) > 0) {
                    for ($i = 0; $i < count($this->children); $i++) {
                        $string .= $this->children[$i]->toString();
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
    function _getParentString()
    {
        $string = '['.$this->name.']';
        if (!is_null($this->parent->parent)) {
            $string = $this->parent->_getParentString().$string;
        }
        return $string;
    } // end func _getParentString
} // end class Config_Container_PHPArray
?>