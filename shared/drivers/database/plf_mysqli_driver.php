<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
* ------------------------------------------------
* Mysql Improved Driver
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class PLF_Mysqli_driver extends PLF_Db
{


/**
* ------------------------------------------------
* Database variables
* ------------------------------------------------
* 
* @author ilker özcan
* @var $result
* @var $resultData
* @var $multiQuery bool
* 
*/
	
	protected $result;
	protected $resultData;
	protected $multiQuery;


/**
* ------------------------------------------------
* Database connection
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/
	
	public function __construct()
	{
		parent::__construct();
		if(!extension_loaded('mysqli'))
		{
			$this->dbError(0, 'mysqli extension not found on your server');
		}

		$this->connect();
	}
	
	
/**
* ------------------------------------------------
* Connect database
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function connect()
	{
		$this->connection	= @new mysqli(Database::$hostname , Database::$username , Database::$password , Database::$database , Database::$port , NULL);
		
		if($this->connection->connect_errno)
		{
			$this->dbError($this->connection->connect_errno, $this->connection->connect_error);
		}else{
			$this->connection->set_charset(Database::$char_set);

			if(Database::$dbTransaction)
				$this->transaction();
		}
	}
	
	
/**
* ------------------------------------------------
* Close current connection
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	protected function disconnect()
	{
		if(!isset($this->connection))
			return;

		if(!$this->connection->connect_errno)
		{
			$this->connection->close();
			unset($this->connection);
		}
	}
	
/**
* ------------------------------------------------
* Reconnect database
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function reconnect()
	{
		$this->disconnect();
		$this->connect();
	}


/**
* ------------------------------------------------
* Destruct method
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function destruct()
	{
		if(Database::$dbTransaction && !is_null($this->lastQuery))
			if(isset($this->connection))
				$this->commit();

		$this->disconnect();
	}
	
	
/**
* ------------------------------------------------
* Return last insert id
* ------------------------------------------------
* 
* @author ilker özcan
* @return integer
* 
*/

	public function insertId()
	{
		return $this->connection->insert_id;
	}


/**
* ------------------------------------------------
* Gets the number of rows in a result
* ------------------------------------------------
* 
* @author ilker özcan
* @return integer
* 
*/

	public function numRows()
	{
		if(isset($this->result))
		{
			return (!is_null($this->result->num_rows)) ? $this->result->num_rows : 0;
		}
		
		return 0;
	}


/**
* ------------------------------------------------
* Return affected rows
* ------------------------------------------------
* 
* @author ilker özcan
* @return integer
* 
*/

	public function affectedRows()
	{
		return $this->connection->affected_rows;
	}
	

/**
* ------------------------------------------------
* Execute query
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function query($sql, $binds = NULL, $freeResult = FALSE)
	{
		$this->multiQuery	= FALSE;
		$this->lastQuery	= $this->prepare($sql, $binds);
		if(!$this->result = $this->connection->query($this->lastQuery))
		{
			$this->dbError($this->connection->errno, $this->connection->error, $this->lastQuery);
		}else{
			/*if($freeResult)
			{
				$this->result->free();
				return FALSE;
			}else{
			}*/
			return $this;
		}
	}


/**
* ------------------------------------------------
* Execute stored procedure
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function mquery($sql, $binds = NULL, $freeResult = FALSE)
	{
		$this->multiQuery	= TRUE;
		$this->lastQuery	= $this->prepare($sql, $binds);

		if(!$this->result = $this->connection->multi_query($this->lastQuery))
		{
			$this->dbError($this->connection->errno, $this->connection->error, $this->lastQuery);
		}else{
			if($freeResult)
			{
				do{
					if ($result = $this->connection->store_result())
					{
            			$result->free();
					}
					
					if (!$this->connection->more_results())
					{
						break;
					}
    			} while ($this->connection->next_result());
				
				return FALSE;
			}else{
				return $this;
			}
		}
	}


/**
* ------------------------------------------------
* Return first row for last query
* ------------------------------------------------
* 
* @author ilker özcan
* @return array
* 
*/

	public function row($type = 'object')
	{
		if(isset($this->result))
        {
            switch ($type)
            {
			case 'array':
				$this->resultData		= $this->result->fetch_array();
			break;
			default:
				$this->resultData		= $this->result->fetch_object();
			break;
            }
			
			$this->result->close();
			
            return $this->resultData;
        }
    
        return FALSE;
	}


/**
* ------------------------------------------------
* Start transaction
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/	

	public function transaction()
	{
		$this->isTransactionStart		= TRUE;
		$this->transactionError			= FALSE;
		$this->connection->autocommit(FALSE);
	}


/**
* ------------------------------------------------
* Commit transaction
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/	

	public function commit()
	{
		$this->isTransactionStart		= FALSE;
		if(!$this->connection->commit())
		{
			$this->rollback();
			$this->disconnect();
			$this->dbError($this->connection->errno, $this->connection->error, $this->lastQuery);
		}
	}


/**
* ------------------------------------------------
* Rollback transaction
* ------------------------------------------------
* 
* @author ilker özcan
* 
*/

	public function rollback()
	{
		$this->isTransactionStart		= FALSE;
		if(!$this->connection->rollback())
		{
			$this->dbError($this->connection->errno, $this->connection->error, $this->lastQuery);
		}
	}


/**
* ------------------------------------------------
* Return last query results
* ------------------------------------------------
* 
* @author ilker özcan
* @return array
* 
*/

	protected function singleQueryResult($type)
	{
		if(isset($this->result))
        {
			
			$this->resultData	= array();
			
            switch ($type)
            {
			case 'array':
				while($row = $this->result->fetch_array())
				{
					$this->resultData[]		= $row;
				}
			break;
			default:
				while($row = $this->result->fetch_object())
				{
					$this->resultData[]		= $row;
				}
			break;
            }
			
			$this->result->close();
			
            return $this->resultData;
        }
    
        return FALSE;
	}


/**
* ------------------------------------------------
* Return last multi query results
* ------------------------------------------------
* 
* @author ilker özcan
* @return array
* 
*/
	
	protected function multiQueryResult()
	{
		if(isset($this->result))
        {
			
			$this->resultData	= array();
			$resultId			= 0;
			
			do{
				if ($result = $this->connection->store_result())
				{
					$tmpResult			= array();
					
            		while ($row = $result->fetch_object())
					{
                		$tmpResult[]	= $row;
            		}
					
            		$result->free();
				}
        		if ($this->connection->more_results())
				{
					$resultId++;
					$resultName						= 'result'.$resultId;
					$this->resultData[$resultName]	= $tmpResult;
        		}else{
					$resultId++;
					$resultName						= 'result'.$resultId;
					$this->resultData[$resultName]	= $tmpResult;
            		break;
				}
    		} while ($this->connection->next_result());
			
			return $this->resultData;
        }
    
        return FALSE;
	}


/**
* ------------------------------------------------
* Insert row
* ------------------------------------------------
*
* @author ilker özcan
* @param string $tableName
* @param array $params
* @return bool
*
 */

	public function insert($tableName, $params = array())
	{
		if(is_array($params))
		{
			$keyArray		= [];
			$valuesString	= '';

			foreach($params as $paramKey => $paramValue)
			{
				if(is_array($paramValue))
				{
					$tmpValueString		= '';
					foreach($paramValue as $paramObjectKey => $paramObjectVal)
					{
						if(!in_array($paramObjectKey, $keyArray))
						{
							$keyArray[]		= $paramObjectKey;
						}
						$tmpValueString	.= $this->escape_str($paramObjectVal).',';
					}

					$tmpValueString		= substr($tmpValueString, 0, -1);
					$valuesString		.= '(' . $tmpValueString . '),';
				}else{
					$keyArray[]		= $paramKey;
					$valuesString	.= $this->escape_str($paramValue).',';
				}
			}

			$keyString		= join(', ', $keyArray);
			$valuesString	= substr($valuesString, 0, -1);

			if(substr($valuesString, 0, 1) != '(')
				$valuesString	= '(' . $valuesString . ')';
			else
				$valuesString	= $valuesString . ';';


			$query			= 'insert into '.$tableName.'('.$keyString.') values '.$valuesString;
			$this->query($query);

			return TRUE;
		}
		return FALSE;
	}

	/**
	 * ------------------------------------------------
	 * Insert row if row exists ignore
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 * @param string $tableName
	 * @param array $params
	 * @return bool
	 *
	 */

	public function insertignore($tableName, $params = array())
	{
		if(is_array($params))
		{
			$keyArray		= [];
			$valuesString	= '';

			foreach($params as $paramKey => $paramValue)
			{
				if(is_array($paramValue))
				{
					$tmpValueString		= '';
					foreach($paramValue as $paramObjectKey => $paramObjectVal)
					{
						if(!in_array($paramObjectKey, $keyArray))
						{
							$keyArray[]		= $paramObjectKey;
						}
						$tmpValueString	.= $this->escape_str($paramObjectVal).',';
					}

					$tmpValueString		= substr($tmpValueString, 0, -1);
					$valuesString		.= '(' . $tmpValueString . '),';
				}else{
					$keyArray[]		= $paramKey;
					$valuesString	.= $this->escape_str($paramValue).',';
				}
			}

			$keyString		= join(', ', $keyArray);
			$valuesString	= substr($valuesString, 0, -1);

			if(substr($valuesString, 0, 1) != '(')
				$valuesString	= '(' . $valuesString . ')';
			else
				$valuesString	= $valuesString . ';';

			$query			= 'insert ignore into '.$tableName.'('.$keyString.') values '.$valuesString;
			$this->query($query);

			return TRUE;
		}
		return FALSE;
	}

	/**
	 * ------------------------------------------------
	 * Update row
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 * @param string $tableName
	 * @param array $params
	 * @param array $where
	 * @return int affected rows
	 *
	 */

	public function update($tableName, $params, $where = array())
	{
		if(is_array($params) && is_array($where))
		{
			$paramStrings		= '';
			$whereStrings		= '';

			foreach($params as $pkey => $pval)
			{
				$paramStrings		.= $pkey.'='.$this->escape_str($pval).',';
			}

			$paramStrings		= substr($paramStrings, 0, -1);

			$whereClause		= FALSE;

			if(count($where) > 0)
			{
				$whereClause	= TRUE;

				foreach($where as $wkey => $wval)
				{
					$whereWithOperator	= $wkey;
					$whereOperator		= substr($wkey, -2);

					switch($whereOperator)
					{
						case '<=':
						case '>=':
						case ' =':
						case ' >':
						case ' <':
						case '!=':
							$whereWithOperator		= $whereWithOperator . ' ';
							break;
						default:
							$whereWithOperator		= $whereWithOperator . ' = ';
							break;
					}

					$whereStrings		.= $whereWithOperator . $this->escape_str($wval) . ' and';
				}

				$whereStrings	= substr($whereStrings, 0, -4);
			}

			$query				= 'update '.$tableName.' set '.$paramStrings.' ';
			if($whereClause)
			{
				$query			.= 'where '.$whereStrings;
			}

			$this->query($query);

			return $this->affectedRows();
		}
		return FALSE;
	}


	/**
	 * ------------------------------------------------
	 * Delete row
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 * @param string $tableName
	 * @param array $where
	 * @return int affected rows
	 *
	 */

	public function delete($tableName, $where)
	{
		if(is_array($where))
		{
			$whereStrings		= '';

			foreach($where as $wkey => $wval)
			{
				$whereWithOperator	= $wkey;
				$whereOperator		= substr($wkey, -2);

				switch($whereOperator)
				{
					case '<=':
					case '>=':
					case ' =':
					case ' >':
					case ' <':
					case '!=':
						$whereWithOperator		= $whereWithOperator . ' ';
						break;
					default:
						$whereWithOperator		= $whereWithOperator . ' = ';
						break;
				}
				$whereStrings		.= $whereWithOperator . $this->escape_str($wval).' and ';
			}

			$whereStrings	= substr($whereStrings, 0, -4);

			$query				= 'delete from '.$tableName.' where '.$whereStrings;

			$this->query($query);

			return $this->affectedRows();
		}
		return FALSE;
	}


	/**
	 * ------------------------------------------------
	 * Active record select query generator
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function select($tableName, $pk, $fields, $joinedTables, $predicate, $sortDescriptors, $limit, $mtmField)
	{
		$sqlString							= 'SELECT ';
		if(!is_null($fields))
		{
			$selectedColumns				= [];

			foreach($fields as $field)
			{
				$fieldTableAndColumn		= explode('.', $field);

				if(count($fieldTableAndColumn) < 2)
				{
					$fieldTableAndColumnString		= $tableName . '.' . $field;
				}else{
					$fieldTableAndColumnString		= $field;
				}

				$selectedColumns[]			= $fieldTableAndColumnString;
			}

			$sqlString						.= implode(',', $selectedColumns) . ' ';
		}else{
			$sqlString						.= '* ';
		}

		$sqlString							.= 'FROM ' . $tableName . ' ';

		if(!is_null($joinedTables))
		{
			foreach($joinedTables as $joinedTable)
			{
				$joinType					= '';
				if(strtolower($joinedTable['type']) != 'join')
				{
					$joinType				= $joinedTable['type'];
				}

				$sqlString					.= $joinType . ' JOIN ' . $joinedTable['table'] . ' ';
				$sqlString					.= 'on ' . $joinedTable['predicate'] . ' ';
			}
		}

		$predicateValues					= null;
		if(!is_null($predicate))
		{
			$sqlString						.= 'WHERE ';
			$sqlString						.= $predicate['predicate'] . ' ';
			$predicateValues				= $predicate['values'];
		}else{
			if(!is_null($pk))
			{
				$sqlString					.= 'WHERE ';
				$sqlString					.= $tableName . '.' . $pk['name'] . ' = ? ';
				$predicateValues[]			= $pk['value'];
			}
		}

		if(!is_null($sortDescriptors))
		{
			$sqlString						.= 'ORDER BY ';
			$sortDescriptorStrings			= [];
			foreach($sortDescriptors as $sortDescriptor)
			{
				$sortDescriptorString		= $sortDescriptor['column'] . ' ' . ( ($sortDescriptor['ascending'])?'ASC':'DESC' );
				$sortDescriptorStrings[]	= $sortDescriptorString;
			}

			$sqlString						.= implode(', ', $sortDescriptorStrings) . ' ';
		}

		if(!is_null($limit))
		{
			$sqlString						.= 'LIMIT ' . $limit;
		}

		if(!is_null($mtmField) && !is_null($pk))
		{
			$multiQueryPredicateValues		= $predicateValues;
			$multiQueryPredicateValues[]	= $pk['value'];
			$referenceProperties			= [];
			foreach($mtmField['properties'] as $property)
			{
				$referenceProperties[]		= 'ref.' . $property;
			}
			$referenceProperties[0]	= $referenceProperties[0] . ' as pk';
			$mtmPropertiesString	= join(', ', $referenceProperties);
			$queryString			= $sqlString . ';';
			$queryString			.= 'select ' . $mtmPropertiesString . ' from ' . $mtmField['dbName'] . ' mtm ';
			$queryString			.= 'left join ' . $mtmField['field']['value']['mtm']['table'] . ' ref ';
			$queryString			.= 'on ref.' . $mtmField['field']['value']['mtm']['reference'] . ' = ';
			$queryString			.= 'mtm.' . $mtmField['field']['value']['mtm']['reference'] . '_mtm ';
			$queryString			.= 'where mtm.' . $mtmField['field']['name'] . ' = ?;';
			return $this->mquery($queryString, $multiQueryPredicateValues);
		}else{
			return $this->query($sqlString, $predicateValues);
		}

	}


	/**
	 * ------------------------------------------------
	 * Return tables in database for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function getTableList()
	{
		$tableList			= $this->query('show tables')->result('array');
		$responseValue		= [];

		foreach($tableList as $table)
		{
			$responseValue[]		= $table[0];
		}

		return $responseValue;
	}


	/**
	 * ------------------------------------------------
	 * Return table create statement for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function getCreateSteatment($dbName, $tbObjects)
	{
		$sqlString				= 'CREATE TABLE `' . $dbName . '` (';

		$indexedObjects			= [];
		$uniqueKeyFields		= [];
		$primaryKeyField		= '';
		$currentArrayIdx		= 0;

		foreach($tbObjects as $column)
		{
			$columnOtherProperties		= '';

			if(isset($column['value']['mtm']))
			{
				continue;
			}

			if(isset($column['value']['un']))
			{
				if($column['value']['un'])
				{
					$columnOtherProperties		.= 'unsigned ';
				}
			}

			if(isset($column['value']['nn']))
			{
				if($column['value']['nn'])
				{
					$columnOtherProperties		.= 'NOT NULL ';
				}else{
					$columnOtherProperties		.= 'NULL ';
				}
			}else{
				$columnOtherProperties		.= 'NULL ';
			}

			if(isset($column['value']['default']))
			{
				if(preg_match('/date/i', $column['value']['type']) || preg_match('/time/i', $column['value']['type']))
				{
					$columnOtherProperties		.= 'DEFAULT ' . $column['value']['default'] . ' ';
				}else{
					$columnOtherProperties		.= 'DEFAULT \'' . $column['value']['default'] . '\' ';
				}
			}

			if(isset($column['value']['ai']))
			{
				if($column['value']['ai'])
				{
					$columnOtherProperties		.= 'AUTO_INCREMENT ';
				}
			}

			if(isset($column['value']['pk']))
			{
				if($column['value']['pk'])
				{
					$primaryKeyField			= $column['name'];
				}
			}

			$sqlString					.= '`' . $column['name'] . '` ' . $column['value']['type'] . ' ' . $columnOtherProperties . ',';

			if(isset($column['value']['indexed']))
			{
				if($column['value']['indexed'])
				{
					$indexedObjects[]			= $currentArrayIdx;
				}
			}

			if(isset($column['value']['uq']))
			{
				if($column['value']['uq'])
				{
					$uniqueKeyFields[]			= $currentArrayIdx;
				}
			}

			$currentArrayIdx++;
		}

		$sqlString				.= 'PRIMARY KEY (`' . $primaryKeyField . '`)';

		foreach($uniqueKeyFields as $uniqueKeyField)
		{
			$sqlString			.= ', UNIQUE KEY `' . $dbName . '_' . $tbObjects[$uniqueKeyField]['name'] . '_UNIQUE`(`' . $tbObjects[$uniqueKeyField]['name'] . '`)';
		}

		foreach($indexedObjects as $indexedField)
		{
			$sqlString			.= ', KEY `' . $dbName . '_' . $tbObjects[$indexedField]['name'] . '_IDX`(`' . $tbObjects[$indexedField]['name'] . '`)';
		}

		$sqlString				.= ') ENGINE=InnoDB DEFAULT CHARSET=' . Database::$char_set . ';';
		return $sqlString;
	}


	/**
	 * ------------------------------------------------
	 * Return column and index list for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function getColumnAndIndexList($tableName)
	{
		$results			= $this->mquery('SHOW COLUMNS FROM `' . $tableName . '`;SHOW INDEXES FROM `' . $tableName . '`;
		SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
		FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
		WHERE
  		TABLE_NAME = ?;', array($tableName))->multiQueryResult();

		// create pretty response for manager class
		// all database drivers should return same response
		$responseData					= new stdClass();
		$responseData->columns			= [];
		$responseData->indexes			= [];
		$responseData->foreignKeys		= [];

		if(isset($results['result1'])) {
			foreach ($results['result1'] as $columnData) {
				$tmpColumnData = new stdClass();
				$tmpColumnData->name = $columnData->Field;
				$tmpColumnData->type = $columnData->Type;
				$tmpColumnData->un = false;

				if (preg_match('/unsigned/i', $columnData->Type)) {
					$columnTypeData = explode(' ', $columnData->Type);
					$tmpColumnData->type = $columnTypeData[0];
					$tmpColumnData->un = true;
				}

				$tmpColumnData->nn = ($columnData->Null == 'YES') ? false : true;
				$tmpColumnData->default = $columnData->Default;
				$tmpColumnData->ai = ($columnData->Extra == 'auto_increment') ? true : false;
				$tmpColumnData->pk = ($columnData->Key == 'PRI') ? true : false;
				$tmpColumnData->uq = ($columnData->Key == 'UNI') ? true : false;
				$tmpColumnData->indexed = ($columnData->Key == 'MUL') ? true : false;
				$responseData->columns[] = $tmpColumnData;
			}
		}

		if(isset($results['result2'])) {
			foreach ($results['result2'] as $indexData) {
				$tmpIndexData = new stdClass();
				$tmpIndexData->columnName = $indexData->Column_name;
				$tmpIndexData->uniqueIndex = ($indexData->Non_unique == 1) ? false : true;
				$tmpIndexData->indexName = $indexData->Key_name;
				$tmpIndexData->isPrimaryIndex = ($indexData->Key_name == 'PRIMARY') ? true : false;
				$responseData->indexes[] = $tmpIndexData;
			}
		}

		if(isset($results['result3'])) {
			foreach ($results['result3'] as $foreignKeyData) {

				if (is_null($foreignKeyData->REFERENCED_TABLE_NAME) || empty($foreignKeyData->REFERENCED_TABLE_NAME))
					continue;

				$tmpForeignKeyData = new stdClass();
				$tmpForeignKeyData->columnName = $foreignKeyData->COLUMN_NAME;
				$tmpForeignKeyData->foreignKeyName = $foreignKeyData->CONSTRAINT_NAME;
				$tmpForeignKeyData->referenceTable = $foreignKeyData->REFERENCED_TABLE_NAME;
				$tmpForeignKeyData->referenceColumn = $foreignKeyData->REFERENCED_COLUMN_NAME;
				$responseData->foreignKeys[] = $tmpForeignKeyData;
			}
		}

		return $responseData;
	}


	/**
	 * ------------------------------------------------
	 * Update column for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function addOrUpdateColumn($tableName, $tableObject, $isUpdate = false)
	{
		$sqlString			= 'ALTER TABLE `' . $tableName . '` ';
		if(!$isUpdate)
		{
			$sqlString		.= 'ADD COLUMN ';
		}else{
			$sqlString		.= 'CHANGE COLUMN `' . $tableObject['name'] . '` ';
		}

		$sqlString		.= '`' . $tableObject['name'] . '` ';
		$objectValue	= $tableObject['value'];
		$sqlString		.= $objectValue['type'] . ' ';

		$columnOtherProperties		= '';
		if(isset($objectValue['un']))
		{
			if($objectValue['un'])
			{
				$columnOtherProperties		.= 'unsigned ';
			}
		}

		if(isset($objectValue['nn']))
		{
			if($objectValue['nn'])
			{
				$columnOtherProperties		.= 'NOT NULL ';
			}else{
				$columnOtherProperties		.= 'NULL ';
			}
		}else{
			$columnOtherProperties		.= 'NULL ';
		}

		if(isset($objectValue['default']))
		{
			if(preg_match('/date/i', $objectValue['type']) || preg_match('/time/i', $objectValue['type']))
			{
				$columnOtherProperties		.= 'DEFAULT ' . $objectValue['default'] . ' ';
			}else{
				$columnOtherProperties		.= 'DEFAULT \'' . $objectValue['default'] . '\' ';
			}
		}

		if(isset($objectValue['ai']))
		{
			if($objectValue['ai'])
			{
				$columnOtherProperties		.= 'AUTO_INCREMENT ';
			}
		}

		if(isset($objectValue['pk']))
		{
			if($objectValue['pk'])
			{
				$columnOtherProperties		.= ', DROP PRIMARY KEY, ';
				$columnOtherProperties		.= 'ADD PRIMARY KEY (`' . $tableName . '`, `' . $tableObject['name'] . '`)';
			}
		}

		$sqlString					.= $columnOtherProperties . ';';
		$this->query($sqlString, null, true);
	}


	/**
	 * ------------------------------------------------
	 * Remove index for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function removeIndex($tableName, $indexName)
	{
		$sql			= 'ALTER TABLE `' . $tableName . '` DROP INDEX `' . $indexName . '`;';
		$this->query($sql, null, false);
	}


	/**
	 * ------------------------------------------------
	 * add index for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function addIndex($tableName, $indexName, $columnName)
	{
		$sql			= 'ALTER TABLE `' . $tableName . '` ADD INDEX `' . $indexName . '`(`' . $columnName . '`);';
		$this->query($sql, null, false);
	}


	/**
	 * ------------------------------------------------
	 * Remove foreign key for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function removeForeignKey($tableName, $foreignKeyName)
	{
		$sql			= 'ALTER TABLE `' . $tableName . '` DROP FOREIGN KEY `' . $foreignKeyName . '`;';
		$this->query($sql, null, false);
	}


	/**
	 * ------------------------------------------------
	 * Add foreign key for sync manager
	 * ------------------------------------------------
	 *
	 * @author ilker özcan
	 *
	 */

	public function addForeignKey($tableName, $foreignKeyName, $columnName, $referanceTableName, $referanceColumn)
	{
		$sql			= 'ALTER TABLE `' . $tableName . '` ADD CONSTRAINT `' . $foreignKeyName . '` FOREIGN KEY(`' . $columnName . '`) REFERENCES `' . $referanceTableName . '`(`' . $referanceColumn . '`) ON DELETE CASCADE;';
		$this->query($sql, null, false);
	}

}


/**
* ------------------------------------------------
* End of file plf_mysqli_driver.php
* ------------------------------------------------
*/