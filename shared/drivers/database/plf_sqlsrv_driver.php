<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
* ------------------------------------------------
* Sqlsrv Driver
* ------------------------------------------------
* 
* @author ilker ozcan
* 
*/

class PLF_sqlsrv_driver extends PLF_Db
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

		throw new Exception('sqlsrv is not available!');

		if(!extension_loaded('sqlsrv'))
		{
			$this->dbError(0, 'sqlsrv extension not found on your server');
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
		if(Database::$port == NULL)
		{
			$serverName			= Database::$hostname;
		}else{
			$serverName			= Database::$hostname.', '.Database::$port;
		}
		
		$connectionInfo			= array(
										'Database'		=> Database::$database, 
										'UID'			=> Database::$username,
										'PWD'			=> Database::$password,
										'CharacterSet'	=> Database::$char_set
								);

		$this->connection		= @sqlsrv_connect( $serverName, $connectionInfo);
	
		if(!$this->connection)
		{
			$error				= sqlsrv_errors();
			$this->dbError($error[0]['code'], $error[0]['message']);
		}else{
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
		if($this->connection)
		{
			sqlsrv_close($this->connection);
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
		if(Database::$dbTransaction)
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
		$this->query('SELECT @@IDENTITY as ID');
		return $this->row()->ID;
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

	public function num_rows()
	{
		if(isset($this->result))
		{
			return sqlsrv_num_rows($this->result);
		}
		
		return FALSE;
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

	public function affected_rows()
	{
		if(isset($this->result))
		{
			return sqlsrv_rows_affected($this->result);
		}
		
		return FALSE;
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
		$params				= array();
		$options			= array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		if(!$this->result = sqlsrv_query($this->connection, $this->lastQuery, $params, $options))
		{
			$error				= sqlsrv_errors();
			$this->dbError($error[0]['code'], $error[0]['message'], $this->lastQuery);
		}else{
			if($freeResult)
			{
				sqlsrv_free_stmt($this->result);
				return FALSE;
			}else{
				return $this;
			}
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
		$params				= array();
		$options			= array();
		if(!$this->result = sqlsrv_query($this->connection, $this->lastQuery, $params, $options))
		{
			$error				= sqlsrv_errors();
			$this->dbError($error[0]['code'], $error[0]['message'], $this->lastQuery);
		}else{
			if($freeResult)
			{
				sqlsrv_free_stmt($this->result);
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
				$this->resultData		= sqlsrv_fetch_array($this->result);
			break;
			default:
				$this->resultData		= sqlsrv_fetch_object($this->result);
			break;
            }
			
			sqlsrv_free_stmt($this->result);
			
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
		sqlsrv_begin_transaction($this->connection);
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
		sqlsrv_commit($this->connection);
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
		sqlsrv_rollback($this->connection);
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
				while($row = sqlsrv_fetch_array($this->result))
				{
					$this->resultData[]		= $row;
				}
			break;
			default:
				while($row = sqlsrv_fetch_object($this->result))
				{
					$this->resultData[]		= $row;
				}
			break;
            }
			
			sqlsrv_free_stmt($this->result);
			
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
				$tmpResult			= array();
				
            	while ($row = sqlsrv_fetch_object($this->result))
				{
                	$tmpResult[]	= $row;
            	}
				
				$resultId++;
				$resultName						= 'result'.$resultId;
				$this->resultData[$resultName]	= $tmpResult;

    		} while (sqlsrv_next_result($this->result));
			
			sqlsrv_free_stmt($this->result);
			
			return $this->resultData;
        }
    
        return FALSE;
	}
}


/**
* ------------------------------------------------
* End of file plf_mysqli_driver.php
* ------------------------------------------------
*/