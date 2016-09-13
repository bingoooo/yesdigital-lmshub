<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;

class Sf_Getlevel extends Yny_Json {
	protected $dbinstance;

	function initialize(){
		parent::initialize();
		if (!empty($_REQUEST['instance']))
			$this->dbinstance = trim($_REQUEST['instance']);
		else
			$this->dbinstance = !defined('DEVEL') ? 'ynynewlms' : 'ynytest';
	}

	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}

	function main(){
		/*$requestData = $this->getRequestData(true, true, true);
		$uid = 'users';
		$query = 'SELECT '.
				'UI.user_id,UI.lastname,UI.firstname,UI.email, '.
				'BU.branch_id, '.
				'BI.branch_name, '.
				'AL.value AS acquired_level, '.
				'RL.value AS recommended_level '.
				'FROM YNY_NEWLMS.UserInfo AS UI '.
				'LEFT JOIN YNY_NEWLMS.BranchUsers AS BU ON BU.user_id = UI.user_id AND BU.branch_id <> 0 '.
				'LEFT JOIN YNY_NEWLMS.BranchInfo AS BI ON BI.branch_id = BU.branch_id '.
				'LEFT JOIN YNY_NEWLMS.UserAdditionalInfo AS AL ON AL.user_id = UI.user_id AND AL.attribute LIKE "%acquired level%" '.
				'LEFT JOIN YNY_NEWLMS.UserAdditionalInfo AS RL ON RL.user_id = UI.user_id AND RL.attribute LIKE "%recommended level%" ';
		if (!empty($requestData['user'])){
			$uid = $requestData['user'];
			$query .= 'WHERE email LIKE "%'.$uid.'%"';
			$user = $this->getDb($this->dbinstance)->getTable($query);
			$this->_view->json[$uid] = $user;
		} else {
			$users = $this->getDB($this->dbinstance)->getTable($query);
			$this->_view->json[$uid] = $users;
		}*/

		$this->_view->json = {user: 200};
	}
}
