<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* Loader sub-class
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class Autoload extends PLF_Loader 
{


/**
* ------------------------------------------------
* Autoload libraries
* ------------------------------------------------
* 
* @author ilker ozcan
* @var arrary $autoloadLibraries
* @example $autoloadLibraries = array('upload');
* 
*/

	protected $autoloadLibraries		= array();


/**
* ------------------------------------------------
* Autoload helpers
* ------------------------------------------------
* 
* @author ilker ozcan
* @var arrary $autoloadHelpers
* @example $autoloadHelpers = array('text');
* 
*/

	protected $autoloadHelpers			= array();


/**
* ------------------------------------------------
* Autoload config files
* ------------------------------------------------
* 
* @author ilker ozcan
* @var arrary $autoloadConfig
* @example $autoloadConfig = array('config1', 'config2');
* 
*/

	protected $autoloadConfig			= array();


/**
* ------------------------------------------------
* Autoload models
* ------------------------------------------------
* 
* @author ilker ozcan
* @var arrary $autoloadModel
* @example $autoloadModell = array('model1', 'model2');
* 
*/

	protected $autoloadModel			= array();


/**
* ------------------------------------------------
* Autoload static classes
* ------------------------------------------------
* 
* @author ilker özcan
* @var array $autoloadStaticClasses
* this classes must be inside libraries folder
* 
*/

	protected $autoloadStaticClasses	= array();


	public function __construct()
	{
		parent::__construct();
		
		// Autoload database
		//$this->db();
	}
	
	
/**
* ------------------------------------------------
* Load Database (Alias driver)
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/
	
	public function db()
	{
		$this->config('database');
		$this->driver('plf_'.Database::$dbdriver.'_driver', 'drivers/database', 'plf_db', NULL, 'db');
	}

}


/**
* ------------------------------------------------
* End of file autoload.php
* ------------------------------------------------
*/