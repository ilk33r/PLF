<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


Class Pagination
{
	protected $pageCount;
	protected $activePage;
	
	protected $paginationType;
	protected $bracketStyle;
	
	protected $response;
	
	public function __construct($settings = array())
	{
		$this->paginationType	= (isset($settings['paginationType']))?$settings['paginationType']:'standart';
		$this->bracketStyle		= (isset($settings['bracketStyle']))?$settings['bracketStyle']:'...';
		
		$this->response			= new stdClass();
	}
	
	public function getPagination($pageCount, $activePage)
	{
		$this->pageCount		= $pageCount;
		$this->activePage		= $activePage;
		
		switch($this->paginationType)
		{
		default:
			$this->standartPagination();
		break;
		}
		
		return $this->response;
	}
	
	protected function standartPagination()
	{
		$this->setPrevNextPages();
		
		$this->response->pageList		= array();
		
		if((int)$this->pageCount < 10)
		{
			for($i = 1; $i <= (int)$this->pageCount; $i++)
			{
				$this->response->pageList[]		= $i;
			}
		}else{
			for($i = 1; $i < 4; $i++)
			{
				$this->response->pageList[]		= $i;
			}
			
			if((int)$this->activePage == 4)
			{
				$this->response->pageList[]		= 4;
			}
			
			if((int)$this->activePage != 5)
			{
				$this->response->pageList[]		= $this->bracketStyle;
			}
			
			if((int)$this->activePage == ((int)$this->pageCount - 3))
			{
				$this->response->pageList[]		= $this->activePage;
			}
			
			if(((int)$this->activePage > 4) && ((int)$this->activePage < ((int)$this->pageCount - 3)))
			{
				$this->response->pageList[]		= (int) $this->activePage - 1;
				$this->response->pageList[]		= $this->activePage;
				$this->response->pageList[]		= (int) $this->activePage + 1;
			
				if((int)$this->activePage != ((int)$this->pageCount - 4))
				{
					$this->response->pageList[]		= $this->bracketStyle;
				}
			}
			
			for($j = ((int)$this->pageCount - 2); $j <= (int)$this->pageCount; $j++)
			{
				$this->response->pageList[]		= $j;
			}
		}
	}
	
	protected function setPrevNextPages()
	{
		if((int)$this->activePage > 1)
		{
			$this->response->previousPage	= ((int) $this->activePage) - 1;
		}else{
			$this->response->previousPage	= FALSE;
		}
		
		if((int)$this->activePage < (int)$this->pageCount)
		{
			$this->response->nextPage		= ((int) $this->activePage) + 1;
		}else{
			$this->response->nextPage		= FALSE;
		}
	}
}