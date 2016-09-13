<?php
namespace FragTale\Controller\Provalliance_Json;
use FragTale\Controller\Provalliance_Json;

/**
 * @author fabrice
 */
class Launch_Url extends Provalliance_Json{
	
	protected $referer = 'PROVALLIANCE';
	
	function initialize(){
		if (!empty($_SERVER['HTTP_ORIGIN']))
			$this->referer = $_SERVER['HTTP_ORIGIN'];
		if (empty($_SESSION[$this->referer]['id_user'])){
			//This means that the id_user was previously passed (to the course_structure class)
			$this->exitOnError(400, 'Bad request');
		}
		parent::initialize();
	}
	
	function doPostBack(){
		$requestData = $this->getRequestData(true, (defined('ENV') && ENV==='devel'));
		if (empty($requestData['id_org'])){
			$this->exitOnError(417, 'Exceptation failed');
		}
		$params['id_org']	= (int)$requestData['id_org'];
		$params['id_user']	= (int)$_SESSION[$this->referer]['id_user'];
		$this->_view->json	= $this->retrieve('organization/play', $params);
	}
	
	function main(){
		//Nothing to code. Preventing parent behavior
	}
	
}