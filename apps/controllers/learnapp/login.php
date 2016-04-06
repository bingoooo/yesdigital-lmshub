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
		$email = !empty($_POST['email']) ? trim($_POST['email']) : (!empty($posts['email']) ? trim($posts['email']) : null);
		$password = !empty($_POST['password']) ? trim($_POST['password']) : (!empty($posts['password']) ? trim($posts['password']) : null);
		if (!$email || !$password){
			unset($_SESSION['Learnapp']);
			$this->exitOnError(403, 'Missing required parameters');
		}
		$result = $this->retrieve('user/authenticate', array('username'=>$email, 'password'=>$password));
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