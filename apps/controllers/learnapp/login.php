<?php
namespace FragTale\Controller\Learnapp;
use FragTale\Controller\Learnapp;

/**
 * @author fabrice
 */
class Login extends Learnapp{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		if (empty($_POST['email']) || empty($_POST['password'])){
			$this->exitOnError(403, 'Unrecognized user');
		}
		$this->_view->json = $this->retrieve('user/authenticate', array('username'=>trim($_POST['email']), 'password'=>trim($_POST['password'])));
		
	}
	
	function main(){
		
	}
}