<?php
if ( ! defined('BASEPATH')) exit('Script access forbidden.');

class Upload
{
	private $uploadPath;
	private $fileName;
	private $maxSize		= 0;
	private $maxWidth		= 0;
	private $maxHeight		= 0;
	private $overwrite		= FALSE;
	private $allowedTypes	= array();
	private $errorString;
	private $errorStatus	= FALSE;
	private $errorCode		= 0;
	private $uploadResult;
	
	
	public function __construct($settings = array())
	{
		$this->updateSettings($settings);
	}
	
	public function updateSettings($settings = array())
	{
		if(isset($settings['uploadPath']))
		{
			$this->uploadPath		= $settings['uploadPath'];
		}
		
		if(isset($settings['fileName']))
		{
			$this->fileName			= trim($settings['fileName']);
		}
		
		if(isset($settings['maxSize'])){$this->maxSize=$settings['maxSize'];}
		if(isset($settings['maxWidth'])){$this->maxWidth=$settings['maxWidth'];}
		if(isset($settings['maxHeight'])){$this->maxHeight=$settings['maxHeight'];}
		if(isset($settings['overwrite'])){$this->overwrite=$settings['overwrite'];}
		if(isset($settings['allowedTypes'])){$this->allowedTypes=$settings['allowedTypes'];}
		if(isset($settings['addExtension'])){$this->addExtension=$settings['addExtension'];}
		
		$this->uploadResult		= new stdClass();
	}
	
	public function error()
	{
		$response			= new stdClass();
		$response->error	= $this->errorStatus;
		$response->message	= $this->errorString;
		$response->errCode	= $this->errorCode;
		return $response;
	}

	public function doUpload($name)
	{
		if(!is_dir($this->uploadPath))
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_DIRECTORY_ERROR;
			$this->errorCode		= 101;
		}else if(!is_writable($this->uploadPath))
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_DIRECTORY_WRITE_ERROR;
			$this->errorCode		= 102;
		}else if(!$this->checkTypes($name))
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_MIME_ERROR;
			$this->errorCode		= 104;
		}else if(!$this->checkFileSize($name))
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_FILESIZE_ERROR;
			$this->errorCode		= 105;
		}else if($_FILES[$name]['error'] != 0)
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_UNKOWN_ERROR;
			$this->errorCode		= 106;
		}else{
			$this->uploadTmpFile($name);
		}
	}

	public function info()
	{
		return $this->uploadResult;
	}
	
	private function checkTypes($fileDataKey)
	{
		if(count($this->allowedTypes) > 0)
		{
			if(in_array($_FILES[$fileDataKey]['type'], $this->allowedTypes))
			{
				return TRUE;
			}else{
				return FALSE;
			}
		}else{
			return TRUE;
		}
	}

	private function checkFileSize($fileDataKey)
	{
		if($this->maxSize > 0)
		{
			if($_FILES[$fileDataKey]['size'] > $this->maxSize)
			{
				return FALSE;
			}else{
				return TRUE;
			}
		}else{
			return TRUE;
		}
	}

	private function uploadTmpFile($fileDataKey)
	{
		$tmpFileName	= 'tmp-'.$this->fileName;
		$file			= $this->uploadPath.'/'.$this->fileName;
		$tmpFile		= $this->uploadPath.'/'.$tmpFileName;
		
		$this->uploadResult->fileName	= $this->fileName;
		$this->uploadResult->filePath	= $file;
		$this->uploadResult->fileSize	= $_FILES[$fileDataKey]['size'];
		$this->uploadResult->fileType	= $_FILES[$fileDataKey]['type'];
		
		
		if(!$this->overwrite)
		{
			if(file_exists($file))
			{
				$this->errorStatus		= TRUE;
				$this->errorString		= Corelanguage::UPLOAD_FILE_EXISTS_ERROR;
				$this->errorCode		= 107;
				$uploadStatus			= FALSE;
			}else{
				$uploadStatus			= @move_uploaded_file($_FILES[$fileDataKey]['tmp_name'], $tmpFile);
				@chmod($tmpFile, 0644);
			}
		}else{
			$uploadStatus			= @move_uploaded_file($_FILES[$fileDataKey]['tmp_name'], $tmpFile);
			@chmod($tmpFile, 0644);
		}
		
		if($uploadStatus)
		{
			$this->checkFileIsImage($tmpFile, $file);
		}else{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_SOURCE_ERROR;
			$this->errorCode		= 108;
		}
	}
	
	private function checkFileIsImage($tmpFile, $realFile)
	{
		$imageSize		= @getimagesize($tmpFile);
		if($imageSize !== false)
		{
			$this->uploadResult->isImage	= TRUE;
			if(($this->maxWidth > 0) || ($this->maxHeight > 0))
			{
				$this->checkImageSize($tmpFile, $realFile, $imageSize[0], $imageSize[1]);
			}else{
				$this->uploadResult->imageWidth		= $imageSize[0];
				$this->uploadResult->imageHeight	= $imageSize[1];
				$this->saveFile($tmpFile, $realFile);
			}
		}else{
			if(($this->maxWidth > 0) || ($this->maxHeight > 0))
			{
				$this->errorStatus		= TRUE;
				$this->errorString		= Corelanguage::UPLOAD_NOT_IMAGE_ERROR;
				$this->errorCode		= 109;
				$this->deleteFile($tmpFile);
			}else{
				$this->uploadResult->isImage	= FALSE;
				$this->saveFile($tmpFile, $realFile);
			}
		}
	}
	
	private function deleteFile($source)
	{
		@unlink($source);
	}
	
	private function checkImageSize($tmpFile, $realFile, $width, $height)
	{
		if(($this->maxWidth > 0) && ($width > $this->maxWidth))
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_IMAGE_WIDTH_ERROR;
			$this->errorCode		= 110;
			$this->deleteFile($tmpFile);
		}else if(($this->maxHeight > 0) && ($height > $this->maxHeight))
		{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_IMAGE_HEIGHT_ERROR;
			$this->errorCode		= 111;
			$this->deleteFile($tmpFile);
		}else{
			$this->uploadResult->imageWidth		= $width;
			$this->uploadResult->imageHeight	= $height;
			$this->saveFile($tmpFile, $realFile);
		}
	}

	private function saveFile($tmpFile, $realFile)
	{
		if(@rename($tmpFile, $realFile))
		{
			@chmod($realFile, 0644);
			$this->uploadResult->fileHash	= md5_file($realFile);
		}else{
			$this->errorStatus		= TRUE;
			$this->errorString		= Corelanguage::UPLOAD_FILE_NAME_ERROR;
			$this->errorCode		= 112;
			$this->deleteFile($tmpFile);
		}
	}
}