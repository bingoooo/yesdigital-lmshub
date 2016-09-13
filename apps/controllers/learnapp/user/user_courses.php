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
		//Nothing to code. Just preventing the parent "doPostBack" function behavior
	}
	
	function main(){
		try{
			$posts = $this->getPHPInputs();
			$postParams['id_user'] = $_SESSION['Learnapp']['id_user'];
	        if (!empty($posts['elearning']))
	            $postParams['elearning'] = 1;
	        elseif (!empty($posts['elearning']))
	       		$postParams['elearning'] = 1;
	        if (!empty($posts['classroom']))
	            $postParams['classroom'] = 1;
	        elseif (!empty($posts['classroom']))
	            $postParams['classroom'] = 1;
			$this->_view->json = $this->retrieve('user/userCourses', $postParams);
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
}