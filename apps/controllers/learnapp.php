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
		'http://m.learnapp.fr', 'https://m.learnapp.fr',
		'http://m.learnapp.fr/', 'https://m.learnapp.fr/',
	);
	
	/**
	 * @var array
	 */
	protected $develHosts = array(
			'http://127.0.0.1', 'http://localhost', 'http://fragbis',
			'http://127.0.0.1/', 'http://localhost/', 'http://fragbis/',
	);
	
	function initialize(){
		if ($this->checkRestrictedHosts()){//First of all, check if the remote host is allowed to connect
			$origin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] :
				(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '*');
			header('Access-Control-Allow-Origin: '.$origin);
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
			//header('P3P: CP="NON DSP LAW CUR ADM DEV TAI PSA PSD HIS OUR DEL IND UNI PUR COM NAV INT DEM CNT STA POL HEA PRE LOC IVD SAM IVA OTC"');
			header('Access-Control-Allow-Headers: Origin,X-Requested-With,Content-Type,Accept,Authorization,Accept-Language,Content-Language,Last-Event-ID,X-HTTP-Method-Override');
			header('Access-Control-Max-Age: 0');
			header('Cache-Control: max-age=0');
		}
		if (!defined('ENV') || ENV!=='devel')
			$this->setLayout('json');	//On production environment, use JSON format
		else{
			$this->setLayout('clean');	//On development environment, use HTML format to print or dump the result
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
		
		$origin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] :
			(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '*');
		header('Access-Control-Allow-Origin: '.$origin);
		header("Content-type: application/json; charset=UTF-8");
		header('HTTP/1.0 '.$errcode.' '.$errmsg);
		die(json_encode($errs));
	}
	
	function checkRestrictedHosts(){
		$origin = !empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] :
			(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['HTTP_HOST']);
		//First, check the permission in case of devel or sandbox environment
		if (stripos($origin, 'apisdbx')!==false || stripos($origin, 'localhost')!==false ||
			(defined('ENV') && ENV==='devel')
		){
			if (in_array($origin, $this->develHosts))
				return true;
		}
		//2nd, check on production env
		if (!empty($_SERVER['HTTP_ORIGIN']) || !empty($_SERVER['HTTP_REFERER'])){
			if (in_array($origin, $this->allowedHosts))
				return true;
		}
		$this->exitOnError(403, 'Forbidden for '.$origin);
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
}