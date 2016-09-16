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
        $dsn = 'pgsql:dbname='.getenv('DATABASE').';host='.getenv('HOST').';port=5432';
        //$dsn = 'pgsql:dbname=dfhsc23783mu7c;host=ec2-54-228-247-206.eu-west-1.compute.amazonaws.com;port=5432';
        try {
    		$db = new PDO($dsn, 'rnerypprnrtsjx', getenv('PASSWORD'));
        } catch(PDOException $e) {
            $db = null;
            echo 'ERREUR DB: '.$e->getMessage();
        }
        if($db){
    		$query = $db->prepare("SELECT * FROM temps;");
            $query->execute();
            $result = $query->fetchAll();
			$villes['towns'] = $result;
			$this->_view->json = json_encode($villes);
			echo ' : '.getenv('PASSWORD');
			echo ' : '.getenv('USER');
			echo $this->_view->json;
        } else {
            echo 'no database connection ?';
        }
        //Show config var

        //$towns = $this->getDb($this->dbinstance)->getTable($query);
		//$json = '{"test":"test", "message":"message"}';
        //$this->_view->json = $towns;
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
