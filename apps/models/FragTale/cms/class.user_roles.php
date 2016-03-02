<?php
namespace FragTale\CMS;
use \FragTale\Db\CMS;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class User_Roles extends CMS{
	protected $_tablename = 'user_roles';
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Reference to Role
	 * @var int
	 */
	var $rid;
	
	/**
	 * Fetch single array filled with a given User ID
	 * @param int $uid
	 * @return array of int
	 */
	function getUserRoles($uid){
		$roles = $this->select("uid='$uid'");
		$rids = array();
		foreach ($roles as $role)
			$rids[] = $role['rid'];
		return $rids;
	}
}