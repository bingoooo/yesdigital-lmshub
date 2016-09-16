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
        if (!empty($_REQUEST['instance']))
			$this->dbinstance = trim($_REQUEST['instance']);
		else
			$this->dbinstance = !defined('DEVEL') ? 'default' : 'ynytest';
	}

	function doPostBack(){
		//Nothing to code. Preventing parent behavior.
	}

	function main(){

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
			$villes['weather'] = $result;
			$this->_view->json = json_encode($villes);
        } else {
            echo 'no database connection ?';
        }

		/*
		* Using
		*/
		$query = 'SELECT * FROM villes;';
        $towns = $this->getDb($this->dbinstance)->getTable($query);
        $this->_view->json['towns'] = json_encode($towns);
		echo $this->_view->json;
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
