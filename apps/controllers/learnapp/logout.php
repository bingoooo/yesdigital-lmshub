<?php
namespace FragTale\Controller\Learnapp;
use FragTale\Controller\Learnapp;

/**
 * @author fabrice
 */
class Logout extends Learnapp{
	
	function initialize(){
		parent::initialize();
	}
	
	function main(){
		if (empty($_SESSION['Learnapp']['id_user'])){
			return $this->_view->json = array('success'=>false, 'message'=>'User already logged out');
		}
		$result = $this->retrieve('user/logout', array('id_user'=>$_SESSION['Learnapp']['id_user']));
		$this->_view->json = $result;
		if (!empty($result['success'])){
			unset($_SESSION['Learnapp']);
		}
	}
}