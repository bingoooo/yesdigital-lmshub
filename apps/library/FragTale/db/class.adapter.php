<?php
namespace FragTale\Db;
use FragTale\Application as APP;
use \PDO;
use \PDOException;

/**
 * Use static function "getInstanceOf", instead of using "new Adapter()".
 * Database access class.
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 */
class Adapter{
	private $connectionString = '';
	private $dbName	= '';
	private $dbHost = '';
	private $dbPort = '';
	private $dbDriver	= '';
	private $dbUser = '';
	private $dbPwd	= '';
	private $dbPrefix	='';
	private $dbCharset	= '';
	private $allowedDrivers = array('mysql', 'oci', 'oracle', 'mssql', 'dblib', 'odbc', 'sqlite');

	private static $instances		= array();
	private static $defaultSettings	= array();
	private $inifile		= null;
	private $instanceName	= null;
	/**
	 * @var \PDO
	 */
	private $conn;
	/**
	 * Use static function "getInstanceOf", instead of using "new Adapter()".
	 * @param string	$connectionName		The connection name must 
	 * @param bool		$dontSpecifyDbName	Set True if you don't want to pass the database name into connection string
	 * @see See "settings.ini" to know which are the connection names
	 */
	public function __construct($connectionName=DEFAULT_DATABASE_CONNECTOR_NAME){
		$this->instanceName = $connectionName;
		if (strtolower($this->instanceName)!=='none')
			$this->newConnectionString($connectionName);
		self::$instances[$connectionName] = $this;
	}

	/**
	 * Load the default.ini params into an array
	 * @return false (if error)
	 */
	public static function loadDefaultSettings(){
		#Load default settings
		if (empty(self::$defaultSettings)){
			$isPhp = false;
			$defaultIniFile = DB_CONF_ROOT.'/default.php';
			if (!file_exists($defaultIniFile))
				$defaultIniFile = DB_CONF_ROOT.'/default.ini';
			else
				$isPhp = true;
			if (!file_exists($defaultIniFile)){
				APP::catchError(
				'Fatal database configuration error! Missing required "'.$defaultIniFile.'" file.',
				__CLASS__, __FUNCTION__, __LINE__);
				return false;
			}
			if ($isPhp)
				self::$defaultSettings = require $defaultIniFile;
			else
				self::$defaultSettings = parse_ini_file($defaultIniFile);
		}
		return true;
	}
	
	/**
	 * Returns an array of params stored in default.ini file
	 * @return array
	 */
	public static function getDefaultSettings(){
		return self::$defaultSettings;
	}
	
	/**
	 * Return an existing or a created instance of Adapter. Use this method instead of "new Adapter()";
	 * @param string $connectionName	The database instance named into the settings.ini file
	 * @param bool $dontSpecifyDbName	Set True if you don't want to pass the database name into connection string
	 * @return Adapter
	 */
	public static function getInstanceOf($connectionName=DEFAULT_DATABASE_CONNECTOR_NAME){
		#If the connection name doesn't exist, we automatically chose the default one.
		$inifile = DB_CONF_ROOT.'/'.$connectionName.'.php';
		if (!file_exists($inifile))
			$inifile = DB_CONF_ROOT.'/'.$connectionName.'.ini';
		if (!file_exists($inifile))
			$connectionName = DEFAULT_DATABASE_CONNECTOR_NAME;
		if (!isset(self::$instances[$connectionName]))
			self::$instances[$connectionName] = new Adapter($connectionName);
		return self::$instances[$connectionName];
	}
	
	/**
	 * Set db connection parameters
	 * @param string $connectionName	The database instance named into the settings.ini file
	 * @param bool $dontSpecifyDbName	Set True if you don't want to pass the database name into connection string
	 * @return false (if error)
	 */
	private function newConnectionString($connectionName){
		if (!$this->loadDefaultSettings())
			return false;
		
		$isPhp = false;
		if (empty($this->inifile)){
			$this->inifile = DB_CONF_ROOT.'/'.$connectionName.'.php';
				if (!file_exists($this->inifile))
					$this->inifile = DB_CONF_ROOT.'/'.$connectionName.'.ini';
				else
					$isPhp = true;
		}
		if (!file_exists($this->inifile)){
			APP::catchError('Fatal database configuration error! "'.$this->inifile.'" file not found', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		
		if ($isPhp)
			$settings = require $this->inifile;
		else
			$settings = parse_ini_file($this->inifile);
		
		$this->dbHost = !empty($settings['host']) ? $settings['host'] : (!empty(self::$defaultSettings['host']) ? self::$defaultSettings['host'] : 'localhost');
		$this->dbUser = !empty($settings['user']) ? $settings['user'] : (!empty(self::$defaultSettings['user']) ? self::$defaultSettings['user'] : 'root');
		$this->dbPort = !empty($settings['port']) ? $settings['port'] : (!empty(self::$defaultSettings['port']) ? self::$defaultSettings['port'] : '3306');
		$this->dbName = !empty($settings['db_name'])? $settings['db_name']	: (!empty(self::$defaultSettings['db_name'])? self::$defaultSettings['db_name'] : null);
		$this->dbPwd  = !empty($settings['pwd'])	? $settings['pwd']		: (!empty(self::$defaultSettings['pwd'])	? self::$defaultSettings['pwd']		: null);
		$this->dbPrefix = !empty($settings['prefix'])	? $settings['prefix']		: (!empty(self::$defaultSettings['prefix'])	? self::$defaultSettings['prefix']		: null);
		$this->dbCharset= !empty($settings['charset'])	? str_replace('-', '', strtolower($settings['charset'])) : (!empty(self::$defaultSettings['pwd']) ? str_replace('-', '', strtolower(self::$defaultSettings['pwd'])) : null);
		$this->dbDriver	= !empty($settings['driver'])	? strtolower($settings['driver']) : (!empty(self::$defaultSettings['driver']) ? strtolower(self::$defaultSettings['driver']) : 'mysql');
		
		if (!in_array($this->dbDriver, $this->allowedDrivers)){
			APP::catchError(
				'Fatal database configuration error! The driver declared in your "'.$this->inifile.'" file is not supported for connection name : "'.$connectionName.'"; Driver = "'.$this->dbDriver.'".',
				__CLASS__, __FUNCTION__, __LINE__);
		}
		if ($this->dbDriver=='oracle')
			$this->dbDriver = 'oci';
		$this->connectionString = $this->dbDriver.':';
		$this->connectionString.= 'host='.$this->dbHost.';';
		$this->connectionString.= 'port='.$this->dbPort.';';
		$this->connectionString.= 'dbname='.$this->dbName.';';
		$this->rowsAffected		= 0;
	}

	/**
	 * Store all queries for devel var dumps.
	 * @param strin		$query		Sql statement
	 * @param string	$resulting 'Failed' or 'Success'
	 */
	private function storeQuery($query, $resulting='Success'){
		if (defined('ENV') && ENV=='devel')
			$GLOBALS['QUERIES'][$this->instanceName.'@'.$this->dbName][$resulting][] = $query;
	}
	
	/**
	 * Connect ONCE to the database.
	 */
	private function connect(){
		try{
			if (!is_a($this->conn, 'PDO')){
				if ($this->dbDriver=='mysql' && $this->dbCharset)
					$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$this->dbCharset);
				else
					$options = null;
				$this->conn = new PDO($this->connectionString, $this->dbUser, $this->dbPwd, $options);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			return true;
		}
		catch(PDOException $e){
			APP::catchError($e->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
		}
		return false;
	}

	/**
	 * Returns data table into a generic PHP array, indexed by incremented integers.
	 * If false: connection failed
	 * @param string	$query	Sql statement
	 * @return ArrayIterator
	 */
	final public function getTable($query){
		if ($this->connect()){
			$array = Array();
			try{
				if ($ret=$this->conn->query($query)){
					$array=$ret->fetchAll(PDO::FETCH_ASSOC);
					$this->storeQuery($query, 'Success');
					if (empty($array)) return Array();
					else return $array;
				}else {
					$msg = 'Bad syntax: '.$query;
					$this->newLogError($msg, __METHOD__);
				}
				return $array;
			}
			catch(PDOException $e){
				$msg = 'Error while fetching data table: '.$e->getMessage().'. ('.$query.')';
				APP::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
			}
		}
		$this->storeQuery($query, 'Fail');
		return false;
	}

	/**
	 * Returns ONE row from a data table.
	 * @param string	$query		Sql statement
	 * @param int		$rowNumber	The row position into the data table. Optional (default, 1st line)
	 * @return Array indexed by field names
	 */
	final public function getRow($query, $rowNumber=0){
		$array = $this->getTable($query);
		if ($array==false) return false;
		if (is_array($array)){
			$lim = count($array);
			if ($rowNumber<count($array))
				return $array[$rowNumber];
			else
				APP::catchError("Parameter '_rowNumber' ($rowNumber) is out of range (max.: $lim).", __CLASS__, __FUNCTION__, __LINE__);
		}
		return Array();
	}

	/**
	 * Returns a single value.
	 * If false: connection failed
	 * @param string	$query	Sql statement
	 * @return Anything but an array
	 */
	final public function getScalar($query){
		if ($this->connect()){
			try{
				if ($ret=$this->conn->query($query)){
					if ($res=$ret->fetchAll(PDO::FETCH_ASSOC)){
						$colsName	= array_keys($res[0]);
						$nbLn		= count($res);
						$nbCols		= count($colsName);
						if ($nbLn > 1 || count($nbCols) > 1){
							$msg = "This query is fetching a table ($nbLn line(s) & $nbCols column(s)) but the method is expecting a scalar. ; Query: $query";
							APP::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
						}
						else{
							$scalar = $res[0][$colsName[0]];
							$this->storeQuery($query, 'Success');
							return $scalar;
						}
					}
				}else {
					$msg = "Bad query syntax: '$query'";
					APP::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
				}
			}
			catch(PDOException $e){
				$msg = 'Error while fetching the scalar value: '.$e->getMessage()." ; Query: $query";
				APP::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
			}
		}
		$this->storeQuery($query, 'Fail');
		return false;
	}

	/**
	 * Any action in write mod (insert, update, delete).
	 * If returns false: connection failed.
	 * @param string $query obligatoire
	 * @param bool $withTransaction indique si l'on veut automatiquement ou non effectuer un "commit et un "rollback"
	 * @return bool (false) or int (rows affected)
	 */
	final public function exec($query, $withTransaction = true){
		if ($this->connect()){
			try{
				if ($withTransaction) $this->conn->beginTransaction();
				$result = $this->conn->exec($query);
				if ($result!==false) {
					if ($withTransaction) $this->conn->commit();
					$this->storeQuery($query, 'Success');
					return $result;
				}
				elseif ($withTransaction) $this->conn->rollBack();
			} catch(PDOException $e) {
				if ($withTransaction) $this->conn->rollBack();
				$msg = 'Database transaction error: '.$e->getMessage()." ; query: $query";
				APP::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
			}
		}
		$this->storeQuery($query, 'Fail');
		return false;
	}

	/**
	 * Close connection.
	 */
	public function dispose(){
		unset($this->conn);
		unset(self::$instances[$this->instanceName]);
	}
	
	/**
	 * @return string
	 */
	public function getTablePrefix(){
		return $this->dbPrefix;
	}
}