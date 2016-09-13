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
class Message extends CMS{
	protected $_tablename = 'message';
	/**
	 * Primary key
	 * @var int
	 */
	var $mid;
	/**
	 * When the message has been sent
	 * @var Date
	 */
	var $send_date;
	/**
	 * The sender is a regitered User
	 * @var int
	 */
	var $sender_id;
	/**
	 * The recipient is a regitered User
	 * @var int
	 */
	var $recipient_id;
	/**
	 * Message body (can be as HTML format)
	 * @var string
	 */
	var $body;
	/**
	 * The recipient has read the message
	 * @var bool (tinyint)
	 */
	var $opened;
	
	/**
	 * 
	 * @param array $args It must contain at least the "mid" (message id)
	 */
	function set($args){
		if (empty($args['mid']))
			return false;
		$mid = $args['mid'];
		unset($args['mid']);
		return $this->update('mid='.$mid, $args);
	}
}