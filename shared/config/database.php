<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* Database Config Class
* ------------------------------------------------
* 
* @author ilker ozcan 
* 
*/

final class Database 
{


/**
* ------------------------------------------------
* Database hostname
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $hostname
* @example $hostname = 'localhost'
* 
*/

	public static $hostname				= 'localhost';


/**
* ------------------------------------------------
* Database username
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $username = username The username used to connect to the database
* @example $username = 'db_user'
* 
*/

	public static $username				= '';


/**
* ------------------------------------------------
* Database password
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $password = password The password used to connect to the database
* @example $password = 'your_password'
* 
*/
	
	public static $password				= '';


/**
* ------------------------------------------------
* Database name
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $database = database The name of the database you want to connect to
* @example $database = 'your_database_name'
* 
*/


	public static $database					= '';
	
	
/**
* ------------------------------------------------
* Database port
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $port = your database server port number.
* @example $port = NULL
* 
*/

	public static $port					= NULL;
	

/**
* ------------------------------------------------
* Database driver
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $dbdriver = the database type
* @example $dbdriver = 'mysqli'
* Currently supported: mysqli, sqlsrv
* 
*/

	public static $dbdriver				= 'mysqli';
	

/**
* ------------------------------------------------
* Database character set
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $char_set =  char_set The character set used in communicating with the database.
* @example $char_set = 'utf8'
* 
*/

	public static $char_set				= 'utf8';

	
/**
* ------------------------------------------------
* The character collation 
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $dbcollat =  The character collation used in communicating with the database
* @example $dbcollat = 'utf8_general_ci'
* 
*/

	public static $dbcollat				= 'utf8_general_ci';


/**
* ------------------------------------------------
* Database debug on off
* ------------------------------------------------
* 
* @author ilker özcan
* @var bool $dbDebug  = TRUE
* 
*/

	public static $dbDebug				= TRUE;


/**
* ------------------------------------------------
* Automatic Transaction
* ------------------------------------------------
*
* @author ilker özcan
* @var bool $dbTransaction  = TRUE
*
*/

	public static $dbTransaction		= TRUE;
	
	
}


/**
* ------------------------------------------------
* End of file database.php
* ------------------------------------------------
*/