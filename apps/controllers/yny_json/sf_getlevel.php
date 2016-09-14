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
		$dsn = "pgsql:"
						."host=ec2-54-75-232-50.eu-west-1.compute.amazonaws.com;"
						."dbname=DATABASE;"
						."user=yaenxbrkkfkiez;"
						."port=5432;"
						."sslmodule=require;"
						."password=2v_ZsFr8gvzuMWEqxd-FVGpV55";
		$db = new PDO($dsn);
		$query = 'SELECT * FROM villes';
		/*if (!empty($requestData['user'])){
			$uid = $requestData['user'];
			$query .= 'WHERE email LIKE "%'.$uid.'%"';
			$user = $this->getDb($this->dbinstance)->getTable($query);
			$this->_view->json[$uid] = $user;
		} else {
			$users = $this->getDB($this->dbinstance)->getTable($query);
			$this->_view->json[$uid] = $users;
		}*/
		//$ville =  $villes->fetch(PDO::FETCH_ASSOC);
		//$json1 = '{"user":[{"user_id":"01234", "firstname":"Benjamin", "lastname":"Dant", "acquired_level":"A1.1", "recommended_level":"B1.1"}}]';
		//$json2 = '{"users":[{"user_id":"270","lastname":"","firstname":"","email":"","branch_id":null,"branch_name":null,"acquired_level":null,"recommended_level":null},{"user_id":"12306","lastname":"Van Poeck","firstname":"Steven","email":"steven.vanpoeck@yesnyou.com","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12315","lastname":"Docebo","firstname":"Staff","email":"mauro.giavarini+yesnyou1@docebo.com","branch_id":null,"branch_name":null,"acquired_level":null,"recommended_level":null},{"user_id":"12316","lastname":"Besnard","firstname":"Aurelie","email":"aurelie.besnard@yesnyou.com","branch_id":"66","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12319","lastname":"Young-Tricot","firstname":"Candace","email":"candace.yang-tricot@yesnyou.com","branch_id":null,"branch_name":null,"acquired_level":null,"recommended_level":null},{"user_id":"12322","lastname":"Payne","firstname":"Mark","email":"mark.payne@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12342","lastname":"Ortiz","firstname":"Chris","email":"chris.ortiz@yesnyou.com","branch_id":null,"branch_name":null,"acquired_level":null,"recommended_level":null},{"user_id":"12343","lastname":"Lemaitre","firstname":"David","email":"david.lemaitre@yesnyou.com","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12344","lastname":"Chalons","firstname":"Nicolas","email":"nicolas.chalons@yesnyou.com","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12345","lastname":"Admin","firstname":"Nicolas","email":"nicolas.admin@yesnyou.com","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12346","lastname":"","firstname":"","email":"","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12363","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"60","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12363","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"875","branch_name":"80020000006dn1PAAQ","acquired_level":null,"recommended_level":null},{"user_id":"12363","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"1520","branch_name":"800200000085wfOAAQ","acquired_level":null,"recommended_level":null},{"user_id":"12363","lastname":"Feraga","firstname":"Nesrine","email":"nesrine.feraga@yesnyou.com","branch_id":"1747","branch_name":"800200000085zc3AAA","acquired_level":null,"recommended_level":null},{"user_id":"12364","lastname":"Okeeffe","firstname":"Cormac","email":"","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12365","lastname":"Antalis","firstname":"Stylianos","email":"stylianos.antalis@yesnyou.com","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12365","lastname":"Antalis","firstname":"Stylianos","email":"stylianos.antalis@yesnyou.com","branch_id":"59","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12365","lastname":"Antalis","firstname":"Stylianos","email":"stylianos.antalis@yesnyou.com","branch_id":"60","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12367","lastname":"Waterhouse","firstname":"Rosanna","email":"rosanna.waterhouse@yesnyou.com","branch_id":null,"branch_name":null,"acquired_level":null,"recommended_level":null},{"user_id":"12372","lastname":"Mercier","firstname":"Jerome","email":"aurelie.besnard@yourenglishsolution.com","branch_id":"14","branch_name":"YNY_FR_PARIS","acquired_level":"B2.1","recommended_level":"B2.6"},{"user_id":"12394","lastname":"Dos Santos","firstname":"Maria","email":"maria.dossantos@yesnyou.com","branch_id":"34","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12396","lastname":"JOUANNEL","firstname":"Melissa","email":"melissa.jouannel@yesnyou.com","branch_id":"34","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12398","lastname":"Saulneron","firstname":"Rapha\u00ebl","email":"raphael.saulneron@hotmail.fr","branch_id":"34","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12399","lastname":"Tarallo","firstname":"Antonio","email":"antonio.tarallo@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12400","lastname":"Linaker","firstname":"Matt","email":"matt.linaker@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12401","lastname":"Rowland","firstname":"Helen","email":"helen.rowland@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12402","lastname":"Houser","firstname":"Justin","email":"justin.houser@yesnyou.com","branch_id":"3","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12402","lastname":"Houser","firstname":"Justin","email":"justin.houser@yesnyou.com","branch_id":"12","branch_name":"YNY_FR","acquired_level":null,"recommended_level":null},{"user_id":"12402","lastname":"Houser","firstname":"Justin","email":"justin.houser@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12402","lastname":"Houser","firstname":"Justin","email":"justin.houser@yesnyou.com","branch_id":"1055","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12403","lastname":"Price","firstname":"Joann","email":"joann.price@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12404","lastname":"Waterhouse","firstname":"Rosanna","email":"rosanna.waterhouse@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12405","lastname":"Autran","firstname":"Betsy","email":"betsy.autran@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12406","lastname":"Yang","firstname":"Candace","email":"aurelie.besnard@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12407","lastname":"Leyre","firstname":"Marie","email":"marie.leyre@yesnyou.com","branch_id":"22","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12408","lastname":"Beedaysee","firstname":"Ashley","email":"ashley.beedaysee@yesnyou.com","branch_id":"23","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12409","lastname":"Mommalier","firstname":"Magali","email":"magali.mommalier@yesnyou.com","branch_id":"23","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12410","lastname":"Janak","firstname":"Rhiann","email":"rhiann.janak@yesnyou.com","branch_id":"24","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12411","lastname":"Hann","firstname":"Jonathan","email":"jonathan.hann@yesnyou.com","branch_id":"24","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12412","lastname":"Nicolaas","firstname":"Willy","email":"willy.nicolaas@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12413","lastname":"Waddell","firstname":"Evie","email":"aurelie.besnard@yesnyou.com","branch_id":"25","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12414","lastname":"Davis","firstname":"Lynn","email":"aurelie.besnard@yesnyou.com","branch_id":"26","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12415","lastname":"Hondropoulos","firstname":"George","email":"george.hondropoulos@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12416","lastname":"Gouzelis","firstname":"Alexandros","email":"alexandros.gouzelis@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12417","lastname":"Pappas","firstname":"Anthony","email":"anthony.pappas@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12418","lastname":"Tibay","firstname":"Andrea","email":"andrea.tibay@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12419","lastname":"Maglis","firstname":"Bill","email":"bill.maglis@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12420","lastname":"Digaletou","firstname":"Christina","email":"christina.digaletou@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12421","lastname":"Samoilis","firstname":"Christopher","email":"christopher.samoilis@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12423","lastname":"Spathi","firstname":"Constantina","email":"constantina.spathi@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12424","lastname":"Sheridan","firstname":"Diane","email":"diane.sheridan@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12425","lastname":"Ginsburg","firstname":"Elise","email":"elise.ginsburg@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12426","lastname":"Laiou","firstname":"Elita","email":"elita.laiou@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12427","lastname":"Van Dieman","firstname":"Eon","email":"eon.vandieman@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12428","lastname":"Padfield","firstname":"Fiona","email":"fiona.padfield@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12429","lastname":"Valli","firstname":"Irene","email":"irene.valli@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12430","lastname":"Greaney","firstname":"John","email":"john.greaney@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12431","lastname":"Podimata","firstname":"Kate","email":"kate.podimata@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12432","lastname":"Strang","firstname":"Lauren","email":"lauren.strang@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12433","lastname":"Grant","firstname":"Linda","email":"aurelie.besnard@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12434","lastname":"Szomanska","firstname":"Magdalena","email":"magdalena.szomanska@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12435","lastname":"Marino","firstname":"Maria","email":"maria.marino@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null},{"user_id":"12436","lastname":"Tsotsou_OLD","firstname":"Marielena_OLD","email":"marielena.tsotsou@yesnyou.com","branch_id":"27","branch_name":"","acquired_level":null,"recommended_level":null}]}';
		foreach($db->query($query) as $ville){
			$this->_view->json = $ville;
			break;
		}
		var_dump($this->_view->json);
		/*if(isset($_REQUEST['user'])){
			if(isset($villes)){
				echo $villes;
			} else {
				echo $json1;
			}
		}
		echo $json2;*/
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
