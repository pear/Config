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
require_once('Config/Container.php');

$GLOBALS['CONFIG_TYPES'] = 
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
    * Type of datasource for config
    * Ex: IniCommented, Apache...
    * @var string
    */
    var $configType = '';

    /**
    * Options for parser
    * @var string
    */
    var $parserOptions = array();

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
    * @param    string  $configType     (optional)Type of configuration
    * @return   mixed   PEAR_Error on error or container root element
    */
    function Config($configType = '', $options = array())
    {
        if ($configType != '' && $error = $this->_checkConfigType($configType)) {
            return PEAR::raiseError($error.' constructor.', PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }
        $this->parserOptions = $options;
        $this->container =& new Config_Container('section', 'root', '');
        $this->container->parent = null;
    } // end constructor

    /**
    * Returns true if container is registered
    * @access public
    * @param    string  $configType  Type of config
    * @return   bool
    */
    function isConfigTypeRegistered($configType)
    {
        return in_array($configType, array_keys($GLOBALS['CONFIG_TYPES']));
    } // end func isConfigTypeRegistered

    /**
    * Returns the root container for this config object
    * @access public
    * @return   object  root container object
    */
    function &getRoot()
    {
        return $this->container;
    } // end func getRoot

    /**
    * Reset the root container to accept the object passed as parameter as a child
    * @param object  $rootContainer  container to be used as the first child to root
    * @access public
    * @return   true on success or PEAR_Error
    */
    function setRoot(&$rootContainer)
    {
        if (is_object($rootContainer) && is_a($rootContainer, 'config_container')) {
            unset($this->container);
            $this->container =& new Config_Container('section', 'root', '');
            $this->container->parent = null;
            $this->container->children[0] =& $rootContainer;
            return true;
        } else {
            return PEAR::raiseError("Config::setRoot only accepts object of Config_Container type.", null, PEAR_ERROR_RETURN);
        }
    } // end func setRoot

    /**
    * Parses the datasource contents
    * This will set the root container for this config object
    *
    * @param mixed  $datasrc  Datasource to work with
    * @access public
    * @return mixed PEAR_Error on error or Config_Container object
    */
    function &parseConfig($datasrc, $configType = '', $options = array())
    {
        $this->datasrc = $datasrc;
        if ($error = $this->_checkConfigType($configType)) {
            return PEAR::raiseError($error.'::parseConfig.', PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }
        if (count($options) > 0) {
            $this->parserOptions = $options;
        }
        $className = $GLOBALS['CONFIG_TYPES'][$this->configType][1];
        $includeFile = $GLOBALS['CONFIG_TYPES'][$this->configType][0];
        include_once($includeFile);

        $error = call_user_func(array($className, 'parseDatasrc'), $this->datasrc, $this);
        if ($error !== true) {
            return $error;
        }
        return $this->container;
    } // end func &parseConfig

    /**
    * Checks if config type can be used
    * @param string  $configType  Config type to use (inicommented, apache, ...)
    * @access public
    * @return mixed string on error or false if ok
    */
    function _checkConfigType($configType)
    {
        if ($configType == '') {
            if ($this->configType == '') {
                return "You must specify a config type in Config";
            }
        } else {
            $configType = strtolower($configType);
            if (!Config::isConfigTypeRegistered($configType)) {
                return "Configuration type '$configType' is not registered in Config";
            }
            $this->configType = $configType;
        }
        return false;
    } // end func _checkConfigType

    /**
    * Checks if datasource can be set
    * @param mixed  $datasrc  Datasource to write to
    * @access public
    * @return mixed string on error or false if ok
    */
    function _checkDatasrc($datasrc)
    {
        if (!is_null($datasrc)) {
            $this->datasrc = $datasrc;
        }
        if (is_null($this->datasrc)) {
            return "No datasource given in Config";
        }
        return false;
    } // end func _checkDatasrc

    /**
    * Writes the container contents to datasource
    * @param mixed  $datasrc  (optional)Datasource to write to
    * @access public
    * @return mixed PEAR_Error on error or true if ok
    */
    function writeConfig($datasrc = null, $configType = '')
    {
        if ($error = $this->_checkDatasrc($datasrc)) {
            return PEAR::raiseError($error.'::writeConfig.', null, PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }
        if ($error = $this->_checkConfigType($configType)) {
            return PEAR::raiseError($error.'::writeConfig.', null, PEAR_ERROR_TRIGGER, E_USER_WARNING);
        }

        return $this->container->writeDatasrc($this->datasrc, $this->configType);
    } // end func writeConfig
} // end class Config
?>