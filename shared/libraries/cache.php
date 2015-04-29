<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


class Cache
{

	public function __construct()
	{
		$cacheConfigFile		= Common::getFilePath('config/cacheconfig.php');
		require($cacheConfigFile);

		if(!is_dir(CONTENTFOLDER . Cacheconfig::$cacheDir))
		{
			throw new Exception(Corelanguage::CACHE_DIRECTORY_WRITE_ERROR);
		}
		
		if(!is_writable(CONTENTFOLDER . Cacheconfig::$cacheDir))
		{
			throw new Exception(Corelanguage::CACHE_DIRECTORY_WRITE_ERROR);
		}
	}
	
	public function getCacheData($cacheName)
	{
		$file		= CONTENTFOLDER . Cacheconfig::$cacheDir . '/' . $cacheName;
		
		if(!is_file($file) || !is_readable($file))
		{
			return FALSE;
		}
		
		$handle		= fopen($file, 'r');

		if(!$handle)
		{
			return FALSE;
		}else{
			
			$cacheExpired		= FALSE;
			
			if(Cacheconfig::$expireTime != 0)
			{
				$cacheExpired		= $this->isCacheExpired($file);
			}
			
			if($cacheExpired)
			{
				fclose($handle);
				@unlink($file);
				return FALSE;
			}
			
			$data			= fread($handle, filesize($file));
			fclose($handle);
			return $data;
		}
	}
	
	private function isCacheExpired($file)
	{
		$lastModified	= filemtime($file);
		
		if($lastModified + Cacheconfig::$expireTime >= time())
		{
			return FALSE;
		}else{
			return TRUE;
		}
	}

	public function setCacheData($cacheName, $data)
	{
		$file		= CONTENTFOLDER.cacheconfig::$cacheDir.'/'.$cacheName;
		
		if(is_file($file))
		{
			$this->deleteCacheFile($file);
		}
		
		$handle		= fopen($file, 'w');
		if(!$handle)
		{
			return FALSE;
		}else{
			fwrite($handle, $data);
			fclose($handle);
			return TRUE;
		}
	}
	
	public function deleteCache($cacheName)
	{
		$file		= CONTENTFOLDER . Cacheconfig::$cacheDir.'/'.$cacheName;
		
		if(is_file($file))
			$this->deleteCacheFile($file);
	}
	
	public function deleteAllCache()
	{
		if ($handle = opendir(CONTENTFOLDER . Cacheconfig::$cacheDir))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if($entry == '.' || $entry == '..')
        			continue;
				
				$file		= CONTENTFOLDER . Cacheconfig::$cacheDir . '/' . $entry;
				$this->deleteCacheFile($file);
    		}
			
			closedir($handle);
		}
	}
	
	private function deleteCacheFile($file)
	{
		if(is_writable($file))
			@unlink($file);
	}
}