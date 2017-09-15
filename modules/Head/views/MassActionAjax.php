<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 *************************************************************************************/

class Head_MassActionAjax_View extends Head_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showMassEditForm');
		$this->exposeMethod('showAddCommentForm');
		$this->exposeMethod('showComposeEmailForm');
		$this->exposeMethod('showSendSMSForm');
		$this->exposeMethod('showDuplicatesSearchForm');
		$this->exposeMethod('transferOwnership');
	}

	function process(Head_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function returns the mass edit form
	 * @param Head_Request $request
	 */
	function showMassEditForm(Head_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$this->initMassEditViewContents($request);
		echo $viewer->view('MassEditForm.tpl', $moduleName, true);
	}

	function initMassEditViewContents(Head_Request $request) {
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);

		$moduleModel = Head_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Head_RecordStructure_Model::getInstanceForModule($moduleModel, Head_RecordStructure_Model::RECORD_STRUCTURE_MODE_MASSEDIT);
		$fieldInfo = array();
		$fieldList = $moduleModel->getFields();
		foreach ($fieldList as $fieldName => $fieldModel) {
			$fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
		}
		$picklistDependencyDatasource = Head_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$recordStructure = $recordStructureInstance->getStructure();
		foreach($recordStructure as $blockName => $fields) {
			if(empty($fields)) {
				unset($recordStructure[$blockName]);
			}
		}

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Head_Functions::jsonEncode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODE', 'massedit');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('MODULE_MODEL',$moduleModel); 
		$viewer->assign('MASS_EDIT_FIELD_DETAILS',$fieldInfo); 
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('MODULE_MODEL', $moduleModel);
        //do not show any image details in mass edit form
        $viewer->assign('IMAGE_DETAILS', array());
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}
        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }
	}
	
	/**
	 * Function returns the Add Comment form
	 * @param Head_Request $request
	 */
	function showAddCommentForm(Head_Request $request){
		$sourceModule = $request->getModule();
		$moduleName = 'ModComments';
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        
        $modCommentsModel = Head_Module_Model::getInstance($moduleName);
		$fileNameFieldModel = Head_Field::getInstance("filename", $modCommentsModel);
        $fileFieldModel = Head_Field_Model::getInstanceFromFieldObject($fileNameFieldModel);
        
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}

        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }
        $viewer->assign('FIELD_MODEL', $fileFieldModel);
        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Head_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Head_Util_Helper::getMaxUploadSizeInBytes());

		echo $viewer->view('AddCommentForm.tpl',$moduleName,true);
	}

	/**
	 * Function returns the Compose Email form
	 * @param Head_Request $request
	 */
	function showComposeEmailForm(Head_Request $request) {
		$moduleName = 'Emails';
		$sourceModule = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$step = $request->get('step');
		$relatedLoad = $request->get('relatedLoad');
		
		$emailFieldsInfo = $this->getEmailFieldsInfo($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('EMAIL_FIELDS_INFO', $emailFieldsInfo);
		$viewer->assign('MODULE', $moduleName);
        $viewer->assign('SOURCE_MODULE',$sourceModule);
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('SELECTED_EMAIL_SOURCE_MODULE', $sourceModule);
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}
        
        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }

		$to = $request->get('to');
		if (!$to) {
			$to = array();
		}
		$viewer->assign('TO', $to);

		$parentModule = $request->get('sourceModule');
		$parentRecord = $request->get('sourceRecord');
		if (!empty($parentModule)) {
			$viewer->assign('PARENT_MODULE', $parentModule);
			$viewer->assign('PARENT_RECORD', $parentRecord);
			$viewer->assign('RELATED_MODULE', $sourceModule);
		}
		if($relatedLoad){
			$viewer->assign('RELATED_LOAD', true);
		}

		if($step == 'step1') {
			echo $viewer->view('SelectEmailFields.tpl', $request->getModule(), true);
			exit;
		}
	}
	
	protected function getEmailFieldsInfo(Head_Request $request) {
		$sourceModule = $request->getModule();
		$emailFieldsInfo = array();
		$moduleModel = Head_Module_Model::getInstance($sourceModule);
		$recipientPrefModel = Head_RecipientPreference_Model::getInstance($sourceModule);
		
		if($recipientPrefModel)
		$recipientPrefs = $recipientPrefModel->getPreferences();
		$moduleEmailPrefs = $recipientPrefs[$moduleModel->getId()];
		$emailFields = $moduleModel->getFieldsByType('email');
        $accesibleEmailFields = array();
		
        foreach($emailFields as $index=>$emailField) {
            $fieldName = $emailField->getName();
            if($emailField->isViewable()) {
				if($moduleEmailPrefs && in_array($emailField->getId(),$moduleEmailPrefs)){
					$emailField->set('isPreferred',true);
				}
                $accesibleEmailFields[$fieldName] = $emailField;
            }
        }
		
        $emailFields = $accesibleEmailFields;
        if(count($emailFields) > 0) {
            $recordIds = $this->getRecordsListFromRequest($request);
			global $current_user;
            $baseTableId = $moduleModel->get('basetableid');
            $queryGen = new QueryGenerator($moduleModel->getName(), $current_user);
			$selectFields = array_keys($emailFields);
            array_push($selectFields,'id');
			$queryGen->setFields($selectFields);
			$query = $queryGen->getQuery();
            $query =  $query.' AND crmid IN ('.  generateQuestionMarks($recordIds).')';
			$emailOptout = $moduleModel->getField('emailoptout');
			if($emailOptout) {
				$query .= ' AND '.$emailOptout->get('column').' = 0';
			}
			
            $db = PearDatabase::getInstance();
            $result = $db->pquery($query,$recordIds);
            $num_rows = $db->num_rows($result);
			
			if($num_rows > 0) {
				for($i=0;$i<$num_rows;$i++){
					$emailFieldsList = array();
					foreach ($emailFields as $emailField) {
						$emailValue = $db->query_result($result, $i, $emailField->get('column')) ;
						if(!empty($emailValue)) {
							$emailFieldsList[$emailValue] = $emailField;
						}
					}
					if(!empty($emailFieldsList)) {
                        $recordId = $db->query_result($result, $i,$baseTableId);
						$emailFieldsInfo[$moduleModel->getName()][$recordId] = $emailFieldsList;
					}
				}
			}
        }
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDS_COUNT', count($recordIds));
		
		if($recipientPrefModel && !empty($recipientPrefs)) {
			$viewer->assign('RECIPIENT_PREF_ENABLED',true);
		}

		$viewer->assign('EMAIL_FIELDS', $emailFields);

		$viewer->assign('PREF_NEED_TO_UPDATE',  $this->isPreferencesNeedToBeUpdated($request));
		return $emailFieldsInfo;
	}
	
	protected function isPreferencesNeedToBeUpdated(Head_Request $request) {
		$sourceModule = $request->getModule();
		$moduleModel = Head_Module_Model::getInstance($sourceModule);
		$recipientPrefModel = Head_RecipientPreference_Model::getInstance($sourceModule);
		$status = false;
		
		if(!$recipientPrefModel) return $status;
		$recipientPrefs = $recipientPrefModel->getPreferences();
		if(empty($recipientPrefs))	return true;
		$moduleEmailPrefs = $recipientPrefs[$moduleModel->getId()];
		if(!$moduleEmailPrefs) return $status;
		foreach ($moduleEmailPrefs as $fieldId) {
			$field = Head_Field_Model::getInstance($fieldId, $moduleModel);
			if($field) {
				if(!$field->isActiveField()) {
					$status = true;
				}
			}else{
				$status = true;
			}
		}
		return $status;
	}

	/**
	 * Function shows form that will lets you send SMS
	 * @param Head_Request $request
	 */
	function showSendSMSForm(Head_Request $request) {

		$sourceModule = $request->getModule();
		$moduleName = 'SMSNotifier';
		$selectedIds = $this->getRecordsListFromRequest($request);
		$excludedIds = $request->get('excluded_ids');
		$cvId = $request->get('viewname');

		$user = Users_Record_Model::getCurrentUserModel();
        $moduleModel = Head_Module_Model::getInstance($sourceModule);
        $phoneFields = $moduleModel->getFieldsByType('phone');
		$viewer = $this->getViewer($request);
		
		if(count($selectedIds) == 1){
			$recordId = $selectedIds[0];
			$selectedRecordModel = Head_Record_Model::getInstanceById($recordId, $sourceModule);
			$viewer->assign('SINGLE_RECORD', $selectedRecordModel);
		}
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', $user);
		$viewer->assign('PHONE_FIELDS', $phoneFields);
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}

        $searchParams = $request->get('search_params');
        if(!empty($searchParams)) {
            $viewer->assign('SEARCH_PARAMS',$searchParams);
        }
        
		echo $viewer->view('SendSMSForm.tpl', $moduleName, true);
	}

	/**
	 * Function returns the record Ids selected in the current filter
	 * @param Head_Request $request
	 * @return integer
	 */
	function getRecordsListFromRequest(Head_Request $request, $module = false) {
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
        if(empty($module)) {
            $module = $request->getModule();
        }
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		if ($sourceRecord && $sourceModule) {
			$sourceRecordModel = Head_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			return $sourceRecordModel->getSelectedIdsList($module, $excludedIds);
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if($customViewModel) {
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if(!empty($operator)) {
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', $searchValue);
			}
            $customViewModel->set('search_params', $request->get('search_params'));
			return $customViewModel->getRecordIds($excludedIds,$module);
		}
	}

	/**
	 * Function shows the List of Mail Merge Templates
	 * @param Head_Request $request
	 */
	function showMailMergeTemplates(Head_Request $request) {
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$cvId = $request->get('viewname');
		$module = $request->getModule();
		$templates = Settings_MailMerge_Record_Model::getByModule($module);

		$viewer = $this->getViewer($request);
		$viewer->assign('TEMPLATES', $templates);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('MODULE', $module);

		return $viewer->view('showMergeTemplates.tpl', $module);
	}

	/**
	 * Function shows the duplicate search form
	 * @param Head_Request $request
	 */
	function showDuplicatesSearchForm(Head_Request $request) {
		$module = $request->getModule();
		$moduleModel = Head_Module_Model::getInstance($module);
		$fields = $moduleModel->getFields();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$viewer->assign('FIELDS', $fields);
		$viewer->view('showDuplicateSearch.tpl', $module);
	}
	
	function transferOwnership(Head_Request $request){
		$module = $request->getModule();
		$moduleModel = Head_Module_Model::getInstance($module);

		$relatedModules = $moduleModel->getRelations();
		//User doesn't have the permission to edit related module,
		//then don't show that module in related module list.
		foreach ($relatedModules as $key => $relModule) {
			if (!Users_Privileges_Model::isPermitted($relModule->get('relatedModuleName'), 'EditView')) {
				unset($relatedModules[$key]);
			}
		}
		
		$viewer = $this->getViewer($request);
		$skipModules = array('Emails');
		$viewer->assign('MODULE',$module);
		$viewer->assign('RELATED_MODULES', $relatedModules);
		$viewer->assign('SKIP_MODULES', $skipModules);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TransferRecordOwnership.tpl', $module);
	}
}
