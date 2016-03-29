<?php
namespace FragTale;
use FragTale\CMS\User;
use FragTale\CMS\Article;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class View{
	/**
	 * @var string
	 */
	protected $_my_current_view;
	/**
	 * @var string
	 */
	protected $_current_script;
	/**
	 * @var string
	 */
	protected static $_layout_script;
	/**
	 * @var string
	 */
	protected static $_title;
	/**
	 * @var array of strings
	 */
	static protected $_css= array();
	/**
	 * @var array of strings
	 */
	static protected $_js = array();
	/**
	 * @var boolean
	 */
	protected $_is404	 = false;
	/**
	 * @var boolean
	 */
	protected $_isMeta	 = false;
	/**
	 * The very first instance of View, globally scoped, instanciated in index.php
	 * @var \FragTale\View
	 */
	protected static $_metaView;
	/**
	 * If this View is instanciated by another View (method "getBlock"), then the calling View is the parent
	 * @var \FragTale\View
	 */
	protected $_parentView;
	/**
	 * Instance of Article (for CMS page)
	 * @var \FragTale\CMS\Article
	 */
	protected $_article;
	
	/**
	 * HTML output to render
	 * @var string
	 */
	protected $_render;
	
	/**
	 * @desc This object stores all the usefull informations to render the Web page.
	 * The controller can set new properties intending to display specific informations.
	 * @param string $view_name
	 */
	function __construct($view_name=null){
		# Instanciate the article associated to the view
		if (class_exists('Article'))
			$this->_article = new Article();
		if (!is_a(self::$_metaView, __CLASS__)){
			## Default meta view settings:
			$this->_isMeta = true;
			self::$_metaView = $this;
			
			$ini_params = Application::getIniParams();
			
			if (defined('DEFAULT_PAGE_LAYOUT')){
				$this->setLayoutScript(TPL_ROOT.'/pages/'.DEFAULT_PAGE_LAYOUT.'.layout.phtml');
			}
			if (defined('DEFAULT_PAGE_TITLE')){
				$this->setTitle(DEFAULT_PAGE_TITLE);
			}
			if (!empty($ini_params['default_css_files'])){
				foreach ($ini_params['default_css_files'] as $script){
					$script = trim($script);
					if (empty($script)) continue;
					$this->addCSS(WEB_ROOT.'/css/'.$script);
				}
			}
			if (!empty($ini_params['default_js_files'])){
				foreach ($ini_params['default_js_files'] as $script){
					$script = trim($script);
					if (empty($script)) continue;
					$this->addJS(WEB_ROOT.'/js/'.$script);
				}
			}
			$this->_my_current_view = !empty($_GET['my_current_view']) ? trim(trim($_GET['my_current_view']), '/') : '';
			if (!$this->_my_current_view){
				$this->_my_current_view = defined('LANDING_PAGE') ? LANDING_PAGE : 'home';
			}
			if (!$this->isCmsPage()){
				$view_name = $this->_my_current_view;
			}
		}
		if (is_string($view_name)){
			$this->setViewName($view_name);
			$this->setCurrentScript(TPL_ROOT.'/views/'.$view_name.'.phtml');
		}
	}
	
	/**
	 * Run its controller if exists
	 */
	protected function runController(){
		if (file_exists(APP_ROOT.'/controllers/'.$this->getViewName().'.php')){
			Application::requireControllers($this->getViewName());
			$viewName = $this->getViewName()=='cms/default' ? 'cms/_Default' : $this->getViewName();
			$className = '\\FragTale\\Controller\\'.str_replace(array(' ', '-'), '_', str_replace('/', '\\', $viewName));
			$controller = new $className($this);
			$controller->run();
		}
	}
	
	/**
	 * Render the web page
	 */
	function render(){
		try{
			ob_start();
			$view = $this;
			$this->runController();
			include $this->getCurrentScript();
			$this->_render = ob_get_clean();
			if ($this->_isMeta){
				# If this view is the "meta" view (the very first instance of View declared in index.php)
				# we will also load the layout
				if ($view->is404()){
					header('HTTP/1.0 404 Not Found');
					header('Status: 404 Not Found');
				}
				require_once $this->getLayoutScript();
			}
			else
				# For any else cases, this view is a block, so we return the output as string value
				return $this->_render;
		}
		catch (\Exception $e){
			if (defined('ENV') && ENV=='devel') return $e->getMessage();
		}
		return '';
	}
	
	/**
	 * Returns the HTML output of a controller/view include as a block into a parent controller/view
	 * @param string	$block_name	The block name placed in the "views" folder
	 * @param array		$vars		Array of vars that you can communicate to the block view
	 * @return string
	 */
	function getBlock($block_name, $vars=array()){
		$block_name = trim($block_name, '/');
		$view = new View($block_name);
		$view->_parentView = $this;
		if (!empty($vars) && is_array($vars)){
			foreach ($vars as $k=>$v){
				$view->$k = $v;
			}
		}
		return $view->render();
	}
	
	### GETTERS ###
	/**
	 * @return \FragTale\View
	 */
	function getMetaView(){
		return self::$_metaView;
	}
	/**
	 * 
	 * @return \FragTale\View
	 */
	function getParentView(){
		return $this->_parentView;
	}
	/**
	 * @return string: The current view name
	 */
	function getViewName(){
		return $this->_my_current_view;
	}
	/**
	 * @return string: The full path of the current "phtml" view script
	 */
	function getCurrentScript(){
		if (!empty($this->_current_script) && file_exists($this->_current_script))
			return $this->_current_script;
		else{
			$this->_is404 = true;
			return PAGE_404;
		}
	}
	/**
	 * @return string: The full path of the selected "phtml" page layout file
	 */
	function getLayoutScript(){
		if (empty(self::$_layout_script))
			if (defined('DEFAULT_PAGE_LAYOUT'))
				return TPL_ROOT.'/pages/'.DEFAULT_PAGE_LAYOUT.'.layout.phtml';
			else
				return DEFAULT_LAYOUT;
		return self::$_layout_script;
	}
	/**
	 * @return string: HTML tag output giving CSS source files to include
	 */
	function getCssTags(){
		$tags = '';
		foreach (self::$_css as $k=>$script){
			$tags .= '<link rel="stylesheet" type="text/css" href="'.$script.'" />';
			unset(self::$_css[$k]);
		}
		return $tags;
	}
	/**
	 * @return string: HTML tag output giving JS source files to include
	 */
	function getJsTags(){
		$tags = '';
		foreach (self::$_js as $k=>$script){
			$tags .= '<script type="text/javascript" src="'.$script.'"></script>';
			unset(self::$_js[$k]);
		}
		return $tags;
	}
	/**
	 * @return string: The Web page title
	 */
	function getTitle(){
		return self::$_title;
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
		return $this->_is404;
	}
	/**
	 * @return bool
	 */
	function isMeta(){
		return $this->_isMeta;
	}
	
	### SETTERS ###
	/**
	 * Set the current view name
	 * @param string $view_name
	 */
	function setViewName($view_name){
		$this->_my_current_view = $view_name;
	}
	/**
	 * Set the full path of the current "phtml" view script. If the script doesn't exist, quote that is a 404 page.
	 * @param string $fullpath
	 */
	function setCurrentScript($fullpath){
		if (file_exists($fullpath)){
			$this->_current_script = $fullpath;
			$this->_is404 = false;
		}
		else
			$this->_is404 = true;
	}
	/**
	 * Set the full path of the selected "phtml" page layout file
	 * @param string $fullpath
	 */
	function setLayoutScript($fullpath){
		self::$_layout_script = $fullpath;
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
		$fullpath = trim($fullpath);
		if (empty($fullpath)) return;
		if (!in_array($fullpath, self::$_css))
			self::$_css[] = $fullpath;
	}
	/**
	 * Add a JS source file
	 * @param string $fullpath
	 */
	function addJS($fullpath){
		$fullpath = trim($fullpath);
		if (empty($fullpath)) return;
		if (!in_array($fullpath, self::$_js))
			self::$_js[] = $fullpath;
	}
	/**
	 * Set the Web page title
	 * @param string $title
	 */
	function setTitle($title){
		self::$_title = $title;
	}
	
	### Tools
	/**
	 * HTML output. The view returns the rendering output.
	 * @return string
	 */
	function __toString(){
		if (empty($this->_render)){
			return $this->render();
		}
		return $this->_render;
	}
	/**
	 * Round float value into formated string
	 * @param float $number		Value to round
	 * @param int	$decimal	Nb of decimals after sep
	 * @param char	$sep		Decimal separator
	 * @return string
	 */
	function round($number, $decimal=2, $sep='.'){
		return str_replace('.', $sep, number_format((float)$number, $decimal));
	}
	
	### CMS
	/**
	 * This function is launched in "$this->setCurrentScript()" function to determine if the current page is a CMS page.
	 * @return boolean
	 */
	function isCmsPage(){
		if (!defined('DEFAULT_CMS_DATABASE_CONNECTOR_NAME') || strtolower(DEFAULT_CMS_DATABASE_CONNECTOR_NAME)==='none')
			return false;
		if (!class_exists('Article'))
			return false;
		$this->_article->load("request_uri='$this->_my_current_view'");
		if (!empty($this->_article->aid)){
			$viewScript = APP_ROOT.'/templates/views/cms/'.$this->_article->view.'.phtml';
			if (!file_exists($viewScript)){
				$this->setViewName('cms/default');
				$this->setCurrentScript(APP_ROOT.'/templates/views/cms/default.phtml');
			}
			else{
				$this->setViewName('cms/'.$this->_article->view);
				$this->setCurrentScript($viewScript);
			}
			return true;
		}
		$this->_current_script = PAGE_404;
		return false;
	}
	
	/**
	 * Add a new message to throw into the user interface.
	 * @param string $type
	 * @param string $msg
	 */
	function addUserEndMsg($type, $msg){
		$type = strtoupper($type);
		if (in_array($type, array('ERROR', 'WARNING')))
			$type.='S';
		if (!in_array($type, array('ERRORS', 'SUCCESS', 'WARNINGS'))){
			$msg = _('Attempt to store the following message in an unknown type').' "'.$type.'" : "'.$msg.'".';
			$_SESSION['USER_END_MSGS']['ERRORS'][] = $msg;
			return;
		}
		if (empty($_SESSION['USER_END_MSGS'][$type]) || !in_array($msg, $_SESSION['USER_END_MSGS'][$type])){
			$_SESSION['USER_END_MSGS'][$type][] = $msg;
		}
	}
	/**
	 * Display all stored user end messages.
	 * @return string
	 */
	function getUserEndMsgs(){
		$output = '';
		if (!empty($_SESSION['USER_END_MSGS']) && is_array($_SESSION['USER_END_MSGS'])){
			if (!empty($_SESSION['USER_END_MSGS']['ERRORS']) && is_array($_SESSION['USER_END_MSGS']['ERRORS'])){
				$output .= '<div class="user_end_error">';
				foreach ($_SESSION['USER_END_MSGS']['ERRORS'] as $msg){
					Application::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
					$output .= '<p>'.$msg.'</p>';
				}
				$output .= '</div>';
			}
			if (!empty($_SESSION['USER_END_MSGS']['SUCCESS']) && is_array($_SESSION['USER_END_MSGS']['SUCCESS'])){
				$output .= '<div class="user_end_success">';
				foreach ($_SESSION['USER_END_MSGS']['SUCCESS'] as $msg){
					$output .= '<p>'.$msg.'</p>';
				}
				$output .= '</div>';
			}
			if (!empty($_SESSION['USER_END_MSGS']['WARNINGS']) && is_array($_SESSION['USER_END_MSGS']['WARNINGS'])){
				$output .= '<div class="user_end_warning">';
				foreach ($_SESSION['USER_END_MSGS']['WARNINGS'] as $msg){
					$output .= '<p>'.$msg.'</p>';
				}
				$output .= '</div>';
			}
		}
		unset($_SESSION['USER_END_MSGS']);
		return $output;
	}
	
	/**
	 * Get instance of the registered and logged user
	 * @return \FragTale\CMS\User
	 */
	function getUser(){
		static $user;
		if (!is_a($user, '\FragTale\CMS\User')){
			$user = new User();
			if ($this->userIsLogged()){
				$user->load('uid='.$_SESSION['REG_USER']['uid']);
			}
		}
		return $user;
	}
	
	/**
	 * Check if user is logged in
	 * @return boolean
	 */
	function userIsLogged(){
		return !empty($_SESSION['REG_USER']['uid']);
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsAdmin(){
		if (!$this->userIsLogged()) return false;
		$role = $this->getUser()->getStrongestRole();
		if (empty($role['rid']) || $role['rid'] > 2) return false;
		return true;
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsSuperAdmin(){
		if (!$this->userIsLogged()) return false;
		$role = $this->getUser()->getStrongestRole();
		if (empty($role['rid']) || $role['rid'] > 1) return false;
		return true;
	}
	/**
	 * Check if session user is at least front-end user
	 * @return boolean
	 */
	function userCanEditArticles(){
		if (!$this->userIsLogged()) return false;
		$role = $this->getUser()->getStrongestRole();
		if (empty($role['rid']) || $role['rid'] > 3) return false;
		return true;
	}
	/**
	 * Attention! This will clear the HTML output
	 */
	function unsetRender(){
		unset($this->_render);
		foreach ($this as $key=>$object){
			if (is_a($object, '\\FragTale\\Db\\Table')){
				$this->$key->clearDb();
			}
		}
	}
}