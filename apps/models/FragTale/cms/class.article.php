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
class Article extends CMS{
	protected $_tablename = 'article';
	/**
	 * Primary key
	 * @var int
	 */
	var $aid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $owner_id;
	/**
	 * Reference to article_category
	 * @var int
	 */
	var $catid;
	/**
	 * Its view name
	 * @var string
	 */
	var $view;
	/**
	 * Degre of accessibility: 1=Only for super-admin, 2=For administrators, etc.
	 * @var int
	 */
	var $access;
	/**
	 * Url alias
	 * @var string
	 */
	var $request_uri;
	/**
	 * Reference to Files
	 * @var int
	 */
	var $fid;
	/**
	 * Article title
	 * @var string
	 */
	var $title;
	/**
	 * Article summary (to display into news box)
	 * @var string
	 */
	var $summary;
	/**
	 * Article HTML body content
	 * @var string
	 */
	var $body;
	/**
	 * Small ending message before signature
	 * @var string
	 */
	var $greeting_text;
	/**
	 * If null, get the author name or login
	 * @var string
	 */
	var $signature;
	/**
	 * Publishing or not?
	 * @var boolean
	 */
	var $publish;
	/**
	 * Hierarchical position of article (between a same category)
	 * @var Int
	 */
	var $position;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $edit_date;
	/**
	 * Creation date
	 * @var Timestamp
	 */
	var $cre_date;
	
	/**
	 * To be converted before update or insert
	 * @var array
	 */
	protected $_toHtmlEntities = array('title');
	
	/**
	 * @var Article_History
	 */
	protected $_initialArticle;
	
	/**
	 * @return Article_Category
	 */
	function getCategory(){
		if (!$this->catid)
			return false;
		static $Category;
		if (!empty($Category->catid))
			return $Category;
		$Category = new Article_Category();
		$Category->load('catid='.$this->catid);
		return $Category;
	}
	
	/**
	 * @return boolean
	 */
	function historicize(){
		if (empty($this->aid)){
			\FragTale\Application::catchError('The article has not been loaded before its historization.', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		$AH = new Article_History();
		$values = (array)$this;
		foreach ($values as $key=>$value){
			if (substr($key, 0, 1)==='_' || !property_exists($AH, $key))
				unset($values[$key]);
		}
		return $AH->insert($values);
	}
	
	/**
	 * On update, check if the author has changed or if the date is a day after.
	 * @param Article $oldOne	If null, this article will be historicized
	 * @return boolean
	 */
	function autoHistoricize(Article $oldOne=null){
		if (empty($oldOne)){
			$oldOne = $this;
		}
		if (empty($this->aid) || empty($oldOne->aid)){
			\FragTale\Application::catchError('You must load the Article objects before using "autoHistoricize" method.', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		if ($oldOne->uid!==$this->uid)
			return $oldOne->historicize();
		if (date('Ymd')>date('Ymd', strtotime($oldOne->edit_date)))
			return $oldOne->historicize();
		return false;
	}
	
	protected function _isLoaded(&$aid=null){
		if (empty($aid) && empty($this->aid)){
			return false;
		}
		elseif (!empty($aid) && $aid != $this->aid){
			return $this->load('aid='.$aid);
		}
		elseif (!empty($this->aid)){
			return true;
		}
		return false;
	}
	
	/**
	 * The owner of the article is the first user that have register the article.
	 * @param int $aid
	 * @return boolean|number
	 */
	function getOwnerID($aid=null){
		if ($initialArticle = $this->getInitialArticle($aid)){
			return $initialArticle->uid;
		}
		return false;
	}
	
	/**
	 * Get the very first article
	 * @param int $aid
	 * @return \FragTale\CMS\Article_History|boolean
	 */
	function getInitialArticle($aid=null){
		if ($this->_isLoaded($aid)){
			if (empty($this->_initialArticle)){
				$this->_initialArticle = new Article_History();
				if ($this->_initialArticle->load('aid='.$this->aid, 'ahid ASC LIMIT 0,1')){
					return $this->_initialArticle;
				}
				else{
					$this->_initialArticle = $this;
				}
			}
		}
		return false;
	}
	
	/**
	 * Get an array of all this article history
	 * @param int $aid
	 * @return array|boolean
	 */
	function getHistory($aid=null){
		static $cache = array();
		if ($this->_isLoaded($aid)){
			if (empty($cache[$this->aid])){
				$AH = new Article_History();
				$cache[$this->aid] = $AH->selectDistinct('aid='.$this->aid, null, 'edit_date DESC, ahid DESC');
			}
			return $cache[$this->aid];
		}
		return false;
	}
	
	/**
	 * @param boolean $usecache		Set false if you want to renew the data
	 * @return array|boolean
	 */
	function getCustomFields($usecache=true){
		static $cache = array();
		if ($this->_isLoaded($aid)){
			if (empty($cache[$this->aid]) || !$usecache){
				$CF = new \FragTale\CMS\Article_Custom_Fields();
				$values = array();
				if ($results = $CF->selectDistinct('aid='.$this->aid, null, 'position ASC')){
					foreach ($results as $i=>$row){
						$value = @unserialize($row['field_value']);
						if ($value)
							$row['field_value'] = $value;
						$values[$row['field_key']] = $row;
					}
				}
				$cache[$this->aid] = $values;
			}
			return $cache[$this->aid];
		}
		return false;
	}
}

class Article_History extends CMS{
	protected $_tablename = 'article_history';
	/**
	 * Primary key
	 * @var int
	 */
	var $ahid;
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
	 * Reference to article_category
	 * @var int
	 */
	var $catid;
	/**
	 * Its view name
	 * @var string
	 */
	var $view;
	/**
	 * Degre of accessibility: 1=Only for super-admin, 2=For administrators, etc.
	 * @var int
	 */
	var $access;
	/**
	 * Url alias
	 * @var string
	 */
	var $request_uri;
	/**
	 * Article title
	 * @var string
	 */
	var $title;
	/**
	 * Article summary (to display into news box)
	 * @var string
	 */
	var $summary;
	/**
	 * Article HTML body content
	 * @var string
	 */
	var $body;
	/**
	 * Small ending message before signature
	 * @var string
	 */
	var $greeting_text;
	/**
	 * If null, get the author name or login
	 * @var string
	 */
	var $signature;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $edit_date;
	
	/**
	 * To be converted before update or insert
	 * @var array
	 */
	protected $_toHtmlEntities = array('title');
}