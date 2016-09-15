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
		$json2 = '{"users":[{"user_id":"13838","lastname":"Demo","firstname":"Tester","email":"nesrine.feraga@yesnyou.com","branch_id":"452","branch_name":"","acquired_level":"B1.6","recommended_level":"B2.1"},{"user_id":"13885","lastname":"Tester","firstname":"Demo","email":"nesrine.feraga@yesnyou.com","branch_id":"467","branch_name":"","acquired_level":"B1.6","recommended_level":"B2.1"},{"user_id":"13886","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"88","branch_name":"","acquired_level":"A1.2","recommended_level":"A1.3"},{"user_id":"13887","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"88","branch_name":"","acquired_level":"A1.4","recommended_level":"A2.1"},{"user_id":"13965","lastname":"Demo","firstname":"Tester","email":"nesrine.feraga@yesnyou.com","branch_id":"290","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"13966","lastname":"Demo","firstname":"Tester2","email":"nesrine.feraga@yesnyou.com","branch_id":"290","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"13982","lastname":"Credit Agricole","firstname":"Tester","email":"nesrine.Feraga@yesnyou.com","branch_id":"83","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"14111","lastname":"LMS","firstname":"Tester","email":"nesrine.feraga@yesnyou.com","branch_id":"352","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"14153","lastname":"Feraga","firstname":"Nesrine","email":"Nesrine.feraga@yesnyou.com","branch_id":"352","branch_name":"","acquired_level":"B1.6","recommended_level":"B2.1"},{"user_id":"14180","lastname":"Tester","firstname":"Demo","email":"nesrine.feraga@yesnyou.com","branch_id":"467","branch_name":"","acquired_level":"B1.6","recommended_level":"B2.1"},{"user_id":"14263","lastname":"FERREIRA","firstname":"Luciana","email":"lferreira@citelum.fr","branch_id":"559","branch_name":"80020000007lPmQAAU","acquired_level":"B1.6","recommended_level":"B2.1"},{"user_id":"14374","lastname":"GRANGEON","firstname":"Elsa","email":"elsa_ferreira@merck.com","branch_id":"585","branch_name":"80020000008QeSWAA0","acquired_level":"B1.2","recommended_level":"B1.3"},{"user_id":"14766","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"88","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"14806","lastname":"Laufer","firstname":"MARIE ANDREE","email":"marie-andree.laufer@cetelem.fr","branch_id":"648","branch_name":"80020000008Qg6IAAS","acquired_level":null,"recommended_level":null},{"user_id":"15088","lastname":"FERRER Christelle","firstname":"Christelle","email":"christelle.ferrer@fr.transavia.com","branch_id":"494","branch_name":"800200000081QylAAE","acquired_level":null,"recommended_level":null}]}';
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
