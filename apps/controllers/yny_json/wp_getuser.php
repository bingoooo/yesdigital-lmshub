<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;

/**
 * @author fabrice
 */
class Wp_Getuser extends Yny_Json{
	
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
		$requestData = $this->getRequestData(true, true, true);
		if (empty($requestData['user']))
			$this->exitOnError(417, 'Expectation failed');
		$uid = (int)$requestData['user'];
		$user= $this->getDb($this->dbinstance)->getRow('SELECT lastname AS name, firstname AS surname, email FROM UserInfo WHERE user_id = '.$uid);
		if (!empty($user['email']))
			$this->_view->json[$uid] = $user;
	}
	
}