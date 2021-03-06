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

	protected $dbinstance;

	protected $ips = array('136.146.128.8', '85.222.130.8', '127.0.0.1');

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
		$query3 = 'SELECT * FROM users;';
		$learners = $this->getDb($this->dbinstance)->getTable($query3);

		$json['towns'] = $towns;
		$json['learners'] = $learners;
		$this->_view->json = json_encode($json);
		echo $this->_view->json;
	}

	function checkRestrictedHosts(){
		if(in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $this->ips)){
			return '*';
		} else {
			$this->exitOnError(403, 'Forbidden for '.$_SERVER['HTTP_X_FORWARDED_FOR'].' '.$this->ips);
		}
	}
}
