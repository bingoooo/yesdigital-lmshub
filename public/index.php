<?php
namespace FragTale;

# Measuring PHP process time
$chrono = microtime(true);

# Base constants
define('PUB_ROOT',		__DIR__);
define('DOC_ROOT',		realpath(PUB_ROOT.'/..'));
define('CONF_ROOT',		DOC_ROOT.'/settings');
define('DB_CONF_ROOT',	CONF_ROOT.'/databases');
define('APP_ROOT',		DOC_ROOT.'/apps');
define('LIB_ROOT',		APP_ROOT.'/library');
define('TPL_ROOT',		APP_ROOT.'/templates');
define('DEFAULT_LAYOUT',TPL_ROOT.'/pages/default.layout.phtml');
define('PAGE_404',		TPL_ROOT.'/views/404.phtml');
if (strpos(PUB_ROOT, $_SERVER['DOCUMENT_ROOT'])!==false)
	define('REL_DIR',	trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', PUB_ROOT), '/'));
elseif (isset($_SERVER['PHP_SELF'])){
	$uri = str_replace('/index.php', '', $_SERVER['PHP_SELF']);
	define('REL_DIR', trim($uri, '/'));
}
else
	die(
		'Unable to set the REL_DIR constant in '.__FILE__.' line '.__LINE__.
		'. This issue is due to a limit from the FragTale framework on some server\'s configuration.'.
		' In that case, $_SERVER["PHP_SELF"] is missing and your project directory is aliased on your web host.'
	);
define('HTTP_PROTOCOLE',!empty($_SERVER['SSL_PROTOCOL']) ? 'https' : 'http');
# Application base url
define('WEB_ROOT',		trim(HTTP_PROTOCOLE.'://'.$_SERVER['HTTP_HOST'].'/'.REL_DIR, '/'));
# Admin base url
define('ADMIN_WEB_ROOT',WEB_ROOT.'/admin');

//session_save_path(DOC_ROOT.'/sessions');
session_start();

require_once LIB_ROOT.'/fragtale.application.php';
# Loading application params & set default view & layout params
Application::loadIniParams();

# Instanciate the meta View
$view = new View();
# Render
$view->render();
if (defined('ENV') && ENV=='devel'){
	$view->unsetRender();
	#Displaying PHP process time on dev env
	Debug::vars('PHP process time: '.substr((microtime(true)-$chrono)*1000, 0, 5).'ms'.' | Allocated mem: '.round(memory_get_peak_usage()/1024/1024, 2).'Mo');
}