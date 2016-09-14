<?php
namespace FragTale\Controller\Yny_Json;
use FragTale\Controller\Yny_Json;
use \PDO;
use \PDOException;
use Herrera\Pdo\PdoServiceProvider;
use Silex\Application;

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
        $port = getenv('PORT');
        $dsn = 'pgsql:dbname='.$dbname.';host='.$host.';port='.$port;
        $dbopts = parse_url(getenv('DATABASE_URL'));
		$db = new PDO($dsn, $dbuser, $dbpass);

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
        //$towns = $pdo->query($query);
		//$json = '{"test":"test", "message":"message"}';
		echo $json;
        foreach ($dbopts as $key => $value){
            echo 'Key : '.$key.' : '.$value;
        }
        echo $dbname;
        echo $host;
        echo $towns;
	}

	function checkRestrictedHosts(){
		return '*';
	}
}
