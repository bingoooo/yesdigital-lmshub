<?php
namespace FragTale\Controller\Learnapp\Organization;
use FragTale\Controller\Learnapp\Organization;

/**
 * @author fabrice
 */
class Play extends Organization{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		try{
	        $postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
	        if (!empty($_POST['id_org'])) {
	            $postParams['id_org'] = (int)$_POST['id_org'];
	            if (!empty($_POST['id_scormitem']))
	            	$postParams['id_scormitem'] = (int)$_POST['id_scormitem'];
				$this->_view->json = $this->retrieve('organization/play', $postParams);
	        }
	        else
	        	$this->_view->json = $this->returnJsonError('Missing required "id_org" parameter');
		}
		catch(Exception $ex){
			$this->_view->json = $this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}