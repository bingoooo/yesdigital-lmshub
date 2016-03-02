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
class Reaction_Type extends CMS{
	protected $_tablename = 'reaction_type';

	/**
	 * Primary key
	 * @var int
	 */
	var $reacid;
	/**
	 * @var string
	 */
	var $reacname;
}