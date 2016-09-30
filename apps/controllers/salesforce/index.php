<?php
namespace FragTale\Controller\Salesforce;
use FragTale\Controller\Salesforce;
use FragTale\Application as App;

class Sf_Sync extends Salesforce {
	protected $dbinstance;

	protected $ips = array('136.146.128.8', '85.222.130.8', '127.0.0.1', '89.225.245.6');

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
		$query = 'SELECT * FROM Users';
		$learners = $this->getDb($this->dbinstance)->getTable($query);

		$this->_view->json['learners'] = $learners;
		//App::catchError();
	}

	function checkRestrictedHosts(){
		// Disables restricted IPs
		/*if(in_array($_SERVER['REMOTE_ADDR'], $this->ips)){
			return '*';
		} else {
			$this->exitOnError(403, 'Forbidden for '.$_SERVER['REMOTE_ADDR']);
		}*/
		return '*';
	}
}
