<?php
namespace FragTale\CMS;
use \FragTale\Db\CMS;
use FragTale\CMS\Article;

/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class User extends CMS{
	protected $_tablename = 'user';
	/**
	 * Primary key
	 * @var int
	 */
	var $uid;
	/**
	 * Is user active ?
	 * @var bit
	 */
	var $active;
	/**
	 * User's pseudonym
	 * @var string
	 */
	var $login;
	/**
	 * His e-mail address
	 * @var string
	 */
	var $email;
	/**
	 * MD5 encoding
	 * @var string
	 */
	var $password;
	/**
	 * 1=Mr, 2=Mrs
	 * @var int
	 */
	var $civility;
	/**
	 * User's first name
	 * @var string
	 */
	var $firstname;
	/**
	 * User's last name
	 * @var string
	 */
	var $lastname;
	/**
	 * User's birth name
	 * @var string
	 */
	var $bir_name;
	/**
	 * User's city of birth
	 * @var string
	 */
	var $bir_city;
	/**
	 * User's department of birth
	 * @var string
	 */
	var $bir_dpt;
	/**
	 * User's country of birth
	 * @var string
	 */
	var $bir_country;
	/**
	 * User's birthday
	 * @var Date
	 */
	var $bir_date;
	/**
	 * User's nationality
	 * @var string
	 */
	var $nationality;
	/**
	 * User's profession
	 * @var string
	 */
	var $profession;
	/**
	 * String as phone number
	 * @var string
	 */
	var $phone;
	/**
	 * String as phone number 2
	 * @var string
	 */
	var $phone2;
	/**
	 * User's social security number
	 * @var string
	 */
	var $ss_number;
	/**
	 * @var string
	 */
	var $address;
	/**
	 * @var string
	 */
	var $zip_code;
	/**
	 * @var string
	 */
	var $city;
	/**
	 * @var string
	 */
	var $region;
	/**
	 * @var string
	 */
	var $state;
	/**
	 * @var string
	 */
	var $country;
	/**
	 * Reference to User that created this User (not only himself)
	 * @var int
	 */
	var $cre_uid;
	/**
	 * Reference to User that last edited user profile
	 * @var int
	 */
	var $upd_uid;
	/**
	 * Creation date
	 * @var Date
	 */
	var $cre_date;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $upd_date;

	/**
	 * Fetch this User's rules
	 * @param int $uid
	 * @return array
	 */
	function getRoles($uid=null){
		static $cache = array();
		if ($uid && $this->uid != $uid){
			$this->load('uid='.$uid);
		}
		if (!isset($cache[$this->uid])){
			$cache[$this->uid] = array();
			$Roles	= new User_Roles();
			$Role	= new Role();
			if ($result = $Roles->selectDistinct('uid='.$this->uid, array('rid'), '1 ASC')){
				foreach ($result as $row){
					$cache[$this->uid][$row['rid']] = $Role->selectRow('rid='.$row['rid']);
				}
			}
		}
		return $cache[$this->uid];
	}

	/**
	 * The roles are already sorted by asc. So the first role is the strongest one.
	 * @param int $uid
	 * @return array The strongest role
	 */
	function getStrongestRole($uid=null){
		static $cache = array();
		if ($uid && $this->uid != $uid){
			$this->load('uid='.$uid);
		}
		if (!isset($cache[$this->uid])){
			$roles = $this->getRoles();
			$cache[$this->uid] = reset($roles);
		}
		return $cache[$this->uid];
	}
	
	function getArticles($uid=null){
		static $cache = array();
			if ($uid && $this->uid != $uid){
			$this->load('uid='.$uid);
		}
		if (!isset($cache[$this->uid])){
			$Article = new Article();
			$cache[$this->uid] = $Article->selectDistinct('owner_id='.$this->uid, null, 'edit_date DESC');
		}
		return $cache[$this->uid];
	}
}