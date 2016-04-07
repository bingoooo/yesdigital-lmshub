<?php
namespace FragTale\Controller;
use FragTale\Controller;
use FragTale\YnY\Curl as YnYCurl;

/**
 * @desc This class will be inherited by all classes placed into the "yny_json" folder
 * @desc It is for instance dedicated to the micro webapps such as CCO, 360 etc.
 * @desc That will generate a basic JSON file containing the course and quiz structure
 * @desc And after that, giving possibility to retrieve the quiz' launch_url
 * @author fabrice
 */
class Provalliance_Json extends Controller{
	
	/**
	 * If string, it is only '*'.
	 * Else, you must set for example: array('https://yesnyou.com', 'https://api.yesnyou.com', ...)
	 * @var mixed array|string
	 */
	protected $allowedHosts = '*';//array();
	
	function initialize(){
		if ($origin = $this->checkRestrictedHosts()){//First of all, check if the remote host is allowed to connect
			$this->_view->headers['Access-Control-Allow-Origin']		= $origin;
			$this->_view->headers['Content-type']						= 'application/json; charset=UTF-8';
			$this->_view->headers['Access-Control-Allow-Credentials']	= 'true';
			$this->_view->headers['Access-Control-Allow-Headers']		= 'Content-Type';
		}
		if (!defined('ENV') || ENV!=='devel')
			$this->setLayout('json');	//On production environment, use JSON format
		else
			$this->setLayout('clean');	//On development environment, use HTML format to print or dump the result
		//Force set this view script for all inherited classes
		$this->_view->setCurrentScript(TPL_ROOT.'/views/provalliance_json.phtml');
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
	
	/**
	 * 
	 * @param int		$errcode			HTTP error code (such as 404, 403, 500 etc.)
	 * @param string	$errmsg				The main error message
	 * @param array		$additionalinfos	If you want to send more messages
	 */
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
		if ((defined('ENV') && ENV==='devel') || $this->allowedHosts==='*'){
			return '*';
		}
		//Only for AJAX request
		if ($origin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] :
			(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false)){
			if (in_array($origin, $this->allowedHosts))
				return $origin;
		}
		$this->exitOnError(403, 'Forbidden');
	}
	
	/**
	 * @param string	$method		(required) The original method name given by the Docebo API such as "user/profile"
	 * @param array		$postParams
	 * @return array (for JSON encode)
	 */
	function retrieve($method, $postParams=array()){
		return YnYCurl::call($method, $postParams, 'provalliance');
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
	
	/**
	 * This returns all the parameters posted or getted from the client into an associative array or into an object
	 * @param bool $assoc	If true, returns an associative array, if not, an object
	 * @return array|object
	 */
	function getPHPInputs($assoc=true){
		return json_decode(file_get_contents('php://input'), $assoc);
	}
	
	/**
	 * Get all the request data, including the PHP input if $takePHPInput is true
	 * @param boolean $takePost
	 * @param boolean $takeGet
	 * @param boolean $takePHPInput
	 * @return array
	 */
	function getRequestData($takePost=true, $takeGet=false, $takePHPInput=false){
		static $data;
		if (!is_array($data)){
			$data = array();
			if ($takePost)
				foreach ($_POST as $k=>$v)
					$data[$k]=$v;
			if ($takeGet)
				foreach ($_GET as $k=>$v)
					if ($k!=='my_current_view' && !isset($data[$k]))
						$data[$k] = $v;
			if ($takePHPInput)
				foreach ($this->getPHPInputs() as $k=>$v)
					if (!isset($data[$k]))
						$data[$k]=$v;
		}
		return $data;
	}
}