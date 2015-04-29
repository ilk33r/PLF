<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* URL sub-class (Route rules)
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class Route extends PLF_Uri 
{


/**
* ------------------------------------------------
* URI Routing
* ------------------------------------------------
* 
* @author ilker ozcan
* @var $defaultController 
* if URI contains no data, this controller class should be loaded
* 
*/

	protected $defaultController	= 'demo';


/**
* ------------------------------------------------
* User defined routes
* ------------------------------------------------
* 
* @author 
* @var array $routes
* This file lets you re-map URI requests to specific controller functions.
* default URLs are index.php?PLF=controller/function/parameter1/parameter2
* @example $routes['test/(:num)/(:num)/(:any)']	= 'class/function/$1/$2/$3';
* 
*/

	protected $userRoutes			= array(
			'admin'							=> 'admin',
			'admin/login'					=> 'admin/login',
			'admin/custom/(:any)'			=> 'admin/custom/$1',
			'admin/(:any)'					=> 'admin/module/$1',
			'admin/(:any)/(:num)'			=> 'admin/module/$1/$2',
			'admin/add/(:any)'				=> 'admin/addmodule/$1',
			'admin/edit/(:any)/(:num)'		=> 'admin/editmodule/$1/$2',
			'admin/delete/(:any)/(:num)'	=> 'admin/deletemodule/$1/$2',
	);
	
	public function __construct()
	{
		//$this->userRoutes['example(:any)']				= 'exampleClass/exampleMethot/$1';
	}
}


/**
* ------------------------------------------------
* End of file route.php
* ------------------------------------------------
*/