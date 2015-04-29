<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Membershipgroupsadmin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 17 15 00:44
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */


class Membershipgroupsadmin extends AdminLibrary
{
	public function __construct()
	{
		$this->dbobject				= ['model'=>'membership', 'dbtable' => 'MembershipGroupsTable'];
		$this->name					= 'z1_groups';
		$this->localizedName		= 'User Groups';
		$this->icon					= 'glyphicon-eye-open';
		$this->listFields			= ['groupId', 'groupName'];
		$this->listFieldNames		= ['ID', 'Group Name'];
		$this->editFields			= ['groupName'];
		$this->editFieldNames		= ['Group Name'];
	}

	public function fieldName()
	{
		return 'groupName';
	}

}