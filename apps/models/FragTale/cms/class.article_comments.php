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
class Article_Comments extends CMS{
	protected $_tablename = 'article_comments';
	/**
	 * Primary key
	 * @var int
	 */
	var $acid;
	/**
	 * Reference to Article
	 * @var int
	 */
	var $aid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Comment
	 * @var string
	 */
	var $message;
	/**
	 * If true, that means this comment is blocked
	 * @var boolean
	 */
	var $blocked;
	/**
	 * Date of post
	 * @var date
	 */
	var $edit_date;
	
	
	function getComments($aid, $asc=true){
		$User = new User();
		$query = 'SELECT C.acid, C.aid, C.message, C.edit_date, U.uid, U.login AS user_login FROM '.$this->getFullTableName().' AS C '.
			'LEFT JOIN '.$User->getFullTableName().' AS U ON U.uid = C.uid '.
			'WHERE C.aid = '.$aid.' ORDER BY C.edit_date '.($asc?'ASC':'DESC');
		unset($User);
		return $this->_db->getTable($query);
	}
}