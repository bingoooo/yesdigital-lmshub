<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Lostpassword extends User{
	
	function initialize(){
		//Nothing to code
	}
	
	function doPostBack(){
		//Nothing to code
	}
	
	function main(){
		try{
			if (!empty($_POST['email']))
				$this->_view->json = $this->retrieve('user/lostpassword', array('email'=>trim($_POST['email'])));
			else
				$this->_view->json = $this->returnJsonError('Missing required parameter "email"');
		}
		catch(Exception $ex){
			$this->_view->json = $this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
}