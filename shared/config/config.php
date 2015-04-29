<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');


/**
* ------------------------------------------------
* General Config Class
* ------------------------------------------------
* 
* @author ilker ozcan 
* 
*/

final class Config 
{


/**
* ------------------------------------------------
* Permitted uri characters
* ------------------------------------------------
* 
* @author ilker ozcan 
* @var string $permitted_uri_chars
* 
*/
	
	public static $permitted_uri_chars			= 'a-z A-Z0-9\~\%\.\:\_\-\,';


/**
* ------------------------------------------------
* PLF Directory
* ------------------------------------------------
*
* @author ilker ozcan
* @var string $plfDirectory
* @example /test/
*
*/

	public static $plfDirectory				= '/';


/**
* ------------------------------------------------
* Encription key
* ------------------------------------------------
* 
* @author ilker ozcan 
* @var string $encription_key
* If you using session you must set an encryption key
* 
*/
	
	public static $encription_key				= 'PLF-012345678901';


/**
* ------------------------------------------------
* Session and cookie settings
* ------------------------------------------------
* 
* @author ilker ozcan
* @var string $session_cookie_name = the name you want for the cookie
* @var int $cookie_expiration = the number of SECONDS you want the session to last.
* Set to zero for no expiration. Default value is 172800 (3 days)
* @var string $cookie_domain = Set to .your-domain.com for site-wide cookies
* @var string $cookie_path = Typically will be a forward slash
* @var bool $cookie_secure
* 
*/

	public static $session_inited_cookie_name	= 'PLF_Inited';
	public static $cookie_expiration			= 172800;
	public static $cookie_domain				= '';
	public static $cookie_path					= '/';
	public static $cookie_secure				= FALSE;


}


/**
* ------------------------------------------------
* End of file config.php
* ------------------------------------------------
*/
