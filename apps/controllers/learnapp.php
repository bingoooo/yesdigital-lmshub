<?php
namespace FragTale\Controller;
use FragTale\Controller;
use FragTale\YnY\Curl as YnYCurl;

/**
 * @author fabrice
 */
class Learnapp extends Controller{
	
	/**
	 * @var array
	 */
	protected $allowedHosts = array(
			'http://127.0.0.1', 'http://localhost', 'http://fragbis',
			'http://127.0.0.1/', 'http://localhost/', 'http://fragbis/',
			'http://m.learnapp.fr', 'http://m.learnapp.fr/',
	);
	
	function initialize(){
		if ($this->checkRestrictedHosts()){//First of all, check if the remote host is allowed to connect
			header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
			header('Access-Control-Allow-Credentials: true');
		}
		if (!defined('ENV') || ENV!=='devel')
			$this->setLayout('json');
		else{
			$this->setLayout('clean');
		}
		//Force set this view script for all inherited classes
		$this->_view->setCurrentScript(TPL_ROOT.'/views/learnapp.phtml');
		$this->_view->json = array();
	}
	
	function main(){
		$this->exitOnError(403, 'Unauthorized request');
	}
	
	function logAjaxRequest($addedmsg=''){
		$msg = $_SERVER['REMOTE_ADDR'].' | '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].(!empty($_SERVER['HTTP_USER_AGENT']) ? ' | '.$_SERVER['HTTP_USER_AGENT'] : '**No UA**');
		$completeMsg = date('Y-m-d H:i:s').' ** '.$msg.(!empty($addedmsg)? ' | '.$addedmsg : '');
		$logFile = DOC_ROOT.'/logs/log-'.date('Ym').'.log';
		fputs(fopen($logFile, 'a+'), $completeMsg."\n");
	}
	
	function exitOnError($errcode, $errmsg, $additionalinfos=array()){
		$errs = array(
				'success'	=> '0',
				'code'		=> $errcode,
				'message'	=> $errmsg
		);
		if (!empty($additionalinfos))
			$errs['more info'] = $additionalinfos;
		
		$this->logAjaxRequest($errcode.' '.$errmsg);
		
		header("Content-type: application/json; charset=UTF-8");
		header('HTTP/1.0 '.$errcode.' '.$errmsg);
		die(json_encode($errs));
	}
	
	function checkRestrictedHosts(){
		if (!empty($_SERVER['HTTP_ORIGIN']) || !empty($_SERVER['HTTP_REFERER'])){
			$origin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] :
				!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
			if (in_array($origin, $this->allowedHosts))
				return true;
		}
		$this->exitOnError(403, 'Forbidden for '.$_SERVER['REMOTE_ADDR']);
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
		$instance = null;
		foreach (array(gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SERVER['REMOTE_ADDR']) as $remotehost){
			if (in_array($remotehost, $this->allowedHosts)){
				if (isset($this->mapHost2Instance[$remotehost])){
					$instance = $this->mapHost2Instance[$remotehost];
					break;
				}
			}
		}
		return YnYCurl::call($method, $postParams, $instance);
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
	
	function retrieveLpDataForUser(){
		//Check arguments
		if (empty($_REQUEST['id_user']) || empty($_REQUEST['id_learningplan'])){
			$this->exitOnError(201, 'Mandatory input parameter is missing');
		}
		$postParams = array(
			'id_user'         => $_REQUEST['id_user'],
			'id_learningplan' => $_REQUEST['id_learningplan']
		);
		return $this->retrieve('yny_learningplan/getEvaluationData', $postParams);
	}
}