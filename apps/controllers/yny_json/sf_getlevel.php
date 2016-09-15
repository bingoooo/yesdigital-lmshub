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

		//$ville =  $villes->fetch(PDO::FETCH_ASSOC);
		$json1 = '{"user":[{"user_id":"01234", "firstname":"Benjamin", "lastname":"Dant", "acquired_level":"A1.1", "recommended_level":"B1.1"}]}';
		$json2 = '{"users":[{"user_id":"21585","lastname":"FERMELY","firstname":"Marithie","email":"marithie.fermely@orange.com","branch_id":"1825","branch_name":"80020000005UgQRAA0","acquired_level":"A2.4","recommended_level":"A2.5"},{"user_id":"21585","lastname":"FERMELY","firstname":"Marithie","email":"marithie.fermely@orange.com","branch_id":"2135","branch_name":"80020000005UkuJAAS","acquired_level":"A2.4","recommended_level":"A2.5"},{"user_id":"21818","lastname":"DUJARDIN","firstname":"Jean","email":"nesrine.feraga@yakjh.com","branch_id":"1463","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"21892","lastname":"REMERY","firstname":"Jennifer","email":"remery.jennifer@neuf.fr","branch_id":"1876","branch_name":"80020000005UhNnAAK","acquired_level":"A2.2","recommended_level":"A2.3"},{"user_id":"22010","lastname":"GROSSO","firstname":"Roberto","email":"roberto.grosso@sergeferrari.com","branch_id":"1892","branch_name":"80020000004zR0KAAU","acquired_level":"A2.4","recommended_level":"A2.5"},{"user_id":"22393","lastname":"FERHANI","firstname":"Belaid","email":"belaid.ferhani@entrepose.com","branch_id":"1955","branch_name":"80020000005Uj3kAAC","acquired_level":"A1.3","recommended_level":"A1.4"}]}';
		if(isset($_REQUEST['user'])) echo $json1;
		else echo $json2;
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
		return '*';
	}
}
