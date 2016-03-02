<?php
namespace FragTale\CMS;
use \FragTale\Db\CMS;
use \FragTale\CMS\Files;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class User_Files extends CMS{
	protected $_tablename = 'user_files';
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Reference to File
	 * @var int
	 */
	var $fid;
	/**
	 * if true, this file is the user main profile picture
	 * @var bool
	 */
	var $is_profile;
	/**
	 * Fetch single array filled with a given User ID
	 * @param int $uid
	 * @return array of Files
	 */
	function getUserFiles($uid=null){
		static $files=array();
		if (empty($uid)){
			if (empty($his->uid))
				return array();
			else
				$uid = $this->uid;
		}
		if (!isset($files[$uid])){
			foreach ($this->select("uid='$uid'") as $uf){
				$files[$uid][$uf['fid']] = new Files();
				$files[$uid][$uf['fid']]->load("uid=$uid AND fid=".$uf['fid']);
			}
		}
		return $files[$uid];
	}
}