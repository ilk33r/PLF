<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* PLF Common Class
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

Class Common
{


/**
* ------------------------------------------------
* Loaded classes, instance classes
* and destroyed classes
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	protected static $classes			= array();
	protected static $instance			= array();
	protected static $destructClasses	= array();


/**
* ------------------------------------------------
* Load file method
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $path
* The file is searched first in the application directory, 
* if file not found in application directory, search shared directory secondly.
* 
*/

	public static function getFilePath($path)
	{
		$appFolderPath		= APPFOLDER.$path;
		$sharedFolderPath	= SHAREDFOLDER.$path;
		
		if(file_exists($appFolderPath))
		{
			return $appFolderPath;
		}else if(file_exists($sharedFolderPath))
		{
			return $sharedFolderPath;
		}else{
			throw new Exception(vsprintf(Corelanguage::NOT_FOUD, array($path)));
		}		
	}


/**
* ------------------------------------------------
* sendHttpResponseCode
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public static function sendHttpResponseCode($code)
	{
		switch($code)
		{
			case 200:
				header('HTTP/1.1 200 Ok');
				break;
			case 201:
				header('HTTP/1.1 201 Created');
				break;
			case 301:
				header('HTTP/1.1 301 Moved Permanently');
				break;
			case 304:
				header('HTTP/1.1 304 Not Modified');
				break;
			case 400:
				header('HTTP/1.1 400 Bad Request');
				break;
			case 401:
				header('HTTP/1.1 401 Unauthorized');
				break;
			case 403:
				header('HTTP/1.1 403 Forbidden');
				break;
			case 404:
				header('HTTP/1.1 404 Not Found');
				break;
			case 501:
				header('HTTP/1.1 501 Not Implemented');
				break;
		}
	}
	
	
/**
* ------------------------------------------------
* Get error names
* ------------------------------------------------
* 
* @author ilker ozcan
* @param int $errorNumber
* @return string
* 
*/

	public static function getErrorName($errorNumber = 0)
	{
		switch($errorNumber)
		{
			case 1:
				$errName	= 'ERROR';
			break;
			case 2:
				$errName	= 'WARNING';
			break;
			case 4:
				$errName	= 'PARSE';
			break;
			case 8:
				$errName	= 'NOTICE';
			break;
			case 16:
				$errName	= 'CORE_ERROR';
			break;
			case 32:
				$errName	= 'CORE_WARNING';
			break;
			case 2048:
				$errName	= 'STRICT';
			break;
			case 8192:
				$errName	= 'DEPRECATED';
			break;
			default:
				$errName	= 'UNKOWN ERROR ('.$errorNumber.')';
			break;
		}
		
		return $errName;
	}


/**
* ------------------------------------------------
* Class loader
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $className
* @param string $path
* @param array  $argv
* @param string $alias
* @return object
* 
*/

	public static function &loadClass($className, $path = NULL, $argv = NULL, $alias = NULL)
	{
		if(!isset(self::$classes[$className]))
		{
			$fileExists		= FALSE;
			
			if(!class_exists($className, FALSE))
			{
				if($path == NULL)
				{
					$file	= BASEPATH.'core/'.strtolower($className).'.php';
					if(file_exists($file))
					{
						$fileExists	= TRUE;
					}

				}else{
					$file	= self::getFilePath($path.'/'.strtolower($className.'.php'));
					$fileExists	= TRUE;
				}


				if($fileExists)
				{
					require($file);	
				}
			}
			
			if(!class_exists($className))
			{
				throw new Exception(vsprintf(Corelanguage::NOT_FOUD, array($className)));
			}
			
			self::$classes[$className]	= TRUE;
			
			if($argv != NULL)
			{
				$createdClass				= new $className($argv);
			}else{
				$createdClass				= new $className();
			}
			
			if($alias != NULL)
			{
				$classAlias					= $alias;
			}else{
				$classAlias					= strtolower($className);
			}
			
			self::getInstance(TRUE, $classAlias, $createdClass);
		}
		
		return self::$classes;
	}


/**
* ------------------------------------------------
* Return all instance classes
* ------------------------------------------------
* 
* @author ilker ozcan
* @return object
* 
*/

	public static function &getInstance($setInstance = FALSE, $className = NULL, $class = NULL)
	{
		if($setInstance)
		{
			self::$instance[$className]	= $class;
		}
		
		return self::$instance;
	}


/**
* ------------------------------------------------
* Register Destructs
* ------------------------------------------------
* 
* @author ilker özcan
* @return array
* 
*/

	public static function destructs($register = FALSE, $className = NULL, $methodName = NULL)
	{
		if($register)
		{
			$tmpRegisterObject				= new stdClass();
			$tmpRegisterObject->class		= $className;
			$tmpRegisterObject->method		= $methodName;
			self::$destructClasses[]		= $tmpRegisterObject;
		}
		return self::$destructClasses;
	}
}


/**
* ------------------------------------------------
* Show 404 Error Page 
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

if(!function_exists('show404'))
{
	function show404()
	{
		ob_clean();
		Common::sendHttpResponseCode(404);
		require(Common::getFilePath('views/errors/error_404.php'));
		PLF_Output::$masterSended		= true;
		exit;
	}
}


/**
* ------------------------------------------------
* Show general errors 
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

if(!function_exists('showError'))
{
	function showError($errorTitle = NULL, $errorMessage = NULL, $statusCode = 400)
	{
		$errorTitle		= ($errorTitle == NULL)?Corelanguage::ERROR:$errorTitle;
		$errorMessage	= ($errorMessage == NULL)?Corelanguage::UNEXPECT_ERROR_MSG:$errorMessage;
		
		ob_end_clean();
		Common::sendHttpResponseCode($statusCode);
		require(Common::getFilePath('views/errors/error_general.php'));
		PLF_Output::$masterSended		= true;
		exit;
	}
}


/**
* ------------------------------------------------
* Error handler
* ------------------------------------------------
* 
* @author ilker ozcan
* @param int $errNumber
* @param string $err
* @param string $errFile
* @param int $lineNumber
* 
*/

if(!function_exists('_error_handler'))
{
	function _error_handler($errNumber = 0, $err, $errFile, $lineNumber)
	{
		if(ENVIRONMENT == 'development')
		{
			if(PHP_SAPI != 'cli')
			{
				echo '<div style="width: 99%;margin: 1px 0px 15px 0px;border: 1px solid #707070;padding: 3px;background-color: #EEE;border-radius: 7px;box-shadow: 2px 2px 3px #CCC;"><strong'.Corelanguage::DEBUG_MSG.'</strong> - ';
				echo '[## '.Common::getErrorName($errNumber).' ##]';
				echo ' '.$err.' @['.$errFile.'] Line: <strong>'.$lineNumber.'</strong></div>';
			}else{
				echo "\n\n{Corelanguage::DEBUG_MSG}\n";
				echo "{Common::getErrorName($errNumber)}\n";
				echo "{$err} - {$errFile} Line: {$lineNumber}\n";
			}
		}
	}
}


/**
* ------------------------------------------------
* Exception handler
* ------------------------------------------------
* 
* @author ilker ozcan
* @param object $execpionData
* @example throw new Exception('example exception');
* 
*/

if(!function_exists('_exception_error_handler'))
{
	function _exception_error_handler($execpionData)
	{
		ob_end_clean();
		if(PHP_SAPI != 'cli')
		{
			echo '<div style="width: 99%;margin: 1px 0px 15px 0px;border: 1px solid #707070;padding: 3px;background-color: #EEE;border-radius: 7px;box-shadow: 2px 2px 3px #CCC;"><strong>'.Corelanguage::EXCEPTION_MSG.'</strong> - ';
			echo $execpionData->getMessage().'</div>';
		}else{
			echo "\n\n{Corelanguage::EXCEPTION_MSG}\n";
			echo "{$execpionData->getMessage()}\n";
		}
	}
}


/**
* ------------------------------------------------
* Get base url
* ------------------------------------------------
* 
* @author ilker ozcan
* @return string
* 
*/

if(!function_exists('baseUrl'))
{
	function baseUrl()
	{
		$protocol		= isset($_SERVER['HTTPS'])?(($_SERVER['HTTPS'] == 'on')?'https':'http'):'http';
		$path			= $_SERVER['PHP_SELF'];
		$pathParts		= pathinfo($path);
		$directory		= $pathParts['dirname'];
		$directory		= ($directory == '/') ? '' : $directory;
		$port			= ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
		$base_url		= $protocol.'://'.$_SERVER['SERVER_NAME'].$port.$directory.'/';
		
		return $base_url;
	}
}


/**
* ------------------------------------------------
* Get active page url
* ------------------------------------------------
* 
* @author ilker ozcan
* @return string
* 
*/

if(!function_exists('activePageUrl'))
{
	function activePageUrl()
	{
		$protocol		= isset($_SERVER['HTTPS'])?(($_SERVER['HTTPS'] == 'on')?'https':'http'):'http';
		$port			= ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
		$URI			= $protocol.'://'.$_SERVER['HTTP_HOST'].$port.$_SERVER['REQUEST_URI'];
		return $URI;
	}
}


/**
* ------------------------------------------------
* Remove Invisible Characters
* ------------------------------------------------
*
* This prevents sandwiching null characters
* between ascii characters, like Java\0script.
*
* @param	string
* @param	bool
* @return	string
* @author EllisLab Dev Team
* 
*/

if ( ! function_exists('remove_invisible_characters'))
{
	function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);
		return $str;
	}
}


/**
* ------------------------------------------------
* End of file common.php
* ------------------------------------------------
*/
