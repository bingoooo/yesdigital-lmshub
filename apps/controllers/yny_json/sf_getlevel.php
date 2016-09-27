<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;

class Sf_Getlevel extends Yny_Json {
	protected $dbinstance;

	protected $ips = array('136.146.128.8', '85.222.130.8', '127.0.0.1');

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
		$query = 'SELECT DISTINCT * FROM users AS UI '.
			'WHERE (UI.acquired_level != "null" || UI.recommended_level != "null") && (UI.lastname != "") ';
		if(isset($_GET['user'])){
			$query .= '&& (UI.user_id = '.$_GET['user'].') ';
		}
		if(isset($_GET['lastname'])){
			$query .= '&& (UI.lastname = "'.$_GET['lastname'].'") ';
		}
			if(isset($_GET['firstname'])){
				$query .= '&& (UI.firstname = "'.$_GET['firstname'].'") ';
		}
			if(isset($_GET['email'])){
				$query .= '&& (UI.email = "'.$_GET['email'].'") ';
		}
		$query .= 'ORDER BY UI.user_id, UI.lastname, UI.firstname '.
					'';
		$levels = $this->getDb($this->dbinstance)->getTable($query);
		$this->_view->json['learners'] = $levels;
	}

	/*function checkRestrictedHosts(){
		if(in_array($_SERVER['REMOTE_ADDR'], $this->ips)){
			return '*';
		} else {
			$this->exitOnError(403, 'Forbidden for '.$_SERVER['REMOTE_ADDR']);
		}
	}*/
}
