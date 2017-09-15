<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 ************************************************************************************/

class PurchaseOrder_ProductsPopupAjax_View extends PurchaseOrder_ProductsPopup_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
	}

	function preProcess(Head_Request $request) {
		return true;
	}

	function postProcess(Head_Request $request) {
		return true;
	}

	function process (Head_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$moduleName = 'Inventory';
		$viewer->assign('MODULE_NAME',$moduleName);
		echo $viewer->view('PopupContents.tpl', $moduleName, true);
	}

}