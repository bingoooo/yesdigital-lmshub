<?php
namespace FragTale\Controller\Yny_Reports\Export;
use FragTale\Controller\Yny_Reports\Export;

/**
 * @author fabrice
 */
class Xlsx extends Export{
	
	protected $dbinstancename;
	
	function initialize(){
		parent::initialize();
		$this->dbinstancename = defined('DEVEL') ? 'ynytest' : 'ynynewlms';
		if (!empty($_REQUEST['instance'])) $this->dbinstancename = $_REQUEST['instance'];
	}
	
	function main(){
		
	}
}