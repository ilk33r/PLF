<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

class Image
{
	private $sourceImage;
	private $newImagePath;
	private $newImageType;
	private $quality		= 60;
	private $aspectRatio	= TRUE;
	
	private $errorString;
	private $errorStatus	= FALSE;
	private $errorCode		= 0;
	private $result;
	
	private $gdImage;
	private $width;
	private $minWidth;
	private $height;
	private $minHeight;
	private $sourceWidth;
	private $sourceHeight;
	private $sourceType;
	private $newWidth;
	private $newHeight;
	
	
	public function __construct($settings = array())
	{
		if(!extension_loaded('GD'))
		{
			$this->errorStatus		= TRUE;
			throw new Exception(Corelanguage::IMAGE_GD_NOT_FOUND);
		}
		
		if(isset($settings['sourceImage']))
		{
			$this->sourceImage		= $settings['sourceImage'];
			if(!file_exists($this->sourceImage))
			{
				$this->errorStatus		= TRUE;
				throw new Exception(Corelanguage::IMAGE_SOURCE_FILE_NOT_FOUNT);
			}
		}
		
		if(isset($settings['newImagePath'])){$this->newImagePath=$settings['newImagePath'];}
		if(isset($settings['aspectRatio'])){$this->aspectRatio=$settings['aspectRatio'];}
		if(isset($settings['quality'])){$this->quality=$settings['quality'];}
		if(isset($settings['imageType'])){$this->newImageType=$settings['imageType'];}
		
		$this->result		= new stdClass();
	}

	public function reConfig($settings = array())
	{
		if(isset($settings['sourceImage']))
		{
			$this->sourceImage		= $settings['sourceImage'];
			if(!file_exists($this->sourceImage))
			{
				$this->errorStatus		= TRUE;
				throw new Exception(Corelanguage::IMAGE_SOURCE_FILE_NOT_FOUNT);
			}
		}
		
		if(isset($settings['newImagePath'])){$this->newImagePath=$settings['newImagePath'];}else{unset($this->newImagePath);}
		if(isset($settings['aspectRatio'])){$this->aspectRatio=$settings['aspectRatio'];}else{$this->aspectRatio=TRUE;}
		if(isset($settings['quality'])){$this->quality=$settings['quality'];}else{$this->quality=60;}
		if(isset($settings['imageType'])){$this->newImageType=$settings['imageType'];}
		
		$this->result		= new stdClass();
	}

	public function error()
	{
		$response			= new stdClass();
		$response->error	= $this->errorStatus;
		$response->message	= $this->errorString;
		$response->errCode	= $this->errorCode;
		return $response;
	}

	public function resize($newWidth, $newHeight, $minWidth = 0, $minHeight = 0)
	{
		if(!isset($this->sourceImage))
		{
			throw new Exception(Corelanguage::IMAGE_SOURCE_FILE_NOT_FOUNT);
		}else{
		
			$this->width		= $newWidth;
			$this->height		= $newHeight;
			$this->minWidth		= $minWidth;
			$this->minHeight	= $minHeight;
			$sourceInfo			= getimagesize($this->sourceImage);
			$this->sourceWidth	= $sourceInfo[0];
			$this->sourceHeight	= $sourceInfo[1];
			$this->sourceType	= $sourceInfo['mime'];
			$this->gdImage		= $this->createTemporaryImage();
			$resizedImage		= $this->createEmptyImage();
		
			if($this->gdImage && $resizedImage)
			{
				if($this->sourceType == 'image/png')
				{
					imagealphablending($this->gdImage, TRUE);
					imagealphablending($resizedImage, FALSE);
					imagesavealpha($resizedImage, TRUE);
				}

				$resizeStatus	= @imagecopyresampled($resizedImage, $this->gdImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->sourceWidth, $this->sourceHeight);
				if(!$resizeStatus)
				{
					$this->errorStatus	= TRUE;
					$this->errorString	= Corelanguage::IMAGE_NOT_RESIZEING;
					$this->errorCode	= 102;
				}else{
					$this->saveImage($resizedImage);
				}
			}
		}
	}

	public function cropImage($width, $height)
	{
		if(!isset($this->sourceImage))
		{
			throw new Exception(Corelanguage::IMAGE_SOURCE_FILE_NOT_FOUNT);
		}else{

			$this->width		= $width;
			$this->height		= $height;
			$this->minWidth		= 0;
			$this->minHeight	= 0;
			$this->aspectRatio	= false;
			$sourceInfo			= getimagesize($this->sourceImage);
			$this->sourceWidth	= $sourceInfo[0];
			$this->sourceHeight	= $sourceInfo[1];
			$this->sourceType	= $sourceInfo['mime'];
			$this->gdImage		= $this->createTemporaryImage();
			//$croppedImage		= $this->createEmptyImage();

			if($this->gdImage)
			{
				$destX				= floor(($this->sourceWidth - $this->width) / 2);
				$destY				= floor(($this->sourceHeight - $this->height) / 2);
				$croppedImageData	= array('x' => $destX , 'y' => $destY, 'width' => $this->width, 'height'=> $this->height);
				$croppedImage		= imagecrop($this->gdImage, $croppedImageData);

				if($this->sourceType == 'image/png')
				{
					imagealphablending($this->gdImage, TRUE);
					imagealphablending($croppedImage, FALSE);
					imagesavealpha($croppedImage, TRUE);
				}

				/*$resizeStatus	= @imagecopyresampled($croppedImage, $this->gdImage, 0, 0, $destX, $destY, $this->newWidth, $this->newHeight, $this->sourceWidth, $this->sourceHeight);
				if(!$resizeStatus)
				{
					$this->errorStatus	= TRUE;
					$this->errorString	= Corelanguage::IMAGE_NOT_RESIZEING;
					$this->errorCode	= 102;
				}else{
					$this->saveImage($croppedImage);
				}*/
				$this->saveImage($croppedImage);
			}
		}
	}

	public function getLastDimensions()
	{
		$response			= new stdClass();
		$response->width	= round($this->newWidth);
		$response->height	= round($this->newHeight);

		return $response;
	}

	private function createTemporaryImage()
	{
		switch($this->sourceType)
		{
		default:
			$this->errorStatus	= TRUE;
			$this->errorString	= Corelanguage::IMAGE_TYPE_NOT_SUPPORTED;
			$this->errorCode	= 100;
			return FALSE;
		break;
		case 'image/jpeg':
			return @imagecreatefromjpeg($this->sourceImage);
		break;
		case 'image/pjpeg':
			return @imagecreatefromjpeg($this->sourceImage);
		break;
		case 'image/gif':
			return @imagecreatefromgif($this->sourceImage);
		break;
		case 'image/png':
			return @imagecreatefrompng($this->sourceImage);
		break;
		}
	}

	private function createEmptyImage()
	{
		if($this->aspectRatio)
		{
			$ratio		= $this->sourceWidth / $this->sourceHeight;
			
			if(($this->minWidth > 0) || ($this->minHeight > 0))
			{
				$newWidth			= $this->width;
				$aspectWidth		= $ratio * $this->height;
				$newHeight			= $this->height;
				$aspectHeight		= $this->width / $ratio;

				$targetWidth		= $newWidth;
				$targetHeight		= $aspectHeight;
				$resizeCalculated	= false;

				if(($targetHeight < $this->minHeight) && ($this->minHeight > 0) && !$resizeCalculated)
				{
					$targetWidth	= $aspectWidth;
					$targetHeight	= $newHeight;
				}
			
				$this->newWidth		= $targetWidth;
				$this->newHeight	= $targetHeight;
			}else{
				$newWidth	= $this->width;
				$newHeight	= $newWidth / $ratio;

				$this->newWidth		= $newWidth;
				$this->newHeight	= $newHeight;
			}
		}else{
			$this->newWidth		= $this->width;
			$this->newHeight	= $this->height;
		}

		return imagecreatetruecolor($this->newWidth, $this->newHeight);
	}

	private function saveImage($image)
	{
		if(!empty($this->newImagePath))
		{
			$fileName	= $this->newImagePath;
		}else{
			$fileName	= $this->sourceImage;
			@chmod($fileName, 0775);
		}

		$imageType	= ($this->newImageType)?$this->newImageType:$this->sourceType;
		
		switch($imageType)
		{
		default:
			$this->errorStatus	= TRUE;
			$this->errorString	= Corelanguage::IMAGE_TYPE_NOT_SUPPORTED;
			$this->errorCode	= 100;
		break;
		case 'image/jpeg':
			$imageStatus	= @imagejpeg($image, $fileName, $this->quality);
		break;
		case 'image/gif':
			$imageStatus	= @imagegif($image, $fileName);
		break;
		case 'image/png':
			$tmpQuality		= ($this->quality > 9)?9:$this->quality;
			imagealphablending($image, FALSE);
			imagesavealpha($image, TRUE);
			$imageStatus	= @imagepng($image, $fileName, $tmpQuality);
		break;
		}
		
		if(!$this->errorStatus && !$imageStatus)
		{
			$this->errorStatus	= TRUE;
			$this->errorString	= Corelanguage::IMAGE_SAVE_ERROR;
			$this->errorCode	= 103;
		}else{
			@chmod($fileName, 0644);
		}
	}
}