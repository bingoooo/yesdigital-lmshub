<?php
namespace FragTale\Controller;
use FragTale\Controller;
use FragTale\YnY\Curl as YnYCurl;

/**
 * @author fabrice
 */
class Docebo extends Controller{
	
	/**
	 * @var array
	 */
	protected $allowedHosts = array(
			'127.0.0.1', 'localhost', '::1', 'ip6-localhost',
			'54.86.250.179', 'afoschi-etime-git.docebo.info',	//AFOSCHI LMS Sandbox
			'54.85.129.207',	//Seemed to be the e-time API server that calling the FER for the Sandbox
			'80.215.234.41',	//Yes'n'You IP address
	);
	
	/**
	 * In fact, you do not need to explicit hosts pointing to "default",
	 * because all host not in this array are "default".
	 * Let it for the examples
	 * @var array
	 */
	protected $mapHost2Instance = array(
			'127.0.0.1'		=>'default',
			'localhost'		=>'default',
			'54.86.250.179'	=>'default',
			'afoschi-etime-git.docebo.info'	=> 'default',
			'54.85.129.207'	=>'default',
	);
	
	function initialize(){
		$this->checkRestrictedHosts();//First of all, check if the remote host is allowed to connect
		$this->setLayout('json');
		$this->_view->json = array();
	}
	
	function doPostBack(){
		
	}
	
	function main(){
		$this->_view->json = $this->retrieve();
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
		if (defined('ENV') && ENV==='devel')
			return true;
		if (in_array($_SERVER['REMOTE_ADDR'], $this->allowedHosts))
			return true;
		if (in_array(gethostbyaddr($_SERVER['REMOTE_ADDR']), $this->allowedHosts))
			return true;
		$this->exitOnError(403, 'Forbidden for '.$_SERVER['REMOTE_ADDR'].' '.gethostbyaddr($_SERVER['REMOTE_ADDR']));
	}
	
	/**
	 * @param string $method	The original method name given by the Docebo API such as "user/profile"
	 * @param array $postParams
	 * @return array (for JSON encode)
	 */
	function retrieve($instance='', $method='', $postParams=array()){
		if (empty($method)){
			if (!empty($_REQUEST['method']))
				$method = $_REQUEST['method'];
			else
				return $this->returnJsonError('Missing required "method" parameter');
		}
		if (empty($instance)){
			if (!empty($_REQUEST['instance']))
				$instance = $_REQUEST['instance'];
			else
				foreach (array(gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SERVER['REMOTE_ADDR']) as $remotehost){
					if (in_array($remotehost, $this->allowedHosts)){
						if (isset($this->mapHost2Instance[$remotehost])){
							$instance = $this->mapHost2Instance[$remotehost];
							break;
						}
					}
				}
		}
		if (empty($postParams)){
			foreach ($_REQUEST as $key=>$value){
				if (!in_array($key, array('instance', 'my_current_view', 'method')))
					$postParams[$key] = $value;
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
			$this->exitOnError(201, 'Mandatory input parameter is missing');
		}
		$postParams = array(
			'id_user'         => $_REQUEST['id_user'],
			'id_learningplan' => $_REQUEST['id_learningplan']
		);
		return $this->retrieve('yny_learningplan/getEvaluationData', $postParams);
	}
}