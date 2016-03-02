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
class Article_Files extends CMS{
	protected $_tablename = 'article_files';

	/**
	 * Primary key
	 * @var int
	 */
	var $afid;
	/**
	 * Article ID
	 * @var int
	 */
	var $aid;
	/**
	 * File ID
	 * @var int
	 */
	var $fid;
	/**
	 * User ID
	 * @var int
	 */
	var $uid;
	/**
	 * Article File Type ID
	 * @var string
	 */
	var $aftid;
	/**
	 * Media file width
	 * @var int
	 */
	var $width;
	/**
	 * Media file height
	 * @var int
	 */
	var $height;
	
}