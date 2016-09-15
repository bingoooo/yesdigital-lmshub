<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;

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
		//$db = new PDO($dsn, $dbuser, $dbpass);

        /*$app = new Application();
        $app->register(
            new PdoServiceProvider(),
            array(
                'pdo.dsn' => $dsn,
                'pdo.username' => $dbuser,
                'pdo.password' => $dbpass
            )
        );
        $pdo = $app['pdo'];*/

		$query = 'SELECT * FROM villes';
        $towns = $this->getDb($this->dbinstance)->getTable($query);
		$json = '{"test":"test", "message":"message"}';
        $this->_view->json = $json;
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
