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
    * @return   object  reference to config's root container object
    */
    function Config()
    {
        $this->container =& new Config_Container('section', 'root');
    } // end constructor

    /**
    * Returns true if container is registered
    * @access public
    * @param    string  $configType  Type of config
    * @return   bool
    */
    function isConfigTypeRegistered($configType)
    {
        return isset($GLOBALS['CONFIG_TYPES'][strtolower($configType)]);
    } // end func isConfigTypeRegistered

    /**
    * Returns the root container for this config object
    * @access public
    * @return   object  reference to config's root container object
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
        if (is_object($rootContainer) && get_class($rootContainer) == 'config_container') {
            unset($this->container);
            $this->container =& new Config_Container('section', 'root');
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
    function &parseConfig($datasrc, $configType, $options = array())
    {
        $configType = strtolower($configType);
        if (!$this->isConfigTypeRegistered($configType)) {
            return PEAR::raiseError("Configuration type '$configType' is not registered in Config::parseConfig.", null, PEAR_ERROR_RETURN);
        }
        $includeFile = $GLOBALS['CONFIG_TYPES'][$configType][0];
        $className = $GLOBALS['CONFIG_TYPES'][$configType][1];
        include_once($includeFile);

        $parser = new $className($options);
        $error = $parser->parseDatasrc($datasrc, $this);
        if ($error !== true) {
            return $error;
        }
        $this->parserOptions = $parser->options;
        $this->datasrc = $datasrc;
        $this->configType = $configType;
        return $this->container;
    } // end func &parseConfig

    /**
    * Writes the container contents to datasource
    * @param mixed  $datasrc  (optional)Datasource to write to
    * @access public
    * @return mixed PEAR_Error on error or true if ok
    */
    function writeConfig($datasrc = null, $configType = null, $options = array())
    {
        if (empty($datasrc)) {
            $datasrc = $this->datasrc;
        }
        if (empty($configType)) {
            $configType = $this->configType;
        }
        if (empty($options)) {
            $options = $this->parserOptions;
        }
        return $this->container->writeDatasrc($datasrc, $configType, $options);
    } // end func writeConfig
} // end class Config
?>