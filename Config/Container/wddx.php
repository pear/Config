<?php
// +---------------------------------------------------------------------+
// | PHP Version 4                                                       |
// +---------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group            |
// +---------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is       |
// | available at through the world-wide-web at                          |
// | http://www.php.net/license/2_02.txt.                                |
// | If you did not receive a copy of the PHP license and areunable to   |
// | obtain it through the world-wide-web, please send a note to         |
// | license@php.net so we can mail you a copy immediately.              |
// +---------------------------------------------------------------------+
// | Authors: Alexander Merz <alexander.merz@t-online.de>                |
// |          Christian Stocker <chregu@phant.ch>                        |
// |          Robert Janeczek <rashid@ds.pg.gda.pl>                      |
// +---------------------------------------------------------------------+
//
// $Id$

require_once( 'Config/Container.php' ) ;

/**
* Config-API-Implemtentation for wddx Files
*
* This class implements the Config-API based on ConfigDefault
*
* @author      Robert Janeczek <rashid@ds.pg.gda.pl>
* @access      public
* @version     $Id$
* @package     Config
*/

class Config_Container_wddx extends Config_Container {
    /**
    * parses the input of the given data source
    *
    * The Data Source is a file, so datasrc requires a existing file.
    * No features available for now, this argument is ignored
    *
    * @access public
    * @param string $datasrc  Name of the datasource to parse
    * @param array $feature   Contains a hash of features
    * @return mixed             returns a PEAR_ERROR, if error occurs
    */

    function parseInput( $datasrc = "", $feature = array() )
    {
        $this -> datasrc = $datasrc;
        
        if( file_exists( $datasrc ) )
        {
            $fd = fopen($datasrc, 'r');
            $wddxstring = fread( $fd, filesize( $datasrc ) );
            fclose( $fd );            
            $data = wddx_deserialize($wddxstring);
            $this->convertFromInput($data);
        } else {
            return new PEAR_Error( "File '".$datasrc."' doesn't
                                   exists!", 31, PEAR_ERROR_RETURN, null, null );        
        }         
    } // end func parseInput
    
    /**
    * writes the data to the given data source or if not given to the datasource of parseInput
    * If $datasrc was a array, the last file will used.
    *
    * See parseInput for $datasrc. The second argument $preserve is ignored
    *
    * @access public
    * @param string $datasrc    Name of the datasource to parse
    * @param boolean    $preserve   preserving behavior
    * @return object PEAR_Error
    * @see parseInput()
    */    
    
    function writeInput( $datasrc = "", $preserve = true  )
    {        
        if( empty( $datasrc ) ) {
            $datasrc = $this -> datasrc ;
        } elseif( !file_exists( $datasrc )) {
            return new PEAR_Error("File '$datasrc' doesn't exist", 41, PEAR_ERROR_RETURN, null,
                              null );
        }

        $data = $this->convertForOutput();
        $wddx = wddx_serialize_value($data);
        $fp = fopen($datasrc, 'w');
        fwrite($fp, $wddx);
        fclose($fp);        
    } // end func writeInput

    /**
    * converts raw wddx data to Config`s internal data structure
    *
    * Data is raw wddx file content - a string
    * Level contains start path for recursive calls
    *
    * @access public
    * @param string $data  wddx data
    * @param array $level   actual path for recursion
    * @see convertForOutput()
    */    
    
    function convertFromInput($data, $level = '') {
        if(!is_array($data)) $data = array();

        foreach($data as $key => $val) {
			if(substr($key, 0, 1) == '/') {
                $children[] = $key;
                $this->convertFromInput($val, $level.$key);
            } else {
				$values[$key] = $val;
            }            
        }
        
        if($level == '') $level = '/';
        
        if(count($values) > 0) {
            $this->data[$level] = $values;
        }
        
        if(count($children) > 0) {
            $this->data[$level]['children'] = $children;
        }
    } // end func convertFromInput
    
    /**
    * converts Config`s internal data structure to raw wddx data 
    *
    * Makes exacly the opposite of convertFromInput to prepare
    * configuration data for writing back to file
    *
    * @access public
    * @return array     converted configuration        
    * @see convertFromInput()
    */  
    
    function convertForOutput() {
        $out = array();
        		
        if(!is_array($this->data)) $this->data = array();
		
        foreach ($this->data as $path => $files) {
            $path = explode('/', substr($path, 1));
            $tmp =& $out;
            foreach ($path as $dir) { 
                if($dir === '') continue;
                if (!isset($tmp['/'.$dir])) {
                    $tmp['/'.$dir] = array();
                }
                $tmp =& $tmp['/'.$dir];            
            }
            
            foreach($files as $key => $val) {
			    if($key !== 'children') {
                    $tmp[$key] = $val;
                }
            }
        }
        return $out;
    }  // end func convertForOutput
    
}//end class Config_Container_wddx

?>
