<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Course extends User{
	
	function doPostBack(){
		//Nothing to code. Just preventing the parent "doPostBack" function behavior
	}
	
	function main(){
		try{
			$posts = !empty($_POST['id_course']) ? $_POST : $this->getPHPInputs();
			if (empty($posts['id_course'])){
				$this->_view->json = $this->returnJsonError('Missing required "id_course" parameter');
				return;
			}
			
			$id_user	= $_SESSION['Learnapp']['id_user'];
			$id_course	= (int)$posts['id_course'];
			
			## Get the 2 lists
			$filteredSessions = array();
			// First, the session list
			$sessions = $this->retrieve('yny_session_api/list', array('id_course'=>$id_course));
			if (!empty($sessions['sessions'])) {
				foreach ($sessions['sessions'] as $session) {
					if (!empty($session['id_session'])) {
						// 2nd, users foreach sessions
						$lstUsers = $this->retrieve('yny_session_api/listUsers', array (
							'id_course' => $id_course,
							'id_session' => $session['id_session'] 
						));
						if (!empty($lstUsers['users'])) {
							foreach ($lstUsers['users'] as $usr) {
								if (!empty($usr['id_user']) && $usr['id_user'] == $id_user) {
									$filteredSessions[] = $session;
									break;
								}
							}
						}
					}
				}
			}
			if (!empty($filteredSessions)) {
				$this->_view->json = array(
					'success' => true,
					'sessions' => $filteredSessions 
				);
			}
			else
				if (!empty($sessions['message']))
					$this->_view->json = $this->returnJsonError($sessions['message']);
				else
					$this->_view->json = $this->returnJsonError('No data found');
		}
		catch(Exception $ex){
			$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
		}
	}
	
}