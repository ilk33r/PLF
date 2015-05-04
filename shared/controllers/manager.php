<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * PLF Install Manager For Command Line
 * ------------------------------------------------
 *
 * @author ilker ozcan
 *
 */

class Manager extends PLF_Controller
{

	public static $cli		= true;

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('UTC');
	}

	public function index()
	{
		global $argv;
		if(isset($argv[2]))
		{
			switch ($argv[2]) {
				case 'createapp':

					if(isset($argv[3]))
						$this->createApp($argv[3]);
					else
						$this->sendError('Application name is invalid');

					break;
				case 'createcontroller':

					if(isset($argv[3]))
						$this->createController($argv[3]);
					else
						$this->sendError('Controller name is invalid');

					break;
				case 'createmodel':

					if(isset($argv[3]))
						$this->createModel($argv[3]);
					else
						$this->sendError('Model name is invalid');

					break;
				case 'createview':

					if(isset($argv[3]))
						$this->createView($argv[3]);
					else
						$this->sendError('View name is invalid');

					break;
				case 'createrootuser':
					$this->createUsersTable();

					break;
				case 'changeuserpassword':

					if(isset($argv[3]))
					{
						$this->changeUserPassword($argv[3]);

					} else
						$this->sendError('View name is invalid');

					$this->createUsersTable();

					break;
				case 'createadminpage':

					if(isset($argv[3]))
						$this->createAdminPage($argv[3]);
					else
						$this->sendError('Admin page name is invalid');

					break;
				case 'syncdb':
					$this->syncDb(false);
					break;
				default:
					$this->sendError();
					break;
			}
		}else{
			$this->sendError();
		}
	}

	private function sendError($error = '')
	{
		echo "Usage: \n";
		echo "manager createapp [Application Name] \n";
		echo "manager createcontroller [Controller Name]\n";
		echo "manager createmodel [Model Name]\n";
		echo "manager createview [View Name]\n";
		echo "manager createadminpage [Admin Page Name]\n";
		echo "manager syncdb\n";
		echo "manager createrootuser\n";
		echo "manager changeuserpassword [User Name]\n";
		echo $error;
	}

	private function createApp($appName)
	{
		$newAppPath		= BASEPATH . 'app/' . $appName;

		if(file_exists($newAppPath))
		{
			echo "Application exists !\n";
			exit;
		}

		if(mkdir($newAppPath))
		{
			mkdir($newAppPath . '/admin');
			mkdir($newAppPath . '/config');
			mkdir($newAppPath . '/controllers');
			mkdir($newAppPath . '/drivers');
			mkdir($newAppPath . '/helpers');
			mkdir($newAppPath . '/languages');
			mkdir($newAppPath . '/libraries');
			mkdir($newAppPath . '/models');
			mkdir($newAppPath . '/traits');
			mkdir($newAppPath . '/views');

			copy(BASEPATH . 'core/index.html', $newAppPath . '/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/admin/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/config/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/controllers/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/drivers/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/helpers/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/languages/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/libraries/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/models/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/traits/index.html');
			copy(BASEPATH . 'core/index.html', $newAppPath . '/views/index.html');

			copy(BASEPATH . 'core/install/autoload.plf', $newAppPath . '/config/autoload.php');
			copy(BASEPATH . 'core/install/cacheconfig.plf', $newAppPath . '/config/cacheconfig.php');
			copy(BASEPATH . 'core/install/database.plf', $newAppPath . '/config/database.php');
			copy(BASEPATH . 'core/install/membershipconfig.plf', $newAppPath . '/config/membershipconfig.php');
			copy(BASEPATH . 'core/install/output.plf', $newAppPath . '/config/output.php');
			copy(BASEPATH . 'core/install/route.plf', $newAppPath . '/config/route.php');
			copy(BASEPATH . 'core/install/adminconfig.plf', $newAppPath . '/config/adminconfig.php');

			$encryptionKey		= md5(mt_rand() . 'PLF - ' . time() . ' - ' . rand() . ' - ' . mt_rand());
			$configFile			= file_get_contents(BASEPATH . 'core/install/config.plf');
			$configFileWithKey	= '';

			if(preg_match('/\{\{(?P<name>\w+)\}\}/i', $configFile, $matches))
			{
				$configFileWithKey	= preg_replace('/\{\{'. $matches['name'] .'\}\}/i', $encryptionKey, $configFile);
			}else{
				$configFileWithKey	= $configFile;
			}

			$fh					= fopen($newAppPath . '/config/config.php', 'w');
			fwrite($fh, $configFileWithKey);
			fclose($fh);

			echo "Application created!\n";
			echo "OK\n";

		}else{
			echo "Directory could not created!\n";
		}

	}

	private function createController($controllerName)
	{
		$appAppPath		= BASEPATH . 'app/' . APPLICATIONNAME;

		if(!file_exists($appAppPath))
		{
			echo "Application could not found !\n";
			exit;
		}

		$lowerControllerName	= strtolower($controllerName);
		$controllerPath			= $appAppPath . '/controllers/' . $lowerControllerName . '.php';

		if(file_exists($controllerPath))
		{
			echo "Controller exists ! \n";
			exit;
		}

		$controllerFile			= file_get_contents(BASEPATH . 'core/install/controller.plf');

		if(preg_match_all('/\{\{(?P<name>\w+)\}\}/i', $controllerFile, $matches))
		{
			foreach($matches['name'] as $keys)
			{
				if($keys == 'CONTROLLER_NAME')
				{
					$controllerClassName	= ucfirst($lowerControllerName);
					$controllerFile			= preg_replace('/\{\{'. $keys .'\}\}/i', $controllerClassName, $controllerFile);
				}elseif($keys == 'APPLICACITON_NAME')
				{
					$controllerFile			= preg_replace('/\{\{'. $keys .'\}\}/i', APPLICATIONNAME, $controllerFile);
				}elseif($keys == 'CREATE_DATE')
				{
					$createDate				= date('M j y H:i');
					$controllerFile			= preg_replace('/\{\{'. $keys .'\}\}/i', $createDate, $controllerFile);
				}
			}
		}

		$fh					= fopen($controllerPath, 'w');
		fwrite($fh, $controllerFile);
		fclose($fh);

		echo "Controller created!\n";
		echo "OK\n";
	}

	private function createModel($modelName)
	{
		$appAppPath		= BASEPATH . 'app/' . APPLICATIONNAME;

		if(!file_exists($appAppPath))
		{
			echo "Application could not found !\n";
			exit;
		}

		$lowerModelName			= strtolower($modelName);
		$modelPath				= $appAppPath . '/models/' . $lowerModelName . '.php';

		if(file_exists($modelPath))
		{
			echo "Model exists ! \n";
			exit;
		}

		$modelFile				= file_get_contents(BASEPATH . 'core/install/model.plf');

		if(preg_match_all('/\{\{(?P<name>\w+)\}\}/i', $modelFile, $matches))
		{
			foreach($matches['name'] as $keys)
			{
				if($keys == 'MODEL_NAME')
				{
					$controllerClassName	= ucfirst($lowerModelName);
					$modelFile				= preg_replace('/\{\{'. $keys .'\}\}/i', $controllerClassName, $modelFile);
				}elseif($keys == 'APPLICACITON_NAME')
				{
					$modelFile				= preg_replace('/\{\{'. $keys .'\}\}/i', APPLICATIONNAME, $modelFile);
				}elseif($keys == 'CREATE_DATE')
				{
					$createDate				= date('M j y H:i');
					$modelFile				= preg_replace('/\{\{'. $keys .'\}\}/i', $createDate, $modelFile);
				}
			}
		}

		$fh					= fopen($modelPath, 'w');
		fwrite($fh, $modelFile);
		fclose($fh);

		echo "Model created!\n";
		echo "OK\n";
	}

	private function createView($viewName)
	{
		$appAppPath		= BASEPATH . 'app/' . APPLICATIONNAME;

		if(!file_exists($appAppPath))
		{
			echo "Application could not found !\n";
			exit;
		}

		$lowerViewName			= strtolower($viewName);
		$viewPath				= $appAppPath . '/views/' . $lowerViewName . '.php';

		if(file_exists($viewPath))
		{
			echo "View exists ! \n";
			exit;
		}

		$viewFile				= file_get_contents(BASEPATH . 'core/install/view.plf');

		if(preg_match_all('/\{\{(?P<name>\w+)\}\}/i', $viewFile, $matches))
		{
			foreach($matches['name'] as $keys)
			{
				if($keys == 'VIEW_NAME')
				{
					$controllerClassName	= ucfirst($lowerViewName);
					$viewFile				= preg_replace('/\{\{'. $keys .'\}\}/i', $controllerClassName, $viewFile);
				}elseif($keys == 'APPLICACITON_NAME')
				{
					$viewFile				= preg_replace('/\{\{'. $keys .'\}\}/i', APPLICATIONNAME, $viewFile);
				}elseif($keys == 'CREATE_DATE')
				{
					$createDate				= date('M j y H:i');
					$viewFile				= preg_replace('/\{\{'. $keys .'\}\}/i', $createDate, $viewFile);
				}
			}
		}

		$fh					= fopen($viewPath, 'w');
		fwrite($fh, $viewFile);
		fclose($fh);

		echo "View created!\n";
		echo "OK\n";
	}

	private function createAdminPage($adminPageName)
	{
		$appAppPath		= BASEPATH . 'app/' . APPLICATIONNAME;

		if(!file_exists($appAppPath))
		{
			echo "Application could not found !\n";
			exit;
		}

		$lowerAdminName			= strtolower($adminPageName);
		$adminPath				= $appAppPath . '/admin/' . $lowerAdminName . '.php';

		if(file_exists($adminPath))
		{
			echo "Admin page exists ! \n";
			exit;
		}

		$adminFile				= file_get_contents(BASEPATH . 'core/install/admin.plf');

		if(preg_match_all('/\{\{(?P<name>\w+)\}\}/i', $adminFile, $matches))
		{
			foreach($matches['name'] as $keys)
			{
				if($keys == 'ADMIN_NAME')
				{
					$controllerClassName	= ucfirst($lowerAdminName);
					$adminFile				= preg_replace('/\{\{'. $keys .'\}\}/i', $controllerClassName, $adminFile);
				}elseif($keys == 'APPLICACITON_NAME')
				{
					$adminFile				= preg_replace('/\{\{'. $keys .'\}\}/i', APPLICATIONNAME, $adminFile);
				}elseif($keys == 'CREATE_DATE')
				{
					$createDate				= date('M j y H:i');
					$adminFile				= preg_replace('/\{\{'. $keys .'\}\}/i', $createDate, $adminFile);
				}
			}
		}

		$fh					= fopen($adminPath, 'w');
		fwrite($fh, $adminFile);
		fclose($fh);

		echo "Admin page created!\n";
		echo "OK\n";
	}

	private function createUsersTable()
	{
		$this->load->db();
		$this->load->config('membershipconfig');
		$this->syncDb(true);

		$params				= array(
			array('groupName'=>'Users'),
			array('groupName'=>'New Users'),
			array('groupName'=>'Admins'),
			array('groupName'=>'Banned')
		);

		$this->db->insert(MembershipConfig::$databaseTable . '_groups', $params);
		$this->load->helper('random');
		$userName		= 'Root';
		$userEmail		= 'root@root.root';
		$userPassword	= generateAlphaNumeric(12);

		$this->load->model('membership');

		if(!$this->membership->addUser($userName, $userEmail, $userPassword, 3))
		{
			echo "User exists in " . MembershipConfig::$databaseTable . "\n";
		}else{
			echo "\n\nUser table created!\n";
			echo "Root Account \n";
			echo "User Name: {$userName}\n";
			echo "Password: {$userPassword}\n";
		}
	}

	private function changeUserPassword($userName)
	{
		$this->load->db();
		$this->load->config('membershipconfig');
		$this->load->helper('text');
		$this->load->helper('random');
		$userNewPassword	= generateAlphaNumeric(12);
		$userPassword		= crypt($userNewPassword, time());
		$userNameClean		= slugifyText($userName);

		$this->db->update(MembershipConfig::$databaseTable, array('userPassword' => $userPassword), array('userNameClean' => $userNameClean));

		if($this->db->affectedRows() > 0)
		{
			echo "\n\nUser password updated!\n";
			echo "New Password: {$userNewPassword}\n";
		}else{
			echo "\n\nUser not found!";
		}
	}

	private function syncDb($silentMode = false)
	{
		$this->load->db();

		$appModelFiles			= scandir(APPFOLDER . 'models');
		$sharedModelFiles		= scandir(SHAREDFOLDER . 'models');
		$activeRecordClasses	= [];


		foreach($appModelFiles as $appModelFile)
		{
			if(substr($appModelFile, -3) == 'php')
			{
				try
				{
					include(APPFOLDER . 'models/' . $appModelFile);
				}catch (Exception $e)
				{

				}
			}
		}

		foreach($sharedModelFiles as $sharedModelFile)
		{
			if(substr($sharedModelFile, -3) == 'php')
			{
				try
				{
					include(SHAREDFOLDER . 'models/' . $sharedModelFile);
				}catch (Exception $e)
				{

				}
			}
		}

		try
		{
			$declaredClasses			= get_declared_classes();
		}catch (Exception $e)
		{
			$declaredClasses			= [];
		}

		foreach($declaredClasses as $className)
		{
			try
			{
				$parentClassName		= get_parent_class($className);
			}catch (Exception $e){
				continue;
			}

			if($parentClassName == 'ActiveRecord')
			{
				$activeRecordClasses[]			= $className;
			}
		}

		$tableList				= $this->db->getTableList();
		foreach($activeRecordClasses as $activeRecordClass)
		{
			$this->syncModel($activeRecordClass, $tableList, $silentMode);
		}


		$this->db->commit();
		$tableMTMList			= $this->db->getTableList();
		// sync many to many fields
		$this->syncManyToManyFields($activeRecordClasses, $tableMTMList, $silentMode);

		$this->db->commit();
		$tableCreatedList		= $this->db->getTableList();
		// resync models after all tables created ! (foreign key issues)
		foreach($activeRecordClasses as $activeRecordClass)
		{
			$this->syncModel($activeRecordClass, $tableCreatedList, $silentMode);
		}
	}

	private function syncModel($className, $tableList, $silentMode)
	{
		$model						= new $className();
		$databaseObjects			= $model->getDatabaseObjects();
		$dbTableName				= $model->databaseTableName;
		$tableExists				= false;


		foreach($tableList as $table)
		{
			if($dbTableName == $table)
			{
				$tableExists		= true;
			}
		}

		if($tableExists)
		{
			$columnAndIndexList		= $this->db->getColumnAndIndexList($dbTableName);

			foreach($databaseObjects as $dbObject)
			{
				$columnExists		= false;
				$columnData			= null;
				$objectData			= $dbObject['value'];

				if(isset($objectData['mtm']))
				{
					continue;
				}

				foreach($columnAndIndexList->columns as $column)
				{
					if($column->name == $dbObject['name'])
					{
						$columnExists	= true;
						$columnData		= $column;
						break;
					}
				}

				if($columnExists)
				{
					$columnChanged				= false;
					preg_match('/([^\(]+)/i', $objectData['type'], $sourceMatches);
					preg_match('/([^\(]+)/i', $objectData['type'], $destMatches);
					if($sourceMatches[0] != $destMatches[0])
					{
						$columnChanged			= true;
					}

					$un							= (isset($objectData['un'])) ? $objectData['un'] : false;
					if(!$columnChanged && $columnData->un !== $un)
					{
						$columnChanged			= true;
					}

					$nn							= (isset($objectData['nn'])) ? $objectData['nn'] : false;
					if(!$columnChanged && $columnData->nn !== $nn)
					{
						$columnChanged			= true;
					}

					$default					= (isset($objectData['default'])) ? $objectData['default'] : null;
					if(!$columnChanged && $columnData->default != $default)
					{
						$columnChanged			= true;
					}

					$ai							= (isset($objectData['ai'])) ? $objectData['ai'] : false;
					if(!$columnChanged && $columnData->ai !== $ai)
					{
						$columnChanged			= true;
					}

					$pk							= (isset($objectData['pk'])) ? $objectData['pk'] : false;
					if(!$columnChanged && $columnData->pk !== $pk)
					{
						$columnChanged			= true;
					}

					$uq							= (isset($objectData['uq'])) ? $objectData['uq'] : false;
					if(!$columnChanged && $columnData->uq !== $uq)
					{
						$columnChanged			= true;
					}

					if($columnChanged)
					{

						if(!$silentMode)
						{
							$this->db->addOrUpdateColumn($dbTableName, $dbObject, true);
							echo $this->db->lastQuery;
							echo "\n";
						}else{
							echo 'Error! You must first run syncdb';
							exit;
						}
					}

				}else{

					if(!$silentMode) {
						$this->db->addOrUpdateColumn($dbTableName, $dbObject);
						echo $this->db->lastQuery;
						echo "\n";
					}else{
						echo 'Error! You must first run syncdb';
						exit;
					}
				}

				$columnHasAindex	= false;
				$columnIndexData	= null;

				foreach($columnAndIndexList->indexes as $column)
				{
					if($column->columnName == $dbObject['name'])
					{
						$columnHasAindex	= true;
						$columnIndexData	= $column;
						break;
					}
				}

				if($columnHasAindex) {

					$isIdx			= false;
					if (isset($objectData['indexed'])) {
						if ($objectData['indexed']) {
							$isIdx			= true;
						}
					}

					if(!$isIdx)
					{
						$removeIdx		= true;
						if(isset($objectData['pk']))
						{
							if($objectData['pk'])
							{
								$removeIdx			= false;
							}
						}

						if(isset($objectData['uq']) && $removeIdx)
						{
							if($objectData['uq'])
							{
								$removeIdx			= false;
							}
						}

						if(isset($objectData['fk']) && $removeIdx)
						{
							$removeIdx			= false;
						}

						if($removeIdx)
						{

							if(!$silentMode) {
								$this->db->removeIndex($dbTableName, $columnIndexData->indexName);
								echo $this->db->lastQuery;
								echo "\n";
							}else{
								echo 'Error! You must first run syncdb';
								exit;
							}
						}
					}
				}else{
					if (isset($objectData['indexed'])) {
						if ($objectData['indexed']) {

							if(!$silentMode) {
								$this->db->addIndex($dbTableName, $dbTableName . '_' . $dbObject['name'] . '_IDX', $dbObject['name']);
								echo $this->db->lastQuery;
								echo "\n";
							}else{
								echo 'Error! You must first run syncdb';
								exit;
							}
						}
					}
				}


				$columnHasAFK		= false;
				$columnFKData		= null;

				foreach($columnAndIndexList->foreignKeys as $columnFK)
				{
					if($columnFK->columnName == $dbObject['name'])
					{
						$columnHasAFK		= true;
						$columnFKData		= $columnFK;
						break;
					}
				}

				if($columnHasAFK)
				{
					if(isset($objectData['fk']))
					{
						$fkTabke		= $objectData['fk']['table'];
						$fkReference	= $objectData['fk']['reference'];

						if($fkTabke != $columnFKData->referenceTable || $fkReference != $columnFKData->referenceColumn)
						{

							if(!$silentMode) {
								$this->db->removeForeignKey($dbTableName, $columnFKData->foreignKeyName);
								echo $this->db->lastQuery;
								echo "\n";
							}else{
								echo 'Error! You must first run syncdb';
								exit;
							}

							if(!$silentMode) {
								$this->db->addForeignKey($dbTableName, $dbTableName . '_' .$dbObject['name'] . '_FNK' , $dbObject['name'], $fkTabke, $fkReference);
								echo $this->db->lastQuery;
								echo "\n";
							}else{
								echo 'Error! You must first run syncdb';
								exit;
							}
						}
					}else{
						if(!$silentMode) {
							$this->db->removeForeignKey($dbTableName, $columnFKData->foreignKeyName);
							echo $this->db->lastQuery;
							echo "\n";
						}else{
							echo 'Error! You must first run syncdb';
							exit;
						}
					}
				}else{
					if(isset($objectData['fk'])) {
						$fkTabke = $objectData['fk']['table'];
						$fkReference = $objectData['fk']['reference'];

						if(!$silentMode) {
							$this->db->addForeignKey($dbTableName, $dbTableName . '_' .$dbObject['name'] . '_FNK' , $dbObject['name'], $fkTabke, $fkReference);
							echo $this->db->lastQuery;
							echo "\n";
						}else{
							echo 'Error! You must first run syncdb';
							exit;
						}
					}
				}
			}

		}else{
			if(!$silentMode) {
				$createSteatment		= $this->db->getCreateSteatment($dbTableName, $databaseObjects);
				$this->db->query($createSteatment);
				echo $this->db->lastQuery;
				echo "\n";
			}else{
				echo 'Error! You must first run syncdb';
				exit;
			}
		}
	}

	private function syncManyToManyFields($classes, $tableList, $silentMode)
	{
		$manyToManyFields			= [];

		foreach($classes as $className)
		{
			$model						= new $className();
			$databaseObjects			= $model->getDatabaseObjects();
			$dbTableName				= $model->databaseTableName;

			foreach($databaseObjects as $object)
			{
				if(isset($object['value']['mtm']))
				{
					$tmpMtmField			= new stdClass();
					$tmpMtmField->table		= $dbTableName;
					$tmpMtmField->field		= $object;
					$tmpMtmField->model		= $model;

					$manyToManyFields[]		= $tmpMtmField;
				}
			}
		}


		foreach($manyToManyFields as $mtmField)
		{
			$mtmTableName					= $mtmField->table . '_' . $mtmField->field['value']['mtm']['table'] . '_' . $mtmField->field['value']['mtm']['reference'];
			$mtmTableExists					= false;

			foreach($tableList as $table)
			{
				if($mtmTableName == $table)
				{
						$mtmTableExists		= true;
				}
			}

			if($mtmTableExists)
			{
				if(!$silentMode) {
					echo "\n\nWARNING!!!\nMany to many fields does not support to updates.\nIf you do any changes you should backup and drop many to many tables!\n\n";
				}
			}else{
				$tbObjects					= [];
				$tbObjects[]				= [
					'name'					=> 'id',
					'value'					=> [
						'type'				=> 'bigint',
						'pk'				=> true,
						'un'				=> true,
						'ai'				=> true,
						'nn'				=> true
					]
				];

				$pkField					= null;
				foreach($mtmField->model->getDatabaseObjects() as $mtpFindPKObject)
				{
					if(isset($mtpFindPKObject['value']['pk']))
					{
						if($mtpFindPKObject['value']['pk'])
						{
							$pkField		= $mtpFindPKObject;
							break;
						}
					}
				}

				if(is_null($pkField))
				{
					continue;
				}

				$tbObjects[]				= [
					'name'					=> $mtmField->field['name'],
					'value'					=> [
						'type'				=> $pkField['value']['type'],
						'un'				=> (isset($pkField['value']['un'])) ? $pkField['value']['un'] : false,
						'nn'				=> (isset($pkField['value']['un'])) ? $pkField['value']['nn'] : false
					]
				];

				$referenceTableExists		= false;
				$referenceColumn			= [];

				foreach($classes as $className)
				{
					$model						= new $className();
					$dbTableName				= $model->databaseTableName;

					if($dbTableName == $mtmField->field['value']['mtm']['table'])
					{

						$databaseObjects			= $model->getDatabaseObjects();
						foreach($databaseObjects as $object)
						{
							if($object['name'] == $mtmField->field['value']['mtm']['reference'])
							{
								$referenceTableExists		= true;
								$referenceColumn			= $object;
								break 2;
							}
						}
					}
				}

				if($referenceTableExists)
				{
					$tbObjects[]				= [
						'name'					=> $referenceColumn['name'] . '_mtm',
						'value'					=> [
							'type'				=> $referenceColumn['value']['type'],
							'un'				=> (isset($referenceColumn['value']['un'])) ? $referenceColumn['value']['un'] : false,
							'nn'				=> (isset($referenceColumn['value']['un'])) ? $referenceColumn['value']['nn'] : false
						]
					];

					if(!$silentMode) {
						$createSteatment		= $this->db->getCreateSteatment($mtmTableName, $tbObjects);
						$this->db->query($createSteatment);
						echo $this->db->lastQuery;
						echo "\n";
					}else{
						echo 'Error! You must first run syncdb';
						exit;
					}

					if(!$silentMode) {
						$this->db->addForeignKey($mtmTableName, $mtmTableName . '_' .$tbObjects[1]['name'] . '_FNK' , $tbObjects[1]['name'], $mtmField->table, $pkField['name']);
						echo $this->db->lastQuery;
						echo "\n";
						$this->db->addForeignKey($mtmTableName, $mtmTableName . '_' .$tbObjects[2]['name'] . '_FNK' , $tbObjects[2]['name'], $mtmField->field['value']['mtm']['table'], substr($tbObjects[2]['name'], 0, -4));
						echo $this->db->lastQuery;
						echo "\n";
					}else{
						echo 'Error! You must first run syncdb';
						exit;
					}
				}
			}
		}
	}
}

/**
 * ------------------------------------------------
 * End of file manager.php
 * ------------------------------------------------
 */