<?php
namespace FragTale;
/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 * @desc Getting the application params
 */
class Application{
	
	static protected $_ini;
	static protected $_errors;
	
	static function loadIniParams(){
		$settings_path =  DOC_ROOT.'/settings';
		$settings_paths['app']		= $settings_path.'/application';
		$settings_paths['locales']	= $settings_paths['app'].'/locales';
		$settings_paths['defaults'] = array(
			'backend' => $settings_paths['app'].'/default/backend.ini',
			'frontend'=> $settings_paths['app'].'/default/frontend.ini',
		);
		
		### Begin to load the default application ini files and then, define if its backend or frontend
		## Ini filenames to load match the requested URL
		$final_uri = str_replace('www.', '', trim($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], '/'));
		$final_uri = str_replace(array(':'), '.', $final_uri);
		if (strpos($final_uri, str_replace(array(HTTP_PROTOCOLE, '://', 'www.'), '', ADMIN_WEB_ROOT))!==false &&
			file_exists($settings_paths['defaults']['backend'])){
			self::setIniParams(parse_ini_file($settings_paths['defaults']['backend']));
		}
		elseif (file_exists($settings_paths['defaults']['frontend']))
			self::setIniParams(parse_ini_file($settings_paths['defaults']['frontend']));
		
		### We will check in locales ini files if we have some parameters to override from default
		$ini2check = explode('/', $final_uri);
		$path = '';
		foreach ($ini2check as $partname){
			$partname = trim($partname);
			if (empty($partname)) continue;
			if (empty($path))
				$path = $partname;
			else
				$path .= '.'.$partname;
			$fullpathname = $settings_paths['locales'].'/'.$path.'.ini';
			if (file_exists($fullpathname))
				self::setIniParams(parse_ini_file($fullpathname));
		}
		
		### Set the parameters into constants
		foreach (self::$_ini as $key=>$value){
			if (!is_array($value))
				define(strtoupper($key), $value);
		}
		
		## In case of forcing redirection to specified URL
		if (defined('BASE_URL') && WEB_ROOT!=BASE_URL){
			$redirect = trim(BASE_URL, '/');
			$baseUri = trim(str_replace(array('http://'.$_SERVER['SERVER_NAME'], 'https://'.$_SERVER['SERVER_NAME']), '', WEB_ROOT), '/');
			if (!empty($_SERVER['REQUEST_URI']))
				$redirect.= '/'.trim(str_replace($baseUri, '', $_SERVER['REQUEST_URI']), '/');
			header('Location:'.$redirect);
			exit;
		}
		
		## In case of forcing redirection to HTTPS
		if (defined('FORCE_HTTPS') && (int)FORCE_HTTPS===1 && HTTP_PROTOCOLE=='http'){
			$redirect = str_replace('http://', 'https://', WEB_ROOT);
			if (!empty($_SERVER['REQUEST_URI']))
				$redirect.= '/'.trim($_SERVER['REQUEST_URI'], '/');
			header('Location:'.$redirect);
			exit;
		}
		
		### Defining development environment
		if (defined('DEVEL') && DEVEL==1){
			define('ENV', 'devel');
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		}
		
		### Any declared php.ini values to override
		if (!empty(self::$_ini['php.ini'])){
			foreach (self::$_ini['php.ini'] as $key=>$value)
				ini_set($key, $value);
		}
		
		### Define default database connector instance name
		if (defined('DEFAULT_DATABASE_CONNECTOR_NAME') && !defined('DEFAULT_CMS_DATABASE_CONNECTOR_NAME'))
			define('DEFAULT_CMS_DATABASE_CONNECTOR_NAME', DEFAULT_DATABASE_CONNECTOR_NAME);
		if (!defined('DEFAULT_DATABASE_CONNECTOR_NAME')){
			if (defined('DEFAULT_CMS_DATABASE_CONNECTOR_NAME'))
				define('DEFAULT_DATABASE_CONNECTOR_NAME', DEFAULT_CMS_DATABASE_CONNECTOR_NAME);
			else
				define('DEFAULT_DATABASE_CONNECTOR_NAME', 'default');
		}
		
		### Define default landing page
		if (!defined('LANDING_PAGE'))
			define('LANDING_PAGE', 'home');
		
		### Set locale (it can be set into ini files, but don't set this constant into ini files if you want to use cookies for language settings)
		if (!defined('LOCALE')){
			//As default, we set the language of the native country of this framework
			define('LOCALE', 'fr_FR');
		}
		### Locale PO files for translations with GETTEXT, assuming gettext PHP extension is installed
		$locale = LOCALE.'.utf8';
		if (isset($_COOKIE['LOCALE']))
			$locale = $_COOKIE['LOCALE'].'.utf8';
		elseif (isset($_SESSION['LOCALE']))
			$locale = $_SESSION['LOCALE'].'.utf8';
		putenv("LC_ALL=$locale");
		setlocale(LC_ALL, $locale);
		bindtextdomain('messages', DOC_ROOT.'/locale');
		textdomain('messages');
		
		# Include system library
		self::requireFolder(LIB_ROOT.'/FragTale');
		if (!empty(self::$_ini['models'])){
			foreach (self::$_ini['models'] as $model)
				self::requireFolder(APP_ROOT.'/models/'.$model);
		}
	}
	
	/**
	 * Set the application ini params
	 */
	final static private function setIniParams($params){
		foreach ($params as $key=>$value){
			self::$_ini[$key] = $value;
		}
	}
	
	/**
	 * Get the application ini params
	 */
	static function getIniParams(){
		return self::$_ini;
	}
	
	/**
	 * Manage
	 * @param string	$msg
	 * @param string	$class
	 * @param string	$function
	 * @param int		$line
	 */
	static function catchError($msg, $class, $function, $line){
		$completeMsg = date('Y-m-d H:i:s').' ** '.$class.'::'.$function.'() in line '.$line.' ** '.$msg;
		$logFile = DOC_ROOT.'/logs/error'.date('Ym').'.log';
		self::$_errors[] = $completeMsg;
		fputs(fopen($logFile, 'a+'), $completeMsg."\n");
	}
	
	/**
	 * @return array
	 */
	static function getErrors(){
		return self::$_errors;
	}
	
	/**
	 * Scan a folder (and subs if in recursive mode) and include once all defined required PHP files.
	 * @param string	$folder
	 * @param bool		$recursively
	 */
	static function requireFolder($folder, $recursively=true){
		$dir = $folder;
		if (!file_exists($dir))
			$dir = DOC_ROOT.'/'.$folder;
		if (!file_exists($dir))
			$dir = APP_ROOT.'/'.$folder;
		if (!file_exists($dir)){
			self::catchError('Folder "'.$folder.'" not found.', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		$handle = opendir($dir);
		$dirs = array();
		while ($content = readdir($handle)) {
			if (!in_array($content, array('.', '..', '.svn'))){
				$path = $folder.'/'.$content;
				if (is_dir($path) && $recursively)
					$dirs[] = $path;
				elseif (file_exists($path) && substr($path, -4) == '.php')
					require_once $path;
			}
		}
		closedir($handle);
		
		if ($recursively)
		foreach ($dirs as $dir)
			self::requireFolder($dir);
	}
	
	/**
	 * Include the controller called by its view and all its possible parent controllers
	 * @param string $viewName
	 */
	static function requireControllers($viewName){
		$subDirs= explode('/', $viewName);
		$path	= APP_ROOT.'/controllers';
		foreach ($subDirs as $f){
			$path .= '/'.$f;
			if (file_exists($path.'.php')){
				require_once $path.'.php';
			}
		}
	}
}
