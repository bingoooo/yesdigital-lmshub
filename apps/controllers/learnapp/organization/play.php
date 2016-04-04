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
			$posts = $this->getPHPInputs();
			$postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
	        $id_org = !empty($_POST['id_org']) ? $_POST['id_org'] : (!empty($posts['id_org']) ? $posts['id_org'] : null);
			if ($id_org) {
	            $postParams['id_org'] = (int)id_org;
	            if ($id_scormitem = (!empty($_POST['id_scormitem']) ? $_POST['id_scormitem'] : (!empty($posts['id_scormitem']) ? $posts['id_scormitem'] : null)))
	            	$postParams['id_scormitem'] = (int)$id_scormitem;
				$this->_view->json = $this->retrieve('organization/play', $postParams);
	        }
	        else
	        	$this->_view->json = $this->returnJsonError('Missing required "id_org" parameter');
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}