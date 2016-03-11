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
	
	function initialize(){
		$this->checkRestrictedHosts();//First of all, check if the remote host is allowed to connect
		$this->logAjaxRequest();
		
		if (!empty($_REQUEST['layout']))
			$this->setLayout(strtolower($_REQUEST['layout']));
		else
			$this->setLayout('json');
		$this->_view->json = array();
	}
	
	/**
	 * As for Curl, the call to this page must be done in POST method (submission).
	 * {@inheritDoc}
	 * @see \FragTale\Controller\Fer::doPostBack()
	 */
	function doPostBack(){
		//if (!$this->processing()) //do some stuff
	}
	
	function main(){
		if (!$_REQUEST){
			header('HTTP/1.0 403 Forbidden');
			die('Forbidden');
		}
		if (!$this->processing()){
			//do some stuff
		}
	}
	
	function processing(){
		foreach ($this->expectedParameters as $pK=>$isRequired){
			if ($isRequired && empty($_REQUEST[$pK])){
				header('HTTP/1.0 201 Mandatory input parameter is missing');
				die('Mandatory input parameter is missing');
			}
			$this->expectedParameters[$pK] = !empty($_REQUEST[$pK]) ? $_REQUEST[$pK] : null;
		}
		if (!in_array(strtolower($this->expectedParameters['report_mode']), array('r', 'w'))){
			header('HTTP/1.0 204 Invalid report_mode');
			die('Invalid report_mode');
		}
		
		//On creation, we get the current template version
		$tplId		= strtolower(trim($this->expectedParameters['template_id']));
		$tplVersion = $this->expectedParameters['template_version'];
		try{
			if (!empty($tplVersion)){
				//Match first if the template version exists
				if (!file_exists(APP_ROOT.'/templates/views/fer/tpl/versions/'.$tplId.'/'.$tplVersion.'.phtml')){
					header('HTTP/1.0 203 Invalid combination template id / template version');
					die('Invalid combination template id / template version');
				}
				else{
					$data = $this->getBlock('fer/tpl/versions/'.$tplId.'/'.$tplVersion, array('RpData'=>$this->retrieveLpDataForUser()));//Get learning plan
					$this->jsonResponse['report'] = $this->specialEncode($data);
				}
			}
			else{
				if (!file_exists(APP_ROOT.'/templates/views/fer/tpl/current/'.$tplId.'.phtml')){
					header('HTTP/1.0 203 Invalid combination template id / template version');
					die('Invalid combination template id / template version');
				}
				else{
					$data = $this->getBlock('fer/tpl/current/'.$tplId, array('RpData'=>$this->retrieveLpDataForUser()));//Get learning plan
					$this->jsonResponse['report'] = $this->specialEncode($data);
				}
			}
		}
		catch(\Exception $exc){
			$this->logAjaxRequest('Exception: '.$exc->getMessage());
			return false;
		}
		$this->jsonResponse['template_id']		= $tplId;
		$this->jsonResponse['template_version'] = $this->getMetaView()->_tpl_version;
		$this->jsonResponse['hashcode']			= !empty($LP['hashcode']) ? $LP['hashcode'] : null;
		$this->jsonResponse['success']			= 1;
		$this->_view->json = $this->jsonResponse;
		return true;
	}
	
	/**
	 * Remove tabs spaces. Then, base64_encoding
	 * @param string $html
	 * @return string
	 */
	function specialEncode($html){
		$html = str_replace('
	', '', $html);
		$html = str_replace('	', '', $html);
		return base64_encode($html);
	}
}