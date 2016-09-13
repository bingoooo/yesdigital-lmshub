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
class Article_Users extends CMS{
	protected $_tablename = 'article_users';

	/**
	 * Article ID, Primary key
	 * @var int
	 */
	var $aid;
	/**
	 * User ID, Primary key
	 * @var int
	 */
	var $uid;
	/**
	 * Primary key, type of association betwen article and user
	 * @var string
	 */
	var $type;
}