{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
 * Contributor(s): JoForce.com
  *
 ********************************************************************************/
-->*}
<script type="text/javascript">
	Head_Barchat_Widget_Js('Head_LeadsByIndustry_Widget_Js',{},{});
</script>

<div class="dashboardWidgetHeader">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
</div>
<div class="dashboardWidgetContent">
    {if count($DATA) gt 0 }
        <input class="widgetData" type=hidden value='{Head_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA))}' />
        <input class="yAxisFieldType" type="hidden" value="{$YAXIS_FIELD_TYPE}" />
        <div style="margin:0px 10px;">
            <div class="widgetChartContainer" id="widgetChartContainer__{$WIDGET->get('linkid')}" name='chartcontent' style="height: 200px; min-width:300px; margin:0 auto">
            </div>
            <br>
        </div>
         </div> 

    {else}
        <span class="noDataMsg">
            {vtranslate('LBL_NO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
        </span>
    {/if}


</div>

<div class="widgeticons dashBoardWidgetFooter">
    <div class="filterContainer">
        <div class="row">
            <div class="col-sm-12">
                <span class="col-lg-4">
                    <span>
                        <strong>{vtranslate('Created Time', $MODULE_NAME)} &nbsp; {vtranslate('LBL_BETWEEN', $MODULE_NAME)}</strong>
                    </span>
                </span>
                <div class="col-lg-7">
                    <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="createdtime">
                        <input type="text" class="input-sm form-control" name="start" style="height:30px;"/>
                        <span class="input-group-addon">to</span>
                        <input type="text" class="input-sm form-control" name="end" style="height:30px;"/>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-sm-12">
                <span class="col-lg-4">
                        <span>
                            <strong>{vtranslate('Assigned To', $MODULE_NAME)}</strong>
                        </span>
                </span>
                <span class="col-lg-7">
                    {assign var=CURRENT_USER_ID value=$CURRENTUSER->getId()}
                    <select class="select2 col-sm-12 widgetFilter reloadOnChange" name="smownerid">
                        <option value="{$CURRENT_USER_ID}">{vtranslate('LBL_MINE')}</option>
                        <option value="">{vtranslate('LBL_ALL', $MODULE_NAME)}</option>
                        {assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
                        {if count($ALL_ACTIVEUSER_LIST) gt 1}
                            <optgroup label="{vtranslate('LBL_USERS')}">
                                {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                    {if $OWNER_ID neq $CURRENT_USER_ID}
                                        <option value="{$OWNER_ID}">{$OWNER_NAME}</option>
                                    {/if}
                                {/foreach}
                            </optgroup>
                        {/if}
                        {assign var=ALL_ACTIVEGROUP_LIST value=$CURRENTUSER->getAccessibleGroups()}
                        {if !empty($ALL_ACTIVEGROUP_LIST)}
                            <optgroup label="{vtranslate('LBL_GROUPS')}">
                                {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
                                    <option value="{$OWNER_ID}">{$OWNER_NAME}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    </select>
                </span>
            </div>
        </div>
    </div>
    <div class="footerIcons pull-right">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>
