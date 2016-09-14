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
        $dbname = 'fhsc23783mu7c';
        $host = 'eec2-54-228-247-206.eu-west-1.compute.amazonaws.com';
        $dbuser = 'rnerypprnrtsjx';
        $dbpass = 'K4SnQkbdazACuf2dNuY3O_9dwY';
        $dsn = "pgsql:host='eec2-54-228-247-206.eu-west-1.compute.amazonaws.com';dbname='dfhsc23783mu7c';user='rnerypprnrtsjx';port='5432';password='K4SnQkbdazACuf2dNuY3O_9dwY'";
		//$db = new PDO($dsn);
		$query = 'SELECT * FROM villes';
        //$towns = $db->query($query);
		$json2 = '{"test":"test", "message":"message"}';
		echo $json2;
        echo $towns;
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
