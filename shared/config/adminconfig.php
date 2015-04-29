<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
 * ------------------------------------------------
 * Admin Config Class
 * ------------------------------------------------
 *
 * @author ilker ozcan
 *
 */

final class Adminconfig
{


	/**
	 * ------------------------------------------------
	 * Set false if you do not want using admin feature.
	 * ------------------------------------------------
	 *
	 * @author ilker ozcan
	 * @var bool $adminPageIsActive
	 *
	 */

	public static $adminPageIsActive		= true;


	/**
	 * ------------------------------------------------
	 * Only this group id access the admin page
	 * ------------------------------------------------
	 *
	 * @author ilker ozcan
	 * @var int $adminGroupId
	 *
	 */

	public static $adminGroupId				= 3;


	/**
	 * ------------------------------------------------
	 * Admin page url (You should also change the routes.php file)
	 * ------------------------------------------------
	 *
	 * @author ilker ozcan
	 * @var string $adminPath
	 *
	 */

	public static $adminPath				= '/admin';


	/**
	 * ------------------------------------------------
	 * Admin login url (You should also change the routes.php file)
	 * ------------------------------------------------
	 *
	 * @author ilker ozcan
	 * @var string $adminPath
	 *
	 */

	public static $adminLoginUrl			= '/admin/login';


}


/**
 * ------------------------------------------------
 * End of file adminconfig.php
 * ------------------------------------------------
 */
