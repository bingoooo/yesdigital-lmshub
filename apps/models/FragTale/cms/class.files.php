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
class Files extends CMS{
	protected $_tablename = 'files';

	/**
	 * Primary key
	 * @var int
	 */
	var $fid;
	/**
	 * Relative path from website
	 * @var string
	 */
	var $path;
	/**
	 * File name (must be unique)
	 * @var string
	 */
	var $filename;
	/**
	 * MIME-TYPE
	 * @var string
	 */
	var $mime_type;
	/**
	 * File size in octet
	 * @var int
	 */
	var $size;
	
	
	function get($values){
		$conditions = '';
		if (isset($values['search'])){
			$search = $values['search'];
			$conditions = "filename LIKE '$search%' ORDER BY filename";
		}
		return $this->select($conditions);
	}
	
	/**
	 * Auto store in DB and filesystem all $_FILES
	 * @param array $_files		You can just pass the $_FILES array
	 * @return array(fid=>filename)
	 */
	function store($_files=null){
		if (empty($_files)){
			$_files = $_FILES;
			if (empty($_files)) return array();
		}
		$files = array();
		if (isset($_files['name']) && is_array($_files['name'])){
			foreach($_files['name'] as $key=>$filenames){
				if (is_array($filenames)){
					foreach ($filenames as $i=>$filename){
						$type		= $_files['type'][$key][$i];
						$tmp_name	= $_files['tmp_name'][$key][$i];
						$error		= $_files['error'][$key][$i];
						$size		= $_files['size'][$key][$i];
						$this->processingFile($filename, $type, $tmp_name, $error, $size, $files);
					}
				}
				else{
					$filename	= $filenames;
					$type		= $_files['type'][$key];
					$tmp_name	= $_files['tmp_name'][$key];
					$error		= $_files['error'][$key];
					$size		= $_files['size'][$key];
					$this->processingFile($filename, $type, $tmp_name, $error, $size, $files);
				}
			}
		}
		elseif (!empty($_files['type']) && empty($_files['error'])){
			$this->processingFile($_files['name'], $_files['type'], $_files['tmp_name'], 0, $_files['size'], $files);
		}
		elseif (is_array($_files)){
			foreach ($_files as $_file){
				$result = $this->store($_file);
				if (!empty($result))
					$files += $result;
			}
		}
		return $files;
	}
	
	function processingFile($filename, $type, $tmp_name, $error, $size, &$files){
		if (empty($filename))
			return false;
		if ($error){
			$_SESSION['USER_END_MSGS']['ERRORS'][] = _('Unable to upload ').$filename.' : '.$error;
			return false;
		}
		
		$upDir = PUB_ROOT.'/uploads/';
		#Check permissions
		if (!is_writeable($upDir)){
			$msg = _('You must allow Apache to have recursive permissions to write upon ').$upDir;
			if (empty($_SESSION['USER_END_MSGS']['ERRORS']) || !in_array($msg, $_SESSION['USER_END_MSGS']['ERRORS']))
				$_SESSION['USER_END_MSGS']['ERRORS'][] = $msg;
		}
		#Create folder if not exist
		$toMkdir = explode('/', $type);
		$concatFolder = '';
		foreach ($toMkdir as $folder){
			$concatFolder.= $folder.'/';
			$tmpFolder = $upDir.$concatFolder;
			if (!is_dir($tmpFolder))
				mkdir($tmpFolder);
		}
		$filepath = $upDir.$type.'/'.$filename;
		$relativepath = str_replace(PUB_ROOT, '', $filepath);
		#Insert
		if ($this->load("path='".$this->escape($relativepath)."'")){
			$_SESSION['USER_END_MSGS']['WARNINGS'][] = $filename._(': a file with same name already exists. Item associated with the existing file.');
			$files[$this->fid] = $filename;
			return false;
		}
		if (file_exists($filepath))
			unlink($filepath);
		if (!copy($tmp_name, $filepath)){
			$_SESSION['USER_END_MSGS']['ERRORS'][] = $filename._(': error occured while transferring this file into the uploads directory.');
			return false;
		}
		else{
			chmod($filepath, 0774);
		}
		if ($this->insert(array('path'=>$relativepath, 'filename'=>$filename, 'mime_type'=>$type, 'size'=>$size))){
			$_SESSION['USER_END_MSGS']['SUCCESS'][] = $filename._(': file successfully transferred in server file system and its informations stored into the database.');
			$this->load("path='".$this->escape($relativepath)."'");
			$files[$this->fid] = $filename;
			return true;
		}
		return false;
	}
}