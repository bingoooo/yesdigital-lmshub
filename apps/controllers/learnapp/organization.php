<?php
namespace FragTale\Controller\Learnapp;
use FragTale\Controller\Learnapp;

/**
 * @author fabrice
 */
class Organization extends Learnapp{
	
	function initialize(){
		if (empty($_SESSION['Learnapp']['id_user'])){
			$this->exitOnError(403, 'User is not logged in.');
		}
		parent::initialize();
	}
	
	function doPostBack(){
		//Nothing to code
	}
	
	function main(){
		$this->exitOnError(403, 'Unauthorized request');
	}
	
}