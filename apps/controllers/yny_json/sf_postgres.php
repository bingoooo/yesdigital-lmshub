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
        $towns = '{"ville":"San Francisco"}';
        $dbname = getenv('DATABASE');
        $host = getenv('HOST');
        $dbuser = getenv('USER');
        $dbpass = getenv('PASSWORD');
        $dsn = "pgsql:host='eec2-54-228-247-206.eu-west-1.compute.amazonaws.com';dbname='dfhsc23783mu7c';user='rnerypprnrtsjx';port='5432';password='K4SnQkbdazACuf2dNuY3O_9dwY'";
        $dbopts = parse_url(getenv('DATABASE_URL'));
		//$db = new PDO($dsn);
		$query = 'SELECT * FROM villes';
        //$towns = $db->query($query);
		$json2 = '{"test":"test", "message":"message"}';
		echo $json2.'<br>';
        foreach ($dbopts as $key => $value){
            echo 'Key : '.$key.' : '.$value.'<br>';
        }
        echo $dbname.'<br>';
        echo $host.'<br>';
        echo $towns.'<br>';
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
