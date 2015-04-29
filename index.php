<?php

/**
 * ------------------------------------------------
 * Application environment
 * ------------------------------------------------
 *
 * @author ilker ozcan
 * @type String
 * development shows error [error_reporting(E_ALL)]
 * production does not show error [error_reporting(E_NONE)]
 * @example (development, production)
 *
 */

	define('ENVIRONMENT', 'development');
	
	
/**
 * ------------------------------------------------
 * Application name
 * ------------------------------------------------
 *
 * @author ilker özcan
 * @type String
 * The name of the app and the content folder that application. Name must be the only ansi type.
 *
 */

	define('APPLICATIONNAME', 'plf');


/**
 * ------------------------------------------------
 * Default Language
 * ------------------------------------------------
 *
 * @type string
 * @default value en_US
 * @author ilker özcan
 *
 */

	define('APPLICATIONLANGUAGE', 'en_US');


/**
 * ------------------------------------------------
 * Session Name
 * ------------------------------------------------
 *
 * @type string
 * @default value PLF_SID
 * @author ilker özcan
 *
 */

	define('SESSION_NAME', 'PLF_SID');


/**
 * ------------------------------------------------
 * Use CLI
 * If this value is true,
 * arg 1 controller
 * arg 2 function
 * arg 3 variable 1
 * arg 4 variable 2
 * ...
 *
 * If you are use plf in cli
 * you should add public static $cli = true
 * in cli controller file
 * ------------------------------------------------
 *
 * @type bool
 * @default value FALSE
 * @author ilker özcan
 *
 */

	define('USE_CLI', TRUE);


/**
 * ------------------------------------------------
 * Default Routes
 * ------------------------------------------------
 *
 * @author ilker özcan
 * @type String
 * Application will run a specific controller.
 * @example define('USE_DEFAULTROUTE', TRUE);
 * @example $PLF_ROUTE_CONTROLLER='defaultController'
 * @example $PLF_ROUTE_FUNCTION=defaultMethod'
 * @example $PLF_ROUTE_PARAMETER=array('param2', 'param2')
 *
 */

	define('USE_DEFAULTROUTE', FALSE);
	$PLF_ROUTE_CONTROLLER		= '';
	$PLF_ROUTE_FUNCTION         = '';
	$PLF_ROUTE_PARAMETER		= array();


/**
 * ------------------------------------------------
 * Default Timezone
 * ------------------------------------------------
 *
 * @author ilker özcan
 *
 */

	date_default_timezone_set('UTC');


/**
* ------------------------------------------------
* Error Reporting
* ------------------------------------------------
* 
* @author ilker ozcan
* development will show errors but production will hide them.
* 
*/

	if(defined('ENVIRONMENT'))
	{
		switch(ENVIRONMENT)
		{
			case 'development':
				error_reporting(-1);
				ini_set('display_errors', 1);
			break;
			case 'production':
				error_reporting(0);
				ini_set('display_errors', 0);
			break;
		}
	}


/**
* ------------------------------------------------
* Main directory
* ------------------------------------------------
* 
* @author ilker ozcan
* Set the main path
* 
*/

	define('BASEPATH', str_replace('\\', '/', __DIR__).'/');
	
	
/**
* ------------------------------------------------
* App and Content folder 
* ------------------------------------------------
*
* @author ilker özcan
* 
*/

	define('APPFOLDER', BASEPATH.'app/'.APPLICATIONNAME.'/');
	define('CONTENTFOLDER', BASEPATH.'content/'.APPLICATIONNAME.'/');


/**
* ------------------------------------------------
* Shared libraries
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	define('SHAREDFOLDER', BASEPATH.'shared/');
	
	
/**
* ------------------------------------------------
* This file 
* ------------------------------------------------
* 
* @author ilker özcan
* 
* 
*/

	define('INDEXFILE', basename(__FILE__));
	
/**
* ------------------------------------------------
* Load core language class
* ------------------------------------------------
*
* @author ilker özcan 
* 
*/

	require_once(BASEPATH.'shared/languages/'.APPLICATIONLANGUAGE.'/corelanguage.php');


/**
* ------------------------------------------------
* Bootstrap file
* ------------------------------------------------
* 
* @author ilker ozcan
* load bootstrap file
* 
*/

	require_once(BASEPATH.'core/plf.php');
	
	
/**
* ------------------------------------------------
* End of file index.php
* ------------------------------------------------
*/
