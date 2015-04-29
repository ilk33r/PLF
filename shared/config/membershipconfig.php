<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* Membership Config Class
* ------------------------------------------------
* 
* @author ilker ozcan 
* 
*/

final class MembershipConfig 
{

	public static $databaseTable				= 'users';

	public static $cookiePrefix					= 'PLF_member_';

	public static $loginExpirationSeconds		= 172800; // 48 hours

	public static $renewUserSessionSeconds		= 300; // 5 minutes

	public static $defaultUserGroupId			= 1;

	public static $activationKeyExpire			= 432000; // 5 days

}


/**
* ------------------------------------------------
* End of file membership.php
* ------------------------------------------------
*/