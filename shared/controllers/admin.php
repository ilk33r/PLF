<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Admin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 16 15 20:02
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */

class Admin extends PLF_Controller
{

	public static $cli				= false;

	private static $alertTypes		= ['success', 'danger', 'info', 'warning'];
	private $adminModules;

	// only override method is __construct
	public function __construct()
	{
		parent::__construct();
		// your init code

		header('Access-Control-Allow-Origin: ' . baseUrl());
		header('Access-Control-Allow-Headers: Content-Type, Accept');
		header('Access-Control-Allow-Methods: GET, POST');
		header('Access-Control-Max-Age: 1728000');
		header('Access-Control-Allow-Credentials: true');

		$this->load->config('adminconfig');
		if(!Adminconfig::$adminPageIsActive)
		{
			Output::$masterSended		= true;
			show404();
		}

		$this->load->db();
		$this->load->model('membership');
		$this->ob->setContentPath('plf');

		if($this->membership->groupId != Adminconfig::$adminGroupId && $_SERVER['REQUEST_URI'] != Adminconfig::$adminLoginUrl)
		{
			Output::$masterSended		= true;
			$this->index();
			exit;
		}

		if($this->membership->login)
		{
			$this->adminModules			= $this->loadAllModules();
		}elseif($_SERVER['REQUEST_URI'] != Adminconfig::$adminLoginUrl){
			throw new Exception('You don\'t have a permission!');
		}
	}

	// this is page index method
	public function index()
	{
		if($this->membership->groupId != Adminconfig::$adminGroupId) {
			$loginError = true;
			$this->membership->login		= false;
			$this->membership->logout();
		}


		if($this->membership->login)
		{
			$templateData					= new stdClass();
			$templateData->userName			= $this->membership->userName;
			$templateData->error			= false;
			$templateData->leftMenu			= $this->getModuleList();
			$templateData->content			= $this->generateAdminStats();
			$this->ob->assignVar($templateData);
			$this->ob->send('admin/adminmasterpage');
		}else{
			$_SESSION['Admin_visitedPage']	= activePageUrl();
			$templateData					= new stdClass();
			$templateData->formAction		= Adminconfig::$adminLoginUrl;
			$templateData->error			= $loginError;
			$templateData->errorMessage		= 'You don\'t have a permission!';
			$templateData->csrfToken		= $this->generateCSRFToken();
			$this->ob->assignVar($templateData);
			$this->ob->send('admin/adminlogin');
		}
	}

	public function login()
	{
		Output::$masterSended		= true;
		if(isset($_POST['plf-csrf-token']))
		{
			$sessionToken			= (isset($_SESSION['Admin_CSRF_Token'])) ? $_SESSION['Admin_CSRF_Token'] : '';
			if($_POST['plf-csrf-token'] == $sessionToken)
			{
				unset($_SESSION['Admin_CSRF_Token']);

				if($this->membership->login)
				{
					Output::$masterSended		= true;
					header('Location: ' . $_SESSION['Admin_visitedPage']);
					exit;
				}else{
					$userLoginID		= (isset($_POST['username']))?$_POST['username']:'';
					$userPassword		= (isset($_POST['password']))?$_POST['password']:'';
					$loginMsg			= $this->membership->loginWithUserName($userLoginID, $userPassword);

					if($loginMsg == 'SUCCESS')
					{
						Output::$masterSended		= true;
						$redirectPage				= $_SESSION['Admin_visitedPage'];

						if(empty($redirectPage))
						{
							$redirectPage			= Adminconfig::$adminPath;
						}

						if (strpos($redirectPage, Adminconfig::$adminLoginUrl) !== false)
						{
							$redirectPage			= Adminconfig::$adminPath;
						}

						header('Location: ' . $redirectPage);
						exit;
					}
				}
			}
		}

		$templateData					= new stdClass();
		$templateData->formAction		= Adminconfig::$adminLoginUrl;
		$templateData->error			= true;
		$templateData->errorMessage		= 'Invalid username or password';
		$templateData->csrfToken		= $this->generateCSRFToken();
		$this->ob->assignVar($templateData);
		$this->ob->send('admin/adminlogin');
	}

	public function custom($pageName)
	{
		switch($pageName)
		{
			case 'logout':
				$this->logout();
				break;
			case 'changePassword':
				$this->changePassword();
				break;
			case 'uploadImage':
				$this->uploadImage();
				break;
			case 'mtmField':
				$this->getManyToManyFieldList();
				break;
			default:
				show404();
				break;
		}
	}

	private function logout()
	{
		Output::$masterSended		= true;
		$this->membership->logout();
		header('Location: /');
	}

	private function changePassword()
	{
		$formFields						= [];

		$tmpFormFieldData					= new stdClass();
		$tmpFormFieldData->name				= 'currentPassword';
		$tmpFormFieldData->localizeName		= 'Current Password';
		$tmpFormFieldData->hasError			= false;
		$tmpFormFieldData->field			= '<fieldset><input readonly type="password" class="form-control" id="currentPassword" name="currentPassword" value="" /></fieldset>';
		$formFields[]						= $tmpFormFieldData;

		$tmpFormFieldData					= new stdClass();
		$tmpFormFieldData->name				= 'newPassword';
		$tmpFormFieldData->localizeName		= 'New Password';
		$tmpFormFieldData->hasError			= false;
		$tmpFormFieldData->field			= '<fieldset><input readonly type="password" class="form-control" id="newPassword" name="newPassword" value="" /></fieldset>';
		$formFields[]						= $tmpFormFieldData;

		$tmpFormFieldData					= new stdClass();
		$tmpFormFieldData->name				= 'newPasswordReType';
		$tmpFormFieldData->localizeName		= 'New Password (Re Type)';
		$tmpFormFieldData->hasError			= false;
		$tmpFormFieldData->field			= '<fieldset><input readonly type="password" class="form-control" id="newPasswordReType" name="newPasswordReType" value="" /></fieldset>';
		$formFields[]						= $tmpFormFieldData;

		$errorStatus						= false;
		$errorData							= new stdClass();

		if(isset($_POST['plf-csrf-token']))
		{
			$sessionToken			= (isset($_SESSION['Admin_CSRF_Token'])) ? $_SESSION['Admin_CSRF_Token'] : '';
			if($_POST['plf-csrf-token'] == $sessionToken) {

				$userDBObject		= $this->membership->dbobject['USER'];
				$userData			= $userDBObject->properties('userPassword')->get($this->membership->userId);

				$passwordCorrect		= false;
				if(PHP_VERSION_ID >= 50600)
				{
					$passwordCorrect	= hash_equals($userData->userPassword, crypt($_POST['currentPassword'], $userData->userPassword));
				}else{
					$passwordCorrect	= ($userData->userPassword == crypt($_POST['currentPassword'], $userData->userPassword)) ? true : false;
				}

				if($passwordCorrect)
				{
					if($_POST['newPassword'] != $_POST['newPasswordReType'])
					{
						$errorStatus			= true;
						$errorData->title		= 'Failed';
						$errorData->type		= self::$alertTypes[1];
						$errorData->message		= 'Your new password does not match.';
					}else{
						if(strlen($_POST['newPassword']) < 4)
						{
							$errorStatus			= true;
							$errorData->title		= 'Failed';
							$errorData->type		= self::$alertTypes[1];
							$errorData->message		= 'Your new password too short.';
						}else{
							$this->membership->setUserPassword($this->membership->userId, $_POST['newPassword']);
							$errorStatus			= true;
							$errorData->title		= 'Success';
							$errorData->type		= self::$alertTypes[0];
							$errorData->message		= 'Your password has been changed.';
						}
					}
				}else{
					$errorStatus			= true;
					$errorData->title		= 'Failed';
					$errorData->type		= self::$alertTypes[1];
					$errorData->message		= 'Your old password does not correct.';
				}
			}
		}

		$formData						= new stdClass();
		$formData->moduleAction			= Adminconfig::$adminPath . '/custom/changePassword/';
		$formData->formFields			= $formFields;
		$formData->csrfToken			= $this->generateCSRFToken();

		$templateData					= new stdClass();
		$templateData->userName			= $this->membership->userName;
		$templateData->error			= $errorStatus;
		$templateData->errorData		= $errorData;
		$templateData->leftMenu			= $this->getModuleList();
		$templateData->content			= $this->ob->getView($formData, 'admin/adminform');
		$this->ob->assignVar($templateData);
		$this->ob->send('admin/adminmasterpage');
	}

	public function module($moduleName, $pageNumber = 1, $hasDeleteWarning = false)
	{
		$module			= $this->getModule($moduleName);
		if($module)
		{
			$modelName		= $module->dbobject['model'];
			$this->load->model($modelName);

			if(isset($module->dbobject['dbtable']))
			{
				$dbTableClassName		= $module->dbobject['dbtable'];
				$dbobject				= new $dbTableClassName();
			}else{
				$dbobject				= $this->$moduleName->dbobject;
			}

			$pkColumn					= $dbobject->getPKFieldName();
			$listFields					= $module->listFields;
			$listFieldNames				= $module->listFieldNames;
			$originalListFields			= $listFields;
			$originalListFieldNames		= $listFieldNames;
			$pkColumnExists				= false;

			foreach($listFields as $field)
			{
				if($field == $pkColumn)
				{
					$pkColumnExists		= true;
					break;
				}
			}

			if(!$pkColumnExists)
			{
				$listFields[]			= $pkColumn;
				$listFieldNames[]		= 'ID';
			}

			$selectFields				= [];
			foreach($listFields as $fieldName)
			{
				if($dbobject->hasObject($fieldName))
				{
					$selectFields[]		= $fieldName;
				}
			}

			call_user_func_array(array($dbobject, 'properties'), $selectFields);
			$startRow					= (((int)$pageNumber) - 1) * $module->rowCountPerPage;
			$dbobject->limit($module->rowCountPerPage, $startRow);
			$objectList					= $dbobject->get();

			$tableData						= new stdClass();
			$originalListFieldNames[]		= 'Options';
			$tableData->theads				= $originalListFieldNames;
			$tableData->tbodies				= [];

			foreach($objectList as $object)
			{
				$rowData					= [];
				foreach($originalListFields as $listField)
				{
					if(isset($object->$listField))
					{
						$rowData[]				= $object->$listField;
					}else{
						if(substr($listField, 0, 4) == 'get_')
						{
							$rowData[]				= $module->$listField($object->$pkColumn, $dbobject);
						}else{
							$rowData[]				= '';
						}
					}
				}

				$pkValue					= $object->$pkColumn;
				$optionsParameters			= '';

				if($module->hasEditPermission())
				{
					$optionsParameters		.= '<a class="optionLink" href="' . Adminconfig::$adminPath . '/edit/' . $module->name . '/' . $pkValue .'/" title="edit"><i class="glyphicon glyphicon-edit"></i></a>';
				}

				if($module->hasDeletePermission()) {
					$optionsParameters .= '<a class="optionLink" href="' . Adminconfig::$adminPath . '/delete/' . $module->name . '/' . $pkValue . '/" title="edit" onClick="return deleteAction();"><i class="glyphicon glyphicon-trash"></i></a>';
				}

				$rowData[]					= $optionsParameters;

				$tableData->tbodies[]		= $rowData;
			}

			$objectCount					= $this->db->query('select count(*) as objectCount from ' . $dbobject->databaseTableName)->row()->objectCount;
			$pageCount						= ceil($objectCount / $module->rowCountPerPage);

			$templateData					= new stdClass();
			$templateData->userName			= $this->membership->userName;
			$templateData->error			= $hasDeleteWarning;
			$templateData->errorData		= new stdClass();
			$templateData->errorData->type	= self::$alertTypes[3];
			$templateData->errorData->title	= 'Warning';
			$templateData->errorData->message	= 'Object has been deleted!';
			$templateData->leftMenu			= $this->getModuleList();
			$templateData->breadcrumb		= $this->generateAdminBreadcrumb($module->name, $module->localizedName);
			$templateData->pagination		= $this->getPagination($module->name, $pageNumber, $pageCount);
			if(!empty($module->customCss))
				$templateData->customCss	= $module->customCss;

			if(!empty($module->customJs))
				$templateData->customJs		= $module->customJs;

			$templateData->content			= $this->ob->getView($tableData, 'admin/admintable');
			$this->ob->assignVar($templateData);
			$this->ob->send('admin/adminmasterpage');

		}else{
			show404();
		}
	}

	public function addmodule($moduleName)
	{
		$module			= $this->getModule($moduleName);
		if($module)
		{
			if(!$module->hasAddPermission())
			{
				show404();
			}

			$modelName		= $module->dbobject['model'];
			$this->load->model($modelName);

			if(isset($module->dbobject['dbtable']))
			{
				$dbTableClassName		= $module->dbobject['dbtable'];
				$dbobject				= new $dbTableClassName();
			}else{
				$dbobject				= $this->$moduleName->dbobject;
			}

			$moduleFields				= $dbobject->getDatabaseObjects();
			$formFields					= [];
			$errorFields				= [];
			$fieldValues				= [];
			$idx						= 0;
			$errorStatus				= false;
			$errorData					= new stdClass();
			$errorData->type			= self::$alertTypes[0];
			$errorData->title			= '';
			$errorData->message			= '';

			if(isset($_POST['plf-csrf-token']))
			{
				$sessionToken			= (isset($_SESSION['Admin_CSRF_Token'])) ? $_SESSION['Admin_CSRF_Token'] : '';
				if($_POST['plf-csrf-token'] == $sessionToken) {

					$saveResponse		= $module->save($_POST, $moduleFields, $dbobject);

					if($saveResponse['status'])
					{
						$errorStatus			= true;
						$errorData->title		= 'Success';
						$errorData->type		= self::$alertTypes[0];
						$errorData->message		= $module->localizedName . ' has been added.';
					}else{
						$errorStatus			= true;
						$errorData->title		= 'Failed';
						$errorData->type		= self::$alertTypes[1];
						$errorData->message		= $saveResponse['errorMessage'];
						$errorFields			= $saveResponse['errorFields'];

						foreach($_POST as $objectName => $objectValue)
						{
							$fieldValues[$objectName]		= $objectValue;
						}
					}
				}
			}

			foreach($module->editFields as $editField)
			{
				$tmpFormFieldData					= new stdClass();
				$tmpFormFieldData->name				= $editField;
				$tmpFormFieldData->localizeName		= $module->editFieldNames[$idx];
				$tmpFormFieldData->field			= '';
				$tmpFormFieldData->hasError			= (in_array($editField, $errorFields)) ? $editField : false;
				$fieldFound							= false;

				foreach($moduleFields as $field)
				{
					if($field['name'] == $editField)
					{
						$fieldData			=  $field;
						$fieldFound			= true;
					}
				}

				if($fieldFound)
				{
					$fieldValue				= $fieldData['value'];
					$fieldDefaultValue		= (isset($fieldValues[$editField])) ? $fieldValues[$editField] : null;

					if(isset($fieldValue['fieldType']))
					{
						$fieldTypeName				= $fieldValue['fieldType'] . 'Field';
						$tmpFormFieldData->field	= $module->$fieldTypeName($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
					}elseif(isset($fieldValue['fk'])) {

						$foreignKeyModuleAndModel	= $this->getForeignKeyModuleAndModel($fieldValue['fk']['table']);
						$tmpFormFieldData->field = $module->selectField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject, $foreignKeyModuleAndModel->model, $foreignKeyModuleAndModel->module]);
					}elseif(isset($fieldValue['mtm']))
					{
						$mtmKeyModuleAndModel		= $this->getForeignKeyModuleAndModel($fieldValue['mtm']['table']);
						$tmpFormFieldData->field	= $module->manyToManyField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject, $mtmKeyModuleAndModel->model, $mtmKeyModuleAndModel->module]);
					}else{
						$fieldDataType		= $fieldValue['type'];
						if(preg_match('/int/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/float/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/double/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/decimal/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/date/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->dateField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/time/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->dateField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/char/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/text/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textAreaField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/enum/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->selectField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}
					}

				}else{
					if(substr($editField, 0, 4) == 'get_')
					{
						$tmpFormFieldData->field	= $module->$editField($dbobject);
					}
				}

				$formFields[]			= $tmpFormFieldData;
				$idx++;
			}

			$formData						= new stdClass();
			$formData->moduleAction			= Adminconfig::$adminPath . '/add/' . $moduleName . '/';
			$formData->formFields			= $formFields;
			$formData->csrfToken			= $this->generateCSRFToken();

			$templateData					= new stdClass();
			$templateData->userName			= $this->membership->userName;
			$templateData->error			= $errorStatus;
			$templateData->errorData		= $errorData;
			$templateData->leftMenu			= $this->getModuleList();
			$templateData->breadcrumb		= $this->generateAdminBreadcrumb($module->name, $module->localizedName, 'Add ' . $module->localizedName);

			if(!empty($module->customCss))
				$templateData->customCss	= $module->customCss;

			if(!empty($module->customJs))
				$templateData->customJs		= $module->customJs;

			$templateData->content			= $this->ob->getView($formData, 'admin/adminform');
			$this->ob->assignVar($templateData);
			$this->ob->send('admin/adminmasterpage');

		}else{
			show404();
		}
	}

	public function editmodule($moduleName, $objectPk)
	{
		$module			= $this->getModule($moduleName);
		if($module)
		{
			if(!$module->hasEditPermission())
			{
				show404();
			}

			$modelName		= $module->dbobject['model'];
			$this->load->model($modelName);

			if(isset($module->dbobject['dbtable']))
			{
				$dbTableClassName		= $module->dbobject['dbtable'];
				$dbobject				= new $dbTableClassName();
			}else{
				$dbobject				= $this->$moduleName->dbobject;
			}

			$currentObject				= $dbobject->get($objectPk);
			$moduleFields				= $currentObject->getDatabaseObjects();
			$formFields					= [];
			$errorFields				= [];
			$fieldValues				= [];
			$idx						= 0;
			$errorStatus				= false;
			$errorData					= new stdClass();
			$errorData->type			= self::$alertTypes[0];
			$errorData->title			= '';
			$errorData->message			= '';

			if(isset($_POST['plf-csrf-token']))
			{
				$sessionToken			= (isset($_SESSION['Admin_CSRF_Token'])) ? $_SESSION['Admin_CSRF_Token'] : '';
				if($_POST['plf-csrf-token'] == $sessionToken) {

					$saveResponse		= $module->save($_POST, $moduleFields, $currentObject);

					if($saveResponse['status'])
					{
						$errorStatus			= true;
						$errorData->title		= 'Success';
						$errorData->type		= self::$alertTypes[0];
						$errorData->message		= $module->localizedName . ' has been updated.';
					}else{
						$errorStatus			= true;
						$errorData->title		= 'Failed';
						$errorData->type		= self::$alertTypes[1];
						$errorData->message		= $saveResponse['errorMessage'];
						$errorFields			= $saveResponse['errorFields'];

						foreach($_POST as $objectName => $objectValue)
						{
							$fieldValues[$objectName]		= $objectValue;
						}
					}
				}
			}

			foreach($module->editFields as $editField)
			{
				$tmpFormFieldData					= new stdClass();
				$tmpFormFieldData->name				= $editField;
				$tmpFormFieldData->localizeName		= $module->editFieldNames[$idx];
				$tmpFormFieldData->field			= '';
				$tmpFormFieldData->hasError			= (in_array($editField, $errorFields)) ? $editField : false;
				$fieldFound							= false;

				foreach($moduleFields as $field)
				{
					if($field['name'] == $editField)
					{
						$fieldData			=  $field;
						$fieldFound			= true;
					}
				}

				if($fieldFound)
				{
					$fieldValue				= $fieldData['value'];
					$fieldDefaultValue		= (isset($fieldValues[$editField])) ? $fieldValues[$editField] : null;
					if(is_null($fieldDefaultValue))
					{
						if(isset($currentObject->$editField))
						{
							$fieldDefaultValue		= $currentObject->$editField;
						}
					}

					if(isset($fieldValue['fieldType']))
					{
						$fieldTypeName				= $fieldValue['fieldType'] . 'Field';
						$tmpFormFieldData->field	= $module->$fieldTypeName($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
					}elseif(isset($fieldValue['fk'])) {

						$foreignKeyModuleAndModel	= $this->getForeignKeyModuleAndModel($fieldValue['fk']['table']);
						$tmpFormFieldData->field = $module->selectField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject, $foreignKeyModuleAndModel->model, $foreignKeyModuleAndModel->module]);
					}elseif(isset($fieldValue['mtm']))
					{
						$mtmKeyModuleAndModel		= $this->getForeignKeyModuleAndModel($fieldValue['mtm']['table']);
						$tmpFormFieldData->field	= $module->manyToManyField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject, $mtmKeyModuleAndModel->model, $mtmKeyModuleAndModel->module, $objectPk]);
					}else{
						$fieldDataType		= $fieldValue['type'];
						if(preg_match('/int/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/float/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/double/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/decimal/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/date/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->dateField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/time/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->dateField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/char/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/text/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->textAreaField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else if(preg_match('/enum/i', $fieldDataType))
						{
							$tmpFormFieldData->field	= $module->selectField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}else{
							$tmpFormFieldData->field	= $module->textField($tmpFormFieldData->name, $fieldData, $fieldDefaultValue, [$dbobject]);
						}
					}

				}else{
					if(substr($editField, 0, 4) == 'get_')
					{
						$tmpFormFieldData->field	= $module->$editField($dbobject);
					}
				}

				$formFields[]			= $tmpFormFieldData;
				$idx++;
			}

			$formData						= new stdClass();
			$formData->moduleAction			= Adminconfig::$adminPath . '/edit/' . $moduleName . '/' . $objectPk . '/';
			$formData->formFields			= $formFields;
			$formData->csrfToken			= $this->generateCSRFToken();

			$templateData					= new stdClass();
			$templateData->userName			= $this->membership->userName;
			$templateData->error			= $errorStatus;
			$templateData->errorData		= $errorData;
			$templateData->leftMenu			= $this->getModuleList();
			$editFieldName					= $module->fieldName();
			$templateData->breadcrumb		= $this->generateAdminBreadcrumb($module->name, $module->localizedName, 'Edit ' . $currentObject->$editFieldName);

			if(!empty($module->customCss))
				$templateData->customCss	= $module->customCss;

			if(!empty($module->customJs))
				$templateData->customJs		= $module->customJs;

			$templateData->content			= $this->ob->getView($formData, 'admin/adminform');
			$this->ob->assignVar($templateData);
			$this->ob->send('admin/adminmasterpage');

		}else{
			show404();
		}
	}

	public function deletemodule($moduleName, $objectPk)
	{
		$module			= $this->getModule($moduleName);
		if($module)
		{
			if(!$module->hasDeletePermission())
			{
				show404();
			}

			$modelName		= $module->dbobject['model'];
			$this->load->model($modelName);

			if(isset($module->dbobject['dbtable']))
			{
				$dbTableClassName		= $module->dbobject['dbtable'];
				$dbobject				= new $dbTableClassName();
			}else{
				$dbobject				= $this->$moduleName->dbobject;
			}

			$this->db->delete($dbobject->databaseTableName, array($dbobject->getPKFieldName()=>$objectPk));
			$this->module($moduleName, 1, true);

		}else{
			show404();
		}
	}

	private function generateCSRFToken()
	{
		$this->load->helper('random');
		$csrfToken							= generateAlphaNumeric(16);
		$_SESSION['Admin_CSRF_Token']		= $csrfToken;
		return $csrfToken;
	}

	private function loadAllModules()
	{
		require_once(SHAREDFOLDER . 'libraries/adminlibrary.php');
		$appAdminFiles			= scandir(APPFOLDER . 'admin');
		$sharedAdminFiles		= scandir(SHAREDFOLDER . 'admin');
		$adminClasses			= array();

		foreach($appAdminFiles as $appAdminFile)
		{
			if(substr($appAdminFile, -3) == 'php')
			{
				require(APPFOLDER . 'admin/' . $appAdminFile);
				$adminClassName		= substr($appAdminFile, 0, -4);
				$adminClasses[]		= new $adminClassName();
			}
		}

		foreach($sharedAdminFiles as $sharedAdminFile)
		{
			if(substr($sharedAdminFile, -3) == 'php')
			{
				require(SHAREDFOLDER . 'admin/' . $sharedAdminFile);
				$adminClassName		= substr($sharedAdminFile, 0, -4);
				$adminClasses[]		= new $adminClassName();
			}
		}

		return $adminClasses;
	}

	private function getModuleList()
	{
		$moduleList			= array();

		if(is_array($this->adminModules))
		{
			foreach($this->adminModules as $module)
			{
				if($module->isHiddenInGroup)
				{
					continue;
				}

				if(!isset($module->name))
				{
					$moduleList[$module->name]		= array();
				}

				$tmpModuleData							= new stdClass();
				$tmpModuleData->name					= $module->name;
				$tmpModuleData->localizedName			= ($module->localizedName) ? $module->localizedName : $module->name;
				$tmpModuleData->icon					= ($module->icon) ? $module->icon : 'glyphicon-info-sign';
				$groupUrls								= array(
					Adminconfig::$adminPath . '/' . $module->name 			=> 'List ' . $tmpModuleData->localizedName
				);

				if($module->hasAddPermission())
				{
					$addUrl		= Adminconfig::$adminPath . '/add/' . $module->name;
					$groupUrls[$addUrl]					= 'Add ' . $tmpModuleData->localizedName;
				}

				foreach($module->extraLinks() as $extraLinkKey => $extraLinkValue)
				{
					$groupUrls[$extraLinkKey]			= $extraLinkValue;
				}

				$tmpModuleData->URLList					= $groupUrls;
				$moduleList[$module->name]				= $tmpModuleData;

			}
		}

		sort($moduleList);
		return $moduleList;
	}

	private function generateAdminStats()
	{
		$databaseSize			= 0;
		$rowCount				= 0;
		$currentRowCount		= 0;
		$biggestTable			= '';

		if(Database::$dbdriver == 'mysqli')
		{
			$databaseData	= $this->db->query('select TABLE_NAME as tableName, TABLE_ROWS as rowCount, Round((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 1) as tableSize from information_schema.tables where TABLE_SCHEMA = ?', array(Database::$database))->result();

			foreach($databaseData as $tableInfo)
			{
				$databaseSize		+= $tableInfo->tableSize;
				$rowCount			+= $tableInfo->rowCount;
				if($currentRowCount < $tableInfo->rowCount)
				{
					$currentRowCount	= $tableInfo->rowCount;
					$biggestTable		= $tableInfo->tableName;
				}
			}
		}

		$userCount						= $this->db->query('select count(*) as userCount from ' . MembershipConfig::$databaseTable)->row()->userCount;
		$activeSessionCount				= $this->db->query('select count(*) as sessionCount from ' . MembershipConfig::$databaseTable . '_session')->row()->sessionCount;

		$templateData					= new stdClass();
		$templateData->theads			= ['Stats', 'Value', 'Stats', 'Value'];
		$templateData->tbodies			= [
			['<strong>Application Name</strong>', APPLICATIONNAME, '<strong>Application Language</strong>', APPLICATIONLANGUAGE],
			['<strong>Application Version</strong>', $this->ob->xVersionHeader, '<strong>Timezone</strong>', date_default_timezone_get()],
			['<strong>PHP Version</strong>', phpversion(), '<strong>Post Max Size</strong>', ini_get('post_max_size')],
			['<strong>SAPI</strong>', php_sapi_name(), '<strong>OS</strong>', PHP_OS],
			['<strong>GD Support</strong>', (extension_loaded('GD')) ? 'Yes' : 'No', '<strong>Opcache Support</strong>', (extension_loaded('Zend OPcache')) ? 'Yes' : 'No'],
			['<strong>Server Software</strong>', $_SERVER['SERVER_SOFTWARE'], '<strong>Server Ip</strong>', $_SERVER['SERVER_ADDR']],
			['<strong>Database Protocol</strong>', Database::$dbdriver, '<strong>Database Name</strong>', Database::$database],
			['<strong>Database Size</strong>', $databaseSize . ' MB', '<strong>Number of Records</strong>', $rowCount],
			['<strong>Biggest Table</strong>', $biggestTable, '<strong>Biggest Table Size</strong>', $currentRowCount . ' Records'],
			['<strong>User Count</strong>', $userCount, '<strong>Active Session Count</strong>', $activeSessionCount]
		];

		return $this->ob->getView($templateData, 'admin/admintable');
	}

	private function getModule($moduleName)
	{
		$foundedModule		= false;

		foreach($this->adminModules as $module) {

			if($moduleName == $module->name)
			{
				$foundedModule		= $module;
				break;
			}
		}

		return $foundedModule;
	}

	private function getForeignKeyModuleAndModel($fieldTableName)
	{
		$fkModule = null;
		$fkDbObject = null;

		foreach ($this->adminModules as $tmpFkModules) {

			if (!is_null($fkModule)) {
				continue;
			}

			$tmpDbobject = $tmpFkModules->dbobject['dbtable'];
			if (class_exists($tmpDbobject)) {

				$tmpClass = new $tmpDbobject();
				if ($tmpClass->databaseTableName == $fieldTableName) {
					$fkDbObject = $tmpClass;
					$fkModule = $tmpFkModules;
				} else {
					unset($tmpClass);
				}

			} else {
				$this->load->model($tmpFkModules->dbobject['model']);
				$tmpClass = new $tmpDbobject();

				if ($tmpClass->databaseTableName == $fieldTableName) {
					$fkDbObject = $tmpClass;
					$fkModule = $tmpFkModules;
				} else {
					unset($tmpClass);
				}

			}
		}

		$response			= new stdClass();
		$response->module	= $fkModule;
		$response->model	= $fkDbObject;

		return $response;
	}

	private function generateAdminBreadcrumb($moduleName, $moduleLinkName, $otherObject = null)
	{
		$adminIndex									= Adminconfig::$adminPath . '/';
		$breadCrumb[$adminIndex]					= 'Home';
		$brLink										= Adminconfig::$adminPath . '/' . $moduleName . '/';
		$breadCrumb[$brLink]						= $moduleLinkName;

		if(!is_null($otherObject))
		{
			$breadCrumb[]			= $otherObject;
		}

		$templateData				= ['breadcrumbs' => $breadCrumb];
		return $this->ob->getView($templateData, 'admin/adminbreadcrumb');
	}

	private function getPagination($moduleName, $currentPage, $pageCount, $popup = '')
	{
		$templateData					= new stdClass();
		$templateData->moduleName		= $moduleName;
		$templateData->currentPage		= $currentPage;
		$templateData->pageCount		= $pageCount;
		$templateData->popup			= $popup;
		$templateData->adminPath		= Adminconfig::$adminPath;
		return $this->ob->getView($templateData, 'admin/adminpagination');
	}

	private function uploadImage()
	{
		$rawPostData			= file_get_contents('php://input');
		$imageData				= json_decode($rawPostData);
		$response				= new stdClass();
		$extension				= 'jpg';

		switch($imageData->imageType)
		{
			case 'image/jpeg':
			case 'image/pjpeg':
				$extension		= 'jpg';
				break;
			case 'image/gif':
				$extension		= 'gif';
				break;
			case 'image/png':
				$extension		= 'png';
				break;
			default:
				$response->status		= false;
				$response->msg			= 'The file is not a valid image file.';
				echo json_encode($response);
				break;
		}

		if($imageData->imageName)
		{
			$imageData->imagePrettyName		= time() . '-' . slugifyText( substr(strtr($imageData->imageName, array('.'=>'')), 0, -3) ) . '.';
			$uploadFolder					= CONTENTFOLDER . $imageData->uploadPath;
			$getLastCharacterOnFolderName	= substr($uploadFolder, -1, 1);

			if($getLastCharacterOnFolderName != '/')
			{
				$uploadFolder				= $uploadFolder . '/';
			}

			if(!is_writable($uploadFolder))
			{
				$response->status		= false;
				$response->msg			= 'Upload directory is not writable';
				echo json_encode($response);
			}else{
				$fileHandler			= fopen($uploadFolder . $imageData->imagePrettyName . $extension, 'wb');
				$data					= explode(',', $imageData->imageData);
				fwrite($fileHandler, base64_decode($data[1]));
				fclose($fileHandler);

				$response->status		= true;
				$response->fileName		= $imageData->imagePrettyName . $extension;
				$response->filePath		= Config::$plfDirectory . 'content/' . APPLICATIONNAME;

				echo json_encode($response);
			}

		}else{
			$response->status		= false;
			$response->msg			= 'The file is not a valid image file.';
			echo json_encode($response);
		}
	}

	private function getManyToManyFieldList()
	{
		$moduleName		= $_GET['moduleName'];
		$pageNumber		= $_GET['pageNumber'];
		$module			= $this->getModule($moduleName);
		if($module)
		{
			$modelName		= $module->dbobject['model'];
			$this->load->model($modelName);

			if(isset($module->dbobject['dbtable']))
			{
				$dbTableClassName		= $module->dbobject['dbtable'];
				$dbobject				= new $dbTableClassName();
			}else{
				$dbobject				= $this->$moduleName->dbobject;
			}

			$pkColumn					= $dbobject->getPKFieldName();
			$listFields					= $module->listFields;
			$listFieldNames				= $module->listFieldNames;
			$originalListFields			= $listFields;
			$originalListFieldNames		= $listFieldNames;
			$pkColumnExists				= false;

			foreach($listFields as $field)
			{
				if($field == $pkColumn)
				{
					$pkColumnExists		= true;
					break;
				}
			}

			if(!$pkColumnExists)
			{
				$listFields[]			= $pkColumn;
				$listFieldNames[]		= 'ID';
			}

			$selectFields				= [];
			foreach($listFields as $fieldName)
			{
				if($dbobject->hasObject($fieldName))
				{
					$selectFields[]		= $fieldName;
				}
			}

			call_user_func_array(array($dbobject, 'properties'), $selectFields);
			$startRow					= (((int)$pageNumber) - 1) * $module->rowCountPerPage;
			$dbobject->limit($module->rowCountPerPage, $startRow);
			$objectList					= $dbobject->get();

			$tableData						= new stdClass();
			$originalListFieldNames[]		= 'Options';
			$tableData->theads				= $originalListFieldNames;
			$tableData->tbodies				= [];

			foreach($objectList as $object)
			{
				$rowData					= [];
				foreach($originalListFields as $listField)
				{
					if(isset($object->$listField))
					{
						$rowData[]				= $object->$listField;
					}else{
						$rowData[]				= $module->$listField($object->$pkColumn);
					}
				}

				$pkValue					= $object->$pkColumn;
				$fieldName					= $module->fieldName();
				$displayName				= $object->$fieldName;
				$optionsParameters			= '';
				$optionsParameters 			.= '<a class="optionLink mtmSelectLink" href="javascript:void(0);" title="edit" data-pk="' . $pkValue . '" data-displayName="' . addslashes($displayName) . '"><i class="glyphicon glyphicon-share-alt"></i> Select</a>';

				$rowData[]					= $optionsParameters;

				$tableData->tbodies[]		= $rowData;
			}

			$objectCount					= $this->db->query('select count(*) as objectCount from ' . $dbobject->databaseTableName)->row()->objectCount;
			$pageCount						= ceil($objectCount / $module->rowCountPerPage);

			$templateData					= new stdClass();
			$templateData->userName			= $this->membership->userName;
			$templateData->error			= false;
			$templateData->pagination		= $this->getPagination($module->name, $pageNumber, $pageCount, 'mtmField');
			if(!empty($module->customCss))
				$templateData->customCss	= $module->customCss;

			if(!empty($module->customJs))
				$templateData->customJs		= $module->customJs;

			$templateData->content			= $this->ob->getView($tableData, 'admin/admintable');
			$this->ob->assignVar($templateData);
			$this->ob->send('admin/adminmasterpage');

		}else{
			show404();
		}
	}
}