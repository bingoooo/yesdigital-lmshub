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
	
	function initialize(){
		if (!empty($_REQUEST['layout']))
			$this->setLayout(strtolower($_REQUEST['layout']));
		else
			$this->setLayout('json');
		$this->_view->json = array();
	}
	
	/**
	 * As for Curl, the call to this page must be done in POST method (submission)
	 * {@inheritDoc}
	 * @see \FragTale\Controller\Fer::doPostBack()
	 */
	function doPostBack(){
		//return $this->processing();
	}
	
	function main(){
		if ($this->processing()){
			if (!$this->postDone){
				header('HTTP/1.0 403 Forbidden');
				die('Forbidden');
			}
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
		//Get user info
		$User = $this->retrieveUserData();
		if (empty($User['success'])){
			$this->_view->json = $this->returnJsonError('Unknown User');
			return false;
		}
		//Get learning plan
		$LP	= $this->retrieveLpDataForUser();
		if (empty($LP['hashcode'])){
			$this->_view->json = $LP;
			$this->_view->json['message'] = 'Unabled to retrieve LP Data for given id_user and id_learningplan';
			return false;
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
					$data = $this->getBlock('fer/tpl/versions/'.$tplId.'/'.$tplVersion, array('User'=>$User, 'LP'=>$LP));
					$this->jsonResponse['report'] = base64_encode($data);
				}
			}
			else{
				if (!file_exists(APP_ROOT.'/templates/views/fer/tpl/current/'.$tplId.'.phtml')){
					header('HTTP/1.0 203 Invalid combination template id / template version');
					die('Invalid combination template id / template version');
				}
				else{
					$data = $this->getBlock('fer/tpl/current/'.$tplId, array('User'=>$User, 'LP'=>$LP));
					$this->jsonResponse['report'] = base64_encode($data);
				}
			}
		}
		catch(\Exception $exc){
			print_r($exc);
			print_r($this->expectedParameters);
		}
		$this->jsonResponse['template_id']		= $tplId;
		$this->jsonResponse['template_version'] = str_replace($tplId.'/', '', $this->getMetaView()->_tpl_version);
		$this->jsonResponse['hashcode']			= $LP['hashcode'];
		$this->jsonResponse['success']			= 1;
		$this->_view->json = $this->jsonResponse;
		$this->postDone = true;
		return true;
	}
	
}