<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');
/**
 * Created by PhpStorm.
 * User: ilk3r
 * Date: 16/04/15
 * Time: 22:46
 */

class AdminLibrary
{
	public $isHiddenInGroup			= false;
	public $rowCountPerPage			= 50;
	public $name;
	public $localizedName;
	public $icon;
	public $dbobject;
	public $listFields;
	public $listFieldNames;
	public $editFields;
	public $editFieldNames;
	public $customCss;
	public $customJs;

	public function fieldName()
	{
		if(is_array($this->listFields))
		{
			return $this->listFields[0];
		}
	}

	public function hasEditPermission()
	{
		return true;
	}

	public function hasDeletePermission()
	{
		return true;
	}

	public function hasAddPermission()
	{
		return true;
	}

	public function extraLinks()
	{
		return array();
	}

	public function save($postData, $allFields, $dbObject)
	{
		$hasError			= false;
		$errorMessage		= '';
		$errorFields		= [];
		$uniqueFields		= [];

		foreach($allFields as $field) {
			$fieldName = $field['name'];
			$fieldProps = $field['value'];

			if(isset($fieldProps['ai']))
			{
				if($fieldProps['ai'])
				{
					continue;
				}
			}

			$objectHasError	= false;

			if (isset($fieldProps['nn'])) {
				if ($fieldProps['nn']) {
					if (isset($postData[$fieldName])) {
						if(!is_array($postData[$fieldName]))
						{
							if (strlen($postData[$fieldName]) == 0) {
								$hasError = true;
								$objectHasError	= true;
								$errorFields[] = $fieldName;

							}
						}
					}else{
						$hasError = true;
						$objectHasError	= true;
						$errorFields[] = $fieldName;
					}
				}
			}

			if(isset($fieldProps['uq']))
			{
				if($fieldProps['uq'])
				{
					$uniqueFields[]		= $fieldName;
				}
			}

			if(isset($fieldProps['mtm']))
			{
				$objectHasError		= false;
			}

			if(!$objectHasError)
			{
				if(isset($postData[$fieldName]))
				{
					if(isset($fieldProps['mtm']))
					{
						$mtmFieldValues			= [];
						foreach($postData[$fieldName] as $mtmNewValue)
						{
							$tmpMtmValue		= new stdClass();
							$tmpMtmValue->pk	= $mtmNewValue;
							$mtmFieldValues[]	= $tmpMtmValue;
						}
						$dbObject->$fieldName = $mtmFieldValues;
					}else{
						$dbObject->$fieldName = $postData[$fieldName];
					}
				}
			}
		}

		if($hasError)
		{
			$errorMessage		= 'Please fill the required fields';
		}else{
			$insertObject			= $dbObject->sync(true);
			$pk						= $insertObject->getObjectId();

			if(!$pk)
			{
				$hasError		= true;
				$errorFields	= $uniqueFields;
				$errorMessage	= 'Some areas already added!';
			}
		}

		$status			= ($hasError) ? false : true;
		return ['status'=>$status, 'errorMessage'=>$errorMessage, 'errorFields'=>$errorFields, 'objectId'=>$pk];
	}

	public function selectField($fieldName, $fieldData, $value, $params)
	{
		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$options				= [];
		if(preg_match_all('/enum\(([^)]+)/i', $fieldData['value']['type'], $matches))
		{
			$enumValues			= $matches[1][0];
			$optionValues		= explode(',', $enumValues);
			foreach($optionValues as $option)
			{
				$trimmedOption	= trim($option);
				$trans			= array('\'' => '', '"' => '');
				$optionString	= strtr($trimmedOption, $trans);
				$options[]		= ['value'=>$optionString, 'name'=>$optionString];
			}
		}elseif(isset($fieldData['value']['options']))
		{
			$options			= $fieldData['value']['options'];
		}elseif(isset($fieldData['value']['fk']))
		{
			$foreignKeyData		= $fieldData['value']['fk'];
			$fkDbObject			= $params[1];
			$fkModule			= $params[2];
			$valueName			= $foreignKeyData['reference'];
			$nameField			= $fkModule->fieldName();
			$fkData				= $fkDbObject->properties($valueName, $nameField)->get();

			foreach($fkData as $fk)
			{
				$options[]		= ['value'=>$fk->$valueName, 'name'=>$fk->$nameField];
			}
		}


		$defaultValue			= (is_null($value)) ? '' : $value;
		$selectFieldHtml		= '<fieldset '. $editable .'>';
		$selectFieldHtml		.= '<select class="form-control" id="' . $fieldName . '" name="' . $fieldName . '">';

		foreach($options as $option)
		{
			$selected			= ($option['value'] == $defaultValue) ? 'selected="selected"' : '';
			$selectFieldHtml	.= '<option value="'. $option['value'] .'" '. $selected .'>'. $option['name'] .'</option>';
		}

		$selectFieldHtml		.= '</select></fieldset>';

		return $selectFieldHtml;
	}

	public function textField($fieldName, $fieldData, $value, $params)
	{
		if(isset($fieldData['value']['maxLen']))
		{
			$maxLength			= 'maxlength="' . $fieldData['value']['maxLen'] . '"';
		}else{
			$maxLength			= '';
		}

		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		if(empty($defaultValue) && isset($fieldData['value']['default']))
		{
			$defaultValue		= $fieldData['value']['default'];
		}

		return '<fieldset '. $editable .'><input readonly type="text" class="form-control" id="' . $fieldName . '" name="' . $fieldName . '" value="'. $defaultValue .'" '. $maxLength .' /></fieldset>';
	}

	public function emailField($fieldName, $fieldData, $value, $params)
	{
		if(isset($fieldData['value']['maxLen']))
		{
			$maxLength			= 'maxlength="' . $fieldData['value']['maxLen'] . '"';
		}else{
			$maxLength			= '';
		}

		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		if(empty($defaultValue) && isset($fieldData['value']['default']))
		{
			$defaultValue		= $fieldData['value']['default'];
		}

		return '<fieldset '. $editable .'><input readonly type="email" class="form-control" id="' . $fieldName . '" name="' . $fieldName . '" value="'. $defaultValue .'" '. $maxLength .' /></fieldset>';
	}

	public function passwordField($fieldName, $fieldData, $value, $params)
	{
		if(isset($fieldData['value']['maxLen']))
		{
			$maxLength			= 'maxlength="' . $fieldData['value']['maxLen'] . '"';
		}else{
			$maxLength			= '';
		}

		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		if(empty($defaultValue) && isset($fieldData['value']['default']))
		{
			$defaultValue		= $fieldData['value']['default'];
		}

		return '<fieldset '. $editable .'><input readonly type="password" class="form-control" id="' . $fieldName . '" name="' . $fieldName . '" value="'. $defaultValue .'" '. $maxLength .' /></fieldset>';
	}

	public function dateField($fieldName, $fieldData, $value, $params)
	{
		if(isset($fieldData['value']['maxLen']))
		{
			$maxLength			= 'maxlength="' . $fieldData['value']['maxLen'] . '"';
		}else{
			$maxLength			= '';
		}

		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		$dateTimeObject			= new DateTime($defaultValue);
		$formattedDateTime		= $dateTimeObject->format('Y-m-d');

		return '<fieldset '. $editable .'><input type="date" class="form-control" id="' . $fieldName . '" name="' . $fieldName . '" value="'. $formattedDateTime .'" '. $maxLength .' /></fieldset>';
	}

	public function textAreaField($fieldName, $fieldData, $value, $params)
	{
		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		if(empty($defaultValue) && isset($fieldData['value']['default']))
		{
			$defaultValue		= $fieldData['value']['default'];
		}

		return '<fieldset '. $editable .'><textarea class="form-control" rows="3" id="' . $fieldName . '" name="' . $fieldName . '">'. $defaultValue .'</textarea></fieldset>';
	}

	public function imageField($fieldName, $fieldData, $value, $params)
	{
		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		if(empty($defaultValue) && isset($fieldData['value']['default']))
		{
			$defaultValue		= $fieldData['value']['default'];
		}

		$uploadPath				= $fieldData['value']['uploadPath'];
		$imageFieldHtml			= '<fieldset '. $editable .'><div class="adminImageUploadArea" id="adminImageUploadArea-' . $fieldName . '">';
		if(!empty($defaultValue))
		{
			$imageLink			= Config::$plfDirectory . 'content/' . APPLICATIONNAME . '/' . $defaultValue;
			$imageFieldHtml		.= '<img src="' . $imageLink . '" />';
		}else{
			$imageFieldHtml		.= '<span>Drag image here!</span>';
		}
		$imageFieldHtml			.= '</div>';
		$imageFieldHtml			.= '<input type="file" class="fakeInput" id="fakeInput-' . $fieldName . '" name="fakeInput-' . $fieldName . '" />';
		$imageFieldHtml			.= '<div class="progress" id="progress-'. $fieldName .'"><div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">';
		$imageFieldHtml			.= '0%</div></div>';
		$imageFieldHtml			.= '</fieldset>';
		$imageFieldHtml			.= '<input type="hidden" name="' . $fieldName . '" value="' . $defaultValue . '" id="' . $fieldName . '"/>';
		$imageFieldHtml			.= '<script type="text/javascript">window.addEventListener(\'load\',  function(){new plfImageField(\'' . $fieldName . '\', \'' . Adminconfig::$adminPath . '\', \'' . $uploadPath . '\');});</script>';
		return $imageFieldHtml;
	}

	public function richtextField($fieldName, $fieldData, $value, $params)
	{
		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$defaultValue			= (is_null($value)) ? '' : $value;
		if(empty($defaultValue) && isset($fieldData['value']['default']))
		{
			$defaultValue		= $fieldData['value']['default'];
		}

		$richTextHtml			= '<fieldset '. $editable .'>';
		$richTextHtml			.= '<div class="btn-group">';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextBold">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-bold"></i>Bold</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextItalic">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-italic"></i>Italic</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextUnderline">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-text-color"></i>Underline</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextHead">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-header"></i>Head</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextLeft">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-align-left"></i>Left</button>';
		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextCenter">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-align-center"></i>Center</button>';
		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextRight">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-align-right"></i>Right</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextOrderedList">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-list"></i>Ordered List</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextUnorderedList">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-th-list"></i>Unordered List</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextCreateLink">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-link"></i>Create Link</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextAddPicture">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-picture"></i>Add Picture</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextCut">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-scissors"></i>Cut</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextCopy">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-copy"></i>Copy</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextPaste">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-paste"></i>Paste</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextUndo">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-arrow-left"></i>Undo</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextRedo">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-arrow-right"></i>Redo</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextHtml">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-modal-window"></i>Html Code</button>';

		$richTextHtml			.= '<button class="btn btn-default" type="button" id="btnRichTextRemoveFormat">';
		$richTextHtml			.= '<i class="glyphicon glyphicon-modal-trash"></i>Remove Format</button>';

		$richTextHtml			.= '</div><div class="richTextField form-control" id="adminRichTextDiv" contenteditable="true">'. $defaultValue .'</div>';
		$richTextHtml			.= '</fieldset>';
		$richTextHtml			.= '<input type="hidden" name="' . $fieldName . '" value="" id="' . $fieldName . '"/>';
		$richTextHtml			.= '<script type="text/javascript">window.addEventListener(\'load\', function(){new plfRichText(\'' . $fieldName . '\');});</script>';

		return $richTextHtml;
	}

	public function manyToManyField($fieldName, $fieldData, $value, $params){

		$editable				= '';
		if(isset($fieldData['value']['editable']))
		{
			if(!$fieldData['value']['editable'])
			{
				$editable		= 'disabled';
			}
		}

		$options			= [];
		$objectPk			= (isset($params[3])) ? $params[3] : 0;
		$mtmModule			= $params[2];

		if($objectPk)
		{
			$nameField		= $mtmModule->fieldName();
			$fieldName		= $fieldData['name'];
			$mtmList		= $params[0]->selectMtm($fieldName, $nameField)->get($objectPk);

			foreach($mtmList->$fieldName as $mtmObject)
			{
				$options[]		= ['value'=>$mtmObject->pk, 'name'=>$mtmObject->$nameField];
			}
		}

		$mtmFieldHtml		= '<fieldset '. $editable .'>';
		$mtmFieldHtml		.= '<div class="col-sm-9"><select class="form-control" id="' . $fieldName . '" name="' . $fieldName . '[]" multiple size="10">';
		foreach($options as $option)
		{
			$mtmFieldHtml	.= '<option value="' . $option['value'] . '">' . $option['name'] . '</option>';
		}
		$mtmFieldHtml		.= '</select></div>';
		$mtmFieldHtml		.= '<div class="col-xs-offset-1 col-sm-1"><button type="button" id="mtmPlusButton-' . $fieldName . '" class="btn btn-default btn-block mtmPlusButton">+</button>';
		$mtmFieldHtml		.= '<button type="button" id="mtmMinusButton-' . $fieldName . '" class="btn btn-default btn-block">-</button></div>';
		$mtmFieldHtml		.= '</fieldset>';
		$mtmFieldHtml		.= '<script type="text/javascript">window.addEventListener(\'load\', function(){new plfMtmField(\'' . $fieldName . '\', \'' . Adminconfig::$adminPath . '\', \'' . $mtmModule->name . '\');});</script>';

		return $mtmFieldHtml;

	}
}