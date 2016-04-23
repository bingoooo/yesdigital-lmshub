<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Lostpassword extends User{
	
	function initialize(){
		if (!defined('ENV') || ENV!=='devel')
			$this->setLayout('json');	//On production environment, use JSON format
		else{
			$this->setLayout('clean');	//On development environment, use HTML format to print or dump the result
		}
	}
	
	function doPostBack(){
		//Nothing to code
	}
	
	function main(){
		try{
			$posts = !empty($_POST[email]) ? $_POST : $this->getPHPInputs();
			if (!empty($posts['email']))
				$this->_view->json = $this->retrieve('user/lostpassword', array('email'=>trim($posts['email'])));
			else
				$this->_view->json = $this->returnJsonError('Missing required parameter "email"');
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
}