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
		//Nothing to code. Just preventing the parent "doPostBack" function behavior
	}
	
	function main(){
		$posts = $this->getPHPInputs();
		if (empty($posts['email']) || empty($posts['password'])){
			unset($_SESSION['Learnapp']);
			$this->exitOnError(403, 'Unrecognized user');
		}
		$result = $this->retrieve('user/authenticate', array('username'=>trim($posts['email']), 'password'=>trim($posts['password'])));
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
}