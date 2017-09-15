<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 ************************************************************************************/
include_once('vtlib/Head/Utils.php');

/**
 * Provides API to work with vtiger CRM Profile
 * @package vtlib
 */
class Head_Profile {

        var $id; 
        var $name; 
        var $desc; 

        public function save() { 
            if (!$this->id) { 
                    $this->create(); 
            } else { 
                    $this->update(); 
            } 
        } 

        private function create() { 
            global $adb; 

            $this->id = $adb->getUniqueID('jo_profile'); 

            $sql = "INSERT INTO jo_profile (profileid, profilename, description) 
                            VALUES (?,?,?)"; 
            $binds = array($this->id, $this->name, $this->desc); 
            $adb->pquery($sql, $binds); 

            $sql = "INSERT INTO jo_profile2field (profileid, tabid, fieldid, visible, readonly) 
                            SELECT ?, tabid, fieldid, 0, 0 
                            FROM jo_field"; 
            $binds = array($this->id); 
            $adb->pquery($sql, $binds); 

            $sql = "INSERT INTO jo_profile2tab (profileid, tabid, permissions) 
                            SELECT ?, tabid, 0 
                            FROM jo_tab"; 
            $binds = array($this->id); 
            $adb->pquery($sql, $binds); 

            $sql = "INSERT INTO jo_profile2standardpermissions (profileid, tabid, Operation, permissions) 
                            SELECT ?, tabid, actionid, 0 
                    FROM jo_actionmapping, jo_tab 
                            WHERE actionname IN ('Save', 'CreateView', 'EditView', 'Delete', 'index', 'DetailView') AND isentitytype = 1"; 
            $binds = array($this->id); 
            $adb->pquery($sql, $binds); 

            self::log("Initializing profile permissions ... DONE"); 
        } 

        private function update() { 
            throw new Exception("Not implemented"); 
        } 
 		 
        /**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delimit=true) {
		Head_Utils::Log($message, $delimit);
	}

	/**
	 * Initialize profile setup for Field
	 * @param Head_Field Instance of the field
	 * @access private
	 */
	static function initForField($fieldInstance) {
		global $adb;

		// Allow field access to all
		$adb->pquery("INSERT INTO jo_def_org_field (tabid, fieldid, visible, readonly) VALUES(?,?,?,?)",
			Array($fieldInstance->getModuleId(), $fieldInstance->id, '0', '0'));

		$profileids = self::getAllIds();
		foreach($profileids as $profileid) {
			$adb->pquery("INSERT INTO jo_profile2field (profileid, tabid, fieldid, visible, readonly) VALUES(?,?,?,?,?)",
				Array($profileid, $fieldInstance->getModuleId(), $fieldInstance->id, '0', '0'));
		}
	}

	/**
	 * Delete profile information related with field.
	 * @param Head_Field Instance of the field
	 * @access private
	 */
	static function deleteForField($fieldInstance) {
		global $adb;

		$adb->pquery("DELETE FROM jo_def_org_field WHERE fieldid=?", Array($fieldInstance->id));
		$adb->pquery("DELETE FROM jo_profile2field WHERE fieldid=?", Array($fieldInstance->id));
	}

	/**
	 * Get all the existing profile ids
	 * @access private
	 */
	static function getAllIds() {
		global $adb;
		$profileids = Array();
		$result = $adb->pquery('SELECT profileid FROM jo_profile', array());
		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$profileids[] = $adb->query_result($result, $index, 'profileid');
		}
		return $profileids;
	}

	/**
	 * Initialize profile setup for the module
	 * @param Head_Module Instance of module
	 * @access private
	 */
	static function initForModule($moduleInstance) {
		global $adb;

		$actionids = Array();
		$result = $adb->pquery("SELECT actionid from jo_actionmapping WHERE actionname IN (?,?,?,?,?,?)", array('Save','EditView','CreateView','Delete','index','DetailView'));
		/* 
		 * NOTE: Other actionname (actionid >= 5) is considered as utility (tools) for a profile.
		 * Gather all the actionid for associating to profile.
		 */
		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$actionids[] = $adb->query_result($result, $index, 'actionid');
		}

		$profileids = self::getAllIds();		

		foreach($profileids as $profileid) {			
			$adb->pquery("INSERT INTO jo_profile2tab (profileid, tabid, permissions) VALUES (?,?,?)",
				Array($profileid, $moduleInstance->id, 0));

			if($moduleInstance->isentitytype) {
				foreach($actionids as $actionid) {
					$adb->pquery(
						"INSERT INTO jo_profile2standardpermissions (profileid, tabid, Operation, permissions) VALUES(?,?,?,?)",
						Array($profileid, $moduleInstance->id, $actionid, 0));
				}
			}
		}
		self::log("Initializing module permissions ... DONE");
	}

	/**
	 * Delete profile setup of the module
	 * @param Head_Module Instance of module
	 * @access private
	 */
	static function deleteForModule($moduleInstance) {
		global $adb;
		$adb->pquery("DELETE FROM jo_profile2tab WHERE tabid=?", Array($moduleInstance->id));
		$adb->pquery("DELETE FROM jo_profile2standardpermissions WHERE tabid=?", Array($moduleInstance->id));
	}
}
?>
