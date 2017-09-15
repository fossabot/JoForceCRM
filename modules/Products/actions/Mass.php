<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 *************************************************************************************/

class Products_Mass_Action extends Head_Mass_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('isChildProduct');
	}

	public function checkPermission(Head_Request $request) {
		return true;
	}

	public function process(Head_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		} else {
			parent::process($request);
		}
	}

	public function isChildProduct(Head_Request $request) {
		$moduleName = $request->getModule();
		$recordIdsList = $this->getRecordsListFromRequest($request);

		$response = new Head_Response();
		if ($moduleName && $recordIdsList) {
			$moduleModel = Head_Module_Model::getInstance($moduleName);
			$areChildProducts = $moduleModel->areChildProducts($recordIdsList);

			$response->setResult($areChildProducts);
		}
		$response->emit();
	}
}
