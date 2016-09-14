<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;

class Sf_Getlevel extends Yny_Json {
	protected $dbinstance;

	function initialize(){
		parent::initialize();
		$this->_view->setCurrentScript(TPL_ROOT.'/views/sf_json.phtml');
		if (!empty($_REQUEST['instance']))
			$this->dbinstance = trim($_REQUEST['instance']);
		else
			$this->dbinstance = !defined('DEVEL') ? 'ynynewlms' : 'ynytest';
	}

	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}

	function main(){
		//return phpinfo();
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
		$json = '{"user":{"user_id":"01234", "firstname":"Benjamin", "lastname":"Dant", "acquired_level":"A1.1", "recommended_level":"B1.1"}}';
		$this->_view->json['data'] = $json;
		echo $json;
	}

	function checkRestrictedHosts(){
		/*if (defined('DEVEL') || in_array($_SERVER['REMOTE_ADDR'], $this->forcedAllowedIP) || $this->allowedHosts === '*')
			return '*';
		//Only for AJAX request (so, HTTP_ORIGIN is set)
		if ($origin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : false){
			if (in_array($origin, $this->allowedHosts))
				return $origin;
		}
		$this->exitOnError(403, 'Forbidden');*/
		return $this->_view->json['data'];
	}
}
