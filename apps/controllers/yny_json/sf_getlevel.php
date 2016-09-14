<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;
use \PDO;
use \PDOException;

class Sf_Getlevel extends Yny_Json {
	protected $dbinstance;

	function initialize(){
		parent::initialize();
		$this->_view->setCurrentScript(TPL_ROOT.'/views/sf_json.phtml');
	}

	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}

	function main(){
		//return phpinfo();
		$dsn = "pgsql:"
						."host=ec2-54-75-232-50.eu-west-1.compute.amazonaws.com;"
						."dbname=dbmhifu3vqjf41;"
						."user=yaenxbrkkfkiez;"
						."port=5432;"
						."sslmodule=require;"
						."password=2v_ZsFr8gvzuMWEqxd-FVGpV55";
		$db = new PDO($dsn);
		$query = 'SELECT * FROM villes';
		/*if (!empty($requestData['user'])){
			$uid = $requestData['user'];
			$query .= 'WHERE email LIKE "%'.$uid.'%"';
			$user = $this->getDb($this->dbinstance)->getTable($query);
			$this->_view->json[$uid] = $user;
		} else {
			$users = $this->getDB($this->dbinstance)->getTable($query);
			$this->_view->json[$uid] = $users;
		}*/
		$villes = $db->query($query);
		//$ville =  $villes->fetch(PDO::FETCH_ASSOC);
		//$json = '{"user":{"user_id":"01234", "firstname":"Benjamin", "lastname":"Dant", "acquired_level":"A1.1", "recommended_level":"B1.1"}}';
		$this->_view->json['data'] = $villes;
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
		return '200';
	}
}
