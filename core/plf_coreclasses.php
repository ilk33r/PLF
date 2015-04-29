<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* Loader class
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class PLF_Loader
{


/**
* ------------------------------------------------
* PLF instance
* ------------------------------------------------
* 
* @author ilker ozcan
* @var object $PLF_instance
* 
*/

	protected $PLF_instance;


/**
* ------------------------------------------------
* Static class list
* ------------------------------------------------
* 
* @author ilker özcan
* @var array $PLF_staticClasses
* 
*/

	protected $PLF_staticClasses;
	protected $PLF_configClasses;


/**
* ------------------------------------------------
* Load config class will automatically and autoload stat
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	public function __construct()
	{
		$this->PLF_instance			=& Common::getInstance();
		
		$this->config('config');
		
		if(PHP_SAPI != 'cli')
		{
			Common::loadClass('session');
			$this->staticClass('Server');
		}
	}


/**
* ------------------------------------------------
* Files loaded automatically
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	public function startAutoload()
	{
		if(PHP_SAPI == 'cli')
			return;

		foreach($this->autoloadConfig as $config)
		{
			$this->config($config);
		}

		foreach($this->autoloadLibraries as $lib)
		{
			$this->lib($lib);
		}
		
		foreach($this->autoloadHelpers as $helper)
		{
			$this->helper($helper);
		}

		foreach($this->autoloadModel as $model)
		{
			$this->model($model);
		}
		
		foreach($this->autoloadStaticClasses as $static)
		{
			$this->staticClass($static);
		}
		
	}


/**
* ------------------------------------------------
* Load model method
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $modelName
* 
*/

	public function model($modelName)
	{
		Common::loadClass($modelName, 'models');
	}


/**
* ------------------------------------------------
* Load library
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $libraryName
* 
*/

	public function lib($libraryName, $argv = NULL)
	{
		Common::loadClass($libraryName, 'libraries', $argv);
	}
	
	
/**
* ------------------------------------------------
* Load helper
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $helperName
* 
*/

	public function helper($helperName)
	{
		$helperFile		= Common::getFilePath('helpers/'.$helperName.'.php');
		require($helperFile);
	}


/**
* ------------------------------------------------
* Load driver
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $driverName
* @param string $driverPath
* @param string $libraryName
* @param array $argv
* @param string $alias
* 
*/

	public function driver($driverName, $driverPath, $libraryName, $argv = NULL, $alias = NULL)
	{
		if(!class_exists($libraryName, false))
		{
			$libraryClass	= Common::getFilePath('libraries/'.$libraryName.'.php');
			require($libraryClass);
		}
		
		Common::loadClass($driverName, $driverPath, $argv, $alias);
	}


/**
* ------------------------------------------------
* Load static class
* ------------------------------------------------
*  
* @author ilkr özcan
* @param string $className
* 
*/

	public function staticClass($className)
	{
		if(!isset($PLF_staticClasses[$className]))
		{
			if(!class_exists($className, FALSE))
			{
				$file		= Common::getFilePath('libraries/'.strtolower($className.'.php'));
				require($file);	
			}
			
			if(!class_exists($className))
			{
				throw new Exception(Corelanguage::printfSentence(array($file), Corelanguage::$NOT_FOUND));
			}
			
			$PLF_staticClasses[$className]	= TRUE;
			
			new $className();
			
		}
	}


/**
* ------------------------------------------------
* Load config
* ------------------------------------------------
*  
* @author ilkr özcan
* @param string $className
* 
*/

	public function config($className)
	{
		if(!isset($PLF_configClasses[$className]))
		{
			if(!class_exists($className, FALSE))
			{
				$file		= Common::getFilePath('config/'.strtolower($className.'.php'));
				require($file);	
			}
			
			if(!class_exists($className))
			{
				throw new Exception(Corelanguage::printfSentence(array($file), Corelanguage::NOT_FOUD));
			}
			
			$PLF_configClasses[$className]	= TRUE;
			
			new $className();
		}
	}
}


/**
* ------------------------------------------------
* URI class
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class PLF_Uri
{
	
/**
* ------------------------------------------------
* Routind data
* ------------------------------------------------
* 
* @author ilker ozcan
* @var object $routingData
* @return string className
* @return string methodName
* @return array parameters
* 
*/
	protected $routingData;
	

/**
* ------------------------------------------------
* Start routing for uri
* ------------------------------------------------
* 
* @author ilker ozcan
* @param object $configData
* 
*/

	public function getRoutingData()
	{
		$permitedCharacters		= Config::$permitted_uri_chars;
		//$queryStringParams		= Config::$query_string_name;


		if($this->controlURICharacter($permitedCharacters))
		{
			if(PHP_SAPI != 'cli')
			{
				$requestURL				= $this->getRequestUrl();

				if(!$requestURL)
				{
					$this->routingData	= $this->parseUri($this->defaultController);
				}else{
					$this->routingData	= $this->chekRouteRules($requestURL, $this->userRoutes);
				}
			}else{
				global $argv;
				$request			= $argv[1];
				$this->routingData	= $this->chekRouteRules($request, $this->userRoutes);
			}
			
			return $this->routingData;
		}else{
			showError(Corelanguage::UNEXPECT_ERROR_MSG, Corelanguage::URL_EERROR, 400);
		}
	}
	
	
/**
* ------------------------------------------------
* Check route rules
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $request
* @param array $routes
* 
*/

	private function chekRouteRules($request, $routes)
	{
		$ruleExists			= FALSE;
		$lastCharRequest	= substr($request, -1);

		if($lastCharRequest == '/')
		{
			$request		= substr($request, 0, -1);
		}

		if(PHP_SAPI != 'cli')
			$request			= substr($request, strlen(Config::$plfDirectory));

		foreach($routes as $key => $route)
		{
			$key		= str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);
			$pattern	= '#^'.$key.'$#';
			if(preg_match($pattern, $request, $matches))
			{
				$ruleExists		= TRUE;
				$ruleValue		= preg_replace($pattern, $route, $request);
				break;
			}
		}

		if($ruleExists)
		{
			return $this->parseUri($ruleValue);
		}else{
			return $this->parseUri($request);
		}
	}
	
	
/**
* ------------------------------------------------
* Parse request uri
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $routeData
* 
*/

	private function parseUri($routeData)
	{
		$routingValue		= explode('/', $routeData);
		$parsedRoute		= new stdClass();
		
		if(count($routingValue) > 1)
		{
			$activeParamNumber	= 0;
			$parameters			= array();
			foreach($routingValue as $rv)
			{
				
				if($activeParamNumber == 0)
				{
					$parsedRoute->className		= $rv;
				}elseif($activeParamNumber == 1)
				{
					$parsedRoute->methodName	= (!empty($rv))?$rv:'index';
				}else{
					if($rv == 0){$parameters[]	= $rv;}
					else
					if(!empty($rv)){$parameters[]	= $rv;}
				}
				
				$activeParamNumber++;
			}
			
			$parsedRoute->parameters	= $parameters;
		}else{
			$parsedRoute->className		= $routingValue[0];
			$parsedRoute->methodName	= 'index';
			$parsedRoute->parameters	= array();
		}
		
		return $parsedRoute;
	}
	
	
/**
* ------------------------------------------------
* Check permited uri character 
* ------------------------------------------------
* 
* @author ilker ozcan
* @param string $permitedCharacters
* 
*/

	private function controlURICharacter($permitedCharacters)
	{
		$result				= TRUE;
		$controlCharacters	= $permitedCharacters . '\?\=\&';

		foreach($_GET as $key => $val)
		{
			if(empty($key) || empty($val))
				continue;

			$pattern	= '/^['.$controlCharacters.'\/]+$/';
			$matchKey	= preg_match($pattern, $key, $out);
			$matchVal	= preg_match($pattern, $val, $out);
			if($matchKey && $matchVal)
			{
				continue;
			}else{
				$result	= FALSE;
				break;
			}
		}
		
		return $result;
	}


	private function getRequestUrl()
	{
		$scriptName		= $_SERVER['SCRIPT_NAME'];
		$requestURL		= $_SERVER['REQUEST_URI'];

		if(preg_match('/\?/i', $requestURL, $matches))
		{
			$explodedGETString		= explode('?', $requestURL);
			$requestURL				= $explodedGETString[0];
		}

		if(empty($requestURL) || $requestURL == '/')
		{
			return false;
		}else{
			$scriptNameLength		= strlen($scriptName);

			if(substr($requestURL, 0, $scriptNameLength) == $scriptName)
			{
				return substr($requestURL, $scriptNameLength);
			}else{
				return $requestURL;
			}
		}
	}


/**
* ------------------------------------------------
* Get route class 
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	public function getRoute($routingData)
	{
		$path				= 'controllers/'.$routingData.'.php';
		$appFolderPath		= APPFOLDER.$path;
		$sharedFolderPath	= SHAREDFOLDER.$path;

		if(file_exists($appFolderPath))
		{
			require($appFolderPath);
		}else if(file_exists($sharedFolderPath))
		{
			require($sharedFolderPath);
		}else{
			if(PHP_SAPI != 'cli')
				show404();
			else
				echo 'Controller file not found!';
		}	
	}


}


/**
* ------------------------------------------------
* Server class
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

class Server
{

/**
* ------------------------------------------------
* Server variables alias
* ------------------------------------------------
*  
* @staticvar $referer string
* @staticvar $userAgent string
* @staticvar $https bool
* @staticvar $ip string
* @staticvar $host string
* @staticvar $queryString string
* @staticvar $docRoot string
* @author ilker özcan
* 
*/
	public static $referer;
	public static $userAgent;
	public static $https;
	public static $ip;
	public static $host;
	public static $docRoot;

	public function __construct()
	{
		Server::$referer		= (isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:FALSE;
		Server::$userAgent		= $_SERVER['HTTP_USER_AGENT'];
		Server::$https			= (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off'))?TRUE:FALSE;
		Server::$ip				= $_SERVER['REMOTE_ADDR'];
		Server::$host			= (isset($_SERVER['REMOTE_HOST']))?$_SERVER['REMOTE_HOST']:FALSE;
		Server::$docRoot		= $_SERVER['DOCUMENT_ROOT'];
	}
	
	public static function isAjaxRequest()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			return TRUE;
		}else{
			return FALSE;
		}
	}
}


/**
* ------------------------------------------------
* Session and cookie Class
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

class Session
{
	
/**
* ------------------------------------------------
* Class variables
* ------------------------------------------------
* 
* @var string $sessionId
* @author ilker özcan
* 
*/
	public $sessionId;
	
	
/**
* ------------------------------------------------
* Get or set session id and import session data
* from cookie
* ------------------------------------------------
* 
* @param string $config
* @author ilker özcan
* 
*/

	public function __construct() 
	{
		if(empty(Config::$encription_key))
		{
			throw new Exception(Corelanguage::ENCRYPTION_KEY);
		}

		$sessionIsValid			= true;

		if(!isset($_SESSION['PLF_Inited']))
		{
			$sessionIsValid		= false;
		}

		$cookieUserAgent			= '';
		$cryptedUserAgen			= md5($this->encryptSessionData(Server::$userAgent));
		if(isset($_COOKIE[Config::$session_inited_cookie_name]))
		{
			$cookieUserAgent		= $_COOKIE[Config::$session_inited_cookie_name];

			if($cookieUserAgent != $cryptedUserAgen)
			{
				$sessionIsValid	= false;
			}
		}else{
			$sessionIsValid		= false;
		}

		if(isset($_SESSION[Config::$session_inited_cookie_name]))
		{
			if($cookieUserAgent != $_SESSION[Config::$session_inited_cookie_name])
			{
				$sessionIsValid	= false;
			}
		}else{
			$sessionIsValid		= false;
		}

		if(!$sessionIsValid)
			$this->reeGenerateSession();

		$this->sessionId	= session_id();
	}


/**
* ------------------------------------------------
* Destroy session
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function destroy()
	{
		session_unset();
	}


/**
* ------------------------------------------------
* Set cookie
* ------------------------------------------------
* 
* @param string $name
* @param string $data
* @param int $time
* @author ilker özcan
* 
*/

	public function setcookie($name, $data, $time = NULL)
	{
		if($time == NULL)
		{
			$time		= Config::$cookie_expiration;
		}
		setcookie($name, $data, time() + $time, Config::$cookie_path, Config::$cookie_domain, Config::$cookie_secure);
	}


/**
* ------------------------------------------------
* Destroy cookie
* ------------------------------------------------
* 
* @param string $name
* @author ilker özcan
* 
*/


	public function destroycookie($name)
	{
		setcookie($name, '', time() - (Config::$cookie_expiration * 10), Config::$cookie_path, Config::$cookie_domain, Config::$cookie_secure);
	}


/**
* ------------------------------------------------
* Encrypt session data
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/


	public function encryptSessionData($data)
	{
		if(!extension_loaded('mcrypt'))
		{
			return base64_encode($data);
		}else{
			$iv_size				= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	    	$iv						= mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$mCryptSession			= mcrypt_encrypt(MCRYPT_RIJNDAEL_256, Config::$encription_key, $data, MCRYPT_MODE_ECB, $iv);
			return base64_encode($mCryptSession);
		}
	}


/**
* ------------------------------------------------
* Decrypt session data
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function decryptSessionData($data)
	{
		if(!extension_loaded('mcrypt'))
		{
			return base64_decode($data);
		}else{
			$mCryptedSession	= base64_decode($data);
			$iv_size			= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    		$iv					= mcrypt_create_iv($iv_size, MCRYPT_RAND);
			return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, Config::$encription_key, $mCryptedSession, MCRYPT_MODE_ECB, $iv);
		}
	}


/**
* ------------------------------------------------
* Re generate session
* ------------------------------------------------
*
* @author ilker özcan
*
*/

	private function reeGenerateSession()
	{
		session_regenerate_id();

		$_SESSION['PLF_Inited']		= true;
		$cryptedUseragent			= md5($this->encryptSessionData(Server::$userAgent));

		$_SESSION[Config::$session_inited_cookie_name]		= $cryptedUseragent;
		$this->setcookie(Config::$session_inited_cookie_name, $cryptedUseragent);

	}
}


/**
* ------------------------------------------------
* End of file plf_coreclasses.php
* ------------------------------------------------
*/
