<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Membership
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Membership
 * @version		1.0.1
 * @author		ilker ozcan
 *
 */

class MembershipUsersTable extends ActiveRecord
{
	public $userId					= ['type'=>'bigint', 'pk'=>true, 'un'=>true, 'ai' => true, 'nn'=>true];
	public $userName				= ['type'=>'VARCHAR(60)', 'nn'=>true];
	public $userNameClean			= ['type'=>'VARCHAR(60)', 'uq'=>true, 'nn'=>true];
	public $userEmail				= ['type'=>'varchar(80)', 'nn'=>true, 'fieldType'=>'email'];
	public $userEmailClean			= ['type'=>'varchar(80)', 'uq'=>true, 'nn'=>true];
	public $userGroupId				= ['type'=>'int', 'un'=>true, 'fk'=>['table' => '_groups', 'reference' => 'groupId']];
	public $registerDate			= ['type'=>'timestamp', 'default'=>'CURRENT_TIMESTAMP'];
	public $userPassword			= ['type'=>'VARCHAR(255)', 'fieldType'=>'password'];
	public $activateToken			= ['type'=>'varchar(32)', 'editable'=>false];

	public function __construct()
	{
		if(!class_exists('MembershipConfig'))
		{
			$PLF				=& Common::getInstance();
			$PLF['load']->config('membershipConfig');
		}

		$this->databaseTableName			= MembershipConfig::$databaseTable;
		$this->userGroupId['fk']['table']	= MembershipConfig::$databaseTable . $this->userGroupId['fk']['table'];

		parent::__construct();
	}
}

class MembershipGroupsTable extends ActiveRecord
{
	public $groupId					= ['type'=>'int', 'pk'=>true, 'un'=>true, 'ai' => true, 'nn'=>true];
	public $groupName				= ['type'=>'VARCHAR(45)', 'nn'=>true];

	public function __construct()
	{
		if(!class_exists('MembershipConfig'))
		{
			$PLF				=& Common::getInstance();
			$PLF['load']->config('membershipConfig');
		}

		$this->databaseTableName		= MembershipConfig::$databaseTable . '_groups';

		parent::__construct();
	}
}

class MembershipSessionTable extends ActiveRecord
{
	public $sessionId				= ['type'=>'bigint', 'pk'=>true, 'un'=>true, 'ai' => true, 'nn'=>true];
	public $userId					= ['type'=>'bigint', 'un'=>true, 'fk'=>['table'=>'', 'reference'=>'userId'], 'nn'=>true];
	public $sessionLastUpdateTime	= ['type'=>'int(10)', 'default'=>'0', 'indexed'=>true];
	public $sessionToken			= ['type'=>'VARCHAR(32)', 'nn'=>true];
	public $httpUserAgent			= ['type'=>'VARCHAR(255)', 'nn'=>true];
	public $remoteAddr				= ['type'=>'char(45)', 'nn'=>true];

	public function __construct()
	{
		if(!class_exists('MembershipConfig'))
		{
			$PLF				=& Common::getInstance();
			$PLF['load']->config('membershipConfig');
		}

		$this->databaseTableName			= MembershipConfig::$databaseTable . '_session';
		$this->userId['fk']['table']		= MembershipConfig::$databaseTable;

		parent::__construct();
	}


}

class Membership extends PLF_Model
{

	public $login					= false;
	public $groupId					= 0;
	public $userId					= 0;
	public $userName				= '';
	public $userNameClean			= '';
	public $userEmail				= '';
	public $userEmailClean			= '';

	private $userCookieId			= 0;
	private $userCookieToken		= '';

	private static $responseCodes	= array(
		'INVALID_USERNAME',
		'INVALID_PASSWORD',
		'NOT_APPROWED',
		'FORBIDDEN',
		'SUCCESS',
		'KEY_EXPIRED',
		'INVALID_KEY'
	);

	public function __construct()
	{
		parent::__construct();

		$this->load->config('membershipconfig');
		$this->dbobject		= ['USER'=>new MembershipUsersTable(), 'GROUPS'=> new MembershipGroupsTable(), 'SESSIONS'=>new MembershipSessionTable()];

		$this->load->helper('text');

		$this->deleteOldSessions();

		if($this->getUserDataFromSession())
		{
			$_SESSION['PLF_MEMBER_SESSION_UPDATE']		= time();
			$this->login								= true;
		}else{
			$cookieIdName				= MembershipConfig::$cookiePrefix . 'UID';
			$cookieIdToken				= MembershipConfig::$cookiePrefix . 'Token';
			$this->userCookieId			= (isset($_COOKIE[$cookieIdName]))?(int)$_COOKIE[$cookieIdName]:0;
			$this->userCookieToken		= (isset($_COOKIE[$cookieIdToken]))?$_COOKIE[$cookieIdToken]:'';

			if($this->userCookieId != 0)
			{
				$tokenData			= $this->decryptUserToken();
				$sessionData		= $this->getUserSessionData($tokenData->sid);
				$snhttpUserAgent	= (isset($sessionData->httpUserAgent)) ? $sessionData->httpUserAgent : '';
				$snremoteAddr		= (isset($sessionData->remoteAddr)) ? $sessionData->remoteAddr : '';
				$snsessionToken		= (isset($sessionData->sessionToken)) ? $sessionData->sessionToken : '';
				$snUid				= (isset($sessionData->userId)) ? $sessionData->userId : '';

				if($snhttpUserAgent == Server::$userAgent && $snremoteAddr == Server::$ip && $tokenData->token == $snsessionToken && $this->userCookieId == $snUid)
				{
					if(((int)$sessionData->sessionLastUpdateTime + MembershipConfig::$renewUserSessionSeconds) < time())
					{
						$this->renewUserSession($tokenData->sid, $sessionData);
					}

					$_SESSION['PLF_MEMBER']						= true;
					$this->login								= true;
					$_SESSION['PLF_MEMBER_SESSION_UPDATE']		= time();
					$_SESSION['PLF_MEMBER_SESSION_GID']			= $sessionData->userGroupId;
					$this->groupId								= $sessionData->userGroupId;
					$_SESSION['PLF_MEMBER_SESSION_UID']			= $sessionData->userId;
					$this->userId								= $sessionData->userId;
					$_SESSION['PLF_MEMBER_SESSION_UNAME']		= $sessionData->userName;
					$this->userName								= $sessionData->userName;
					$_SESSION['PLF_MEMBER_SESSION_UNAME_CLEAN']	= $sessionData->userNameClean;
					$this->userNameClean						= $sessionData->userNameClean;
					$_SESSION['PLF_MEMBER_SESSION_UEMAIL']		= $sessionData->userEmail;
					$this->userEmail							= $sessionData->userEmail;
					$_SESSION['PLF_MEMBER_SESSION_UEMAIL_CLEAN']= $sessionData->userEmailClean;
					$this->userEmailClean						= $sessionData->userEmailClean;
				}
			}
		}
	}

	public function addUser($userName, $userEmail, $passwrod, $userGroup = NULL)
	{
		$this->load->helper('text');

		$userNameClean		= slugifyText($userName);
		$userEmailClean		= slugifyText($userEmail);
		$passwordHash		= crypt($passwrod, time());
		$group				= (is_null($userGroup))?MembershipConfig::$defaultUserGroupId:$userGroup;

		$dbObject					= $this->dbobject['USER'];
		$dbObject->userName			= $userName;
		$dbObject->userNameClean	= $userNameClean;
		$dbObject->userEmail		= $userEmail;
		$dbObject->userEmailClean	= $userEmailClean;
		$dbObject->userGroupId		= $group;
		$dbObject->userPassword		= $passwordHash;
		$dbObject->sync(true);

		if($this->db->affectedRows() > 0)
			return true;
		else
			return false;
	}

	public function loginWithUserName($userName, $password)
	{
		$userNameClean		= slugifyText($userName);
		$db					= $this->dbobject['USER'];
		$userData			= $db->properties('userId', 'userGroupId', 'userPassword')->predicate('userNameClean = ?', $userNameClean)->get();

		if(count($userData) > 0)
		{
			$userRow		= $userData[0];

			if($userRow->userGroupId == 2)
				return self::$responseCodes[2];

			if($userRow->userGroupId == 4)
				return self::$responseCodes[3];

			if($this->checkUserPassword($userRow->userId, $userRow->userPassword, $password))
			{
				return self::$responseCodes[4];
			}else{
				return self::$responseCodes[1];
			}
		}else{
			return self::$responseCodes[0];
		}
	}

	public function loginWithUserEmail($userEmail, $password)
	{
		$userEmailClean		= slugifyText($userEmail);
		$db					= $this->dbobject['USER'];
		$userData			= $db->properties('userId', 'userGroupId', 'userPassword')->predicate('userEmailClean = ?', $userEmailClean)->get();

		if(count($userData) > 0)
		{
			$userRow		= $userData[0];

			if($userRow->userGroupId == 2)
				return self::$responseCodes[2];

			if($userRow->userGroupId == 4)
				return self::$responseCodes[3];

			if($this->checkUserPassword($userRow->userId, $userRow->userPassword, $password))
			{
				return self::$responseCodes[4];
			}else{
				return self::$responseCodes[1];
			}
		}else{
			return self::$responseCodes[0];
		}
	}

	public function forgotPasswordWithUserName($userName)
	{
		$userNameClean		= slugifyText($userName);
		$db					= $this->dbobject['USER'];
		$userData			= $db->properties('userId', 'userEmail')->predicate('userNameClean = ?', $userNameClean)->get();
		$response			= new stdClass();

		if(count($userData) > 0)
		{
			$userRow					= $userData[0];
			$userId						= $userRow->userId;
			$response->activateToken	= $this->updateUserForgotPasswordKey($userId);
			$response->status			= self::$responseCodes[4];
			$response->email			= $userRow->userEmail;

			return $response;
		}else{
			$response->status		= self::$responseCodes[0];
			return $response;
		}

	}

	public function forgotPasswordWithEmail($userEmail)
	{
		$userEmailClean		= slugifyText($userEmail);
		$db					= $this->dbobject['USER'];
		$userData			= $db->properties('userId', 'userEmail')->predicate('userEmailClean = ?', $userEmailClean)->get();
		$response			= new stdClass();

		if(count($userData) > 0)
		{
			$userRow					= $userData[0];
			$userId						= $userRow->userId;
			$response->activateToken	= $this->updateUserForgotPasswordKey($userId);
			$response->status			= self::$responseCodes[4];
			$response->email			= $userRow->userEmail;

			return $response;
		}else{
			$response->status		= self::$responseCodes[0];
			return $response;
		}
	}

	public function checkUserToken($key)
	{
		$decodedKey					= $this->session->decryptSessionData(base64_decode($key));
		$unserializedKey			= @unserialize($decodedKey);
		$response					= new stdClass();

		if($unserializedKey)
		{
			if($unserializedKey->createDate + MembershipConfig::$activationKeyExpire < time())
			{
				$response->status		= false;
				$response->msg			= self::$responseCodes[5];
			}else{
				$db						= $this->dbobject['USER'];
				$userData				= $db->properties('activateToken')->get($unserializedKey->userId);

				if(is_null($userData->activateToken)) {
					$response->status		= false;
					$response->msg			= self::$responseCodes[0];
				}else {
					if ($userData->activateToken == $unserializedKey->key) {
						$response->status = true;
						$_SESSION['ActivateToken'] = $decodedKey;
						$_SESSION['ActivateTokenIsCorrect'] = true;
					} else {
						$response->status = false;
						$response->msg = self::$responseCodes[6];
					}
				}
			}
		}else{
			$response->status		= false;
			$response->msg			= self::$responseCodes[6];
		}

		return $response;
	}

	public function setUserPassword($userId, $newPassword)
	{
		$this->load->helper('random');
		$cryptedPassword			= crypt($newPassword, generateAlphaNumeric(32));
		$updateParams				= array(
			'userPassword'			=> $cryptedPassword,
			'activateToken'			=> ''
		);

		$this->db->update('users', $updateParams, array('userId' => $userId));
	}

	public function logout()
	{
		$this->session->destroy();
		$this->session->destroycookie(MembershipConfig::$cookiePrefix . 'UID');
		$this->session->destroycookie(MembershipConfig::$cookiePrefix . 'Token');
	}

	private function deleteOldSessions()
	{
		$lastSessionTime		= time() - MembershipConfig::$loginExpirationSeconds;
		$this->db->delete($this->dbobject['SESSIONS']->databaseTableName, array('sessionLastUpdateTime <'=>$lastSessionTime));
	}

	private function getUserDataFromSession()
	{
		if(isset($_SESSION['PLF_MEMBER']))
		{
			$updateTime			= (isset($_SESSION['PLF_MEMBER_SESSION_UPDATE']))?$_SESSION['PLF_MEMBER_SESSION_UPDATE']:0;

			if($updateTime + MembershipConfig::$renewUserSessionSeconds >= time())
			{
				$this->groupId			= (isset($_SESSION['PLF_MEMBER_SESSION_GID']))?(int)$_SESSION['PLF_MEMBER_SESSION_GID']:0;
				$this->userId			= (isset($_SESSION['PLF_MEMBER_SESSION_UID']))?(int)$_SESSION['PLF_MEMBER_SESSION_UID']:0;
				$this->userName			= (isset($_SESSION['PLF_MEMBER_SESSION_UNAME']))?$_SESSION['PLF_MEMBER_SESSION_UNAME']:'';
				$this->userNameClean	= (isset($_SESSION['PLF_MEMBER_SESSION_UNAME_CLEAN']))?$_SESSION['PLF_MEMBER_SESSION_UNAME_CLEAN']:'';
				$this->userEmail		= (isset($_SESSION['PLF_MEMBER_SESSION_UEMAIL']))?$_SESSION['PLF_MEMBER_SESSION_UEMAIL']:'';
				$this->userEmailClean	= (isset($_SESSION['PLF_MEMBER_SESSION_UEMAIL_CLEAN']))?$_SESSION['PLF_MEMBER_SESSION_UEMAIL_CLEAN']:'';

				if($this->groupId > 0 && $this->userId > 0)
				{
					return true;
				}else{
					return false;
				}

			}else{
				return false;
			}

		}else{
			return false;
		}
	}

	private function decryptUserToken()
	{
		$tokenData					= base64_decode($this->userCookieToken);
		$decryptedToken				= $this->session->decryptSessionData($tokenData);

		return unserialize($decryptedToken);
	}

	private function encryptUserToken($tokenData)
	{
		$serializedToken			= serialize($tokenData);
		$encryptedToken				= $this->session->encryptSessionData($serializedToken);

		return base64_encode($encryptedToken);
	}

	private function getUserSessionData($sid)
	{
		$db					= $this->dbobject['SESSIONS'];
		$usersTableName		= $this->dbobject['USER']->databaseTableName;
		$db->properties('sessionToken', 'httpUserAgent', 'remoteAddr', 'sessionLastUpdateTime',
			$usersTableName. '.userId', $usersTableName. '.userName', $usersTableName. '.userNameClean',
			$usersTableName. '.userEmail', $usersTableName. '.userEmailClean', $usersTableName. '.userGroupId');
		$db->selectRelation('userId');
		$userData			= $db->get($sid);

		if(!is_null($userData))
		{
			return $userData;
		}else{
			return false;
		}
	}

	private function renewUserSession($sid, $sesssionObject)
	{
		$this->load->helper('random');
		$tokenData			= new stdClass();
		$tokenData->sid		= $sid;
		$tokenData->token	= generateAlphaNumeric(32);
		$encrypedToken		= $this->encryptUserToken($tokenData);

		$sesssionObject->sessionLastUpdateTime		= time();
		$sesssionObject->sessionToken				= $tokenData->token;
		$sesssionObject->sync();

		$expiteTime			= time() + MembershipConfig::$loginExpirationSeconds;
		$this->session->setcookie(MembershipConfig::$cookiePrefix . 'Token', $encrypedToken, $expiteTime);
	}

	private function checkUserPassword($userId, $userPassword, $inputPassword)
	{
		$passwordCorrect		= false;
		if(PHP_VERSION_ID >= 50600)
		{
			$passwordCorrect	= hash_equals($userPassword, crypt($inputPassword, $userPassword));
		}else{
			$passwordCorrect	= ($userPassword == crypt($inputPassword, $userPassword)) ? true : false;
		}

		if($passwordCorrect)
		{
			$this->load->helper('random');
			$sessionToken		= generateAlphaNumeric(32);
			$sessionObject		= $this->createUserSession($userId, $sessionToken);

			$tokenData			= new stdClass();
			$tokenData->sid		= $sessionObject->sessionId;
			$tokenData->token	= $sessionToken;
			$encrypedToken		= $this->encryptUserToken($tokenData);

			$expiteTime			= time() + MembershipConfig::$loginExpirationSeconds;
			$this->session->setcookie(MembershipConfig::$cookiePrefix . 'Token', $encrypedToken, $expiteTime);
			$this->session->setcookie(MembershipConfig::$cookiePrefix . 'UID', $userId, $expiteTime);

			$sessionData								= $this->getUserSessionData($sessionObject->sessionId);

			$_SESSION['PLF_MEMBER']						= true;
			$this->login								= true;
			$_SESSION['PLF_MEMBER_SESSION_UPDATE']		= time();
			$_SESSION['PLF_MEMBER_SESSION_GID']			= $sessionData->userGroupId;
			$this->groupId								= $sessionData->userGroupId;
			$_SESSION['PLF_MEMBER_SESSION_UID']			= $sessionData->userId;
			$this->userId								= $sessionData->userId;
			$_SESSION['PLF_MEMBER_SESSION_UNAME']		= $sessionData->userName;
			$this->userName								= $sessionData->userName;
			$_SESSION['PLF_MEMBER_SESSION_UNAME_CLEAN']	= $sessionData->userNameClean;
			$this->userNameClean						= $sessionData->userNameClean;
			$_SESSION['PLF_MEMBER_SESSION_UEMAIL']		= $sessionData->userEmail;
			$this->userEmail							= $sessionData->userEmail;
			$_SESSION['PLF_MEMBER_SESSION_UEMAIL_CLEAN']= $sessionData->userEmailClean;
			$this->userEmailClean						= $sessionData->userEmailClean;

			return true;
		}else{
			return false;
		}
	}

	private function createUserSession($userId, $sessionToken)
	{
		$db								= $this->dbobject['SESSIONS'];
		$db->userId						= $userId;
		$db->sessionLastUpdateTime		= time();
		$db->sessionToken				= $sessionToken;
		$db->httpUserAgent				= Server::$userAgent;
		$db->remoteAddr					= Server::$ip;
		$sessionObject					= $db->sync();

		return $sessionObject;
	}

	private function updateUserForgotPasswordKey($userId)
	{
		$this->load->helper('random');
		$passwordKey				= new stdClass();
		$passwordKey->createDate	= time();
		$passwordKey->userId		= $userId;
		$passwordKey->key			= generateAlphaNumeric(32);

		$this->db->update(MembershipConfig::$databaseTable, array('activateToken' => $passwordKey->key), array('userId' => $userId));

		return base64_encode($this->session->encryptSessionData(serialize($passwordKey)));
	}

}