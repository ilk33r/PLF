<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
* ------------------------------------------------
* Database Library
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

abstract class PLF_Db
{


/**
* ------------------------------------------------
* Database variables
* ------------------------------------------------
* 
* @author ilker özcan
* @var $connection current connection
* @var $lastQuery string
* 
*/
	
	protected $isTransactionStart		= FALSE;
	public $connection;
	public $lastQuery;
	public $transactionError			= FALSE;

	
/**
* ------------------------------------------------
* Register destruct
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/
	
	public function __construct()
	{
		Common::destructs(TRUE, 'db', 'destruct');
	}


/**
* ------------------------------------------------
* Error handler
* ------------------------------------------------
* 
* @author ilker özcan
* @param $errNo int
* @param $errorMessage string
* @param $query string
* 
*/

	protected function dbError($errNo, $errorMessage, $query = '')
	{
		if($this->isTransactionStart)
		{
			$this->transactionError		= TRUE;
			$this->rollback();
		}

		if(Database::$dbDebug){
			ob_end_clean();
			if(PHP_SAPI != 'cli')
			{
				$dbErrorFile					= Common::getFilePath('views/errors/error_database.php');
				require($dbErrorFile);
			}else{
				echo $errNo . ' ' . $errorMessage . ' ' . $query;
			}
			PLF_Output::$masterSended		= true;
			exit;
		}
	}
	
	
/**
* ------------------------------------------------
* Escape character
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $str
* 
*/

	protected function escape_str($str)
	{
		if (is_string($str))
		{
			$str		= stripslashes($str);
			$str		= str_replace("'", "''", remove_invisible_characters($str, FALSE));
			$str		= str_replace('&#039;', '', $str);
			$escaped	= "'".addcslashes($str, '\\$')."'";
		}
		elseif (is_bool($str))
		{
			$escaped	= ($str == FALSE) ? 0 : 1;
		}
		elseif ($str === NULL)
		{
			$escaped	= 'NULL';
		}else{
			$escaped	= $str;
		}

		return $escaped;
	}
	
/**
* ------------------------------------------------
* Prepare for binding sql query
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $str
* @param array $binds
* 
*/

	protected function prepare($sql, $binds)
	{
		if(is_array($binds))
		{
			foreach($binds as $bind)
			{
				$sql				= preg_replace('/\?/', $this->escape_str($bind), $sql, 1);
			}
			return $sql;
		}else{
			return $sql;
		}
	}


/**
* ------------------------------------------------
* Return results for last query
* ------------------------------------------------
* 
* @author ilker özcan
* @return array
* 
*/

	public function result($type = 'object')
	{
		if($this->multiQuery)
		{
			return $this->multiQueryResult();
		}else{
			return $this->singleQueryResult($type);
		}
	}


/**
* ------------------------------------------------
* Database methods
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/	

	abstract protected function connect();
	abstract protected function disconnect();
	abstract protected function reconnect();
	abstract protected function destruct();
	abstract protected function insertId();
	abstract protected function numRows();
	abstract protected function affectedRows();
	abstract protected function query($sql, $binds = NULL, $freeResult = FALSE);
	abstract protected function mquery($sql, $binds = NULL, $freeResult = FALSE);
	abstract protected function singleQueryResult($type);
	abstract protected function multiQueryResult();
	abstract protected function row($type = 'object');
	abstract protected function transaction();
	abstract protected function commit();
	abstract protected function rollback();
	abstract protected function insert($tableName, $params = array());
	abstract protected function insertignore($tableName, $params = array());
	abstract protected function update($tableName, $params, $where = array());
	abstract protected function delete($tableName, $where);
	abstract protected function select($tableName, $pk, $fields, $joinedTables, $predicate, $sortDescriptors, $limit, $mtmField);
	abstract protected function getTableList();
	abstract protected function getCreateSteatment($dbName, $tbObjects);
	abstract protected function getColumnAndIndexList($tableName);
	abstract protected function addOrUpdateColumn($tableName, $tableObject, $isUpdate = false);
	abstract protected function removeIndex($tableName, $indexName);
	abstract protected function addIndex($tableName, $indexName, $columnName);
	abstract protected function removeForeignKey($tableName, $foreignKeyName);
	abstract protected function addForeignKey($tableName, $foreignKeyName, $columnName, $referanceTableName, $referanceColumn);
}

class ActiveRecord
{
	private $db;

	public  $databaseTableName;

	private $databaseObjects				= null;
	private $pk								= null;
	private $_SelectedFields				= null;
	private $_JoinedTables					= null;
	private $_limit							= null;
	private $_sortDescriptors				= null;
	private $_mtmField						= null;
	private $_predicate						= null;
	private $_lastResult					= null;
	private $_objectCount					= null;

	public function __construct()
	{
		$PLF	=& Common::getInstance();
		if(!isset($PLF['db']))
		{
			$PLF['load']->db();
		}

		$this->db				= $PLF['db'];

		if(!$this->databaseTableName)
		{
			$this->databaseTableName		= get_class($this);
		}

		$this->databaseObjectsToArray();
	}

	public function properties()
	{
		if(is_null($this->_SelectedFields))
		{
			$this->_SelectedFields						= [];
		}

		foreach (func_get_args() as $n)
		{
			$this->_SelectedFields[]				= $n;
		}

		return $this;
	}

	public function selectRelation($fieldName, $type = 'left', $predicate = '')
	{
		if(is_null($this->_JoinedTables))
		{
			$this->_JoinedTables				= [];
		}

		$joinPredicate							= $predicate;
		$fieldObject							= false;

		foreach($this->databaseObjects as $dbObject) {
			if ($dbObject['name'] == $fieldName && isset($dbObject['value']['fk'])) {
				$fieldObject = $dbObject;
				break;
			}
		}

		if($fieldObject)
		{
			if($joinPredicate == '')
			{
				$joinPredicate		= $fieldObject['value']['fk']['table'] . '.' . $fieldObject['value']['fk']['reference'] . '=' . $this->databaseTableName . '.' . $fieldObject['name'];
			}

			$this->_JoinedTables[]					= ['table'=>$fieldObject['value']['fk']['table'], 'type'=>$type, 'predicate'=>$joinPredicate];
		}else{
			if(!empty($predicate))
			{
				$this->_JoinedTables[]				= ['table'=>$fieldName, 'type'=>$type, 'predicate'=>$joinPredicate];
			}
		}

		return $this;
	}

	public function selectMtm()
	{
		if(is_null($this->_mtmField))
		{
			$this->_mtmField				= [];
		}

		$functionArgs						= func_get_args();
		$fieldName							= $functionArgs[0];
		$fieldFounded						= false;
		foreach($this->databaseObjects as $dbObject)
		{
			if($dbObject['name'] == $fieldName)
			{
				$this->_mtmField['field']			= $dbObject;
				$fieldFounded						= true;
				break;
			}
		}

		if($fieldFounded)
		{
			$this->_mtmField['dbName']			= $this->databaseTableName . '_' . $this->_mtmField['field']['value']['mtm']['table'] . '_' . $this->_mtmField['field']['value']['mtm']['reference'];
			$this->_mtmField['properties']		= [];

			$this->_mtmField['properties'][]	= $this->_mtmField['field']['value']['mtm']['reference'];
			for($i = 1; $i < count($functionArgs); $i++)
			{
				$this->_mtmField['properties'][]	= $functionArgs[$i];
			}
		}else{
			throw new Exception($fieldName . ' not found in ' . get_class($this) . ' class');
		}

		return $this;
	}

	public function limit($rowCount, $startRow = 0)
	{
		if(is_null($this->_limit))
		{
			$this->_limit	= [];
		}

		$startRowInt		= (int)$startRow;
		$rowCountInt		= (int)$rowCount;
		$this->_limit		= $startRowInt . ', ' . $rowCountInt;
		return $this;
	}

	public function predicate()
	{
		if(is_null($this->_predicate))
		{
			$this->_predicate				= [];
		}

		$functionArgs						= func_get_args();
		$this->_predicate['predicate']		= $functionArgs[0];

		$predicateValues					= [];
		for($i = 1; $i < count($functionArgs); $i++)
		{
			if(is_array($functionArgs[$i]))
			{
				foreach($functionArgs[$i] as $predicateValue)
				{
					$predicateValues[]		= $predicateValue;
				}
			}else{
				$predicateValues[]			= $functionArgs[$i];
			}
		}

		$this->_predicate['values']			= $predicateValues;
		return $this;
	}

	public function sortDescriptor($column, $ascending = true)
	{
		if(is_null($this->_sortDescriptors))
		{
			$this->_sortDescriptors	= [];
		}

		$this->_sortDescriptors[]			= ['column'=>$column, 'ascending'=>$ascending];
		return $this;
	}

	public function get($pk = null)
	{
		if(!is_null($pk))
		{
			foreach($this->databaseObjects as $object)
			{
				$objectValue			= $object['value'];
				if(isset($objectValue['pk']))
				{
					if($objectValue['pk'])
					{
						$this->pk			= ['name'=>$object['name'], 'value'=>$pk];
						break;
					}
				}
			}
		}

		$sql								= $this->db->select($this->databaseTableName, $this->pk, $this->_SelectedFields, $this->_JoinedTables, $this->_predicate, $this->_sortDescriptors, $this->_limit, $this->_mtmField);
		if($sql)
		{
			if(!is_null($this->pk))
			{
				if(is_null($this->_mtmField))
				{
					$this->_lastResult			= $this->db->row('array');
					foreach($this->_lastResult as $resultKey => $resultValue)
					{
						if(is_numeric($resultKey))
						{
							continue;
						}

						$this->$resultKey		= $resultValue;
					}
				}else{
					$this->_lastResult			= $this->db->result();
					if(isset($this->_lastResult['result1']))
					{
						foreach($this->_lastResult['result1'] as $resultKey => $resultValue)
						{
							$this->$resultKey		= $resultValue;
						}
					}

					if(isset($this->_lastResult['result2']))
					{
						$fieldName			= $this->_mtmField['field']['name'];
						$mtmFieldDataArr	= [];
						$this->$fieldName	= [];

						foreach($this->_lastResult['result2'] as $mtmData)
						{
							$mtmFieldDataArr[]		= $mtmData;
						}

						$this->$fieldName		= $mtmFieldDataArr;
					}
				}

				$cloneObject				= clone $this;
				$this->flushQuery();
				return $cloneObject;
			}else{
				$this->_lastResult			= $this->db->result();
			}
		}

		$this->flushQuery();
		return $this->_lastResult;
	}

	public function sync($ignoreDublicate = false)
	{
		if(is_null($this->pk))
		{
			$insertParams			= [];
			$mtmFields				= [];
			foreach($this->databaseObjects as $databaseObject)
			{
				$objectName						= $databaseObject['name'];
				if(is_null($this->$objectName))
				{
					continue;
				}

				if(isset($databaseObject['value']['mtm']))
				{
					$mtmFields[]	= $databaseObject;
					continue;
				}

				$insertParams[$objectName]		= $this->$objectName;
			}

			if($ignoreDublicate)
			{
				$this->db->insertignore($this->databaseTableName, $insertParams);
			}else{
				$this->db->insert($this->databaseTableName, $insertParams);
			}

			$this->flushQuery();

			foreach($this->databaseObjects as $object)
			{
				$objectValue			= $object['value'];
				if(isset($objectValue['pk']))
				{
					if($objectValue['pk'])
					{
						$pkObjectName			= $object['name'];
						$insertId				= $this->db->insertId();
						$this->$pkObjectName	= $insertId;
						$this->pk				= ['name'=>$object['name'], 'value'=>$insertId];
						break;
					}
				}
			}

			if(count($mtmFields) > 0 && !is_null($this->pk))
			{
				if($this->pk['value'])
				{
					$this->syncMtmFields($mtmFields, $ignoreDublicate);
				}
			}

			$cloneObject				= clone $this;
			$this->flushQuery();
			return $cloneObject;
		}else{
			$updateParams			= [];
			$mtmFields				= [];

			foreach($this->databaseObjects as $databaseObject)
			{
				$objectName			= $databaseObject['name'];

				if(isset($databaseObject['value']['mtm']))
				{
					if(!is_null($this->$objectName))
					{
						$mtmFields[]	= $databaseObject;
						continue;
					}
				}

				if(isset($this->_lastResult[$objectName]) || isset($this->$objectName))
				{
					if(!is_null($this->$objectName))
					{
						if($this->_lastResult[$objectName] != $this->$objectName)
						{
							$updateParams[$objectName]		= $this->$objectName;
						}
					}
				}
			}

			if(count($updateParams) > 0)
			{
				$this->db->update($this->databaseTableName, $updateParams, array($this->pk['name']=>$this->pk['value']));
			}

			if(count($mtmFields) > 0)
			{
				$this->syncMtmFields($mtmFields, true, true);
			}

			return $this;
		}
	}

	public function delete()
	{
		if(!is_null($this->pk))
		{
			$this->db->delete($this->databaseTableName, array($this->pk['name']=>$this->pk['value']));
		}
	}

	public function getPKFieldName()
	{
		$fieldName			= '';
		foreach($this->databaseObjects as $dbObjec){

			$objectData			= $dbObjec['value'];
			if(isset($objectData['pk']))
			{
				if($objectData['pk'])
				{
					$fieldName	= $dbObjec['name'];
					break;
				}
			}
		}

		return $fieldName;
	}

	public function hasObject($objectName)
	{
		$hasObject			= false;
		foreach($this->databaseObjects as $dbObject)
		{
			$dbObjectName			= $dbObject['name'];
			if($objectName == $dbObjectName)
			{
				$hasObject		= true;
				break;
			}
		}

		return $hasObject;
	}

	public function getDatabaseObjects()
	{
		return $this->databaseObjects;
	}

	public function getObjectId()
	{
		if(!is_null($this->pk)) {
			if (isset($this->pk['value'])) {
				return $this->pk['value'];
			} else {
				return false;
			}
		}
	}

	public function count()
	{
		if(is_null($this->_objectCount))
		{
			$this->_objectCount			= $this->db->query('select count(*) as objectCount from ' . $this->databaseTableName)->row()->objectCount;
		}

		return $this->_objectCount;
	}

	private function databaseObjectsToArray()
	{
		$objects			= [];
		foreach(get_object_vars($this) as $objectName => $objectValue)
		{
			if(is_array($objectValue))
			{
				if(isset($objectValue['type']))
				{
					$objects[]				= ['name'=>$objectName, 'value'=>$objectValue];
					$this->$objectName		= null;
				}
			}
		}

		$this->databaseObjects			= $objects;
	}

	private function flushQuery()
	{
		$this->pk						= null;
		$this->_SelectedFields			= null;
		$this->_JoinedTables			= null;
		$this->_limit					= null;
		$this->_sortDescriptors			= null;
		$this->_predicate				= null;
		$this->_mtmField				= null;
	}

	private function syncMtmFields($mtmFields, $ignoreDublicate = false, $isUpdate = false)
	{
		$pkValue				= $this->pk['value'];

		if(!$isUpdate)
		{
			foreach($mtmFields as $field)
			{
				$insertParams	= [];
				$fieldName		= $field['name'];
				if(is_array($this->$fieldName))
				{
					foreach($this->$fieldName as $mtmValue)
					{
						$insertParameter		= [$fieldName => $pkValue, $field['value']['mtm']['reference'] . '_mtm' => $mtmValue->pk];
						$insertParams[]			= $insertParameter;
					}
				}

				$mtmDbTableName	= $this->databaseTableName . '_' . $field['value']['mtm']['table'] . '_' . $field['value']['mtm']['reference'];

				if(count($insertParams) > 0)
				{
					if($ignoreDublicate)
					{
						$this->db->insertignore($mtmDbTableName, $insertParams);
					}else{
						$this->db->insert($mtmDbTableName, $insertParams);
					}
				}
			}
		}else{
			foreach($mtmFields as $field)
			{
				$insertParams		= [];
				$fieldName			= $field['name'];
				$fieldReference		= $field['value']['mtm']['reference'];
				$mtmDbTableName		= $this->databaseTableName . '_' . $field['value']['mtm']['table'] . '_' . $fieldReference;

				if(is_array($this->$fieldName))
				{
					$currentMtmValues			= $this->db->query('select ' . $fieldReference . '_mtm as ' . $fieldReference . ' from ' . $mtmDbTableName . ' where ' . $fieldName . ' = ?', array($pkValue))->result();
					$willHaveDeletedObjects		= [];
					$haveObjects				= [];

					foreach($this->$fieldName as $haveObject)
					{
						$haveObjects[]			= $haveObject->pk;
					}

					foreach($currentMtmValues as $mtmValue)
					{
						$referenceValue			= $mtmValue->$fieldReference;

						$search				= array_search($referenceValue, $haveObjects);
						if($search !== false)
						{
							unset($haveObjects[$search]);
						}else{
							$willHaveDeletedObjects[]		= $referenceValue;
						}
					}

					foreach($haveObjects as $insertObject)
					{
						$insertParameter		= [$fieldName => $pkValue, $field['value']['mtm']['reference'] . '_mtm' => $insertObject];
						$insertParams[]			= $insertParameter;
					}

					if(count($willHaveDeletedObjects) > 0)
					{
						$deleteSqlString		= 'delete from ' . $mtmDbTableName . ' where ' .  $fieldName . ' = ? and ' . $field['value']['mtm']['reference'] . '_mtm in (?)';
						$referencesString		= join(', ', $willHaveDeletedObjects);
						$this->db->query($deleteSqlString, array($pkValue, $referencesString), true);
					}
				}

				if(count($insertParams) > 0) {
					if ($ignoreDublicate) {
						$this->db->insertignore($mtmDbTableName, $insertParams);
					} else {
						$this->db->insert($mtmDbTableName, $insertParams);
					}
				}
			}
		}
	}
}

/**
* ------------------------------------------------
* End of file plf_db.php
* ------------------------------------------------
*/
