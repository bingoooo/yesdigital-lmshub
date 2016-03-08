<?php
namespace FragTale\Controller;
use FragTale\Controller;
use FragTale\YnY\Curl as YnYCurl;

/**
 * @author fabrice
 */
class Fer extends Controller{
	
	protected $allowedIP = array(
			'127.0.0.1',		//localhost
			'54.86.250.179',	//AFOSCHI LMS
			'54.85.129.207',	//Seemed to be the e-time API server that calling the FER for the Sandbox
	);
	
	function initialize(){
		$this->setLayout('clean');
		$this->_view->json = array();
	}
	
	function doPostBack(){
		
	}
	
	function main(){
		$this->_view->json = array(
			'User'		=>$this->retrieveUserData(),
			'LP_Data'	=>$this->retrieveLpDataForUser()
		);
	}
	
	function logAjaxRequest($addedmsg=''){
		$msg = $_SERVER['REMOTE_ADDR'].' | '.$_SERVER['REQUEST_URI'].(!empty($_SERVER['HTTP_USER_AGENT']) ? ' | '.$_SERVER['HTTP_USER_AGENT'] : '**No UA**');
		$completeMsg = date('Y-m-d H:i:s').' ** '.$msg.(!empty($addedmsg)? ' | '.$addedmsg : '');
		$logFile = DOC_ROOT.'/logs/log-'.date('Ym').'.log';
		fputs(fopen($logFile, 'a+'), $completeMsg."\n");
	}
	
	function restrictedIP(){
		return in_array($_SERVER['REMOTE_ADDR'], $this->allowedIP);
	}
	
	/**
	 * @param string $method	The original method name given by the Docebo API such as "user/profile"
	 * @param array $postParams
	 * @return array (for JSON encode)
	 */
	function retrieve($method='', $postParams=array()){
		if (empty($method)){
			if (!empty($_REQUEST['method']))
				$method = $_REQUEST['method'];
			else
				return $this->returnJsonError('Missing required "method" parameter');
		}
		return YnYCurl::call($method, $postParams);
	}
	
	/**
	 * @param string $message
	 * @return array
	 */
	function returnJsonError($message){
		return array(
			'success'	=> 0,
			'message'	=> $message
		);
	}
	
	function retrieveUserData(){
		if (empty($_REQUEST['id_user'])){
			header('HTTP/1.0 201 Mandatory input parameter is missing');
			exit;
		}
		$postParams = array(
			'id_user'	=> $_REQUEST['id_user'],
		);
		return $this->retrieve('user/profile', $postParams);
	}
	
	function retrieveLpDataForUser(){
		//Check arguments
		if (empty($_REQUEST['id_user']) || empty($_REQUEST['id_learningplan'])){
			header('HTTP/1.0 201 Mandatory input parameter is missing');
			exit;
		}
		$postParams = array(
			'id_user'         => $_REQUEST['id_user'],
			'id_learningplan' => $_REQUEST['id_learningplan']
		);
		return $this->retrieve('yny_learningplan/getEvaluationData', $postParams);
	}
}