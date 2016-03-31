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
		try{
			$postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
			if (!empty($_POST['datetime_from'])) {
				$postParams['datetime_from'] = $_POST['datetime_from'];
			}
			if (!empty($_POST['datetime_to'])) {
				$postParams['datetime_to'] = $_POST['datetime_to'];
			}
			if (isset($_POST['nb_recent'])) {
				$postParams['nb_recent'] = (int)$_POST['nb_recent'];
			}
			if (isset($_POST['by_status'])) {
				$postParams['by_status'] = (int)$_POST['by_status'];
			}
			$this->_view->json = $this->retrieve('yny_user/notifications', $postParams);
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}