<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Privacy_Policy extends User{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		//Nothing to code. Just preventing the parent "doPostBack" function behavior
	}
	
	function main(){
		try{
			$posts = $this->getPHPInputs();
			if (isset($posts['privacy_policy'])){
				$postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
				$postParams['privacy_policy'] = (int)$posts['privacy_policy'];
				$this->_view->json = $this->retrieve('user/edit', $postParams);
			}
			else{
				$this->exitOnError(500, 'Mising required argument privacy_policy');
			}
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
}