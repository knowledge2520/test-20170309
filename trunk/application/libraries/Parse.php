<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Parse
 *
 * Serves as a generator for all relevant parse classes.
 *
 */
class Parse {

	private $_database = false;
    public function __construct() {
    	
    }
	
	public function setDatabase($database){
		$this->_database = $database;
	}
	
    public function ParseObject($className) {
        include_once 'parse/ParseObject.php';
        $obj = new ParseObject($className);
        $obj->loadConfig($this->_database);
        return $obj;
    }

    public function ParseUser() {
        include_once 'parse/ParseUser.php';
        $obj = new ParseUser();
        $obj->loadConfig($this->_database);
        return $obj;
    }

    public function ParseQuery($className) {
        include_once 'parse/ParseQuery.php';
        $obj = new ParseQuery($className);
        $obj->loadConfig($this->_database);
        return $obj;
    }
    
    public function ParsePush() {
    	include_once 'parse/ParsePush.php';
    	return new ParsePush();
    }
}

