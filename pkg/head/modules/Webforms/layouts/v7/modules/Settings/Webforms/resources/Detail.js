/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 *************************************************************************************/

Settings_Head_Detail_Js('Settings_Webforms_Detail_Js', {
	
	/*
	 * function to trigger delete record action
	 * @params: delete record url.
	 */
    deleteRecord : function(deleteRecordActionUrl) {
        app.helper.showConfirmationBox({
            message:app.vtranslate('LBL_DELETE_CONFIRMATION')
        }).then(function() {
            app.request.post({'url':deleteRecordActionUrl}).then(
            function(e,res){
                if(!e) {
                    window.location.href = res;
                } else {
                    app.helper.showErrorNotification({
                        'message' : e
                    });
                }
            });
        });
	},
	
	/** 
	 * Function to trigger show webform record action
	 * @params: show webform record url
	 */
	showForm : function(record){
       var params = {
          'module' : 'Webforms',
          'record' : record,
          'view' : 'ShowForm',
          'parent' : 'Settings'
       }
		app.request.get({data:params}).then(
			function(error, data){
                var callback = function(container){
                    //show html without rendering
					var showFormContents = container.find('pre').html();
					showFormContents = showFormContents + '<script  type="text/javascript">'+
					'window.onload = function() { '+
					'var N=navigator.appName, ua=navigator.userAgent, tem;'+
					'var M=ua.match(/(opera|chrome|safari|firefox|msie)\\/?\\s*(\\.?\\d+(\\.\\d+)*)/i);'+
					'if(M && (tem= ua.match(/version\\/([\\.\\d]+)/i))!= null) M[2]= tem[1];'+
					 'M=M? [M[1], M[2]]: [N, navigator.appVersion, "-?"];'+
					'var browserName = M[0];'+

						'var form = document.getElementById("__vtigerWebForm"), '+
						'inputs = form.elements; '+
						'form.onsubmit = function() { '+
							'var required = [], att, val; '+
							'for (var i = 0; i < inputs.length; i++) { '+
								'att = inputs[i].getAttribute("required"); '+
								'val = inputs[i].value; '+
								'type = inputs[i].type; '+
								'if(type == "email") {'+
									'if(val != "") {'+
										'var elemLabel = inputs[i].getAttribute("label");'+
										'var emailFilter = /^[_/a-zA-Z0-9]+([!"#$%&()*+,./:;<=>?\\^_`{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\\_\\-\\.]?[a-zA-Z0-9]+)*\\.([\\-\\_]?[a-zA-Z0-9])+(\\.?[a-zA-Z0-9]+)?$/;'+
										'var illegalChars= /[\\(\\)\\<\\>\\,\\;\\:\\\"\\[\\]]/ ;'+
										'if (!emailFilter.test(val)) {'+
											'alert("For "+ elemLabel +" field please enter valid email address"); return false;'+
										'} else if (val.match(illegalChars)) {'+
											'alert(elemLabel +" field contains illegal characters");return false;'+
										'}'+
									'}'+
								'}'+
								'if (att != null) { '+
										'if (val.replace(/^\\s+|\\s+$/g, "") == "") { '+
												'required.push(inputs[i].getAttribute("label")); '+
										'} '+
								'} '+
							'} '+
							'if (required.length > 0) { '+
								'alert("The following fields are required: " + required.join()); '+
								'return false; '+
							'} '+
							'var numberTypeInputs = document.querySelectorAll("input[type=number]");'+
							'for (var i = 0; i < numberTypeInputs.length; i++) { '+
                                'val = numberTypeInputs[i].value;'+
                                'var elemLabel = numberTypeInputs[i].getAttribute("label");'+
								'var elemDataType = numberTypeInputs[i].getAttribute("datatype");'+
								'if(val != "") {'+
									'if(elemDataType == "double") {'+
										'var numRegex = /^[+-]?\\d+(\\.\\d+)?$/;'+
									'}else{'+
									'var numRegex = /^[+-]?\\d+$/;'+ 
									'}'+
									'if (!numRegex.test(val)) {'+
										'alert("For "+ elemLabel +" field please enter valid number"); return false;'+
									'}'+
                                '}'+
							'}'+
							'var dateTypeInputs = document.querySelectorAll("input[type=date]");' +
							'for (var i = 0; i < dateTypeInputs.length; i++) {' +
							'dateVal = dateTypeInputs[i].value;' +
							'var elemLabel = dateTypeInputs[i].getAttribute("label");' +
							'if(dateVal != "") {' +
							'var dateRegex = /^[1-9][0-9]{3}-(0[1-9]|1[0-2]|[1-9]{1})-(0[1-9]|[1-2][0-9]|3[0-1]|[1-9]{1})$/;' +
							'if(!dateRegex.test(dateVal)) {' +
							'alert("For "+ elemLabel +" field please enter valid date in required format"); return false;' +
							'}}}';
                    if(container.find('[name=isCaptchaEnabled]').val() == true) {
                        showFormContents = Settings_Webforms_Detail_Js.getCaptchaCode(showFormContents);
                    } else {
                        showFormContents = showFormContents +
						'}; '+
                        '}'+
					'</script>';
                    }
					//Html contents should be placed inside textarea element
					container.find('#showFormContent').text(showFormContents);
					//Rendering content has been removed from container
					container.find('pre').remove();
					container.find('code').remove();
				};
                
                app.helper.showModal(data, {
                    'cb' : callback
                });            
			}
		)
	},
    
    /**
     * Function get Captcha Code
     * @param <string> showFormContents
     * @return <string> showFormContents
     */
    getCaptchaCode : function(showFormContents) {
        var captchaContents = '<script type="text/javascript">'+
        'var RecaptchaOptions = { theme : "clean" };' +
        '</script>'+
        '<script type="text/javascript"'+
        'src="http://www.google.com/recaptcha/api/challenge?k=6Lchg-wSAAAAAIkV51_LSksz6fFdD2vgy59jwa38">'+
        '</script>'+
        '<noscript>'+
            '<iframe src="http://www.google.com/recaptcha/api/noscript?k=6Lchg-wSAAAAAIkV51_LSksz6fFdD2vgy59jwa38"'+
                'height="300" width="500" frameborder="0"></iframe><br>'+
            '<textarea name="recaptcha_challenge_field" rows="3" cols="40">'+
            '</textarea>'+
            '<input type="hidden" name="recaptcha_response_field" value="manual_challenge">'+
        '</noscript>';
        showFormContents = showFormContents.replace('<div id="captchaField"></div>',captchaContents);
        showFormContents = showFormContents +
                'var recaptchaValidationValue = document.getElementById("recaptcha_validation_value").value;'+
                'if (recaptchaValidationValue!= true){'+
                    'var recaptchaResponseElement = document.getElementsByName("recaptcha_response_field")[0].value;'+
                    'var recaptchaChallengeElement = document.getElementsByName("recaptcha_challenge_field")[0].value;'+
                    'var captchaUrl = document.getElementById("captchaUrl").value;'+
                    'var url = captchaUrl+"?recaptcha_response_field="+recaptchaResponseElement;'+
                    'url = url + "&recaptcha_challenge_field="+recaptchaChallengeElement+"&callback=JSONPCallback";'+
                    'jsonp.fetch(url);'+
                    'return false;'+
                '}'+
            '}; '+
        '};'+
        'var jsonp = {' +
            'callbackCounter: 0,'+

            'fetch: function(url) {'+
                'url = url +"&callId="+this.callbackCounter;'+
                'var scriptTag = document.createElement("SCRIPT");'+
                'scriptTag.src = url;'+
                'scriptTag.async = true;'+
                'scriptTag.id = "JSONPCallback_"+this.callbackCounter;'+
                'scriptTag.type = "text/javascript";'+
                'document.getElementsByTagName("HEAD")[0].appendChild(scriptTag);'+
                'this.callbackCounter++;'+
            '}'+
        '};'+
        'function JSONPCallback(data) {'+
            'if(data.result.success == true) {'+
                'document.getElementById("recaptcha_validation_value").value = true;'+
                'var form = document.getElementById("__vtigerWebForm");'+
                'form.submit();'+
            '} else {'+
                'document.getElementById("recaptcha_reload").click();'+
                'alert("you entered wrong captcha");'+
            '}'+
            'var element = document.getElementById("JSONPCallback_"+data.result.callId);'+
            'element.parentNode.removeChild(element);'+
        '}'+
        '</script>';
  
        return showFormContents;
    }
}, {
	/**
	 * Function which will handle the registrations for the elements
	 */
	registerEvents : function() {
		this._super();
	}
})