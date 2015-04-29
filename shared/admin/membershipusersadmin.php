<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Membershipusersadmin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 17 15 00:44
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */

class Membershipusersadmin extends AdminLibrary
{
	public function __construct()
	{
		$this->dbobject				= ['model'=>'membership', 'dbtable'=>'MembershipUsersTable'];
		$this->name					= 'z2_users';
		$this->localizedName		= 'Users';
		$this->icon					= 'glyphicon-user';
		$this->listFields			= ['userId', 'userName', 'userEmail', 'get_UserGroupName', 'registerDate'];
		$this->listFieldNames		= ['ID', 'User Name', 'User Email', 'Group Name', 'Register Date'];
		$this->editFields			= ['userName', 'userEmail', 'userGroupId', 'registerDate', 'userPassword', 'activateToken'];
		$this->editFieldNames		= ['User Name', 'User E-Mail', 'User Group', 'Register Date', 'Password', 'Activate Token'];
	}

	public function fieldName()
	{
		return 'userName';
	}

	public function extraLinks()
	{
		parent::extraLinks();
		return array('/admin/custom/logout' => 'Logout');
	}

	public function get_UserGroupName($pkValue, $dbObject)
	{
		$PLF		=& Common::getInstance();
		$db			= $PLF['db'];
		return $db->query('select g.groupName from ' . MembershipConfig::$databaseTable . ' u left join ' . MembershipConfig::$databaseTable . '_groups g on u.userGroupId = g.groupId where u.userId = ?', array($pkValue))->row()->groupName;
	}

	public function save($postData, $allFields, $dbObject)
	{
		$PLF		=& Common::getInstance();
		$PLF['load']->helper('text');

		$postData['userNameClean']			= slugifyText($postData['userName']);
		$postData['userEmailClean']			= slugifyText($postData['userEmail']);
		$postData['userPassword']			= crypt($postData['userPassword'], time());
		if(empty($postData['registerDate']))
		{
			$postData['registerDate']		= date('Y-m-d', time());
		}

		return parent::save($postData, $allFields, $dbObject);
	}
}