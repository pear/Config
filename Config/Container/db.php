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
// | Authors: Christian Stocker <chregu@phant.ch>                         |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config/Container.php');
require_once('DB.php');

/**
* Config-API-Implemtentation for DB-Ini-Entries
*
* This class implements the Config-API based on ConfigDefault
* The Table structure should be as follow by default:
* CREATE TABLE config (
*   datasrc varchar(50) NOT NULL,
*   block varchar(50) NOT NULL,
*   name varchar(50) NOT NULL,
*   value varchar(50)
* );
* You can name the table and fields as you prefer, but you have to supply
* these values in the $feature array as parameter for the constructor.
*
* @author      Christian Stocker <chregu@phant.ch>
* @access      public
* @version     $Id$
* @package     Config
*/
class Config_Container_db extends Config_Container {

    /**
    * Contains the features given by parseInput or the constructor
    * @var array
    */
    var $feature = array(
        'table'      => 'config',
        'blockcol'   => 'block',
        'namecol'    => 'name',
        'valuecol'   => 'value',
        'datasrccol' => 'datasrc',
        'dsn'        => ''
        );

    /**
     * DB object
     * @var object
     */
    var $db = null;

    /**
    * Constructor
    * Connects to the DB via the PEAR::DB-class
    *
    * @param  mixed $options    can be a DSN string or an array of options
    */
    function Config_Container_db ($feature)
    {
        if (is_array($feature)) {
            $this->setFeatures($feature, array('datasrccol', 'table',
                'valuecol','namecol','blockcol', 'dsn'));
            if ($this->feature['dsn'] == '') {
                return new DB_Error('No connection parameters specified.');
            }
        } else {
            $this->feature['dsn'] = $feature;
        }
    } // end constructor
    
    /**
     * Connect to database by using the given DSN string or DB object
     *
     * @access private
     * @param  mixed    DSN string or DB object
     * @return mixed    Object on error, otherwise bool
     */
    function _connect($dsn)
    {
        if (is_string($dsn)) {
            $this->db = DB::Connect($dsn);
        } elseif (get_parent_class($dsn) == 'db_common') {
            $this->db = $dsn;
        } elseif (is_object($dsn) && DB::isError($dsn)) {
            return new DB_Error($dsn->code, PEAR_ERROR_DIE);
        } else {
            return new PEAR_Error("The given dsn was not valid in file " . __FILE__ . " at line " . __LINE__,
                                  41,
                                  PEAR_ERROR_RETURN,
                                  null,
                                  null
                                  );
        }
        if (DB::isError($this->db)) {
            return new DB_Error($this->db->code, PEAR_ERROR_DIE);
        } else {
            return true;
        }
    } // end func _connect

    /**
     * Prepare query to the database
     *
     * This function checks if we have already opened a connection to
     * the database. If that's not the case, a new connection is opened.
     * After that the query is passed to the database.
     *
     * @access public
     * @param  string Query string
     * @return True or DB_Error
     */
    function query($query)
    {
        if (!DB::isConnection($this->db)) {
            $this->_connect($this->feature['dsn']);
        }
        return $this->db->query($query);
    } // end func query

    /**
    * Parses the input of the given data source
    *
    * The Data Source can be a string with field-name of the datasrc field in the db
    *
    * @access public
    * @param string $datasrc  Name of the datasource to parse
    * @param array $feature   Contains a hash of features
    * @return mixed returns a DB_ERROR, if error occurs    
    */
    function parseInput($datasrc = '', $feature = array())
    {
        $this->setFeatures($feature,  array('datasrccol', 'table',
            'valuecol','namecol','blockcol'));

        $query = sprintf("SELECT %s FROM %s WHERE %s = '%s'",
                    $this->feature['blockcol'].", ".
                    $this->feature['namecol'].", ".
                    $this->feature['valuecol'],
                    $this->feature['table'],
                    $this->feature['datasrccol'],
                    $datasrc
                    );

        $res = $this->query($query);
        if (DB::isError($res)) {
            return new DB_Error($res->code, PEAR_ERROR_DIE);
        } else {
            while ($entry = $res->fetchRow()) {
                $this->data[$entry[0]][$entry[1]] = $entry[2];
            }
        }
    } // end func parseInput

    /**
    * Writes the data to the given data source or if not given to the datasource of parseInput
    *
    * Preserve is not supported in DB container
    *
    * @access public
    * @param string     $datasrc    Name of the datasource to parse
    * @param boolean    $preserve   preserving behavior
    * @return mixed     returns a DB_ERROR, if error occurs
    */
    function writeInput($datasrc = '', $preserve = false)
    {
        $query = sprintf("DELETE FROM %s WHERE %s = '%s'",
                         $this->feature['table'],
                         $this->feature['datasrccol'],
                         $datasrc
                         );
        $res = $this->query($query);
        if (DB::isError($res)) {
           return new DB_Error($res->code, PEAR_ERROR_DIE);
        }
        $query = 'INSERT INTO '.$this->feature['table'].' ('
                .$this->feature['datasrccol'].','.$this->feature['blockcol'].','
                .$this->feature['namecol'].','.$this->feature['valuecol']
                .') VALUES (?,?,?,?)';
        $sth = $this->db->prepare($query);
        foreach ($this->data as $block => $blockarray) {
            foreach ($blockarray as $name => $value) {
                $alldata[] = array($datasrc, $block, $name, $value);
            }
        }
        $res = $this->db->executeMultiple($sth, $alldata);
        if (DB::isError($res)) {
            return new DB_Error($res->code, PEAR_ERROR_DIE);
        }
    } // end func writeInput
} // end class Config_Container_db
?>