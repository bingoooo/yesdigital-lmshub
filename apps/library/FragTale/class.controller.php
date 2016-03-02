<?php
namespace FragTale;
use FragTale\CMS\Parameters;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 * @desc Main class to be extended by all controllers.
 */
abstract class Controller{
	/**
	 * @var \FragTale\View
	 */
	protected $_view;
	/**
	 * @var \FragTale\View
	 */
	protected $_meta_view;
	/**
	 * @var \FragTale\CMS\Article
	 */
	protected $_article;
	/**
	 * @var \FragTale\CMS\Article
	 */
	protected $_meta_article;


	/**
	 * @desc Constructor (by default, nothing to do, no param to pass).
	 * @param View $view
	 */
	final function __construct(View $view){
		$this->_view = $view;
		$this->_article = $view->getArticle();
		$this->_meta_view = $view->getMetaView();
		$this->_meta_article = $view->getMetaView()->getArticle();
	}

	/**
	 * @desc To be overrided in each inherited class.
	 * Write in the specific codes for each page.
	 */
	protected function main(){}
	
	/**
	 * @desc Execute any post back procedures on form submission.
	 * This method comes after "initialize" and before "main".
	 * By default, the end of this method will redirect the page
	 * to self if it returns true.
	 * @return boolean
	 */
	protected function doPostBack(){return false;}
	
	/**
	 * Set any wishable variables. Use this function as a preprocess method.
	 */
	protected function initialize(){}
	
	/**
	 * @desc Executed in bootstrap to run the main controller's processes.
	 * By default, execution order is the following:
	 * 	Controller::declarations()
	 * 	Controller::doPostBack()	if returns true --> Controller::redirectToSelf()
	 * 	Controller::main()
	 * @final Not overridable.
	 */
	public function run(){
		$this->initialize();
		if (!empty($_POST) && $this->doPostBack() && (
				empty($_REQUEST['isAjax']) &&
				empty($_REQUEST['is_ajax']) &&
				empty($_REQUEST['ajax'])
				))
			$this->redirectToSelf();
		$this->main();
	}

	/**
	 * @desc Include one or all PHP files (recursively) from a given directory placed in the models' folder.
	 * @param string $model		File or directory placed in 'app/models'
	 */
	final public function loadModel($model=''){
		Application::requireFolder('apps/models/'.$model);
	}

	final public function catchError($msg, $class, $function, $line){
		Application::catchError($msg, $class, $function, $line);
	}
	
	########################## SETTERS ##############################
	/**
	* @desc Change the layout file name (without .phtml extension)
	* @param string $name
	*/
	final public function setLayout($name){
		$script = TPL_ROOT.'/pages/'.$name.'.layout.phtml';
		if (file_exists($script))
			$this->_meta_view->setLayoutScript($script);
	}

	########################## GETTERS ##############################
	/**
	* @desc The main HTML layout file name (without .phtml extension).
	* @return string
	*/
	final public function getLayout(){
		$script = $this->_meta_view->getLayoutScript();
		$name = str_replace(array(TPL_ROOT.'/pages/', '.layout.phtml', '/'), '', $script);
		return trim($name);
	}
	/**
	 * Get the default or the specified instance of DB adapter
	 * @param string $instance_name
	 * @return \FragTale\Db\Adapter
	 */
	final public function getDb($instance_name=DEFAULT_DATABASE_CONNECTOR_NAME){
		return \FragTale\Db\Adapter::getInstanceOf($instance_name);
	}
	########################## From View's methods ##################
	/**
	 * Get instance of the current User logged in
	 * @return \FragTale\CMS\User
	 */
	function getUser(){
		return $this->_view->getUser();
	}
	/**
	* Check if user is logged in
	* @return boolean
	*/
	function userIsLogged(){
		return $this->_view->userIsLogged();
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsAdmin(){
		return $this->_view->userIsAdmin();
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsSuperAdmin(){
		return $this->_view->userIsSuperAdmin();
	}
	/**
	 * Check if session user is at least front-end user
	 * @return boolean
	 */
	function userCanEditArticles(){
		return $this->_view->userCanEditArticles();
	}
	/**
	 * For user sign out, unset his/her session 
	 */
	function unsetUserSession(){
		unset($_SESSION['REG_USER']);
	}
	/**
	 * Add a new message to throw into the user interface.
	 * @param string $type
	 * @param string $msg
	 */
	function addUserEndMsg($type, $msg){
		return $this->_view->addUserEndMsg($type, $msg);
	}
	/**
	 * @desc Returns the HTML output of a controller/view include as a block into a parent controller/view
	 * @param string	$block_name	The block name placed in the "views" folder
	 * @param array		$vars		Array of vars that you can communicate to the block view
	 * @return string
	 */
	function getBlock($block_name, $vars=array()){
		return $this->_view->getBlock($block_name, $vars);
	}

	### GETTERS ###
	/**
	 * @return \FragTale\View
	*/
	function getMetaView(){
		return $this->_view->getMetaView();
	}
	/**
	 * @return \FragTale\View
	 */
	function getParentView(){
		return $this->_view->getParentView();
	}
	/**
	 * @return string: The current view name
	 */
	function getViewName(){
		return $this->_view->getViewName();
	}
	/**
	 * @return string: The full path of the selected "phtml" page layout file
	 */
	function getLayoutScript(){
		return $this->_view->getLayoutScript();
	}
	/**
	 * @return string: The Web page title
	 */
	function getTitle(){
		return $this->_view->getTitle();
	}
	/**
	 * @return \FragTale\CMS\Article
	 */
	function getArticle(){
		return $this->_article;
	}
	/**
	 * @return false: page not found
	 */
	function is404(){
		return $this->_view->is404();
	}

	### SETTERS ###
	/**
	 * Set the current view name
	* @param string $view_name
	*/
	function setViewName($view_name){
		$this->_view->setViewName($view_name);
	}
	/**
	 * Set the full path of the selected "phtml" page layout file
	 * @param string $fullpath
	 */
	function setLayoutScript($fullpath){
		$this->_view->setLayoutScript($fullpath);
	}
	/**
	 * Set new article object
	 * @param \FragTale\CMS\Article $article
	 */
	function setArticle(Article $article){
		$this->_article = $article;
	}
	/**
	 * Add a CSS source file
	 * @param string $fullpath
	 */
	function addCSS($fullpath){
		$this->_view->addCSS($fullpath);
	}
	/**
	 * Add a JS source file
	 * @param string $fullpath
	 */
	function addJS($fullpath){
		$this->_view->addJS($fullpath);
	}
	/**
	 * Set the Web page title
	 * @param string $title
	 */
	function setTitle($title){
		$this->_view->setTitle($title);
	}

	############ Tools	############
	/**
	 * Immediately redirect the web page.
	 * @param string $url
	 * @param string $anchor	Add an anchor to the URL
	 */
	function redirect($url, $anchor=null){
		if ($anchor){
			$sharpPos = strpos($url, '#');
			if ($sharpPos>=0)
				$url = substr($url, 0, $sharpPos);
			$url .= '#'.$anchor;
		}
		ob_end_clean();
		header('Location:'.$url);
		exit;
	}
	/**
	 * Immediately redirect the web page.
	 * @param string $anchor	Add an anchor to the URL
	 */
	function redirectToSelf($anchor=null){
		$url = HTTP_PROTOCOLE.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if ($anchor){
			$sharpPos = strpos($url, '#');
			if ($sharpPos>=0)
				$url = substr($url, 0, $sharpPos);
			$url .= '#'.$anchor;
		}
		$this->redirect($url);
	}
	/**
	 * Round float value into formated string
	 * @param float $number		Value to round
	 * @param int	$decimal	Nb of decimals after sep
	 * @param char	$sep		Decimal separator
	 * @return string
	 */
	function round($number, $decimal=2, $sep='.'){
		return $this->_view->round($number, $decimal, $sep);
	}
	/**
	 * Check e-mail validity
	 * @param string $email
	 * @return bool
	 */
	final public function check_email(&$email) {
		if($email = filter_var($email, FILTER_VALIDATE_EMAIL)){
			list($username,$domain)=explode('@',$email);
			return checkdnsrr($domain,'MX');
		}
		return false;
	}
	/**
	 * Macth permission of the current CMS article
	 * @return boolean
	 */
	function checkRules(){
		if (empty($this->_article->access))
			return true;
		$userRole = $this->getUser()->getStrongestRole();
		if (empty($userRole['rid']))
			return false;
		return $this->_article->access >= $userRole['rid'];
	}
	
	/**
	 * @desc Using PHPMailer
	 * @param string $to		email address(es). if multiple, should be separated by coma (,)
	 * @param string $from		email address
	 * @param string $subject
	 * @param string $message
	 * @param string $template	The email template name (since its a .phtml file, don't explicit the extension)
	 * @param string $bcc		email address(es). if multiple, should be separated by coma (,)
	 * @param array	 $values	Values to pass into the block taken as a mail template
	 * @return boolean
	 */
	function sendMail($to, $from, $subject, $message, $template=null, $bcc=null, array $values=null){
		static $smtpInfos;
		try{
			$Param	= new Parameters();
			//No more mail to send
			if ($Param->load("param_key='MAIL_NOMORE'") && (int)$Param->param_value===1){
				$this->addUserEndMsg('WARNING', _('You are trying to send an email, but your admin have set no email sending allowed.'));
				return true;
			}
			
			if (!class_exists('PHPMailer'))
				require_once LIB_ROOT.'/PHPMailer/PHPMailerAutoload.php';
			$Mail	= new \PHPMailer();
			
			if (empty($smtpInfos)){
				$smtpInfos['isSMTP'] = false;
				if ($Param->load("param_key='SMTP_HOST'") && !empty($Param->param_value)){
					$smtpInfos['isSMTP'] = true;
					$smtpInfos['host'] = $Param->param_value;
					if ($Param->load("param_key='SMTP_USERNAME'") && !empty($Param->param_value)){
						$smtpInfos['user'] = $Param->param_value;
						if ($Param->load("param_key='SMTP_PASSWORD'") && !empty($Param->param_value))
							$smtpInfos['password'] = $Param->param_value;
					}
					if ($Param->load("param_key='SMTP_PORT'") && !empty($Param->param_value))
						$smtpInfos['port'] = $Param->param_value;
				}
			}
			
			if ($smtpInfos['isSMTP']){
				$Mail->isSMTP();					// Set mailer to use SMTP
				$Mail->Host = $smtpInfos['host'];	// Specify main and backup SMTP servers
				if (!empty($smtpInfos['user'])){
					$Mail->SMTPAuth = true;					// Enable SMTP authentication
					$Mail->Username = $smtpInfos['user'];	// SMTP username
					if (!empty($smtpInfos['password']))
						$Mail->Password = $smtpInfos['password'];// SMTP password
					$Mail->SMTPSecure = 'tls';				// Enable TLS encryption, `ssl` also accepted
				}
				if (!empty($smtpInfos['port']))
					$Mail->Port = $Param->param_value;
			}
			else
				$Mail->isSendMail();// telling the class to use SendMail transport
			
			if (empty($from)){
				$Param->load("param_key='NOREPLY_EMAIL'");
				if (empty($Param->param_value))
					$Param->load("param_key='CONTACT_EMAIL'");
				if (empty($Param->param_value))
					$Param->load("param_key='ADMIN_EMAIL'");
				$from = $Param->param_value;
			}
			if (empty($from)){
				$from = 'noreply@'.$_SERVER['SERVER_NAME'];
			}
			
			if (strpos($from, '<')!==false){
				$froms = explode('<', str_replace('>', '', $from));
				$Mail->From		= trim($froms[1]);
				$Mail->FromName = trim($froms[0]);
			}
			else{
				$Mail->From = $from;
				$Mail->FromName = substr($from, 0, strpos($from, '@'));
			}
			if (strpos($to, ',')===false){
				if (strpos($to, '<')!==false){
					$tos 	= explode('<', str_replace('>', '', $to));
					$to		= trim($tos[1]);
					$toName = trim($tos[0]);
				}
				else{
					$toName = substr($to, 0, strpos($to, '@'));
				}
				$Mail->addAddress(trim($to));
			}
			else{
				foreach (explode(',', $to) as $subto){
					if (strpos($subto, '<')!==false){
						$subtos 	= explode('<', str_replace('>', '', $subto));
						$subto		= trim($subtos[1]);
						$subtoName	= trim($subtos[0]);
					}
					else{
						$subtoName = substr($subto, 0, strpos($subto, '@'));
					}
					$Mail->addAddress(trim($subto));
				}
			}
			if (!empty($bcc))
				$Mail->addBCC($bcc);
			$Mail->Subject = $subject;
			
			if (!empty($values['attachment'])){
				if (is_array($values['attachment'])){
					foreach ($values['attachment'] as $file){
						if (!file_exists($file)) continue;
						$Mail->addAttachment($file);
					}
				}
				elseif (file_exists($values['attachment'])){
					$Mail->addAttachment($values['attachment']);
				}
				$Mail->isHTML(true);
			}
			
			if ($template){
				$values['to']		= $to;
				$values['from']		= $from;
				$values['subject']	= $subject;
				$values['message']	= $message;
				$Mail->AltBody = strip_tags($message);
				$message = $this->getBlock($template, $values);
				$Mail->isHTML(true);
			}
			
			$message = trim($message);
			if (empty($message)) $message = _('Empty content');
			
			$Mail->Body = $message;
			$Mail->CharSet = 'UTF-8';
			
			if (!$Mail->send()){
				$this->addUserEndMsg('ERROR', _('Error while sending the email.'));
				//Log the msg into log file
				\FragTale\Application::catchError(
						_('Error while sending the email.').' | to:'.$to.' | from:'.$from.
						' | subject:'.$subject.' | error:'.$Mail->ErrorInfo,
						__CLASS__,
						__FUNCTION__,
						__LINE__
				);
				return false;
			}
			else
				return true;
		}
		catch(\Exception $exc){
			$this->addUserEndMsg('ERROR', _('System error:').' '.$exc->getMessage());
			//Log the msg into log file
			\FragTale\Application::catchError($exc->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
	}
	
	/**
	 * @todo Log mails
	 * @param string $from		E-mail
	 * @param string $subject	Subject
	 * @param string $message	Message can be in HTML format
	 * @param string $template	The mail template name (called as a block)
	 * @param array $values		Different values to pass into block or attachment
	 * @return boolean
	 */
	function sendMailToAdmin($from, $subject, $message, $template=null, array $values=null){
		$Param = new Parameters();
		if ($Param->load("param_key='ADMIN_EMAIL'") && !empty($Param->param_value)){
			return $this->sendMail($Param->param_value, $from, $subject, $message, $template);
		}
		return false;
	}
	/**
	 * @desc By default, if there is no "contact email" address, the "contact us" mails are posted to the admin mail box (if exists)
	 * @param string $from		E-mail
	 * @param string $subject	Subject
	 * @param string $message	Message can be in HTML format
	 * @param string $template	The mail template name (called as a block)
	 * @param array $values		Different values to pass into block or attachment
	 * @return boolean
	 */
	function sendMailToContact($from, $subject, $message, $template=null, array $values=null){
		$Param = new Parameters();
		if ($Param->load("param_key='CONTACT_EMAIL'") && !empty($Param->param_value)){
			return $this->sendMail($Param->param_value, $from, $subject, $message, $template, null, $values);
		}
		return $this->sendMailToAdmin($from, $subject, $message, $template, $values);
	}
}