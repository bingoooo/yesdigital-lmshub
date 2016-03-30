<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Courses extends User{
	
	function initialize(){
		parent::initialize();
	}
	
	function doPostBack(){
		try{
	        $postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
	        if (!empty($_POST['elearning'])) {
	            $postParams['elearning'] = 1;
	        }
	        if (!empty($_POST['classroom'])) {
	            $postParams['classroom'] = 1;
	        }
	        if (!empty($_POST['smartphone'])) {
	            $postParams['smartphone'] = 1;
	        }
			$this->_view->json = $this->retrieve('yny_user_api/userCourses', $postParams);
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
	function main(){
		//Nothing to code. Just preventing the parent "main" function behavior
	}
	
}