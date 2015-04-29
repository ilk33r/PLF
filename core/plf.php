<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * PLF Version
 * ------------------------------------------------
 *
 * @author ilker ozcan
 *
 */

define('PLF_VERSION', '1.0.0');


/**
 * ------------------------------------------------
 * Start Session
 * ------------------------------------------------
 *
 * @author ilker ozcan
 *
 */

if(PHP_SAPI != 'cli')
{
	session_name(SESSION_NAME);
	session_start();
}


if(PHP_VERSION_ID < 50400)
{
	die('Minimum php version must be 5.4 or higher.');
}

/**
* ------------------------------------------------
* Get common functions
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	require_once(BASEPATH.'core/common.php');
	
	
/**
* ------------------------------------------------
* Error and exception handler
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	set_error_handler('_error_handler');
	set_exception_handler('_exception_error_handler');

	
/**
* ------------------------------------------------
* Load core classes, controller, model class file
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	require_once(BASEPATH.'core/mvc.php');
	require_once(BASEPATH.'core/plf_coreclasses.php');


/**
* ------------------------------------------------
* Start controller class
* ------------------------------------------------
* 
* @author ilker ozcan
* @var object
* 
*/

	$PLF				= new PLF_controller();

	
/**
* ------------------------------------------------
* Start model
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	$PLF_model			= new PLF_Model();


/**
 * ------------------------------------------------
 * Get language file if exists
 * ------------------------------------------------
 *
 * @author ilker ozcan
 *
 */

	$languageFile		= APPFOLDER . 'languages/' . APPLICATIONLANGUAGE . '/corelanguage.php';
	if(file_exists($languageFile))
		include($languageFile);


/**
* ------------------------------------------------
* start uri routing
* ------------------------------------------------
* 
* @author ilker ozcan
* @param object Plf_config
* @param object routes 
* 
*/

	require(Common::getFilePath('config/route.php'));
	$PLFRouting			= new Route();

	if(USE_DEFAULTROUTE)
	{
		$routingData				= new stdClass();
		
		$routingData->className		= $PLF_ROUTE_CONTROLLER;
		$routingData->methodName	= $PLF_ROUTE_FUNCTION;
		$routingData->parameters	= $PLF_ROUTE_PARAMETER;
		
		$PLFRouting->getRoute($routingData->className);
	}else{
		$routingData				= $PLFRouting->getRoutingData();
		$PLFRouting->getRoute($routingData->className);
	}
	
	$controllerName		= strtolower($routingData->className);
	if(class_exists($controllerName))
	{
		$userController		= new $controllerName();
		$userMethod			= $routingData->methodName;
		if(method_exists($userController, $userMethod) && is_callable(array($userController, $userMethod)))
		{
			$isControllerCallable		= true;
			if(PHP_SAPI != 'cli')
			{
				if(isset($userController::$cli))
					if($userController::$cli)
					{
						$isControllerCallable	= false;
						showError(Corelanguage::ERROR, Corelanguage::CLI_ERROR, 400);
					}
			}else{
				if(!isset($userController::$cli))
				{
					$isControllerCallable	= false;
					echo 'This controller does not using in command line';
				}elseif(!$userController::$cli)
				{
					$isControllerCallable	= false;
					echo 'This controller does not using in command line';
				}
			}

			if($isControllerCallable)
				call_user_func_array(array($userController, $userMethod), $routingData->parameters);
		}else{
			if(PHP_SAPI != 'cli')
				show404();
			else
				echo 'class not found';
		}
	}else{
		if(PHP_SAPI != 'cli')
			show404();
		else
			echo 'class not found';
	}


/**
* ------------------------------------------------
* End of file plf.php
* ------------------------------------------------
*/
