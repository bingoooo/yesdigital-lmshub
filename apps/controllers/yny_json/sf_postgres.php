<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;
use \PDO;
use \PDOException;

class Sf_Postgres extends Yny_Json {

	protected $allowedHosts = array(
			'https://eu6.salesforce.com','https://yesdigital-lmshub.herokuapp.com',
			'https://ec2-54-228-247-206.eu-west-1.compute.amazonaws.com',
	);

	protected $forcedAllowedIP = array('127.0.0.1', '10.107.2.180');

	protected $dbinstance;

	function initialize(){
		parent::initialize();
		$this->_view->setCurrentScript(TPL_ROOT.'/views/sf_json.phtml');
        if (!empty($_REQUEST['instance']))
			$this->dbinstance = trim($_REQUEST['instance']);
		else
			$this->dbinstance = !defined('DEVEL') ? 'default' : 'lmshub';
	}

	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}

	function main(){

		$json = array();
		/*
		* Using basic pdo_psql
		*/
        $dsn = 'pgsql:dbname='.getenv('DATABASE').';host='.getenv('HOST').';port='.getenv('DBPORT');
        try {
    		$db = new PDO($dsn, getenv('USERNAME'), getenv('PASSWORD'));
        } catch(PDOException $e) {
            $db = null;
            echo 'ERREUR DB: '.$e->getMessage();
        }
        if($db){
    		$query = $db->prepare("SELECT * FROM temps;");
            $query->execute();
            $result = $query->fetchAll();
			$json['weather'] = $result;
        } else {
            echo 'no database connection ?';
        }

		/*
		* Using Built-in class
		*/
		$query2 = 'SELECT * FROM villes;';
        $towns = $this->getDb($this->dbinstance)->getTable($query2);
		$json['towns'] = $towns;
		$json['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
		$json['REMOTE_HOST'] = $_SERVER['REMOTE_HOST'];
		$json['REMOTE_PORT'] = $_SERVER['REMOTE_PORT'];
		$json['HTTPS'] = $_SERVER['HTTPS'];
		$json['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
		$this->_view->json = json_encode($json);
		echo $this->_view->json;
	}

	/*function checkRestrictedHosts(){
		return '*';
	}*/
}
