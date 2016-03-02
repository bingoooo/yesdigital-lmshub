<?php
namespace FragTale\Db;

use FragTale\Application as APP;
use FragTale\Db\Adapter;
/**
 * @desc This class let you declare a DB structure. All public vars must be the table fields.
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 */
abstract class Table{
	
	/**
	 * Instance of FragTale\Db\Adapter
	 * @var \FragTale\Db\Adapter
	 */
	protected $_db;
	
	/**
	 * The DB Table name
	 * @var string
	 */
	protected $_tablename;
	
	/**
	 * The table prefix set into the DB ini file
	 * @var string
	 */
	protected $_tableprefix;
	
	/**
	 * Declare all fields that need to be converted with htmlentities.
	 * Typically, these are values that are typed into <input type="text" value="text input tags">
	 * @var array
	 */
	protected $_toHtmlEntities = array();
	
	
	/**
	 * Pass the connection name as declared in ini file.
	 * @param string||\Database\Adapter $connection
	 */
	function __construct($connection=''){
		$this->setDb($connection);
		$this->_tableprefix = $this->_db->getTablePrefix();
	}
	
	function getFullTableName(){
		return $this->_tableprefix.$this->_tablename;
	}
	
	function escape($string){return trim(str_replace("'", "''", $string));}
	
	/**
	 * @desc Insert new row(s) into DB table.
	 * @param array		$values		array('fieldname' => value, ...)
	 * @return bool (false) or int (rows affected)
	 */
	function insert($values){
		if (empty($this->_db)) return false;
		$values = $this->checkValues($values, __FUNCTION__, __LINE__);
		if (empty($values)) return false;
		$fields = array_keys($values);
		$query = 'INSERT INTO '.$this->getFullTableName().' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
		return $this->_db->exec($query);
	}
	
	/**
	 * @desc Update row into DB table.
	 * @param string	$where			Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$values			array('fieldname' => value, ...)
	 * @return bool (false) or int (rows affected)
	 */
	function update($where, $values){
		if (empty($this->_db)) return false;
		$values = $this->checkValues($values, __FUNCTION__, __LINE__);
		if (empty($values)) return false;
		$set = array();
		foreach ($values as $key=>$value){
			$set[] = $key.' = '.$value;
		}
		$query = 'UPDATE '.$this->getFullTableName().' SET '.implode(', ', $set).' WHERE '.$where;
		return $this->_db->exec($query);
	}
	
	/**
	 * Function dedicated to insert & update
	 * @param array $values
	 * @param string $function
	 * @param string $line
	 * @return boolean|array
	 */
	private function checkValues($values, $function='', $line=''){
		if (empty($values)){
			APP::catchError('Empty value passed: '.$values, get_class($this), $function, $line);
			return false;
		}
		if (!is_array($values)){
			APP::catchError('Array expected, single value passed: '.$values, get_class($this), $function, $line);
			return false;
		}
		foreach ($values as $key=>$value){
			if (!property_exists($this, $key)){
				APP::catchError('Invalid use of field "'.$key.'"', get_class($this), $function, $line);
				unset($values[$key]);
			}
			else{
				if ($value === null)
					$values[$key] = 'null';
				elseif ($value === false)
					$values[$key] = '0';
				elseif ($value === true)
					$values[$key] = '1';
				else{
					if (in_array($key, $this->_toHtmlEntities))
						$value = htmlentities($value, ENT_QUOTES);
					$values[$key] = '\''.$this->escape($value).'\'';
				}
			}
		}
		return $values;
	}
	
	/**
	 * @desc Delete row(s) into DB table. Warning: we don't check (un)escaped strings.
	 * @param string	$where			Explicite the condition(s) into a string. Example: "id=1"
	 * @return bool (false) or int (rows affected)
	 */
	function delete($where){
		if (empty($this->_db)) return false;
		return $this->_db->exec('DELETE FROM '.$this->getFullTableName().' WHERE '.$where);
	}
	
	/**
	 * @desc For a single select query (no "group by": pass it into $where).
	 * @param string	$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$fields		array('alias1'=>'field1','alias2'=>'field2',...)
	 * @param string	$order		Explicite the columns order into a string (with ASC & DESC).
	 * @param bool		$distinct	if true "Select Distinct"
	 * @return array
	 */
	function select($where='', $fields=array(), $order='', $distinct=false){
		if (empty($this->_db)) return false;
		$select = array();
		if (!empty($fields)){
			if (is_array($fields)){
				foreach ($fields as $alias=>$fieldname){
					if (!property_exists($this, $fieldname)){
						APP::catchError('Invalid use of field "'.$key.'"', get_class($this), __FUNCTION__, __LINE__);
						unset($fields[$alias]);
					}
					else $select[] = $fieldname.(is_numeric($alias) ? '' : ' AS '.$alias);
				}
			}
			else{
				if (!property_exists($this, $fields)){
					APP::catchError('Invalid use of field "'.$fields.'"', get_class($this), __FUNCTION__, __LINE__);
				}
				else $select[] = $fields;
			}
		}
		if (empty($select))	$select = '*';
		else				$select = implode(',', $select);
		if (!empty($where)) $where = 'WHERE '.$where;
		if (!empty($order)) $order = 'ORDER BY '.$order;
		$query = 'SELECT '.($distinct?'DISTINCT ':'').$select.' FROM '.$this->getFullTableName().' '.$where.' '.$order;
		return $this->_db->getTable($query);
	}
	
	/**
	 * Same as select, but distinct.
	 * @param string	$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$fields		array('alias1'=>'field1','alias2'=>'field2',...)
	 * @param string	$order		Explicite the columns order into a string (with ASC & DESC).
	 * @return array
	 */
	function selectDistinct($where='', $fields=array(), $order=''){
		return self::select($where, $fields, $order, true);
	}
	
	/**
	 * Same as select, but fetch first (and only one) row.
	 * @param string	$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$fields		array('alias1'=>'field1','alias2'=>'field2',...)
	 * @param string	$order		Explicite the columns order into a string (with ASC & DESC).
	 * @return array
	 */
	function selectRow($where, $fields=array(), $order=''){
		if (empty($this->_db)) return false;
		if ($data = self::select($where, $fields, $order, true))
			return reset($data);
		return array();
	}
	
	/**
	 * Same as select, but fetch first (and only one) value.
	 * @param string			$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param string||array		$field		'fieldname' || array('alias'=>'fieldname')
	 * @param string			$order		Explicite the columns order into a string (with ASC & DESC).
	 * @return scalar
	 */
	function selectValue($where, $field, $order=''){
		if (empty($this->_db)) return false;
		$select = '';
		if (is_string($field)){
			$select = $field;
		}
		elseif (is_array($field)){
			foreach ($field as $alias=>$fieldname){
				if (!property_exists($this, $fieldname)){
					APP::catchError('Invalid use of field "'.$key.'"', get_class($this), __FUNCTION__, __LINE__);
					unset($field[$alias]);
				}
				else $select = $fieldname.(!is_numeric($alias) ? ' AS '.$alias : '');
			}
			if (empty($field)) return null;
		}
		else return null;
		if (!empty($order)) $where .= ' ORDER BY '.$order;
		return $this->_db->getScalar("SELECT $select FROM ".$this->getFullTableName()." WHERE $where");
	}
	
	/**
	 * @param string $field		Column name
	 * @return scalar|NULL
	 */
	function selectMax($field){
		if (!property_exists($this, $field)) return null;
		return $this->_db->getScalar("SELECT MAX($field) FROM ".$this->getFullTableName());
	}
	
	/**
	 * Fill in the database property values of this database object
	 * @param string $where
	 * @param string $order
	 * @return boolean true on success
	 */
	function load($where, $order=''){
		if (empty($this->_db)) return false;
		$row = $this->selectRow($where, null, $order);
		if (empty($row)){
			foreach (get_object_vars($this) as $key=>$value){
				//Properties with a leading underscore must not be changed
				if (strpos($key, '_')!==0)
					$this->$key = null;
			}
			return false;
		}
		foreach ($row as $key=>$value)
			if (property_exists($this, $key))
				$this->$key = $value;
		return true;
	}
	
	/**
	 * @desc Load object giving the property name(s). ATTENTION: this function needs that you have set a value
	 * 		on the property object before. Most of time, prefer the simple "load" function.
	 * @example $Object = new Inherited_Class_Table();
	 * 			$Object->primary_key = 1;
	 * 			$Object->loadBy('primary_key');
	 * @param string|array	$property	Column name(s). Accept one string for one column name or an array of column names
	 * @param string		$order		Example: 'column ASC'
	 * @return boolean true on success
	 */
	function loadBy($property, $order=''){
		if (empty($this->_db)) return false;
		if (empty($property)) return false;
		if (!is_array($property)){
			if (!property_exists($this, $property)) return false;
			if (empty($this->$property)) return false;
			return $this->load($property.'\''.$this->$property.'\'', $order);
		}
		else{
			$where = array();
			foreach ($property as $key){
				if (!property_exists($this, $key)) return false;
				$where[] = $key.'\''.$this->$key.'\'';
			}
			return $this->load(implode(' AND ', $where), $order);
		}
	}
	
	/**
	 * Total number of rows
	 * @return scalar
	 */
	function count($where=null){
		if (empty($this->_db)) return false;
		$sql = 'SELECT COUNT(*) FROM '.$this->getFullTableName();
		if ($where){
			$sql .= ' WHERE '.$where;
		}
		return $this->_db->getScalar($sql);
	}
	
	
	/**
	 * @param mixed $connection
	 */
	function setDb($connection=''){
		if (empty($connection) || is_string($connection))
			$this->_db = Adapter::getInstanceOf($connection);
		elseif (is_a($connection, 'Adapter'))
			$this->_db = $connection;
	}
	/**
	 * @return \FragTale\Db\Adapter
	 */
	function getDb(){
		return $this->_db;
	}
	
	function clearDb(){
		$this->_db = null; 
	}
}