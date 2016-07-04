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
		$this->setViewName('yny_reports/export/xlsx');
		$this->_view->data = null;
	}
	
	function main(){
		
	}
}