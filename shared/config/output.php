<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* Output sub-class
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class Output extends PLF_Output 
{


/**
* ------------------------------------------------
* Calculate script execution time for debugging
* ------------------------------------------------
*
* @author ilker özcan
* @var bool $calculateExecutionTime	= TRUE
*
*/

	public $calculateExecutionTime	= TRUE;


/**
* ------------------------------------------------
* default masterpage file name
* ------------------------------------------------
*
* @author ilker özcan
*
*/

	public $masterpageFileName	= 'masterpage';


/**
* ------------------------------------------------
* X-Powered-By header
* ------------------------------------------------
*
* @author ilker özcan
*
*/

	public $poweredByHeader			= 'PHP PLF Framework (www.plf.rocks)';


/**
* ------------------------------------------------
* X-Frame-options header
* ------------------------------------------------
*
* @author ilker özcan
*
*/

	public $xFrameOptionsHeader		= 'SAMEORIGIN';


/**
* ------------------------------------------------
* X-Version header
* ------------------------------------------------
*
* @author ilker özcan
*
*/

	public $xVersionHeader			= '1.0.0';


}


/**
* ------------------------------------------------
* End of file output.php
* ------------------------------------------------
*/