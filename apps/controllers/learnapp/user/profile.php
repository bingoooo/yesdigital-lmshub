<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Profile extends User{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		try{
			$result = $this->retrieve('user/profile', array('id_user'=>$_SESSION['Learnapp']['id_user']));
			$this->_view->json = $result;
		}
		catch(Exception $ex){
			$this->_view->json = $this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}