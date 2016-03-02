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
class Article_Custom_Fields extends CMS{
	protected $_tablename = 'article_custom_fields';
	/**
	 * Primary key
	 * @var int
	 */
	var $aid;
	/**
	 * Primary key
	 * @var string
	 */
	var $field_key;
	/**
	 * Readable name for field
	 * @var string
	 */
	var $field_name;
	/**
	 * text, textarea, number, password, date or anything else you want to manage into your code
	 * @var string
	 */
	var $input_type;
	/**
	 * Any content of this field
	 * @var string
	 */
	var $field_value;
	/**
	 * Hierarchical position of field (to sort)
	 * @var Int
	 */
	var $position;
	
	/**
	 * To be converted before update or insert
	 * @var array
	 */
	protected $_toHtmlEntities = array('field_name');
}