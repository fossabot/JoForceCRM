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

class Products_QuickCreateAjax_View extends Head_QuickCreateAjax_View {

	public function process(Head_Request $request) {
		$moduleName = $request->getModule();

		$recordModel = Head_Record_Model::getCleanInstance($moduleName);
		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();

		$viewer = $this->getViewer($request);
		$viewer->assign('BASE_CURRENCY_NAME', 'curname' . $baseCurrenctDetails['currencyid']);
		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		$viewer->assign('TAXCLASS_DETAILS', $recordModel->getTaxClassDetails());

		parent::process($request);
	}
}