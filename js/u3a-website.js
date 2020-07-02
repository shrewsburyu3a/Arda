/*
 * To change this license header, choose License Headers in Pru3aect Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var last_document_category_selection = {};
var linkLocation
jQuery(document).ready(function (jQuery)
{
	'use strict';
	jQuery("body").css("display", "none");
	jQuery("body").fadeIn(2000);
	jQuery(".nav-menu a").addClass("u3a-transition");
	jQuery("a.u3a-transition").on("click", function (event)
	{
		event.preventDefault();
		linkLocation = this.href;
		jQuery("body").fadeOut(1000, redirectPage);
	});
	function redirectPage()
	{
		window.location = linkLocation;
	}

	jQuery(document).ajaxStart(function ()
	{
		jQuery("body").addClass("wait");
	});
	jQuery(document).ajaxStop(function ()
	{
		jQuery("body").removeClass("wait");
	});
	jQuery('.u3a-category-name-class').keypress(function (e)
	{
		var key = e.which;
		if (key == 13)  // the enter key code
		{
			var textid = jQuery(this).attr("id");
			if (textid)
			{
				var btnid = textid.replace('name', 'button');
				jQuery('#' + btnid).click();
			}
			return false;
		}
	});
	jQuery('input.u3a-arrow-only').keydown(function (e)
	{
		var allow_key_codes = [37, 39];
		if (jQuery.inArray(e.keyCode, allow_key_codes) < 0)
		{
			e.preventDefault();
		}
	});
//	jQuery('#u3a_upload_image-post-button').on('click', function (e)
//	{
//		e.preventDefault();
//		var form = jQuery('#u3a_upload_image-form')[0];
//		var form_data = new FormData(form);
//		jQuery.ajax({
//			type: 'POST',
//			url: settings.ajaxurl,
//			data: form_data,
//			cache: false,
//			contentType: false,
//			processData: false,
//			error: function (jqXHR, textStatus, errorThrown)
//			{
//				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
//			},
//			success: function (html)
//			{
//				console.log("success");
//			}
//		});
//	});
//	jQuery('.u3a-upload-document-post-button-class').on('click', function (e)
//	{
//		e.preventDefault();
//		var thisid = jQuery(this).attr("id");
//		var formid = thisid.replace("post-button", "form");
//		var form = jQuery('#' + formid)[0];
//		var form_data = new FormData(form);
//		jQuery.ajax({
//			type: 'POST',
//			url: settings.ajaxurl,
//			data: form_data,
//			cache: false,
//			contentType: false,
//			processData: false,
//			error: function (jqXHR, textStatus, errorThrown)
//			{
//				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
//			},
//			success: function (html)
//			{
//				console.log("success", form_data);
//				var result = JSON.parse(html);
//				swal.fire("Upload Document", result.message, result.success ? "success" : "error");
//				u3a_reload_group_page();
//			}
//		});
//	});
	jQuery("label.u3a-search-label").on("click", function (e)
	{
		var linkid = jQuery(this).attr("for");
//		console.debug("u3a-search-label click", lnkid);
		jQuery('#' + linkid).click();
	});
	jQuery('.u3a-every-select').on("change", function (e)
	{
		e.preventDefault();
//			console.debug("u3a-every-select");
		var thisid = jQuery(this).attr("id");
		var idsuffix = thisid.substr("u3a-every-".length);
		var val = jQuery(this).val();
//			console.debug("u3a-every-select", val);
		if (val === "week")
		{
			jQuery('#u3a-week-div-' + idsuffix).removeClass("u3a-invisible");
			jQuery('#u3a-week-div-' + idsuffix).addClass("u3a-visible");
			jQuery('#u3a-month-div-' + idsuffix).removeClass("u3a-visible");
			jQuery('#u3a-month-div-' + idsuffix).addClass("u3a-invisible");
		}
		else
		{
			jQuery('#u3a-week-div-' + idsuffix).removeClass("u3a-visible");
			jQuery('#u3a-week-div-' + idsuffix).addClass("u3a-invisible");
			jQuery('#u3a-month-div-' + idsuffix).removeClass("u3a-invisible");
			jQuery('#u3a-month-div-' + idsuffix).addClass("u3a-visible");
		}
	});
//	jQuery('#u3a_select-group-btn').on('click', function (e) {
//		e.preventDefault();
//
//	});

	jQuery("a[data-popup]").on('click', function (e)
	{
		window.open(jQuery(this)[0].href, "_blank");
//		console.debug(jQuery(this)[0].href);
		// Prevent the link from actually being followed
		e.preventDefault();
	});
	var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
	jQuery.fn.u3achange = function (callback)
	{
		if (MutationObserver)
		{
			var options = {
				subtree: false,
				attributes: true,
				childList: true,
				characterData: true
			};
			var observer = new MutationObserver(function (mutations)
			{
				mutations.forEach(function (e)
				{
//					console.debug("1.mutation", e);
					callback.call(e.target, e.attributeName);
//					callback.call(e);
				});
			});
			return this.each(function ()
			{
				observer.observe(this, options);
			});
		}
	};
	jQuery('ul.u3a-download-document').dropdown({autoToggle: false, "toggleText": "attach document"});
	jQuery('ul.u3a-download-image').dropdown({autoToggle: false, "toggleText": "attach image"});
	jQuery('ul.u3a-category-list').dropdown({autoToggle: true});
//	jQuery("div.dropdown-text").u3achange(function (e)
//	{
//		console.debug("2.mutation", e);
//	});
//
	jQuery("li.dropdown-item").u3achange(function (attrname)
	{
		console.debug("1.attrchange", jQuery(this), attrname);
		if (attrname === 'class' && jQuery(this).hasClass("dropdown-selected"))
		{
			var catid = jQuery(jQuery(this).children("a.dropdown-link")).children("input").val();
			var divid = jQuery(this).closest("div.u3a-category-select-div").attr("id");
			var grp = jQuery('#' + divid + " input.u3a-category-list-id-input").val();
			var typ = jQuery('#' + divid + " input.u3a-category-list-type-input").val();
			console.debug("2.attrchange", catid, divid, grp, typ);
			u3a_document_category_select1(grp, typ, catid);
			//		jQuery(this).closest("div.dropdown").siblings("select").trigger("change");
		}
	});
	jQuery.fn.scrollView = function ()
	{
		return this.each(function ()
		{
			jQuery('html, body').animate({
				scrollTop: jQuery(this).offset().top
			}, 1000);
		});
	};
	setup_TimePicker();
	jQuery(".u3a-sort-list").sortable();
	// is this a paypal page
	if (jQuery('.u3a-paypal-container').length > 0)
	{
		console.debug("has paypal container");
		var ppid = jQuery('.u3a-paypal-container').attr("id");
//		console.debug("with id", ppid);
		paypal.Buttons(
				  {
//					  funding: {
//						  disallowed: [paypal.FUNDING.CARD]
//					  },
					  createOrder: function (data, actions)
					  {
						  var affiliation = jQuery('input.u3a-name-input-class[name="member-affiliation"').val().trim();
						  var rate = affiliation.length > 0 ? jQuery('#u3a-associate-subscription-rate').val() : jQuery('#u3a-subscription-rate').val();
						  var ppaction = jQuery("#u3a-paypal-action").val();
						  if (!ppaction)
						  {
							  ppaction = "join";
						  }
						  // This function sets up the details of the transaction, including the amount and line item details.
						  return actions.order.create({
							  purchase_units: [{
									  reference_id: ppaction,
									  description: "Membership of Shrewsbury U3A",
									  amount: {
										  currency_code: "GBP",
										  value: rate
									  }
								  }]
						  });
					  },
					  onApprove: function (data, actions)
					  {
						  // This function captures the funds from the transaction.
						  return actions.order.capture().then(function (details)
						  {
							  console.debug(details, details.status);
							  var ppaction = jQuery("#u3a-paypal-action").val();
							  if (ppaction === 'join')
							  {
								  var op = jQuery(".u3a-member-op-class").val();
								  if (details.status === "COMPLETED")
								  {
									  var form_data = {};
									  for (var n = 0; n < member_fields.length; n++)
									  {
										  var value = jQuery("#u3a-member-" + member_fields[n] + "-" + op).val();
										  if (typeof value !== 'undefined')
										  {
											  form_data[member_fields[n]] = value;
											  if (jQuery("input[type='checkbox']#u3a-member-" + member_fields[n] + "-" + op).length > 0)
											  {
												  form_data[member_fields[n]] = jQuery("#u3a-member-" + member_fields[n] + "-" + op).prop("checked") ? "yes" : "no";
											  }
										  }
									  }
									  console.debug(form_data);
									  u3a_ajax(form_data, capitalizeFirstLetter(op) + " Member", gotoregister);
								  }
							  }
							  else
							  {
								  var members_id = jQuery("#u3a-member-id").val();
								  var formdata = {
									  action: "u3a_renew_membership",
									  member: members_id
								  };
							  }
//							  var msg = u3a_validate_member_details_form();
//							  console.debug("msg", msg);
//							  if (msg)
//							  {
//								  swal.fire("Incomplete Form", '"' + msg + '" must be supplied.', "error");
//							  }
//							  else
//							  {
//								  var form_data = {};
//								  for (var n = 0; n < member_fields.length; n++)
//								  {
//									  var value = jQuery("#u3a-member-" + member_fields[n] + "-" + op).val();
//									  if (typeof value !== 'undefined')
//									  {
//										  form_data[member_fields[n]] = value;
//										  if (jQuery("input[type='checkbox']#u3a-member-" + member_fields[n] + "-" + op).length > 0)
//										  {
//											  form_data[member_fields[n]] = jQuery("#u3a-member-" + member_fields[n] + "-" + op).prop("checked") ? "yes" : "no";
//										  }
//									  }
//								  }
//								  console.debug(form_data);
//								  u3a_ajax(form_data, capitalizeFirstLetter(op) + " Member");
//							  }
							  // This function shows a transaction success message to your buyer.
//							  alert('Transaction completed by ' + details.payer.name.given_name);
						  });
					  }
				  }
		).render('#' + ppid);
	}
// is this a registration page
	if (jQuery('input.um-form-field[data-key="real_email"]').length > 0)
	{
		console.debug("registration form");
		// get rid of the superfluous login button
		jQuery("div.um-right.um-half").hide();
		var qv = u3a_get_query_vars();
		if (qv.hasOwnProperty("email") && qv.hasOwnProperty("mnum"))
		{
			jQuery('input.um-form-field[data-key="real_email"]').val(qv["email"]);
//			jQuery('input.um-form-field[data-key="real_email"]').prop("disabled", true);
			jQuery('input.um-form-field[data-key="user_login"]').val(qv["mnum"]);
//			jQuery('input.um-form-field[data-key="user_login"]').prop("disabled", true);
			jQuery('div.um-register').prepend('<b>please choose a new password, then press "Register"</b>')
		}
	}
//is this the home page
	if (jQuery('div.u3a-home-page-class').length > 0)
	{
		jQuery('div.entry-content p:first-of-type').remove();
		if (window.myInterval != undefined && window.myInterval != 'undefined')
		{
			window.clearInterval(window.myInterval);
		}
		window.myInterval = setInterval(function ()
		{
			change_header_image(0);
		}, 20000);
	}
// do not allow enter on single line text forms
	jQuery('input[type="text"]').on('keypress', function (e)
	{
		console.debug("key", e.which);
		return e.which !== 13;
	});
	// is this the profile page
	if (jQuery('div.um-account-main').length > 0)
	{
//		jQuery('div.um-cover').addClass("u3a-invisible");
//		jQuery('div.um-profile-navbar').addClass("u3a-invisible");
//		jQuery('div.um-profile-nav').addClass("u3a-invisible");
//		jQuery('div.um-profile-body').addClass("u3a-invisible");
//		jQuery('div.um-profile-edit').addClass("u3a-invisible");
//		jQuery('div.um-meta-text').addClass("u3a-invisible");
//		jQuery('div.um-header').addClass("u3a-margin-top-100");
		jQuery('input#user_email').attr("readonly", "readonly");
		jQuery('select').chosen({width: "12em"});
		console.debug("chosen width");
	}
	else
	{
		jQuery('select').chosen();
		console.debug("chosen no width");
	}
	if (jQuery('div.um-account-meta-img').length > 0)
	{
		var href = jQuery('div.um-account-meta-img a').attr("href");
		jQuery('div.um-account-meta-img a').attr("href", href + "?action=edit&um_action=edit");
	}
	if (jQuery('div.um-account-meta-img-b').length > 0)
	{
		var href = jQuery('div.um-account-meta-img-b a').attr("href");
		jQuery('div.um-account-meta-img-b a').attr("href", href + "?action=edit&um_action=edit");
	}
});
function u3a_get_query_vars()
{
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for (var i = 0; i < hashes.length; i++)
	{
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}

function setup_TimePicker()
{
	var time_inputs = jQuery(".u3a-time-input");
//	console.debug(time_inputs);
	if (time_inputs.length > 0)
	{
		var timepicker = new TimePicker(time_inputs.toArray(), {
			lang: 'en',
			theme: 'blue-grey'
		});
		timepicker.on('change', function (evt)
		{
			console.debug(evt);
			var value = (evt.hour || '00') + ':' + (evt.minute || '00');
			evt.element.value = value;
		});
	}
}

function u3a_select_group_member_dialog_close(nxt, op)
{
	console.debug("u3a_select_group_member_dialog_close", nxt, op);
	var idsuffix = "-" + nxt.replace("_", "-") + "-" + op;
	var mbr = jQuery('#u3a-member-select-' + nxt + ' option:selected').text();
	var mbrs = mbr.split(" ");
	console.debug(mbrs);
	var mnum = mbrs[0].split(':')[0];
	var sname = mbrs[1].split(',')[0];
	var fname = mbrs[2].split(',')[0];
	var which1 = jQuery("input.u3a-group-coordinator-mnum-class[value='" + mnum + "']");
	var which1id = jQuery(which1).attr("id");
	var divid = which1id.replace("mnum", "outer-div");
	u3a_show(divid);
	var ncoords = jQuery(".u3a-group-coordinator-outer-div-class.u3a-visible").length;
	jQuery(".u3a-group-del-coord-button-class").prop("disabled", ncoords === 1);
	console.debug(which1id);
//	if (nxt == "be_coordinator")
//	{
//		jQuery('#u3a-group-coordinator-forename' + idsuffix).val(fname);
//		jQuery('#u3a-group-coordinator-surname' + idsuffix).val(sname);
//		jQuery('#u3a-group-coordinator-mnum' + idsuffix).val(mnum);
//	}
}

function u3a_find_member_dialog_close(nxt, op, sfx)
{
	if (typeof sfx === "undefined")
	{
		sfx = "";
	}
	console.debug("u3a_find_member_dialog_close", nxt, op, sfx);
	var idsuffix = "-" + op + sfx;
	var sel = '#u3a-found-members-' + nxt.replace(/_/g, '-') + idsuffix + ' option:selected';
	console.debug("sel", sel);
	var mbr = jQuery(sel).text();
	console.debug("mbr", mbr);
	var mbrs = mbr.split(" ");
	console.debug("mbrs", mbrs);
	var mnum = mbrs[0].split(':')[0];
	var sname = mbrs[1].split(',')[0];
	var fname = mbrs[2].split(',')[0];
	if (nxt == "be_coordinator")
	{
		console.debug("set value of", '#u3a-group-coordinator-forename' + idsuffix, "to", fname);
		jQuery('#u3a-group-coordinator-forename' + idsuffix).val(fname);
		jQuery('#u3a-group-coordinator-surname' + idsuffix).val(sname);
		jQuery('#u3a-group-coordinator-mnum' + idsuffix).val(mnum);
	}
	else if (nxt == "edit_details")
	{
		var form_data = {
			action: "u3a_get_member_details",
			op: op,
			membership_number: mnum
		};
//		console.debug(form_data);
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					jQuery("#u3a-member-edit-details" + sfx).html(returned["message"]);
				}
				else
				{
					swal.fire("Get Member Details", returned["message"], "error");
				}
			}
		});
	}
	else if (nxt == "change_status")
	{
		var form_data = {
			action: "u3a_get_member_status",
			op: op,
			membership_number: mnum
		};
//		console.debug(form_data);
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					jQuery("#u3a-member-change-" + op + sfx).html(returned["message"]);
				}
				else
				{
					swal.fire("Get Member Status", returned["message"], "error");
				}
			}
		});
	}
	else if (nxt == "view_details")
	{
		var form_data = {
			action: "u3a_get_member_details",
			op: op,
			suffix: sfx,
			membership_number: mnum
		};
//		console.debug(form_data);
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
//				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					jQuery("#u3a-member-details-" + op + sfx).html(returned["message"]);
					jQuery("#u3a-member-details-" + op + sfx + " input").prop("readonly", true);
					jQuery("#u3a-member-details-" + op + sfx + " select").prop("disabled", true);
					jQuery('#u3a-select-member-text-div-' + op + sfx).removeClass("u3a-visible");
					jQuery('#u3a-select-member-text-div-' + op + sfx).addClass("u3a-invisible");
				}
				else
				{
					swal.fire("Get Member Details", returned["message"], "error");
				}
			}
		});
	}
	else if (nxt == "delete")
	{
		var form_data = {
			action: "u3a_delete_member",
			membership_number: mnum
		};
//		console.debug(form_data);
		/*
		 * Swal.fire({
		 title: 'Are you sure?',
		 text: "You won't be able to revert this!",
		 icon: 'warning',
		 showCancelButton: true,
		 confirmButtonColor: '#3085d6',
		 cancelButtonColor: '#d33',
		 confirmButtonText: 'Yes, delete it!'
		 }).then((result) => {
		 if (result.value) {
		 Swal.fire(
		 'Deleted!',
		 'Your file has been deleted.',
		 'success'
		 )
		 }
		 })
		 */
		swal.fire({title: fname + " " + sname + " will be deleted!",
			text: "Are you sure you wish to proceed?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: '#d33',
			confirmButtonText: 'confirm delete'})
//			buttons: true,
//			dangerMode: true})
				  .then(
							 function (result)
							 {
								 if (result.value)
								 {
									 jQuery.ajax({
										 type: 'POST',
										 url: settings.ajaxurl,
										 data: form_data,
										 error: function (jqXHR, textStatus, errorThrown)
										 {
											 console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
										 },
										 success: function (data)
										 {
											 console.log("success", data);
											 var returned = JSON.parse(data);
											 if (returned["success"])
											 {
												 swal.fire("Delete Member", returned["message"], "success");
											 }
											 else
											 {
												 swal.fire("Delete Member", returned["message"], "error");
											 }
										 }
									 });
								 }
							 });
	}
	else if (nxt == "add_to_group")
	{
		var wl = jQuery('#u3a-add2grp-waiting-list').is(":checked") ? 1 : 0;
		var form_data = {
			action: "u3a_add_member_to_group",
			group: op,
			member: mnum,
			waiting: wl
		};
//		console.debug(form_data);
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
				console.debug("this1");
//				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					swal.fire("Add Member to Group", returned["message"], "success").then(function ()
					{
						var groups_id = jQuery('#u3a-group-page-group-id').val();
						if (groups_id)
						{
							u3a_reload_group_page();
						}
						else
						{
							u3a_reload_committee_manage_groups_page();
						}
					});
				}
				else
				{
					swal.fire("Add Member to Group", returned["message"], "error");
				}
			}
		});
	}
	else if (nxt == "goto_member")
	{
		console.debug("goto member", mnum);
		u3a_reload_member_page(mnum);
	}
}

function u3a_add_member_to_group(nxt, op, grp)
{
	var mbr = jQuery('#u3a-found-members').val();
	var form_data = {
		action: "u3a_add_member_to_group",
		group: grp,
		member: mbr
	};
//	console.debug(form_data);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
			console.log("success", data);
			var returned = JSON.parse(data);
			if (returned["success"])
			{
				swal.fire("Add Member to Group", returned["message"], "success").then(function ()
				{
					u3a_reload_group_page();
				});
			}
			else
			{
				swal.fire("Add Member to Group", returned["message"], "error");
			}
		}
	});
}

function u3a_after_select_group(op)
{
	var form_data = {
		action: "u3a-select-group-action",
		groups_id: jQuery('#u3a-select-group-' + op).val(),
		op: op
	};
	var gname = jQuery('#u3a-select-group-' + op + " option:selected").text();
//	console.debug("u3a_after_select_group", form_data);
	if (op == "delete")
	{
		swal.fire({title: gname + " will be deleted permanently!",
			text: "Are you sure you wish to proceed?",
			icon: "warning",
			buttons: true,
			dangerMode: true})
//			showCancelButton: true,
//			confirmButtonColor: "#DD6B55",
//			confirmButtonText: "Delete Group",
//			cancelButtonText: "Cancel",
//			closeOnConfirm: false,
//			closeOnCancel: false})
				  .then(
							 function (isConfirm)
							 {
								 if (isConfirm)
								 {
									 jQuery.ajax({
										 type: 'POST',
										 url: settings.ajaxurl,
										 data: form_data,
										 error: function (jqXHR, textStatus, errorThrown)
										 {
											 console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
										 },
										 success: function (data)
										 {
											 console.log("success", data);
											 var returned = JSON.parse(data);
											 if (returned["success"])
											 {
												 swal.fire("Group deleted", returned["message"], "success");
												 u3a_reload_committee_manage_groups_page();
											 }
											 else
											 {
												 swal.fire("Group retained", gname + " has not been deleted!", "error");
											 }
										 }});
								 }
							 });
	}
	else if (op == "add_member")
	{
		form_data["action"] = "u3a_get_add_member_to_group";
		console.debug("add_member");
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
//				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					var ret = returned["message"];
					jQuery("#u3a-add-member-container").html(ret);
//					console.debug(ret);
				}
			}});
	}
	else if (op == "remove_member")
	{
		form_data["action"] = "u3a_do_remove_member_from_group";
		console.debug("remove_member");
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
//				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					var ret = returned["message"];
					jQuery("#u3a-remove-member-container").html(ret);
//					console.debug(ret);
				}
			}});
	}
	else
	{
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (data)
			{
				console.log("success", data);
				var returned = JSON.parse(data);
				if (returned["success"])
				{
					var ret = JSON.parse(returned["message"]);
					jQuery("#u3a-group-coordinators-edit").html(ret.coord);
					jQuery("#u3a-group-id-edit").val(ret.id);
					jQuery("#u3a-group-name-edit").val(ret.name);
					jQuery("#u3a-group-when-edit").val(ret.meets_when);
					jQuery("#u3a-group-when-json-edit").val(ret.meets_when_json);
					jQuery("#u3a-group-venue-edit").val(ret.venue);
					jQuery("#u3a-group-max-edit").val(ret.max);
					jQuery("#u3a-group-notes-edit").val(ret.info);
					console.debug(ret);
//					jQuery("#u3a-group-div-" + op).html(returned["message"]);
				}
			}});
	}
}

function u3a_meeting_times_dialog_close(op)
{
//					"ord"	 => 0,
//					"day"	 => "monday",
//					"from" => "10:00",
//					"to"	 => "12:00"
	var idsuffix = "-" + op;
	var meet = {};
	meet["ntimes"] = parseInt(jQuery("#u3a-number-of-meetings-" + op).val());
	meet["every"] = jQuery("#u3a-every-" + op).val();
	if (meet["every"] === "week")
	{
		meet["onmonth"] = [];
		var onw = [];
		var n = 0;
		jQuery(".u3a-week-div-class" + idsuffix + " .u3a-weekday-select").each(function ()
		{
			if (n < meet["ntimes"])
			{
				var thisid = jQuery(this).attr("id");
				var dash = thisid.lastIndexOf('-');
				var idx = thisid.substr(dash + 1);
				var w = {};
				w["ord"] = 0;
				w["day"] = jQuery(this).val();
				w["from"] = jQuery("#u3a-group-from-time-week-" + op + '-' + idx).val();
				w["to"] = jQuery("#u3a-group-to-time-week-" + op + '-' + idx).val();
				console.debug(thisid, w);
				onw.push(w);
				n++;
			}
		});
		meet["onweek"] = onw;
	}
	else
	{
		meet["onweek"] = [];
		var onm = [];
		for (var n = 0; n < meet["ntimes"]; n++)
		{
//			var thisid = jQuery(this).attr("id");
//			var dash = thisid.lastIndexOf('-');
//			var idx = thisid.substr(dash + 1);
			var m = {};
			m["ord"] = jQuery("#u3a-ordinal-select-month-" + op + '-' + n).val();
			m["day"] = jQuery("#u3a-weekday-select-month-" + op + '-' + n).val();
			m["from"] = jQuery("#u3a-group-from-time-month-" + op + '-' + n).val();
			m["to"] = jQuery("#u3a-group-to-time-month-" + op + '-' + n).val();
			onm.push(m);
		}
		meet["onmonth"] = onm;
	}
	var str = meeting_time_to_string(meet);
//	console.debug(str);
//	console.debug(meet);
	jQuery("#u3a-group-when-" + op).val(str);
	jQuery("#u3a-group-when-json-" + op).val(JSON.stringify(meet));
}

function u3a_show(div)
{
	jQuery('#' + div).removeClass("u3a-invisible");
	jQuery('#' + div).addClass("u3a-visible");
}

function u3a_hide(div)
{
	jQuery('#' + div).removeClass("u3a-visible");
	jQuery('#' + div).addClass("u3a-invisible");
}

function u3a_show1(div)
{
	jQuery('#' + div).removeClass("u3a-invisible");
	jQuery('#' + div).addClass("u3a-inline-block");
}

function u3a_hide1(div)
{
	jQuery('#' + div).removeClass("u3a-inline-block");
	jQuery('#' + div).addClass("u3a-invisible");
}

function u3a_show_all(cssclass)
{
	jQuery('.' + cssclass).removeClass("u3a-invisible");
	jQuery('.' + cssclass).addClass("u3a-visible");
}

function u3a_hide_all(cssclass)
{
	jQuery('.' + cssclass).removeClass("u3a-visible");
	jQuery('.' + cssclass).addClass("u3a-invisible");
}

function u3a_show_hide_plus_minus(div, iconspan)
{
	if (jQuery('#' + div).hasClass("u3a-invisible"))
	{
		jQuery('#' + div).removeClass("u3a-invisible");
		jQuery('#' + div).addClass("u3a-visible");
		jQuery('#' + iconspan).removeClass("dashicons-plus");
		jQuery('#' + iconspan).addClass("dashicons-minus");
	}
	else
	{
		jQuery('#' + div).removeClass("u3a-visible");
		jQuery('#' + div).addClass("u3a-invisible");
		jQuery('#' + iconspan).removeClass("dashicons-minus");
		jQuery('#' + iconspan).addClass("dashicons-plus");
	}
}

function u3a_remove_div(divclass, n, min)
{
	var num = jQuery("." + divclass).length;
	if (num > min)
	{
		jQuery("#" + divclass.replace("class", n.toString())).remove();
	}
}

function u3a_meet_every_change(op)
{
//	console.debug("u3a-every-select");
//	var every = jQuery("#u3a-every-" + op).val();
//	console.debug("u3a-every-select", val);
	u3a_hide_all("u3a-week-month-div-class");
	u3a_meet_ntimes_change(op);
//	if (val === "week")
//	{
//		jQuery('#u3a-week-div-' + idsuffix).removeClass("u3a-invisible");
//		jQuery('#u3a-week-div-' + idsuffix).addClass("u3a-visible");
//		jQuery('#u3a-month-div-' + idsuffix).removeClass("u3a-visible");
//		jQuery('#u3a-month-div-' + idsuffix).addClass("u3a-invisible");
//	}
//	else
//	{
//		jQuery('#u3a-week-div-' + idsuffix).removeClass("u3a-visible");
//		jQuery('#u3a-week-div-' + idsuffix).addClass("u3a-invisible");
//		jQuery('#u3a-month-div-' + idsuffix).removeClass("u3a-invisible");
//		jQuery('#u3a-month-div-' + idsuffix).addClass("u3a-visible");
//	}
}

function u3a_meet_ntimes_change(op)
{
	var maxntimes = 5;
//	console.debug(op);
	var ntimes = jQuery("#u3a-number-of-meetings-" + op).val();
	jQuery("#u3a-time-text-" + op).text(ntimes == 1 ? "time every" : "times every")
//	console.debug(ntimes);
	var every = jQuery("#u3a-every-" + op).val();
	var divprefix = "u3a-" + every + "-div-" + op + "-";
	for (var n = 0; n < maxntimes; n++)
	{
		var div = divprefix + n;
//		console.debug(div);
		if (n < ntimes)
		{
			u3a_show(div);
		}
		else
		{
			u3a_hide(div);
		}
	}
}

function u3a_meet_week_build(idsuffix)
{
	var every = jQuery("#u3a-every-" + idsuffix).val();
	var idx = 0;
	jQuery(".u3a-month-div-class .u3a-weekday-select").each(function ()
	{

	});
	var sel = jQuery(".u3a-weekday-select")[0];
	var selhtml = sel.outerHTML; //jQuery(sel).html();
//	console.debug(selhtml);
	var html = '<span id="u3a-week-text-' + idsuffix + '" class="u3a-text u3a-inline-block">on</span>' +
			  '';
}

function number_to_string(ord)
{
	if (ord == 0)
	{
		ret = "zero";
	}
	else if (ord == 1)
	{
		ret = "one";
	}
	else if (ord == 2)
	{
		ret = "two";
	}
	else if (ord == 3)
	{
		ret = "three";
	}
	else if (ord == 4)
	{
		ret = "four";
	}
	else if (ord == 5)
	{
		ret = "five";
	}
	else
	{
		ret = "many";
	}
	return ret;
}

function ordinal_to_string(ord)
{
	if (ord == 1)
	{
		ret = "1st";
	}
	else if (ord == 2)
	{
		ret = "2nd";
	}
	else if (ord == 3)
	{
		ret = "3rd";
	}
	else if (ord == 4)
	{
		ret = "4th";
	}
	else
	{
		ret = "last";
	}
	return ret;
}

function number_to_adverb(ord)
{
	if (ord == 1)
	{
		ret = "once";
	}
	else if (ord == 2)
	{
		ret = "twice";
	}
	else if (ord == 3)
	{
		ret = "three times";
	}
	else if (ord == 4)
	{
		ret = "four times";
	}
	else
	{
		ret = "many times";
	}
	return ret;
}

function day_to_string(day)
{
	return (day["ord"] >= 1 ? "alternate " : "") + day["day"] + " from " + day["from"] + " to " + day["to"];
}

function ordinal_day_to_string(ordday)
{
	return ordinal_to_string(ordday["ord"]) + " " + ordday["day"] + " from " + ordday["from"] + " to " + ordday["to"];
}

function days_to_string(days, ntimes)
{
	var day = [];
	for (var n = 0; n < ntimes; n++)
	{
		day.push(day_to_string(days[n]));
	}
	return day.join(" and ");
}

function ordinal_days_to_string(orddays, ntimes)
{
	var day = [];
	for (var n = 0; n < ntimes; n++)
	{
		day.push(ordinal_day_to_string(orddays[n]));
	}
	return day.join(" and ");
}

function meeting_time_to_string(meet)
{
//		$val = [
//			"ntimes"	 => 1,
//			"every"	 => "month",
//			"onweek"	 => [
//				[
//					"ord"	 => 0,
//					"day"	 => "monday",
//					"from" => "10:00",
//					"to"	 => "12:00"
//				]
//			],
//			"onmonth" => [
//				[
//					"ord"	 => 1,
//					"day"	 => "monday",
//					"from" => "10:00",
//					"to"	 => "12:00"
//				]
//			]
//		];
	var nt = meet["ntimes"];
	var every = meet["every"];
	var ret = number_to_adverb(nt) + " every " + every + " on ";
	if (every === "week")
	{
		ret += days_to_string(meet["onweek"], nt);
	}
	else if (every === "month")
	{
		ret += ordinal_days_to_string(meet["onmonth"], nt);
	}
	return ret;
}

function capitalizeFirstLetter(string)
{
	return string.charAt(0).toUpperCase() + string.slice(1);
}

function basename(path)
{
	var fname = path.split(/[\\/]/).pop();
	var lastdot = fname.lastIndexOf('.');
	return lastdot > 0 ? fname.substring(0, lastdot) : fname;
}

var member_fields = [
	"membership_number",
	"title",
	"forename",
	"surname",
	"known_as",
	"gender",
	"email",
	"telephone",
	"mobile",
	"emergency_contact",
	"house",
	"address1",
	"address2",
	"address3",
	"town",
	"postcode",
	"county",
	"payment_type",
	"gift_aid",
	"action",
	"op",
	"affiliation",
	"TAM",
	"newsletter",
	"mbr"
];
function u3a_clear_member_form(op)
{
	console.debug("u3a_clear_member_form", op);
	jQuery('#u3a-member-details-' + op).html("");
	jQuery('#u3a-select-member-text-div-' + op).removeClass("u3a-invisible");
	jQuery('#u3a-select-member-text-div-' + op).addClass("u3a-visible");
}

function u3a_member_form(op)
{
	var msg = u3a_validate_member_details_form(op);
	console.debug("msg", msg);
	if (msg)
	{
		swal.fire("Incomplete Form", '"' + msg + '" must be supplied.', "error");
	}
	else
	{
		var form_data = {};
		for (var n = 0; n < member_fields.length; n++)
		{
			var value = jQuery("#u3a-member-" + member_fields[n] + "-" + op).val();
			if (typeof value !== 'undefined')
			{
				form_data[member_fields[n]] = value;
				if (jQuery("input[type='checkbox']#u3a-member-" + member_fields[n] + "-" + op).length > 0)
				{
					form_data[member_fields[n]] = jQuery("#u3a-member-" + member_fields[n] + "-" + op).prop("checked") ? "yes" : "no";
				}
			}
		}
		console.debug(form_data);
		u3a_ajax(form_data, capitalizeFirstLetter(op) + " Member");
	}
}

function gotoregister(arg)
{
	var url = get_permalink(get_page_by_path('register')) + "?mnum=" + arg[0] + "&email=" + arg[1];
	window.open(url);
}

function u3a_ajax(form_data, title, and_then, and_then_args)
{
//	console.debug(form_data);
//	console.debug(title);
//	console.debug(and_then);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.debug(jqXHR, textStatus, errorThrown);
		},
		success: function (data)
		{
//			console.log("success", data);
			var returned = JSON.parse(data);
			if (returned["success"] === 1)
			{
				if (returned.hasOwnProperty("message"))
				{
					swal.fire(title, returned["message"], "success").then(
							  function ()
							  {
								  if (typeof and_then !== "undefined")
								  {
									  if (typeof and_then_args === "undefined")
									  {
										  if (returned.hasOwnProperty("arg"))
										  {
											  and_then(returned["arg"]);
										  }
										  else
										  {
											  and_then();
										  }
									  }
									  else
									  {
										  and_then(and_then_args);
									  }
								  }
							  });
				}
				else if (typeof and_then !== "undefined")
				{
					if (typeof and_then_args === "undefined")
					{
						if (returned.hasOwnProperty("arg"))
						{
							and_then(returned["arg"]);
						}
						else
						{
							and_then();
						}
					}
					else
					{
						and_then(and_then_args);
					}
				}
			}
			else if (returned["success"] === 0)
			{
				if (returned.hasOwnProperty("message"))
				{
					swal.fire(title, returned["message"], "error");
				}
			}
			else
			{
				if (typeof and_then !== "undefined")
				{
					if (typeof and_then_args === "undefined")
					{
						if (returned.hasOwnProperty("arg"))
						{
							and_then(returned["arg"]);
						}
						else
						{
							and_then();
						}
					}
					else
					{
						and_then(and_then_args);
					}
				}
			}
		}
	});
}

function u3a_remove_coordinator(n)
{
	console.debug("u3a_remove_coordinator", n);
	u3a_hide("u3a-group-coordinator-outer-div-edit-" + n);
	var ncoords = jQuery(".u3a-group-coordinator-outer-div-class.u3a-visible").length;
	jQuery(".u3a-group-del-coord-button-class").prop("disabled", ncoords === 1);
}

function u3a_add_coordinator_block()
{
	console.debug("u3a_add_coordinator_block");
}

function u3a_create_new_category(memgrp, typ)
{
	var id = "u3a-category-name-" + memgrp + "-" + typ;
	var nm = jQuery('#' + id).val().trim();
	if (nm)
	{
		var form_data = {
			"action": "u3a_create_document_category",
			"memgrp": memgrp,
			"type": typ,
			"name": nm
		};
//		u3a_ajax(form_data, "create category", u3a_clear_value, id);
		if (memgrp > 0)
		{
			u3a_ajax(form_data, "create category", u3a_reload_group_page);
		}
		else if (memgrp < 0)
		{
			u3a_ajax(form_data, "create category", u3a_reload_page);
		}
		else
		{
			u3a_ajax(form_data, "create category", u3a_reload_committee_manage_page);
		}
	}
}

function u3a_rename_category(grp, typ, selid)
{
	var id = "u3a-category-rename-" + grp + "-" + typ;
	var nm = jQuery('#' + id).val().trim();
	if (nm)
	{
		var catid = jQuery('#' + selid).val();
		console.debug(grp, typ, selid, nm, catid);
		var form_data = {
			"action": "u3a_rename_category",
			"group": grp,
			"type": typ,
			"name": nm,
			"category": catid
		};
		u3a_ajax(form_data, "rename category", u3a_reload_group_page);
	}
}

function u3a_delete_category(grp, typ, selid)
{
	var catid = jQuery('#' + selid).val();
	console.debug(grp, typ, selid, catid);
	var form_data = {
		"action": "u3a_delete_category",
		"group": grp,
		"type": typ,
		"category": catid
	};
	u3a_ajax(form_data, "delete category", u3a_reload_group_page);
}

function u3a_clear_value(id)
{
	jQuery('#' + id).val("");
}

function u3a_document_category_select(grp, typ, selid)
{
	var catid = jQuery("#" + selid).val();
	u3a_document_category_select1(grp, typ, catid);
}

function u3a_document_category_select1(grp, typ, catid)
{
	jQuery(".u3a-document-div-class-" + typ).removeClass("u3a-visible");
	jQuery(".u3a-document-div-class-" + typ).addClass("u3a-invisible");
	jQuery("#u3a-document-div-" + grp + "-" + typ + "-" + catid).removeClass("u3a-invisible");
	jQuery("#u3a-document-div-" + grp + "-" + typ + "-" + catid).addClass("u3a-visible");
}

function u3a_document_category_change(grp, typ, selid)
{
	var catid = jQuery("#" + selid).val();
	last_document_category_selection[selid] = catid;
	console.debug("u3a_document_category_change", grp, typ, catid);
//	jQuery("#" + inpid).val(val);
	jQuery(".u3a-manage-document-div-class-" + typ).removeClass("u3a-visible");
	jQuery(".u3a-manage-document-div-class-" + typ).addClass("u3a-invisible");
	jQuery("#u3a-manage-document-div-" + grp + "-" + typ + "-" + catid).removeClass("u3a-invisible");
	jQuery("#u3a-manage-document-div-" + grp + "-" + typ + "-" + catid).addClass("u3a-visible");
	jQuery('select').chosen("destroy");
	jQuery('select').chosen();
}

function u3a_move_document(selid, type, catselid, catid, groups_id, is_group)
{
	var docid = jQuery("#" + selid).val();
	var dest = -1;
	if (jQuery("#" + catselid).length > 0)
	{
		dest = jQuery("#" + catselid).val();
	}
	var form_data = {
		"action": "u3a_move_document",
		"type": type,
		"document": docid,
		"dest": dest,
		"catid": catid
	};
	var hdng = dest < 0 ? "delete document" : "move document";
	if (is_group)
	{
		if (groups_id > 0)
		{
			u3a_ajax(form_data, hdng, u3a_reload_group_page);
		}
		else
		{
			u3a_ajax(form_data, hdng, u3a_reload_committee_manage_page);
		}
	}
	else
	{
		u3a_ajax(form_data, hdng, u3a_reload_member_page);
	}
}

function u3a_copy_document(selid, type, catselid, catid, groups_id, is_group)
{
//	console.debug("u3a_copy_document", selid, type, catselid, catid, groups_id);
	var docid = jQuery("#" + selid).val();
	var dest = -1;
	if (jQuery("#" + catselid).length > 0)
	{
		dest = jQuery("#" + catselid).val();
	}
	var form_data = {
		"action": "u3a_copy_document",
		"type": type,
		"document": docid,
		"dest": dest,
		"catid": catid
	};
	var hdng = "copy document";
	if (is_group)
	{
		if (groups_id > 0)
		{
			u3a_ajax(form_data, hdng, u3a_reload_group_page);
		}
		else
		{
			u3a_ajax(form_data, hdng, u3a_reload_committee_manage_page);
		}
	}
	else
	{
		u3a_ajax(form_data, hdng, u3a_reload_member_page);
	}
}

function u3a_create_permission(groups_id, is_committee)
{
	var who = jQuery("#u3a-permit-to-list-" + groups_id).val();
	var what = jQuery("#u3a-allow-to-list-" + groups_id).val();
	console.debug("allow", who, "to", what);
	var form_data = {
		"action": "u3a_create_permission",
		"group": groups_id,
		"committee": is_committee,
		"who": who,
		"what": what
	};
	if (groups_id > 0)
	{
		u3a_ajax(form_data, "create permission", u3a_reload_group_page);
	}
	else
	{
		u3a_ajax(form_data, "create permission", u3a_reload_committee_manage_permissions_page);
	}
}

function u3a_remove_permission(groups_id)
{
	var which = jQuery("#u3a-remove-permit-div-" + groups_id + " input:checked").map(function ()
	{
		return jQuery(this).val();
	}).get();
	console.debug(which);
	var form_data = {
		"action": "u3a_remove_permission",
		"permit": which.join(',')
	};
	if (groups_id > 0)
	{
		u3a_ajax(form_data, "remove permission", u3a_reload_group_page);
	}
	else
	{
		u3a_ajax(form_data, "remove permission", u3a_reload_committee_manage_permissions_page);
	}
}

function u3a_update_type_changed(utype)
{
	if (utype === "all")
	{
		var chk = jQuery("#update-type-checkbox-all").prop("checked");
		jQuery(".update-type-checkbox-class").prop("checked", chk);
	}
	else
	{
		var allchkd = jQuery(".update-type-checkbox-class").length === jQuery(".update-type-checkbox-class:checked").length;
		jQuery("#update-type-checkbox-all").prop("checked", allchkd);
	}
}

function u3a_update_site()
{
	console.debug("update site");
	var form = jQuery('#u3a-site-update-form')[0];
	var form_data = new FormData(form);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
			console.log("success", data);
			var returned = JSON.parse(data);
			if (returned["success"])
			{
				swal.fire("site update", returned["message"], "success");
			}
			else
			{
				swal.fire("site update", returned["message"], "error");
			}
		}
	});
}

function u3a_update_videos()
{
	var form_data = {
		"action": "u3a_update_videos"
	};
	u3a_ajax(form_data, "update videos");
}

function u3a_update_help_videos()
{
	var form_data = {
		"action": "u3a_update_help_videos"
	};
	u3a_ajax(form_data, "update help videos");
}

function edit_news(newsid)
{
	var txtareaid = "news-item-" + newsid;
	var titleid = "news-title-" + newsid;
	var btnid = "news-edit-" + newsid;
	if (jQuery('#' + txtareaid).hasClass("u3a-news-item-div"))
	{
		jQuery('#' + txtareaid).removeClass("u3a-news-item-div");
		jQuery('#' + titleid).removeClass("u3a-news-title");
		jQuery('#' + txtareaid).prop("readonly", false);
		jQuery('#' + titleid).prop("readonly", false);
		jQuery('#' + btnid).text("Done");
	}
	else
	{
		jQuery('#' + txtareaid).addClass("u3a-news-item-div");
		jQuery('#' + titleid).addClass("u3a-news-title");
		jQuery('#' + txtareaid).prop("readonly", true);
		jQuery('#' + titleid).prop("readonly", true);
		jQuery('#' + btnid).text("Edit");
		var form_data = {
			"action": "u3a_edit_news",
			"newsid": newsid,
			"title": jQuery('#' + titleid).val(),
			"text": jQuery('#' + txtareaid).val()
		};
		u3a_ajax(form_data, "edit news");
	}
}

function add_news()
{
	if (jQuery('#add-news-contents-div').hasClass("u3a-invisible"))
	{
		jQuery('#add-news-title').val("");
		jQuery('#add-news-item').val("");
		jQuery('#add-news-contents-div').removeClass("u3a-invisible");
		jQuery('#news-add-button').text("Add");
	}
	else
	{
		jQuery('#add-news-contents-div').addClass("u3a-invisible");
		jQuery('#news-add-button').text("+News");
		var form_data = {
			"action": "u3a_add_news",
			"expires": jQuery('#add-news-expires').val(),
			"title": jQuery('#add-news-title').val(),
			"text": jQuery('#add-news-item').val(),
			"members_id": jQuery('#u3a-add-news-members-id').val()
		};
		u3a_ajax(form_data, "add news");
	}
}

function u3a_testmail()
{
	var form_data = {
		"action": "u3a_sendmail",
		"to": "computermike27@gmail.com",
		"subject": "test email",
		"contents": "a test email"
	};
	u3a_ajax(form_data, "test mail");
}

function u3a_test_group_mail()
{
	var form_data = {
		"action": "u3a_send_group_mail",
		"group": 140,
		"subject": "test group email",
		"contents": "a test group email",
		"sender": 233
	};
	u3a_ajax(form_data, "test mail");
}

function u3a_clear_mail(mailtype, id)
{
	jQuery('#u3a-send-mail-form-' + mailtype).trigger("reset");
	jQuery('#u3a-mail-attach-' + mailtype + '-' + id).html("");
}

function u3a_send_mail(mailtype)
{
	if ((mailtype === 'group') || (mailtype === 'committee') || (mailtype === 'coordinators') || (mailtype === 'individual') || (mailtype === 'contact'))
	{
		console.debug("u3a_send_mail", mailtype);
		var form = jQuery('#u3a-send-mail-form-' + mailtype)[0];
		var form_data = new FormData(form);
		console.log("success", form_data);
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (html)
			{
				var result = JSON.parse(html);
				swal.fire("Send Mail", result.message, result.success ? "success" : "error");
//				u3a_reload_group_page();
			}
		});
	}
}

function attach_file_changed(mailtype, id, n)
{
	var fname = basename(jQuery('#u3a-mail-attachment-' + mailtype + '-' + id + '-' + n).val());
//	var txt = jQuery('#u3a-mail-attach-' + mailtype + '-' + id).val();
	var newtxt = '<div class="u3a-attach-item-div">' + fname + '</div>';
//	if (txt)
//	{
//		newtxt = txt + "\n" + fname;
//	}
//	else
//	{
//		newtxt = fname;
//	}

// <input id="u3a-mail-file-group-140" type="file" name="u3a-mail-file" onchange="attach_file_changed('group', '140')">
//	console.debug("fname", fname, "newtxt", newtxt);
//	var fhtml = jQuery('#u3a-mail-file-' + mailtype + '-' + id).parent("div").html();
//	console.debug(fhtml);
	jQuery('#u3a-mail-attach-' + mailtype + '-' + id).append(newtxt);
	jQuery('#u3a-attachment-div-' + mailtype + '-' + id + '-' + n).removeClass("u3a-inline-block").addClass("u3a-invisible");
	n++;
	jQuery('#u3a-attachment-div-' + mailtype + '-' + id + '-' + n).removeClass("u3a-invisible").addClass("u3a-inline-block");
}

function u3a_document_selected(grp, type)
{

}

function attach_image(mailtype, id, n)
{

}

function attach_document(mailtype, id, n, typ)
{
	var divid = "#u3a-mail-document-" + mailtype + "-" + id + "-" + n;
	console.debug("attach document", mailtype, id, n, divid);
	jQuery(divid).removeClass("u3a-invisible").addClass("u3a-visible");
}

function attach_document1(mailtype, id, n, title, attachid)
{
	var newtxt = '<div class="u3a-attach-item-div">' + title + '<input type="hidden" name="document-' + n + '" value="' + attachid + '"/></div>';
	console.debug("attach document", title, attachid);
	jQuery('#u3a-mail-attach-' + mailtype + '-' + id).append(newtxt);
	jQuery('#u3a-mail-document-div-' + mailtype + '-' + id + '-' + n).removeClass("u3a-inline-block").addClass("u3a-invisible");
	n++;
	jQuery('#u3a-mail-document-div-' + mailtype + '-' + id + '-' + n).removeClass("u3a-invisible").addClass("u3a-inline-block");
}

function u3a_reload_group_page()
{
	var groups_id = jQuery('#u3a-group-page-group-id').val();
	if (groups_id)
	{
		var tab = jQuery("span.su-tabs-current").text();
		if (tab === 'Manage')
		{
			var spoiler = jQuery("div.su-spoiler:not(.su-spoiler-closed) div.su-spoiler-title").text();
			var cat = jQuery("div.su-spoiler:not(.su-spoiler-closed) select.u3a-document-category-select").val();
			if (typeof cat === "undefined")
			{
				console.debug("cat undefined");
				cat = 0;
			}
			console.debug("groups_id", groups_id, "tab", tab, "spoiler", spoiler, "category", cat);
			jQuery("body").fadeOut(1000, function ()
			{
				jQuery('<form action="' + window.location.href + '" method="POST"><input type="hidden" name="group" value="' + groups_id +
						  '"><input type="hidden" name="tab" value="' + tab + '"><input type="hidden" name="spoiler" value="' + spoiler +
						  '"><input type="hidden" name="category" value="' + cat + '"></form>').appendTo('body').submit();
			});
		}
		else if ((tab === "Forum") || (tab === "Members"))
		{
			jQuery("body").fadeOut(1000, function ()
			{
				jQuery('<form action="' + window.location.href + '" method="POST"><input type="hidden" name="group" value="' + groups_id +
						  '"><input type="hidden" name="tab" value="' + tab + '"></form>').appendTo('body').submit();
			});
		}
		else
		{
			console.debug("groups_id", groups_id, "tab", tab);
		}
	}
	else
	{
		console.debug("no group id");
	}
}

function u3a_reload_member_page(members_id)
{
	if ((typeof members_id === 'undefined') || !members_id)
	{
		members_id = jQuery('#u3a-member-personal-page-number').val();
	}
	if (members_id)
	{
		var tab = jQuery("span.su-tabs-current").text();
		if (tab === 'Manage')
		{
			var spoiler = jQuery("div.su-spoiler:not(.su-spoiler-closed) div.su-spoiler-title").text();
			var cat = jQuery("div.su-spoiler:not(.su-spoiler-closed) select.u3a-document-category-select").val();
			if (typeof cat === "undefined")
			{
				console.debug("cat undefined");
				cat = 0;
			}
			console.debug("members_id", members_id, "tab", tab, "spoiler", spoiler, "category", cat);
			jQuery("body").fadeOut(1000, function ()
			{
				jQuery('<form action="' + window.location.href.split('?')[0] + '" method="POST"><input type="hidden" name="member" value="' + members_id +
						  '"><input type="hidden" name="tab" value="' + tab + '"><input type="hidden" name="spoiler" value="' + spoiler +
						  '"><input type="hidden" name="category" value="' + cat + '"></form>').appendTo('body').submit();
			});
		}
		else if (tab === 'Friends')
		{
			jQuery("body").fadeOut(1000, function ()
			{
				jQuery('<form action="' + window.location.href.split('?')[0] + '" method="POST"><input type="hidden" name="member" value="' + members_id +
						  '"><input type="hidden" name="tab" value="' + tab + '"></form>').appendTo('body').submit();
			});
		}
		else
		{
			console.debug("members_id", members_id, "tab", tab);
		}
	}
	else
	{
		console.debug("no member id");
	}
}

function u3a_reload_page()
{
	jQuery("body").fadeOut(1000, function ()
	{
		jQuery('<form action="' + window.location.href + '" method="POST"></form>').appendTo('body').submit();
	});
}

function u3a_reload_committee_manage_page()
{
	var spoiler = jQuery("div.su-spoiler:not(.su-spoiler-closed) div.su-spoiler-title").text();
	console.debug("spoiler", spoiler);
	var form_data = {
		"action": "u3a_reload_committee_manage",
		"spoiler": spoiler
	};
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (html)
		{
//					console.debug(html);
			jQuery("article.type-page").html(html);
			Object.keys(last_document_category_selection).forEach(function (key, index)
			{
				jQuery('#' + key).val(this[key]);
				console.log("last_document_category_selection", key, this[key]);
			}, last_document_category_selection);
		}
	});
}

function u3a_reload_committee_manage_permissions_page()
{
	var form_data = {
		"action": "u3a_reload_committee_manage_permissions"
	};
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (html)
		{
//					console.debug(html);
			jQuery("article.type-page").html(html);
		}
	});
}

function u3a_reload_committee_manage_groups_page()
{
	var form_data = {
		"action": "u3a_reload_committee_manage_groups"
	};
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (html)
		{
//					console.debug(html);
			jQuery("article.type-page").html(html);
		}
	});
}

function upload_file_changed(file_input_id)
{
	var fname = basename(jQuery('#' + file_input_id).val());
	var fnamebits = fname.replace(/-/g, ' ').replace(/_/g, ' ').replace(/\(/g, ' ').replace(/\)/g, ' ').replace(/  +/g, ' ').split(' ');
	console.debug(fname, fnamebits);
	jQuery('#u3a-newletter-number').val(fnamebits[3]);
	jQuery('#u3a-newletter-year').val(fnamebits[5]);
	jQuery('#u3a-newletter-month').val(fnamebits[4]);
}

function u3a_member_checkbox_changed(cbid, mtype)
{
	var chk = jQuery('#' + cbid).prop("checked");
	if (cbid === "u3a-member-checkbox-all")
	{
		jQuery(".u3a-member-checkbox-class").prop("checked", chk);
	}
	else
	{
		if (!chk)
		{
			jQuery("#u3a-member-checkbox-all").prop("checked", false);
		}
	}
	jQuery('#u3a-send-' + mtype + '-mail-button').prop("disabled", jQuery(".u3a-member-checkbox-class:checked").length === 0);
//	console.debug(jQuery('#u3a-members-of-group').length);
	if (jQuery('#u3a-mail-members-of-' + mtype).length > 0)
	{
		console.debug(jQuery('.u3a-member-checkbox-class:checked').length);
		if (jQuery(".u3a-member-checkbox-class:checked").length === 0)
		{
			jQuery('#u3a-members-of-' + mtype).val("");
		}
		else
		{
			var ids = [];
			jQuery(".u3a-member-checkbox-class:checked").each(function ()
			{
				var v = jQuery(this).val();
				if (v > 0)
				{
					ids.push(v);
				}
			});
			jQuery(".u3a-waiting-checkbox-class:checked").each(function ()
			{
				var v = jQuery(this).val();
				if (v > 0)
				{
					ids.push(v);
				}
			});
//			console.debug(ids);
			jQuery('#u3a-mail-members-of-' + mtype).val(ids.join(','));
		}
	}
	u3a_check_sublist_buttons(cbid);
}

function u3a_category_name_clicked(grp, typ, m)
{
	var gtm = "" + grp + "-" + typ + "-" + m;
	console.debug(gtm);
	jQuery(".u3a-category-documents-class").removeClass("u3a-visible");
	jQuery(".u3a-category-documents-class").addClass("u3a-invisible");
	jQuery("#u3a-category-documents-" + gtm).removeClass("u3a-invisible");
	jQuery("#u3a-category-documents-" + gtm).addClass("u3a-visible");
	jQuery(".u3a-category-name-class").removeClass("u3a-category-selected");
	jQuery("#u3a-category-name-" + gtm).addClass("u3a-category-selected");
}

function u3a_upload_document_from_form(groups_id, type, is_group)
{
	var formid = 'upload-document-form-' + groups_id + "-" + type;
	var fileid = formid.replace("form", "file");
	var title = jQuery('#' + formid + " .u3a-input-title-class").val();
	if (!title)
	{
		var fname = basename(jQuery('#' + fileid).val());
		title = fname.replace(/-/g, ' ').replace(/_/g, ' ').replace(/  +/g, ' ');
		jQuery('#' + formid + " .u3a-input-title-class").val(title);
	}
	var form = jQuery('#' + formid)[0];
	var form_data = new FormData(form);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (html)
		{
			console.log("success", form_data);
			var result = JSON.parse(html);
			swal.fire("Upload Document", result.message, result.success ? "success" : "error")
					  .then(
								 function ()
								 {
									 if (is_group)
									 {
										 if (groups_id > 0)
										 {
											 u3a_reload_group_page();
										 }
										 else
										 {
											 u3a_reload_committee_manage_page();
										 }
									 }
									 else
									 {
										 u3a_reload_member_page();
									 }
								 });
		}
	});
}

function u3a_assume_identity()
{
	var val = jQuery('#u3a-site-assume-identity-input').val();
	if (val)
	{
		u3a_set_identity(val);
	}
}

function u3a_set_identity(val)
{
	var form_data = {
		"action": "u3a_assume_identity",
		"mbr": val
	};
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
			console.log("goto", data);
			window.location.href = data;
		}
	});
}

function u3a_clear_identity()
{
	u3a_set_identity(0);
}

function get_radio_value(name)
{
	var val = "";
	var selected = jQuery("input[type='radio'][name='" + name + "']:checked");
	if (selected.length > 0)
	{
		val = selected.val();
	}
	return val;
}

function u3a_initial_letter_clicked(letter)
{
	console.debug(letter);
	var form_data = {
		action: "u3a_list_members"
	};
	if (letter !== 'all')
	{
		form_data.initial = letter;
	}
	var tam = parseInt(get_radio_value("TAM"));
	if (tam >= 0)
	{
		form_data["TAM"] = tam;
	}
	var nl = parseInt(get_radio_value("newsletter"));
	if (nl >= 0)
	{
		form_data["newsletter"] = nl;
	}
	var ga = parseInt(get_radio_value("gift_aid"));
	if (ga >= 0)
	{
		form_data["gift_aid"] = ga;
	}
	var em = parseInt(get_radio_value("email"));
	if (em >= 0)
	{
		if (em === 1)
		{
			var val = jQuery('#u3a-filter-email-yes-value').val();
			if (val && val.trim())
			{
				form_data["email"] = val;
			}
			else
			{
				form_data["email"] = 1;
			}
		}
		else
		{
			form_data["email"] = em;
		}
	}
	var pt = "";
	jQuery(".u3a-checkbox-array-item-payment_type:checked").each(function ()
	{
		pt += "|" + jQuery(this).val();
	});
	if (pt.length > 0)
	{
		form_data["payment_type"] = pt.substring(1);
	}

	console.debug(form_data);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
//			console.debug(data);
			jQuery("#u3a-all-members-details").html(data);
			u3a_show('u3a-members-display-div');
		}
	});
}

function u3a_get_member_details(mnum)
{
	var form_data = {
		action: "u3a_get_member_details",
		op: "view",
		button: "no",
		groups: "yes",
		membership_number: mnum
	};
	console.debug(form_data);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
//			console.log("success", data);
			var returned = JSON.parse(data);
			if (returned["success"])
			{
				jQuery("#u3a-member-details-" + mnum).html(returned["message"]);
				jQuery("#u3a-member-details-" + mnum + " input").prop("readonly", true);
				jQuery("#u3a-member-details-" + mnum + " select").prop("disabled", true);
				jQuery(".u3a-member-list-details-class").removeClass("u3a-visible");
				jQuery(".u3a-member-list-details-class").addClass("u3a-invisible");
				jQuery("#u3a-member-details-" + mnum).removeClass("u3a-invisible");
				jQuery("#u3a-member-details-" + mnum).addClass("u3a-visible");
				jQuery("#u3a-member-details-" + mnum).scrollView();
			}
			else
			{
				swal.fire("Get Member Details", returned["message"], "error");
			}
		}
	});
}

function u3a_change_documents()
{
	var form_data = {
		"action": "u3a_change_documents"
	};
	u3a_ajax(form_data, "change documents");
}

function u3a_mail_clicked(recipient)
{
	jQuery('#u3a-mail-members-of-individual').val(recipient);
	jQuery('#u3a-send-individual-mail-button').prop("disabled", false);
}

function u3a_open(url)
{
	console.debug("open", url);
	window.open(url, "_BLANK");
}

function u3a_open_self(url)
{
	console.debug("open", url);
	window.open(url, "_SELF");
}

function u3a_download_group_table()
{
	var form_data = {
		"action": "u3a_download_group_table"
	};
	u3a_ajax(form_data, "group table", u3a_open);
//	jQuery.ajax({
//		type: 'POST',
//		url: settings.ajaxurl,
//		data: form_data,
//		error: function (jqXHR, textStatus, errorThrown)
//		{
//			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
//		},
//		success: function (data)
//		{
//			console.debug("here");
//			console.debug(data);
//		}
//	});
}

function preferred_role_changed(mbr, cmt)
{
	console.debug("preferred_role", mbr, cmt)
	var form_data = {
		"action": "u3a_set_preferred_role",
		"member_id": mbr,
		"committee_id": cmt
	};
	u3a_ajax(form_data, "set preferred role");
}

function u3a_use_cc_changed(id)
{
	if (jQuery('#' + id).is(":checked"))
	{
		swal.fire("Potential Data Breach", "recipients of this email will be able to see each other's addresses", "warning");
	}
}

function on_group_button_click(thisid)
{
//	e.preventDefault();
//	var thisid = jQuery(this).attr("id");
	var idsuffix = thisid.substr("u3a-group-btn".length);
	console.debug("u3a-group-btn", thisid, idsuffix);
	var ngname = jQuery("#u3a-group-name" + idsuffix).val();
	var source = jQuery("#u3a-edit-group-source" + idsuffix).val();
	var coorda = [];
	jQuery('.u3a-group-coordinator-outer-div-class.u3a-visible .u3a-group-coordinator-id-class').each(function ()
	{
		coorda.push(jQuery(this).val());
	});
	ngcoord = coorda.join(",");
//	var coordid = "#u3a-group-coordinator-mnum" + idsuffix;
//	var ngcoord = "0";
//	if (jQuery(coordid).length > 0)
//	{
//		ngcoord = jQuery(coordid).val();
//	}
//	else
//	{
//		var coorda = [];
//		var coordid1 = coordid + "-0";
//		var max = 20;
//		var n = 0;
//		while (jQuery(coordid1).length > 0)
//		{
//			coorda[n] = jQuery(coordid1).val();
//			n++;
//			coordid1 = coordid + "-" + n;
//		}
//		ngcoord = coorda.join(",");
//	}
	var ngvenue = jQuery("#u3a-group-venue" + idsuffix).val();
	var ngwhn = jQuery("#u3a-group-when-json" + idsuffix).val();
	var ngmax = jQuery("#u3a-group-max" + idsuffix).val();
//		var ngfrom = jQuery("#u3a-group-from-time" + idsuffix).val();
//		var ngto = jQuery("#u3a-group-to-time" + idsuffix).val();
	var ngnotes = jQuery("#u3a-group-notes" + idsuffix).val();
	var actn = jQuery("#u3a-group-action" + idsuffix).val();
	var grpid = jQuery("#u3a-group-id" + idsuffix).val();
//		var thisid = jQuery(this).attr("id");
//		var formid = thisid.replace("btn", "form");
//		var form = jQuery('#' + formid)[0];
//		var form_data = new FormData(form);
	var form_data = {
		action: actn,
		name: ngname,
		coord: ngcoord,
		venue: ngvenue,
		meets_when: ngwhn,
		max_members: ngmax,
		notes: ngnotes,
		group: grpid
	};
	console.debug("u3a-group-btn", form_data);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
//			cache: false,
//			contentType: false,
//			processData: false,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (html)
		{
			console.log("result", html);
			var result = JSON.parse(html);
			if (source === "group")
			{
				swal.fire("Edit Group", result.message, result.success ? "success" : "error").then(
						  function ()
						  {
							  u3a_reload_group_page();
						  });
			}
			else
			{
				if (source === "committee_new")
				{
					swal.fire("New Group", result.message, result.success ? "success" : "error");
				}
				else
				{
					swal.fire("Edit Group", result.message, result.success ? "success" : "error");
				}
				u3a_reload_committee_manage_groups_page();
			}
		}
	});
}

function u3a_find_members_search_clicked(thisid)
{
	var idsuffix = thisid.substr("u3a-find-members-search-button".length);
//		console.debug("u3a-find-members-search-button", thisid);
//		var form = jQuery('#u3a-find-members-search-form')[0];
//		console.debug("form", form);
	var byname = jQuery('#u3a-find-members-search-byname' + idsuffix).val();
	var sname = "";
	var fname = "";
	var mnum = "";
	if (byname === "yes")
	{
		sname = jQuery('#find-member-surname' + idsuffix).val();
		fname = jQuery('#find-member-forename' + idsuffix).val();
	}
	else
	{
		mnum = jQuery('#find-member-number' + idsuffix).val();
	}
	var action = jQuery('#u3a-find-members-search-action' + idsuffix).val();
	var group = jQuery('#u3a-find-members-search-group' + idsuffix).val();
	var nxt = jQuery('#u3a-find-members-search-next' + idsuffix).val();
	var op = jQuery('#u3a-find-members-search-op' + idsuffix).val();
//		console.debug("op", op);
	var form_data = {
		"member-surname": sname,
		"member-forename": fname,
		"member-number": mnum,
		"action": action,
		"group": group,
		"next_action": nxt,
		"op": op,
		"byname": byname,
		"idsuffix": idsuffix
	};
	console.debug("u3a-find-members-search-button", "form_data", form_data);
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.debug(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
			var returned = JSON.parse(data)
			console.debug("success", returned, "idsuffix", idsuffix);
			jQuery('#u3a-find-members-results-div' + idsuffix).html(returned["html"]);
			jQuery('#u3a-find-members-results-div' + idsuffix).removeClass("u3a-invisible");
			jQuery('#u3a-find-members-results-div' + idsuffix).addClass("u3a-visible");
			if (returned["nfound"] > 0)
			{
				u3a_show('u3a-find-member-a-div' + idsuffix);
			}
			else
			{
				u3a_hide('u3a-find-member-a-div' + idsuffix);
			}
		}
	});
}

function u3a_remove_member_from_group_clicked(e)
{
	Swal.fire({
		title: 'Are you sure?',
		text: "Member will be permanently removed from group!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, remove!'
	}).then(function (result)
	{
		if (result.value)
		{
			var form_data = {
				action: "u3a-remove-member-from-group-action",
				members_id: jQuery('#u3a-member-select-remove').val(),
				groups_id: jQuery('#u3a-member-select-group-remove').val()
			}
//		console.debug("remove from group", form_data);
			jQuery.ajax({
				type: 'POST',
				url: settings.ajaxurl,
				data: form_data,
				error: function (jqXHR, textStatus, errorThrown)
				{
					console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
				},
				success: function (html)
				{
					console.log("success", html);
					var mbr = jQuery("#u3a-member-select-remove option:selected").text();
					var grp = jQuery('#u3a-member-select-groupname-remove').val();
					swal.fire("Remove Member", "member " + mbr + " removed from " + grp, "success").then(function ()
					{
						var groups_id = jQuery('#u3a-group-page-group-id').val();
						if (groups_id)
						{
							u3a_reload_group_page();
						}
						else
						{
							u3a_reload_committee_manage_groups_page();
						}
					});
				}
			});
		}
	});
}

function u3a_slideshow(groups_id, name, attach, titles)
{
//	console.debug(name, attach);
	jQuery('<form action="' + settings.slideshow + '" method="POST" target="_blank"><input type="hidden" name="group" value="' + groups_id +
			  '"><input type="hidden" name="name" value="' + name + '"><input type="hidden" name="ids" value="' + attach + '"><input type="hidden" name="titles" value="' + titles + '"></form>').appendTo('body').submit();
}

function u3a_sort_list_close(groups_id, type, catid, is_group)
{
	var docs = jQuery("#u3a-sort-list-" + groups_id + "-" + type + "-" + catid + " input.u3a-sort-list-item-value").map(
			  function ()
			  {
				  return jQuery(this).val();
			  }).get().join(",");
	console.debug(docs);
	var form_data = {
		action: "u3a-sort-documents",
		type: type,
		category: catid,
		documents: docs
	}
	if (is_group)
	{
		if (groups_id > 0)
		{
			u3a_ajax(form_data, "Sort", u3a_reload_group_page);
		}
		else
		{
			u3a_ajax(form_data, "Sort", u3a_reload_committee_manage_page);
		}
	}
	else
	{
		u3a_ajax(form_data, "Sort", u3a_reload_member_page);
	}
}

function u3a_edit_document_changed(groups_id, type)
{
	var catid = jQuery('#u3a-document-category-select-manage-documents-' + groups_id + '-' + type).val();
	var editid = "u3a-copy-document-" + groups_id + "-" + type + "-" + catid;
	var docid = jQuery('#' + editid).val();
	console.debug(catid, editid, docid);
	var form_data = {
		action: "u3a_get_document_details",
		document: docid
	}
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
			var returned = JSON.parse(data);
			if (returned["success"])
			{
				jQuery('#u3a-edit-title-' + groups_id + "-" + type).val(returned["title"]);
				jQuery('#u3a-edit-by-' + groups_id + "-" + type).val(returned["author"]);
				jQuery('#u3a-visibility-edit-' + groups_id + "-" + type).val(returned["visibility"]);
			}
		}
	});
}

function u3a_edit_document(groups_id, type, is_group)
{
	console.debug("edit");
	var catid = jQuery('#u3a-document-category-select-manage-documents-' + groups_id + '-' + type).val();
	var editid = "u3a-copy-document-" + groups_id + "-" + type + "-" + catid;
	var docid = jQuery('#' + editid).val();
	var title = jQuery('#u3a-edit-title-' + groups_id + "-" + type).val();
	var author = jQuery('#u3a-edit-by-' + groups_id + "-" + type).val();
	var visibility = jQuery('#u3a-visibility-edit-' + groups_id + "-" + type).val();
	var form_data = {
		action: "u3a_edit_document_details",
		document: docid,
		title: title,
		author: author,
		visibility: visibility
	}
	if (is_group)
	{
		if (groups_id > 0)
		{
			u3a_ajax(form_data, "Edit", u3a_reload_group_page);
		}
		else
		{
			u3a_ajax(form_data, "Sort", u3a_reload_committee_manage_page);
		}
	}
	else
	{
		u3a_ajax(form_data, "Edit", u3a_reload_member_page);
	}
}

function u3a_payment_type_changed(id, op)
{
	var val = jQuery('#' + id).val();
	if (op === 'join')
	{
		if ((val === 'PayPal') || (val === 'CreditCard'))
		{
			jQuery('.u3a-member-btn-class').hide();
			jQuery('.u3a-member-pay-btn-class').show();
//			jQuery('.u3a-paypal-container').show();
		}
		else
		{
			jQuery('.u3a-member-btn-class').show();
			jQuery('.u3a-member-pay-btn-class').hide();
			jQuery('.u3a-paypal-container').hide();
			jQuery('.u3a-paypal-text-class').hide();
		}
	}
}

function u3a_join_paypal()
{
	var msg = u3a_validate_member_details_form('join');
	console.debug("msg", msg);
	if (msg)
	{
		swal.fire("Incomplete Form", '"' + msg + '" must be supplied.', "error");
	}
	else
	{
		jQuery('.u3a-paypal-container').show();
		jQuery('.u3a-paypal-text-class').show();
		jQuery([document.documentElement, document.body]).animate({
			scrollTop: $(".u3a-paypal-container").offset().top
		}, 2000);
	}
}

function u3a_validate_member_details_form(op)
{
	var req = jQuery("#u3a-member-required-" + op).val().split(',');
	var len = req.length;
	var ret = null;
	for (var n = 0; (n < len) && (ret === null); n++)
	{
		var inp = jQuery('#u3a-member-form-' + op + ' input[name="' + req[n] + '"]');
		var val = inp.val();
//		console.debug(inp, val);
		if (!val)
		{
			var st = inp.parent().children("span").text();
			ret = st.substring(0, st.length - 2);
//			console.debug("setting ret", ret);
			break;
		}
	}
//	console.debug("ret", ret);
	return ret;
}

function change_member_status(op)
{
	var mnum = jQuery("#u3a-member-number-" + op).text();
	var status = jQuery("#member-status-select-" + op).val();
	var form_data = {
		action: "u3a_change_member_status",
		membership_number: mnum,
		status: status
	}
	u3a_ajax(form_data, "Change Status", u3a_reload_group_page);
//	console.debug("change_member_status");
}

function u3a_join_clicked(cssclass)
{
	console.debug("u3a_gift_aid_clicked");
	jQuery("." + cssclass).modal();
}

function u3a_join_close(id1, id2)
{
	console.debug("u3a_gift_aid_close", id1, id2);
	if (jQuery('#' + id2).prop("checked"))
	{
		jQuery('#' + id1).prop("checked", true);
	}
	else
	{
		jQuery('#' + id1).prop("checked", false);
	}
}

function u3a_download_group_emails()
{
	var groupname = jQuery("#u3a-group-page-group-name").val().toLowerCase() + ".csv";
	var data1 = [];
	jQuery(".u3a-group-email-class").each(function ()
	{
		var txt = jQuery(this).text();
		if (txt && txt.length > 0)
		{
			data1.push(txt);
		}
	});
	var data = data1.join("\n");
	var blob = new Blob([data], {type: 'text/csv'});
	if (window.navigator.msSaveOrOpenBlob)
	{
		window.navigator.msSaveBlob(blob, groupname);
	}
	else
	{
		var elem = window.document.createElement('a');
		elem.href = window.URL.createObjectURL(blob);
		elem.download = groupname;
		document.body.appendChild(elem);
		elem.click();
		document.body.removeChild(elem);
	}
}

function u3a_download_coordinator_emails()
{
	var groupname = "coordinators.csv";
	var data1 = [];
	jQuery(".u3a-group-email-class").each(function ()
	{
		var txt = jQuery(this).text();
		if (txt && txt.length > 0)
		{
			data1.push(txt);
		}
	});
	var data = data1.join("\n");
	var blob = new Blob([data], {type: 'text/csv'});
	if (window.navigator.msSaveOrOpenBlob)
	{
		window.navigator.msSaveBlob(blob, groupname);
	}
	else
	{
		var elem = window.document.createElement('a');
		elem.href = window.URL.createObjectURL(blob);
		elem.download = groupname;
		document.body.appendChild(elem);
		elem.click();
		document.body.removeChild(elem);
	}
}

function gotoregister(args)
{
//	console.debug(args);
	var url = args["url"] + "?mnum=" + args["mnum"] + "&email=" + args["email"];
//	console.debug(url);
	window.location.href = url;
}

function test_membership_card()
{
	var form_data = {
		action: "u3a_test_membership_card",
		membership_number: 174
	}
	u3a_ajax(form_data, "Membership Card");
}

function u3a_address_labels()
{
	var form_data = {
		action: "u3a_address_labels"
	}
	u3a_ajax(form_data, "Address Labels", u3a_open);
}

function test_mailing_lists()
{
	var form_data = {
		action: "u3a_test_mailing_list",
		mailing_list: "posc"
	}
	u3a_ajax(form_data, "Mailing List");
}

function u3a_set_option(optname, optval)
{
	var form_data = {
		action: "u3a_set_option",
		option: optname,
		optval: optval
	}
	u3a_ajax(form_data, "Set Option");
}

function u3a_test_alert(title, message, icon)
{
	swal.fire(title, message, icon).then(function ()
	{
		console.debug("finished");
	});
}

function u3a_create_mailing_list(groups_id)
{
	var name = jQuery('#u3a-mailing-list-name').val();
	if (name && name.length > 0)
	{
		var form_data = {
			action: "u3a_create_mailing_list",
			name: name,
			group: groups_id
		}
		u3a_ajax(form_data, "Create Mailing List");
	}
}

function u3a_select_contact_details()
{
	var members_id = jQuery('#u3a-member-select-view_contact_details').val();
	console.debug("u3a_select_contact_details", members_id);
	var form_data = {
		action: "u3a_contact_details",
		member: members_id
	}
	u3a_ajax(form_data, "Contact Details", function (html)
	{
		jQuery('#u3a-group-member-contact-details').html(html);
	});
}

function u3a_remove_from_waiting_list(groups_id, members_id)
{
	var form_data = {
		action: "u3a_remove_from_waiting_list",
		member: members_id,
		group: groups_id
	}
	u3a_ajax(form_data, "Remove from Waiting List");
}

function u3a_accept_from_waiting_list(groups_id, members_id)
{
	var form_data = {
		action: "u3a_accept_from_waiting_list",
		member: members_id,
		group: groups_id
	}
	u3a_ajax(form_data, "Accept from Waiting List");
}


function u3a_test_reply_preference(email)
{
	var form_data = {
		action: "u3a_test_reply_preference",
		email: email
	}
	u3a_ajax(form_data, "Test Reply Preference");
}

function u3a_download_address_list(where)
{
	var form_data = {
		action: "u3a_download_address_list",
		where: where
	}
	u3a_ajax(form_data, "Download Address List", u3a_open);
}

function u3a_enable_if_checked(check_if_checked, to_enable)
{
	if (jQuery('#' + check_if_checked).is(":checked"))
	{
		console.debug("enabling", check_if_checked, to_enable);
		jQuery('#' + to_enable).prop("disabled", false);
	}
	else
	{
		console.debug("disabling", check_if_checked, to_enable);
		jQuery('#' + to_enable).prop("disabled", true);
	}
}

function u3a_disable_if_checked(check_if_checked, to_enable)
{
	if (jQuery('#' + check_if_checked).is(":checked"))
	{
		jQuery('#' + to_enable).prop("disabled", true);
	}
	else
	{
		jQuery('#' + to_enable).prop("disabled", false);
	}
}

function u3a_col_close()
{
	var nchecked = jQuery('#u3a-member-select-div input[type="checkbox"]:checked').length;
	if (nchecked > 0)
	{

		jQuery("#u3a-members-display-table").prop("disabled", false);
		jQuery("#u3a-members-download-csv").prop("disabled", false);
		jQuery("#u3a-members-download-xlsx").prop("disabled", false);
		//enable buttons
	}
}

function u3a_toggle_up_down(prefix, updown, divid)
{
//	console.debug(prefix, updown, divid);
	if (updown === 'up')
	{
		u3a_hide(divid);
		u3a_hide1(prefix + 'up');
		u3a_show1(prefix + 'down');
	}
	else
	{
		u3a_show(divid);
		u3a_show1(prefix + 'up');
		u3a_hide1(prefix + 'down');
	}
}

function u3a_outer_clicked(prefix, which1)
{
	jQuery('#' + prefix + 'div-' + which1 + ' input[type="checkbox"]').prop("checked", false);
	var checked = jQuery('#' + prefix + which1).is(":checked");
//	console.debug(prefix, which1, checked ? "yes" : "no");
	if (checked)
	{
		var def = jQuery('#' + prefix + 'default-value-' + which1).val();
//		console.debug("def", def);
		jQuery('#' + prefix + 'div-' + which1 + ' input[name="' + def + '"]').prop("checked", true);
	}
// if on, click the default only
// if off unclick all
}

function u3a_inner_clicked(prefix, which1)
{
//	console.debug("u3a_inner_clicked");
	var nchecked = jQuery('#' + prefix + 'div-' + which1 + ' input[type="checkbox"]:checked').length;
//	console.debug(prefix, which1, nchecked);
	if (nchecked > 0)
	{
		jQuery('#' + prefix + which1).prop("checked", true);
	}
	else
	{
		jQuery('#' + prefix + which1).prop("checked", false);
	}
// if at least one on, set outer
// if all off unset outer
}

function u3a_members_display_table()
{
	jQuery("#u3a-display-members-after-close").val("table");
	u3a_get_members_display();
}

function u3a_get_members_display()
{
	console.debug("u3a_members_display_table");
	var cols = {};
	var column_headings = [];
	var column_names = [];
	var val = null;
	var val0 = null;
	var val1 = null;
	var slash = null;
	var hdr = null;
	var subs = {};
	var plain = {};
	var n = 0;
	jQuery(".u3a-members-column-checkbox-class:checked").each(function ()
	{
		hdr = "" + n + "." + jQuery(this).siblings("span.u3a-labelled-object-text").text();
		n++;
		val = jQuery(this).val();
//		console.debug(hdr, val);
		slash = val.indexOf('/');
		if (slash > 0)
		{
			val0 = val.substr(0, slash);
			val1 = val.substr(slash + 1);
			var subhdr = [];
			subhdr[val1] = hdr;
			if (subs[val0] === undefined)
			{
				subs[val0] = [];
			}
			subs[val0][val1] = hdr;
//			cols[val0] = null;
//			val = val1;
		}
		else
		{
			plain[val] = hdr; //
		}
//		cols[val] = hdr;
	});
//	console.debug("subs", subs);
//	console.debug("plain", plain);
	for (var val in subs)
	{
		if (subs.hasOwnProperty(val))
		{
			cols[val] = null;
			var subs1 = subs[val];
			for (var val1 in subs1)
			{
				cols[val1] = subs1[val1];
			}
		}
	}
	for (var val in plain)
	{
		if (plain.hasOwnProperty(val) && !cols.hasOwnProperty(val))
		{
			cols[val] = plain[val];
		}
	}
	var sort_contents = "";
	var maxndx = 0;
	for (var colname in cols)
	{
		if (cols.hasOwnProperty(colname))
		{
			var hdr = cols[colname];
			if (hdr !== null)
			{
				var hdra = hdr.split('.');
				var ndx = parseInt(hdra[0]);
				column_headings[ndx] = hdra[1];
				column_names[ndx] = colname;
				if (ndx > maxndx)
				{
					maxndx = ndx;
				}
			}
		}
	}
	for (var n = 0; n <= maxndx; n++)
	{
		if (column_headings[n] !== undefined)
		{
			sort_contents += '<li class="u3a-member-columns-sort-item"><span class="u3a-member-columns-text">' + column_headings[n] + '</span><input type="hidden" class="u3a-member-columns-name" value="' +
					  column_names[n] + '"/></li>';
		}
	}
	jQuery("#u3a-members-column-sort-list").html(sort_contents);
	jQuery("#u3a-members-column-sort-list-outer").modal();
//	console.debug("cols", cols);

}

function u3a_members_download(fmt)
{
	console.debug("u3a_members_download", fmt);
	jQuery("#u3a-display-members-after-close").val(fmt);
	u3a_get_members_display();
}

function u3a_sort_columns_close()
{
	console.debug("u3a_sort_colums_close");
	var mbrs = [];
	jQuery(".u3a-get-member-details-id-class").each(function ()
	{
		mbrs.push(jQuery(this).val());
	});
	var members = mbrs.join(',');
	var colnames = [];
	var colhdrs = [];
	jQuery(".u3a-member-columns-name").each(function ()
	{
		colnames.push(jQuery(this).val());
		colhdrs.push(jQuery(this).siblings("span.u3a-member-columns-text").text());
	});
	var columns = colnames.join(',');
	var headers = colhdrs.join(',');
	console.debug(members);
	console.debug(columns);
	console.debug(headers);
	var which_action = jQuery("#u3a-display-members-after-close").val();
	if (which_action === "table")
	{
		var link = jQuery("#u3a-display-members-page-link").val();
		jQuery('<form action="' + link + '" method="POST"><input type="hidden" name="members" value="' + members +
				  '"><input type="hidden" name="columns" value="' + columns + '"><input type="hidden" name="headers" value="' + headers +
				  '"></form>').appendTo('body').submit();
	}
	else
	{
		var fmt = jQuery("#u3a-display-members-after-close").val();
		var form_data = {
			action: "u3a_members_download",
			members: members,
			colnames: columns,
			colhdrs: headers,
			format: fmt
		};
		u3a_ajax(form_data, "Download Members List", u3a_open);
	}
}

function u3a_update_information(members_id)
{
//	console.debug("members_id", members_id);
//	var info = jQuery("textarea#u3a-member-personal-page-text").val();
	var info = tinymce.activeEditor.getContent();
//	console.debug("info", info);
	var form_data = {
		action: "u3a_update_information",
		member: members_id,
		info: info
	};
	u3a_ajax(form_data, "Update Member Information");
}

function u3a_refresh_personal_page(members_id, manage)
{
//	var members_id = jQuery('#u3a-member-personal-page-id').val();
	if (members_id)
	{
		if (typeof manage === 'undefined')
		{
			manage = "no";
		}
		var tab = jQuery("span.su-tabs-current").text();
		var spoiler = jQuery("div.su-spoiler:not(.su-spoiler-closed) div.su-spoiler-title").text();
		console.debug("members_id", members_id, "tab", tab, "spoiler", spoiler);
		jQuery('<form action="' + window.location.href.split('?')[0] + '" method="POST"><input type="hidden" name="member" value="' + members_id +
				  '"><input type="hidden" name="tab" value="' + tab + '"><input type="hidden" name="spoiler" value="' + spoiler +
				  '"><input type="hidden" name="manage" value="' + manage + '"></form>').appendTo('body').submit();
	}
	else
	{
		console.debug("no member id");
	}


}

function change_header_image(rnd)
{
	var type = jQuery(".u3a-home-image-type").val();
	var cat = jQuery(".u3a-home-image-category").val();
	var grp = jQuery(".u3a-home-image-group").val();
	var mbr = jQuery(".u3a-home-image-member").val();
	var form_data = {
		action: "u3a_get_header_image",
		type: type,
		cat: cat,
		group: grp,
		member: mbr
	};
	if (!rnd)
	{
		form_data["index"] = jQuery(".u3a-home-image-index").val();
		form_data["total"] = jQuery(".u3a-home-image-total").val();
	}
	if (jQuery('#u3a-group-page-group-id').length)
	{
		var grp = jQuery('#u3a-group-page-group-id').val();
		form_data["group"] = grp;
	}
	if (jQuery('#u3a-member-personal-page-id').length)
	{
		var mbr = jQuery('#u3a-member-personal-page-id').val();
		form_data["member"] = mbr;
	}
	u3a_ajax(form_data, "Change Header Image", function (data)
	{
		var data1 = data.split('|');
		jQuery('img.u3a-header-image').attr("src", data1[0]);
		jQuery('img.u3a-header-image').attr("title", data1[1]);
		jQuery(".u3a-home-image-index").val(data1[2]);
		jQuery(".u3a-home-image-total").val(data1[3]);
	});
}

function u3a_delete_thread(groups_id, key)
{
	console.debug("delete thread", key);
	Swal.fire({
		title: 'Are you sure?',
		text: "Thread will be permanently deleted!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!'
	}).then(function (result)
	{
		if (result.value)
		{
			var form_data = {
				action: "u3a_delete_post",
				group: groups_id,
				thread: key
			};
			u3a_ajax(form_data, "delete post", u3a_reload_group_page);
		}
	});
}

function u3a_get_forum_post(groups_id, key)
{
	console.debug("start thread", groups_id);
	//<textarea aria-label="Type your message here" class="swal2-textarea" placeholder="Type your message here..." style="display: flex;"></textarea>
	Swal.fire({
//		input: 'textarea',
//		inputPlaceholder: 'Type your message here...',
//		inputAttributes: {
//			'aria-label': 'Type your message here'
//		},
		title: 'Your comment',
		html:
				  '<input id="swal-input1" class="swal2-input" placeholder="title">' +
				  '<textarea id="swal-input2" class="swal2-textarea" placeholder="Type your message here..." style="display: flex;">',
		focusConfirm: false,
		preConfirm: function ()
		{
			return [
				jQuery('#swal-input1').val(),
				jQuery('#swal-input2').val()
			];
		},
		showCancelButton: true
	}).then(function (text)
	{
		if (text.isConfirmed && text.value)
		{
			var form_data = {
				action: "u3a_add_post",
				group: groups_id,
				title: text.value[0],
				text: text.value[1],
				replyto: key
			};
			u3a_ajax(form_data, "new post", u3a_reload_group_page);
		}
		console.debug("text is", text);
	});
//										  const { value: text } = await Swal.fire({
//								input: 'textarea',
//										  inputPlaceholder: 'Type your message here...',
//										  inputAttributes: {
//										  'aria-label': 'Type your message here'
//										  },
//										  showCancelButton: true
//										  })
//
//										  if (text) {
//								Swal.fire(text)
//								}
}

function u3a_add_friend(members_id, friend_id)
{
	var form_data = {
		action: "u3a_add_friend",
		member: members_id,
		friend: friend_id
	};
	u3a_ajax(form_data, "add friend", u3a_reload_member_page);
	console.debug("add friend", members_id, friend_id);
}

function u3a_remove_friend_clicked(members_id, friend_id)
{
	swal.fire({title: "remove as friend!",
		text: "Are you sure you wish to proceed?",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: '#d33',
		confirmButtonText: 'confirm remove'})
//			buttons: true,
//			dangerMode: true})
			  .then(
						 function (result)
						 {
							 if (result.value)
							 {
								 var form_data = {
									 action: "u3a_remove_friend",
									 member: members_id,
									 friend: friend_id
								 };
								 u3a_ajax(form_data, "remove friend", u3a_reload_member_page);
								 console.debug("remove friend", members_id, friend_id);
							 }
						 });
}

function u3a_group_mailing_list(op, groups_id)
{
	console.debug("u3a_group_mailing_list", op, groups_id);
	var members = [];
	jQuery(".u3a-member-checkbox-class:checked").each(function ()
	{
		members.push(jQuery(this).val());
	});
	console.debug(members);
	var form_data = {
		action: "u3a_group_mailing_list",
		group: groups_id,
		list: jQuery("#u3a-current-sublist-" + groups_id).val(),
		members: members.join(','),
		op: op
	};
	if (op === "save")
	{
		Swal.fire({
			title: 'sublist name',
			input: 'text',
			showCancelButton: true
		}).then(
				  function (result)
				  {
					  console.debug(result);
					  if (result.isConfirmed && result.value)
					  {
						  form_data["name"] = result.value;
						  u3a_ajax(form_data, op + " sublist", u3a_reload_group_page);
					  }
				  });
	}
	else if (op === "delete")
	{
		Swal.fire({
			title: 'Are you sure?',
			text: "sublist will be permanently deleted!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!'
		}).then(function (result)
		{
			if (result.isConfirmed)
			{
				u3a_ajax(form_data, "delete sublist", u3a_reload_group_page);
			}
		});
	}
	else
	{
		u3a_ajax(form_data, op + " sublist", u3a_reload_group_page);
	}
}

function u3a_sublist_select_changed(groups_id)
{
	var list_id = jQuery("#u3a-sublist-select-" + groups_id).val();
	console.debug("u3a_load_group_mailing_list", groups_id);
	var form_data = {
		action: "u3a_load_group_mailing_list",
		group: groups_id,
		list: list_id
	};
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (data)
		{
//			console.log("success", data);
			var returned = JSON.parse(data);
			if (returned["success"])
			{
				var member_ids = returned["message"].split(',');
				jQuery("#u3a-current-sublist-" + groups_id).val(list_id);
				jQuery("#u3a-group-delete-button-" + groups_id).prop("disabled", false);
				jQuery(".u3a-member-checkbox-class").prop("checked", false);
				for (var n = 0; n < member_ids.length; n++)
				{
					jQuery("#u3a-member-checkbox-" + member_ids[n]).prop("checked", true);
				}
			}
			else
			{
				swal.fire("Load Sublist", returned["message"], "error");
			}
		}
	});
}

function u3a_check_sublist_buttons(cbid)
{
	var groups_id = jQuery("#u3a-group-page-group-id").val();
	if (cbid === "u3a-member-checkbox-all")
	{
		jQuery(".u3a-sublist-button").prop("disabled", true);
	}
	else
	{
		if (jQuery(".u3a-member-checkbox-class:checked").length === 0)
		{
			jQuery(".u3a-sublist-button").prop("disabled", true);
		}
		else
		{
			jQuery("#u3a-group-save-button-" + groups_id).prop("disabled", false);
			jQuery("#u3a-group-update-button-" + groups_id).prop("disabled", jQuery("#u3a-current-sublist-" + groups_id).val() == 0);
		}
	}
	jQuery("#u3a-group-delete-button-" + groups_id).prop("disabled", jQuery("#u3a-current-sublist-" + groups_id).val() == 0);
}

function u3a_link_section_select_changed(groups_id, members_id)
{
	var section_id = jQuery("#u3a-link-section-select-" + groups_id + "-" + members_id).val();
	if (section_id > 0)
	{
		jQuery("#u3a-new-link-button-" + groups_id + "-" + members_id).prop("disabled", false);
	}
	else
	{
		jQuery("#u3a-new-link-button-" + groups_id + "-" + members_id).prop("disabled", true);
	}
}

function u3a_new_link_section(groups_id, members_id)
{
	var form_data = {
		action: "u3a_new_link_section",
		group: groups_id,
		member: members_id
	};
	Swal.fire({
		title: 'section name',
		input: 'text',
		showCancelButton: true
	}).then(
			  function (result)
			  {
				  console.debug(result);
				  if (result.isConfirmed && result.value)
				  {
					  form_data["name"] = result.value;
					  u3a_ajax(form_data, "create section", u3a_reload_manage_links);
				  }
			  });
}

function u3a_new_link(groups_id, members_id)
{
	var section_id = jQuery("#u3a-link-section-select-" + groups_id + "-" + members_id).val();
	Swal.fire({
		title: 'New Link',
		html:
				  '<input id="swal-input1" class="swal2-input" placeholder="description">' +
				  '<input id="swal-input2" class="swal2-input" placeholder="url">',
		focusConfirm: false,
		preConfirm: function ()
		{
			return [
				jQuery('#swal-input1').val(),
				jQuery('#swal-input2').val()
			];
		},
		showCancelButton: true
	}).then(function (text)
	{
		if (text.isConfirmed && text.value)
		{
			var form_data = {
				action: "u3a_new_link",
				section: section_id,
				description: text.value[0],
				url: text.value[1]
			};
			u3a_ajax(form_data, "new link", u3a_reload_links);
		}
//		console.debug("text is", text);
	});
}

function u3a_reload_manage_links()
{
	var members_id = 0;
	var groups_id = 0;
	var gid = jQuery('#u3a-group-page-group-id').val();
	if (gid && gid > 0)
	{
		groups_id = gid;
	}
	else
	{
		var mid = jQuery("#u3a-member-personal-page-id").val();
		if (mid && mid > 0)
		{
			members_id = mid;
		}
	}
	var form_data = {
		action: "u3a_reload_manage_links",
		member: members_id,
		group: groups_id
	};
	jQuery.ajax({
		type: 'POST',
		url: settings.ajaxurl,
		data: form_data,
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
		},
		success: function (html)
		{
//					console.debug(html);
			jQuery("div.u3a-manage-links-div").html(html);
		}
	});
}

function u3a_reload_links()
{
	var members_id = 0;
	var groups_id = 0;
	var gid = jQuery('#u3a-group-page-group-id').val();
	if (gid && gid > 0)
	{
		groups_id = gid;
	}
	else
	{
		var mid = jQuery("#u3a-member-personal-page-id").val();
		if (mid && mid > 0)
		{
			members_id = mid;
		}
	}
	if (members_id > 0 || groups_id > 0)
	{
		var form_data = {
			action: "u3a_reload_links",
			member: members_id,
			group: groups_id
		};
		jQuery.ajax({
			type: 'POST',
			url: settings.ajaxurl,
			data: form_data,
			error: function (jqXHR, textStatus, errorThrown)
			{
				console.log(jqXHR + ' :: ' + textStatus + ' :: ' + errorThrown);
			},
			success: function (html)
			{
//					console.debug(html);
				jQuery("div.u3a-links-div").html(html);
			}
		});
	}
}

