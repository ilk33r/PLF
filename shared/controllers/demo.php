<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

class Demo extends PLF_Controller
{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{
		$this->ob->send('masterpage');
	}
}