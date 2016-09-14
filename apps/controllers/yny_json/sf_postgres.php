<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;
use \PDO;
use \PDOException;

class Sf_Postgres extends Yny_Json {
	protected $dbinstance;

	function initialize(){
		parent::initialize();
		$this->_view->setCurrentScript(TPL_ROOT.'/views/sf_json.phtml');
	}

	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}

	function main(){
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
        $towns = $db->query($query);
		$json2 = '{"test":"test", "message":"message"}';
		echo $json2;
        echo $towns;
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
