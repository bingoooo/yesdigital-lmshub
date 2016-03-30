<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class User_Courses extends User{
	
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
	 		$result = $this->retrieve('user/userCourses', $postParams);
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