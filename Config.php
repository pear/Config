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
            'phparray'      =>array('Config/Container/PHPArray.php','Config_Container_PHPArray'),
            'xml'           =>array('Config/Container/XML.php','Config_Container_XML')
            );

/**
* Config
*
* This class allows for parsing and editing of configuration datasources.
* Do not use this class only to read datasources because of the overhead
* it creates to keep track of the configuration structure.
*
* @author   Bertrand Mansion <bmansion@mamasam.com>
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
    * Creates a root container
    *
    * @access public
    */
    function Config()
    {
        $this->container =& new Config_Container('section', 'root');
    } // end constructor

    /**
    * Returns true if container is registered
    *
    * @param    string  $configType  Type of config
    * @access public
    * @return   bool
    */
    function isConfigTypeRegistered($configType)
    {
        return isset($GLOBALS['CONFIG_TYPES'][strtolower($configType)]);
    } // end func isConfigTypeRegistered

    /**
    * Returns the root container for this config object
    *
    * @access public
    * @return   object  reference to config's root container object
    */
    function &getRoot()
    {
        return $this->container;
    } // end func getRoot

    /**
    * Sets the content of the root Config_container object.
    *
    * This method will replace the current child of the root
    * Config_Container object by the given object.
    *
    * @param object  $rootContainer  container to be used as the first child to root
    * @access public
    * @return   mixed    true on success or PEAR_Error
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
    *
    * This method will parse the datasource given and fill the root 
    * Config_Container object with other Config_Container objects.
    *
    * @param mixed   $datasrc     Datasource to parse
    * @param string  $configType  Type of configuration
    * @param array   $options     Options for the parser
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
    * Writes the container contents to the datasource.
    *
    * @param mixed   $datasrc     Datasource to write to
    * @param string  $configType  Type of configuration
    * @param array   $options     Options for config container
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