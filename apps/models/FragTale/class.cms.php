<?php
namespace FragTale\Db;
use \FragTale\Db\Table;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class CMS extends Table{
	/**
	 * This class will be extended in all CMS model's classes
	 * @param string $connection
	 */
	function __construct($connection=''){
		if (empty($connection)){
			if (defined('DEFAULT_CMS_DATABASE_CONNECTOR_NAME'))
				$connection = DEFAULT_CMS_DATABASE_CONNECTOR_NAME;
		}
		if (empty($connection) || strtolower($connection)==='none'){
			if (empty($_SESSION['CMS_DB_MESSAGE']))
				$_SESSION['CMS_DB_MESSAGE'] = 'Connection to CMS database not set by default into "ini" file.';
		}
		parent::__construct($connection);
	}
}