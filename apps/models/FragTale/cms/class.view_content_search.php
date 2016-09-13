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
class View_Content_Search extends CMS{
	protected $_tablename = 'view_content_search';
	/**
	 * @var int
	 */
	var $result_strength;
	/**
	 * Article ID
	 * @var int
	 */
	var $aid;
	/**
	 * User ID
	 * @var int
	 */
	var $uid;
	/**
	 * Category ID
	 * @var int
	 */
	var $catid;
	/**
	 * Article request URI
	 * @var string
	 */
	var $request_uri;
	/**
	 * Article title
	 * @var string
	 */
	var $article_title;
	/**
	 * Any searched content
	 * @var string
	 */
	var $content;
	/**
	 * Article creation date
	 * @var Timestamp
	 */
	var $cre_date;
	/**
	 * Is article published?
	 * @var boolean
	 */
	var $publish;
	
	/***   There can't be updates, deletes and inserts   ***/
	function update($where=null, $values=null){
		return false;
	}
	function insert($values=null){
		return false;
	}
	function delete($where=null){
		return false;
	}
}