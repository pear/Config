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

require_once('XML/Parser.php');

/**
* Config parser for XML Files
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_XML extends XML_Parser {

    /**
    * This class options:
    * version (1.0)
    * encoding (ISO-8859-1)
    *
    * @var  array
    */
    var $options = array();

    /**
    * Container objects
    *
    * @var  array
    */
    var $containers = array();

    /**
    * Constructor
    *
    * @access public
    * @param    string  $options    (optional)Options to be used by renderer
    */
    function Config_Container_XML($options = array())
    {
        if (empty($options['version'])) {
            $options['version'] = '1.0';
        }
        if (empty($options['encoding'])) {
            $options['encoding'] = 'ISO-8859-1';
        }
        $this->options = $options;
    } // end constructor

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @param object $obj        reference to a config object
    * @return mixed returns a PEAR_ERROR, if error occurs or true if ok
    */
    function &parseDatasrc($datasrc, &$obj)
    {
        $this->folding = false;
        $this->XML_Parser(null, 'event');
        $this->containers[0] =& $obj->container;

        $err = $this->setInputFile($datasrc);
        if (PEAR::isError($err)) {
            return $err;
        }
        $err = $this->parse();
        if (PEAR::isError($err)) {
            return $err;
        }
        return true;
    } // end func parseDatasrc

    /**
    * Handler for the xml-data
    *
    * @param mixed  $xp         ignored
    * @param string $elem       name of the element
    * @param array  $attribs    attributes for the generated node
    *
    * @access private
    */
    function startHandler($xp, $elem, &$attribs)
    {
        $container =& new Config_Container('section', $elem, null, $attribs);
        $this->containers[] =& $container;
        return null;
    } // end func startHandler

    /**
    * Handler for the xml-data
    *
    * @param mixed  $xp         ignored
    * @param string $elem       name of the element
    *
    * @access private
    */
    function endHandler($xp, $elem)
    {
        $count = count($this->containers);
        $container =& $this->containers[$count-1];
        $currentSection =& $this->containers[$count-2];
        $currentSection->addItem($container);
        array_pop($this->containers);
        return null;
    } // end func endHandler

    /*
    * The xml character data handler
    *
    * @param mixed  $xp         ignored
    * @param string $data       PCDATA between tags
    *
    * @access private
    */
    function cdataHandler($xp, $cdata)
    {
        if (trim($cdata) != '') {
            $container =& $this->containers[count($this->containers)-1];
            $container->setType('directive');
            $container->setContent(trim($cdata));
        }
    } //  end func cdataHandler

    /**
    * Returns a formatted string of the object
    * @param    object  $obj    Container object to be output as string
    * @access   public
    * @return   string
    */
    function toString(&$obj)
    {
        static $deep = -1;
        $ident = '';
        if (!$obj->isRoot()) {
            // no indent for root
            $deep++;
            $ident = str_repeat('  ', $deep);
        } else {
            // Initialize string with xml declaration
            $string = '<?xml version="'.$this->options['version'].'" ';
            $string .= 'encoding="'.$this->options['encoding']."\"?>\n"; // <? Fix coloring
        }
        if (!isset($string)) {
            $string = '';
        }
        switch ($obj->type) {
            case 'directive':
                $string = $ident.'<'.$obj->name;
                foreach ($obj->attributes as $name => $value) {
                    $string .= ' ' . $name . '="' . $value . '"';
                }
                
                $string .= '>'.$obj->content.'</'.$obj->name.">\n";
                break;
            case 'section':
                $hasChildren = (count($obj->children) > 0) ? true : false;
                if (!$obj->isRoot()) {
                    $string = $ident.'<'.$obj->name;
                    foreach ($obj->attributes as $name => $value) {
                        $string .= ' ' . $name . '="' . $value . '"';
                    }
                }
                if ($hasChildren) {
                    if (!$obj->isRoot()) {
                        $string .= ">\n";
                    }
                    for ($i = 0; $i < count($obj->children); $i++) {
                        $string .= $this->toString($obj->getChild($i));
                    }
                }
                if (!$obj->isRoot()) {
                    if ($hasChildren) {
                        $string .= $ident.'</'.$obj->name.">\n";
                    } else {
                        $string .= "/>\n";
                    }
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
} // end class Config_Container_XML
?>