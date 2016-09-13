<?php
namespace FragTale\Controller;
use FragTale\Controller;

/**
 * @author fabrice
 */
class Yny_Reports extends Controller{
	
	/**
	 * @var array
	 */
	protected $allowedHosts = array(
			'127.0.0.1', 'localhost', '::1', 'ip6-localhost', 'fragbis', '192.168.2.95',
			'54.86.250.179', 'afoschi-etime-git.docebo.info',	//AFOSCHI LMS Sandbox
			'54.85.129.207',	//Seemed to be the e-time API server that calling the FER for the Sandbox
			'80.215.234.41', '89.225.245.6',	//Yes'n'You IP address
			'54.72.164.181', 'd-eu-smtpgw.docebopaas.com', 'www.yesnyoulearning.com', 'yesnyoulearning.com'	//Production YnY
	);
	
	/**
	 * In fact, you do not need to explicit hosts pointing to "default",
	 * because all host not in this array are "default".
	 * Let it for the examples
	 * @var array
	 */
	protected $mapHost2Instance = array(
			'127.0.0.1'		=>'default',
			'localhost'		=>'yny',
			'54.86.250.179'	=>'default',
			'afoschi-etime-git.docebo.info'	=> 'default',
			'54.85.129.207'	=>'default',
			'54.72.164.181'	=>'yny',		//prod
			'd-eu-smtpgw.docebopaas.com'	=>'yny',//prod
			'192.168.2.205'	=>'yny'
	);
	
	function initialize(){
		//if ($this->checkRestrictedHosts()){//First of all, check if the remote host is allowed to connect
			if (!empty($_SERVER['HTTP_ORIGIN']))
				header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
			/*elseif (!defined('ENV') || ENV!=='devel')
				$this->exitOnError(403, 'No HTTP ORIGIN sent by client');*/
		/*}
		else
			$this->exitOnError(403, 'Restricted Area');*/
	}
	
	function doPostBack(){
		
	}
	
	function main(){
		
	}
	
	function logAjaxRequest($addedmsg=''){
		$msg = $_SERVER['REMOTE_ADDR'].' | '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].(!empty($_SERVER['HTTP_USER_AGENT']) ? ' | '.$_SERVER['HTTP_USER_AGENT'] : '**No UA**');
		$completeMsg = date('Y-m-d H:i:s').' ** '.$msg.(!empty($addedmsg)? ' | '.$addedmsg : '');
		$logFile = DOC_ROOT.'/logs/log-'.__CLASS__.'-'.date('Ym').'.log';
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
		
		header('Access-Control-Allow-Origin: *');
		header("Content-type: application/json; charset=UTF-8");
		header('HTTP/1.0 '.$errcode.' '.$errmsg);
		die(json_encode($errs));
	}
	
	function checkRestrictedHosts(){
		if (defined('DEVEL') || (defined('ENV') && ENV==='devel'))
			return true;
		if (in_array($_SERVER['REMOTE_ADDR'], $this->allowedHosts))
			return true;
		if (in_array(gethostbyaddr($_SERVER['REMOTE_ADDR']), $this->allowedHosts))
			return true;
		$this->exitOnError(403, 'Forbidden for '.$_SERVER['REMOTE_ADDR'].' '.gethostbyaddr($_SERVER['REMOTE_ADDR']));
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
}