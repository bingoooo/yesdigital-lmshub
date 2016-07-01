<?php
namespace FragTale\Controller\Yny_Reports;
use FragTale\Controller\Yny_Reports;

/**
 * @author fabrice
 */
class Export extends Yny_Reports{
	
	function initialize(){
		parent::initialize();
		$this->setLayout('clean');
	}
	
}