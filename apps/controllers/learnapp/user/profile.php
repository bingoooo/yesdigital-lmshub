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
			$this->_view->json = $this->retrieve('user/profile', array('id_user'=>$_SESSION['Learnapp']['id_user']));
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}