<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


if(!function_exists('executeProcessInBackground'))
{
	function executeProcessInBackground($process)
	{
		shell_exec('php ' . BASEPATH . INDEXFILE . ' ' . $process . ' >/dev/null 2>/dev/null &');
	}
}