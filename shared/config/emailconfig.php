<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');
/**
 * Created by PhpStorm.
 * User: ilk3r
 * Date: 26/01/15
 * Time: 17:21
 */


final class Emailconfig
{

	public static $smtpServer		= 'smtp-mail.outlook.com';

	public static $smtpAuth			= true;

	public  static $smtpUsername	= '';

	public static $smtpPassword		= '';

	public static $smtpProtocol		= 'tls';

	public static $smtpPort			= 587;

	public static $emailFrom		= '';

	public static $emailFromName	= '';

}