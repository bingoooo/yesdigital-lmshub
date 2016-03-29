<?php
namespace FragTale\Controller\Learnapp;
use FragTale\Controller\Learnapp;

/**
 * @author fabrice
 */
class User extends Learnapp{
	
	function initialize(){
		if (empty($_SESSION['Learnapp']['id_user'])){
			$this->exitOnError(403, 'User is not logged in.');
		}
		parent::initialize();
	}
	
	function doPostBack(){
		$result = $this->retrieve('user/profile', array('id_user'=>$_SESSION['Learnapp']['id_user']));
		$this->_view->json = $result;
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
	
}