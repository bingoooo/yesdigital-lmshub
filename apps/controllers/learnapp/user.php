<?php
namespace FragTale\Controller\Learnapp;
use FragTale\Controller\Learnapp;

/**
 * @author fabrice
 */
class User extends Learnapp{
	
	function initialize(){
		parent::initialize();
		if (empty($_SESSION['Learnapp']['id_user'])){
			$this->_view->json = array('success'=>false, 'message'=>'Session not opened, credentials missing');
		}
	}
	
	function doPostBack(){
		//Nothing to code
	}
	
	function main(){
		$this->exitOnError(403, 'Unauthorized request');
	}
	
}