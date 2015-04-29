<?php
if ( ! defined('BASEPATH')) exit('Script access forbidden.');


/**
* ------------------------------------------------
* Controller class
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class PLF_Controller
{

	private $destructsCalled		= false;

	public function __construct()
	{

		/**
		 * ------------------------------------------------
		 * Call destruct method.
		 * (Close database connection, send output, etc..)
		 * ------------------------------------------------
		 *
		 * @author ilker özcan
		 * @var $destructMethods array
		 *
		 */

		register_shutdown_function(function()
		{
			if($this->destructsCalled)
				return;

			$destructMethods		= Common::destructs();
			if(count($destructMethods) > 0)
			{
				foreach($destructMethods as $destruct)
				{
					$class		= $destruct->class;
					if($this->$class)
					{
						@call_user_func_array(array($this->$class, $destruct->method), array());
					}
				}
			}

			$this->destructsCalled	= true;
		});


		/**
		* ------------------------------------------------
		* Start output and loader class
		* ------------------------------------------------
		*
		* @author ilker ozcan
		*
		*/

		Common::loadClass('Output', 'config', NULL, 'ob');
		Common::loadClass('Autoload', 'config', NULL, 'load');
	}


/**
* ------------------------------------------------
* Magic method __get
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

	public function __get($key)
	{
		$PLF	=& Common::getInstance();
		if(isset($PLF[$key]))
		{
			return $PLF[$key];
		}
	}
}


/**
* ------------------------------------------------
* Model class
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class PLF_Model
{

	public $dbobject;

	public function __construct(){$this->load->startAutoload();}
	
	
/**
* ------------------------------------------------
* Magic method __get
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/
	public function __get($key)
	{
		$PLF	=& Common::getInstance();
		if(isset($PLF[$key]))
		{
			return $PLF[$key];
		}else{
			throw new Exception(vsprintf(Corelanguage::NOT_LOADED, array($key . ' class')));
		}
	}
}

/**
* ------------------------------------------------
* Output class
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

class PLF_Output
{


/**
* ------------------------------------------------
* Start time for plf
* Plf Execution Time
* ------------------------------------------------
* 
* @var $plf_start int
* @var $execTime int
* @author ilker özcan
* 
*/
	public $plf_start;
	public $execTime;
	public static $masterSended		= FALSE;

	private $contentPath			= '';
	private $contentPathName		= '';
	private $subDirectory			= '';
	private $outputData				= array();



/**
* ------------------------------------------------
* Register destruct method, get start time and
* output buffering start
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function __construct() 
	{
		if($this->calculateExecutionTime)
			$this->plf_start		= microtime();
		
		Common::destructs(TRUE, 'ob', 'outputDestruct');
		ob_start();
		
		$pathParts				= pathinfo($_SERVER['PHP_SELF']);
		$this->subDirectory		= (substr($pathParts['dirname'], -1) == '/')?$pathParts['dirname']:$pathParts['dirname'].'/';
	}

	
/**
* ------------------------------------------------
* End output buffering
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function endFlush()
	{
		$outputHandlers				= ob_list_handlers();
		if(count($outputHandlers) > 0)
		{
			ob_end_flush();
		}
	}


/**
* ------------------------------------------------
* Get execution time
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/
	
	public function outputDestruct()
	{
		if($this->calculateExecutionTime)
			$this->execTime		= microtime() - $this->plf_start;

		$this->endFlush();
	}


/**
* ------------------------------------------------
* Set output data file
* ------------------------------------------------
* 
* @author ilker özcan
* @param $name string
* @param $data array/object
* @param $path string
* 
*/

	public function setView($name, $data = NULL, $path = NULL)
	{
		$this->setContentPath();
		if(is_array($data))
		{
			extract($data);
		}else if(is_object($data))
		{
			foreach($data as $key => $value)
			{
				$$key		= $value;
			}
		}

		if($path != NULL)
		{
			$themePath	= 'views/'.$path.'.php';
		}else{
			$themePath	= 'views/'.$name.'.php';
		}
		
		extract($this->outputData);
		
		$outputHandlers				= ob_list_handlers();
		if(count($outputHandlers) > 0)
		{
			ob_flush();
			ob_start();
		}

		$file						= Common::getFilePath($themePath);
		require $file;
		$this->outputData[$name]	= ob_get_clean();
	}
	
	
/**
* ------------------------------------------------
* Get output data file
* ------------------------------------------------
* 
* @author ilker özcan
* @param $name string
* @param $data array/object
* @param $path string
* 
*/

	public function getView($data = NULL, $path = NULL)
	{
		$this->setContentPath();
		if(is_array($data))
		{
			extract($data);
		}else if(is_object($data))
		{
			foreach($data as $key => $value)
			{
				$$key		= $value;
			}
		}
		
		$corPath	= 'views/'.$path.'.php';
		
		extract($this->outputData);
		
		$outputHandlers				= ob_list_handlers();
		if(count($outputHandlers) > 0)
		{
			ob_flush();
			ob_start();
		}
		
		
		$file						= Common::getFilePath($corPath);
		require $file;
		return ob_get_clean();
	}

	
/**
* ------------------------------------------------
* Set output data variable
* ------------------------------------------------
* 
* @author ilker özcan
* @param $data array/object
* 
*/

	public function assignVar($data = NULL)
	{
		if(is_array($data) || is_object($data))
		{
			foreach($data as $key => $value)
			{
				$this->outputData[$key]		= $value;
			}
		}
	}


/**
* ------------------------------------------------
* Send output data
* ------------------------------------------------
* 
* @author ilker özcan
* @param $masterPageFile string
* 
*/

	public function send($masterPageFile = '')
	{
		$this->setHeader();
		$this->setContentPath();

		if(empty($masterPageFile))
			$masterPageFile	= $this->masterpageFileName;

		$themePath			= 'views/'.$masterPageFile.'.php';
		$file				= Common::getFilePath($themePath);
		$outputHandlers		= ob_list_handlers();

		if(count($outputHandlers) > 0)
		{
			ob_flush();
			ob_start();
		}
			
		extract($this->outputData);
		require $file;
			
		self::$masterSended		= TRUE;
	}


/**
* ------------------------------------------------
* Set content path
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function setContentPath($path = NULL)
	{
		if(!isset($this->outputData['contentPath']))
		{
			if($path == NULL)
			{
				$this->contentPath		= $this->subDirectory.'content/'.APPLICATIONNAME.'/';
				$this->contentPathName	= APPLICATIONNAME;
			}else{
				$this->contentPath		= $this->subDirectory.'content/'.$path.'/';
				$this->contentPathName	= $path;
			}
			
			$this->outputData['contentPath']	= $this->contentPath;
		}
	}


/**
* ------------------------------------------------
* Set response headers
* ------------------------------------------------
*
* @author ilker özcan
*
*/

	protected function setHeader()
	{
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0");
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Expires: Mon, 24 Oct 1988 22:30:00 GMT');

		if(!empty($this->xFrameOptionsHeader))
			header('X-Frame-Options: '.$this->xFrameOptionsHeader);

		header("X-XSS-Protection:1; mode=block");

		if(!empty($this->poweredByHeader))
			header('X-Powered-By: '.$this->poweredByHeader);

		if(!empty($this->xVersionHeader))
			header('X-Version: '.$this->xVersionHeader);
	}

}


/**
* ------------------------------------------------
* End of file mvc.php
* ------------------------------------------------
*/