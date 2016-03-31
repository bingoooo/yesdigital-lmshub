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
			unset($_SESSION['Learnapp']);
			$this->exitOnError(403, 'Unrecognized user');
		}
		$result = $this->retrieve('user/authenticate', array('username'=>trim($_POST['email']), 'password'=>trim($_POST['password'])));
		if (!empty($result['success']) && !empty($result['token'])){
			$_SESSION['Learnapp']['token']	= $result['token'];
			$_SESSION['Learnapp']['id_user']= $result['id_user'];
			$this->_view->json = $result;
		}
		else{
			unset($_SESSION['Learnapp']);
			$this->_view->json = $this->returnJsonError('Invalid user');
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
}