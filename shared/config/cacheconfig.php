<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
 * ------------------------------------------------
 * Cache config class
 * ------------------------------------------------
 *
 * @author ilker ozcan
 *
 */

final class Cacheconfig
{

/**
 * ------------------------------------------------
 * Cache directory name this folder must be inside
 * content/APPNAME
 * ------------------------------------------------
 *
 * @author ilker ozcan
 * @var string $cacheDir
 *
 */

	public static $cacheDir			= 'cache';


/**
 * ------------------------------------------------
 * Cache expire time (second)
 * ------------------------------------------------
 *
 * @author ilker ozcan
 * @var int $expireTime
 *
 */

	public static $expireTime		= 0;


}