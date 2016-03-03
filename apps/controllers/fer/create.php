<?php
namespace FragTale\Controller\Fer;
use FragTale\Controller\Fer;

/**
 * @author fabrice
 */
class Create extends Fer{
	
	protected $expectedParameters = array(
			'id_user'			=> 1,
			'id_learningplan'	=> 1,
			'template_id'		=> 1,//ex.: lsat
			'template_version'	=> 0,//optional, latest version if not mentioned
			'report_mode'		=> 1,//"r"=>final HTML result (read), "w"=>the form inputs ("write")
			'report_format'		=> 1,//HTML ? pas compris
	);
	
	protected $jsonResponse = array(
			'success'			=> false,
			'template_id'		=> '',
			'template_version'	=> '',
			'hashcode'			=> '',
			'report'			=> '' //HTML content in base64
	);
	
	protected $postDone = false;
	
	/**
	 * As for Curl, the call to this page must be done in POST method (submission)
	 * {@inheritDoc}
	 * @see \FragTale\Controller\Fer::doPostBack()
	 */
	function doPostBack(){
		foreach ($this->expectedParameters as $pK=>$isRequired){
			if ($isRequired && empty($_POST[$pK])){
				header('HTTP/1.0 201 Mandatory input parameter is missing');
				exit;
			}
			$this->expectedParameters[$pK] = !empty($_POST[$pK]) ? $_POST[$pK] : null;
		}
		if (!in_array(strtolower($this->expectedParameters['report_mode']), array('r', 'w'))){
			header('HTTP/1.0 204 Invalid report_mode');
			exit;
		}
		
		$this->postDone = true;
	}
	
	function main(){
		if (!$this->postDone){
			header('HTTP/1.0 403 Forbidden');
			die('Forbidden');
		}
	}
	
}