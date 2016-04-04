<?php
namespace FragTale\Controller\Learnapp\Organization;
use FragTale\Controller\Learnapp\Organization;

/**
 * @author fabrice
 */
class Objects extends Organization{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		try{
			$posts = $this->getPHPInputs();
	        $postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
	        $id_course = !empty($_POST['id_course']) ? $_POST['id_course'] : (!empty($posts['id_course']) ? $posts['id_course'] : null);
	        if ($id_course) {
	            $postParams['id_course'] = (int)$id_course;
	            if ($id_org = (!empty($_POST['id_org']) ? $_POST['id_org'] : (!empty($posts['id_org']) ? $posts['id_org'] : null)))
	            	$postParams['id_org'] = (int)$id_org;
				$this->_view->json = $this->retrieve('organization/listObjects', $postParams);
	        }
	        else
	        	$this->_view->json = $this->returnJsonError('Missing required "id_course" parameter');
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}