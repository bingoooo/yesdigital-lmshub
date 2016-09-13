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
class Article_Files_User_Reactions extends CMS{
	protected $_tablename = 'article_files_user_reactions';

	/**
	 * Primary key
	 * @var int
	 */
	var $afuid;
	/**
	 * Article Files ID
	 * @var int
	 */
	var $afid;
	/**
	 * User ID
	 * @var int
	 */
	var $uid;
	/**
	 * Reaction Type ID
	 * @var int
	 */
	var $reacid;
	/**
	 * @var string
	 */
	var $content;
	/**
	 * @var string
	 */
	var $date;
	
	##### Like REST API system (put/get, no need to update here)
	/**
	 * @desc Fetch a list of Chat (usually called by AJAX or REST API systems).
	 * By default, the method returns the last 5 posts.
	 * @param array $values Any array containing the interesting key(s)
	 * @return array
	 */
	function get($values=array()){
		$User = new User();
		#Base query
		$sql = 'SELECT DISTINCT C.*, '.
				'U.login AS user_login, '.
				//'U.email AS user_email, '.
				//'U.firstname AS user_firstname, '.
				//'U.lastname AS user_lastname, '.
				'U.active AS user_is_active '.
			'FROM '.$this->getFullTableName().' AS C LEFT JOIN '.$User->getFullTableName().' AS U ON C.uid = U.uid ';
		#Order direction (asc/desc)
		$direction = isset($values['direction']) && in_array($values['direction'], array('desc', 'DESC')) ? 'DESC' : 'ASC';
		#Apply conditions
		$reacid = empty($values['reacid']) ? 1 : (int)$values['reacid'];
		if (!empty($values['afuid'])){
			if (empty($values['previous']))
				$sql .= 'WHERE C.reacid = '.$reacid.' AND C.afuid > '.$values['afuid'].' ORDER BY C.afuid '.$direction;
			else
				$sql .= 'WHERE C.reacid = '.$reacid.' AND C.afuid < '.$values['afuid'].' ORDER BY C.date DESC LIMIT 0, 10';
		}
		elseif (!empty($values['afid'])){
			$sql .= 'WHERE C.reacid = '.$reacid.' AND C.afid = '.$values['afid'].' ORDER BY C.date '.$direction;
		}
		else
			$sql .= 'WHERE C.reacid = '.$reacid.' AND C.afuid > (SELECT MAX(afuid) - 10 FROM '.$this->getFullTableName().') ORDER BY C.afuid ASC';
		if (!empty($values['limit']))
			$sql .= ' LIMIT '.$values['limit'];
		#Parse date
		$data = $this->_db->getTable($sql);
		foreach ($data as $i=>$row){
			$data[$i]['date']	= date('d/m/Y - H:i:s', strtotime($row['date']));
			$data[$i]['content']= str_ireplace(array('<script', '</script>'), '', trim($data[$i]['content']));
		}
		return $data;
	}
	/**
	 * @desc Insert new chat.
	 * Note: the UID is only from the Session User. Cannot insert without authentication.
	 * @param array $values
	 */
	function put($values){
		foreach (array('afid', 'uid', 'content') as $requiredKey){
			if (empty($values[$requiredKey]))
				return false;
		}
		if (empty($values['reacid'])) $values['reacid'] = 1;
		return $this->insert($values);
	}
}