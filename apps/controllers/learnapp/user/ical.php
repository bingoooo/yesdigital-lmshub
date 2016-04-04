<?php
namespace FragTale\Controller\Learnapp\User;
use FragTale\Controller\Learnapp\User;

/**
 * @author fabrice
 */
class Ical extends User{
	
	function initialize(){
		if ($this->_view->isMeta())
			parent::initialize();
	}
	
	function doPostBack(){
		//Nothing to code
	}
	
	function main(){
		if ($this->_view->isMeta()){
			try{
				$phpinputs = $this->getPHPInputs();
				if (empty($phpinputs['iCalEvents'])){
					$this->_view->json = $this->returnJsonError('Empty iCal Events');
					return false;
				}
				$filename	= md5($_SESSION['Learnapp']['id_user']);
				$filepath	= PUB_ROOT.'/icals/'.$filename.'.ics';
				$filecontent= $this->getBlock('learnapp/user/ical', array('iCalEvents'=>$phpinputs['iCalEvents']));
				if (file_put_contents($filepath, $filecontent))
					$this->_view->json = array('success' => true, 'ical' => WEB_ROOT.'/icals/'.$filename.'.ics');
				else
					$this->_view->json = array('success' => false, 'error' => 'ICS file creation: permission denied');
			}
			catch(Exception $ex){die('test');
				$this->exitOnError(500, 'Server error', array('Exception code '.$ex->getCode(), $ex->getMessage()));
			}
		}
	}
	
}