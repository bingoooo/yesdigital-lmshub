<?php
namespace FragTale\CMS;
use \FragTale\Db\CMS;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class Parameters extends CMS{
	protected $_tablename = 'parameters';
	/**
	 * Primary key
	 * @var string
	 */
	var $param_key;
	/**
	 * Any value
	 * @var string
	 */
	var $param_value;
	
	/**
	 * To be converted before update or insert
	 * @var array
	 */
	protected $_toHtmlEntities = array('key', 'value');
}