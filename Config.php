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

require_once('PEAR.php');

$GLOBALS['CONFIG_CONTAINERS'] = 
        array(
            'apache'        =>array('Config/Container/Apache.php','Config_Container_Apache'),
            'genericconf'   =>array('Config/Container/GenericConf.php','Config_Container_GenericConf'),
            'inifile'       =>array('Config/Container/IniFile.php','Config_Container_IniFile'),
            'inicommented'  =>array('Config/Container/IniCommented.php','Config_Container_IniCommented'),
            'phparray'      =>array('Config/Container/PHPArray.php','Config_Container_PHPArray')
        );

/**
* Config
*
* This class allows for parsing and editing of configuration datasources.
* Do not use this class only to read datasources because of the overhead
* it creates to keep track of the configuration structure.
*
* @author   Bertrand Mansion <bmansion@mamasam.com>
* @credit   Alexander Merz <alexander.merz@t-online.de>
* @credit   Christian Stocker <chregu@phant.ch>
* @package  Config
*/
class Config {

    /**
    * Datasource
    * Can be a file url, a dsn, an object...
    * @var mixed
    */
    var $datasrc;

    /**
    * Container object
    * @var object
    */
    var $container;

    /**
    * Constructor
    * Initializes a container and returns its root element.
    *
    * Requires the type of data container. If the container needs
    * special parameters upon initialization, set them in $options.
    * The format and required content of object 'datasrc' depend on the
    * chosen container.
    *
    * @access public
    * @param    string  $container  (optional)Container
    * @param    array   $options    (optional)Array of options for the container
    * @return   mixed   PEAR_Error on error or container root element
    */
    function Config($container, $options = null)
    {
        $container = strtolower($container);
        if (!Config::isContainerRegistered($container)) {
            return PEAR::raiseError("Container '$container' is not registered in Config::Config.", null, PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }
        $className = $GLOBALS['CONFIG_CONTAINERS'][$container][1];
        $includeFile = $GLOBALS['CONFIG_CONTAINERS'][$container][0];
        include_once ($includeFile);
        $obj = new stdclass;
        $this->container =& new $className('section', 'root', '', $obj);
        if (is_array($options)) {
            $this->container->setOptions($options);
        }
        return $this->container;
    } // end constructor

    /**
    * Returns true if container is registered
    * @access public
    * @param    string  $container  type of container
    * @return   bool
    */
    function isContainerRegistered($container)
    {
        return in_array($container, array_keys($GLOBALS['CONFIG_CONTAINERS']));
    } // end func isContainerRegistered

    /**
    * Parses the datasource contents
    * @param mixed  $datasrc  Datasource to work with
    * @access public
    * @return mixed PEAR_Error on error or Config_Container object
    */
    function &parseConfig($datasrc)
    {
        $this->datasrc = $datasrc;
        return $this->container->parseDatasrc($datasrc);
    } // end func &parseConfig

    /**
    * Writes the container contents to datasource
    * @param mixed  $datasrc  (optional)Datasource to write to
    * @access public
    * @return mixed PEAR_Error on error or true if ok
    */
    function writeConfig($datasrc = null)
    {
        if (!is_null($datasrc)) {
            $this->datasrc = $datasrc;
        }
        if (is_null($this->datasrc)) {
            return PEAR::raiseError("No datasource given for Config::writeConfig.", null, PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }
        if (!$this->container->writeDatasrc($this->datasrc)) {
            return PEAR::raiseError("Unable to write to datasource in Config::writeConfig.", null, PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }
        return true;
    } // end func writeConfig
} // end class Config
?>