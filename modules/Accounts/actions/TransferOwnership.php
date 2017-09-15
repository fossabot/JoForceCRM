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

class Accounts_TransferOwnership_Action extends Head_Action_Controller {
	
	function checkPermission(Head_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Head_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName, $moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Head_Request $request) {
		$module = $request->getModule();
		$moduleModel = Head_Module_Model::getInstance($module);
		$transferOwnerId = $request->get('transferOwnerId');
		$record = $request->get('record');
		if(empty($record))
			$recordIds = $this->getBaseModuleRecordIds($request);
		else
			$recordIds[] = $record;
		$relatedModuleRecordIds = $moduleModel->getRelatedModuleRecordIds($request, $recordIds);
		foreach ($recordIds as $key => $recordId) {
			array_push($relatedModuleRecordIds, $recordId);
		}
		array_merge($relatedModuleRecordIds, $recordIds);
		$moduleModel->transferRecordsOwnership($transferOwnerId, $relatedModuleRecordIds);
		
		$response = new Head_Response();
		$response->setResult(true);
		$response->emit();
	}
	
	protected function getBaseModuleRecordIds(Head_Request $request) {
		$cvId = $request->get('viewname');
		$module = $request->getModule();
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		if($selectedIds == 'all'){
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			if($customViewModel) {
				$operator = $request->get('operator');
				$searchParams = $request->get('search_params');
				if (!empty($operator)) {
					$customViewModel->set('operator', $operator);
					$customViewModel->set('search_key', $request->get('search_key'));
					$customViewModel->set('search_value', $request->get('search_value'));
				}
				if (!empty($searchParams)) {
					$customViewModel->set('search_params', $searchParams);
				}
				return $customViewModel->getRecordIds($excludedIds, $module);
			}
		}
        return array();
	}
    
    public function validateRequest(Head_Request $request) {
        $request->validateWriteAccess();
    }
}
