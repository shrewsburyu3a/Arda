function ojAlert(options) {
	var deferredObject = jQuery.Deferred();
	var defaults = {
		type: "alert", //alert, prompt,confirm
		modalSize: 'modal-sm', //modal-sm, modal-lg
		okButtonText: 'Ok',
		cancelButtonText: 'Cancel',
		yesButtonText: 'Yes',
		noButtonText: 'No',
		headerText: 'Attention',
		messageText: 'Message',
		alertType: 'default', //default, primary, success, info, warning, danger
		inputFieldType: 'text', //could ask for number,email,etc
	}
	jQuery.extend(defaults, options);

	var _show = function () {
		var headClass = "navbar-default";
		switch (defaults.alertType) {
			case "primary":
				headClass = "alert-primary";
				break;
			case "success":
				headClass = "alert-success";
				break;
			case "info":
				headClass = "alert-info";
				break;
			case "warning":
				headClass = "alert-warning";
				break;
			case "danger":
				headClass = "alert-danger";
				break;
		}
		jQuery('BODY').append(
				  '<div id="ojAlerts" class="modal fade">' +
				  '<div class="modal-dialog" class="' + defaults.modalSize + '">' +
				  '<div class="modal-content">' +
				  '<div id="ojAlerts-header" class="modal-header ' + headClass + '">' +
				  '<h4 id="ojAlerts-title" class="modal-title">Modal title</h4>' +
				  '<button id="close-button" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' +
				  '</div>' +
				  '<div id="ojAlerts-body" class="modal-body">' +
				  '<div id="ojAlerts-message" ></div>' +
				  '</div>' +
				  '<div id="ojAlerts-footer" class="modal-footer">' +
				  '</div>' +
				  '</div>' +
				  '</div>' +
				  '</div>'
				  );

		jQuery('.modal-header').css({
			'padding': '15px 15px',
			'-webkit-border-top-left-radius': '5px',
			'-webkit-border-top-right-radius': '5px',
			'-moz-border-radius-topleft': '5px',
			'-moz-border-radius-topright': '5px',
			'border-top-left-radius': '5px',
			'border-top-right-radius': '5px'
		});

		jQuery('#ojAlerts-title').text(defaults.headerText);
		console.debug('defaults.messageText', defaults.messageText);
		if (defaults.messageText !== 'none')
		{
			jQuery('#ojAlerts-message').html(defaults.messageText);
		}
		var keyb = false, backd = "static";
		var calbackParam = "";
		switch (defaults.type) {
			case 'alert':
				keyb = true;
				backd = "true";
				jQuery('#ojAlerts-footer').html('<button class="btn btn-' + defaults.alertType + '">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
					calbackParam = true;
					jQuery('#ojAlerts').modal('hide');
				});
				break;
			case 'confirm':
				var btnhtml = '<button id="ojok-btn" class="btn btn-primary">' + defaults.yesButtonText + '</button>';
				if (defaults.noButtonText && defaults.noButtonText.length > 0) {
					btnhtml += '<button id="ojclose-btn" class="btn btn-default">' + defaults.noButtonText + '</button>';
				}
				jQuery('#ojAlerts-footer').html(btnhtml).on('click', 'button', function (e) {
					if (e.target.id === 'ojok-btn') {
						calbackParam = true;
						jQuery('#ojAlerts').modal('hide');
					} else if (e.target.id === 'ojclose-btn') {
						calbackParam = false;
						jQuery('#ojAlerts').modal('hide');
					}
				});
				break;
			case 'prompt':
				jQuery('#ojAlerts-message').html(defaults.messageText + '<br /><br /><div class="form-group"><input type="' + defaults.inputFieldType + '" class="form-control" id="prompt" /></div>');
				jQuery('#ojAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
					calbackParam = jQuery('#prompt').val();
					jQuery('#ojAlerts').modal('hide');
				});
				break;
			case 'custom':
				var flds = defaults.custom.split(',');
				console.debug("flds", flds);
				var html = (defaults.messageText === 'none' ? "" : ("<h3>" + defaults.messageText + '</h3>')) + '<div class="oj-alert-controls">';
				var ev = {};
				for (var n = 0; n < flds.length; n++)
				{
					var fldid = flds[n];
					console.debug("defaults", defaults);
					console.debug("field", fldid);
					if (defaults.hasOwnProperty(fldid))
					{
						var fld = defaults[fldid];
						var lbl = fld.label;
						var disp = "";
						if (fld.hasOwnProperty('display'))
						{
							disp = ' style="display:' + fld["display"] + ';"';
						}
						if (fld.hasOwnProperty('events'))
						{
							ev[fldid] = fld["events"];
						}
						html += '<div class="oj-alert-control-div" id="oj-alert-control-div-' + fldid + '"' + disp + '><label class="oj-alert-label" for="' + fldid + '">' + lbl + '</label>';
						if (fldid.startsWith("prompt"))
						{
							var promptval = "";
							if (fld.hasOwnProperty('value'))
							{
								promptval = ' value="' + fld['value'] + '"';
							}
							html += '<input type="text" class="oj-alert-control" id="' + fldid + '"' + promptval + '/>';
						} else if (fldid.startsWith("password"))
						{
							var promptval = "";
							if (fld.hasOwnProperty('value'))
							{
								promptval = ' value="' + fld['value'] + '"';
							}
							html += '<input type="password" class="oj-alert-control" id="' + fldid + '"' + promptval + '/>';
						} else if (fldid.startsWith("check"))
						{
							var promptval = "";
							if (fld.hasOwnProperty('value'))
							{
								promptval = ' value="' + fld['value'] + '"';
							}
							html += '<input type="checkbox" class="oj-alert-control" id="' + fldid + '"' + promptval + '/>';
						} else if (fldid.startsWith("choice"))
						{
							html += '<select class="oj-alert-control" id="' + fldid + '">';
							var selvals = fld['options'];
							var sel = null;
							if (fld.hasOwnProperty('value'))
							{
								sel = fld['value'];
							}
							var vals = selvals.split('|');
							for (var m = 0; m < vals.length; m++)
							{
								var val = vals[m];
								var selected = val == sel ? ' selected="selected"' : '';
								html += '<option value="' + val + '"' + selected + '>' + val + '</option>';
							}
							html += '</select>';
						} else if (fldid.startsWith("dropdown"))
						{
							var lbl = fld.hasOwnProperty('label') ? fld['label'] : "select";
							var vals = fld["values"].split('|');
							html += '<div class="input-group oj-input-group"><input type="text" class="form-control oj-dropdown-text" aria-label="..." id="' + fldid + '">' +
									  '<div class="input-group-btn">' +
									  '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
									  lbl + ' <span class="caret"></span></button>' +
									  '<ul class="dropdown-menu dropdown-menu-right">';
							for (var m = 0; m < vals.length; m++)
							{
								html += '<li><a href="#" target="_self" onclick="oj_set_dropdown_text(\'' + fldid + '\', \'' + vals[m] + '\');">' + vals[m] + '</a></li>';
							}
							html += '</ul></div></div>';
						}
						html += '</div>';
					}
				}
				html += '</div>';
				jQuery('#ojAlerts-message').html(html);
				jQuery('.oj-alert-label').css(
						  {
							  "display": "inline-block",
							  "width": "25%",
							  "text-align": "right",
							  "margin-top": "5px",
							  "padding-right": "3px"
						  });
				jQuery(".oj-alert-control").css(
						  {
							  "width": "65%"
						  });
				for (var fid in ev)
				{
					if (ev.hasOwnProperty(fid))
					{
						for (var e in ev[fid])
						{
							jQuery('#' + fid).on(e, ev[fid][e]);
						}
					}
				}
				jQuery('#ojAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText + '</button>').on('click', ".btn", function ()
				{
					calbackParam = [];
					for (var n = 0; n < flds.length; n++)
					{
						calbackParam[flds[n]] = jQuery('#' + flds[n]).val();
					}
					jQuery('#ojAlerts').modal('hide');
				});
				break;
		}

		jQuery('#ojAlerts').modal({
			show: false,
			backdrop: backd,
			keyboard: keyb
		}).on('hidden.bs.modal', function (e) {
			jQuery('#ojAlerts').remove();
			deferredObject.resolve(calbackParam);
		}).on('shown.bs.modal', function (e) {
			if (jQuery('#prompt').length > 0) {
				if (defaults.hasOwnProperty("initialValue"))
				{
					jQuery('#prompt').val(defaults["initialValue"]);
				}
				jQuery('#prompt').focus();
			}
		}).modal('show');
	}

	_show();
	return deferredObject.promise();
}

