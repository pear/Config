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
// | Authors: Alan Knowles <alan@akbkhome.com>                 |
// +----------------------------------------------------------------------+
//
// $Id$

require_once( "Config/Container.php" ) ;

/**
* Config-API-Implemtentation for Ini-Files using parse_ini_file
*
* Note this is faster than the IniFile container, however there are 
* a number of restrictions : like file size etc. see
* http://www.php.net/parse_ini_file for more details
*
* This class implements the Config-API based on ConfigDefault
*
* @author      Alan Knowles <alan@akbkhome.com>
* @access      public
* @version     $Id$
* @package     Config
*/

class Config_Container_phpIniFile extends Config_Container {

/**
* contains the features given by parseInput
* @var array
* @see parseInput()
*/
var $feature = array() ;

/**
* parses the input of the given data source
*
* The Data Source can be a string with the file or a array of strings with the files to read,
* so datasrc requires a existing file.
* The feature-array have to contain the comment char array("cc" => Comment char)
*
* @access public
* @param string $datasrc  Name of the datasource to parse
* @param array $feature   Contains a hash of features
* @return mixed				returns a PEAR_ERROR, if error occurs
*/

function parseInput( $datasrc = "", $feature = array( "cc" => ";") )
{

    // Checking if $datasrc is a array, then call parseInput with
    // each file
    if( is_array( $datasrc) ) {
         foreach( $datasrc as $file ) {
                  $ret = $this -> parseInput( $file, $feature ) ;
                  if( PEAR::isError( $ret) ) {
                      return $ret ;
                  }
         }
    }


  
    $this -> datasrc = $datasrc ;
    $this -> feature = $feature ;
    
    if( !file_exists( $datasrc ) )
        return new PEAR_Error( "File '".$datasrc."' doesn't exists!", 31, PEAR_ERROR_RETURN, null, null );
        
    $array = parse_ini_file($datasrc,TRUE);
    if (!$array)           
        return new PEAR_Error( "File '".$datasrc."' does not contain configuration data", 31, PEAR_ERROR_RETURN, null, null );

    foreach($array as $block => $items) {
        if (!$items) continue;
	$this->data['/'.$block] = $items;
    } 


} // end func parseInput


/**
* Relay for writeInput in Config_Container_IniFile::writeInput();
* uses the wonderfull trick of $this being inherited on static method calls
*
* See Documentation for the IniFile Container for more details
*
* @access public
* @param string $datasrc    Name of the datasource to parse
* @param boolean    $preserve   preserving behavior
* @return object PEAR_Error
* @see  Config_Container_IniFile::writeInput()
*/

function writeInput( $datasrc = "", $preserve = true  )
{

    require_once('Config/Container/IniFile.php'); 
    return Config_Container_IniFile::writeInput($datasrc,$preserve);
} // end func writeInput


}; // end class Config_IniFile



?>
