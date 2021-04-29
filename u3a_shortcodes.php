<?php

/* Arda v1.0
 * Copyright 2021 Mike Curtis (mike@computermike.biz)
 *
 * This file is part of Arda.
 *   Arda is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU Affero General Public License version 3
 *   as published by the Free Software Foundation
 *
 *   Ardais distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You can get a copy The GNU Affero General Public license from
 *   http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

require_once(ABSPATH . 'wp-config.php');
require_once 'U3ADatabase.php';
require_once 'u3a_information.php';
require_once 'u3a_admin.php';
require_once 'u3a_database_utilities.php';

add_shortcode("u3a_find_member_dialog", "u3a_find_member_dialog_contents");

function u3a_find_member_dialog_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'			 => 0,
		'next_action'	 => "be_coordinator",
		'close'			 => "OK",
		'op'				 => "add",
		'icon'			 => "search",
		'title'			 => null,
		'suffix'			 => "",
		'byname'			 => "yes"
	  ), $atts1, 'u3a_find_member_dialog');
//	write_log($atts1);
//	write_log($atts);
	$idsuffix = "-" . str_replace("_", "-", $atts["next_action"]) . "-" . $atts["op"] . $atts["suffix"];
	if ($atts["close"] == "tick")
	{
		$cls = '<span class="dashicons dashicons-yes-alt"></span>';
	}
	else
	{
		$cls = $atts["close"];
	}
	if ($atts["group"])
	{
		$txt = do_shortcode('[u3a_select_group_members group="' . $atts["group"] . '" next_action="' . $atts["next_action"] . '" op="' . $atts["op"] . '"]');
		$closea = new U3A_A('#', $cls, 'u3a-find-member-a' . $idsuffix, null,
		  "u3a_select_group_member_dialog_close('" . $atts["next_action"] . "', '" . $atts["op"] . "');");
		$closea->add_attribute("rel", "modal:close");
		$close = new U3A_DIV($closea, 'u3a-find-member-a-div' . $idsuffix, "u3a-visible");
	}
	else
	{
		$txt = do_shortcode('[u3a_find_members group="' . $atts["group"] . '" next_action="' . $atts["next_action"] . '" op="' . $atts["op"] . '" byname="' . $atts["byname"] . '" suffix="' . $atts["suffix"] . '"]');
		$closea = new U3A_A('#', $cls, 'u3a-find-member-a' . $idsuffix, null,
		  "u3a_find_member_dialog_close('" . $atts["next_action"] . "', '" . $atts["op"] . "', '" . $atts["suffix"] . "');");
		$closea->add_attribute("rel", "modal:close");
		$close = new U3A_DIV($closea, 'u3a-find-member-a-div' . $idsuffix, $atts["group"] ? "u3a-visible" : "u3a-invisible");
	}
	$div = new U3A_DIV([$txt, $close], "u3a-find-member-div" . $idsuffix, "modal");
	$open = new U3A_A('#u3a-find-member-div' . $idsuffix,
	  '<span class="dashicons dashicons-' . $atts["icon"] . '"></span>', 'u3a-find-member-search-a' . $idsuffix,
	  "u3a-inline-block");
	$open->add_attribute("rel", "modal:open");
	if ($atts["title"])
	{
		$open->add_attribute("title", $atts["title"]);
	}
	return U3A_HTML::to_html([$open, $div]);
}

add_shortcode('u3a_group', 'u3a_group_contents');

function u3a_group_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'	 => NULL,
		"groupid" => 0,
		'pgid'	 => 0
	  ), $atts1, 'u3a_group');
	$member = U3A_Information::u3a_logged_in_user();
	$groups_id = $atts["groupid"];
	$grp = U3A_Groups::get_group($groups_id);
//	write_log($grp);
//	$grp = U3A_Groups::get_group(addslashes($atts["group"]));
	$info = new U3A_DIV($grp->information, "u3a-group-info-" . $grp->id, "u3a-group-info-class u3a-group-class");
	$when1 = $grp->get_meets_when_text();
	$when = new U3A_DIV([new U3A_SPAN("Meets: ", null, "u3a-inline-block u3a-width-12-em"), $when1],
	  "u3a-group-when-" . $grp->id, "u3a-group-when-class u3a-group-class u3a-margin-top-5");
	$venue = $grp->get_venue_name();
	if ($venue)
	{
		$vnu = new U3A_DIV([new U3A_SPAN("Venue: ", null, "u3a-inline-block u3a-width-12-em"), $venue],
		  "u3a-group-venue-" . $grp->id, "u3a-group-venue-class u3a-group-class u3a-margin-top-5");
	}
	else
	{
		$vnu = null;
	}
	$nmem = new U3A_DIV([new U3A_SPAN("Current membership: " . $grp->get_number_of_members() . ".", null,
		  "u3a-inline-block u3a-width-12-em"),
		new U3A_SPAN($grp->get_number_of_registered_members() . " registered on Arda.", null,
		  "u3a-inline-block u3a-width-12-em u3a-italic")], null, "");
	$nwait = new U3A_DIV([new U3A_SPAN("Waiting List: ", null, "u3a-inline-block u3a-width-12-em"), "" . $grp->get_number_waiting()],
	  null, "");
	if ($atts['pgid'])
	{
		$linkto1 = do_shortcode('[su_permalink id="' . $atts['pgid'] . '"]</p>');
		$linkto = new U3A_DIV([new U3A_SPAN("Group Page: ", null, "u3a-inline-block u3a-width-12-em"), $linkto1], null, "");
	}
	else
	{
		$linkto = null;
	}
	if ($member)
	{
		$coords = U3A_Groups::get_coordinators($grp);
//		write_log($coords);
		$coordinators = [];
		foreach ($coords as $cd)
		{
			if ($cd)
			{
				$c = U3A_Members::get_member($cd);
				$ml = new U3A_A("#u3a-send-mail-individual-0", $c->forename . " " . $c->surname, null, "u3a-group-mail-class",
				  "u3a_mail_clicked('" . U3A_Utilities::strip_all_slashes($c->email) . "')");
				$ml->add_attribute("rel", "modal:open");
				$coordinators[] = $ml->to_html();
			}
		}
//		write_log($coordinators);
		$allcoordinators = implode(", ", $coordinators);
//		write_log($allcoordinators);
		$coord = new U3A_DIV([new U3A_SPAN((count($coordinators) > 1 ? "coordinators: " : "coordinator: "), null,
			  "u3a-inline-block u3a-width-12-em"), $allcoordinators], "u3a-group-coord-" . $grp->id,
		  "u3a-group-coord-class u3a-group-class u3a-margin-top-5");
		$ret = U3A_HTML::to_html([$info, $when, $vnu, $nmem, $nwait, $coord, $linkto]);
	}
	else
	{
		$ret = U3A_HTML::to_html([$info, $when, $vnu, $linkto]);
	}
	return $ret;
}

add_shortcode("u3a_member_search", "u3a_member_search_contents");

function u3a_member_search_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group' => NULL
	  ), $atts1, 'u3a_member_search');
}

add_shortcode('u3a_members', 'u3a_members_contents');

function u3a_members_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'			 => NULL,
		'select'			 => "no",
		'ids'				 => null,
		'op'				 => null,
		"checked"		 => "no",
		"emailcoord"	 => "no",
		"includecount"	 => "no"
	  ), $atts1, 'u3a_members');
	$mbrs = [];
	if ($atts["group"] === "all")
	{
		$groups_id = 0;
		$group_name = "all";
		$coord_ids = [];
		$mtype = "individual";
		$mbrs = U3A_Members::get_all_members(["status" => "Current"]);
	}
	elseif ($atts["group"] === "email")
	{
		$groups_id = 0;
		$group_name = "email";
		$coord_ids = [];
		$mtype = "individual";
		$mbrs = U3A_Members::get_all_members(["status" => "Current", "email<>" => null]);
	}
	elseif ($atts["group"] === "noemail")
	{
		$groups_id = 0;
		$group_name = "noemail";
		$coord_ids = [];
		$mtype = "individual";
		$mbrs = U3A_Members::get_all_members(["status" => "Current", "email" => null]);
	}
	elseif ($atts["group"] > 0)
	{
		$groups_id = U3A_Groups::get_group_id($atts["group"]);
		$group_name = U3A_Groups::get_group_name($groups_id);
		$coord_ids = U3A_Groups::get_coordinator_ids($groups_id);
		$mtype = "group";
	}
	else
	{
		$groups_id = 0;
		$group_name = "committee";
		$coord_ids = [];
		$mtype = "committee";
	}
	$checked = $atts["checked"] === "yes";
	$select = $atts["select"];
	$emailcoord = $atts["emailcoord"];
	$waiting = null;
	if (!$mbrs)
	{
		if ($atts["ids"])
		{
			$mbrs = U3A_Members::get_members(explode(",", $atts["ids"]));
		}
		else
		{
			$mbrs = U3A_Group_Members::get_members_in_group($groups_id, true);
//			$waiting = U3A_Group_Members::get_waiting_list($groups_id, true);
		}
//		usort($mbrs, ["U3A_Members", "compare"]);
	}
//	write_log("mbrs", $mbrs);
	$reg = 0;
	foreach ($mbrs as $m)
	{
		if ($m->wpid)
		{
			$reg++;
		}
	}
	if ($atts["op"])
	{
		$select_id = "u3a-member-select-" . $atts["op"];
		$group_input = new U3A_INPUT("hidden", "u3a-member-select-group", "u3a-member-select-group-" . $atts["op"], null,
		  $groups_id);
		$groupname_input = new U3A_INPUT("hidden", "u3a-member-select-groupname",
		  "u3a-member-select-groupname-" . $atts["op"], null, $group_name);
	}
	else
	{
		$select_id = "u3a-member-select";
		$group_input = new U3A_INPUT("hidden", "u3a-member-select-group", "u3a-member-select-group", null, $groups_id);
		$groupname_input = new U3A_INPUT("hidden", "u3a-member-select-groupname", "u3a-member-select-groupname", null,
		  $group_name);
	}
//	write_log("members in $group");
//	write_log($mbrs);
	if ($atts["includecount"] === "yes")
	{
		$nmem = new U3A_DIV([new U3A_SPAN("Current membership: " . count($mbrs) . ".", null,
			  "u3a-inline-block u3a-width-12-em"), new U3A_SPAN($reg . " registered on Arda.", null,
			  "u3a-inline-block u3a-width-12-em u3a-italic")], null, "u3a-count-members-class u3a-border-bottom");
		$contents = $nmem->to_html();
	}
	else
	{
		$contents = "";
	}
//	write_log($contents);
	if ($mbrs)
	{
		$cdiv = new U3A_DIV(u3a_list_of_group_members($groups_id, $mbrs, $coord_ids, $select, $select_id, $checked, $mtype,
			 $group_input, $groupname_input, $emailcoord, "member"), "u3a-group-members-list-div", "u3a-group-list-div-class");
		$cdiv->add_attribute("tabindex", "-1");
		$contents .= $cdiv->to_html();
//		if ($waiting)
//		{
//			$wait_text = new U3A_DIV(new U3A_B("waiting list"), null, "u3a-margin-left-10 u3a-margin-top-5 u3a-border-top");
//			$contents .= $wait_text->to_html();
//			$wdiv = new U3A_DIV(u3a_list_of_group_members($groups_id, $waiting, $coord_ids, $select, $select_id, $checked,
//				 $mtype, $group_input, $groupname_input, $emailcoord, "waiting"), "u3a-group-members-waiting-list-div",
//			  "u3a-group-list-div-class");
//			$contents .= $wdiv->to_html();
//		}
	}

//	write_log($contents);
	return U3A_HTML::to_html($contents);
}

function u3a_list_of_group_members($groups_id, $mbrs, $coord_ids, $select, $select_id, $checked, $mtype, $group_input,
  $groupname_input, $emailcoord, $cbcss)
{
	$mbr1 = U3A_Information::u3a_logged_in_user();
	$see_renewals = false;
	if ($mbr1 && (U3A_Committee::is_committee_member($mbr1) || U3A_Committee::is_webmanager($mbr1) || U3A_Group_Members::is_a_coordinator($mbr1)))
	{
		$see_renewals = true;
	}
	$contents = "";
//	write_log("select", $select, $mbrs);
	if ($select === "no")
	{
		$contents1 = [];
		foreach ($mbrs as $mbr)
		{
			$asterix = array_search($mbr->id, $coord_ids) === FALSE ? "" : "<sup>*</sup>";
			$mnum = " (" . $mbr->membership_number . ")";
			$cls = ["u3a-text"];
			if ($see_renewals && $mbr->renewal_needed)
			{
				$cls[] = "u3a-red";
			}
			if ($mbr->wpid)
			{
				$cls[] = "u3a-italic";
			}
			$mbrb = new U3A_SPAN($mbr->surname . ", " . $mbr->forename . $mnum . $asterix, null, implode(" ", $cls));
			$mbrtext = $mbrb->to_html();
			if ($checked)
			{
				$cbid = "u3a-$cbcss-checkbox-" . $mbr->id;
				$cb = new U3A_INPUT("checkbox", null, $cbid, "u3a-$cbcss-checkbox-class", $mbr->id);
				$cb->add_attribute("onchange", "u3a_member_checkbox_changed('" . $cbid . "', '" . $mtype . "')");
				$cblbl = new U3A_LABEL($cbid, $mbrtext, null, "u3a-member-checkbox-label-class u3a-inline-block u3a-margin-left-5");
				$p = new U3A_DIV([$cb, $cblbl], null, "u3a-member-class");
			}
			elseif ($emailcoord == "yes" && $asterix)
			{
				$ml = new U3A_A("#u3a-send-mail-individual-" . $groups_id, $mbrtext, null, "u3a-group-mail-class",
				  "u3a_mail_clicked('" . U3A_Utilities::strip_all_slashes($mbr->email) . "')");
				$ml->add_attribute("rel", "modal:open");
				$p = new U3A_DIV($ml, null, "u3a-member-class");
			}
			else
			{
				$p = new U3A_DIV($mbrtext, null, "u3a-member-class");
			}
//				$contents1[] = new U3A_DIV([$p, $group_input, $groupname_input], null, null);
			$contents1[] .= $p;
		}
		if ($checked && ($cbcss === 'member'))
		{
			$cbid = "u3a-$cbcss-checkbox-all";
			$cb = new U3A_INPUT("checkbox", null, $cbid, "u3a-$cbcss-checkbox-class", 0);
			$cb->add_attribute("onchange", "u3a_member_checkbox_changed('" . $cbid . "', '" . $mtype . "')");
			$cblbl = new U3A_LABEL($cbid, "all", null,
			  "u3a-member-checkbox-label-class u3a-inline-block u3a-margin-left-5 u3a-margin-bottom-10");
			$p = new U3A_DIV([$cb, $cblbl], null, "u3a-member-class");
			array_unshift($contents1, $p);
		}
		$contents2 = new U3A_DIV([$contents1, $group_input, $groupname_input], null, "u3a-40vh-auto-y");
		$contents .= $contents2->to_html();
	}
	else
	{
		$options = [];
		foreach ($mbrs as $mbr)
		{
			$ital = $mbr->wpid ? " u3a-italic" : "";
			$asterix = array_search($mbr->id, $coord_ids) === FALSE ? "" : "<sup>*</sup>";
			$options[] = new U3A_OPTION($mbr->membership_number . ": " . $mbr->surname . ", " . $mbr->forename . " (" . U3A_Utilities::strip_all_slashes($mbr->email) . ")" . $asterix,
			  $mbr->id, false, "u3a-member-select-option-" . $mbr->id, "u3a-member-select-option-class$ital");
		}
//		write_log("options", $options);
		$sel = new U3A_SELECT($options, "u3a-member-select", $select_id, "u3a-member-select-class");
		if ($select !== "yes")
		{
			$sel->add_attribute("onchange", $select . "()");
		}
		$lbl = U3A_HTML::labelled_html_object("select member", $sel, null, "u3a-select-label class", false, true);
		$contents .= new U3A_DIV([$lbl, $group_input, $groupname_input], null, null);
	}
	return $contents;
}

add_shortcode('u3a_document_list', 'u3a_document_list_contents');

function u3a_document_list_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'		 => 0,
		'member'		 => 0,
		'category'	 => null,
		'type'		 => 0,
		'upload'		 => "no",
		"visibility" => U3A_Documents::VISIBILITY_GROUP
	  ), $atts1, 'u3a_document_list');
	$grp = intval($atts["group"]);
	$typ = intval($atts["type"]);
	$vis = intval($atts["visibility"]);
	$doctype = $typ === U3A_Documents::NEWSLETTER_TYPE ? "newsletter" : "document";
	$doctypes = $doctype . "s";
	$mbr = U3A_Information::u3a_logged_in_user();
	$docs = "";
	if ($mbr)
	{
		if ($atts["member"])
		{
			$alldocs = U3A_Documents::get_all_documents_for_member($atts["member"], $typ, $vis);
			$id = intval($atts["member"]);
			$memgrp = U3A_Document_Categories::MEMBER_CATEGORY;
			$select = U3A_Document_Utilities::get_category_list_member($id, $typ);
		}
		else
		{
			$alldocs = U3A_Documents::get_all_documents_for_group($grp, $typ, $vis);
			$id = $grp;
			$memgrp = U3A_Document_Categories::GROUP_CATEGORY;
			$select = U3A_Document_Utilities::get_category_list_group($id, $typ);
		}
//		write_log($alldocs["total"]);
//		write_log($grp);
//		write_log($typ);
//		write_log($atts["category"]);
		if ($alldocs["total"])
		{
			$number_of_categories = $alldocs["number_of_categories"];
			if ($atts["category"] !== null)
			{
				if (is_numeric($atts["category"]))
				{
					$thecategory = U3A_Document_Categories::get_category_name($atts["category"]);
					$number_of_categories = 0;
					$alldocuments1 = $alldocs["documents"];
					$alldocuments = [$thecategory => $alldocuments1[$thecategory]];
				}
				else
				{
					$thecategory = $atts["category"];
					$alldocuments = $alldocs["documents"];
				}
			}
			else
			{
				$thecategory = $alldocs["first_non_empty"];
				$alldocuments = $alldocs["documents"];
			}
//			write_log($number_of_categories);
//			write_log(" the category $thecategory");
//			write_log($alldocs["first_non_empty"]);
			if ($number_of_categories)
			{
				$include_default = array_key_exists("default", $alldocuments);
//				$select1 = U3A_Document_Categories::get_select_list($id, $memgrp, $typ, "document-list", "u3a_document_category_select", $thecategory, $include_default);
//				$select = $select1["select"];

				$span = new U3A_SPAN("select category:", null, "u3a-inline-block u3a-right-margin-5");
				$seldiv = new U3A_DIV([new U3A_LABEL("u3a-document-category-select-" . $id . "-" . $typ, $span->to_html()), $select],
				  "u3a-category-select-div-document-list-" . $id . "-" . $typ, "u3a-category-select-div u3a-bottom-margin-5");
				$docs .= $seldiv->to_html();
			}
			foreach ($alldocuments as $catname => $documents)
			{
				$cat = $documents["category"];
				$catid = $cat ? $cat->id : 0;
				$tags = [];
//				write_log("catname $catname");
				if ($thecategory)
				{
					$vis = $catname == $thecategory ? "" : "u3a-invisible";
				}
				else
				{
					$vis = $catname == $alldocs["first_non_empty"] ? "" : "u3a-invisible";
				}
				if ($catid)
				{
					$tags[] = new U3A_H(4, stripslashes($cat->name));
				}
				if ($documents["count"])
				{
					$tags[] = U3A_Document_Utilities::get_document_table($documents["documents"], $typ);
				}
				else
				{
					$tags[] = "There are no $doctypes in this category.";
				}
				$div = new U3A_DIV($tags, "u3a-document-div-$id-$typ-$catid", "u3a-document-div-class-$typ $vis");
				$docs .= $div->to_html();
			}
		}
		else
		{
			$h = new U3A_H(6, "no $doctypes to display");
			$docs .= $h->to_html();
		}
		if ($atts["upload"] === "yes")
		{
			$docs .= '[u3a_manage_document group="' . $id . '"]';
		}
	}
	return do_shortcode($docs);
}

add_shortcode("u3a_manage_document", "u3a_manage_document_contents");

function u3a_manage_document_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'		 => 0,
		'member'		 => 0,
		'type'		 => U3A_Documents::GROUP_DOCUMENT_TYPE,
		'category'	 => 0
	  ), $atts1, 'u3a_manage_document');
	$grp = intval($atts["group"]);
	$mbr = intval($atts["member"]);
	$typ = intval($atts["type"]);
	$cat = intval($atts["category"]);
	if ($mbr)
	{
		$id = $mbr;
		$mbrgrp = U3A_Document_Categories::MEMBER_CATEGORY;
	}
	else
	{
		$id = $grp;
		$mbrgrp = U3A_Document_Categories::GROUP_CATEGORY;
	}
	$docs = U3A_Document_Utilities::get_document_management($id, $mbrgrp, $typ, $cat);
	return U3A_HTML::to_html($docs);
}

add_shortcode('u3a_image_list', 'u3a_image_list_contents');

function u3a_image_list_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'		 => 0,
		'member'		 => 0,
		'category'	 => 0,
		'type'		 => U3A_Documents::GROUP_IMAGE_TYPE
	  ), $atts1, 'u3a_image_list');
	$grp = intval($atts["group"]);
	$typ = intval($atts["type"]);
	$mbr = U3A_Information::u3a_logged_in_user();
	$docs = "";
//	$gall = "<h6>no images to display</h6>";
	if ($mbr)
	{
		if ($atts["member"])
		{
			$attachment_ids = U3A_Documents::get_attachment_ids_for_member($atts["member"], $typ, $atts["category"]);
			$alldocs = U3A_Documents::get_all_documents_for_member($atts["member"], $typ);
			$id = intval($atts["member"]);
			$memgrp = U3A_Document_Categories::MEMBER_CATEGORY;
			$select = U3A_Document_Utilities::get_category_list_member($id, $typ);
		}
		else
		{
			$attachment_ids = U3A_Documents::get_attachment_ids_for_group($grp, $typ, $atts["category"]);
			$alldocs = U3A_Documents::get_all_documents_for_group($grp, $typ);
			$id = $grp;
			$memgrp = U3A_Document_Categories::GROUP_CATEGORY;
			$select = U3A_Document_Utilities::get_category_list_group($id, $typ);
		}
//		write_log($alldocs);
		if ($alldocs["total"])
		{
			$docs1 = "";
			if ($alldocs["number_of_categories"])
			{
//				$select1 = U3A_Document_Categories::get_select_list($id, $memgrp, $typ, "image-list", "u3a_document_category_select", $alldocs["first_non_empty"]);
//				$select = $select1["select"];
				$span = new U3A_SPAN("select album:", null, "u3a-inline-block u3a-right-margin-5");
				$seldiv = new U3A_DIV([new U3A_LABEL("u3a-document-category-select-" . $id . "-" . $typ, $span->to_html()), $select],
				  "u3a-category-select-div-image-list-" . $id . "-" . $typ, "u3a-category-select-div u3a-bottom-margin-5");
				$docs1 .= $seldiv->to_html();
			}
//			write_log($alldocs);
			$alldocuments = $alldocs["documents"];
			$docs2 = "";
			foreach ($alldocuments as $catname => $documents)
			{
				$cat = $documents["category"];
				$catid = $cat ? $cat->id : 0;
				$tags = [];
				$vis = $catname == $alldocs["first_non_empty"] ? "" : " u3a-invisible";
				$attachment_ids = U3A_Object::extract_field_array($documents["documents"], "attachment_id");
				$attachment_urls = [];
				$titles = [];
				foreach ($documents["documents"] as $doc)
				{
					$attachment_urls[] = wp_get_attachment_url($doc->attachment_id);
					$titles[] = $doc->get_full_title();
				}
				$attachments = implode(",", $attachment_urls);
				$alltitles = addcslashes(implode("^", $titles), "'");
				if ($catid)
				{
					if ($documents["count"])
					{
						$ssbtn = new U3A_BUTTON("button", "slideshow", null, "u3a-wide-button u3a-slideshow-button",
						  "u3a_slideshow($id, '" . $cat->name . "', '" . $attachments . "', '" . $alltitles . "')");
						$tags[] = new U3A_H(4, $cat->name . $ssbtn->to_html());
					}
					else
					{
						$tags[] = new U3A_H(4, $cat->name);
					}
				}
				if ($documents["count"])
				{
//					write_log("images");
//					write_log($attachment_ids);
					$div = new U3A_DIV('[su_slider source="media: ' . implode(",", $attachment_ids) .
					  '" link="image" target="blank" width="540" height="360" centered="yes" arrows="yes" autoplay="0" responsive="no" title="yes"]',
					  "u3a-gallery-div-" . $id . "-" . $typ . "-" . $catid, "u3a-gallery-div");
					$tags[] = $div;
					$sl = new U3A_Slideshow();
					$sl->write_page($attachment_ids);
				}
				else
				{
					$tags[] = "There are no images in this album.";
				}
				$div = new U3A_DIV($tags, "u3a-document-div-$id-$typ-$catid", "u3a-document-div-class-" . $typ . $vis);
				$docs2 .= $div->to_html();
			}
			$docs = $docs1 . $docs2;
		}
		else
		{
			$h = new U3A_H(6, "no images to display");
			$docs .= $h->to_html();
		}
//		if (U3A_Information::u3a_has_permission($mbr, "manage images", $id) && !(U3A_Group_Members::is_coordinator($mbr, $grp) || U3A_Committee::is_webmanager($mbr)))
//		{
//			$docs .= '[u3a_upload_image group="' . $id . '"]';
//		}
	}
	return do_shortcode($docs);
}

add_action("delete_attachment", "u3a_attachment_deleted", 10, 1);

function u3a_attachment_deleted($attachment_id)
{
	U3A_Row::delete_rows("u3a_documents", ["attachment_id" => $attachment_id]);
}

add_shortcode("u3a_delete_document", "u3a_delete_document_contents");

function u3a_delete_document_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'		 => NULL,
		'category'	 => 0,
		'type'		 => 0
	  ), $atts1, 'u3a_delete_document');
	if ($atts["group"] == null)
	{
		$grp = 0;
	}
	else
	{
		$grp = intval($atts["group"]);
	}
	$type = $atts["type"];
	$type_name = U3A_Documents::get_type_name($type);
	$type_name1 = U3A_Documents::get_type_title_indefinite($type);
	$del = [];
	$alldocs = U3A_Documents::get_all_documents_for_group($grp, $type);
	if ($alldocs["total"])
	{
		$del[] = new U3A_H(4, "Delete " . $type_name1);
		foreach ($alldocs["documents"] as $catname => $catdocs)
		{
			$cat = $catdocs["category"];
			$catid = $cat ? $cat->id : 0;
			if ($catdocs["count"])
			{
				$docs = $catdocs["documents"];
				$opts = U3A_HTML_Utilities::get_options_array_from_object_array($docs, "title", "id");
				$selid = "u3a-delete-document-" . $grp . "-" . $type . "-" . $catid;
				$sel = new U3A_SELECT($opts, "u3a-" . $type_name . "-select", $selid, "u3a-" . $type_name . "-select-class");
				$lbl = [
					new U3A_SPAN("select " . $type_name . " to delete:", null, "u3a-block u3a-margin-right-5"),
					$sel,
					new U3A_BUTTON("button", "delete", "u3a-" . $type_name . "-select-button", "u3a-select-button-class u3a-button",
					  "u3a_delete_document('" . $selid . "', '" . $type_name . "')")
				];
			}
			else
			{
				$lbl = new U3A_SPAN("There are no " . $type_name . "s in this category.", null, "u3a-inline-block");
			}
			$div = new U3A_DIV($lbl, "u3a-delete-document-div-" . $grp . "-" . $type . "-" . $catid,
			  "u3a-delete-document-div-class-$type u3a-border-top");
			if ($catid != $atts["category"])
			{
				$div->add_class("u3a-invisible");
			}
			$del[] = $div;
		}
	}
	else
	{
		$del[] = new U3A_DIV("No " . $type_name . "s to delete", "u3a-delete-documents-div-" . $grp . "-" . $type,
		  "u3a-delete-document-div-class-$type u3a-border-top");
	}
	return U3A_HTML::to_html($del);
}

//add_shortcode("u3a_upload_image", "u3a_upload_image_contents");
//
//function u3a_upload_image_contents($atts1)
//{
//	$atts = shortcode_atts(array(
//		'group' => NULL
//	  ), $atts1, 'u3a_upload_image');
//	$gall = "";
//	if ($atts["group"] == null)
//	{
//		$grp = 0;
//	}
//	else
//	{
//		$grp = intval($atts["group"]);
//	}
//	$gall .= '<div class="u3a-upload-div-class">' . "\n";
//	$gall .= "<h4>Upload a new image</h4>";
//	$gall .= U3A_HTML::to_html(U3A_Documents::get_document_management($grp, U3A_Documents::GROUP_IMAGE_TYPE));
//	$gall .= '</div>';
//	return $gall;
//}

add_shortcode("u3a_document_link", "u3a_document_link_contents");

function u3a_document_link_contents($atts1)
{
	$atts = shortcode_atts(array(
		"title"		 => null,
		"text"		 => null,
		"target"		 => null,
		"id"			 => null,
		"cssclass"	 => null
	  ), $atts1, 'u3a_document_link');
	$ret = "";
	if ($atts["title"])
	{
		$doc = U3A_Row::get_single_value("U3A_Documents", "attachment_id", ["title" => $atts["title"]]);
		if ($doc)
		{
			$docurl = wp_get_attachment_url($doc);
			$text = $atts["text"] ? $atts["text"] : $atts["title"];
			$a = new U3A_A($docurl, $text, $atts["id"], $atts["cssclass"]);
			if ($atts["target"])
			{
				if (strtoupper($atts["target"]) === "_BLANK")
				{
					$a->add_attribute("data-popup", "true");
				}
				else
				{
					$a->add_attribute("target", $atts["target"]);
				}
			}
			$ret = $a->to_html();
		}
	}
	return $ret;
}

add_shortcode("u3a_text", "u3a_text_contents");

function u3a_text_contents($atts1)
{
	$atts = shortcode_atts(array(
		"name"			 => null,
		"para"			 => "no",
		"div"				 => "no",
		"header"			 => null,
		"header_size"	 => 2
	  ), $atts1, 'u3a_text');
	$txt = $atts["name"] ? U3A_Text::get_text($atts["name"]) : "";
	$para = strtolower($atts["para"]);
	if ($para !== "no")
	{
		$cssclass = null;
		if ($para !== "yes")
		{
			$cssclass = $atts["para"];
		}
		$p = new U3A_P($txt, null, $cssclass);
		$txt = $p->to_html();
	}
	$div = strtolower($atts["div"]);
	if ($div !== "no")
	{
		$cssclass = null;
		if ($div !== "yes")
		{
			$cssclass = $atts["div"];
		}
		$p = new U3A_DIV($txt, null, $cssclass);
		$txt = $p->to_html();
	}
	if ($atts["header"])
	{
		$hsize = $atts["header_size"];
		$txt = "<h$hsize>" . $atts["header"] . "</$hsize>" . $txt;
	}
	return do_shortcode($txt);
}

add_shortcode("u3a_coordinators", "u3a_coordinators_contents");

function u3a_coordinators_contents($atts1)
{
	$atts = shortcode_atts(array(
		"email" => "no"
	  ), $atts1, 'u3a_coordinators');
	$pgcontent = "";
	$mbr = U3A_Information::u3a_logged_in_user();
	if (U3A_Committee::is_committee_member($mbr) || U3A_Group_Members::is_a_coordinator($mbr))
	{
		$pgcontent .= '[su_tabs style="wood"]';
		$docs = '[u3a_document_list group="0" type="' . U3A_Documents::COORDINATORS_DOCUMENT_TYPE . '"]';
		$pgcontent .= '[su_tab title="Documents" disabled="no" anchor="" url="" target="blank" class=""]' . $docs . "[/su_tab]\n";
		if (U3A_Committee::is_committee_member($mbr))
		{
			$mng = "[u3a_manage_cooordinator_documents]";
			$pgcontent .= '[su_tab title="Manage" disabled="no" anchor="" url="" target="blank" class=""]' . U3A_HTML::to_html($mng) . "[/su_tab]\n";
		}
		$coorddiv = new U3A_DIV('[u3a_coordinators_list checked="yes"]', "u3a-coordinators-div-0",
		  "u3a-coordinators-list-class u3a-inline-block u3a-padding-right-5 u3a-width-30-pc u3a-va-top");
		$emaildiv1 = U3A_HTML_Utilities::get_mail_contents_div($mbr->id, "coordinators", 0,
			 "u3a-inline-block u3a-width-70-pc u3a-height-100-pc u3a-va-top", 0, 0);
		$pgcontent .= '[su_tab title="Email" disabled="no" anchor="" url="" target="blank" class=""]' . U3A_HTML::to_html([$coorddiv, $emaildiv1]);
		if (U3A_Committee::is_webmanager($mbr))
		{
			$coords = U3A_Groups::get_all_coordinators_details();
			foreach ($coords as $c)
			{
				if ($c["membership_number"] < 100000)
				{
					$gmdiv = new U3A_DIV($c["email"], null, "u3a-group-email-class u3a-invisible");
					$pgcontent .= $gmdiv->to_html();
				}
			}
			$dlbtn = new U3A_BUTTON("button", "download as csv", "u3a-group-emails-download", "u3a-wide-button",
			  "u3a_download_coordinator_emails()");
			$pgcontent .= $dlbtn->to_html();
		}
		$pgcontent .= "[/su_tab]";
		$pgcontent .= "[/su_tabs]";
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_coordinators_list", "u3a_coordinators_list_contents");

function u3a_coordinators_list_contents($atts1)
{
	$atts = shortcode_atts(array(
		"checked" => "no"
	  ), $atts1, 'u3a_coordinators_list');
	$contents1 = [];
	$mbr = U3A_Information::u3a_logged_in_user();
	if (U3A_Committee::is_committee_member($mbr) || U3A_Group_Members::is_a_coordinator($mbr))
	{
		$seeall = U3A_Committee::is_webmanager($mbr) || U3A_Members::is_system($mbr);
		$checked = $atts["checked"] === "yes";
		$coords = U3A_Groups::get_all_coordinators_details();
		foreach ($coords as $mbr)
		{
			if ($seeall || $mbr["membership_number"] < 100000)
			{
				if ($checked)
				{
					$cbid = "u3a-member-checkbox-" . $mbr["id"];
					$cb = new U3A_INPUT("checkbox", null, $cbid, "u3a-member-checkbox-class", $mbr["id"]);
					$cb->add_attribute("onchange", "u3a_member_checkbox_changed('" . $cbid . "', 'coordinators')");
					$cblbl = new U3A_LABEL($cbid, $mbr["group_name"] . ", ", null,
					  "u3a-member-checkbox-label-class u3a-inline-block u3a-margin-left-5");
					$p = new U3A_DIV([$cb, $cblbl], null, "u3a-member-class");
				}
				else
				{
					$p = new U3A_DIV($mbr["group_name"], null, "u3a-member-class");
				}
				$p->add_attribute("title", $mbr["forename"] . " " . $mbr["surname"]);
//				$contents1[] = new U3A_DIV([$p, $group_input, $groupname_input], null, null);
				$contents1[] = $p;
			}
		}
		if ($checked)
		{
			$cbid = "u3a-member-checkbox-all";
			$cb = new U3A_INPUT("checkbox", null, $cbid, "u3a-member-checkbox-class", 0);
			$cb->add_attribute("onchange", "u3a_member_checkbox_changed('" . $cbid . "', 'coordinators')");
			$cblbl = new U3A_LABEL($cbid, "all", null,
			  "u3a-member-checkbox-label-class u3a-inline-block u3a-margin-left-5 u3a-margin-bottom-10");
			$p = new U3A_DIV([$cb, $cblbl], null, "u3a-member-class");
			array_unshift($contents1, $p);
		}
		$group_input = new U3A_INPUT("hidden", "u3a-member-select-group", "u3a-member-select-group", null, 0);
		$groupname_input = new U3A_INPUT("hidden", "u3a-member-select-groupname", "u3a-member-select-groupname", null,
		  "committee");
	}
	$contents = new U3A_DIV([$contents1, $group_input, $groupname_input], null, "u3a-40vh-auto-y");
	return $contents->to_html();
}

add_shortcode("u3a_committee", "u3a_committee_contents");

function u3a_committee_contents($atts1)
{
	$atts = shortcode_atts(array(
		"checked" => "no"
	  ), $atts1, 'u3a_committee');
//	$cfg = U3A_CONFIG::get_the_config();
//	write_log($cfg);
	$checked = $atts["checked"] === "yes";
	$cmte = U3A_Committee::get_all_members();
	$contents1 = [];
	foreach ($cmte as $cmbr)
	{
		$mbr = U3A_Members::get_member($cmbr["members_id"]);
		if ($checked)
		{
			$cbid = "u3a-member-checkbox-" . $cmbr["members_id"];
			$cb = new U3A_INPUT("checkbox", null, $cbid, "u3a-member-checkbox-class", $cmbr["members_id"]);
			$cb->add_attribute("onchange", "u3a_member_checkbox_changed('" . $cbid . "', 'committee')");
			$cblbl = new U3A_LABEL($cbid, $cmbr["role"], null,
			  "u3a-member-checkbox-label-class u3a-inline-block u3a-margin-left-5");
			$p = new U3A_DIV([$cb, $cblbl], null, "u3a-member-class");
		}
		else
		{
			$p = new U3A_DIV($cmbr["role"], null, "u3a-committee-member-class");
		}
		$p->add_attribute("title", $mbr->get_name());
//				$contents1[] = new U3A_DIV([$p, $group_input, $groupname_input], null, null);
		$contents1[] = $p;
	}
	if ($checked)
	{
		$cbid = "u3a-member-checkbox-all";
		$cb = new U3A_INPUT("checkbox", null, $cbid, "u3a-member-checkbox-class", 0);
		$cb->add_attribute("onchange", "u3a_member_checkbox_changed('" . $cbid . "', 'committee')");
		$cblbl = new U3A_LABEL($cbid, "all", null,
		  "u3a-member-checkbox-label-class u3a-inline-block u3a-margin-left-5 u3a-margin-bottom-10");
		$p = new U3A_DIV([$cb, $cblbl], null, "u3a-member-class");
		array_unshift($contents1, $p);
	}
	$group_input = new U3A_INPUT("hidden", "u3a-member-select-group", "u3a-member-select-group", null, 0);
	$groupname_input = new U3A_INPUT("hidden", "u3a-member-select-groupname", "u3a-member-select-groupname", null,
	  "committee");
	$contents = new U3A_DIV([$contents1, $group_input, $groupname_input], null, "u3a-40vh-auto-y");
	return $contents->to_html();
}

add_shortcode("u3a_committee_members", "u3a_committee_members_contents");

function u3a_committee_members_contents($atts1)
{
	$atts = shortcode_atts(array(
		"email"	 => "no",
		"spoiler" => "Manage Links"
	  ), $atts1, 'u3a_committee_members');
	$cmte = U3A_Committee::get_all_members();
	$active = 1;
	$tabnames = ["members", "gallery", "documents", "manage", "email"];
	if (isset($_GET["tab"]))
	{
		$act = array_search($_GET["tab"], $tabnames);
		if ($act !== FALSE)
		{
			$active = $act + 1;
		}
	}
	$category = "";
	if (isset($_GET["category"]))
	{
		$category = ' category="' . $_GET["category"] . '"';
	}
//	write_log($_GET);
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontent = "";
	if ($mbr)
	{
		$iscom = U3A_Committee::list_roles($mbr);
//		write_log("iscom", $iscom);
		if (!$iscom)
		{
			$wm = U3A_Committee::get_webmanager($mbr);
			if ($wm && ($wm->members_id == $mbr->id))
			{
				$iscom = [$wm];
			}
		}
		$ndocs = 0;
		$nimgs = 0;
		$addemail = /* U3A_Committee::is_committee_member($mbr) && */$atts["email"] === "yes";
		$headers = [
			new U3A_TH("Role"),
			new U3A_TH("Name")
		];
		if ($addemail)
		{
			array_push($headers, new U3A_TH("Email"));
		}
		$rows = [];
		$thead = new U3A_THEAD(new U3A_TR($headers));
		foreach ($cmte as $cmbr)
		{
			if (count($iscom) > 1)
			{
				$isme = U3A_Row::is_one_of($cmbr["id"], $iscom);
				if ($isme >= 0)
				{
					$cb = new U3A_INPUT("radio", "checkbox-preferred-role", "u3a-checkbox-$isme");
					if ($isme === 0)
					{
						$cb->add_attribute("checked", "checked");
					}
					$cb->add_attribute("onchange", "preferred_role_changed(" . $mbr->id . ", " . $cmbr["id"] . ")");
					$lbl = new U3A_LABEL("u3a-checkbox-$isme", $cmbr["role"], "u3a-checkbox-label-$isme");
					$cell = [$cb, $lbl];
				}
				else
				{
					$cell = $cmbr["role"];
				}
			}
			else
			{
				$cell = $cmbr["role"];
			}
			$td = [
				new U3A_TD($cell),
				new U3A_TD($cmbr["forename"] . " " . $cmbr["surname"])
			];
			if ($addemail)
			{
				$ml = new U3A_A("#u3a-send-mail-individual-0", "contact", null, "u3a-committee-mail-class",
				  "u3a_mail_clicked('" . $cmbr["email"] . "')");
				$ml->add_attribute("rel", "modal:open");
//			$ml = new U3A_A("mailto:" . $cmbr["email"], "contact", null, "u3a-committee-mail-class");
				array_push($td, new U3A_TD($ml));
			}
//			if ($isme >= 0)
//			{
//				foreach ($td as $td1)
//				{
//					$td1->add_class("u3a-bold");
//				}
//			}
			$rows[] = new U3A_TR($td);
		}
		$tbl = new U3A_TABLE([$thead, new U3A_TBODY($rows)]);
		$ctee_table = '[su_table responsive="yes" alternate="yes"]' . U3A_HTML::to_html($tbl) . "[/su_table]";
//		write_log("iscom2", $iscom);
		if ($iscom)
		{
			$dochead1 = new U3A_H(6, "Private Documents");
			$dochead2 = new U3A_H(6, "Public Documents");
			$hr = new U3A_HR();
			$docs = $dochead1->to_html();
			$docs .= '[u3a_document_list group="0" type="' . U3A_Documents::PRIVATE_DOCUMENT_TYPE . '"' . $category . ']';
			$docs .= $hr->to_html();
			$docs .= $dochead2->to_html();
			$docs .= '[u3a_document_list group="0" type="' . U3A_Documents::PUBLIC_DOCUMENT_TYPE . '"' . $category . ']';
		}
		else
		{
			$docs = '[u3a_document_list group="0" type="' . U3A_Documents::PUBLIC_DOCUMENT_TYPE . '"' . $category . ']';
		}
		$gall = '[u3a_image_list group="0" type="' . U3A_Documents::COMMITTEE_IMAGE_TYPE . '"]';
		$pgcontent .= '[su_tabs style="wood" active="' . $active . '"]\n';
		$pgcontent .= '[su_tab title="Members" anchor="members" disabled="no" anchor="" url="" target="blank" class=""]' . $ctee_table . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Gallery" anchor="gallery" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $gall . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Documents" anchor="documents" disabled="no" anchor="" url="" target="blank" class=""]' . $docs . "\n[/su_tab]\n";
		if ($iscom)
		{
			$mng = [];
			$mng[] = new U3A_DIV('[su_permalink id="' . U3A_Information::u3a_manage_committee_documents_page() . '"]',
			  "u3a-manage-committee-documents-link-div", "u3a-link-div-class");
			$mng[] = new U3A_DIV('[su_permalink id="' . U3A_Information::u3a_manage_committee_permissions_page() . '"]',
			  "u3a-manage-committee-permissions-link-div", "u3a-link-div-class");
			if (U3A_Information::u3a_management_enabled())
			{
				$mng[] = new U3A_DIV('[su_permalink id="' . U3A_Information::u3a_manage_groups_page() . '"]',
				  "u3a-manage-groups-link-div", "u3a-link-div-class");
				$mng[] = new U3A_DIV('[su_permalink id="' . U3A_Information::u3a_manage_members_page() . '"]',
				  "u3a-manage-members-link-div", "u3a-link-div-class");
				$mng[] = new U3A_DIV('[su_permalink id="' . U3A_Information::u3a_manage_venues_page() . '"]',
				  "u3a-manage-venues-link-div", "u3a-link-div-class");
			}
			else
			{
				$mng[] = U3A_Information::not_available("other management functions");
			}
			$mng[] = new U3A_DIV('[su_permalink id="' . U3A_Information::u3a_manage_links_page() . '"]',
			  "u3a-manage-links-link-div", "u3a-link-div-class");
			$mngst = U3A_HTML::to_html($mng);
//			write_log("u3a_committee_members_contents manage", $mngst);
//			$mngst .= "[su_accordion]\n";
//			$mngst .= U3A_Information::get_manage_open_spoiler("Manage Links", /* $atts["spoiler"] */ "");
//			$div = new U3A_DIV('[u3a_manage_links member="0" group="0"]', null, "u3a-manage-links-div");
//			$mngst .= $div->to_html();
//			$mngst .= "[/su_spoiler]\n";
//			$mngst .= "[/su_accordion]\n";
			$pgcontent .= '[su_tab title="Manage" anchor="manage" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $mngst . "\n[/su_tab]\n";
			$mbrdiv = new U3A_DIV('[u3a_committee checked="yes"]', "u3a-committee-members-div-0",
			  "u3a-committee-member-list-class u3a-inline-block u3a-padding-right-5 u3a-width-30-pc u3a-va-top");
			$ndocs = U3A_Row::count_rows("U3A_Documents",
				 ["groups_id" => 0, "document_type" => U3A_Documents::PRIVATE_DOCUMENT_TYPE]);
			$nimgs = U3A_Row::count_rows("U3A_Documents",
				 ["groups_id" => 0, "document_type" => U3A_Documents::COMMITTEE_IMAGE_TYPE]);
			$emaildiv = U3A_HTML_Utilities::get_mail_contents_div($mbr->id . "+" . $iscom[0]->id, "committee", 0,
				 "u3a-inline-block u3a-width-70-pc u3a-height-100-pc u3a-va-top", $ndocs, $nimgs);
			$mailer = "[su_accordion]\n";
			$mailer .= '[su_spoiler title="email members of committee" style="fabric" icon="arrow-circle-1"]';
			$mailer .= U3A_HTML::to_html([$mbrdiv, $emaildiv]);
			$mailer .= "[/su_spoiler]\n";
			if (U3A_Committee::is_webmanager($mbr))
			{
				$mailer .= '[su_spoiler title="list official emails" style="fabric" icon="arrow-circle-1"]';
				$mailer1 = '[su_spoiler title="list private emails" style="fabric" icon="arrow-circle-1"]';
				$cts = U3A_Row::load_array_of_objects("U3A_Committee", null);
				foreach ($cts["result"] as $ct)
				{
					$gmdiv = new U3A_DIV(U3A_Utilities::strip_all_slashes($ct->email), null, "u3a-group-email-class");
					$mailer .= $gmdiv->to_html();
					$gmdiv1 = new U3A_DIV(U3A_Members::get_email_address($ct->members_id), null, "u3a-group-email-class");
					$mailer1 .= $gmdiv1->to_html();
				}
				$mailer .= "[/su_spoiler]\n";
				$mailer1 .= "[/su_spoiler]\n";
				$mailer .= $mailer1;
			}
			$mailer .= "[/su_accordion]\n";
//		$mdiv = new U3A_DIV($mailer, null, "u3a-inline-block u3a-width-70-pc u3a-height-100-pc u3a-va-top");
//		$mbrs = U3A_HTML::to_html([$mbrdiv, $mdiv]);
			$pgcontent .= '[su_tab title="Email" anchor="email" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $mailer . "\n[/su_tab]\n";
		}
		$pgcontent .= "[/su_tabs]\n";
		$maildiv = U3A_HTML_Utilities::get_mail_contents_div($mbr->id, "individual", 0, "modal u3a-mail-dialog", $ndocs,
			 $nimgs, "", "modal:close");
		$pgcontent .= $maildiv->to_html();
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_manage_groups", "u3a_manage_groups_contents");

function u3a_manage_groups_contents($atts1)
{
	$atts = shortcode_atts(array(
		"role" => null
	  ), $atts1, 'u3a_manage_groups');
	$ret = "";
	$mbr = U3A_Information::u3a_logged_in_user();
	if ($mbr && U3A_Committee::is_committee_member($mbr))
	{
		$mng = "[su_accordion]\n";
		$mng .= '[su_spoiler title="Add New Group" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_add_new_group]';
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Edit Group" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_edit_group]';
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Delete Group" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_delete_group]';
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Add Member to Group" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_add_member_to_group group="0"]';
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Remove Member from Group" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_remove_member_from_group group="0"]';
		$mng .= "[/su_spoiler]\n";
		$mng .= "[/su_accordion]\n";
		$ret = do_shortcode($mng);
	}
	return $ret;
}

add_shortcode("u3a_manage_members", "u3a_manage_members_contents");

function u3a_manage_members_contents($atts1)
{
	$atts = shortcode_atts(array(
		"role" => null
	  ), $atts1, 'u3a_manage_members');
	$ret = "";
	$mbr = U3A_Information::u3a_logged_in_user();
//	write_log("\n\nu3a_manage_members_contents", $mbr);
	$cmbr = U3A_Committee::is_committee_member($mbr);
//	write_log("\n\nu3a_manage_members_contents", $cmbr);
	if ($mbr && $cmbr)
	{
//		write_log("in here");
		$mng = 'There are [u3a_number_of_members] current members: [u3a_number_of_members paid="yes" associate="no"] full members paid, [u3a_number_of_members paid="yes" associate="yes"] associate members paid and [u3a_number_of_members paid="no"] not yet renewed.';
//		write_log($mng);
		$mng .= "[su_accordion]\n";
		$mng .= '[su_spoiler title="Add New Member" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_add_new_member]';
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Edit Member" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_edit_member]';
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Change Member Status" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_change_member_status]';
		$mng .= "[/su_spoiler]\n";
		if (U3A_Committee::is_membership_secretary($mbr) || U3A_Committee::is_treasurer($mbr) || U3A_Committee::is_webmanager($mbr))
		{
			$mng .= '[su_spoiler title="Renew Member" style="fabric" icon="arrow-circle-1"]';
			$mng .= '[u3a_renew_member]';
			$mng .= "[/su_spoiler]\n";
		}
		$mng .= '[su_spoiler title="Delete Member" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_delete_member]';
		$mng .= "[/su_spoiler]\n";
		if (U3A_Committee::is_membership_secretary($mbr) || U3A_Committee::is_webmanager($mbr))
		{
			$mng .= '[su_spoiler title="TAM Address List" style="fabric" icon="arrow-circle-1"]';
			$json = "TAM=1";
			$alistbtn = new U3A_BUTTON("button", "download address list", null, "u3a-wide-button",
			  "u3a_download_address_list('$json')");
			$mng .= $alistbtn->to_html();
			$mng .= "[/su_spoiler]\n";
		}
		if (U3A_Committee::is_treasurer($mbr) || U3A_Committee::is_webmanager($mbr))
		{
			$mng .= '[su_spoiler title="Gift Aid List" style="fabric" icon="arrow-circle-1"]';
			$to = new DateTime();
			$now = new DateTime();
			$from = $to->sub(new DateInterval('P1Y'));
			$frominp = new U3A_INPUT("date", "gift-aid-from", "u3a-gift-aid-from", null, $from->format('Y-m-d'));
			$toinp = new U3A_INPUT("date", "gift-aid-to", "u3a-gift-aid-to", null, $now->format('Y-m-d'));
			$fromlbl = U3A_HTML::labelled_html_object("from", $frominp, "u3a-gift-aid-from-label", "u3a-gift-aid-label", false,
				 true, "start date for list of subscription payments", false);
			$tolbl = U3A_HTML::labelled_html_object("to", $toinp, "u3a-gift-aid-to-label", "u3a-gift-aid-label", false, true,
				 "end date for list of subscription payments", false);
			$galistbtn = new U3A_BUTTON("button", "download gift aid", null, "u3a-wide-button", "u3a_download_gift_aid()");
			$mng .= $fromlbl->to_html();
			$mng .= $tolbl->to_html();
			$mng .= $galistbtn->to_html();
			$mng .= "[/su_spoiler]\n";
		}
		$mng .= '[su_spoiler title="Lapsed members" style="fabric" icon="arrow-circle-1"]';
		$lmbrs = U3A_Members::get_all_members(["status" => 'Lapsed']);
		if ($lmbrs)
		{
			$divs = [];
			foreach ($lmbrs as $lmbr)
			{
				$btn1 = new U3A_BUTTON("button", "delete", "u3a-delete-lapsed-member-" . $lmbr->id,
				  "u3a-button u3a-float-right u3a-margin-left-2", "u3a_delete_lapsed(" . $lmbr->id . ")");
				$btn2 = new U3A_BUTTON("button", "unlapse", "u3a-unlapse-lapsed-member-" . $lmbr->id, "u3a-button u3a-float-right",
				  "u3a_unlapse_member(" . $lmbr->id . ")");
				$div = new U3A_DIV([$lmbr->formal_name, $btn1, $btn2], "u3a-lapsed-member-div-" . $lmbr->id,
				  "u3a-lapsed-member-div-class u3a-width-100-pc u3a-clear-right");
				$divs[] = $div;
			}
			$div = new U3A_DIV($divs, "u3a-lapsed-members-div",
			  "u3a-lapsed-members-div-class u3a-height-10-em u3a-width-100-pc u3a-overflow-y-auto");
			$mng .= $div->to_html();
		}
		else
		{
			$div = new U3A_DIV("there are no lapsed members", "u3a-lapsed-members-div", "u3a-lapsed-members-div-class");
			$mng .= $div->to_html();
		}
		$mng .= "[/su_spoiler]\n";
		$dt = new DateTime();
		$dtf0 = $dt->format('Y-m-d');
		$dt->add(new DateInterval('P7D'));
		$dtf1 = $dt->format('Y-m-d');
		$dt->add(new DateInterval('P1Y'));
		$dtf2 = $dt->format('Y-m-d');
		$addexpires = new U3A_INPUT("date", "news-expires", "add-news-expires", null, $dtf1);
		$addexpires->add_attribute("min", $dtf0);
		$addexpires->add_attribute("max", $dtf2);
		$mng .= "[/su_accordion]\n";
		$ret = do_shortcode($mng);
	}
	return $ret;
}

add_shortcode("u3a_add_new_member", "u3a_add_new_member_contents");

function u3a_add_new_member_contents($atts1)
{
//	write_log("u3a_add_new_member_contents");
	return /* U3A_Information::not_implemented("fully") . */do_shortcode("[u3a_member_details_form]");
}

add_shortcode("u3a_edit_member", "u3a_edit_member_contents");

function u3a_edit_member_contents($atts1)
{
	$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="edit_details" close="tick" op="edit" byname="yes" suffix="1"]');
	$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="edit_details" close="tick" op="edit" byname="no" suffix="2"]');
	$lbl1 = new U3A_LABEL("u3a-find-member-search-a-edit-details-edit1", "search for a member by name", null,
	  "u3a-search-label");
	$lbl1->add_attribute("role", "button");
	$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-edit1", "u3a-margin-bottom-5");
	$mbrdiv1 = new U3A_DIV("", "u3a-member-edit-details1", "u3a-member-edit-class");
	$lbl2 = new U3A_LABEL("u3a-find-member-search-a-edit-details-edit2", "search for a member by number", null,
	  "u3a-search-label");
	$lbl2->add_attribute("role", "button");
	$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-select-member-text-div-edit2", "u3a-margin-bottom-5");
	$mbrdiv2 = new U3A_DIV("", "u3a-member-edit-details2", "u3a-member-edit-class");
	return U3A_HTML::to_html([$mbrsearchdiv1, $mbrdiv1, $mbrsearchdiv2, $mbrdiv2]);
//	$mbrsearch = do_shortcode('[u3a_find_member_dialog group="0" next_action="edit_details" close="tick" op="edit"]');
//	$lbl = new U3A_SPAN("select member", "u3a-select-member-text-edit", "u3a-inline-block");
//	$mbrdiv = new U3A_DIV("", "u3a-member-details-edit", "u3a-member-details-class");
//	return /* U3A_Information::not_implemented("fully") . */$lbl->to_html() . $mbrsearch . $mbrdiv->to_html();
}

add_shortcode("u3a_renew_member", "u3a_renew_member_contents");

function u3a_renew_member_contents($atts1)
{
	$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="renew_member" close="tick" op="renew" byname="yes" suffix="1"]');
	$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="renew_member" close="tick" op="renew" byname="no" suffix="2"]');
	$lbl1 = new U3A_LABEL("u3a-find-member-search-a-renew-member-status1", "search for a member by name", null,
	  "u3a-search-label");
	$lbl1->add_attribute("role", "button");
	$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-status1", "u3a-margin-bottom-5");
	$mbrdiv1 = new U3A_DIV("", "u3a-member-renew-member1", "u3a-member-renew-class");
	$lbl2 = new U3A_LABEL("u3a-find-member-search-a-renew-member-status2", "search for a member by number", null,
	  "u3a-search-label");
	$lbl2->add_attribute("role", "button");
	$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-renew-member-text-div-status2", "u3a-margin-bottom-5");
	$mbrdiv2 = new U3A_DIV("", "u3a-member-renew-member2", "u3a-member-status-class");
	return U3A_HTML::to_html([$mbrsearchdiv1, $mbrdiv1, $mbrsearchdiv2, $mbrdiv2]);
}

add_shortcode("u3a_change_member_status", "u3a_change_member_status_contents");

function u3a_change_member_status_contents($atts1)
{
	$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="change_status" close="tick" op="status" byname="yes" suffix="1"]');
	$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="change_status" close="tick" op="status" byname="no" suffix="2"]');
	$lbl1 = new U3A_LABEL("u3a-find-member-search-a-change-status-status1", "search for a member by name", null,
	  "u3a-search-label");
	$lbl1->add_attribute("role", "button");
	$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-status1", "u3a-margin-bottom-5");
	$mbrdiv1 = new U3A_DIV("", "u3a-member-change-status1", "u3a-member-status-class");
	$lbl2 = new U3A_LABEL("u3a-find-member-search-a-change-status-status2", "search for a member by number", null,
	  "u3a-search-label");
	$lbl2->add_attribute("role", "button");
	$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-change-status-text-div-status2", "u3a-margin-bottom-5");
	$mbrdiv2 = new U3A_DIV("", "u3a-member-change-status2", "u3a-member-status-class");
	return U3A_HTML::to_html([$mbrsearchdiv1, $mbrdiv1, $mbrsearchdiv2, $mbrdiv2]);
}

add_shortcode("u3a_view_member", "u3a_view_member_contents");

function u3a_view_member_contents($atts1)
{
	$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="view_details" close="tick" op="view" byname="yes" suffix="1"]');
	$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="view_details" close="tick" op="view" byname="no" suffix="2"]');
//	$span = new U3A_SPAN("search for a member", "u3a-select-member-text-view", "u3a-inline-block");
	$lbl1 = new U3A_LABEL("u3a-find-member-search-a-view-details-view1", "search for a member by name", null,
	  "u3a-search-label");
	$lbl1->add_attribute("role", "button");
	$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-view1", "u3a-margin-bottom-5");
	$mbrdiv1 = new U3A_DIV("", "u3a-member-details-view1", "u3a-member-details-class");
	$lbl2 = new U3A_LABEL("u3a-find-member-search-a-view-details-view2", "search for a member by number", null,
	  "u3a-search-label");
	$lbl2->add_attribute("role", "button");
	$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-select-member-text-div-view2", "u3a-margin-bottom-5");
	$mbrdiv2 = new U3A_DIV("", "u3a-member-details-view2", "u3a-member-details-class");
//	return /* U3A_Information::not_implemented("fully") . */$lbl->to_html() . $mbrsearch . $mbrdiv->to_html();
	return U3A_HTML::to_html([$mbrsearchdiv1, $mbrdiv1, $mbrsearchdiv2, $mbrdiv2]);
}

add_shortcode("u3a_goto_member", "u3a_goto_member_contents");

function u3a_goto_member_contents($atts1)
{
	$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="goto_member" close="tick" op="goto" byname="yes" suffix="1"]');
	$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="goto_member" close="tick" op="goto" byname="no" suffix="2"]');
//	$span = new U3A_SPAN("search for a member", "u3a-select-member-text-goto", "u3a-inline-block");
	$lbl1 = new U3A_LABEL("u3a-find-member-search-a-goto-details-goto1", "search for a member by name", null,
	  "u3a-search-label");
	$lbl1->add_attribute("role", "button");
	$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-goto1", "u3a-margin-bottom-5");
	$mbrdiv1 = new U3A_DIV("", "u3a-member-details-goto1", "u3a-member-details-class");
	$lbl2 = new U3A_LABEL("u3a-find-member-search-a-goto-details-goto2", "search for a member by number", null,
	  "u3a-search-label");
	$lbl2->add_attribute("role", "button");
	$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-select-member-text-div-goto2", "u3a-margin-bottom-5");
	$mbrdiv2 = new U3A_DIV("", "u3a-member-details-goto2", "u3a-member-details-class");
//	return /* U3A_Information::not_implemented("fully") . */$lbl->to_html() . $mbrsearch . $mbrdiv->to_html();
	return U3A_HTML::to_html([$mbrsearchdiv1, $mbrdiv1, $mbrsearchdiv2, $mbrdiv2]);
}

add_shortcode("u3a_delete_member", "u3a_delete_member_contents");

function u3a_delete_member_contents($atts1)
{
	$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="delete" close="tick" op="delete" byname="yes" suffix="1"]');
	$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="delete" close="tick" op="delete" byname="no" suffix="2"]');
	$lbl1 = new U3A_LABEL("u3a-find-member-search-a-delete-delete1", "search for a member by name", null,
	  "u3a-search-label");
	$lbl1->add_attribute("role", "button");
	$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-delete1", "u3a-margin-bottom-5");
	$mbrdiv1 = new U3A_DIV("", "u3a-member-delete1", "u3a-member-status-class");
	$lbl2 = new U3A_LABEL("u3a-find-member-search-a-delete-delete2", "search for a member by number", null,
	  "u3a-search-label");
	$lbl2->add_attribute("role", "button");
	$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-select-member-text-div-delete2", "u3a-margin-bottom-5");
	$mbrdiv2 = new U3A_DIV("", "u3a-member-delete2", "u3a-member-status-class");
	return U3A_HTML::to_html([$mbrsearchdiv1, $mbrdiv1, $mbrsearchdiv2, $mbrdiv2]);
//	$mbrsearch = do_shortcode('[u3a_find_member_dialog group="0" next_action="delete" close="tick" op="delete"]');
//	$lbl = new U3A_SPAN("select member", "u3a-select-member-text-delete", "u3a-inline-block");
//	return /* U3A_Information::not_implemented("fully") . */$lbl->to_html() . $mbrsearch;
}

add_shortcode("u3a_member_details_form", "u3a_member_details_form_contents");

function u3a_get_member_details_form_text_field($op, $name, $label, $idsuffix, $value, $type = "text")
{
	if ($op === "mail")
	{
		$ret = new U3A_DIV($label . ": " . $value, null, null);
//		$ret1->add_attribute("disabled", "disabled");
	}
	else
	{
		$ret1 = new U3A_INPUT($type, $name, "u3a-" . $name . $idsuffix, "u3a-name-input-class", $value);
		if (U3A_Information::u3a_application_form_is_required($name, $op))
		{
			$labelb = new U3A_B($label);
			$label = $labelb->to_html() . "<sup>*</sup>";
		}
		$ret = U3A_HTML::labelled_html_object($label . ":", $ret1, "$name-label" . $idsuffix, "u3a-label-class", false, true,
			 null, true);
	}
	return $ret;
}

function u3a_member_details_form_contents($atts1)
{
//	write_log("u3a_member_details_form_contents");
//	write_log($atts1);
	$atts = shortcode_atts(array(
		"member"	 => 0,
		"op"		 => "add",
		"button"	 => "yes",
		"groups"	 => "no",
		"suffix"	 => ""
	  ), $atts1, 'u3a_member_details_form');
//	write_log($atts);
	$op = $atts["op"];
	$idsuffix = "-" . $atts["op"] . $atts["suffix"];
	$fname_value = null;
	$sname_value = null;
	$nname_value = null;
	$mnum_value = null;
	$gender_value = "N";
	$email_value = null;
	$tel_value = null;
	$mob_value = null;
	$payment_value = null;
	$house_value = null;
	$address1_value = null;
	$address2_value = null;
	$address3_value = null;
	$county_value = "Shropshire";
	$town_value = "Shrewsbury";
	$postcode_value = null;
	$emergency_value = null;
	$giftaid_value = null;
	$title_value = null;
	$tam_value = null;
	$newsletter_value = 0;
	$affiliation_value = null;
	$mbr = 0;
	if ($atts["member"])
	{
		$mbr = U3A_Members::get_member($atts["member"]);
		$title_value = $mbr->title;
		$fname_value = $mbr->forename;
		$sname_value = $mbr->surname;
		$nname_value = $mbr->known_as;
		$mnum_value = $mbr->membership_number;
		if ($mbr->gender)
		{
			$gender_value = $mbr->gender;
		}
		$email_value = U3A_Utilities::strip_all_slashes($mbr->email);
		$tel_value = $mbr->telephone;
		$mob_value = $mbr->mobile;
		$payment_value = $mbr->payment_type;
		$house_value = $mbr->house;
		$address1_value = $mbr->address1;
		$address2_value = $mbr->address2;
		$address3_value = $mbr->address3;
		$county_value = $mbr->county;
		$town_value = $mbr->town;
		$postcode_value = $mbr->postcode;
		$emergency_value = $mbr->emergency_contact;
		$giftaid_value = $mbr->gift_aid;
		$tam_value = intval($mbr->TAM);
		$newsletter_value = intval($mbr->newsletter);
		$affiliation_value = $mbr->affiliation;
	}
	$mbrhdr = new U3A_H(6, "Member Details");
	if (($atts["op"] === "add") || ($atts["op"] === "join") || ($atts["op"] === "selfedit"))
	{
		if ($atts["op"] === "selfedit")
		{
			$memnum = u3a_get_member_details_form_text_field($op, "member-membership_number", "number", $idsuffix, $mnum_value);
			$memnum->add_class("u3a-invisible");
		}
		else
		{
			$memnum = null;
		}
		$reqtxt = "Required fields are marked <b>thus</b><sup>*</sup>.";
	}
	else
	{
		$reqtxt = null;
		$memnum = u3a_get_member_details_form_text_field($op, "member-membership_number", "number", $idsuffix, $mnum_value);
//		$memnum1 = new U3A_INPUT("text", "membership_number", "u3a-member-membership_number" . $idsuffix, "u3a-number-input-class", $mnum_value);
//		$memnum = U3A_HTML::labelled_html_object("<b>number:</b>", $memnum1, "u3a-member-membership-number-label" . $idsuffix, "u3a-membership-number-label-class u3a-label-class", false, true, null, true);
	}
	if ($op === "mail")
	{
		$title = new U3A_DIV("title: " . $title_value);
//		$hsel->add_attribute("disabled", "disabled");
	}
	else
	{
		$hsel = U3A_HTML_Utilities::get_honorific_select("u3a-member-title" . $idsuffix, "u3a-title-class", $title_value);
		$title = U3A_HTML::labelled_html_object("title:", $hsel, "u3a-title-label" . $idsuffix, "u3a-title-label-class",
			 false, true, null, true);
	}
//	$fname1 = new U3A_INPUT("text", "member-forename", "u3a-member-forename" . $idsuffix, "u3a-name-input-class", $fname_value);
//	$fname = U3A_HTML::labelled_html_object("<b>forename:</b>", $fname1, "u3a-member-forename-label" . $idsuffix, "u3a-forename-label-class u3a-label-class", false, true, null, true);
	$fname = u3a_get_member_details_form_text_field($op, "member-forename", "forename", $idsuffix, $fname_value);
//	$sname1 = new U3A_INPUT("text", "member-surname", "u3a-member-surname" . $idsuffix, "u3a-name-input-class", $sname_value);
//	$sname = U3A_HTML::labelled_html_object("<b>surname:</b>", $sname1, "u3a-member-surname-label" . $idsuffix, "u3a-surname-label-class u3a-label-class", false, true, null, true);
	$sname = u3a_get_member_details_form_text_field($op, "member-surname", "surname", $idsuffix, $sname_value);
//	$nname1 = new U3A_INPUT("text", "member-known_as", "u3a-member-known_as" . $idsuffix, "u3a-name-input-class", $nname_value);
//	$nname = U3A_HTML::labelled_html_object("known as:", $nname1, "u3a-member-known_as-label" . $idsuffix, "u3a-nickname-label-class u3a-label-class", false, true, null, true);
	$nname = u3a_get_member_details_form_text_field($op, "member-known_as", "known as", $idsuffix, $nname_value);
	if ($op === "mail")
	{
		$gender = new U3A_DIV("gender: " . ($gender_value === 'N' ? "rather not say" : $gender_value));
//		$gsel->add_attribute("disabled", "disabled");
	}
	else
	{
		$gsel = U3A_HTML_Utilities::get_gender_select("u3a-member-gender" . $idsuffix, "u3a-gender-class", $gender_value);
		$gender = U3A_HTML::labelled_html_object("gender:", $gsel, "u3a-gender-label" . $idsuffix, "u3a-gender-label-class",
			 false, true, null, true);
	}
	$mbrdiv = new U3A_DIV([$mbrhdr, $memnum, $title, $fname, $sname, $nname, $gender], "u3a-member-div" . $idsuffix,
	  "u3a-member-div-class u3a-border-bottom u3a-border-top");
	$ctchdr = new U3A_H(6, "Contact Details");
//	$email1 = new U3A_INPUT("email", "member-email", "u3a-member-email" . $idsuffix, "u3a-name-input-class", $email_value);
//	$email = U3A_HTML::labelled_html_object("email:", $email1, "u3a-member-email-label" . $idsuffix, "u3a-email-label-class u3a-label-class", false, true, null, true);
	$email = u3a_get_member_details_form_text_field($op, "member-email", "email", $idsuffix, $email_value, "email");
//	$tel1 = new U3A_INPUT("text", "member-telephone", "u3a-member-telephone" . $idsuffix, "u3a-name-input-class", $tel_value);
//	$tel = U3A_HTML::labelled_html_object("telephone:", $tel1, "u3a-member-telephone-label" . $idsuffix, "u3a-tel-label-class u3a-label-class", false, true, null, true);
	$tel = u3a_get_member_details_form_text_field($op, "member-telephone", "telephone", $idsuffix, $tel_value);
//	$mob1 = new U3A_INPUT("text", "member-mobile", "u3a-member-mobile" . $idsuffix, "u3a-name-input-class", $mob_value);
//	$mob = U3A_HTML::labelled_html_object("mobile:", $mob1, "u3a-member-mobile-label" . $idsuffix, "u3a-mobile-label-class u3a-label-class", false, true, null, true);
	$mob = u3a_get_member_details_form_text_field($op, "member-mobile", "mobile", $idsuffix, $mob_value);
//	$emergency1 = new U3A_INPUT("text", "member-emergency_contact", "u3a-member-emergency_contact" . $idsuffix, "u3a-name-input-class", $emergency_value);
//	$emergency = U3A_HTML::labelled_html_object("emergency:", $emergency1, "u3a-member-emergency-label" . $idsuffix, "u3a-emergency-label-class u3a-label-class", false, true, null, true);
	$emergency = u3a_get_member_details_form_text_field($op, "member-emergency_contact", "in emergency contact", $idsuffix,
	  $emergency_value);
	$ctcdiv = new U3A_DIV([$ctchdr, $email, $tel, $mob, $emergency], "u3a-contact-div" . $idsuffix,
	  "u3a-contact-div-class u3a-border-bottom");
	$addhdr = new U3A_H(6, "Address");
//	$house1 = new U3A_INPUT("text", "member-house", "u3a-member-house" . $idsuffix, "u3a-name-input-class", $house_value);
//	$house = U3A_HTML::labelled_html_object("house name or number:", $house1, "u3a-member-house-label" . $idsuffix, "u3a-house-label-class u3a-label-class", false, true, null, true);
	$house = u3a_get_member_details_form_text_field($op, "member-house", "house name or number", $idsuffix, $house_value);
//	$address11 = new U3A_INPUT("text", "member-address1", "u3a-member-address1" . $idsuffix, "u3a-name-input-class", $address1_value);
//	$address1 = U3A_HTML::labelled_html_object("address line 1:", $address11, "u3a-member-address1-label" . $idsuffix, "u3a-address-label-class u3a-label-class", false, true, null, true);
	$address1 = u3a_get_member_details_form_text_field($op, "member-address1", "address line 1", $idsuffix, $address1_value);
//	$address21 = new U3A_INPUT("text", "member-address2", "u3a-member-address2" . $idsuffix, "u3a-name-input-class", $address2_value);
//	$address2 = U3A_HTML::labelled_html_object("address line 2:", $address21, "u3a-member-address2-label" . $idsuffix, "u3a-address-label-class u3a-label-class", false, true, null, true);
	$address2 = u3a_get_member_details_form_text_field($op, "member-address2", "address line 2", $idsuffix, $address2_value);
//	$address31 = new U3A_INPUT("text", "member-address3", "u3a-member-address3" . $idsuffix, "u3a-name-input-class", $address3_value);
//	$address3 = U3A_HTML::labelled_html_object("address line 3:", $address31, "u3a-member-address3-label" . $idsuffix, "u3a-address-label-class u3a-label-class", false, true, null, true);
	$address3 = u3a_get_member_details_form_text_field($op, "member-address3", "address line 3", $idsuffix, $address3_value);
//	$town1 = new U3A_INPUT("text", "member-town", "u3a-member-town" . $idsuffix, "u3a-name-input-class", $town_value);
//	$town = U3A_HTML::labelled_html_object("town:", $town1, "u3a-member-town-label" . $idsuffix, "u3a-address-label-class u3a-label-class", false, true, null, true);
	$town = u3a_get_member_details_form_text_field($op, "member-town", "town", $idsuffix, $town_value);
//	$postcode1 = new U3A_INPUT("text", "member-postcode", "u3a-member-postcode" . $idsuffix, "u3a-name-input-class", $postcode_value);
//	$postcode = U3A_HTML::labelled_html_object("postcode:", $postcode1, "u3a-member-postcode-label" . $idsuffix, "u3a-address-label-class u3a-label-class", false, true, null, true);
	$postcode = u3a_get_member_details_form_text_field($op, "member-postcode", "postcode", $idsuffix, $postcode_value);
	if ($op === "mail")
	{
		$county = new U3A_DIV("county: " . $county_value);
//		$csel->add_attribute("disabled", "disabled");
	}
	else
	{
		$csel = U3A_HTML_Utilities::get_county_select("u3a-member-county" . $idsuffix, "u3a-member-county-class",
			 $county_value);
		$county = U3A_HTML::labelled_html_object("county:", $csel, "u3a-member-county-label" . $idsuffix,
			 "u3a-county-label-class", false, true, null, true);
	}
	$adddiv = new U3A_DIV([$addhdr, $house, $address1, $address2, $address3, $town, $postcode, $county],
	  "u3a-address-div" . $idsuffix, "u3a-address-div-class u3a-border-bottom");
	$payhdr = new U3A_H(6, "Payment");
	$payselectid = "u3a-member-payment_type" . $idsuffix;
	if ($op === "mail")
	{
		$payment = new U3A_DIV("payment type: " . $payment_value);
//		$payselect->add_attribute("disabled", "disabled");
	}
	else
	{
		$payselect = U3A_HTML_Utilities::get_payment_type_select($payselectid, "u3a-payment_type-class", $payment_value);
		$payselect->add_attribute("onchange", "u3a_payment_type_changed('$payselectid', '$op')");
		$payment = U3A_HTML::labelled_html_object("payment type:", $payselect, "u3a-payment-type-label" . $idsuffix,
			 "u3a-payment-type-label-class", false, true, null, true);
	}
	$gadiv = null;
	$gaid1 = "u3a-member-gift_aid" . $idsuffix;
	if ($op === "mail")
	{
		$giftaid = new U3A_DIV("gift aid: " . ($giftaid_value ? "yes" : "no"));
//		$giftaid1->add_attribute("disabled", "disabled");
	}
	else
	{
		$giftaid1 = new U3A_INPUT("checkbox", "gift_aid", $gaid1, "u3a-checkbox");
		if ($giftaid_value)
		{
			$giftaid1->add_attribute("checked", "checked");
		}
		if ($op === "join" || $op === "selfedit")
		{
			$giftaid1->add_attribute("disabled", "disabled");
			$gaid2 = "u3a-member-gift_aid2" . $idsuffix;
			$close = new U3A_A('#', "OK", 'u3a_gift_aid_a' . $idsuffix, null, "u3a_join_close('$gaid1', '$gaid2');");
			$close->add_attribute("rel", "modal:close");
			$img_url = "giftaid.jpeg";
			$attachment = get_page_by_title("giftaid", OBJECT, 'attachment');
			if ($attachment)
			{
				$img_url = $attachment->guid;
			}
			$gaimg = new U3A_IMG($img_url, 'u3a_gift_aid_image' . $idsuffix, null, null, "GiftAid it");
			$giftaid2 = new U3A_INPUT("checkbox", "gift_aid2", $gaid2, "u3a-checkbox");
//		$giftaid1->add_attribute("onchange", "u3a_gift_aid2_changed()");
			$giftaid2lbl = U3A_HTML::labelled_html_object("I am a UK taxpayer and I would like Shrewsbury U3A to treat all subscriptions and donations I have made in the past " .
				 "and that I make in the future as Gift Aid Donations and to reclaim tax accordingly.", $giftaid2,
				 "u3a-member-gift_aid2-label" . $idsuffix, "u3a-join-label-class u3a-checkbox-class u3a-label-class", false, true,
				 null, true);
			$gatxt1 = new U3A_P("I confirm that I have paid and/or will pay an amount of Income Tax and/or Capital Gains Tax for each tax year that is at least equal to the amount " .
			  "that all charities will claim on my behalf for that year.");
			$gatxt2 = new U3A_P("If your circumstances change and you no longer pay Income Tax and/or Capital Gains Tax equal to the tax that Shrewsbury U3A reclaims, please inform Shrewsbury U3A.");
			$gadiv = new U3A_DIV([$gaimg, $giftaid2lbl, $gatxt1, $gatxt2, $close], "u3a_gift_aid_div" . $idsuffix,
			  "u3a_gift_aid_div modal");
		}
		$gaspan = new U3A_SPAN("", null, "dashicons dashicons-editor-help");
		if ($op === "join" || $op === "selfedit")
		{
			$giftaid = U3A_HTML::labelled_html_object("gift aid:" . $gaspan->to_html(), $giftaid1,
				 "u3a-member-gift_aid-label" . $idsuffix, "u3a-checkbox-class u3a-label-class", false, true, null, true);
			$giftaid->add_attribute("onclick", "u3a_join_clicked('u3a_gift_aid_div')");
		}
		else
		{
			$giftaid = U3A_HTML::labelled_html_object("gift aid:", $giftaid1, "u3a-member-gift_aid-label" . $idsuffix,
				 "u3a-checkbox-class u3a-label-class", false, true, null, true);
		}
	}
	$tamdiv = null;
	$tamid1 = "u3a-member-TAM" . $idsuffix;
	$tamid2 = "u3a-member-TAM2" . $idsuffix;
	if ($op === "mail")
	{
		$tam = new U3A_DIV("receive TAM: " . ($tam_value ? "yes" : "no"));
//		$tam1->add_attribute("disabled", "disabled");
	}
	else
	{
		$tam1 = new U3A_INPUT("checkbox", "TAM", $tamid1, "u3a-checkbox");
		if ($tam_value === 1)
		{
			$tam1->add_attribute("checked", "checked");
		}
		if ($op === "join" || $op === "selfedit")
		{
			$tam1->add_attribute("disabled", "disabled");
			$tamclose = new U3A_A('#', "OK", 'u3a_TAM_a' . $idsuffix, null, "u3a_join_close('$tamid1', '$tamid2');");
			$tamclose->add_attribute("rel", "modal:close");
			$tamtxt = new U3A_P("With your permission, we would also like to forward your mailing information to The Third Age Trust so that you receive national U3A publications like Third Age Matters");
			$tam2 = new U3A_INPUT("checkbox", "TAM2", $tamid2, "u3a-checkbox");
			$tamlbl2 = U3A_HTML::labelled_html_object("I consent to my data being shared with The Third Age Trust", $tam2,
				 "u3a-member-gift_TAM2-label" . $idsuffix, "u3a-join-label-class u3a-checkbox-class u3a-label-class", false, true,
				 null, true);
			$tamdiv = new U3A_DIV([$tamtxt, $tamlbl2, $tamclose], "u3a_TAM_div" . $idsuffix, "u3a_TAM_div modal");
		}
		if ($op === "join" || $op === "selfedit")
		{
			$tam = U3A_HTML::labelled_html_object("receive TAM:" . $gaspan->to_html(), $tam1, "u3a-member-TAM-label" . $idsuffix,
				 "u3a-checkbox-class u3a-label-class", false, true, null, true);
			$tam->add_attribute("onclick", "u3a_join_clicked('u3a_TAM_div')");
		}
		else
		{
			$tam = U3A_HTML::labelled_html_object("receive TAM:", $tam1, "u3a-member-TAM-label" . $idsuffix,
				 "u3a-checkbox-class u3a-label-class", false, true, null, true);
		}
	}
	$nldiv = null;
	$nlid1 = "u3a-member-newsletter" . $idsuffix;
	$nlid2 = "u3a-member-newsletter2" . $idsuffix;
	if ($op === "mail")
	{
		$newsletter = new U3A_DIV("newsletter posted: " . ($newsletter_value ? "yes" : "no"));
//		$newsletter1->add_attribute("disabled", "disabled");
	}
	else
	{
		$newsletter1 = new U3A_INPUT("checkbox", "newsletter", $nlid1, "u3a-checkbox");
		if ($newsletter_value)
		{
			$newsletter1->add_attribute("checked", "checked");
		}
		if ($op === "join" || $op === "selfedit")
		{
			$newsletter1->add_attribute("disabled", "disabled");
			$nlclose = new U3A_A('#', "OK", 'u3a_newsletter_a' . $idsuffix, null, "u3a_join_close('$nlid1', '$nlid2');");
			$nlclose->add_attribute("rel", "modal:close");
			$nltxt = new U3A_P("Shrewsbury U3A's policy is to avoid use of paper wherever possible. Our monthly newsletter will be sent by email." .
			  " Please do not tick this box unless you have a particular need to receive it by post.");
			$newsletter2 = new U3A_INPUT("checkbox", "newsletter2", $nlid2, "u3a-checkbox");
			$nllbl2 = U3A_HTML::labelled_html_object("Please send my copy of the monthly newsletter by post", $newsletter2,
				 "u3a-member-gift_newsletter2-label" . $idsuffix, "u3a-join-label-class u3a-checkbox-class u3a-label-class", false,
				 true, null, true);
			$nldiv = new U3A_DIV([$nltxt, $nllbl2, $nlclose], "u3a_newsletter_div" . $idsuffix, "u3a_newsletter_div modal");
		}
		if ($op === "join" || $op === "selfedit")
		{
			$newsletter = U3A_HTML::labelled_html_object("newsletter posted:" . $gaspan->to_html(), $newsletter1,
				 "u3a-member-newsletter-label" . $idsuffix, "u3a-checkbox-class u3a-label-class", false, true, null, true);
			$newsletter->add_attribute("onclick", "u3a_join_clicked('u3a_newsletter_div')");
		}
		else
		{
			$newsletter = U3A_HTML::labelled_html_object("newsletter posted:", $newsletter1,
				 "u3a-member-newsletter-label" . $idsuffix, "u3a-checkbox-class u3a-label-class", false, true, null, true);
		}
	}
	$affiliation = u3a_get_member_details_form_text_field($op, "member-affiliation", "other U3A membership", $idsuffix,
	  $affiliation_value);
	$paydiv = new U3A_DIV([$payhdr, $payment, $giftaid], "u3a-payment-div" . $idsuffix,
	  "u3a-payment-div-class u3a-border-bottom");
	$opthdr = new U3A_H(6, "Options");
	$optdiv = new U3A_DIV([$opthdr, $affiliation, $tam, $newsletter], "u3a-options-div" . $idsuffix,
	  "u3a-options-div-class u3a-border-bottom");
	$privdiv = null;
	$amount_div = null;
//	$subs_div = null;
	if ($op === 'join')
	{
		$privhdr = new U3A_H(6, "PRIVACY STATEMENT");
		$priv1 = new U3A_P(do_shortcode('The information you have supplied will be used in accordance with our [u3a_document_link title="Data Protection Policy" target="_BLANK"] document.'));
		$priv2 = new U3A_P("Shrewsbury U3A will:");
		$privli1 = new U3A_LI("Store it securely for membership purposes");
		$privli2 = new U3A_LI("Use it to communicate with you as a U3A member");
		$privli3 = new U3A_LI("Share it with coordinators of those groups to which you belong");
		$privul = new U3A_LIST([$privli1, $privli2, $privli3]);
		$privdiv = new U3A_DIV([$privhdr, $priv1, $priv2, $privul], "u3a-privacy-" . $idsuffix, "u3a-privacy-div-class");
		$date_format = "F jS Y";
		$amount_text = "The cost to join now" . (U3A_Information::u3a_is_reduced_rate() ? ", at the reduced rate with less than half a year to go," : "") . " is £" .
		  U3A_Information::u3a_get_current_join_rate(false) . " for full members and £" . U3A_Information::u3a_get_current_join_rate(true) .
		  " for associate members who are already members of another U3A. The next subscription will be due on " . U3A_Information::u3a_get_subscriptions_due_next_year($date_format) .
		  ", you may renew any time after " . U3A_Information::u3a_get_renewals_from_this_year($date_format) .
		  ". Your membership will lapse if you do not renew before " . U3A_Information::u3a_get_membership_lapses_next_year($date_format) . ".";
//		$subs_div = new U3A_DIV($subs_due, "u3a-subs-" . $idsuffix, "u3a-subs-div-class u3a-margin-bottom-5");
		$amount_div = new U3A_DIV($amount_text, "u3a-cost-" . $idsuffix, "u3a-cost-div-class u3a-margin-bottom-10");
	}
	$groups = null;
	if (($op !== "mail") && ($atts["groups"] === "yes") && $mbr)
	{
		$grps = U3A_Group_Members::get_groups_for_member($mbr);
		$grparray = [];
		if ($grps)
		{
			foreach ($grps as $grp)
			{
				$astrx = U3A_Group_Members::is_coordinator($mbr, $grp) ? "*" : "";
				$grparray[] = new U3A_DIV($grp->name . $astrx, null, "u3a-margin-bottom-2");
			}
		}
		$grphdr = new U3A_H(6, "Groups");
		$groups = new U3A_DIV([$grphdr, $grparray], null, "u3a-border-bottom");
	}
	if (($op === 'mail') || ($atts["button"] === "no"))
	{
		$btn = null;
	}
	elseif ($op === "view")
	{
		$btn = new U3A_BUTTON("button", "Clear", "u3a-member-btn" . $idsuffix,
		  "u3a-member-btn-class u3a-button u3a-inline-block u3a-margin-top-5",
		  "u3a_clear_member_form('" . $atts["op"] . $atts["suffix"] . "')");
	}
	else
	{
		$btn1 = new U3A_BUTTON("button", "OK", "u3a-member-btn" . $idsuffix,
		  "u3a-member-btn-class u3a-button u3a-inline-block u3a-margin-top-5", "u3a_member_form('" . $atts["op"] . "')");
		if ($atts["op"] === "join")
		{
			$paybtn = new U3A_BUTTON("button", "Pay Now", "u3a-member-pay-btn" . $idsuffix,
			  "u3a-member-pay-btn-class u3a-wide-button u3a-margin-top-5 u3a-invisible", "u3a_join_paypal()");
			$btn = [$btn1, $paybtn];
		}
		else
		{
			$btn = $btn1;
		}
	}
	if ($op === "mail")
	{
		$new_member_action = null;
		$new_member_op = null;
		$new_member_mbr = null;
		$new_member_req = null;
		$new_member_status = null;
	}
	else
	{
		$new_member_action = new U3A_INPUT("hidden", "action", "u3a-member-action" . $idsuffix, "u3a-member-action-class",
		  "u3a_member");
		$new_member_op = new U3A_INPUT("hidden", "op", "u3a-member-op" . $idsuffix, "u3a-member-op-class", $atts["op"]);
		$new_member_mbr = new U3A_INPUT("hidden", "mbr", "u3a-member-mbr" . $idsuffix, "u3a-member-mbr-class", $atts["member"]);
		$new_member_req_val = array_key_exists($op, U3A_Information::$application_form_required_fields) ? implode(",",
			 U3A_Information::$application_form_required_fields[$op]) : null;
		$new_member_req = new U3A_INPUT("hidden", "required", "u3a-member-required" . $idsuffix, "u3a-member-required-class",
		  $new_member_req_val);
		$new_member_status = new U3A_INPUT("hidden", "member-status", "u3a-member-status" . $idsuffix,
		  "u3a-member-status-class", "Provisional");
	}
	$form = new U3A_FORM([$privdiv, $amount_div, $reqtxt, $mbrdiv, $ctcdiv, $adddiv, $paydiv, $optdiv, $groups, $new_member_action, $new_member_op, $new_member_mbr, $new_member_req, $btn],
	  "u3a_add_member", "POST", "u3a-member-form" . $idsuffix, "u3a-member-form-class");
	return U3A_HTML::to_html([$form, $gadiv, $tamdiv, $nldiv]);
}

add_shortcode("u3a_new_group_form", "u3a_new_group_form_contents");

function u3a_new_group_form_contents($atts1)
{
	$atts = shortcode_atts(array(
		"group"	 => 0,
		"op"		 => "add"
	  ), $atts1, 'u3a_new_group_form');
	$idsuffix = "-" . $atts["op"];
//	$grpname_value = null;
//	if ($atts["group"])
//	{
//		$grp = U3A_Groups::get_group($atts["group"]);
//	}
	$grpid = new U3A_INPUT("hidden", "group-id", "u3a-group-id" . $idsuffix, null, $atts["group"]);
	$source = new U3A_INPUT("hidden", "source", "u3a-edit-group-source" . $idsuffix, null, "committee_new");
	$grpname1 = new U3A_INPUT("text", "group-name", "u3a-group-name" . $idsuffix, "u3a-input-class u3a-name-input-class");
	$grpname = U3A_HTML::labelled_html_object("name:", $grpname1, "u3a-group-name-label" . $idsuffix,
		 "u3a-group-name-label-class", false, true);
	$coordfname1 = new U3A_INPUT("text", "group-coordinator-forename", "u3a-group-coordinator-forename" . $idsuffix,
	  "u3a-input-class u3a-name-input-class");
	$coordfname1->add_attribute("readonly", "readonly");
	$coordsname1 = new U3A_INPUT("text", "group-coordinator-surname", "u3a-group-coordinator-surname" . $idsuffix,
	  "u3a-input-class u3a-name-input-class");
	$coordsname1->add_attribute("readonly", "readonly");
	$coordmnum = new U3A_INPUT("hidden", "group-coordinator-mnum", "u3a-group-coordinator-mnum" . $idsuffix,
	  "u3a-group-coordinator-mnum-class");
	$coordsearch = do_shortcode('[u3a_find_member_dialog group="0" next_action="be_coordinator" close="tick" op="add"]');
	$coord = new U3A_DIV([$coordfname1, $coordsname1, $coordmnum], "u3a-group-coordinator-div" . $idsuffix,
	  "u3a-group-coordinator-div-class u3a-inline-block");
	$coordname1 = U3A_HTML::labelled_html_object("coordinator:", $coord, "u3a-group-coord-label" . $idsuffix,
		 "u3a-group-coord-label-class", false, false);
	$coordname = new U3A_DIV($coordname1, "u3a-group-coord-label-div" . $idsuffix, "u3a-when-div-class u3a-inline-block");
	$btnid = "u3a-group-btn" . $idsuffix;
	$btn = new U3A_BUTTON("button", "OK", $btnid, "u3a-group-btn-class u3a-button");
	$btn->add_attribute("onclick", "on_group_button_click('$btnid', 'add')");
	$new_group_action = new U3A_INPUT("hidden", "action", "u3a-group-action" . $idsuffix, null, "u3a-new-group-action");
	$vn = U3A_Row::load_array_of_objects("U3A_Venues");
	$venues = [new U3A_OPTION('to be determined', 0, true)];
	foreach ($vn["result"] as $v)
	{
		$venues[] = new U3A_OPTION($v->venue, $v->id);
	}
	$venue_select = new U3A_SELECT($venues, "venue", "u3a-group-venue" . $idsuffix, "u3a-group-venue-class");
	$venue = U3A_HTML::labelled_html_object("venue:", $venue_select, "u3a-group-venue-label" . $idsuffix,
		 "u3a-group-venue-label-class", false, true);
	$whnname1 = new U3A_INPUT("text", "group-when", "u3a-group-when" . $idsuffix, "u3a-input-class u3a-when-input-class");
	$whnname1->add_attribute("readonly", "readonly");
	$whnname = U3A_HTML::labelled_html_object("when:", $whnname1, "u3a-group-when-label" . $idsuffix,
		 "u3a-group-when-label-class", false, false);
	$whnjson = new U3A_INPUT("hidden", "group-when-json", "u3a-group-when-json" . $idsuffix, null, "");
	$whnnamediv = new U3A_DIV([$whnname, $whnjson], "u3a-group-when-div" . $idsuffix, "u3a-when-div-class u3a-inline-block");
	$whndialog = do_shortcode('[u3a_meeting_times_dialog close="tick" op="add" inline="yes" group="' . $atts["group"] . '"]');
	$maxname1 = new U3A_INPUT("number", "group-max", "u3a-group-max" . $idsuffix, "u3a-input-class u3a-number-input-class");
	$maxname1->add_attribute("min", 2);
	$maxname1->add_attribute("max", 25);
	$maxname = U3A_HTML::labelled_html_object("maximum size:", $maxname1, "u3a-group-max-label" . $idsuffix,
		 "u3a-group-max-label-class", false, true);
	$notes1 = new U3A_TEXTAREA("group-notes", "u3a-group-notes" . $idsuffix, "u3a-textarea");
	$notes1->add_attributes([
		"autocomplete"	 => "on",
		"spellcheck"	 => "true",
		"rows"			 => 5,
		"cols"			 => 20,
		"maxlength"		 => 1024
	]);
	$notes = U3A_HTML::labelled_html_object("information:", $notes1, "u3a-group-notes-label" . $idsuffix,
		 "u3a-group-notes-label-class", false, true);
	$form = new U3A_FORM([$grpid, $grpname, $coordname, $coordsearch, $venue, $whnnamediv, $whndialog, $maxname, $notes, $btn, $new_group_action],
	  "/wp-admin/admin-ajax.php", "POST", "u3a-group-form" . $idsuffix, "u3a-group-form-class");
	return $form;
}

add_shortcode("u3a_group_select", "u3a_group_select_contents");

function u3a_group_select_contents($atts1)
{
	$atts = shortcode_atts(array(
		"op" => "edit"
	  ), $atts1, 'u3a_group_select');
	$idsuffix = "-" . $atts["op"];
	$grps = U3A_Row::load_array_of_objects("U3A_Groups", null, "name");
	$options = [];
	foreach ($grps["result"] as $grp)
	{
		$options[] = new U3A_OPTION($grp->name, $grp->id);
	}
	$sel = new U3A_SELECT($options, "select-group", "u3a-select-group" . $idsuffix, "u3a-select-group-class");
	$ok = new U3A_BUTTON("button", "OK", "u3a_select-group-btn" . $idsuffix,
	  "u3a_select-group-btn-class u3a-button u3a-inline-block u3a-margin-left-5",
	  "u3a_after_select_group('" . $atts["op"] . "')");
	$div = new U3A_DIV(U3A_HTML::labelled_html_object("group:", $sel, "u3a-select-group-label" . $idsuffix,
		 "u3a-select-group-label-class", false, false), null, "u3a-inline-block");
	$edit_div = new U3A_DIV(null, "u3a-group-div" . $idsuffix, "u3a-edit-group-div-class u3a-border-top");
	return U3A_HTML::to_html([$div, $ok, $edit_div]);
}

add_shortcode("u3a_edit_group_form", "u3a_edit_group_form_contents");

function u3a_edit_group_form_contents($atts1)
{
	$atts = shortcode_atts(array(
		"group"	 => null,
		"source"	 => "committee_edit"
	  ), $atts1, 'u3a_edit_group_form');
	$form = "";
	$idsuffix = "-edit";
	if ($atts["group"])
	{
		$grp = U3A_Groups::get_group($atts["group"]);
		$group_id = $grp->id;
		$group_name = $grp->name;
		$gmw = $grp->get_meets_when_text();
		$gmwj = $grp->meets_when;
//		write_log("1.got meets when", $gmwj);
		$group_venue = $grp->venue;
		$group_info = U3A_Utilities::strip_all_slashes($grp->information);
		$group_max = $grp->max_members;
	}
	else
	{
		$group_id = 0;
		$group_name = "";
		$gmw = "";
		$gmwj = "";
		$group_venue = 0;
		$group_info = "";
		$group_max = 10;
	}
	$grpid = new U3A_INPUT("hidden", "group-id", "u3a-group-id" . $idsuffix, null, $group_id);
	$source = new U3A_INPUT("hidden", "source", "u3a-edit-group-source" . $idsuffix, null, $atts["source"]);
	$grpname1 = new U3A_INPUT("text", "group-name", "u3a-group-name" . $idsuffix, "u3a-input-class u3a-name-input-class",
	  $group_name);
	$grpname = U3A_HTML::labelled_html_object("name:", $grpname1, "u3a-group-name-label" . $idsuffix,
		 "u3a-group-name-label-class", false, true);
	if ($group_id)
	{
		$coorddiv = new U3A_DIV(U3A_HTML_Utilities::get_coordinators_for_group_edit($grp),
		  "u3a-group-coordinators" . $idsuffix);
	}
	else
	{
		$coorddiv = new U3A_DIV(null, "u3a-group-coordinators" . $idsuffix);
	}
	$btnid = "u3a-group-btn" . $idsuffix;
	$btn = new U3A_BUTTON("button", "OK", $btnid, "u3a-group-btn-class u3a-button");
	$btn->add_attribute("onclick", "on_group_button_click('$btnid', 'edit')");
	$edit_group_action = new U3A_INPUT("hidden", "action", "u3a-group-action" . $idsuffix, null, "u3a-edit-group-action");
	$vn = U3A_Row::load_array_of_objects("U3A_Venues");
	$venues = [new U3A_OPTION('to be determined', 0, 0 == $group_venue)];
	foreach ($vn["result"] as $v)
	{
		$venues[] = new U3A_OPTION($v->venue, $v->id, $v->id == $group_venue);
	}
	$venue_select = new U3A_SELECT($venues, "venue", "u3a-group-venue" . $idsuffix, "u3a-group-venue-class");
	$venue = U3A_HTML::labelled_html_object("venue:", $venue_select, "u3a-group-venue-label" . $idsuffix,
		 "u3a-group-venue-label-class", false, true);
	$whnname1 = new U3A_INPUT("text", "group-when", "u3a-group-when" . $idsuffix, "u3a-input-class u3a-when-input-class",
	  $gmw);
	$whnname1->add_attribute("readonly", "readonly");
	$whnname = U3A_HTML::labelled_html_object("when:", $whnname1, "u3a-group-when-label" . $idsuffix,
		 "u3a-group-when-label-class", false, false);
	$whnjson = new U3A_INPUT("hidden", "group-when-json", "u3a-group-when-json" . $idsuffix, null,
	  htmlentities(U3A_Utilities::strip_all_slashes($gmwj)));
	$whnnamediv = new U3A_DIV([$whnname, $whnjson], "u3a-group-when-div" . $idsuffix, "u3a-when-div-class u3a-inline-block");
	$whndialog = do_shortcode('[u3a_meeting_times_dialog close="tick" op="edit" inline="yes" group="' . $group_id . '"]');
	$maxname1 = new U3A_INPUT("number", "group-max", "u3a-group-max" . $idsuffix, "u3a-input-class u3a-number-input-class",
	  $group_max);
	$maxname1->add_attribute("min", 2);
	$maxname1->add_attribute("max", 25);
	$maxname = U3A_HTML::labelled_html_object("maximum size:", $maxname1, "u3a-group-max-label" . $idsuffix,
		 "u3a-group-max-label-class", false, true);
	$notes1 = new U3A_TEXTAREA("group-notes", "u3a-group-notes" . $idsuffix, "u3a-textarea", $group_info);
	$notes1->add_attributes([
		"autocomplete"	 => "on",
		"spellcheck"	 => "true",
		"rows"			 => 5,
		"cols"			 => 20,
		"maxlength"		 => 4096
	]);
	$notes = U3A_HTML::labelled_html_object("information:", $notes1, "u3a-group-notes-label" . $idsuffix,
		 "u3a-group-notes-label-class", false, true);
//		$form = new U3A_FORM([$grpid, $grpname, $coordnames, $coordplus, $coordname, $venue, $whnnamediv, $whndialog, $maxname, $notes, $btn, $edit_group_action], "/wp-admin/admin-ajax.php", "POST", "u3a-group-form" . $idsuffix, "u3a-group-form-class");
	$form = new U3A_FORM([$grpid, $source, $grpname, $coorddiv, $venue, $whnnamediv, $whndialog, $maxname, $notes, $btn, $edit_group_action],
	  "/wp-admin/admin-ajax.php", "POST", "u3a-group-form" . $idsuffix, "u3a-group-form-class");
	return $form;
}

add_shortcode("u3a_add_new_group", "u3a_add_new_group_contents");

function u3a_add_new_group_contents($atts1)
{
	$hdr = new U3A_H(6, "Create a New Group");
	return $hdr->to_html() . do_shortcode("[u3a_new_group_form]");
}

add_shortcode("u3a_edit_group", "u3a_edit_group_contents");

function u3a_edit_group_contents($atts1)
{
	$hdr = new U3A_H(6, "Edit a Group");
	$div = new U3A_DIV(do_shortcode("[u3a_edit_group_form]"), "u3a-edit-group-form-div", "u3a-edit-group-form-div-class");
	return /* U3A_Information::not_implemented("fully") . */$hdr->to_html() . do_shortcode("[u3a_group_select]") . $div->to_html();
}

add_shortcode("u3a_delete_group", "u3a_delete_group_contents");

function u3a_delete_group_contents($atts1)
{
	return do_shortcode('[u3a_group_select op="delete"]');
}

add_shortcode("u3a_committee_mail", "u3a_committee_mail_contents");

function u3a_committee_mail_contents($atts1)
{
	$atts = shortcode_atts(array(
		"role" => null
	  ), $atts1, 'u3a_committee_mail');
	$ret = "";
	if ($atts["role"])
	{
		$cm = U3A_Committee::get_committee($atts["role"]);
		if ($cm)
		{
			$ml = new U3A_A("mailto:" . U3A_Utilities::strip_all_slashes($cm->email), $cm->role, null, "u3a-committee-mail-class");
			$ret = $ml->to_html();
		}
	}
	return $ret;
}

add_shortcode("u3a_role_mail", "u3a_role_mail_contents");

function u3a_role_mail_contents($atts1)
{
	$atts = shortcode_atts(array(
		"role" => null
	  ), $atts1, 'u3a_role_mail');
	$ret = "";
	if ($atts["role"])
	{
		$cm = U3A_Roles::get_roles($atts["role"]);
		if ($cm)
		{
			$ml = new U3A_A("mailto:" . U3A_Utilities::strip_all_slashes($cm->email), $cm->role, null, "u3a-roles-mail-class");
			$ret = $ml->to_html();
		}
	}
	return $ret;
}

add_shortcode("u3a_number_of_members", "u3a_number_of_members_contents");

function u3a_number_of_members_contents($atts1)
{
	$atts = shortcode_atts(array(
		"paid"		 => null,
		"associate"	 => null
	  ), $atts1, 'u3a_number_of_members');
	$where = ["status" => "Current", "class<>" => "System"];
	if ($atts["paid"] === "yes")
	{
		$where["renewal_needed"] = 0;
	}
	if ($atts["paid"] === "no")
	{
		$where["renewal_needed"] = 1;
	}
	if ($atts["associate"] === "yes")
	{
		$where["affiliation<>"] = null;
	}
	if ($atts["associate"] === "no")
	{
		$where["affiliation"] = null;
	}
	return U3A_Row::count_rows("U3A_Members", $where);
}

add_shortcode("u3a_number_of_groups", "u3a_number_of_groups_contents");

function u3a_number_of_groups_contents($atts1)
{
	return U3A_Row::count_rows("U3A_Groups", ["status" => 1, "name<>" => "System Test"]);
}

add_shortcode('u3a_meetings', 'u3a_meetings_contents');

function u3a_meetings_contents($atts1)
{
	$atts = shortcode_atts(array(), $atts1, 'u3a_meetings');
	$mbr = U3A_Information::u3a_logged_in_user();
	$mm = do_shortcode('[u3a_text name="monthly_meeting" div="u3a-bottom-margin-5"][u3a_monthly_meeting][su_gmap address="Theatre Severn, Frankwell Quay, Shrewsbury, Shropshire, SY3 8FT" responsive="yes"]');
	$ct = do_shortcode('[u3a_text name="coffee_time" div="u3a-bottom-margin-5"][su_gmap address="Palmer\'s Café, Claremont Street, SY1 1QG" responsive="yes"]');
	$pgcontent = "";
	$pgcontent .= "[su_tabs]\n";
	$pgcontent .= '[su_tab title="Monthly Meeting" disabled="no" anchor="" url="" target="blank" class=""]' . $mm . "\n[/su_tab]\n";
	$pgcontent .= '[su_tab title="Coffee Time" disabled="no" anchor="" url="" target="blank" class=""]' . $ct . "\n[/su_tab]\n";
	if ($mbr)
	{
		$labels = [
			"contact",
			"address",
			"telephone",
			"email"
		];
		$vn = U3A_Row::load_array_of_objects("U3A_Venues", null, "venue");
		$venues = "[su_accordion]\n";
		foreach ($vn["result"] as $venue)
		{
//			write_log($venue);
			if ($venue->postcode)
			{
				$venues .= '[su_spoiler title="' . $venue->venue . '" style="fabric" icon="arrow-circle-1"]';
				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">contact:</span><span class="u3a-venue-value">' . $venue->contact . '</span></div>';
				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">address:</span><span class="u3a-venue-value">' . $venue->address . ', ' . $venue->postcode . '</span></div>';
				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">telephone:</span><span class="u3a-venue-value">' . $venue->telephone . '</span></div>';
				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">email:</span><span class="u3a-venue-value">' . U3A_Utilities::strip_all_slashes($venue->email) . '</span></div>';
				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">website:</span><span class="u3a-venue-value">' . $venue->website . '</span></div>';
				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">accessible:</span><span class="u3a-venue-value">' . ($venue->is_accessible ? "yes" : "no") . '</span></div>';
				if ($venue->notes)
				{
					$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">notes:</span><span class="u3a-venue-value">' . $venue->notes . '</span></div>';
				}
				$venues .= '[su_gmap address="' . $venue->address . ', ' . $venue->postcode . '" responsive="yes"]';
				$venues .= "[/su_spoiler]\n";
			}
		}
		$venues .= "[/su_accordion]\n";
		$pgcontent .= '[su_tab title="Venues" disabled="no" anchor="" url="" target="blank" class=""]' . $venues . "\n[/su_tab]\n";
	}
	$pgcontent .= "[/su_tabs]\n";
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_join", "u3a_join_contents");

function u3a_join_contents($atts1)
{
	$atts = shortcode_atts(array(), $atts1, 'u3a_join');
	$pgcontent = "";
	$pgcontent .= do_shortcode('[u3a_text name="join_1" para="yes"]');
	$pgcontent .= do_shortcode('[u3a_text name="join_2" para="yes"]');
	$pgcontent .= do_shortcode('[u3a_text name="join_3" para="yes"]');
	$pgcontent .= do_shortcode('[u3a_text name="join_4" para="yes"]');
	$pgcontent .= do_shortcode('[u3a_text name="join_5" para="yes"]');
	return $pgcontent;
}

add_shortcode("u3a_add_member_to_group", "u3a_add_member_to_group_contents");

function u3a_add_member_to_group_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group' => NULL
	  ), $atts1, 'u3a_add_member_to_group');
	$pgcontent = "";
	if (!$atts["group"])
	{
		$pgcontent = '[u3a_group_select op="add_member"]';
		$div = new U3A_Div(null, "u3a-add-member-container");
		$pgcontent .= $div->to_html();
	}
	else
	{
		$groups_id = intval($atts["group"]);
		$grp = U3A_Groups::get_group($groups_id);
		if ($grp)
		{
			$mbr = U3A_Information::u3a_logged_in_user();
			if (U3A_Group_Members::is_coordinator($mbr, $grp) || U3A_Committee::is_committee_member($mbr) || U3A_Information::u3a_has_permission($mbr,
				 "manage group membership", $grp))
			{
				$mbrsearch1 = do_shortcode('[u3a_find_member_dialog group="0" next_action="add_to_group" close="tick" op="' . $groups_id . '" byname="yes" suffix="1"]');
				$mbrsearch2 = do_shortcode('[u3a_find_member_dialog group="0" next_action="add_to_group" close="tick" op="' . $groups_id . '" byname="no" suffix="2"]');
				$lbl1 = new U3A_LABEL("u3a-find-member-search-a-add-to-group-add2grp1", "search for a member by name", null,
				  "u3a-search-label");
				$lbl1->add_attribute("role", "button");
				$mbrsearchdiv1 = new U3A_DIV([$lbl1, $mbrsearch1], "u3a-select-member-text-div-add2grp1", "u3a-margin-bottom-5");
				$lbl2 = new U3A_LABEL("u3a-find-member-search-a-add-to-group-add2grp2", "search for a member by number", null,
				  "u3a-search-label");
				$lbl2->add_attribute("role", "button");
				$mbrsearchdiv2 = new U3A_DIV([$lbl2, $mbrsearch2], "u3a-select-member-text-div-add2grp2", "u3a-margin-bottom-5");
				$hdr = new U3A_H(4, "Add Member to Group");
				$wlcb = new U3A_INPUT("checkbox", "waiting_list", "u3a-add2grp-waiting-list");
				$wllbl = U3A_HTML::labelled_html_object("on waiting list", $wlcb, "u3a-add2grp-waiting-list-label", null, false,
					 true);
				$pgcontent = U3A_HTML::to_html([$hdr, $wllbl, $mbrsearchdiv1, $mbrsearchdiv2]);
			}
//				$pgcontent = U3A_HTML::to_html([$hdr, '[u3a_select_group_members group="' . $grp->id . '" next_action="add_to_group" op="add"]']);
		}
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_remove_member_from_group", "u3a_remove_member_from_group_contents");

function u3a_remove_member_from_group_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group' => NULL
	  ), $atts1, 'u3a_remove_member_from_group');
	$pgcontent = "";
	if (!$atts["group"])
	{
		$pgcontent = do_shortcode('[u3a_group_select op="remove_member"]');
		$div = new U3A_Div(null, "u3a-remove-member-container");
		$pgcontent .= $div->to_html();
	}
	else
	{
		$grp = U3A_Groups::get_group($atts["group"]);
		if ($grp)
		{
			$mbr = U3A_Information::u3a_logged_in_user();
			if (U3A_Group_Members::is_coordinator($mbr, $grp) || U3A_Committee::is_webmanager($mbr) || U3A_Information::u3a_has_permission($mbr,
				 "manage group membership", $grp))
			{
				$hdr = new U3A_H(4, "Remove a Member from the Group");
				$txt = '[u3a_members group="' . $grp->id . '" select="yes" op="remove"]';
				$btn = new U3A_BUTTON("button", '<span class="dashicons dashicons-no"></span>',
				  "u3a-remove-member-from-group-button", "u3a-remove-member-from-group-button-class u3a-button");
				$btn->add_attribute("onclick", "u3a_remove_member_from_group_clicked()");
				$div = new U3A_DIV([$hdr, $txt, $btn], null,
				  "u3a-remove-member-from-group-div-class u3a-dropdown-container u3a-border-top");
				$pgcontent = do_shortcode($div->to_html());
			}
		}
	}
	return $pgcontent;
}

add_shortcode("u3a_new_document_category", "u3a_new_document_category_contents");

function u3a_new_document_category_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'	 => null,
		"member"	 => 0
	  ), $atts1, 'u3a_new_document_category');
	$pgcontent = "";
	if ($atts["group"] !== null)
	{
		$groups_id = intval($atts["group"]);
		if ($groups_id)
		{
			$mbr = U3A_Information::u3a_logged_in_user();
			$candodocs = U3A_Information::u3a_has_permission($mbr, "manage documents", $groups_id);
			$candoimages = U3A_Information::u3a_has_permission($mbr, "manage images", $groups_id);
			if ($candodocs)
			{
				$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("document", "category", $groups_id,
					 U3A_Documents::GROUP_DOCUMENT_TYPE, U3A_Document_Categories::GROUP_CATEGORY);
			}
			if ($candoimages)
			{
				$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("image", "album", $groups_id,
					 U3A_Documents::GROUP_IMAGE_TYPE, U3A_Document_Categories::GROUP_CATEGORY);
			}
		}
		else
		{
			$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("private document", "category", $groups_id,
				 U3A_Documents::PRIVATE_DOCUMENT_TYPE, U3A_Document_Categories::COMMITTEE_CATEGORY);
			$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("public document", "category", $groups_id,
				 U3A_Documents::PUBLIC_DOCUMENT_TYPE, U3A_Document_Categories::COMMITTEE_CATEGORY);
			$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("image", "album", $groups_id,
				 U3A_Documents::COMMITTEE_IMAGE_TYPE, U3A_Document_Categories::COMMITTEE_CATEGORY);
		}
	}
	elseif ($atts["member"])
	{
		$members_id = intval($atts["member"]);
		$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("document", "category", $members_id,
			 U3A_Documents::PERSONAL_DOCUMENT_TYPE, U3A_Document_Categories::MEMBER_CATEGORY);
		$pgcontent .= U3A_HTML_Utilities::u3a_get_document_section("image", "album", $members_id,
			 U3A_Documents::PERSONAL_IMAGE_TYPE, U3A_Document_Categories::MEMBER_CATEGORY);
	}
	return $pgcontent;
}

add_shortcode('u3a_group_page', 'u3a_group_page_contents');

function u3a_group_page_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'		 => 0,
		'tab'			 => null,
		'spoiler'	 => null,
		'category'	 => 0
	  ), $atts1, 'u3a_group_page');
//	write_log($_GET);
//	write_log($_POST);
//	write_log($atts);
	if (isset($_POST["spoiler"]) && $_POST["spoiler"])
	{
		$atts["spoiler"] = $_POST["spoiler"];
	}
	if (isset($_POST["tab"]) && $_POST["tab"])
	{
		$atts["tab"] = $_POST["tab"];
	}
	if (isset($_POST["group"]) && $_POST["group"])
	{
		$atts["group"] = $_POST["group"];
	}
	if (isset($_POST["category"]) && $_POST["category"])
	{
		$atts["category"] = $_POST["category"];
	}
	if (isset($_GET["spoiler"]) && $_GET["spoiler"])
	{
		$atts["spoiler"] = $_GET["spoiler"];
	}
	if (isset($_GET["tab"]) && $_GET["tab"])
	{
		$atts["tab"] = $_GET["tab"];
	}
	if (isset($_GET["group"]) && $_GET["group"])
	{
		$atts["group"] = $_GET["group"];
	}
	if (isset($_GET["category"]) && $_GET["category"])
	{
		$atts["category"] = $_GET["category"];
	}
//	write_log("u3a_group_page_contents");
//	write_log($atts);
	$grpidinp = new U3A_INPUT("hidden", null, "u3a-group-page-group-id", null, $atts["group"]);
	$pgcontent = $grpidinp->to_html();
	if ($atts["group"] > 0)
	{
		$grp = U3A_Groups::get_group($atts["group"]);
		if ($grp)
		{
			$grpnameinp = new U3A_INPUT("hidden", null, "u3a-group-page-group-name", null, $grp->name);
			$pgcontent .= $grpnameinp->to_html();
			$grppgid = U3A_Information::u3a_group_page($grp);
			$grppginp = new U3A_INPUT("hidden", null, "u3a-group-page-page-id", null, $grppgid);
			$pgcontent .= $grppginp->to_html();
			$mbr = U3A_Information::u3a_logged_in_user();
			$grpid = $grp->id;
			if ($mbr)
			{
				$mbridinp = new U3A_INPUT("hidden", null, "u3a-group-page-member-id", null, $mbr->id);
				$pgcontent .= $mbridinp->to_html();
				$imgs1 = U3A_Documents::get_header_images($grpid);
				$imgs = $imgs1["images"];
				$categories_id = $imgs1["categories_id"];
				if ($imgs)
				{
					$ndx = rand(0, count($imgs) - 1);
					$src = wp_get_attachment_url($imgs[$ndx]);
//<figure class="wp-block-image"><img src="http://shrewsburyu3a.website/wp-content/uploads/2019/01/shrewsbury-u3a-banner.jpg" alt="" class="wp-image-25"/></figure>
					$img = new U3A_IMG($src, null, "wp-image-25 u3a-header-image", "change_header_image(1)", "images for " . $grp->name);
					$img->add_attribute("title", get_the_title($imgs[$ndx]));
					$fig = new U3A_FIGURE($img, null, "wp-block-image");
					$type_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-type", U3A_Documents::COMMITTEE_IMAGE_TYPE);
					$cat_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-category", $categories_id);
					$ndx_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-index", $ndx);
					$total_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-total", count($imgs));
					$group_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-group", $grpid);
					$mbr_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-member", $mbr->id);
					$pgcontent .= U3A_HTML::to_html([$fig, $type_val, $cat_val, $ndx_val, $total_val]);
				}
				$candodocs = U3A_Information::u3a_has_permission($mbr, "manage documents", $grp);
				$candoimages = U3A_Information::u3a_has_permission($mbr, "manage images", $grp);
				$candoperms = U3A_Information::u3a_has_permission($mbr, "manage permissions", $grp);
				$candomembers = U3A_Information::u3a_has_permission($mbr, "manage group membership", $grp);
				$candoemail = U3A_Information::u3a_has_permission($mbr, "email group", $grp) || U3A_Information::u3a_has_permission($mbr,
					 "email group", 0);
				$candodetails = U3A_Information::u3a_has_permission($mbr, "edit group details", $grp);
				$cando = $candodocs || $candoimages || $candoperms || $candomembers;
				$docs = '[u3a_document_list group="' . $grpid . '" type="' . U3A_Documents::GROUP_DOCUMENT_TYPE . '"]';
				$gall = '[u3a_image_list group="' . $grpid . '"]';
				$active1 = U3A_Information::get_group_page_active_tab($atts["tab"]);
//			$active1 = 2;
				write_log("active: " . $active1 . " " . $atts["tab"]);
				$pgcontent .= '[su_tabs style="wood" active="' . $active1 . '"]\n';
				$pgcontent .= '[su_tab title="Information" disabled="no" anchor="" url="" target="blank" class=""]' . U3A_Utilities::strip_all_slashes($grp->information) . "\n[/su_tab]\n";
				if ($candoemail || $candomembers)
				{
					$checked = "yes";
					$mbrlistdiv = new U3A_DIV('[u3a_members group="' . $grpid . '" checked="' . $checked . '" includecount="yes"]',
					  "u3a-group-members-list-div-" . $grpid, "u3a-member-list-class u3a-border-bottom");
					$sublist_hdr = new U3A_H(6, "group sublists");
					$sub_save_btn = new U3A_BUTTON("button", "save", "u3a-group-save-button-" . $grpid,
					  "u3a-sublist-button u3a-button u3a-margin-right-5", "u3a_group_mailing_list('save', " . $grpid . ")");
					$sub_update_btn = new U3A_BUTTON("button", "update", "u3a-group-update-button-" . $grpid,
					  "u3a-sublist-button u3a-button u3a-margin-right-5", "u3a_group_mailing_list('update', " . $grpid . ")");
					$sub_delete_btn = new U3A_BUTTON("button", "delete", "u3a-group-delete-button-" . $grpid,
					  "u3a-sublist-button u3a-button", "u3a_group_mailing_list('delete', " . $grpid . ")");
					$sub_save_btn->add_attribute("disabled", "disabled");
					$sub_update_btn->add_attribute("disabled", "disabled");
					$sub_delete_btn->add_attribute("disabled", "disabled");
					$btndiv = new U3A_DIV([$sub_save_btn, $sub_update_btn, $sub_delete_btn], "u3a-sublist-button-div-" . $grpid,
					  "u3a-sublist-button-div");
					$lstdiv = null;
					$existing_lists = U3A_Email_Utilities::get_group_mailing_sublist_select($grpid);
					if ($existing_lists)
					{
						$lstdiv = U3A_HTML::labelled_html_object("load sublist:", $existing_lists, null, null, false, true);
					}
					$current_list = new U3A_INPUT("hidden", "u3a-current-sublist", "u3a-current-sublist-" . $grpid, NULL, 0);
					$mbrdiv = new U3A_DIV([$mbrlistdiv, $sublist_hdr, $btndiv, $lstdiv, $current_list],
					  "u3a-group-members-div-" . $grpid, "u3a-inline-block u3a-padding-right-5 u3a-width-30-pc u3a-va-top");
					$ndocs = U3A_Row::count_rows("U3A_Documents",
						 ["groups_id" => $grpid, "document_type" => U3A_Documents::GROUP_DOCUMENT_TYPE]);
					$nimgs = U3A_Row::count_rows("U3A_Documents",
						 ["groups_id" => $grpid, "document_type" => U3A_Documents::GROUP_IMAGE_TYPE]);
					$emaildiv = U3A_HTML_Utilities::get_mail_contents_div($mbr->id, "group", $grpid,
						 "u3a-height-100-pc u3a-width-100-pc", $ndocs, $nimgs);
					$mailer = "[su_accordion]\n";
					if ($candoemail)
					{
						$mailer .= '[su_spoiler title="email members of group" style="fabric" icon="arrow-circle-1"]';
						$mailer .= U3A_HTML::to_html($emaildiv);
						$mailer .= "[/su_spoiler]\n";
//					if (U3A_Committee::is_webmanager($mbr))
//					{
						$mailer .= '[su_spoiler title="list group emails" style="fabric" icon="arrow-circle-1"]';
						$grpmbrs = U3A_Group_Members::get_members_in_group($grp);
						foreach ($grpmbrs as $grpmbr)
						{
							$gmdiv = new U3A_DIV(U3A_Utilities::strip_all_slashes($grpmbr->email), null, "u3a-group-email-class");
							$mailer .= $gmdiv->to_html();
						}
						$dlbtn = new U3A_BUTTON("button", "download as csv", "u3a-group-emails-download", "u3a-wide-button",
						  "u3a_download_group_emails()");
						$mailer .= $dlbtn->to_html();
						$mailer .= "[/su_spoiler]\n";

//					}
					}
					if ($candomembers)
					{
						$mailer .= '[su_spoiler title="move members to another group" style="fabric" icon="arrow-circle-1"]';
						$mailer .= do_shortcode('[u3a_group_select op="move_members"]');
//						$dlbtn = new U3A_BUTTON("button", "move", "u3a-group-move-members", "u3a-button", "u3a_move_group_members(" . $grpid . ")");
						$mailer .= "[/su_spoiler]\n";
					}
					$mailer .= "[/su_accordion]\n";
					$mdiv = new U3A_DIV($mailer, null, "u3a-inline-block u3a-width-70-pc u3a-height-100-pc u3a-va-top");
					$mbrs = U3A_HTML::to_html([$mbrdiv, $mdiv]);
					$maildiv = null;
				}
				else
				{
					$checked = "no";
					$mbrdiv = new U3A_DIV('[u3a_members group="' . $grpid . '" checked="' . $checked . '" emailcoord="yes" includecount="yes"]',
					  "u3a-group-members-div-" . $grpid, "u3a-member-list-class");
					$mbrs = $mbrdiv->to_html();
					$maildiv = U3A_HTML_Utilities::get_mail_contents_div($mbr->id, "individual", $grpid, "modal u3a-mail-dialog", 0, 0,
						 "", "modal:close");
				}
				$pgcontent .= '[su_tab title="Members" disabled="no" anchor="" url="" target="blank" class=""]' . $mbrs . "\n[/su_tab]\n";
				$pgcontent .= '[su_tab title="Documents" disabled="no" anchor="" url="" target="blank" class=""]' . $docs . "\n[/su_tab]\n";
				$pgcontent .= '[su_tab title="Gallery" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $gall . "\n[/su_tab]\n";
				if ($cando)
				{
					$mng = "[su_accordion]\n";
					if ($candoimages || $candodocs)
					{
						$mng .= U3A_Information::get_manage_open_spoiler("Manage Categories", $atts["spoiler"]);
						$mng .= '[u3a_new_document_category group="' . $grpid . '"]';
						$mng .= "[/su_spoiler]\n";
					}
					if ($candoimages)
					{
						$mng .= U3A_Information::get_manage_open_spoiler("Manage Images", $atts["spoiler"]);
						$mng .= '[u3a_manage_document group="' . $grpid . '" type="' . U3A_Documents::GROUP_IMAGE_TYPE . '" category="' . $atts["category"] . '"]';
						$mng .= "[/su_spoiler]\n";
					}
					if ($candodocs)
					{
						$mng .= U3A_Information::get_manage_open_spoiler("Manage Documents", $atts["spoiler"]);
						$mng .= '[u3a_manage_document group="' . $grpid . '" type="' . U3A_Documents::GROUP_DOCUMENT_TYPE . '" category="' . $atts["category"] . '"]';
						$mng .= "[/su_spoiler]\n";
					}
					if ($candoperms)
					{
						$mng .= U3A_Information::get_manage_open_spoiler("Manage Permissions", $atts["spoiler"]);
						$mng .= '[u3a_manage_permissions group="' . $grpid . '" committee="0"]';
						$mng .= "[/su_spoiler]\n";
					}
					if (U3A_Information::u3a_management_enabled() && $candomembers)
					{
						$mng .= U3A_Information::get_manage_open_spoiler("Manage Members", $atts["spoiler"]);
						$mng .= '[u3a_add_member_to_group group="' . $grpid . '"]';
						$mng .= '[u3a_remove_member_from_group group="' . $grpid . '"]';
						$mng .= "[/su_spoiler]\n";
						$mng .= U3A_Information::get_manage_open_spoiler("View Contact Details", $atts["spoiler"]);
						$mng .= '[u3a_select_group_members next_action="view_contact_details" onselect="u3a_select_contact_details" op="contact" group="' . $grpid . '"]';
						$first1 = U3A_Group_Members::get_first_id($grpid, true);
						$contact_details_div = new U3A_DIV('[u3a_view_member_contact_details member="' . $first1 . '"]',
						  "u3a-group-member-contact-details", "u3a-group-member-contact-details-class");
						$mng .= $contact_details_div->to_html();
						$mng .= "[/su_spoiler]\n";
						$wait = U3A_Group_Members::get_waiting_list($grpid, true);
						if ($wait)
						{
							$mng .= U3A_Information::get_manage_open_spoiler("Manage Waiting List", $atts["spoiler"]);
							foreach ($wait as $wt)
							{
								$yesbtn = new U3A_BUTTON("button", 'accept', null, "u3a-button u3a-inline-block",
								  "u3a_accept_from_waiting_list(" . $grpid . ", " . $wt->id . ")");
								$yesbtn->add_tooltip("accept into group");
								$nobtn = new U3A_BUTTON("button", 'remove', null, "u3a-button u3a-inline-block u3a-margin-left-5",
								  "u3a_remove_from_waiting_list(" . $grpid . ", " . $wt->id . ")");
								$nobtn->add_tooltip("remove");
								$txt = $wt->get_formal_name() . " (" . $wt->membership_number . ")";
								$spn = new U3A_SPAN($txt, null, "u3a-width-20-em u3a-inline-block");
								$div = new U3A_DIV([$spn, $yesbtn, $nobtn], null, "u3a-margin-bottom-5");
								$mng .= $div->to_html();
							}
							$mng .= "[/su_spoiler]\n";
						}
					}
					if (U3A_Information::u3a_management_enabled() && $candodetails)
					{
						$mng .= '[su_spoiler title="Edit Group Details" style="fabric" icon="arrow-circle-1"]';
						$mng .= '[u3a_edit_group_form group="' . $grpid . '" source="group"]';
						$lfm = $grp->looking_for_members;
						$lfmbtn_text = $lfm ? "unset accepting new members" : "set accepting new members";
						$lfmbtn = new U3A_BUTTON("button", $lfmbtn_text, "u3a-lfm-button", "u3a-very-wide-button",
						  "u3a_looking_for_members($grpid, $lfm)");
						$lfmbtndiv = new U3A_DIV($lfmbtn, "u3a-lfm-button-div", "u3a-member-manage-button-div");
						$mng .= $lfmbtndiv;
						$vm = $grp->virtual_meetings;
						$vmbtn_text = $vm ? "unset holding virtual meetings" : "set holding virtual meetings";
						$vmbtn = new U3A_BUTTON("button", $vmbtn_text, "u3a-vm-button", "u3a-very-wide-button",
						  "u3a_virtual_meetings($grpid, $vm)");
						$vmbtndiv = new U3A_DIV($vmbtn, "u3a-vm-button-div", "u3a-member-manage-button-div");
						$mng .= $vmbtndiv;
						$mng .= "[/su_spoiler]\n";
					}
					if (!$grp->has_mailing_list())
					{
						$mng .= '[su_spoiler title="Create Mailing List" style="fabric" icon="arrow-circle-1"]';
						$mng .= '[u3a_mailing_list_name group="' . $grpid . '"]';
						$mng .= "[/su_spoiler]\n";
					}
					$mng .= U3A_Information::get_manage_open_spoiler("Manage Links", $atts["spoiler"]);
					$div = new U3A_DIV('[u3a_manage_links member="0" group="' . $grpid . '"]', null, "u3a-manage-links-div");
					$mng .= $div->to_html();
					$mng .= "[/su_spoiler]\n";
					$mng .= "[/su_accordion]\n";
					$pgcontent .= '[su_tab title="Manage" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $mng . "\n[/su_tab]\n";
				}
//				if (U3A_Committee::is_webmanager($mbr))
//				{
				$links = new U3A_DIV('[u3a_links member="0" group="' . $grpid . '"]', null, "u3a-links-div u3a-overflow-y-auto");
				$pgcontent .= '[su_tab title="Links" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $links . "\n[/su_tab]\n";
				$pgcontent .= '[su_tab title="Forum" disabled="no" anchor="" url="" target="blank" class=""]';
				$forum = new U3A_Forum($grp->name, $grpid, $grp->keep_forum_posts);
//					$posts = U3A_Forum_Posts::get_posts_for_group($grpid);
//					$forum->add($posts);
				$pgcontent .= $forum->to_html(true);
				$pgcontent .= '[/su_tab]';
//				}
				$pgcontent .= "[/su_tabs]\n";
				if ($maildiv)
				{
					$pgcontent .= $maildiv->to_html();
				}
			}
//			write_log($pgcontent);
		}
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_view_member_contact_details", "u3a_view_member_contact_details_contents");

function u3a_view_member_contact_details_contents($atts1)
{
	$atts = shortcode_atts(array(
		'member' => 0
	  ), $atts1, 'u3a_view_member_contact_details');
	$pgcontent = "";
	if ($atts["member"])
	{
		$mbr = U3A_Members::get_member($atts["member"]);
		if ($mbr->email)
		{
			$lbl0 = new U3A_SPAN("email:", null, "u3a-contact-details-label u3a-inline-block u3a-width-10-em");
			$val0 = new U3A_SPAN(U3A_Utilities::strip_all_slashes($mbr->email), null,
			  "u3a-contact-details-value u3a-inline-block");
			$div0 = new U3A_DIV([$lbl0, $val0], null, "u3a-contact-details-div");
		}
		else
		{
			$div0 = null;
		}
		if ($mbr->telephone)
		{
			$lbl1 = new U3A_SPAN("telephone:", null, "u3a-contact-details-label u3a-inline-block u3a-width-10-em");
			$val1 = new U3A_SPAN($mbr->telephone, null, "u3a-contact-details-value u3a-inline-block");
			$div1 = new U3A_DIV([$lbl1, $val1], null, "u3a-contact-details-div");
		}
		else
		{
			$div1 = null;
		}
		if ($mbr->mobile)
		{
			$lbl2 = new U3A_SPAN("mobile:", null, "u3a-contact-details-label u3a-inline-block u3a-width-10-em");
			$val2 = new U3A_SPAN($mbr->mobile, null, "u3a-contact-details-value u3a-inline-block");
			$div2 = new U3A_DIV([$lbl2, $val2], null, "u3a-contact-details-div");
		}
		else
		{
			$div2 = null;
		}
		$lbl3 = new U3A_SPAN("address:", null, "u3a-contact-details-label u3a-inline-block u3a-width-10-em");
		$val3 = $mbr->house;
		$conj = is_numeric($val3) ? " " : ", ";
		$val3 .= $conj . $mbr->address1;
		if ($mbr->address2)
		{
			$val3 .= ", " . $mbr->address2;
		}
		if ($mbr->address3)
		{
			$val3 .= ", " . $mbr->address3;
		}
		$val3 .= ", " . $mbr->town . ", " . $mbr->postcode;
		$div3 = new U3A_DIV([$lbl3, $val3], null, "u3a-contact-details-div");
		return U3A_HTML::to_html([$div0, $div1, $div2, $div3]);
	}
}

add_shortcode("u3a_mailing_list_name", "u3a_mailing_list_name_contents");

function u3a_mailing_list_name_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group' => 0
	  ), $atts1, 'u3a_mailing_list_name');
	$div = null;
	if ($atts["group"])
	{
		$grp = U3A_Groups::get_group($atts["group"]);
		if ($grp)
		{
			$mlname = $grp->get_mailing_list_name();
			$inp = new U3A_INPUT("text", "list-name", "u3a-mailing-list-name", "u3a-mailing-list-name-class u3a-width-10-em",
			  $mlname);
			$mlist = U3A_HTML::labelled_html_object("mailing list name", $inp, "u3a-mailing-list-name-label",
				 "u3a-mailing-list-name-label-class", false, false,
				 "Enter a name for the list with no spaces or punctuation symbols except'.'.");
			$btn = new U3A_BUTTON("button", "Create", "u3a-mailing-list-create-button", "u3a-button",
			  "u3a_create_mailing_list(" . $grp->id . ")");
			$div = new U3A_DIV([$mlist, $btn], "u3a-mailing-list-create-div", "u3a-mailing-list-create-div-class");
		}
	}
	return U3A_HTML::to_html($div);
}

add_shortcode("u3a_select_group_members", "u3a_select_group_members_contents");

function u3a_select_group_members_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'			 => 0,
		"next_action"	 => "add_to_group",
		"op"				 => "add",
		"onselect"		 => null
	  ), $atts1, 'u3a_select_group_members');
//	write_log("u3a_find_members");
//	write_log($atts);
	$idsuffix = "-" . str_replace("_", "-", $atts["next_action"]) . "-" . $atts["op"];
	$grp = intval($atts["group"]);
	$pgcontent = "";
	if ($grp)
	{
		$sel = $atts["onselect"] ? $atts["onselect"] : "yes";
		$hdr1 = new U3A_H(6, "Select group member to " . str_replace('_', ' ', $atts["next_action"]) . ":");
		$grp_members = do_shortcode('[u3a_members group="' . $grp . '" select="' . $sel . '" op="' . $atts["next_action"] . '"]');
		$search_group = new U3A_INPUT("hidden", "group", "u3a-find-members-search-group" . $idsuffix, null, $grp);
		$search_div = new U3A_DIV([$grp_members, $search_group], "u3a-find-members-search-form-div" . $idsuffix,
		  "u3a-find-members-search-form-div-class u3a-border-top");
		$pgcontent = $search_div->to_html();
	}
	return $pgcontent;
}

add_shortcode("u3a_find_members", "u3a_find_members_contents");

function u3a_find_members_contents($atts1)
{
	$atts = shortcode_atts(array(
		"next_action"	 => "add_to_group",
		"op"				 => "add",
		"byname"			 => "yes",
		"suffix"			 => ""
	  ), $atts1, 'u3a_find_members');
//	write_log("u3a_find_members");
//	write_log($atts);
	$idsuffix = "-" . str_replace("_", "-", $atts["next_action"]) . "-" . $atts["op"] . $atts["suffix"];
	$hdr1 = new U3A_H(6, "Search for member to " . str_replace('_', ' ', $atts["next_action"]) . ":");
//	$search_button = new U3A_BUTTON("button", '<span class="dashicons dashicons-search"></span>', "u3a-find-members-search-button", "u3a-find-members-search-button-class u3a-button");
	$sbid = "u3a-find-members-search-button" . $idsuffix;
	$search_button = new U3A_A("#", '<span class="dashicons dashicons-search"></span>', $sbid,
	  "u3a-find-members-search-button-class");
	$search_button->add_attribute("onclick", "u3a_find_members_search_clicked('$sbid')");
	$search_button_div = new U3A_DIV($search_button, "u3a-find-members-search-button-div" . $idsuffix,
	  "u3a-find-members-search-button-div-class");
	$search_action = new U3A_INPUT("hidden", "action", "u3a-find-members-search-action" . $idsuffix, null,
	  "u3a_find_members_search");
	$search_next = new U3A_INPUT("hidden", "next_action", "u3a-find-members-search-next" . $idsuffix, null,
	  $atts["next_action"]);
	$search_byname = new U3A_INPUT("hidden", "byname", "u3a-find-members-search-byname" . $idsuffix, null, $atts["byname"]);
	$search_op = new U3A_INPUT("hidden", "op", "u3a-find-members-search-op" . $idsuffix, null, $atts["op"]);
	if ($atts["byname"] === "yes")
	{
		$surname_input = new U3A_INPUT("text", "member-surname", "find-member-surname" . $idsuffix,
		  "find-member-surname-class find-member-input-class");
		$forename_input = new U3A_INPUT("text", "member-forename", "find-member-forename" . $idsuffix,
		  "find-member-forename-class find-member-input-class");
		$surname = U3A_HTML::labelled_html_object("surname", $surname_input, null, "u3a-input-label-class", false, true);
		$forename = U3A_HTML::labelled_html_object("forename", $forename_input, null, "u3a-input-label-class", false, true);
		$xdiv = new U3A_DIV("fill some part of name and then press " . '<span class="dashicons dashicons-search"></span>',
		  "u3a-find-members-search-x" . $idsuffix, "u3a-find-members-search-x-class");
		$search_form = new U3A_FORM([$surname, $forename, $search_button_div, $search_action, $search_next, $search_op, $search_byname],
		  "/wp-admin/admin-ajax.php", "POST", "u3a-find-members-search-form" . $idsuffix, "u3a-find-members-search-form-class");
	}
	else
	{
		$mnum_input = new U3A_INPUT("text", "member-number", "find-member-number" . $idsuffix,
		  "find-member-number-class find-member-input-class");
		$mnum = U3A_HTML::labelled_html_object("membership number", $mnum_input, null, "u3a-wide-input-label-class", false,
			 true);
		$xdiv = new U3A_DIV("enter the membership number and then press " . '<span class="dashicons dashicons-search"></span>',
		  "u3a-find-members-search-x" . $idsuffix, "u3a-find-members-search-x-class");
		$search_form = new U3A_FORM([$mnum, $search_button_div, $search_action, $search_next, $search_op, $search_byname],
		  "/wp-admin/admin-ajax.php", "POST", "u3a-find-members-search-form" . $idsuffix, "u3a-find-members-search-form-class");
	}
	$search_div = new U3A_DIV([$hdr1, $xdiv, $search_form], "u3a-find-members-search-form-div" . $idsuffix,
	  "u3a-find-members-search-form-div-class u3a-border-top");
	$results_div = new U3A_DIV(null, "u3a-find-members-results-div" . $idsuffix,
	  "u3a-find-members-results-div-class u3a-invisible");
	$pgcontent = U3A_HTML::to_html([$search_div, $results_div]);
	return $pgcontent;
}

add_shortcode('u3a_groups_page', 'u3a_groups_page_contents');

function u3a_groups_page_contents($atts)
{
	$member = U3A_Information::u3a_logged_in_user();
	$where = (U3A_Committee::is_webmanager($member) || U3A_Members::is_system($member)) ? null : ["name<>" => "System Test"];
//	write_log("where: ", $where);
	$grps1 = U3A_Row::load_array_of_objects("U3A_Groups", $where, "name");
	$grps = $grps1["result"];
	$grp_pages = U3A_Information::u3a_group_pages($grps);
//	write_log($grps);
	$proposed_groups = [];
	$my_groups = [];
	$active_groups = [];
	$content = "";
	foreach ($grps as $grp)
	{
//		write_log($grp->name);
//		write_log(U3A_Group_Members::is_member($member, $grp) ? "member" : "not member");
		if (U3A_Utilities::starts_with($grp->name, "PROPOSED - "))
		{
//			write_log("proposed");
			$proposed_groups[] = $grp;
		}
		elseif ($member && U3A_Group_Members::is_member($member, $grp))
		{
//			write_log("mine");
			$my_groups[] = $grp;
		}
		else
		{
//			write_log("active");
			$active_groups[] = $grp;
		}
	}
	if (count($my_groups))
	{
		$content .= "<h4>My Groups</h4>";
		foreach ($my_groups as $mgrp)
		{
			$newpgid = $grp_pages[$mgrp->name];
			if ($mgrp->virtual_meetings)
			{
				if ($mgrp->looking_for_members)
				{
					$lfmtext = " (holding virtual meetings and accepting new members)";
				}
				else
				{
					$lfmtext = " (holding virtual meetings)";
				}
			}
			elseif ($mgrp->looking_for_members)
			{
				$lfmtext = " (accepting new members)";
			}
			else
			{
				$lfmtext = " ";
			}
			$content .= '<p class="u3a-group-link">[su_permalink id="' . $newpgid . '"] ' . $lfmtext . '</p>';
		}
	}
	$content .= $member ? "<h4>Other Active Groups</h4>" : "<h4>Active Groups</h4>";
	$content .= "[su_accordion]\n";
	foreach ($active_groups as $grp)
	{
		$apageid = $grp_pages[$grp->name];
		if ($grp->virtual_meetings)
		{
			if ($grp->looking_for_members)
			{
				$lfmtext = " (holding virtual meetings and accepting new members)";
			}
			else
			{
				$lfmtext = " (holding virtual meetings)";
			}
		}
		elseif ($grp->looking_for_members)
		{
			$lfmtext = " (accepting new members)";
		}
		else
		{
			$lfmtext = " ";
		}
// add group entry
		$content .= '[su_spoiler title="' . $grp->name . $lfmtext . '" style="fabric" icon="arrow-circle-1"]';
		$content .= '[u3a_group group="' . $grp->name . '" groupid="' . $grp->id . '" pgid="' . $apageid . '"]' . "\n";
		$content .= "[/su_spoiler]\n";
	}
	$content .= "[/su_accordion]\n";
	if (count($proposed_groups))
	{
		$content .= "<h4>Proposed New Groups</h4>";
		$content .= "[su_accordion]\n";
		foreach ($proposed_groups as $pgrp)
		{
			$content .= '[su_spoiler title="' . $pgrp->name . '" style="fabric" icon="arrow-circle-1"]' . addslashes($pgrp->information) . "[/su_spoiler]\n";
		}
		$content .= "[/su_accordion]\n";
	}
	$container = new U3A_DIV($content, "u3a-groups-page-container", "u3a-50vh-auto-y");
	$tblbtn = null;
	if (U3A_Information::u3a_has_permission($member, "manage newsletters"))
	{
		$container->add_class("u3a-margin-bottom-5");
		$tblbtn = new U3A_BUTTON("button", "download as table", "u3a-group-table-download-button", "u3a-wide-button",
		  "u3a_download_group_table()");
	}
	if ($member)
	{
		$maildiv = U3A_HTML_Utilities::get_mail_contents_div($member->id, "individual", 0, "modal u3a-mail-dialog", 0, 0, "",
			 "modal:close");
	}
	else
	{
		$maildiv = null;
	}
	return do_shortcode(U3A_HTML::to_html([$container, $tblbtn, $maildiv]));
//	return do_shortcode($content);
}

add_shortcode("u3a_help_videos", "u3a_help_videos_contents");

function u3a_help_videos_contents($atts1)
{
	$atts = shortcode_atts(array(
		"category" => 0
	  ), $atts1, 'u3a_help_videos');
	$videos = U3A_Row::load_array_of_objects("U3A_Help_Videos", ["category" => $atts["category"]], "name");
	$pgcontent = "";
	if ($videos["total_number_of_rows"])
	{
		$pgcontent .= "[su_accordion]\n";
		foreach ($videos["result"] as $video)
		{
			$pgcontent .= '[su_spoiler title="' . $video->name . '" style="fabric" icon="arrow-circle-1"]';
			$pgcontent .= '[su_youtube_advanced url="' . $video->url . '" responsive="yes"]';
			$div = new U3A_DIV($video->description, null, "u3a-video-description-class");
			$pgcontent .= $div->to_html();
			$pgcontent .= '[/su_spoiler]';
		}
		$pgcontent .= "[/su_accordion]\n";
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_videos", "u3a_videos_contents");

function u3a_videos_contents()
{
	$videos = U3A_Row::load_array_of_objects("U3A_Videos", null, "date");
//	write_log($videos);
	$yrs = [];
	foreach ($videos["result"] as $video)
	{
		$yr = U3A_Timestamp_Utilities::year(strtotime($video->date));
		if (array_key_exists($yr, $yrs))
		{
			array_push($yrs[$yr], $video);
		}
		else
		{
			$yrs[$yr] = [$video];
		}
	}
//	write_log($yrs);
	$pgcontent = '[su_tabs style="wood"]\n';
	foreach ($yrs as $yr => $vids)
	{
		$pgcontent .= '[su_tab title="' . $yr . '" disabled="no" anchor="" url="" target="blank" class=""]';
		$pgcontent .= "[su_accordion]\n";
		foreach ($vids as $vid)
		{
			if (strtolower($vid->name) !== "deleted video")
			{
				$pgcontent .= '[su_spoiler title="' . $vid->name . '" style="fabric" icon="arrow-circle-1"]';
				if ($vid->source === "youtube")
				{
					$pgcontent .= '[su_youtube_advanced url="' . $vid->url . '" responsive="yes"]';
				}
				elseif ($vid->source === "url")
				{
					$pgcontent .= '[video src="' . $vid->url . '"]';
				}
				$div = new U3A_DIV($vid->description, null, "u3a-video-description-class");
				$pgcontent .= $div->to_html();
				$pgcontent .= '[/su_spoiler]';
			}
		}
		$pgcontent .= "[/su_accordion]\n";
		$pgcontent .= "[/su_tab]\n";
	}
	$pgcontent .= '[/su_tabs]';
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_meeting_times", "u3a_meeting_times_contents");

function u3a_meeting_times_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'	 => 0,
		'op'		 => "add"
	  ), $atts1, 'u3a_meeting_times');
	$val = [
		"ntimes" => null
	];
	$val = [];
	if ($atts["group"])
	{
		$grp = U3A_Groups::get_group($atts["group"]);
		$val = json_decode(U3A_Utilities::strip_all_slashes($grp->meets_when), true);
//		write_log("meets when", $grp->meets_when);
//		write_log("val", $val);
	}
	if (!$val)
	{
		$val = [
			"ntimes"			 => 1,
			"every"			 => "month",
			"onweek"			 => [
				[
					"ord"	 => 0,
					"day"	 => "monday",
					"from" => "10:00",
					"to"	 => "12:00"
				]
			],
			"onfortnight"	 => [
				[
					"ord"	 => 0,
					"day"	 => "monday",
					"from" => "10:00",
					"to"	 => "12:00"
				]
			],
			"onmonth"		 => [
				[
					"ord"	 => 1,
					"day"	 => "monday",
					"from" => "10:00",
					"to"	 => "12:00"
				]
			]
		];
	}
//	$ntimes = new U3A_INPUT("number", "number_of_times", "u3a-number-of-meetings-" . $atts["op"], "u3a-number-input u3a-inline-block u3a-margin-left-5", $val["ntimes"]);
//	$ntimes->add_attributes([
//		"min"	 => 1,
//		"max"	 => 4
//	]);
	$div1 = null;
	$divw = null;
	$divm = null;
	if ($val)
	{
		$op = $atts["op"];
		$maxntimes = 5;
		$ntimes = U3A_HTML_Utilities::get_number_select("number_of_times", "u3a-number-of-meetings-$op",
			 "u3a-number-input u3a-inline-block u3a-margin-left-5", $val["ntimes"]);
		$ntimes->add_attribute("onchange", "u3a_meet_ntimes_change('$op')");
		$weekly = $val["every"] === "week";
		$fortnightly = $val["every"] === "fortnight";
		$monthly = $val["every"] === "month";
		$everyopts = [
			new U3A_OPTION("week", "week", $weekly),
			new U3A_OPTION("fortnight", "fortnight", $fortnightly),
			new U3A_OPTION("month", "month", $monthly)
		];
		$every = new U3A_SELECT($everyopts, "every", "u3a-every-$op", "u3a-every-select u3a-inline-block u3a-margin-left-5");
		$every->add_attribute("onchange", "u3a_meet_every_change('$op')");
		$span1 = new U3A_SPAN("meet ", "u3a-meet-text-$op", "u3a-text u3a-inline-block");
		$span2 = new U3A_SPAN($val["ntimes"] == 1 ? "time every" : "times every", "u3a-time-text-$op",
		  "u3a-text u3a-inline-block u3a-margin-left-5");
		$div1 = new U3A_DIV([$span1, $ntimes, $span2, $every], "u3a-ntimes-div-$op", "u3a-meeting-times-div");
		$onw = $val["onweek"];
		$divw = [];
		$defonw = [
			"ord"	 => 0,
			"day"	 => "monday",
			"from" => "14:00:00",
			"to"	 => "16:00:00"
		];
		for ($n = 0; $n < $maxntimes; $n++)
		{
			if ($onw)
			{
				if ($n < $val["ntimes"] && $n < count($onw))
				{
					$onwn = $onw[$n];
				}
				else
				{
					$onwn = $onw[0];
				}
			}
			else
			{
				$onwn = $defonw;
			}
			$sel = U3A_HTML_Utilities::get_day_of_week_select("weekday-" . $n, "u3a-weekday-select-week-$op-$n",
				 "u3a-weekday-select u3a-inline-block u3a-margin-left-5", $onwn["day"], true);
			$fromw = new U3A_INPUT("text", "from-time", "u3a-group-from-time-week-$op-$n",
			  "u3a-input u3a-time-input u3a-inline-block u3a-margin-left-5", $onwn["from"]);
//		$fromw->add_attribute("min", "08:00");
//		$fromw->add_attribute("max", "22:00");
//		$frmw = U3A_HTML::labelled_html_object("from:", $fromw, "u3a-group-from-label-week-$op-$n", "u3a-group-time-label-class u3a-inline-block u3a-margin-left-5", false, false);
			$tow = new U3A_INPUT("text", "to-time", "u3a-group-to-time-week-$op-$n",
			  "u3a-input u3a-time-input u3a-inline-block u3a-margin-left-5", $onwn["to"]);
//		$tow->add_attribute("min", "09:00");
//		$tow->add_attribute("max", "23:00");
//		$tw = U3A_HTML::labelled_html_object("to:", $tow, "u3a-group-to-label-week-$op-$n", "u3a-group-time-label-class u3a-inline-block", false, false);
			$spantow = new U3A_SPAN("to", "u3a-week-text-$op", "u3a-text u3a-inline-block u3a-margin-left-5  u3a-margin-right-5");
//		$timedivw = new U3A_DIV([$fromw, $spantow, $tow], "u3a-group-time-div-week-$op-$n", "u3a-group-time-div-class u3a-margin-bottom-10 u3a-inline-block");
//		$selectw = U3A_HTML::to_html([$sel, $fromw, $spantow, $tow]);
			$spanw = new U3A_SPAN($n === 0 ? "on" : "and", "u3a-week-text-$op-$n", "u3a-meet-text u3a-text u3a-inline-block");
			$vis = ($val["every"] === "week") && ($n < $val["ntimes"]) ? "u3a-visible" : "u3a-invisible";
			$divw[$n] = new U3A_DIV([$spanw, $sel, $fromw, $spantow, $tow], "u3a-week-div-$op-$n",
			  "u3a-week-month-div-class u3a-week-div-class u3a-week-div-class-$op u3a-margin-top-5 " . $vis);
		}
		$onf = array_key_exists("onfortnight", $val) ? $val["onfortnight"] : null;
		$divf = [];
		$defonf = [
			"ord"	 => 0,
			"day"	 => "monday",
			"from" => "14:00:00",
			"to"	 => "16:00:00"
		];
		for ($n = 0; $n < $maxntimes; $n++)
		{
			if ($onf)
			{
				if ($n < $val["ntimes"] && $n < count($onf))
				{
					$onfn = $onf[$n];
				}
				else
				{
					$onfn = $onf[0];
				}
			}
			else
			{
				$onfn = $defonf;
			}
			$self = U3A_HTML_Utilities::get_day_of_week_select("weekday-" . $n, "u3a-weekday-select-fortnight-$op-$n",
				 "u3a-weekday-select u3a-inline-block u3a-margin-left-5", $onfn["day"], true);
			$fromf = new U3A_INPUT("text", "from-time", "u3a-group-from-time-fortnight-$op-$n",
			  "u3a-input u3a-time-input u3a-inline-block u3a-margin-left-5", $onfn["from"]);
//		$fromw->add_attribute("min", "08:00");
//		$fromw->add_attribute("max", "22:00");
//		$frmw = U3A_HTML::labelled_html_object("from:", $fromw, "u3a-group-from-label-week-$op-$n", "u3a-group-time-label-class u3a-inline-block u3a-margin-left-5", false, false);
			$tof = new U3A_INPUT("text", "to-time", "u3a-group-to-time-fortnight-$op-$n",
			  "u3a-input u3a-time-input u3a-inline-block u3a-margin-left-5", $onfn["to"]);
//		$tow->add_attribute("min", "09:00");
//		$tow->add_attribute("max", "23:00");
//		$tw = U3A_HTML::labelled_html_object("to:", $tow, "u3a-group-to-label-week-$op-$n", "u3a-group-time-label-class u3a-inline-block", false, false);
			$spantof = new U3A_SPAN("to", "u3a-fortnight-text-$op",
			  "u3a-text u3a-inline-block u3a-margin-left-5  u3a-margin-right-5");
//		$timedivw = new U3A_DIV([$fromw, $spantow, $tow], "u3a-group-time-div-week-$op-$n", "u3a-group-time-div-class u3a-margin-bottom-10 u3a-inline-block");
//		$selectw = U3A_HTML::to_html([$sel, $fromw, $spantow, $tow]);
			$spanf = new U3A_SPAN($n === 0 ? "on" : "and", "u3a-fortnight-text-$op-$n", "u3a-meet-text u3a-text u3a-inline-block");
			$visf = ($val["every"] === "fortnight") && ($n < $val["ntimes"]) ? "u3a-visible" : "u3a-invisible";
			$divf[$n] = new U3A_DIV([$spanf, $self, $fromf, $spantof, $tof], "u3a-fortnight-div-$op-$n",
			  "u3a-week-month-div-class u3a-fortnight-div-class u3a-fortnight-div-class-$op u3a-margin-top-5 " . $visf);
		}
		$onm = $val["onmonth"];
		$divm = [];
		$defonm = [
			"ord"	 => 1,
			"day"	 => "monday",
			"from" => "14:00:00",
			"to"	 => "16:00:00"
		];
		for ($n = 0; $n < $maxntimes; $n++)
		{
			if ($onm)
			{
				if ($n < $val["ntimes"] && $n < count($onm))
				{
					$onmn = $onm[$n];
				}
				else
				{
					$onmn = $onm[0];
				}
			}
			else
			{
				$onmn = $defonm;
			}
			$selo = U3A_HTML_Utilities::get_ordinal_select("ordinal-" . $n, "u3a-ordinal-select-month-$op-$n",
				 "u3a-ordinal-select u3a-inline-block u3a-margin-left-5", $onmn["ord"]);
			$seld = U3A_HTML_Utilities::get_day_of_week_select("weekday-" . $n, "u3a-weekday-select-month-$op-$n",
				 "u3a-weekday-select u3a-inline-block", $onmn["day"], true);
			$fromm = new U3A_INPUT("text", "from-time", "u3a-group-from-time-month-$op-$n",
			  "u3a-input u3a-time-input u3a-inline-block u3a-margin-left-5", $onmn["from"]);
//		$fromm->add_attribute("min", "08:00");
//		$fromm->add_attribute("max", "22:00");
//		$frmm = U3A_HTML::labelled_html_object("from:", $fromm, "u3a-group-from-label-month-$op-$n", "u3a-group-time-label-class u3a-inline-block u3a-margin-left-5", false, false);
			$tom = new U3A_INPUT("text", "to-time", "u3a-group-to-time-month-$op-$n",
			  "u3a-input u3a-time-input u3a-inline-block u3a-margin-left-5", $onmn["to"]);
//		$tom->add_attribute("min", "09:00");
//		$tom->add_attribute("max", "23:00");
//		$tm = U3A_HTML::labelled_html_object("to:", $tom, "u3a-group-to-label-month-$op-$n", "u3a-group-time-label-class u3a-inline-block", false, false);
			$spantom = new U3A_SPAN("to", "u3a-month-text-$op", "u3a-text u3a-inline-block u3a-margin-left-5  u3a-margin-right-5");
//		$timedivm = new U3A_DIV([$frmm, $spantom, $tm], "u3a-group-time-div-month-$op-$n", "u3a-group-time-div-class u3a-margin-bottom-10 u3a-inline-block");
//		$selectm = U3A_HTML::to_html([$selo, $seld, $fromm, $spantom, $tom]);
			$spanm = new U3A_SPAN($n === 0 ? "on the " : "and the ", "u3a-month-text-$op-$n",
			  "u3a-meet-text u3a-text u3a-inline-block");
			$vis = ($val["every"] === "month") && ($n < $val["ntimes"]) ? "u3a-visible" : "u3a-invisible";
			$divm[$n] = new U3A_DIV([$spanm, $selo, $seld, $fromm, $spantom, $tom], "u3a-month-div-$op-$n",
			  "u3a-week-month-div-class u3a-month-div-class u3a-month-div-class-$op u3a-margin-top-5 " . $vis);
		}
	}
	return U3A_HTML::to_html([$div1, $divw, $divf, $divm]);
}

add_shortcode("u3a_meeting_times_dialog", "u3a_meeting_times_dialog_contents");

function u3a_meeting_times_dialog_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'	 => 0,
		'close'	 => "OK",
		'op'		 => "add",
		'inline'	 => "no"
	  ), $atts1, 'u3a_meeting_times_dialog');
//	write_log($atts1);
//	write_log($atts);
	$idsuffix = "-" . $atts["op"];
	if ($atts["close"] == "tick")
	{
		$cls = '<span class="dashicons dashicons-yes-alt"></span>';
	}
	else
	{
		$cls = $atts["close"];
	}
	$txt = do_shortcode('[u3a_meeting_times group="' . $atts["group"] . '" op="' . $atts["op"] . '"]');
	$close = new U3A_A('#', $cls, 'u3a_meeting_times_a' . $idsuffix, null,
	  "u3a_meeting_times_dialog_close('" . $atts["op"] . "');");
	$close->add_attribute("rel", "modal:close");
	$div = new U3A_DIV([$txt, $close], "u3a_meeting_times_div" . $idsuffix, "modal");
	$open = new U3A_A('#u3a_meeting_times_div' . $idsuffix, '<span class="dashicons dashicons-calendar"></span>', null,
	  $atts['inline'] === "yes" ? "u3a-inline-block" : null);
	$open->add_attribute("rel", "modal:open");
	$open->add_attribute("onclick", "u3a_modal_open('u3a_meeting_times_div$idsuffix')");
	return U3A_HTML::to_html([$open, $div]);
}

add_shortcode("u3a_manage_committee_documents", "u3a_manage_committee_documents_contents");

function u3a_manage_committee_documents_contents($atts1)
{
	$atts = shortcode_atts(array(
		'spoiler' => null
	  ), $atts1, 'u3a_manage_committee_documents');
	$mbr = U3A_Information::u3a_logged_in_user();
	$mng = "";
	if (U3A_Committee::is_committee_member($mbr))
	{
		$bck = new U3A_DIV('Back to [su_permalink id="' . U3A_Information::u3a_committee_page() . '" title="Back to Committee Page"]',
		  "u3a-back-to-committee-link-div", "u3a-link-div-class");
		$mng .= $bck->to_html();
		$mng .= "[su_accordion]\n";
		$mng .= U3A_Information::get_manage_open_spoiler("Manage Categories", $atts["spoiler"]);
		$mng .= '[u3a_new_document_category group="0"]';
		$mng .= "[/su_spoiler]\n";
		$mng .= U3A_Information::get_manage_open_spoiler("Manage Images", $atts["spoiler"]);
		$mng .= '[u3a_manage_document group="0" type="' . U3A_Documents::COMMITTEE_IMAGE_TYPE . '"]';
		$mng .= "[/su_spoiler]\n";
		$mng .= U3A_Information::get_manage_open_spoiler("Manage Private Documents", $atts["spoiler"]);
		$mng .= '[u3a_manage_document group="0"' . ' type="' . U3A_Documents::PRIVATE_DOCUMENT_TYPE . '"]';
		$mng .= "[/su_spoiler]\n";
		$mng .= U3A_Information::get_manage_open_spoiler("Manage Public Documents", $atts["spoiler"]);
		$mng .= '[u3a_manage_document group="0"' . ' type="' . U3A_Documents::PUBLIC_DOCUMENT_TYPE . '"]';
		$mng .= "[/su_spoiler]\n";
		if (U3A_Information::u3a_has_permission($mbr, "manage newsletters"))
		{
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Newsletters", $atts["spoiler"]);
			$mng .= '[u3a_manage_document group="0"' . ' type="' . U3A_Documents::NEWSLETTER_TYPE . '"]';
			$mng .= "[/su_spoiler]\n";
		}
		$mng .= "[/su_accordion]\n";
	}
	return do_shortcode($mng);
}

add_shortcode("u3a_manage_cooordinator_documents", "u3a_manage_cooordinator_documents_contents");

function u3a_manage_cooordinator_documents_contents()
{
	$mbr = U3A_Information::u3a_logged_in_user();
	$mng = "";
	if (U3A_Committee::is_committee_member($mbr))
	{
		$mng .= "[su_accordion]\n";
		$mng .= '[su_spoiler title="Manage Categories" style="fabric" icon="arrow-circle-1"]';
		$mng .= U3A_HTML_Utilities::u3a_get_document_section("document", "category", U3A_Documents::COMMITTEE_GROUP,
			 U3A_Documents::COORDINATORS_DOCUMENT_TYPE, U3A_Document_Categories::COORDINATOR_CATEGORY);
//		$groups_id = U3A_Documents::COMMITTEE_GROUP;
//		$span0 = new U3A_SPAN("New Document Category ", null, "u3a-document-category-span-class");
//		$txt0 = new U3A_INPUT("text", "document-category-name", "u3a-category-name-" . $groups_id . "-" . U3A_Documents::COORDINATORS_DOCUMENT_TYPE, "u3a-document-name-class u3a-name-input-class");
//		$btn0 = new U3A_BUTTON("button", "create", "u3a-category-button-" . $groups_id . "-" . U3A_Documents::COORDINATORS_DOCUMENT_TYPE, "u3a-document-button-class u3a-button", "u3a_create_new_category('" . $groups_id . "', '" . U3A_Documents::COORDINATORS_DOCUMENT_TYPE . "')");
//		$div0 = new U3A_DIV([$span0, $txt0, $btn0], "u3a-category-div-" . $groups_id . "-" . U3A_Documents::COORDINATORS_DOCUMENT_TYPE, "u3a-category-div");
//		$mng .= $div0->to_html();
		$mng .= "[/su_spoiler]\n";
		$mng .= '[su_spoiler title="Manage Documents" style="fabric" icon="arrow-circle-1"]';
		$mng .= '[u3a_manage_document group="0"' . ' type="' . U3A_Documents::COORDINATORS_DOCUMENT_TYPE . '"]';
		$mng .= "[/su_spoiler]\n";
		$mng .= "[/su_accordion]\n";
	}
	return do_shortcode($mng);
}

add_shortcode("u3a_manage_permissions", "u3a_manage_permissions_contents");

function u3a_manage_permissions_contents($atts1)
{
	$atts = shortcode_atts(array(
		'group'		 => 0,
		'committee'	 => 0
	  ), $atts1, 'u3a_manage_permissions');
	$groups_id = 0;
	$ptype = 0;
	if ($atts["group"])
	{
		$groups_id = intval(U3A_Groups::get_group_id($atts["group"]));
		$ptype = 1;
	}
	$is_committee = $atts["committee"];
//	var_dump($is_committee);
	$bck = null;
	if ($groups_id)
	{
		if ($is_committee)
		{
			$object_list1 = U3A_Row::load_array_of_objects("U3A_Committee", null, "role");
			$object_list = $object_list1["result"];
			$text_key = "role";
		}
		else
		{
			$objs = U3A_Group_Members::get_members_in_group($groups_id, true);
			$allhash = ["title" => "", "surname" => "everyone", "forename" => "", "membership_number" => 0];
			$all = new U3A_Members($allhash);
			$all->id = 0;
//			array_push($object_list, $all);
			$text_key = "name";
			$object_list = ["members" => $objs, "all" => [$all]];
		}
	}
	else
	{
		if ($is_committee)
		{
			$bck1 = new U3A_DIV('Back to [su_permalink id="' . U3A_Information::u3a_committee_page() . '" title="Back to Committee Page"]',
			  "u3a-back-to-committee-link-div", "u3a-link-div-class");
			$bck = do_shortcode($bck1->to_html());
			$object_list1 = U3A_Row::load_array_of_objects("U3A_Committee", null, "role");
			$object_list = $object_list1["result"];
			$text_key = "role";
		}
		else
		{
			$object_list = U3A_Members::get_all_members();
			$text_key = "name";
		}
	}
//	var_dump($object_list);
	if ($object_list)
	{
		$permit_types = U3A_Permission_Types::list_permission_types($ptype, U3A_Information::u3a_management_enabled());
		if ($permit_types)
		{
			$sel = U3A_HTML_Utilities::get_select_list_from_object_array($object_list, "permit_to", $text_key, "id", null, null,
				 "u3a-permit-to-list-$groups_id", "u3a-select");
			$permsel = U3A_HTML_Utilities::get_select_list_from_object_array($permit_types, "allow_to", "name", "id", null, null,
				 "u3a-allow-to-list-$groups_id", "u3a-select");
//			$span0 = new U3A_SPAN("allow", null, "u3a-inline-block u3a-margin-right-5");
			$span1 = new U3A_SPAN("to", null, "u3a-inline-block u3a-margin-right-5 u3a-margin-left-5 u3a-margin-right-5");
			$btn0 = new U3A_BUTTON("button", "Allow", "u3a-permit-button-$groups_id",
			  "u3a-button u3a-inline-block u3a-margin-right-5", "u3a_create_permission($groups_id, $is_committee)");
			$btn0->add_tooltip("Click to set permission shown");
			$div0 = new U3A_DIV([$btn0, $sel, $span1, $permsel], "u3a-permit-div-$groups_id",
			  "u3a-border-bottom u3a-border-top u3a-bottom-margin-5 u3a-padding-top-5");
		}
		else
		{
			$div0 = new U3A_DIV("no permissions to give.", "u3a-permit-div-$groups_id",
			  "u3a-border-bottom u3a-border-top u3a-bottom-margin-5 u3a-padding-top-5");
		}
	}
	else
	{
		$div0 = new U3A_DIV("no permissions to give.", "u3a-permit-div-$groups_id", "u3a-border-bottom");
	}
	$perms = U3A_Permissions::get_permissions_for_group($groups_id, U3A_Information::u3a_management_enabled());
	if ($perms)
	{
		$divs = [];
		foreach ($perms as $perm)
		{
			$cb = new U3A_INPUT("checkbox", "is-permitted", "u3a-is-permitted-" . $perm->id, "", $perm->id);
			if ($perm->forename)
			{
				if ($perm->surname)
				{
					$name = (ucfirst($perm->forename) . " " . ucfirst($perm->surname));
				}
				else
				{
					$name = ucfirst($perm->forename);
				}
			}
			elseif ($perm->surname)
			{
				$name = ucfirst($perm->surname);
			}
			else
			{
				$name = "everyone";
			}
			$text = ($perm->committee_role ? $perm->committee_role : $name) . " can " . $perm->permission_name;
			$lbl = new U3A_LABEL("u3a-is-permitted-" . $perm->id, $text, "u3a-is-permitted-label-" . $perm->id, "");
			$divs[] = new U3A_DIV([$cb, $lbl], "u3a-is-permitted-div-" . $perm->id, "");
		}
		$btn1 = new U3A_BUTTON("button", "Remove", "u3a-remove-permit-button-$groups_id",
		  "u3a-button u3a-inline-block u3a-margin-right-5", "u3a_remove_permission($groups_id)");
		$btn1->add_tooltip("Click to remove all selected permissions");
		$lbl1 = new U3A_LABEL("u3a-remove-permit-button-$groups_id", "selected permissions",
		  "u3a-remove-permit-button-label-$groups_id", null);
		$divs[] = new U3A_DIV([$btn1, $lbl1], "u3a-remove-permit-button-div-$groups_id",
		  "u3a-bottom-margin-5 u3a-top-margin-5");
		$div1 = new U3A_DIV($divs, "u3a-remove-permit-div-$groups_id", "u3a-border-bottom");
	}
	else
	{
		$div1 = new U3A_DIV("no permissions to remove.", "u3a-remove-permit-div-$groups_id", "u3a-border-bottom");
	}
	$pgcontents = U3A_HTML::to_html([$bck, $div0, $div1]);
	return $pgcontents;
}

add_shortcode("u3a_home", "u3a_home_contents");

function u3a_home_contents()
{
	$mbr = U3A_Information::u3a_logged_in_user();
	$u3aname = ucfirst(U3A_Information::u3a_get_u3a_name());
	$imgs1 = U3A_Documents::get_header_images(0, $mbr);
	$imgs = $imgs1["images"];
	$categories_id = $imgs1["categories_id"];
	$ndx = rand(0, count($imgs) - 1);
	$src = wp_get_attachment_url($imgs[$ndx]);
//<figure class="wp-block-image"><img src="http://shrewsburyu3a.website/wp-content/uploads/2019/01/shrewsbury-u3a-banner.jpg" alt="" class="wp-image-25"/></figure>
	$img = new U3A_IMG($src, null, "wp-image-25 u3a-header-image", "change_header_image(1)", "views of " . $u3aname);
	$type_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-type", U3A_Documents::COMMITTEE_IMAGE_TYPE);
	$cat_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-category", $categories_id);
	$ndx_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-index", $ndx);
	$total_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-total", count($imgs));
	$group_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-group", 0);
	$mbr_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-member", $mbr ? $mbr->id : 0);
	$img->add_attribute("title", get_the_title($imgs[$ndx]));
	$fig = new U3A_FIGURE($img, null, "wp-block-image");
	$names = U3A_Row::load_column("u3a_text", "name", ["name~%" => "home_"], false, true);
//	write_log("names", $names);
	$bg = new U3A_H(5, "Background");
	$urgent = "[u3a_urgent]";
	$rnw = [];
	if ($mbr)
	{
		$renew1 = U3A_Members::can_renew();
		$renew2 = $mbr->renewal_needed;
		write_log("renew", $renew1, $renew2);
		if (($renew1 || U3A_Committee::is_webmanager($mbr)) && $renew2)
		{
			$now = time();
			$rnw[] = new U3A_INPUT("hidden", null, "u3a-paypal-action", null, "renew");
			$rnw[] = new U3A_INPUT("hidden", "member-affiliation", "u3a-member-affiliation", "u3a-name-input-class",
			  $mbr->affiliation);
			$rnw[] = new U3A_INPUT("hidden", "subscription-rate", "u3a-subscription-rate", null,
			  U3A_Information::u3a_get_renewal_rate());
			$rnw[] = new U3A_INPUT("hidden", "associate-subscription-rate", "u3a-associate-subscription-rate", null,
			  U3A_Information::u3a_get_renewal_rate(true));
			$renew = strtotime($mbr->renew);
			$sub_date = date("jS F Y", $renew);
			if (time() < $renew)
			{
				$rnw[] = new U3A_DIV(new U3A_B("Your subscription is due for renewal on $sub_date."), "u3a-paypal-text",
				  "u3a-paypal-text-class");
			}
			else
			{
				$lapses = date("jS F Y", U3A_Information::u3a_get_membership_lapses($renew));
				$rnw[] = new U3A_DIV(new U3A_B("Your subscription was due for renewal on $sub_date. Please pay before $lapses"),
				  "u3a-paypal-text", "u3a-paypal-text-class");
			}
			$rnw[] = new U3A_DIV("You can pay with a credit card via PayPal, you do not need to have a PayPal account.",
			  "u3a-paypal-text", "u3a-paypal-text-class");
			$rnw[] = new U3A_DIV(null, "u3a-paypal-container-renew", "u3a-paypal-container");
		}
	}
	$pgcontents = [$type_val, $cat_val, $ndx_val, $total_val, $fig, $rnw, $urgent, $bg];
	$joinbuttondiv = null;
	foreach ($names as $name)
	{
		$div = new U3A_DIV('[u3a_text name="' . $name . '"]', null, "u3a-text-paragraph");
		$pgcontents[] = $div;
	}
//	var_dump($mbr);
	if ($mbr)
	{
		$url = get_permalink(U3A_Information::u3a_help_page()); // . "?tab=documents&category=Website+User+Guide";
		$a = new U3A_A($url, "here");
		$lnk = $a->to_html();
		$txt = "The User Guide for this website can be found $lnk.";
		$div = new U3A_DIV($txt, null, "u3a-text-paragraph");
		$url1 = get_permalink(U3A_Information::u3a_help_videos_page()); // . "?tab=documents&category=Website+User+Guide";
		$a1 = new U3A_A($url1, "here");
		$lnk1 = $a1->to_html();
		$txt1 = "Videos demonstrating how to use features of this website can be found $lnk1.";
		$div1 = new U3A_DIV($txt1, null, "u3a-text-paragraph");
		$url2 = "https://www.youtube.com/watch?v=9isp3qPeQ0E"; // . "?tab=documents&category=Website+User+Guide";
		$a2 = new U3A_A($url2, "here");
		$a2->add_attribute("data-popup", "true");
		$lnk2 = $a2->to_html();
		$txt2 = "A video explaining how to join a zoom meeting can be found $lnk2.";
		$div2 = new U3A_DIV($txt2, null, "u3a-text-paragraph");
		$pgcontents[] = [$div, $div1, $div2];
		$pgcontents[] = new U3A_INPUT("hidden", "u3a-member-id", "u3a-member-id", null, $mbr->id);
		$pgcontents[] = U3A_HTML_Utilities::get_the_news(false, $mbr);
		if (U3A_Information::u3a_has_permission($mbr, "add news"))
		{
			$addbtn = new U3A_BUTTON("button", "+News", "news-add-button", "u3a-button u3a-add-news-button", "add_news()");
			$addtitlelbl = new U3A_LABEL("add-news-title", "title:", "add-news-title-label", "u3a-inline-block u3a-width-5em");
			$addtitle = new U3A_INPUT("text", "news-title", "add-news-title",
			  "u3a-add-news-title u3a-inline-block u3a-margin-bottom-5");
			$addtitlediv = new U3A_DIV([$addtitlelbl, $addtitle], "add-news-title-div", "add-news-div");
			$dt = new DateTime();
			$dtf0 = $dt->format('Y-m-d');
			$dt->add(new DateInterval('P7D'));
			$dtf1 = $dt->format('Y-m-d');
			$dt->add(new DateInterval('P1Y'));
			$dtf2 = $dt->format('Y-m-d');
			$addexpires = new U3A_INPUT("date", "news-expires", "add-news-expires", null, $dtf1);
			$addexpires->add_attribute("min", $dtf0);
			$addexpires->add_attribute("max", $dtf2);
			$addexpireslbl = new U3A_LABEL("add-news-expires", "expires:", "add-news-expires-label",
			  "u3a-inline-block u3a-width-5em");
			$addexpiresdiv = new U3A_DIV([$addexpireslbl, $addexpires], "add-news-expires-div", "add-news-div");
			$addtxtarea = new U3A_TEXTAREA("add-news-item", "add-news-item", "u3a-add-news-item-div");
			$addid = new U3A_INPUT("hidden", "members-id", "u3a-add-news-members-id", null, $mbr->id);
			$adddiv = new U3A_DIV([$addtitlediv, $addexpiresdiv, $addtxtarea, $addid], "add-news-contents-div", "u3a-invisible");
			$addnewsdiv = new U3A_DIV([$addbtn, $adddiv], "add-news-div", "u3a-add-news-div");
			$pgcontents[] = $addnewsdiv;
		}
	}
	else
	{
		$doc = U3A_Documents::get_document("01.Registration");
		if (!$doc)
		{
			$doc = U3A_Documents::get_document("01 Registration");
		}
		if ($doc)
		{
			$a = new U3A_A(wp_get_attachment_url($doc->attachment_id), "here");
			$a->add_attribute("data-popup", "true");
			$lnk = $a->to_html();
			$vidurl = U3A_Row::get_single_value("U3A_Help_Videos", "url",
				 ["name" => "registration", "category" => U3A_Help_Videos::ALLMEMBERS]);
			$vida = new U3A_A($vidurl, "here");
			$vida->add_attribute("data-popup", "true");
			$vidlnk = $vida->to_html();
			$txt = "If you are a member of $u3aname U3A and have not yet registered on this site, the instructions can be found $lnk and a video demonstration $vidlnk.";
			$div = new U3A_DIV($txt, null, "u3a-text-paragraph");
			$pgcontents[] = $div;
			$joinurl = get_permalink(U3A_Information::u3a_application_page());
			$joina = new U3A_A($joinurl, "here");
			$joinlnk = $joina->to_html();
			$jointxt = "If you want to join $u3aname U3A click $joinlnk.";
			$joindiv = new U3A_DIV($jointxt, null, "u3a-text-paragraph");
			$pgcontents[] = $joindiv;
			$pgcontents[] = U3A_HTML_Utilities::get_the_news(true, null);
			$joinbutton = new U3A_A($joinurl, "join", null, "u3a-button u3a-float-right");
			$joinbutton->add_attribute("role", "button");
			$contacturl = get_permalink(U3A_Information::u3a_contact_page());
			$contactbutton = new U3A_A($contacturl, "contact", null, "u3a-button u3a-float-left");
			$joinbutton->add_attribute("role", "button");
			$joinbuttondiv = new U3A_DIV([$contactbutton, $joinbutton], null);
//			$pgcontents .= $joinbuttondiv->to_html();
		}
	}
	$pgcontentsdiv = new U3A_DIV($pgcontents, null, "u3a-page-contents u3a-margin-bottom-10");
	$pagediv = new U3A_DIV([$pgcontentsdiv, $joinbuttondiv], "home-page-div", "u3a-home-page-class u3a-overflow-y-auto");
	return do_shortcode(U3A_HTML::to_html([$pagediv]));
}

add_shortcode("u3a_administration", "u3a_administration_contents");

function u3a_administration_contents()
{
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontents = "page not available";
	if ($mbr && U3A_Information::u3a_has_permission($mbr->get_real_member(), "site administrator"))
	{
//		$divs = [
//			new U3A_H(4, "Update from Beacon"),
//			new U3A_INPUT("hidden", "action", null, null, "u3a_update_site"),
//			new U3A_DIV(U3A_Text::get_text("update_instructions"), "u3a-update-instructions", "u3a-padding-bottom-5 u3a-border-bottom u3a-margin-bottom-5")
//		];
//		foreach (U3A_Update::$update_types as $ut)
//		{
//			$ut1 = str_replace(" ", "-", $ut);
//			$ut2 = str_replace(" ", "_", $ut);
//			$inp = new U3A_INPUT("checkbox", "update-type-$ut1", "update-type-checkbox-$ut1", "update-type-checkbox-class", $ut2);
//			$inp->add_attribute("onchange", "u3a_update_type_changed('" . $ut1 . "')");
//			$inp->add_attribute("checked", "checked");
//			$lbl = new U3A_LABEL("update-type-checkbox-$ut1", $ut, "update-type-label-$ut1", "update-type-label-class");
//			$divs[] = new U3A_DIV([$inp, $lbl], "update-type-div-$ut1", "update-type-div-class");
//		}
//		$inp = new U3A_INPUT("checkbox", "update-type-all", "update-type-checkbox-all", "update-type-checkbox-all-class", "all");
//		$inp->add_attribute("onchange", "u3a_update_type_changed('all')");
//		$inp->add_attribute("checked", "checked");
//		$lbl = new U3A_LABEL("update-type-all", "all", "update-type-label-all", "update-type-label-class");
//		$divs[] = new U3A_DIV([$inp, $lbl], "update-type-div-all", "update-type-div-class u3a-margin-top-5 u3a-margin-bottom-5");
//		$inp1 = new U3A_INPUT("checkbox", "update-delete", "update-type-checkbox-delete", "update-type-checkbox-delete-class", "delete");
//		$inp1->add_attribute("checked", "checked");
//		$lbl1 = new U3A_LABEL("update-type-all", "remove old values", "update-type-label-delete", "update-type-label-class");
//		$divs[] = new U3A_DIV([$inp1, $lbl1], "update-type-div-delete", "update-type-div-class u3a-margin-top-10 u3a-margin-bottom-5");
//		$file_input = new U3A_INPUT("file", "u3a-upload-update-file", "u3a-upload-update-file");
//		$file_input->add_attribute("accept", ".xls,.xslx,.csv");
//		$divs[] = new U3A_DIV($file_input, "update-type-div-file", "update-type-div-class u3a-margin-top-5 u3a-padding-bottom-5 u3a-margin-bottom-5");
//		$form = new U3A_FORM($divs, "/wp-admin/admin-ajax.php", "POST", "u3a-site-update-form", "update-type-form-class");
//		$form->add_attribute("enctype", "multipart/form-data");
//		$btn = new U3A_BUTTON("button", "update", "u3a-site-update-button", "u3a-button", "u3a_update_site()");
//		$btndiv = new U3A_DIV($btn, "site-update-button", "u3a-margin-top-5 u3a-border-bottom");
		$vbtn = new U3A_BUTTON("button", "update", "u3a-site-videos-button", "u3a-button", "u3a_update_videos()");
		$vh = new U3A_H(4, "Update Videos");
		$vdiv = new U3A_DIV([$vh, $vbtn], "site-videos-div", "update-type-div-class");
		$hvbtn = new U3A_BUTTON("button", "update", "u3a-site-help-videos-button", "u3a-button", "u3a_update_help_videos()");
		$hvh = new U3A_H(4, "Update Help Videos");
		$hvdiv = new U3A_DIV([$hvh, $hvbtn], "site-help-videos-div", "update-type-div-class");
		$mh = new U3A_H(4, "Send a Test Email");
		$mbtn = new U3A_BUTTON("button", "send", "u3a-site-testmail-button", "u3a-button", "u3a_testmail()");
		$mdiv = new U3A_DIV([$mh, $mbtn], "site-testmail-div", "update-type-div-class");
		$gmh = new U3A_H(4, "Send a Test Group Email");
		$gmbtn = new U3A_BUTTON("button", "send", "u3a-site-test-group-mail-button", "u3a-button", "u3a_test_group_mail()");
		$gmdiv = new U3A_DIV([$gmh, $gmbtn], "site-test-group-mail-div", "update-type-div-class");
		$idh = new U3A_H(4, "Assume an Identity");
		$assumed_value = $mbr->get_assumed_membership_number();
		$idinp = new U3A_INPUT("text", "newid", "u3a-site-assume-identity-input",
		  "u3a-site-assume-identity-input-class u3a-number-input-class", $assumed_value);
		$idlbl = new U3A_LABEL("u3a-site-assume-identity-input", "membership number:", "u3a-site-assume-identity-label",
		  "u3a-inline-block u3a-width-15-em u3a-margin-right-5");
		$idinpdiv = new U3A_DIV([$idlbl, $idinp]);
		$idbtn = new U3A_BUTTON("button", "assume", "u3a-site-assume-identity-button", "u3a-button", "u3a_assume_identity()");
		$idbtn1 = new U3A_BUTTON("button", "clear", "u3a-site-clear-identity-button", "u3a-button", "u3a_clear_identity()");
		$iddiv = new U3A_DIV([$idh, $idinpdiv, $idbtn, $idbtn1], "site-assume-identity-div", "update-type-div-class");
//		$logh = new U3A_H(4, "Download debug log");
		$newname = 'debug.log.' . date('Ymd');
		$newpath = WP_CONTENT_DIR . '/' . $newname;
		if (file_exists($newpath))
		{
			$suffix = 1;
			$newname1 = $newname . "_";
			$newname = $newname1 . $suffix;
			$newpath = WP_CONTENT_DIR . '/' . $newname;
			while (file_exists($newpath))
			{
				$suffix++;
				$newname = $newname1 . $suffix;
				$newpath = WP_CONTENT_DIR . '/' . $newname;
			}
		}
		rename(WP_CONTENT_DIR . '/debug.log', $newpath);
		file_put_contents(WP_CONTENT_DIR . '/debug.log',
		  "New log created on  " . date("d/m/Y \a\\t H:i:s") . PHP_EOL . PHP_EOL);
		$logbtn = new U3A_BUTTON("button", "download debug log", null, "u3a-wide-button");
		$loga = new U3A_A(content_url($newname), $logbtn);
		$loga->add_attribute("download", $newname);
		$loga->add_attribute("data-popup", "true");
//		$logbtn = new U3A_BUTTON("button", "download", "u3a-site-debuglog-button", "u3a-button", "u3a_download_debug_log()");
		$logdiv = new U3A_DIV($loga, "site-debuglog-div", "update-type-div-class");
//		$tmpbutton1 = new U3A_BUTTON("button", "change docs", "u3a-change-docs-button", "u3a-button", "u3a_change_documents()");
		$testmemcard = new U3A_BUTTON("button", "test membership card", null, "u3a-wide-button", "test_membership_card()");
		$testmemcarddiv = new U3A_DIV($testmemcard, "site-testmemcard-div", "update-type-div-class");
		$testml = new U3A_BUTTON("button", "test mailing lists", null, "u3a-wide-button", "test_mailing_lists()");
		$testmldiv = new U3A_DIV($testml, "site-testml-div", "update-type-div-class");
		$testal = new U3A_BUTTON("button", "test address labels", null, "u3a-wide-button", "u3a_address_labels()");
		$testaldiv = new U3A_DIV($testal, "site-testal-div", "update-type-div-class");
		$testalert = new U3A_BUTTON("button", "test alert", null, "u3a-wide-button",
		  "u3a_test_alert('Test', 'a test message', 'success')");
		$testalertdiv = new U3A_DIV($testalert, "site-testalert-div", "update-type-div-class");
		$testrp = new U3A_BUTTON("button", "test reply preference", null, "u3a-wide-button",
		  "u3a_test_reply_preference('system.test@mg.shrewsburyu3a.org.uk')");
		$testrpdiv = new U3A_DIV($testrp, "site-testreplypreference-div", "update-type-div-class");
		$json = "TAM=1";
		$testalist = new U3A_BUTTON("button", "test address list", null, "u3a-wide-button",
		  "u3a_download_address_list('$json')");
		$testalistdiv = new U3A_DIV($testalist, "site-testaddresslist-div", "update-type-div-class");
		$testbg = new U3A_BUTTON("button", "test background colour", null, "u3a-wide-button", "u3a_change_bg()");
		$testbgdiv = new U3A_DIV($testbg, "site-background-div", "update-type-div-class");
		$rnwbtn = new U3A_BUTTON("button", "renewals needed", null, "u3a-wide-button", "u3a_renewals_needed()");
		$rnwbtndiv = new U3A_DIV($rnwbtn, "renewal-needed-div", "update-type-div-class");
		$lpsbtn = new U3A_BUTTON("button", "lapse members", null, "u3a-wide-button", "u3a_lapse_members()");
		$lpsbtndiv = new U3A_DIV($lpsbtn, "lapse-members-div", "update-type-div-class");
		$cwpbtn = new U3A_BUTTON("button", "check wpid", null, "u3a-wide-button", "u3a_check_wpid()");
		$cwpbtndiv = new U3A_DIV($cwpbtn, "check-wpid-div", "update-type-div-class");
		if (U3A_Information::u3a_management_enabled())
		{
			$mngenbtn = new U3A_BUTTON("button", "disable management", null, "u3a-wide-button",
			  "u3a_set_option('enable_management', 'no')");
		}
		else
		{
			$mngenbtn = new U3A_BUTTON("button", "enable management", null, "u3a-wide-button",
			  "u3a_set_option('enable_management', 'yes')");
		}
		$mngenbtndiv = new U3A_DIV($mngenbtn, "site-enable-management-div", "update-type-div-class");
		$pgcontents = U3A_HTML::to_html([/* $form, $btndiv, */$vdiv, $hvdiv, $mdiv, $gmdiv, $iddiv, $logdiv, $testmemcarddiv, $testmldiv, $testaldiv,
			  $testalertdiv, $testrpdiv, $testalistdiv, $mngenbtndiv, $testbgdiv, $rnwbtndiv, $lpsbtndiv, $cwpbtndiv]);
	}
	return $pgcontents;
}

add_shortcode("u3a_contact_page", "u3a_contact_page_contents");

function u3a_contact_page_contents($atts1)
{
	$atts = shortcode_atts(array(
		'to'		 => "secretary",
		"subject" => "enquiry"
	  ), $atts1, 'u3a_contact_page');
	$maildiv = U3A_HTML_Utilities::get_mail_contents_div(0, "contact", $atts["to"], "u3a-height-100-pc u3a-width-100-pc",
		 0, 0);
	return $maildiv->to_html();
}

add_shortcode("u3a_newsletter_page", "u3a_newsletter_page_contents");

function u3a_newsletter_page_contents($atts)
{
	$pgcontents = "";
	$mbr = U3A_Information::u3a_logged_in_user();
	if (U3A_Information::u3a_has_permission($mbr, "print labels"))
	{
		$plbtn = new U3A_BUTTON("button", "print address labels", null, "u3a-wide-button", "u3a_address_labels()");
		$pgcontents .= $plbtn->to_html();
	}
	$pgcontents .= '[u3a_document_list group="0" type="2"]';
	return do_shortcode($pgcontents);
}

add_shortcode("u3a_allmembers", "u3a_allmembers_contents");

function u3a_allmembers_contents($atts1)
{
	$atts = shortcode_atts(array(), $atts1, 'u3a_allmembers');
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontent = "page not available";
	$ctee = U3A_Committee::is_committee_member($mbr);
	if ($ctee)
	{
//		$ctee = U
		$pgcontent = '[su_tabs style="wood"]\n';
		$pgcontent .= '[su_tab title="Search" disabled="no" anchor="" url="" target="blank" class=""]';
		$pgcontent .= '[u3a_view_member]';
		$pgcontent .= "[/su_tab]\n";
		$pgcontent .= '[su_tab title="List" disabled="no" anchor="" url="" target="blank" class=""]';
		$pgcontent .= '[u3a_list_all_members]';
		$pgcontent .= "[/su_tab]\n";
		$pgcontent .= '[su_tab title="Groups" disabled="no" anchor="" url="" target="blank" class=""]';
		$pgcontent .= '[u3a_list_members_in_groups]';
		$pgcontent .= "[/su_tab]\n";
		$div1 = new U3A_DIV('[u3a_members group="email" checked="yes"]', "u3a-all-members-email-members-list",
		  "u3a-inline-block u3a-width-30-pc u3a-height-100-pc u3a-va-top");
		$ndocs = U3A_Row::count_rows("U3A_Documents",
			 ["groups_id" => 0, "document_type" => U3A_Documents::PRIVATE_DOCUMENT_TYPE]);
		$nimgs = U3A_Row::count_rows("U3A_Documents",
			 ["groups_id" => 0, "document_type" => U3A_Documents::COMMITTEE_IMAGE_TYPE]);
//		write_log($mbr, $ctee);
		$emaildiv = U3A_HTML_Utilities::get_mail_contents_div($mbr->id . "+" . $ctee->id, "individual", 0,
			 "u3a-inline-block u3a-width-70-pc u3a-height-100-pc u3a-va-top", $ndocs, $nimgs);
		$div = new U3A_DIV([$div1, $emaildiv], "u3a-all-members-email-div", "u3a-width-100-pc u3a-height-100-pc");
		$pgcontent .= '[su_tab title="Email" disabled="no" anchor="" url="" target="blank" class=""]';
		$pgcontent .= $div->to_html();
		$pgcontent .= "[/su_tab]\n";
		$pgcontent .= '[/su_tabs]';
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_list_all_members", "u3a_list_all_members_contents");

function u3a_list_all_members_contents($atts1)
{
//	$atts = shortcode_atts(array(), $atts1, 'u3a_list_all_members');
	$mbr = U3A_Information::u3a_logged_in_user();
	write_log($mbr);
	$pgcontent = "page not available";
	if (U3A_Committee::is_committee_member($mbr))
	{
		$pgcontent = "";
		$links = [];
		foreach (U3A_HTML_Utilities::$alphabet_upper as $letter)
		{
			$links[] = new U3A_A('#', $letter, "u3a-member-initial-" . $letter,
			  "u3a-member-initial-letter-class u3a-inline-block u3a-margin-right-2",
			  "u3a_initial_letter_clicked('" . $letter . "')");
		}
		$links[] = new U3A_A('#', "all", "u3a-member-initial-all",
		  "u3a-member-initial-letter-class u3a-inline-block u3a-margin-left-10", "u3a_initial_letter_clicked('all')");
		$div = new U3A_DIV($links, "u3a-initials-div", "u3a-margin-bottom-5");
		$details = new U3A_DIV(null, "u3a-all-members-details", "u3a-padding-left-5");
		$lhs = new U3A_DIV([$div, $details], null, "u3a-inline-block u3a-width-60-pc u3a-all-members-div-class");
		$filter_heading = new U3A_H(6, "Filters");
		$tam = U3A_HTML::labelled_html_object("Third Age Matters",
			 new U3A_DIV(U3A_INPUT::yes_no_ignore_radio_array("TAM"), null, "u3a-inline-block"), null, "u3a-width-50-pc", false,
			 true, null);
		$nl = U3A_HTML::labelled_html_object("posted newsletter",
			 new U3A_DIV(U3A_INPUT::yes_no_ignore_radio_array("newsletter"), null, "u3a-inline-block"), null, "u3a-width-50-pc",
			 false, true, null);
		$ga = U3A_HTML::labelled_html_object("gift aid",
			 new U3A_DIV(U3A_INPUT::yes_no_ignore_radio_array("gift_aid"), null, "u3a-inline-block"), null, "u3a-width-50-pc",
			 false, true, null);
		$labval = [
			"Cheque"		 => "Cheque",
			"Cash"		 => "Cash",
			"PayPal"		 => "PayPal",
			"CreditCard" => "CreditCard"
		];
		$pt = U3A_HTML::labelled_html_object("payment type",
			 new U3A_DIV(U3A_INPUT::checkbox_array("payment_type", $labval, false, "u3a-width-8-em"), null, "u3a-inline-block"),
			 null, "u3a-width-50-pc", false, true, null);
		$em = U3A_HTML::labelled_html_object("email address",
			 new U3A_DIV(U3A_INPUT::yes_no_ignore_value_radio_array("email"), null, "u3a-inline-block"), null, "u3a-width-50-pc",
			 false, true, null);
		$colclose = new U3A_A('#', "OK", 'u3a_all_members_a', null, "u3a_col_close();");
		$colclose->add_attribute("rel", "modal:close");
		$cols = [];
		foreach (U3A_Members::$display_column_names as $label => $colname)
		{
			if (is_array($colname))
			{
				$def = $colname["default"];
				$cname = $colname[$def];
				$definp = new U3A_INPUT("hidden", null, "u3a-members-default-value-" . $cname, null, $cname);
				$divid = "u3a-members-div-" . $cname;
				$inp = new U3A_INPUT("checkbox", $cname, "u3a-members-" . $cname, "u3a-members-column-checkbox-class", $cname);
				$inp->add_attribute("onchange", "u3a_outer_clicked('u3a-members-', '$cname')");
				$lbl = U3A_HTML::labelled_html_object($label, $inp, "u3a-members-label-" . $cname,
					 "u3a-member-column-label-class u3a-label-class", false, false);
				$btnup = new U3A_A('#', '<span class="dashicons dashicons-arrow-up-alt2"></span>', "u3a-members-column-$cname-up",
				  "u3a-invisible", "u3a_toggle_up_down('u3a-members-column-$cname-', 'up', '$divid')");
				$btndown = new U3A_A("#", '<span class="dashicons dashicons-arrow-down-alt2"></span>',
				  "u3a-members-column-$cname-down", "", "u3a_toggle_up_down('u3a-members-column-$cname-', 'down', '$divid')");
				$cols[] = new U3A_DIV([$lbl, $btnup, $btndown], null, "u3a-padding-bottom-5 u3a-margin-bottom-5");
				$sub = [];
				foreach ($colname as $label1 => $colname1)
				{
					if ($label1 !== 'default')
					{
						$inp1 = new U3A_INPUT("checkbox", $colname1, "u3a-members-sub-" . $colname1, "u3a-members-column-checkbox-class",
						  $cname . '/' . $colname1);
						$inp1->add_attribute("onchange", "u3a_inner_clicked('u3a-members-', '$cname')");
						$lbl1 = U3A_HTML::labelled_html_object($label1, $inp1, "u3a-members-label-" . $colname1,
							 "u3a-member-column-label-class u3a-label-class", false, true);
						$sub[] = $lbl1;
					}
				}
				$cols[] = new U3A_DIV([$definp, $sub], $divid, "u3a-padding-left-10 u3a-invisible");
			}
			else
			{
				$inp = new U3A_INPUT("checkbox", $colname, "u3a-members-" . $colname, "u3a-members-column-checkbox-class", $colname);
				$cols[] = U3A_HTML::labelled_html_object($label, $inp, "u3a-members-label-" . $colname,
					 "u3a-member-column-label-class u3a-label-class", false, true);
			}
		}
		$display_heading = new U3A_H(6, "Display");
		$open = new U3A_A('#u3a-member-select-div', "Select columns", 'u3a-member-select-a', "u3a-inline-block");
		$open->add_attribute("rel", "modal:open");
//		$coldiv = new U3A_DIV([$cols, $colclose], "u3a-member-select-div", "u3a-member-select-div-class u3a-height-60-pc u3a-overflow-y-auto");
		$coldiv1 = new U3A_DIV([$cols, $colclose], null, "u3a-member-select-div-class u3a-overflow-y-auto u3a-height-100-pc");
		$coldiv = new U3A_DIV($coldiv1, "u3a-member-select-div", "u3a-padding-5 modal u3a-height-60-pc");
		$sortclose = new U3A_A('#', "OK", 'u3a_sort_columns_a', null, "u3a_sort_columns_close()");
		$sortclose->add_attribute("rel", "modal:close");
		$sortlist = new U3A_LIST(null, false, "u3a-members-column-sort-list", "u3a-sort-list");
		$sorthdr = new U3A_H(6, "sort columns into required order, then press OK");
		$sortdiv1 = new U3A_DIV([$sorthdr, $sortlist], "u3a-members-column-sort-list-container",
		  "u3a-member-column-div-class u3a-height-90-pc  u3a-overflow-y-auto u3a-margin-bottom-10");
		$sortdiv = new U3A_DIV([$sortdiv1, $sortclose], "u3a-members-column-sort-list-outer",
		  "u3a-padding-5 modal u3a-height-40-pc");
		$btn_display = new U3A_BUTTON("button", "display table", "u3a-members-display-table", "u3a-mid-button",
		  "u3a_members_display_table()");
		$btn_display->add_attribute("disabled", "disabled");
		$btn_csv = new U3A_BUTTON("button", "download csv", "u3a-members-download-csv", "u3a-mid-button u3a-margin-left-5",
		  "u3a_members_download('csv')");
		$btn_csv->add_attribute("disabled", "disabled");
		$btn_xls = new U3A_BUTTON("button", "download xlsx", "u3a-members-download-xlsx", "u3a-mid-button u3a-margin-left-5",
		  "u3a_members_download('xlsx')");
		$btn_xls->add_attribute("disabled", "disabled");
		$display_page = new U3A_INPUT("hidden", null, "u3a-display-members-page-link", null,
		  get_permalink(U3A_Information::u3a_members_display_page()));
		$after_close = new U3A_INPUT("hidden", null, "u3a-display-members-after-close", null, "table");
		$btndiv = new U3A_DIV([$display_page, $after_close, $btn_display, $btn_csv, $btn_xls], null, "u3a-margin-top-5");
		$display_div = new U3A_DIV([$display_heading, $open, $btndiv], "u3a-members-display-div", "u3a-invisible");
		$rhs = new U3A_DIV([$filter_heading, $tam, $nl, $ga, $pt, $em, $display_div, $coldiv, $sortdiv],
		  "u3a-all-members-filter",
		  "u3a-border-left u3a-inline-block u3a-width-40-pc u3a-padding-left-5 u3a-all-members-div-class");
		$pgcontent .= U3A_HTML::to_html([$lhs, $rhs]);
	}
	return $pgcontent;
}

add_shortcode("u3a_list_members_in_groups", "u3a_list_members_in_groups_contents");

function u3a_list_members_in_groups_contents($atts1)
{
	$atts = shortcode_atts(array(), $atts1, 'u3a_list_members_in_groups');
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontent = "page not available";
	if (U3A_Committee::is_committee_member($mbr))
	{
		$pgcontent = "";
		$grps1 = U3A_Row::load_array_of_objects("U3A_Groups", null, "name");
		$grps = $grps1["result"];
		$pgcontent .= "[su_accordion]\n";
		foreach ($grps as $grp)
		{
// add group entry
			$pgcontent .= '[su_spoiler title="' . $grp->name . '" style="fabric" icon="arrow-circle-1"]';
			$mbrdiv = new U3A_DIV('[u3a_members group="' . $grp->id . '" checked="no"]', "u3a-group-members-div-" . $grp->id,
			  "u3a-member-list-class");
			$pgcontent .= $mbrdiv->to_html();
			$pgcontent .= "[/su_spoiler]\n";
		}
		$pgcontent .= "[/su_accordion]\n";
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_help_page", "u3a_help_page_contents");

function u3a_help_page_contents($atts1)
{
	$atts = shortcode_atts(array(), $atts1, 'u3a_help_page');
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontent = "";
	if (U3A_Committee::is_committee_member($mbr) || U3A_Group_Members::is_a_coordinator($mbr))
	{
		$pgcontent .= '[su_tabs style="wood"]\n';
		$cat1 = U3A_Document_Categories::get_category_id("Website User Guide", U3A_Documents::USERGUIDE_DOCUMENT_TYPE);
		$cat2 = U3A_Document_Categories::get_category_id("Website User Guide",
			 U3A_Documents::USERGUIDE_COORDINATORS_DOCUMENT_TYPE);
		$cat3 = U3A_Document_Categories::get_category_id("Website User Guide",
			 U3A_Documents::USERGUIDE_COMMITTEE_DOCUMENT_TYPE);
		$docs1 = '[u3a_document_list group="0" type="' . U3A_Documents::USERGUIDE_DOCUMENT_TYPE . '"' . ' category="' . $cat1 . '"]';
		$docs2 = '[u3a_document_list group="0" type="' . U3A_Documents::USERGUIDE_COORDINATORS_DOCUMENT_TYPE . '"' . ' category="' . $cat2 . '"]';
		$docs3 = '[u3a_document_list group="0" type="' . U3A_Documents::USERGUIDE_COMMITTEE_DOCUMENT_TYPE . '"' . ' category="' . $cat3 . '"]';
//		write_log($docs1);
//		write_log($docs2);
//		write_log($docs3);
		$pgcontent .= '[su_tab title="Members" disabled="no" anchor="" url="" target="blank" class=""]' . $docs1 . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Coordinators" disabled="no" anchor="" url="" target="blank" class=""]' . $docs2 . "\n[/su_tab]\n";
		if (U3A_Committee::is_committee_member($mbr))
		{
			$pgcontent .= '[su_tab title="Committee" disabled="no" anchor="" url="" target="blank" class=""]' . $docs3 . "\n[/su_tab]\n";
		}
		$pgcontent .= "[/su_tabs]\n";
	}
	else
	{
		$pgcontent .= '[u3a_document_list group="0" type="' . U3A_Documents::USERGUIDE_DOCUMENT_TYPE . '"]';
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_help_videos_page", "u3a_help_videos_page_contents");

function u3a_help_videos_page_contents($atts1)
{
	$atts = shortcode_atts(array(), $atts1, 'u3a_help_page');
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontent = "";
	$vids_members = '[u3a_help_videos category="' . U3A_Help_Videos::ALLMEMBERS . '"]';
	if (U3A_Committee::is_committee_member($mbr) || U3A_Group_Members::is_a_coordinator($mbr))
	{
		$pgcontent .= '[su_tabs style="wood"]\n';
		$vids_coordinators = '[u3a_help_videos category="' . U3A_Help_Videos::COORDINATORS . '"]';
		$pgcontent .= '[su_tab title="Members" disabled="no" anchor="" url="" target="blank" class=""]' . $vids_members . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Coordinators" disabled="no" anchor="" url="" target="blank" class=""]' . $vids_coordinators . "\n[/su_tab]\n";
		if (U3A_Committee::is_committee_member($mbr))
		{
			$vids_committee = '[u3a_help_videos category="' . U3A_Help_Videos::COMMITTEE . '"]';
			$pgcontent .= '[su_tab title="Committee" disabled="no" anchor="" url="" target="blank" class=""]' . $vids_committee . "\n[/su_tab]\n";
		}
		$pgcontent .= "[/su_tabs]\n";
	}
	else
	{
		$pgcontent .= $vids_members;
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_payment_return", "u3a_payment_return_contents");

function u3a_payment_return_contents($atts)
{
	$hdr = new U3A_H(4, "Payment Return");
	$txt = new U3A_DIV(json_encode($_POST), "u3a-payment-return", "u3a-payment-return-class");
	return U3A_HTML::to_html([$hdr, $txt]);
}

add_shortcode("u3a_payment_complete", "u3a_payment_complete_contents");

function u3a_payment_complete_contents($atts)
{
	$hdr = new U3A_H(4, "Payment Complete");
	$txt = new U3A_DIV(json_encode($_POST), "u3a-payment-complete", "u3a-payment-complete-class");
	return U3A_HTML::to_html([$hdr, $txt]);
}

add_shortcode("u3a_payment_void", "u3a_payment_void_contents");

function u3a_payment_void_contents($atts)
{
	$hdr = new U3A_H(4, "Payment Void");
	$txt = new U3A_DIV(json_encode($_POST), "u3a-payment-void", "u3a-payment-void-class");
	return U3A_HTML::to_html([$hdr, $txt]);
}

add_shortcode("u3a_monthly_meeting", "u3a_monthly_meeting_contents");

function u3a_monthly_meeting_contents($atts)
{
//	$meetings = U3A_Row::load_array_of_objects("U3A_Meetings", ["day>" => date('Y-m-d')], "day");
//	$contents = "";
//	if ($meetings["total_number_of_rows"])
//	{
//		$next1 = $meetings["result"][0];
//		$subject = new U3A_B($next1->subject);
//		if ($next1->speaker)
//		{
//			$speaker = new U3A_B($next1->speaker);
//			$contents .= "At the next meeting " . $speaker->to_html() . ' will talk about "' . $subject->to_html() . '".';
//		}
//		else
//		{
//			$contents .= "The next meeting will be the " . $subject->to_html();
//		}
//		if ($next1->notes)
//		{
//			$contents .= " " . $next1->notes;
//		}
//	}
//	return U3A_HTML::to_html(new U3A_DIV($contents, null, "u3a-meeting-div-class"));
	$h = new U3A_H(6, "Monthly meetings cancelled until further notice.");
	return $h->to_html();
}

add_shortcode("u3a_application", "u3a_application_contents");

function u3a_application_contents($atts)
{
	$pgcontents = do_shortcode('[u3a_member_details_form op="join"]');
	$rate = new U3A_INPUT("hidden", "subscription-rate", "u3a-subscription-rate", null,
	  U3A_Information::u3a_get_current_join_rate());
	$arate = new U3A_INPUT("hidden", "associate-subscription-rate", "u3a-associate-subscription-rate", null,
	  U3A_Information::u3a_get_current_join_rate(true));
	$ppaction = new U3A_INPUT("hidden", null, "u3a-paypal-action", null, "join");
	$pgcontents .= $ppaction->to_html();
	$pgcontents .= $rate->to_html();
	$pgcontents .= $arate->to_html();
	$text = new U3A_DIV("You can pay with a credit card via PayPal, you do not need to have a PayPal account.",
	  "u3a-paypal-text", "u3a-paypal-text-class u3a-invisible");
	$ppdiv = new U3A_DIV(null, "u3a-paypal-container-join", "u3a-paypal-container u3a-invisible");
	$pgcontents .= $text->to_html();
	$pgcontents .= $ppdiv->to_html();
	return $pgcontents;
}

add_shortcode("u3a_permalink", "u3a_permalink_contents");

function u3a_permalink_contents($atts1)
{
	$atts = shortcode_atts(array(
		'page'	 => "application",
		'title'	 => "Application Form"
	  ), $atts1, 'u3a_permalink');
	$method = "u3a_" . $atts["page"] . "_page";
	$pgid = U3A_Information::$method();
	$title = $atts["title"] ? (' title="' . $atts["title"] . '"') : "";
	return do_shortcode('[su_permalink id="' . $pgid . '"' . $title . ']');
}

add_shortcode("u3a_urgent", "u3a_urgent_contents");

function u3a_urgent_contents($atts1)
{
	return do_shortcode('[u3a_text name="urgent"]');
}

add_shortcode("u3a_display_members_table", "u3a_display_members_table_contents");

function u3a_display_members_table_contents($atts1)
{
	$members = U3A_Utilities::get_post("members", null);
	$columns = U3A_Utilities::get_post("columns", null);
	$headers = U3A_Utilities::get_post("headers", null);
	$tbl_contents = [];
	if ($columns)
	{
		$cols = explode(",", $columns);
		if ($headers)
		{
			$hdrs = explode(",", $headers);
			if (count($hdrs) === count($cols))
			{
				$head = [];
				foreach ($hdrs as $h)
				{
					$head[] = new U3A_TH($h, null, "u3a-members-table-element u3a-members-table-header-element");
				}
				$hrow = new U3A_TR($head, null, "u3a-members-table-row u3a-members-table-header-row");
				$tbl_contents[] = new U3A_THEAD($hrow, null, "u3a-members-table-head");
			}
		}
		if ($members)
		{
			$mbrs = explode(",", $members);
			$rows = [];
			foreach ($mbrs as $members_id)
			{
				$mbr = U3A_Members::get_member($members_id);
				$trow = [];
				foreach ($cols as $col)
				{
					$trow[] = new U3A_TD($mbr->$col, null, "u3a-members-table-element u3a-members-table-body-element");
				}
				$rows[] = new U3A_TR($trow, null, "u3a-members-table-row u3a-members-table-body-row");
			}
			$tbl_contents[] = new U3A_TBODY($rows, null, "u3a-members-table-body");
		}
	}
	$table = new U3A_TABLE($tbl_contents, "u3a-members-display-table", "u3a-members-display-table-class");
	$tab = do_shortcode('[su_table responsive="yes" alternate="yes"]' . U3A_HTML::to_html($table) . "[/su_table]");
	$div = new U3A_DIV($tab, null, "u3a-50vh-auto-y");
	return $div->to_html();
}

add_shortcode("u3a_members_personal", "u3a_members_personal_contents");

function u3a_members_personal_contents($atts1)
{
	wp_enqueue_editor();
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontent = null;
	if ($mbr)
	{
//		write_log("wpid " . $mbr->get_wpid());
//		write_log("email " . $mbr->email);
//		write_log("forename " . $mbr->forename);
//		write_log("surname " . $mbr->surname);
//		write_log(um_get_avatar('', $mbr->get_wpid()));
		$default_member = U3A_Utilities::get_post("member", $mbr->membership_number);
		$default_manage = U3A_Utilities::get_post("manage", 'no');
		$atts = shortcode_atts(array(
			'member'		 => $default_member,
			'manage'		 => $default_manage,
			'spoiler'	 => null,
			'category'	 => 0
		  ), $atts1, 'u3a_members_personal');
//		write_log($atts, $default_members_id);
//		$profile = $atts["profile"];
//		$inprofile = $profile === 'yes';
		$manage = $atts["manage"];
		$active1 = 1;
		$isme = intval($atts["member"]) === intval($mbr->membership_number);
		$member = $isme ? $mbr : U3A_Members::get_member_from_membership_number($atts["member"]);
		$member_id = new U3A_INPUT("hidden", "member-id", "u3a-member-personal-page-id", null, $member->id);
		$member_firstname = new U3A_INPUT("hidden", "member-firstname", "u3a-member-personal-page-firstname", null,
		  $member->get_first_name());
		$member_surname = new U3A_INPUT("hidden", "member-surname", "u3a-member-personal-page-surname", null, $member->surname);
		$member_number = new U3A_INPUT("hidden", "member-number", "u3a-member-personal-page-number", null,
		  $member->membership_number);
		$pgcontent = U3A_HTML::to_html([$member_id, $member_firstname, $member_surname, $member_number]);
		$imgs1 = U3A_Documents::get_header_images(-1, $mbr);
		$imgs = $imgs1["images"];
		$categories_id = $imgs1["categories_id"];
		if ($imgs)
		{
			$ndx = rand(0, count($imgs) - 1);
			$src = wp_get_attachment_url($imgs[$ndx]);
//<figure class="wp-block-image"><img src="http://shrewsburyu3a.website/wp-content/uploads/2019/01/shrewsbury-u3a-banner.jpg" alt="" class="wp-image-25"/></figure>
			$img = new U3A_IMG($src, null, "wp-image-25 u3a-header-image", "change_header_image(1)",
			  "images for " . $member->get_name());
			$img->add_attribute("title", get_the_title($imgs[$ndx]));
			$fig = new U3A_FIGURE($img, null, "wp-block-image");
			$type_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-type", U3A_Documents::PERSONAL_IMAGE_TYPE);
			$cat_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-category", $categories_id);
			$ndx_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-index", $ndx);
			$total_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-total", count($imgs));
			$group_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-group", -1);
			$mbr_val = new U3A_INPUT("hidden", null, null, "u3a-home-image-member", $mbr->id);
			$pgcontent .= $fig->to_html();
		}
		$pgcontent .= '[su_tabs style="wood" active="' . $active1 . '"]\n';
		$pgcontent .= '[su_tab title="About" disabled="no" anchor="" url="" target="blank" class=""]';
		$info = $member->get_information();
//		write_log("2.info", $info);
		if (($manage === 'yes') && $isme)
		{
			if (!$info)
			{
				$info = "Please enter some information about yourself here.";
			}
			ob_start();
			wp_editor($info, "u3a-member-personal-page-text", ["default_editor" => "tinymce"]);
			$pgcontent .= ob_get_clean();
			$updatebtn = new U3A_BUTTON("button", "update", "u3a-member-personal-page-update", "u3a-button u3a-margin-right-5",
			  "u3a_update_information('" . $member->id . "')");
			$pgcontent .= $updatebtn->to_html();
		}
		else
		{
			if ($info === null)
			{
				$info = '';
			}
			$ta = new U3A_DIV($info, "u3a-member-personal-page-text", "u3a-member-personal-page-text-class");
//			$ta = new U3A_TEXTAREA("member-personal-page-text", "u3a-member-personal-page-text", null, $info);
//			$ta->add_attribute("readonly", "readonly");
			$pgcontent .= $ta->to_html();
		}
		if ($isme)
		{
			if (($atts["manage"] === 'yes'))
			{
				$lbl = "done";
				$manage = 'no';
			}
			else
			{
				$lbl = "edit";
				$manage = 'yes';
			}
			$editbtn = new U3A_BUTTON("button", $lbl, "u3a-member-personal-page-edit", "u3a-button",
			  "u3a_refresh_personal_page('" . $member->membership_number . "', '$manage')");
			$pgcontent .= $editbtn->to_html();
		}
		$pgcontent .= "[/su_tab]";
		$docs = '[u3a_document_list member="' . $member->id . '" type="' . U3A_Documents::PERSONAL_DOCUMENT_TYPE . '"]';
		$gall = '[u3a_image_list member="' . $member->id . '" type="' . U3A_Documents::PERSONAL_IMAGE_TYPE . '"]';
		$friends = $member->get_friends();
		$fdivs = [];
		if ($friends)
		{
			foreach ($friends as $friend)
			{
				$fbtn = new U3A_BUTTON("button", '<span class="dashicons dashicons-no"></span>', "u3a-remove-friend-button",
				  "u3a-friend-button-class u3a-very-narrow-button u3a-margin-right-5");
				$fbtn->add_attribute("onclick", "u3a_remove_friend_clicked(" . $mbr->id . ", " . $friend->id . ")");
				$fbtn->add_attribute("title", "remove friend");
				$fa = new U3A_A(home_url() . "/index.php/members-personal/?member=" . $friend->membership_number,
				  $friend->get_name(), "u3a-friend-link-" . $friend->id, "u3a-friend-link");
				$fdivs[] = new U3A_DIV([$fbtn, $fa], "u3a-back-to-my-page-link-div-" . $friend->id, "u3a-link-div-class");
			}
			$fdivs[count($fdivs) - 1]->add_class("u3a-border-bottom u3a-margin-bottom-5");
		}
		$goto = '[u3a_goto_member]';
		$my_groups = U3A_Group_Members::get_groups_for_member($member);
		$groups = "";
		if (count($my_groups))
		{
			$grp_pages = U3A_Information::u3a_group_pages($my_groups);
			$groups .= "<h4>My Groups</h4>";
			foreach ($my_groups as $mgrp)
			{
				$newpgid = $grp_pages[$mgrp->name];
				$groups .= '<p class="u3a-group-link">[su_permalink id="' . $newpgid . '"]</p>';
			}
		}
		$pgcontent .= '[su_tab title="Groups" disabled="no" anchor="" url="" target="blank" class=""]' . $groups . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Documents" disabled="no" anchor="" url="" target="blank" class=""]' . $docs . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Gallery" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $gall . "\n[/su_tab]\n";
		$pgcontent .= '[su_tab title="Friends" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . U3A_HTML::to_html($fdivs) . $goto . "\n[/su_tab]\n";
		$links = new U3A_DIV('[u3a_links group="0" member="' . $member->id . '"]', null, "u3a-links-div u3a-overflow-y-auto");
		$pgcontent .= '[su_tab title="Links" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $links . "\n[/su_tab]\n";

		if (($manage === 'yes') && $isme)
		{
			$mng = "[su_accordion]\n";
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Membership Details", $atts["spoiler"]);
			$mng .= '[u3a_member_details_form member="' . $member->id . '" op="selfedit"]';
			$mng .= "[/su_spoiler]\n";
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Categories", $atts["spoiler"]);
			$mng .= '[u3a_new_document_category member="' . $member->id . '"]';
			$mng .= "[/su_spoiler]\n";
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Images", $atts["spoiler"]);
			$mng .= '[u3a_manage_document member="' . $member->id . '" type="' . U3A_Documents::PERSONAL_IMAGE_TYPE . '" category="' . $atts["category"] . '"]';
			$mng .= "[/su_spoiler]\n";
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Documents", $atts["spoiler"]);
			$mng .= '[u3a_manage_document member="' . $member->id . '" type="' . U3A_Documents::PERSONAL_DOCUMENT_TYPE . '" category="' . $atts["category"] . '"]';
			$mng .= "[/su_spoiler]\n";
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Links", $atts["spoiler"]);
			$div = new U3A_DIV('[u3a_manage_links group="0" member="' . $member->id . '"]', null, "u3a-manage-links-div");
			$mng .= $div->to_html();
			$mng .= "[/su_spoiler]\n";
			$mng .= U3A_Information::get_manage_open_spoiler("Manage Options", $atts["spoiler"]);
			$optdiv = U3A_Option_Utilities::get_option_select(U3A_Options::OPTION_CATEGORY_MEMBER, $member->id);
			$mng .= $optdiv->to_html();
			$mng .= "[/su_spoiler]\n";
			$mng .= "[/su_accordion]\n";
			$mcardbtn = new U3A_BUTTON("button", "Get Membership Card", "u3a-get-card-button", "u3a-very-wide-button",
			  "u3a_get_card(" . $member->id . ")");
			$mcardbtndiv = new U3A_DIV($mcardbtn, "u3a-get-card-button-div", "u3a-get-card-button-div-class");
			$mng .= $mcardbtndiv;
			$pgcontent .= '[su_tab title="Manage" disabled="no" anchor="" url="" target="blank" class=""]' . "\n" . $mng . "\n[/su_tab]\n";
		}
		$pgcontent .= "[/su_tabs]\n";
		if (!$isme)
		{
			$email = U3A_Utilities::strip_all_slashes($member->email);
			if ($email)
			{
				$email_link = new U3A_A("mailto:" . $email, "send email to " . $member->get_first_name(),
				  "u3a-personal-email-" . $member->id, "u3a-personal-email u3a-wide-button u3a-border");
				$email_link->add_attribute("role", "button");
				$pgcontent .= $email_link;
			}
			$friend_link = new U3A_A("#", "add " . $member->get_first_name() . " as a friend", "u3a-add-friend-" . $member->id,
			  "u3a-wide-button u3a-margin-left-10 u3a-border", "u3a_add_friend(" . $mbr->id . ", " . $member->id . ")");
			$friend_link->add_attribute("role", "button");
			$back_link = new U3A_A(home_url() . "/index.php/members-personal/?member=" . $mbr->membership_number,
			  "Back to My Page", "u3a-return-to-my-page-" . $member->id, "u3a-wide-button u3a-margin-left-10 u3a-border",
			  "u3a_reload_member_page(" . $mbr->id . ");");
			$pgcontent .= $friend_link;
			$pgcontent .= $back_link;
		}
	}
	return do_shortcode($pgcontent);
}

add_shortcode("u3a_manage_links", "u3a_manage_links_contents");

function u3a_manage_links_contents($atts1)
{
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontents = null;
	if ($mbr)
	{
		$default_member = U3A_Utilities::get_post("member", 0);
		$default_group = U3A_Utilities::get_post("group", 0);
		$atts = shortcode_atts(array(
			'member'	 => $default_member,
			'group'	 => $default_group
		  ), $atts1, 'u3a_manage_links');
		$groups_id = $atts["group"];
		$members_id = $atts["member"];
		if (($groups_id > 0 /* && U3A_Group_Members::is_coordinator($members_id, $groups_id) */) || ($members_id == $mbr->id) || (!$groups_id && !$members_id && U3A_Committee::is_committee_member($mbr)))
		{
			$sbtn = new U3A_BUTTON("button", "new link section", "u3a-new-link-section-button-$groups_id-$members_id",
			  "u3a-wide-button", "u3a_new_link_section($groups_id, $members_id)");
			$sbtn_div = new U3A_DIV($sbtn, null, "u3a-new-link-section-div");
			$lbtn = new U3A_BUTTON("button", "new link in ", "u3a-new-link-button-$groups_id-$members_id", "u3a-wide-button",
			  "u3a_new_link($groups_id, $members_id)");
			$lbtn->add_attribute("disabled", "disabled");
			$sel = U3A_Link_Utilities::get_section_select($groups_id, $members_id);
			$lbtn_div = new U3A_DIV([$lbtn, $sel], null, "u3a-new-link-section-div");
			$pgcontents = U3A_HTML::to_html([$sbtn_div, $lbtn_div]);
		}
	}
	return $pgcontents;
	//		$div = new U3A_DIV([], null, "u3a-manage-links-div");
}

add_shortcode("u3a_links", "u3a_links_contents");

function u3a_links_contents($atts1)
{
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontents = null;
	if ($mbr)
	{
		$default_member = U3A_Utilities::get_post("member", 0);
		$default_group = U3A_Utilities::get_post("group", 0);
		$atts = shortcode_atts(array(
			'member'	 => $default_member,
			'group'	 => $default_group
		  ), $atts1, 'u3a_manage_links');
		$groups_id = $atts["group"];
		$members_id = $atts["member"];
		$pgcontents = U3A_HTML::to_html(U3A_Link_Utilities::get_section_divs($groups_id, $members_id));
	}
	return $pgcontents;
}

add_shortcode("u3a_sent_mails", "u3a_sent_mails_contents");

function u3a_sent_mails_contents($atts1)
{
	$atts = shortcode_atts(array(
		'to'		 => 0,
		'from'	 => 0,
		'group'	 => 0
	  ), $atts1, 'u3a_sent_mails');
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontents = null;
	$groups_id = intval($atts["group"]);
	$is_committee = $groups_id === 0;
	$is_coordinators = $groups_id === -1;
	if ($mbr && (U3A_Committee::is_webmanager($mbr) || ($is_committee && U3A_Committee::is_committee_member($mbr)) || (!$is_committee && U3A_Group_Members::is_coordinator($mbr,
		 $groups_id))))
	{

	}
	return $pgcontents;
}

add_shortcode("u3a_manage_venues", "u3a_manage_venues_contents");

function u3a_manage_venues_contents($atts1)
{
	$atts = shortcode_atts(array(
		'venue' => 0
	  ), $atts1, 'u3a_manage_venues');
	$mbr = U3A_Information::u3a_logged_in_user();
	$pgcontents = "";
	if ($mbr && U3A_Committee::is_committee_member($mbr))
	{
//		$vn = U3A_Row::load_array_of_objects("U3A_Venues", null, "venue");
		$pgcontents .= "[su_accordion]\n";
		$pgcontents .= '[su_spoiler title="add venue" style="fabric" icon="arrow-circle-1"]';
		$pgcontents .= U3A_HTML::to_html(U3A_Venue_Utilities::get_venue_editor("add"));
		$pgcontents .= "[/su_spoiler]\n";
		$pgcontents .= '[su_spoiler title="edit venue" style="fabric" icon="arrow-circle-1"]';
		$sel = U3A_Venue_Utilities::get_venue_select_list("u3a-edit-venue-select");
		$sel->add_attribute("onchange", "u3a_select_venue_change()");
		$pgcontents .= U3A_HTML::to_html($sel);
		$pgcontents .= U3A_HTML::to_html(new U3A_DIV("", "u3a-edit-venue-div", "u3a-edit-venue-div-class"));
		$pgcontents .= "[/su_spoiler]\n";
		$pgcontents .= '[su_spoiler title="remove venue" style="fabric" icon="arrow-circle-1"]';
		$pgcontents .= U3A_HTML::to_html(U3A_Venue_Utilities::get_venue_select_list("u3a-remove-venue-select"));
		$pgcontents .= U3A_HTML::to_html(new U3A_BUTTON("button", "Remove", "u3a-remove-venue-button", "u3a-button",
			 "u3a_remove_venue()"));
		$pgcontents .= "[/su_spoiler]\n";
//		foreach ($vn["result"] as $venue)
//		{
//			write_log($venue);
//			if ($venue->postcode)
//			{
//				$venues .= '[su_spoiler title="' . $venue->venue . '" style="fabric" icon="arrow-circle-1"]';
//				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">contact:</span><span class="u3a-venue-value">' . $venue->contact . '</span></div>';
//				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">address:</span><span class="u3a-venue-value">' . $venue->address . ', ' . $venue->postcode . '</span></div>';
//				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">telephone:</span><span class="u3a-venue-value">' . $venue->telephone . '</span></div>';
//				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">email:</span><span class="u3a-venue-value">' . U3A_Utilities::strip_all_slashes($venue->email) . '</span></div>';
//				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">website:</span><span class="u3a-venue-value">' . $venue->website . '</span></div>';
//				$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">accessible:</span><span class="u3a-venue-value">' . ($venue->is_accessible ? "yes" : "no") . '</span></div>';
//				if ($venue->notes)
//				{
//					$venues .= '<div class="u3a-venue-div"><span class="u3a-venue-label">notes:</span><span class="u3a-venue-value">' . $venue->notes . '</span></div>';
//				}
//				$venues .= '[su_gmap address="' . $venue->address . ', ' . $venue->postcode . '" responsive="yes"]';
//				$venues .= "[/su_spoiler]\n";
//			}
//		}
		$pgcontents .= "[/su_accordion]\n";
	}
	return do_shortcode($pgcontents);
}
