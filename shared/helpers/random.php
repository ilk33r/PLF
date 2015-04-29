<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


if(!function_exists('generateAlphaNumeric'))
{
	function generateAlphaNumeric($characterCount)
	{
		$characters		= 'abcdefghijklmnopqrstuvwxyz0123456789';

		$result			= '';
		for ($i = 0; $i < $characterCount; $i++)
		{
			$result		.=	$characters[mt_rand(0, strlen($characters) - 1)];
		}

		return $result;
	}
}