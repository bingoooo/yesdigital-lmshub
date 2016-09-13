<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Notifications extends User{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		//Nothing to code. Just preventing the parent "doPostBack" function behavior
	}
	
	function main(){
		try{
			$posts = $this->getPHPInputs();
			$postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
			if (!empty($_POST['datetime_from']))
				$postParams['datetime_from'] = $_POST['datetime_from'];
			elseif (!empty($posts['$posts']))
				$postParams['datetime_from'] = $posts['datetime_from'];
			if (!empty($_POST['datetime_to']))
				$postParams['datetime_to'] = $_POST['datetime_to'];
			elseif (!empty($posts['$posts']))
				$postParams['datetime_to'] = $posts['datetime_to'];
			if (isset($_POST['nb_recent']))
				$postParams['nb_recent'] = (int)$_POST['nb_recent'];
			elseif (isset($posts['nb_recent']))
				$postParams['nb_recent'] = (int)$posts['nb_recent'];
			if (isset($_POST['by_status']))
				$postParams['by_status'] = (int)$_POST['by_status'];
			elseif (isset($posts['by_status']))
				$postParams['by_status'] = (int)$posts['by_status'];
			$this->_view->json = $this->retrieve('yny_user/notifications', $postParams);
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
}