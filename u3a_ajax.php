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
require_once 'u3a_admin.php';
require_once 'u3a_mail.php';

add_action("wp_ajax_u3a_find_members_search", "u3a_find_members_search_action");

function u3a_find_members_search_action()
{
//	write_log("in action");
//	write_log($_POST);
	$surname = isset($_POST["member-surname"]) ? $_POST["member-surname"] : null;
	$forename = isset($_POST["member-forename"]) ? $_POST["member-forename"] : null;
	$mnum = isset($_POST["member-number"]) ? $_POST["member-number"] : null;
	$groups_id = isset($_POST["group"]) ? $_POST["group"] : 0;
	$next_action = isset($_POST["next_action"]) ? $_POST["next_action"] : null;
	$op = $_POST["op"];
	$idsuffix = isset($_POST["idsuffix"]) ? $_POST["idsuffix"] : ("-" . str_replace("_", "-", $next_action) . "-" . $op);
	$ret = "";
	$nfound = 0;
//	write_log($idsuffix);
	$where = [];
	if (isset($_POST["status"]))
	{
		if ($_POST["status"] !== "any" && $_POST["status"] !== "*")
		{
			$where["status"] = $_POST["status"];
		}
	}
	elseif ($next_action !== "change_status")
	{
		$where["status"] = "Current";
	}
	if ($surname || $forename)
	{
		if ($surname)
		{
			$where["surname%~%"] = "$surname";
		}
		if ($forename)
		{
			$where["forename%~%"] = "$forename";
		}
//		write_log($where);
		$found = U3A_Row::load_array_of_objects("U3A_Members", $where, "surname, forename");
//		write_log($found);
		$nfound = $found["total_number_of_rows"];
	}
	elseif ($mnum)
	{
		$where["membership_number"] = $mnum;
		$found = U3A_Row::load_array_of_objects("U3A_Members", $where);
		$nfound = $found["total_number_of_rows"];
	}
	if ($nfound)
	{
		$options = [];
		foreach ($found["result"] as $mbr)
		{
			$opt = new U3A_OPTION($mbr->membership_number . ": " . $mbr->surname . ", " . $mbr->forename . " (" . U3A_Utilities::strip_all_slashes($mbr->email) . ")",
			  $mbr->id);
//				$opt->add_tooltip("member " . $mbr->membership_number . " " . $mbr->email);
			$options[] = $opt;
		}
		$sel = new U3A_SELECT($options, "member", "u3a-found-members" . $idsuffix, "u3a-found-members-class");
		if ($next_action === "add_to_group")
		{
//				write_log("adding to group");
//			$add_btn = new U3A_BUTTON("button", "add", "u3a-found-members-button" . $idsuffix, "u3a-found-members-button-class", "u3a_add_member_to_group('" . $next_action . "', '" . $op . "', " . $groups_id . ")");
			$add_member_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null,
			  "add_member_to_group");
			$add_member_group = new U3A_INPUT("hidden", "group", "u3a-found-members-search-group" . $idsuffix, null, $groups_id);
			$form = new U3A_FORM([$sel, $add_member_action, $add_member_group], "/wp-admin/admin-ajax.php", "POST",
			  "u3a-add-member-form" . $idsuffix, "u3a-add-member-form-class");
		}
		elseif ($next_action === "be_coordinator")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");

			$be_coordinator_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null,
			  "be_coordinator_of_new_group");
			$be_coordinator_group = new U3A_INPUT("hidden", "group", "u3a-found-members-search-group" . $idsuffix, null,
			  $groups_id);
			$form = new U3A_FORM([$sel, $be_coordinator_action, $be_coordinator_group], "/wp-admin/admin-ajax.php", "POST",
			  "u3a-be-coordinator-form" . $idsuffix, "u3a-be-coordinator-form-class");
		}
		elseif ($next_action === "edit_details")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");
//				$member_details_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null, "be_coordinator_of_new_group");
			$form = new U3A_FORM([$sel], "/wp-admin/admin-ajax.php", "POST", "u3a-member-details-form" . $idsuffix,
			  "u3a-member-details-form-class");
		}
		elseif ($next_action === "view_details")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");
//				$member_details_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null, "be_coordinator_of_new_group");
			$form = new U3A_FORM([$sel], "/wp-admin/admin-ajax.php", "POST", "u3a-member-details-form" . $idsuffix,
			  "u3a-member-details-form-class");
		}
		elseif ($next_action === "goto_member")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");
//				$member_details_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null, "be_coordinator_of_new_group");
			$form = new U3A_FORM([$sel], "/wp-admin/admin-ajax.php", "POST", "u3a-member-details-form" . $idsuffix,
			  "u3a-member-details-form-class");
		}
		elseif ($next_action === "change_status")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");
//				$member_details_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null, "be_coordinator_of_new_group");
			$form = new U3A_FORM([$sel], "/wp-admin/admin-ajax.php", "POST", "u3a-member-status-form" . $idsuffix,
			  "u3a-member-details-form-class");
		}
		elseif ($next_action === "renew_member")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");
//				$member_details_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null, "be_coordinator_of_new_group");
			$form = new U3A_FORM([$sel], "/wp-admin/admin-ajax.php", "POST", "u3a-member-status-form" . $idsuffix,
			  "u3a-member-details-form-class");
		}
		elseif ($next_action === "delete")
		{
//				$add_btn = new U3A_BUTTON("button", "select", "u3a-found-members-button", "u3a-found-members-button-class");
//				$member_details_action = new U3A_INPUT("hidden", "action", "u3a-found-members-action" . $idsuffix, null, "be_coordinator_of_new_group");
			$form = new U3A_FORM([$sel], "/wp-admin/admin-ajax.php", "POST", "u3a-member-delete-form" . $idsuffix,
			  "u3a-member-delete-form-class");
		}
		else
		{
			$form = "";
		}
		$hdr = new U3A_H(6, "found: " . $nfound . ($nfound == 1 ? " member" : " members"));
		$ret = U3A_HTML::to_html([$hdr, $form]);
	}
	else
	{
		$p = new U3A_P("No members found to match '" . ($forename ? "$forename " : "") . $surname);
		$ret = $p->to_html();
	}
//	else
//	{
//		$ret = new U3A_P("No members found to match " . ($forename ? "$forename " : "") . $surname);
//	}
	echo json_encode(["nfound" => $nfound, "html" => $ret]);
	wp_die();
}

add_action("wp_ajax_u3a_add_member_to_group", "u3a_add_member_to_group_action");

function u3a_add_member_to_group_action()
{
//	write_log($_POST);
	$mnum = $_POST["member"];
	$groups_id = $_POST["group"];
	$wl = U3A_Utilities::get_post("waiting", 0);
	$msg = "";
	$success = 1;
	if ($mnum && $groups_id)
	{
		$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum, "status" => "Current"]);
		if ($mbr)
		{
			$grp = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
			if ($grp)
			{
				$gmhash = ["members_id" => $mbr->id, "groups_id" => $groups_id];
				if ($wl)
				{
					$gmhash["status"] = U3A_Group_Members::WAITING;
				}
				$grpmem = new U3A_Group_Members($gmhash);
				$grpmem->save();
				$msg = $mbr->forename . " " . $mbr->surname . " added to " . $grp->name;
				do_action("u3a_group_member_added", $groups_id, $mbr->id);
			}
			else
			{
				$msg = "no group found with id " . $groups_id;
				$success = 0;
			}
		}
		else
		{
			$msg = "no member found with number " . $mnum;
			$success = 0;
		}
	}
	else
	{
		$msg = "invalid input";
		$success = 0;
	}
	echo json_encode(["success" => $success, "message" => $msg]);
	wp_die();
}

add_action("wp_ajax_be_coordinator_of_new_group", "be_coordinator_of_new_group_action");

function be_coordinator_of_new_group_action()
{
	write_log("be_coordinator_of_new_group_action");
}

add_action("wp_ajax_u3a_move_document", "u3a_move_document");

function u3a_move_document()
{
	$docid = intval($_POST["document"]);
	$type = $_POST["type"];
	$dest = intval($_POST["dest"]);
	$catid = intval($_POST["catid"]);
//	$groups_id = intval($_POST["group"]);
	write_log($_POST);
//	write_log($type);
//	write_log($dest);
//	write_log($docid);
	if ($docid)
	{
		$doc = U3A_Row::load_single_object("U3A_Documents", ["id" => $docid]);
//		write_log($doc);
		if ($doc)
		{
			$title = $doc->title;
			$docid = $doc->id;
			$cdrels0 = U3A_Row::load_array_of_objects("U3A_Document_Category_Relationship", ["documents_id" => $docid]);
			$cdrels = $cdrels0["result"];
//			write_log($cdrels);
			if ($dest < 0)
			{
				$attachment_id = $doc->attachment_id;
				$others = 0;
				foreach ($cdrels as $cdrel)
				{
					if ($cdrel->document_categories_id == $catid)
					{
						audit_log("document category relationship deleted", $cdrel);
						$cdrel->delete();
					}
					else
					{
						$others++;
					}
				}
				if ($others === 0)
				{
					audit_log("document deleted", $doc);
					$doc->delete();
					if ($attachment_id)
					{
						$da = wp_delete_attachment($attachment_id);
						if ($da === FALSE)
						{
							$msg = "Failed to delete attachment with id $attachment_id";
							$success = 0;
						}
						else
						{
							$success = 1;
							$msg = ucfirst($type) . " '$title' deleted";
						}
					}
				}
				else
				{
					$success = 1;
					$msg = ucfirst($type) . " '$title' removed from category '" . U3A_Document_Categories::get_category_name($catid) . "'.";
				}
			}
			else
			{
				foreach ($cdrels as $cdrel)
				{
					if ($cdrel->document_categories_id == $catid)
					{
						$cdrel->document_categories_id = $dest;
						$cdrel->save();
					}
				}
				$success = 1;
				$msg = U3A_Documents::get_type_name($type) . ' "' . $doc->title . '" moved to ' . stripslashes(U3A_Document_Categories::get_category_name($dest));
			}
		}
		else
		{
			$msg = "No $type found with id $docid";
			$success = 0;
		}
	}
	else
	{
		$dcrels = U3A_Row::load_array_of_objects("U3A_Document_Category_Relationship", ["document_categories_id" => $catid]);
		$success = 1;
		$msg = "moving " . U3A_Documents::get_type_name($type) . "s";
		foreach ($dcrels["result"] as $dcrel)
		{
			if ($dcrel)
			{
				if ($dest < 0)
				{
					$doc = U3A_Row::load_single_object("U3A_Documents", ["id" => $dcrel->documents_id]);
					$cdrels1 = U3A_Row::load_array_of_objects("U3A_Document_Category_Relationship",
						 ["documents_id" => $dcrel->documents_id]);
					$attachment_id = $doc->attachment_id;
					$others = 0;
					foreach ($cdrels1["result"] as $cdrel1)
					{
						if ($cdrel1->document_categories_id == $catid)
						{
							audit_log("document category relationship deleted", $cdrel1);
							$cdrel1->delete();
						}
						else
						{
							$others++;
						}
					}
					if ($others === 0)
					{
						audit_log("document deleted", $doc);
						$doc->delete();
						if ($attachment_id)
						{
							audit_log("attachment deleted", $attachment_id);
							$da = wp_delete_attachment($attachment_id);
							if ($da === FALSE)
							{
								$msg .= " Failed to delete attachment with id $attachment_id";
								$success = 0;
							}
						}
					}
				}
				else
				{
					$dcrel->document_categories_id = $dest;
					$dcrel->save();
				}
			}
		}
	}
	echo json_encode(["success" => $success, "message" => $msg]);
	wp_die();
}

add_action("wp_ajax_u3a_copy_document", "u3a_copy_document");

function u3a_copy_document()
{
	$docid = intval($_POST["document"]);
	$type = $_POST["type"];
	$dest = intval($_POST["dest"]);
	$catid = intval($_POST["catid"]);
//	write_log("u3a_copy_document");
//	write_log($docid);
//	write_log($type);
//	write_log($dest);
//	write_log($catid);
	if ($dest)
	{
		if ($docid)
		{
			$doc = U3A_Row::load_single_object("U3A_Documents", ["id" => $docid]);
//			write_log($doc);
			if ($doc)
			{
				$title = $doc->title;
				$docid = $doc->id;
				$dcrel = U3A_Row::load_single_object("U3A_Document_Category_Relationship",
					 ["documents_id" => $docid, "document_categories_id" => $dest]);
				if (!$dcrel)
				{
					// not already there
					$dcrel = new U3A_Document_Category_Relationship(["documents_id" => $docid, "document_categories_id" => $dest]);
					$dcrel->save();
					$msg = "$type \"$title\" copied";
					$success = 1;
				}
				else
				{
					$msg = "$type \"$title\" already there!";
					$success = 0;
				}
			}
			else
			{
				$msg = "No $type found with id $docid";
				$success = 0;
			}
		}
		else
		{
			$dcrels = U3A_Row::load_array_of_objects("U3A_Document_Category_Relationship", ["document_categories_id" => $catid]);
			$success = 1;
			$msg = U3A_Documents::get_type_name($type) . "s copied";
			foreach ($dcrels["result"] as $dcrelsrc)
			{
				if ($dcrelsrc)
				{
					$docid = $dcrelsrc->documents_id;
					$dcrel = U3A_Row::load_single_object("U3A_Document_Category_Relationship",
						 ["documents_id" => $docid, "document_categories_id" => $dest]);
					if (!$dcrel)
					{
						// not already there
						$dcrel = new U3A_Document_Category_Relationship(["documents_id" => $docid, "document_categories_id" => $dest]);
						$dcrel->save();
					}
				}
			}
		}
	}
	else
	{
		$msg = "no destination for copy specified";
		$success = 0;
	}
	echo json_encode(["success" => $success, "message" => $msg]);
	wp_die();
}

//add_action('wp_ajax_u3a_upload_image', 'u3a_upload_image');
//
//function u3a_upload_image()
//{
//	write_log("u3a upload image");
////	write_log($_POST);
////	write_log($_FILES);
//	$uploadedfile = $_FILES['u3a_upload_image-file'];
//	$mbr = U3A_Information::u3a_logged_in_user();
//	if ($uploadedfile['size'] > 0)
//	{
//		require_once( ABSPATH . 'wp-admin/includes/image.php' );
//		require_once( ABSPATH . 'wp-admin/includes/file.php' );
//		require_once( ABSPATH . 'wp-admin/includes/media.php' );
//
//// Let WordPress handle the upload.
//// Remember, 'my_image_upload' is the name of our file input in our form above.
//		$attachment_id = media_handle_upload('u3a_upload_image-file', 0);
//
//		if (is_wp_error($attachment_id))
//		{
//			die(json_encode(array('type' => 'error', 'error' => 1)));
//		}
//		else
//		{
//			$file = get_attached_file($attachment_id);
//			$title = "";
//			$by = null;
//			if (isset($_POST["title"]))
//			{
//				$title = $_POST["title"];
//			}
//			if (!$title)
//			{
//				$title = str_replace("_", " ", U3A_File_Utilities::remove_extension(basename($file)));
//			}
//			if ($title)
//			{
//				if (isset($_POST["by"]))
//				{
//					$by = $_POST["by"];
//					if ($by)
//					{
//						$title .= " by " . $by;
//					}
//				}
//			}
//			if (isset($_POST["type"]))
//			{
//				$imgtype = intval($_POST["type"]);
//			}
//			else
//			{
//				$imgtype = U3A_Documents::GROUP_IMAGE_TYPE;
//			}
//			$img = new U3A_Documents([
//				"members_id"	 => $mbr->id,
//				"groups_id"		 => $_POST["group"],
//				"file"			 => $file,
//				"title"			 => $title,
//				"author"			 => $by,
//				"attachment_id" => $attachment_id,
//				"document_type" => $imgtype
//			]);
//			$imgid = $img->save();
//			$dcrel = new U3A_Document_Category_Relationship([
//				"document_categories_id" => $_POST["category"],
//				"documents_id"				 => $imgid
//			]);
//			$dcrel->save();
//			die(json_encode(array('type' => 'error', 'text' => 'Ok', 'error' => 0)));
//		}
//	}
//	die(json_encode(array('type' => 'error', 'error' => 2)));
//}
//
add_action('wp_ajax_u3a_upload_document', 'u3a_upload_document');

function u3a_upload_document()
{
	write_log("u3a upload document");
	write_log($_POST);
//	write_log($_FILES);
	$groups_id = U3A_Utilities::get_post("group", 0);
	$type = U3A_Utilities::get_post("type", 0);
	$uploadedfile = $_FILES['u3a-upload-document-file'];
	$visibility = U3A_Utilities::get_post("visibility", 0);
	$mbr = U3A_Information::u3a_logged_in_user();
	if ($uploadedfile['size'] > 0)
	{
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

// Let WordPress handle the upload.
		$attachment_id = media_handle_upload('u3a-upload-document-file', 0);

		if (is_wp_error($attachment_id))
		{
			$result = ["success" => 0, "message" => "failed to save uploaded file " . $uploadedfile["name"] . " reason: " . $attachment_id->get_error_message()];
		}
		else
		{
			$file = get_attached_file($attachment_id);
			$title = "";
			$by = null;
			if (isset($_POST["title"]))
			{
				$title = $_POST["title"];
//				write_log("title from _POST " . $title);
			}
			else
			{
				if (isset($_POST["title1"]))
				{
					$title .= $_POST["title1"];
					if (isset($_POST["title2"]))
					{
						$title .= " " . $_POST["title2"];
						if (isset($_POST["title3"]))
						{
							$title .= " " . $_POST["title3"];
						}
					}
				}
//				write_log("title from bits " . $title);
			}
			if (!$title)
			{
				$title = str_replace("_", " ", U3A_File_Utilities::remove_extension(basename($file)));
//				write_log("title from filename " . $title);
			}
			$attachment_title = $title;
			if (isset($_POST["by"]) && $_POST["by"])
			{
				$by = $_POST["by"];
				$attachment_title .= " by " . $by;
			}
			$doc = new U3A_Documents([
				"members_id"	 => $mbr->id,
				"groups_id"		 => $groups_id,
				"document_type" => $type,
				"file"			 => $file,
				"title"			 => $title,
				"author"			 => $by,
				"attachment_id" => $attachment_id,
				"visibility"	 => $visibility
			]);
			$docid = $doc->save();
			$dcrel = new U3A_Document_Category_Relationship([
				"document_categories_id" => $_POST["category"],
				"documents_id"				 => $docid
			]);
			$dcrel->save();
			$doc_meta = array(
				'ID'				 => $attachment_id, // Specify the image (ID) to be updated
				'post_title'	 => $attachment_title, // Set image Title to sanitized title
				'post_excerpt'	 => $attachment_title, // Set image Caption (Excerpt) to sanitized title
				'post_content'	 => $attachment_title, // Set image Description (Content) to sanitized title
			);
			if ($type == U3A_Documents::GROUP_IMAGE_TYPE || $type == U3A_Documents::COMMITTEE_IMAGE_TYPE || $type == U3A_Documents::PERSONAL_IMAGE_TYPE)
			{
				// Set the image Alt-Text
				update_post_meta($attachment_id, '_wp_attachment_image_alt', $attachment_title);
			}

			// Set the image meta (e.g. Title, Excerpt, Content)
			wp_update_post($doc_meta);
			write_log("writing log");
			audit_log("u3a_upload_document", $doc->get_as_hash(), $dcrel->get_as_hash());
			$result = ["success" => 1, "message" => "document '$title' uploaded"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "failed to upload file " . $uploadedfile["name"]];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a-remove-member-from-group-action", "u3a_remove_member_from_group_action");

function u3a_remove_member_from_group_action()
{
	$members_ids = $_POST["members_id"];
	$groups_id = $_POST["groups_id"];
	$mbrs = explode(",", $members_ids);
	foreach ($mbrs as $members_id)
	{
//	write_log("remove_from_group", $members_id, $members_id);
		$grpmem = U3A_Row::load_single_object("U3A_Group_Members", ["members_id" => $members_id, "groups_id" => $groups_id]);
		audit_log("group membership deleted", $grpmem);
		$grpmem->delete();
		do_action("u3a_member_removed_from_group", $groups_id, $members_id);
	}
	wp_die();
//	write_log($grpmem);
//	U3A_Group_Members::remove_from_group($members_id, $groups_id);
}

add_action("wp_ajax_u3a-new-group-action", "u3a_new_group_action");

function u3a_new_group_action()
{
	write_log($_POST);
	$ngname = isset($_POST["name"]) ? $_POST["name"] : "";
	$ngcoord = isset($_POST["coord"]) ? $_POST["coord"] : "";
	$ngvenue = isset($_POST["venue"]) ? $_POST["venue"] : 0;
	$ngwhen = isset($_POST["meets_when"]) ? $_POST["meets_when"] : "";
	$ngmax = isset($_POST["max_members"]) ? $_POST["max_members"] : "";
//	$ngfrom = isset($_POST["from"]) ? $_POST["from"] : "";
//	$ngto = isset($_POST["to"]) ? $_POST["to"] : "";
	$ngnotes = isset($_POST["notes"]) ? $_POST["notes"] : "";
	$grp = U3A_Row::load_single_object("U3A_Groups", ["name" => $ngname]);
	if ($grp)
	{
		$result = ["success" => 0, "message" => "a group named $ngname already exists"];
	}
	else
	{
		$coord = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $ngcoord, "status" => "Current"]);
		if (!$coord)
		{
			$result = ["success" => 0, "message" => "no member with number $ngcoord found to be coordinator"];
		}
		else
		{
			$params = ["name" => $ngname, "status" => 1];
			if ($ngvenue)
			{
				$params["venue"] = $ngvenue;
			}
			if ($ngwhen)
			{
				$params["meets_when"] = $ngwhen;
			}
			if ($ngmax)
			{
				$params["max_members"] = $ngmax;
			}
//			if ($ngfrom)
//			{
//				$params["start_time"] = $ngfrom;
//			}
//			if ($ngto)
//			{
//				$params["end_time"] = $ngto;
//			}
			if ($ngnotes)
			{
				$params["information"] = $ngnotes;
			}
			$grp = new U3A_Groups($params);
//			write_log($grp);
			$groups_id = $grp->save();
			if ($groups_id)
			{
				$gmem = new U3A_Group_Members([
					"members_id" => $coord->id,
					"groups_id"	 => $groups_id,
					"status"		 => 1
				]);
				$gmem->save();
				$result = ["success" => 1, "message" => "group $ngname created"];
//				$grp1 = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
//				$obj = json_decode(stripslashes($grp1->meets_when));
//				write_log($obj);
			}
			else
			{
				$result = ["success" => 0, "message" => "group $ngname was not created"];
			}
		}
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a-edit-group-action", "u3a_edit_group_action");

function u3a_edit_group_action()
{
//	write_log($_POST);
	$params = [];
	$params["name"] = U3A_Utilities::get_post("name", "");
	$ngname = $params["name"];
	$ngcoord = U3A_Utilities::get_post("coord");
	$params["venue"] = U3A_Utilities::get_post("venue", "");
	$params["meets_when"] = U3A_Utilities::get_post("meets_when", "");
	$params["max_members"] = U3A_Utilities::get_post("max_members", "");
//	$ngfrom = isset($_POST["from"]) ? $_POST["from"] : "";
//	$ngto = isset($_POST["to"]) ? $_POST["to"] : "";
	$params["information"] = U3A_Utilities::get_post("notes", "");
	$params["id"] = U3A_Utilities::get_post("group", 0);
	$groups_id = $params["id"];
	$grp = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
	if ($grp)
	{
		$coordchanged = false;
		if ($ngcoord)
		{
			$newcoordid = array_unique(explode(",", $ngcoord));
			$oldcoordid = U3A_Groups::get_coordinator_ids($grp);
			$addcoord = array_diff($newcoordid, $oldcoordid);
			$rmcoord = array_diff($oldcoordid, $newcoordid);
			write_log("add", $addcoord, "rm", $rmcoord);
			foreach ($addcoord as $cid)
			{
				$gmem = U3A_Row::load_single_object("U3A_Group_Members", ["groups_id" => $groups_id, "members_id" => $cid]);
				if ($gmem)
				{
					$status = intval($gmem->status);
					switch ($status) {
						case 0:
						case 4:
							{
								$gmem->status = 1;
								$gmem->save();
//								write_log("1.saved", $gmem->members_id);
								$coordchanged = true;
								break;
							}
						case 2:
							{
								$gmem->status = 3;
								$gmem->save();
								$coordchanged = true;
//								write_log("2.saved", $gmem->members_id);
								break;
							}
						case 1:
						case 3:
						default:
							{
								break;
							}
					}
				}
			}
			foreach ($rmcoord as $cid)
			{
				$gmem = U3A_Row::load_single_object("U3A_Group_Members", ["groups_id" => $groups_id, "members_id" => $cid]);
				if ($gmem)
				{
					$status = intval($gmem->status);
					switch ($status) {
						case 1:
							{
								$gmem->status = 0;
								$gmem->save();
								$coordchanged = true;
//								write_log("3.saved", $gmem->members_id);
								break;
							}
						case 3:
							{
								$gmem->status = 2;
								$gmem->save();
								$coordchanged = true;
//								write_log("4.saved", $gmem->members_id);
								break;
							}
						case 0:
						case 4:
						default:
							{
								break;
							}
					}
				}
			}
//			foreach ($coordid as $cid)
//			{
//				$coord1 = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $cid, "status" => "Current"]);
//				if ($coord1)
//				{
//					$coord[] = $coord1;
//				}
//			}
		}
		$groups_columns = U3A_Row::get_the_column_names("u3a_groups");
		$changed = false;
		$changed1 = false;
		foreach ($groups_columns as $column)
		{
			if (isset($params[$column]) && $params[$column])
			{

				// trouble with lots of backslashes appearing
				if ($column === "meets_when")
				{
//				write_log("2. meets when old", $grp->$column, "new", $params[$column]);
					$pc = U3A_Utilities::strip_all_slashes($params[$column]);
					$gc = U3A_Utilities::strip_all_slashes($grp->$column);
				}
				else
				{
					$pc = $params[$column];
					$gc = $grp->$column;
				}
				If ($pc !== $gc)
				{
					$grp->$column = $params[$column];
					$changed = true;
				}
			}
		}
		if ($changed || $coordchanged)
		{
			$grp->save();
			$result = ["success" => 1, "message" => "group $ngname has been changed!"];
		}
		else
		{
			$result = ["success" => 1, "message" => "nothing has been changed in group $ngname!"];
		}
//			$gmem = new U3A_Group_Members([
//				"members_id" => $coord->id,
//				"groups_id"	 => $params["id"],
//				"status"		 => 1
//			]);
//			$gmem->save();
//			$result = ["success" => 1, "message" => "group $ngname modified"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no group with id $grpid found"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a-select-group-action", "u3a_select_group_action");

function u3a_select_group_action()
{
	$groups_id = isset($_POST["groups_id"]) ? $_POST["groups_id"] : 0;
	$op = isset($_POST["op"]) ? $_POST["op"] : "";
//	write_log($_POST);
	if ($op == "edit")
	{
		$grp = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
		if ($grp)
		{
			$group_id = $grp->id;
			$group_name = $grp->name;
			$gmw = $grp->get_meets_when_text();
			$gmwj = $grp->meets_when;
			$group_venue = $grp->venue;
			$group_info = $grp->information;
			$group_max = $grp->max_members;
			$coord = U3A_HTML_Utilities::get_coordinators_for_group_edit($grp);
			$ret = [
				"op"					 => $op,
				"id"					 => $group_id,
				"name"				 => $group_name,
				"meets_when"		 => $gmw,
				"meets_when_json"	 => $gmwj,
				"venue"				 => $group_venue,
				"info"				 => $group_info,
				"max"					 => $group_max,
				"coord"				 => U3A_HTML::to_html($coord)
			];
			$result = ["success" => 1, "message" => json_encode($ret)];
//			$result = ["success" => 1, "message" => do_shortcode('[u3a_edit_group_form group="' . $groups_id . '"]')];
		}
		else
		{
			$result = ["success" => 0, "message" => "no group found with id $groups_id"];
		}
	}
	elseif ($op == "delete")
	{
		$grp = U3A_Groups::get_group($groups_id);
		if ($grp)
		{
			$gname = $grp->name;
			audit_log("group deleted", $grp);
			$grp->delete();
			$grp_members = U3A_Row::load_array_of_objects("U3A_Group_Members", ["groups_id" => $groups_id]);
			foreach ($grp_members["result"] as $gmbr)
			{
				audit_log("group membership deleted", $gmbr);
				$gmbr->delete();
			}
			$result = ["success" => 1, "message" => "$gname has been deleted!"];
		}
		else
		{
			$result = ["success" => 0, "message" => "no group found with id $groups_id!"];
		}
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_member", "u3a_member_action");
add_action("wp_ajax_nopriv_u3a_member", "u3a_member_action");

function u3a_member_action()
{
//	write_log($_POST);
	$members_columns = U3A_Row::get_the_column_names("u3a_members");
	$op = isset($_POST["op"]) ? $_POST["op"] : "";
	$params = [];
	$date = date("Y-m-d");
	foreach ($members_columns as $column)
	{
		if (isset($_POST[$column]) && $_POST[$column])
		{
			if ($column === "gift_aid")
			{
				if ($_POST[$column] === "yes")
				{
					$params[$column] = $date;
				}
			}
			elseif (($column === 'TAM') || ($column === 'tam'))
			{
				if ($_POST[$column] === "no")
				{
					$params["TAM"] = 0;
				}
				else
				{
					$params["TAM"] = 1;
				}
			}
			elseif ($column === 'newsletter')
			{
				if ($_POST[$column] === "no")
				{
					$params[$column] = 0;
				}
				else
				{
					$params[$column] = 1;
				}
			}
			elseif ($column === 'surname')
			{
				$params[$column] = ucfirst($_POST[$column]);
			}
			elseif ($column === 'forename')
			{
				$params[$column] = ucfirst($_POST[$column]);
				$params["initials"] = substr($params[$column], 0, 1);
			}
			elseif (($column === 'gender') && ($_POST[$column] === 'N'))
			{
				$params[$column] = null;
			}
			else
			{
				$params[$column] = $_POST[$column];
			}
		}
	}
//	write_log($op, $params);
	if (($op == "add") || ($op == "join"))
	{
		$params["membership_number"] = U3A_Members::get_maximum_membership_number() + 1;
		if (isset($params["forename"]) && isset($params["surname"]))
//		if (isset($params["forename"]) && isset($params["surname"]) && (isset($params["telephone"]) || isset($params["mobile"]) || isset($params["email"]) || isset($params["address1"])))
		{
			$params["joined"] = $date;
			$params["renew"] = (date("Y") + 1) . "-01-01";
			if (!isset($params["status"]))
			{
				if (($params["payment_type"] === "PayPal") || ($params["payment_type"] === "CreditCard"))
				{
					$params["status"] = "Current";
				}
				else
				{
					$params["status"] = "Provisional";
				}
			}
			$params["class"] = "Individual";
			$params["renew"] = U3A_Members::get_next_renewal_date();
			$member = new U3A_Members($params);
			$members_id = $member->save();
			$result = ["success" => 1, "message" => $params["forename"] . " " . $params["surname"] . " has been successfully added with membership number " . $params["membership_number"] . "."];
			if (($params["payment_type"] === "PayPal") || ($params["payment_type"] === "CreditCard"))
			{
				if ($op === "join")
				{
					$sent = send_new_member_email($member);
					$amount = isset($params["amount"]) ? $params["amount"] : U3A_Information::u3a_get_current_join_rate($member->associate !== null);
					$sub = new U3A_Subscriptions(["members_id" => $members_id, "amount" => $amount, "subscription_year" => U3A_Timestamp_Utilities::year()]);
					$sub->save();
				}
				else
				{
					$sent = send_add_member_email($member);
				}
				$regpg = get_permalink(get_page_by_path('register'));
				$result = ["success" => 1, "message" => $params["forename"] . " " . $params["surname"] . " has been successfully added with membership number " . $params["membership_number"] .
					". An email has " . ($sent ? "" : "not") . " been sent", "arg"		 => ["mnum" => $params["membership_number"], "email" => $params["email"], "url" => $regpg]];
				audit_log("new paid up member", $member);
			}
			else
			{
				$sent = send_provisional_member_email($member);
				$result = ["success" => 1, "message" => $params["forename"] . " " . $params["surname"] . " has been provisionally added with membership number " . $params["membership_number"] .
					". An email has " . ($sent ? "" : "not") . " been sent"];
				audit_log("new provisional member", $member);
			}
		}
		else
		{
			$result = ["success" => 0, "message" => "Insufficient information, member has NOT been added!"];
		}
	}
	elseif ($op == "edit" || $op == "selfedit")
	{
		$mbr = U3A_Row::load_single_object("U3A_Members",
			 ["membership_number" => $params["membership_number"], "status" => "Current"]);
//		write_log($mbr);
		if ($mbr)
		{
			$changed = false;
			$changed_values = [];
			foreach ($members_columns as $column)
			{
				if (array_key_exists($column, $params))
				{
					$newval = $params[$column];
					$oldval = $mbr->$column;
					if ($newval != $oldval)
					{
						if ($oldval && !$newval)
						{
							// remove old value
							$mbr->$column = null;
							$changed = true;
						}
						elseif ($newval)
						{
							switch ($column) {
								case "gift_aid":
									{
										// only change if oldval was null
										if (!$oldval)
										{
											$changed = true;
											$mbr->$column = $newval;
											$changed_values[$column] = ["oldval" => $oldval, "newval" => $newval];
										}
										break;
									}
								default:
									{
										$changed = true;
										$mbr->$column = $newval;
										$changed_values[$column] = ["oldval" => $oldval, "newval" => $newval];
									}
							}
						}
					}
				}
			}
			if ($changed)
			{
//				write_log($changed);
				$mbr->save();
				$result = ["success" => 1, "message" => "member has been changed!"];
				audit_log("member edited", $mbr);
				do_action("u3a_member_edited", $mbr, $changed_values);
			}
			else
			{
				$result = ["success" => 1, "message" => "nothing has been changed!"];
			}
		}
		else
		{
			$result = ["success" => 0, "message" => "No member found with number" . $params["membership_number"] . "!"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "Invalid operation, member has NOT been added!"];
	}
	echo json_encode($result);
	wp_die();
}

function send_mailing_list_email($name)
{
	$config = U3A_CONFIG::get_the_config();
	$wm = U3A_Committee::get_webmanager();
	$contents = "<p>Mailing list $name@" . $config->MAILING_LIST_DOMAIN . " has been created, please create a mail forwarder for $name@" . $config->DOMAIN_NAME . ".";
	$sent = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($wm->email),
		 "Mailing list forwarder requirement", $contents);
	return $sent;
}

function send_new_member_email($mbr)
{
	$wm = U3A_Committee::get_webmanager();
	$ms = U3A_Committee::get_membership_secretary();
	if (!U3A_Information::u3a_is_live_server())
	{
		$sent = true;
		$cc = [U3A_Utilities::strip_all_slashes($wm->email), "mike@computermike.biz"];
		$p0 = new U3A_DIV("Hi " . $mbr->get_first_name());
		$p1 = new U3A_P("Welcome to the " . U3A_Information::u3a_get_u3a_name() . " U3A, your membership number is " . $mbr->membership_number .
		  '. You will need this number and your email address to register and login to <a href="' . get_site_url() . '">our website</a>, if you have not already done so.' .
		  " On the website you will find details of our monthly meetings and all the study groups.");
		$p2 = new U3A_P("We are very pleased that you have joined us.");
		$p3 = new U3A_DIV("regards");
		$p4 = new U3A_DIV($ms->get_name() . " (membership secretary)");
		$contents1 = U3A_HTML::to_html([$p0, $p1, $p2, $p3, $p4]);
		$card = U3A_PDF::get_membership_card($mbr);
		$sent1 = U3A_Sent_Mail::send($ms->id, U3A_Utilities::strip_all_slashes($mbr->email),
			 "Welcome to " . U3A_Information::u3a_get_u3a_name() . " U3A", $contents1, $cc, null, null,
			 U3A_Utilities::strip_all_slashes($ms->email), [$card["path"]]);
	}
	else
	{
		$tr = U3A_Committee::get_treasurer();
		$cc = [U3A_Utilities::strip_all_slashes($wm->email), U3A_Utilities::strip_all_slashes($tr->email), U3A_Utilities::strip_all_slashes($ms->email)];
		$contents = "<p>An application form has been submitted <b>fully paid</b>:</p>" . do_shortcode('[u3a_member_details_form member="' . $mbr->id . '" op="mail" button="no"]');
		$sent = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($wm->email), "New Member Form", $contents, $cc);
		$p0 = new U3A_DIV("Hi " . $mbr->get_first_name());
		$p1 = new U3A_P("Welcome to the " . U3A_Information::u3a_get_u3a_name() . " U3A, your membership number is " . $mbr->membership_number .
		  '. You will need this number and your email address to register and login to <a href="' . get_site_url() . '">our website</a>, if you have not already done so.' .
		  " On the website you will find details of our monthly meetings and all the study groups.");
		$p2 = new U3A_P("We are very pleased that you have joined us.");
		$p3 = new U3A_DIV("regards");
		$p4 = new U3A_DIV($ms->get_name() . " (membership secretary)");
		$contents1 = U3A_HTML::to_html([$p0, $p1, $p2, $p3, $p4]);
		$card = U3A_PDF::get_membership_card($mbr);
		$sent1 = U3A_Sent_Mail::send($ms->id, U3A_Utilities::strip_all_slashes($mbr->email),
			 "Welcome to " . U3A_Information::u3a_get_u3a_name() . " U3A", $contents1, $cc, null, null,
			 U3A_Utilities::strip_all_slashes($ms->email), [$card["path"]]);
	}
	return $sent && $sent1;
}

function send_renewal_email($mbr)
{
	$wm = U3A_Committee::get_webmanager();
	$ms = U3A_Committee::get_membership_secretary();
	if (!U3A_Information::u3a_is_live_server())
	{
		$sent = true;
		$cc = [U3A_Utilities::strip_all_slashes($wm->email), "mike@computermike.biz"];
		$p0 = new U3A_DIV("Hi " . $mbr->get_first_name());
		$p1 = new U3A_P("Thank you for renewing your membership of " . U3A_Information::u3a_get_u3a_name() . " U3A, your membership number remains " . $mbr->membership_number .
		  '. You will need this number and your email address to register and login to <a href="' . get_site_url() . '">our website</a>, if you have not already done so.');
		$p2 = "Your new membership card is attached to this email.";
		$p3 = new U3A_DIV("regards");
		$p4 = new U3A_DIV($ms->get_name() . " (membership secretary)");
		$contents1 = U3A_HTML::to_html([$p0, $p1, $p2, $p3, $p4]);
		$card = U3A_PDF::get_membership_card($mbr);
		$sent1 = U3A_Sent_Mail::send($ms->id, U3A_Utilities::strip_all_slashes($mbr->email),
			 "Welcome to " . U3A_Information::u3a_get_u3a_name() . " U3A", $contents1, $cc, null, null,
			 U3A_Utilities::strip_all_slashes($ms->email), [$card["path"]]);
	}
	else
	{
		$tr = U3A_Committee::get_treasurer();
		$cc = [U3A_Utilities::strip_all_slashes($wm->email), U3A_Utilities::strip_all_slashes($tr->email), U3A_Utilities::strip_all_slashes($ms->email)];
		$who = $mbr->get_name() . " (" . $mbr->membership_number . ")";
		$contents = "<p>$who has renewed his/her membership</p>";
		$sent = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($wm->email), "Membership Renewal", $contents,
			 $cc);
		$p0 = new U3A_DIV("Hi " . $mbr->get_first_name());
		$p1 = new U3A_P("Thank you for renewing your membership of " . U3A_Information::u3a_get_u3a_name() . " U3A, your membership number remains " . $mbr->membership_number .
		  '. You will need this number and your email address to register and login to <a href="' . get_site_url() . '">our website</a>, if you have not already done so.');
		$p2 = "Your new membership card is attached to this email.";
		$p3 = new U3A_DIV("regards");
		$p4 = new U3A_DIV($ms->get_name() . " (membership secretary)");
		$contents1 = U3A_HTML::to_html([$p0, $p1, $p2, $p3, $p4]);
		$card = U3A_PDF::get_membership_card($mbr);
		$sent1 = U3A_Sent_Mail::send($ms->id, U3A_Utilities::strip_all_slashes($mbr->email),
			 "Welcome back to " . U3A_Information::u3a_get_u3a_name() . " U3A", $contents1, $cc, null, null,
			 U3A_Utilities::strip_all_slashes($ms->email), [$card["path"]]);
	}
	return $sent && $sent1;
}

function send_add_member_email($mbr)
{
	$wm = U3A_Committee::get_webmanager();
//	$tr = U3A_Committee::get_treasurer();
//	$ms = U3A_Committee::get_membership_secretary();
	$cc = [U3A_Utilities::strip_all_slashes($wm->email)/* , U3A_Utilities::strip_all_slashes($tr->email), U3A_Utilities::strip_all_slashes($ms->email) */];
	$contents = "<p>An application form has been submitted <b>fully paid</b>:</p>" . do_shortcode('[u3a_member_details_form member="' . $mbr->id . '" op="mail" button="no"]');
	$sent = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($wm->email), "New Member Form", $contents, $cc);
	$p0 = new U3A_DIV("Hi " . $mbr->get_first_name());
	$p1 = new U3A_P("Welcome to the " . U3A_Information::u3a_get_u3a_name() . " U3A, your membership number is " . $mbr->membership_number .
	  '. You will need this number and your email address to register and login to <a href="' . get_site_url() . '">our website</a>, if you have not already done so.' .
	  " On the website you will find details of our monthly meetings and all the study groups.");
	$p2 = new U3A_P("We are very pleased that you have joined us.");
	$p3 = new U3A_DIV("regards");
	$p4 = new U3A_DIV($wm->get_name() . " (webmanager)");
	$contents1 = U3A_HTML::to_html([$p0, $p1, $p2, $p3, $p4]);
	$card = U3A_PDF::get_membership_card($mbr);
	$sent1 = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($mbr->email),
		 "Welcome to " . U3A_Information::u3a_get_u3a_name() . " U3A", $contents1, $cc, null, null,
		 U3A_Utilities::strip_all_slashes($ms->email), [$card["path"]]);
	return $sent && $sent1;
}

function send_provisional_member_email($mbr)
{
	$wm = U3A_Committee::get_webmanager();
	$tr = U3A_Committee::get_treasurer();
	$ms = U3A_Committee::get_membership_secretary();
	$cc = [U3A_Utilities::strip_all_slashes($tr->email), U3A_Utilities::strip_all_slashes($ms->email)];
	$contents = "<p>An application form has been submitted <b>without payment</b>:</p>" . do_shortcode('[u3a_member_details_form member="' . $mbr->id . '" op="mail" button="no"]');
	$sent = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($wm->email), "Provisional Member Form",
		 $contents, $cc);
	$p0 = new U3A_DIV("Hi " . $mbr->get_first_name());
	$p1 = new U3A_P("Welcome to the " . U3A_Information::u3a_get_u3a_name() . " U3A, your provisional membership number is " . $mbr->membership_number .
	  ". You still need to pay your membership subscription. This can be done in person at one of our monthly meetings. If you cannot attend one of these meetings, please contact " .
	  "the Membership Secretary at " . U3A_Utilities::strip_all_slashes($ms->email) . " to make alternative arrangements to pay. When you have completed your membership you will be able " .
	  'to register and login to <a href="' . get_site_url() . '">our website</a> where you will find details of our monthly meetings and all the study groups.');
	$p2 = new U3A_P("We are very pleased that you have joined us.");
	$p3 = new U3A_DIV("regards");
	$p4 = new U3A_DIV($wm->get_name() . " (webmanager)");
	$contents1 = U3A_HTML::to_html([$p0, $p1, $p2, $p3, $p4]);
	$sent1 = U3A_Sent_Mail::send($wm->id, U3A_Utilities::strip_all_slashes($mbr->email),
		 "Welcome to " . U3A_Information::u3a_get_u3a_name() . " U3A", $contents1);
	return $sent && $sent1;
}

add_action("wp_ajax_u3a_change_member_status", "u3a_change_member_status");

function u3a_change_member_status()
{
	$mnum = $_POST["membership_number"];
	$newstatus = $_POST["status"];
	$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum]);
	if ($mbr)
	{
		$oldstatus = $mbr->status;
		$mbr->status = $newstatus;
		$members_id = $mbr->save();
		$name = $mbr->get_name();
		if ($oldstatus === "Provisional" && $newstatus === "Current")
		{
			$sent = send_new_member_email($mbr);
			$amount = isset($_POST["amount"]) ? $_POST["amount"] : U3A_Information::u3a_get_current_join_rate($mbr->associate !== null);
			$sub = new U3A_Subscriptions(["members_id" => $members_id, "amount" => $amount, "subscription_year" => U3A_Timestamp_Utilities::year()]);
			$sub->save();
			if ($sent)
			{
				$result = ["success" => 1, "message" => "member $name now has status $newstatus, an email has been sent."];
			}
			else
			{
				$result = ["success" => 1, "message" => "member $name now has status $newstatus."];
			}
		}
		else
		{
			$result = ["success" => 1, "message" => "member $name now has status $newstatus."];
		}
		audit_log("member status changed from $oldstatus to $newstatus", $mbr);
	}
	else
	{
		$result = ["success" => 0, "message" => "member with number $mnum has not been found!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_member_status", "u3a_get_member_status");

function u3a_get_member_status()
{
	$mnum = $_POST["membership_number"];
	$op = $_POST["op"];
	$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum]);
	if ($mbr)
	{
		$status = $mbr->status;
		$span1 = new U3A_SPAN($mnum, "u3a-member-number-$op");
		$span2 = new U3A_SPAN($mbr->get_name(), "u3a-member-name-$op");
		$div = new U3A_DIV([$span1, ": ", $span2], null, "u3a-member-name-class");
		$sel = U3A_HTML_Utilities::get_select_list_from_array(U3A_Members::$status_values, "status", $status,
			 "member-status-select-$op", "member-status-select-class");
		$lbl = U3A_HTML::labelled_html_object("change status to:", $sel, "member-status-select-label-$op",
			 "member-status-select-label-class");
		$btn = new U3A_BUTTON("button", "set", "member-status-change-button-$op", "member-status-change-button u3a-button",
		  "change_member_status('$op')");
		$result = ["success" => 1, "message" => U3A_HTML::to_html([$div, $lbl, $btn])];
	}
	else
	{
		$result = ["success" => 0, "message" => "member with number $mnum has not been found!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_member_details", "u3a_get_member_details");

function u3a_get_member_details()
{
//	write_log("u3a_get_member_details");
//	write_log($_POST);
	$mnum = $_POST["membership_number"];
	$op = $_POST["op"];
	$suffix = isset($_POST["suffix"]) ? $_POST["suffix"] : "";
	$button = isset($_POST["button"]) ? $_POST["button"] : "yes";
	$groups = isset($_POST["groups"]) ? $_POST["groups"] : "no";
	$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum, "status" => "Current"]);
	if ($mbr)
	{
		$sc = '[u3a_member_details_form member="' . $mbr->id . '" op="' . $op . '" button="' . $button . '" groups="' . $groups . '" suffix="' . $suffix . '"]';
//		write_log("sc = " . $sc);
		$result = ["success" => 1, "message" => do_shortcode($sc)];
	}
	else
	{
		$result = ["success" => 0, "message" => "member with number $mnum has not been found!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_delete_member", "u3a_delete_member");

function u3a_delete_member()
{
	$mnum = isset($_POST["membership_number"]) ? $_POST["membership_number"] : 0;
	$result = ["success" => 0, "message" => "member with number $mnum has not been deleted!"];
	$member_to_delete = null;
	if ($mnum)
	{
		$member_to_delete = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum]);
	}
	else
	{
		$id = isset($_POST["member"]) ? $_POST["member"] : 0;
		if ($id)
		{
			$member_to_delete = U3A_Row::load_single_object("U3A_Members", ["id" => $id]);
		}
	}
	if ($member_to_delete)
	{
		$mnum = $member_to_delete->membership_number;
		$xmbr = new U3A_Members_Deleted();
//		write_log("xmbr1", $xmbr);
		$members_deleted_columns = U3A_Row::get_the_column_names("u3a_members_deleted");
		$member_hash = $member_to_delete->get_as_hash();
		foreach ($members_deleted_columns as $colname)
		{
			if ($colname !== "id" && array_key_exists($colname, $member_hash))
			{
				$xmbr->$colname = $member_hash[$colname];
			}
		}
		$xmbr->oldid = $member_to_delete->id;
//		write_log("xmbr2", $xmbr);
		$perms = U3A_Row::load_array_of_objects("U3A_Permissions", ["members_id" => $member_to_delete->id]);
		if ($perms["total_number_of_rows"])
		{
			$pm = [];
			foreach ($perms["results"] as $perm)
			{
				$pm[] = $perm->groups_id . "+" . $perm->committee_id . "+" . $perm->permission_types_id;
				audit_log("permission deleted", $perm);
				$perm->delete();
			}
			$xmbr->permissions = implode(",", $pm);
		}
		$mbrships = U3A_Row::load_array_of_objects("U3A_Group_Members", ["members_id" => $member_to_delete->id]);
		if ($mbrships["total_number_of_rows"])
		{
			$gm = [];
			foreach ($mbrships["result"] as $mbrship)
			{
				$gm[] = $mbrship->groups_id . "+" . $mbrship->status . "+" . $mbrship->added;
				audit_log("group membership deleted", $mbrship);
				$mbrship->delete();
			}
			$xmbr->groups = implode(",", $gm);
		}
//		write_log("xmbr3", $xmbr);
//			$xmbr = new U3A_Members_Deleted($member_hash);
		$xmbr->save();
		audit_log("member deleted", $member_to_delete);
		$member_to_delete->delete();
		$result = ["success" => 1, "message" => "member with number $mnum has been deleted!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_member_set_status", "u3a_member_set_status");

function u3a_member_set_status()
{
	$mnum = $_POST["membership_number"];
	$status = $_POST["status"];
	$mbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum]);
	if ($mbr)
	{
		$mbr->status = ucfirst(strtolower($status));
//		write_log($mbr->_data);
		$mbr->save();
		$result = ["success" => 1, "message" => "member $mnum: " . $mbr->forename . " " . $mbr->surname . " status changed to $status."];
	}
	else
	{
		$result = ["success" => 0, "message" => "member with number $mnum has not been found!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_create_document_category", "u3a_create_document_category");

function u3a_create_document_category()
{
	$cat = $_POST["name"];
	$memgrp = $_POST["memgrp"];
	$typ = intval($_POST["type"]);
//	$groups_id = U3A_Groups::get_group_id($grp);
	if (($typ === U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($typ === U3A_Documents::PERSONAL_IMAGE_TYPE))
	{
		$category = U3A_Row::load_single_object("U3A_Document_Categories",
			 ["name" => $cat, "groups_id" => -1, "members_id" => $memgrp, "document_type" => $typ]);
	}
	else
	{
		$category = U3A_Row::load_single_object("U3A_Document_Categories",
			 ["name" => $cat, "groups_id" => $memgrp, "document_type" => $typ]);
	}
	if ($category)
	{
		$result = ["success" => 0, "message" => "category with name $cat already exists!"];
	}
	else
	{
		if (($typ === U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($typ === U3A_Documents::PERSONAL_IMAGE_TYPE))
		{
			$category = new U3A_Document_Categories(["name" => $cat, "groups_id" => -1, "members_id" => $memgrp, "document_type" => $typ]);
		}
		else
		{
			$category = new U3A_Document_Categories(["name" => $cat, "groups_id" => $memgrp, "document_type" => $typ]);
		}
		$category->save();
		$result = ["success" => 1, "message" => "category $cat created!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_rename_category", "u3a_rename_category");

function u3a_rename_category()
{
	$cat = trim($_POST["name"]);
	$grp = $_POST["group"];
	$typ = $_POST["type"];
	$catid = $_POST["category"];
	$groups_id = U3A_Groups::get_group_id($grp);
	$category = U3A_Row::load_single_object("U3A_Document_Categories",
		 ["id" => $catid, "groups_id" => $groups_id, "document_type" => $typ]);
	if ($cat)
	{
		if ($category)
		{
			$oldname = $category->name;
			$category->name = $cat;
			$category->save();
			$result = ["success" => 1, "message" => "category with name \"$oldname\" renamed to \"$cat\"!"];
		}
		else
		{
			$result = ["success" => 0, "message" => "category with id $catid not found!"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "cannot use empty name for category!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_delete_category", "u3a_delete_category");

function u3a_delete_category()
{
	$grp = $_POST["group"];
	$typ = $_POST["type"];
	$catid = $_POST["category"];
	if ($catid)
	{
		$dcrels = U3A_Row::load_array_of_objects("U3A_Document_Category_Relationship", ["document_categories_id" => $catid]);
		if ($dcrels["total_number_of_rows"])
		{
			$result = ["success" => 0, "message" => "category with id $catid is not empty!"];
		}
		else
		{
			$groups_id = U3A_Groups::get_group_id($grp);
			$category = U3A_Row::load_single_object("U3A_Document_Categories",
				 ["id" => $catid, "groups_id" => $groups_id, "document_type" => $typ]);
			if ($category)
			{
				$oldname = $category->name;
				audit_log("category deleted", $category);
				$category->delete();
				$result = ["success" => 1, "message" => "category with name \"$oldname\" deleted!"];
			}
			else
			{
				$result = ["success" => 0, "message" => "category with id $catid not found!"];
			}
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "cannot delete default category!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_category_options", "u3a_get_category_options");

function u3a_get_category_options()
{
	$grp = $_POST["group"];
	if (isset($_POST["type"]))
	{
		$typ = $_POST["type"];
	}
	else
	{
		$type = 0;
	}
	if (isset($_POST["selected"]))
	{
		$sel = $_POST["selected"];
	}
	else
	{
		$sel = null;
	}
	$cats = U3A_Document_Categories::get_categories_for_group($grp, $type);
	$ret = "";
	if ($cats)
	{
		$def = new U3A_OPTION("default", 0);
		$opts = array_unshift(U3A_HTML_Utilities::get_options_array_from_object_array($cats, "name", "id", $sel, null), $def);
		$ret = U3A_HTML::to_html($opts);
	}
	echo $ret;
	wp_die();
}

add_action("wp_ajax_u3a_get_category_select", "u3a_get_category_select");

function u3a_get_category_select()
{
	$grp = $_POST["group"];
	if (isset($_POST["type"]))
	{
		$typ = $_POST["type"];
	}
	else
	{
		$type = 0;
	}
	if (isset($_POST["selected"]))
	{
		$sel = $_POST["selected"];
	}
	else
	{
		$sel = null;
	}
	$cats = U3A_Document_Categories::get_categories_for_group($grp, $type);
	$ret = "";
	if ($cats)
	{
		$def = new U3A_OPTION("default", 0);
		$opts = array_unshift(U3A_HTML_Utilities::get_options_array_from_object_array($cats, "name", "id", $sel, null), $def);
		$selct = new U3A_SELECT($opts, "category-select", "u3a-category-select-$grp-$type", "u3a-category-select-class");
		$ret = U3A_HTML::to_html($selct);
	}
	echo $ret;
	wp_die();
}

add_action("wp_ajax_u3a_create_permission", "u3a_create_permission");

function u3a_create_permission()
{
	$groups_id = $_POST["group"];
	$is_committee = $_POST["committee"];
	$who = $_POST["who"];
	$what = $_POST["what"];
	$where = ["groups_id" => $groups_id, "permission_types_id" => $what];
	if ($is_committee)
	{
		$where["committee_id"] = $who;
	}
	else
	{
		$where["members_id"] = $who;
	}
	$permit = U3A_Row::load_single_object("U3A_Permissions", $where);
	if ($permit)
	{
		$result = ["success" => 0, "message" => "permission already exists!"];
	}
	else
	{
		$permit = new U3A_Permissions($where);
		$permit_id = $permit->save();
		if ($permit_id)
		{
			$result = ["success" => 1, "message" => "permission granted!"];
		}
		else
		{
			$result = ["success" => 0, "message" => "permission creation failed!"];
		}
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_remove_permission", "u3a_remove_permission");

function u3a_remove_permission()
{
	$permits = explode(",", $_POST["permit"]);
	$count = 0;
	foreach ($permits as $permissions_id)
	{
		$permit = U3A_Row::load_single_object("U3A_Permissions", ["id" => $permissions_id]);
		if ($permit)
		{
			audit_log("permission deleted", $permit);
			$permit->delete();
			$count++;
		}
	}
	if ($count === count($permits))
	{
		if ($count === 1)
		{
			$result = ["success" => 1, "message" => "permission removed!"];
		}
		else
		{
			$result = ["success" => 1, "message" => "all $count permissions removed!"];
		}
	}
	elseif ($count)
	{
		$result = ["success" => 0, "message" => "only $count permissions removed!"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no permissions removed!"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_update_site", "u3a_update_site");

function u3a_update_site()
{
//	write_log($_POST);
//	write_log($_FILES);
	$uploadedfile = $_FILES['u3a-upload-update-file'];
	$mbr = U3A_Information::u3a_logged_in_user();
	$result = ["success" => 0, "message" => "problem in upload file"];
	if ($uploadedfile['size'] > 0)
	{
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

// Let WordPress handle the upload.
		$upload_overrides = array('test_form' => false);
		$uploaded = wp_handle_upload($uploadedfile, $upload_overrides);
//		$attachment_id = media_handle_upload('u3a-upload-update-file', 0);
//		write_log("got attachment id");
//		write_log($attachment_id);
		if ($uploaded && isset($uploaded['file']) && file_exists($uploaded['file']))
		{
//			write_log("getting file");
//			$file = get_attached_file($attachment_id);
//			write_log("got file");
//			write_log($file);
			$update = new U3A_Update($uploaded['file']);
//			write_log($update);
			$delete = isset($_POST["update-delete"]);
			if (isset($_POST["update-type-all"]))
			{
				$update->u3a_update_all($delete);
			}
			else
			{
				if (isset($_POST["update-type-venues"]))
				{
					$update->u3a_update_venues($delete);
				}
				if (isset($_POST["update-type-members"]))
				{
					$update->u3a_clear_members();
					$update->u3a_update_members($delete);
				}
				if (isset($_POST["update-type-groups"]))
				{
					$update->u3a_update_groups($delete);
				}
				if (isset($_POST["update-type-group-membership"]))
				{
					$update->u3a_update_group_membership($delete);
				}
				if (isset($_POST["update-type-committee"]))
				{
					$update->u3a_update_committee();
				}
			}
			$result = ["success" => 1, "message" => "updated using uploaded file " . $uploadedfile["name"]];
		}
		else
		{
			write_log("wp error");
			$result = ["success" => 0, "message" => "failed to save uploaded file " . $uploadedfile["name"]];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "failed to upload file"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_update_videos", "u3a_update_videos");

function u3a_update_videos()
{
	$vupdate = new U3A_Video_Update();
	$vupdate->u3a_load_videos();
	$result = ["success" => 1, "message" => "videos updated"];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_update_help_videos", "u3a_update_help_videos");

function u3a_update_help_videos()
{
	$vupdate = new U3A_Help_Video_Update();
	$vupdate->u3a_load_videos();
	$result = ["success" => 1, "message" => "videos updated"];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_edit_news", "u3a_edit_news");

function u3a_edit_news()
{
	$newsid = $_POST["newsid"];
	$title = $_POST["title"];
	$text = $_POST["text"];
	$newsitem = U3A_Row::load_single_object("U3A_News", ["id" => $newsid]);
	if ($newsitem)
	{
		$newsitem->title = $title;
		$newsitem->item = $text;
		$newsitem->save();
		$result = ["success" => 1, "message" => "news item '$title' updated"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no such news item"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_add_news", "u3a_add_news");

function u3a_add_news()
{
	$expires = $_POST["expires"];
	$title = $_POST["title"];
	$text = $_POST["text"];
	$members_id = $_POST["members_id"];
	$hash = [
		"title"		 => $title,
		"item"		 => $text,
		"expires"	 => $expires . " 00:00:00",
		"members_id" => $members_id
	];
	$newsitem = new U3A_News($hash);
	$newsitem->save();
	$result = ["success" => 1, "message" => "news item '$title' saved"];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_sendmail", "u3a_sendmail");

function u3a_sendmail()
{
	$to = $_POST["to"];
	$subject = $_POST["subject"];
	$contents = $_POST["contents"];
	if (isset($_POST["sendmailtest"]) && $_POST["sendmailtest"])
	{
		update_option("u3a_testing_email", "1");
	}
	$mailer = U3A_Mail::get_the_mailer();
	$sent = $mailer->sendmail($to, $subject, $contents);
	if ($sent)
	{
		$result = ["success" => 1, "message" => "mail sent"];
	}
	else
	{
		$result = ["success" => 0, "message" => "mail not sent"];
	}
	update_option("u3a_testing_email", "0");
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_send_coordinators_mail", "u3a_send_coordinators_mail");

function u3a_send_coordinators_mail()
{
	$subject = $_POST["subject"];
	$contents = $_POST["contents"];
	$mbrid = $_POST["sender"];
	if (isset($_POST["sendmailtest"]) && $_POST["sendmailtest"])
	{
		update_option("u3a_testing_email", "1");
	}
//	write_log($_POST);
//	write_log($_FILES);
	$nsent = false;
	$mbr = U3A_Row::load_single_object("U3A_Members", ["id" => $mbrid]);
//	$contents = mail_merge($contents1, $member, $group, $committee);
	$attachments = u3a_get_file_attachments();
	if (isset($_POST["members"]) && $_POST["members"])
	{
		$mbrs = explode(',', $_POST["members"]);
		$nsent = U3A_Members::send_mail_to_some($mbrs, $mbr, $subject, $contents, $attachments);
	}
	if ($nsent)
	{
		$result = ["success" => 0, "message" => "mail not sent$nsent"];
	}
	else
	{
		$result = ["success" => 1, "message" => "mail sent"];
	}
	update_option("u3a_testing_email", "0");
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_send_committee_mail", "u3a_send_committee_mail");

function u3a_send_committee_mail()
{
	$subject = $_POST["subject"];
	$contents = $_POST["contents"];
	$mbrid1 = $_POST["sender"];
	$p = strpos($mbrid1, "+");
	if ($p !== FALSE)
	{
		$mbrid = substr($mbrid1, 0, $p);
		$committee_id = substr($mbrid1, $p + 1);
	}
	else
	{
		$mbrid = $mbrid1;
		$committee_id = 0;
	}
	$use_private_email = isset($_POST["sendmailprivate"]) && $_POST["sendmailprivate"];
	$use_cc = isset($_POST["sendmailcc"]) && $_POST["sendmailcc"];
	$use_reply_to = isset($_POST["sendmailrt"]) && $_POST["sendmailrt"];
	$use_no_reply = isset($_POST["sendmailnr"]) && $_POST["sendmailnr"];
	if (isset($_POST["sendmailtest"]) && $_POST["sendmailtest"])
	{
		update_option("u3a_testing_email", "1");
	}
//	write_log($_POST);
//	write_log($_FILES);
	$nsent = "";
	$mbr = U3A_Row::load_single_object("U3A_Members", ["id" => $mbrid]);
//	$group = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
	$attachments = u3a_get_file_attachments();
	if (isset($_POST["members"]) && $_POST["members"])
	{
		$mbrs = explode(',', $_POST["members"]);
		$nsent = U3A_Committee::send_mail_to_some($committee_id, $mbrs, $mbr, $subject, $contents, $attachments,
			 $use_private_email, $use_cc, $use_no_reply, $use_reply_to);
	}
	else
	{
		$nsent = U3A_Committee::send_mail_to_all($committee_id, $mbr, $subject, $contents, $attachments, $use_private_email,
			 $use_cc, $use_no_reply, $use_reply_to);
	}
	if ($nsent)
	{
		$result = ["success" => 0, "message" => "mail not sent$nsent"];
	}
	else
	{
		$result = ["success" => 1, "message" => "mail sent"];
	}
	update_option("u3a_testing_email", "0");
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_send_group_mail", "u3a_send_group_mail");

function u3a_send_group_mail()
{
	$subject = $_POST["subject"];
	$contents = $_POST["contents"];
	$mbrid = $_POST["sender"];
	$grpid = isset($_POST["group"]) ? $_POST["group"] : 0;
	if (isset($_POST["sendmailtest"]) && $_POST["sendmailtest"])
	{
		update_option("u3a_testing_email", "1");
	}
//	write_log($_POST);
//	write_log($_FILES);
	$nsent = "";
	$mbr = U3A_Row::load_single_object("U3A_Members", ["id" => $mbrid]);
	$groups_id = $_POST["group"];
	$group = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
	$attachments = u3a_get_file_attachments();
	if (isset($_POST["members"]) && $_POST["members"])
	{
		$mbrs = explode(",", $_POST["members"]);
		$nsent = $group->send_mail_to_some($mbrs, $mbr, $subject, $contents, $attachments);
	}
	else
	{
		$nsent = $group->send_mail_to_all($mbr, $subject, $contents, $attachments);
	}
	if ($nsent)
	{
		$result = ["success" => 0, "message" => "mail not sent$nsent"];
	}
	else
	{
		$result = ["success" => 1, "message" => "mail sent"];
	}
	update_option("u3a_testing_email", "0");
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_send_individual_mail", "u3a_send_individual_mail");

function u3a_send_individual_mail()
{
//	write_log("send individual mail");
	$subject = $_POST["subject"];
	$contents = $_POST["contents"];
	$mbrid1 = $_POST["sender"];
	if (isset($_POST["sendmailtest"]) && $_POST["sendmailtest"])
	{
		update_option("u3a_testing_email", "1");
	}
	$p = strpos($mbrid1, "+");
	$u3a = U3A_Information::u3a_get_u3a_name() . "U3A";
	if ($p !== FALSE)
	{
		$mbrid = substr($mbrid1, 0, $p);
		$committee_id = substr($mbrid1, $p + 1);
		if ($committee_id)
		{
			$cm = U3A_Committee::get_committee_from_id($committee_id);
		}
		else
		{
			$cm = U3A_Committee::get_committee($mbrid);
		}
		if ($cm)
		{
			$nr = "$u3a " . $cm->role . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
			$from = U3A_Utilities::strip_all_slashes($cm->email);
			$sender_id = $committee_id;
			$from_committee = true;
		}
		else
		{
			$nr = "$u3a Message <" . U3A_Mail::get_no_reply_mailbox() . ">";
			$from = U3A_Members::get_email_address($mbrid);
			$sender_id = $mbrid;
			$from_committee = false;
		}
	}
	else
	{
		$mbrid = $mbrid1;
		$committee_id = 0;
		$sender_id = $mbrid;
		$from_committee = false;
		$from = U3A_Members::get_email_address($mbrid);
		$nr = "$u3a Message <" . U3A_Mail::get_no_reply_mailbox() . ">";
	}
	$rcpt = $_POST["recipient"];
	$to = U3A_Members::get_email_address($_POST["members"]);
//	write_log("ajax send");
//	write_log($to);
//	write_log($sender_id);
//	write_log($_POST);
	$attachments = u3a_get_file_attachments();
	$nsent = U3A_Members::send_mail_to_some(explode(',', $_POST["members"]), $mbrid, $subject, $contents, $attachments);
//	$sent = U3A_Sent_Mail::send($sender_id, $to, $subject, $contents, null, null, $nr, $from, $attachments, true, $from_committee);
	if ($nsent)
	{
		$result = ["success" => 0, "message" => "mail not sent$nsent"];
	}
	else
	{
		$result = ["success" => 1, "message" => "mail sent"];
	}
	update_option("u3a_testing_email", "0");
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_send_contact_mail", "u3a_send_contact_mail");
add_action("wp_ajax_nopriv_u3a_send_contact_mail", "u3a_send_contact_mail");

function u3a_send_contact_mail()
{
	write_log("send contact mail");
	$subject = $_POST["subject"];
	$contents = $_POST["contents"];
	$from = $_POST["from"];
	$to = $_POST["recipient"];
	if (isset($_POST["sendmailtest"]) && $_POST["sendmailtest"])
	{
		update_option("u3a_testing_email", "1");
	}
	write_log($_POST);
	$attachments = u3a_get_file_attachments();
	$nsent = U3A_Sent_Mail::send(0, $to, $subject, $contents, null, null, $from, $from, $attachments);
	if ($nsent)
	{
		$result = ["success" => 1, "message" => "mail sent"];
	}
	else
	{
		$result = ["success" => 0, "message" => "mail not sent $nsent"];
	}
	update_option("u3a_testing_email", "0");
	echo json_encode($result);
	wp_die();
}

function u3a_get_file_attachments()
{
	$ret = [];
	foreach ($_FILES as $attachname => $file)
	{
		if (!$file["error"])
		{
			$upload_overrides = ['test_form' => false];
			$f = wp_handle_upload($file, $upload_overrides);
			$ret[] = $f["file"];
		}
	}
	for ($n = 0; $n < U3A_Mail::MAX_ATTACHMENTS; $n++)
	{
		if (isset($_POST["document-$n"]))
		{
			$f = get_attached_file($_POST["document-$n"]);
			if ($f)
			{
				$ret[] = $f;
			}
		}
		if (isset($_POST["image-$n"]))
		{
			$f = get_attached_file($_POST["image-$n"]);
			if ($f)
			{
				$ret[] = $f;
			}
		}
	}
	return $ret;
}

add_action("wp_ajax_u3a_reload_group_page", "u3a_reload_group_page");

function u3a_reload_group_page()
{
	$groups_id = $_POST["group"];
	$tab = $_POST["tab"];
	$spoiler = $_POST["spoiler"];
//	write_log($groups_id);
//	write_log($tab);
//	write_log($spoiler);
	echo do_shortcode('[u3a_group_page group="' . $groups_id . '" tab="' . $tab . '" spoiler="' . $spoiler . '"]');
	wp_die();
}

add_action("wp_ajax_u3a_reload_committee_manage", "u3a_reload_committee_manage");

function u3a_reload_committee_manage()
{
	$spoiler = $_POST["spoiler"];
//	write_log($groups_id);
//	write_log($tab);
//	write_log($spoiler);
	echo do_shortcode('[u3a_manage_committee_documents spoiler="' . $spoiler . '"]');
	wp_die();
}

add_action("wp_ajax_u3a_reload_committee_manage_permissions", "u3a_reload_committee_manage_permissions");

function u3a_reload_committee_manage_permissions()
{
	echo do_shortcode('[u3a_manage_permissions committee="1"]');
	wp_die();
}

add_action("wp_ajax_u3a_reload_committee_manage_groups", "u3a_reload_committee_manage_groups");

function u3a_reload_committee_manage_groups()
{
	echo do_shortcode('[u3a_manage_groups]');
	wp_die();
}

add_action("wp_ajax_u3a_assume_identity", "u3a_assume_identity");

function u3a_assume_identity()
{
	$mnum = $_POST["mbr"];
	update_option("assumed_identity", $mnum);
	echo home_url();
	wp_die();
}

add_action("wp_ajax_u3a_list_members", "u3a_list_members");

function u3a_list_members()
{
	if (isset($_POST["initial"]))
	{
		$where = ["status" => "Current", "surname~%" => $_POST["initial"]];
	}
	else
	{
		$where = ["status" => "Current"];
	}
	$tam = U3A_Utilities::get_post("TAM", null);
	if ($tam !== null)
	{
		if ($tam)
		{
			$where["TAM"] = $tam;
		}
		else
		{
			$where["TAM"] = [0, 2];
		}
	}
	$nl = U3A_Utilities::get_post("newsletter", null);
	if ($nl !== null)
	{
		$where["newsletter"] = $nl;
	}
	$ga = U3A_Utilities::get_post("gift_aid", null);
	if ($ga !== null)
	{
		$where["gift_aid"] = $ga;
	}
	$pt = U3A_Utilities::get_post("payment_type", null);
	if ($pt !== null)
	{
		if (strpos($pt, '|') !== FALSE)
		{
			$where["payment_type"] = explode('|', $pt);
		}
		else
		{
			$where["payment_type"] = $pt;
		}
	}
	$em = U3A_Utilities::get_post("email", null);
	if ($em !== null)
	{
		if (is_numeric($em))
		{
			if ($em)
			{
				$where["email<>"] = null;
			}
			else
			{
				$where["email"] = null;
			}
		}
		else
		{
			$where["email%~%"] = $em;
		}
	}
	$members = U3A_Row::load_array_of_objects("U3A_Members", $where, "surname, forename");
//	write_log($where);
//	write_log($members);
	$h = new U3A_H(6, $members["total_number_of_rows"] . " members");
	$links = [];
	foreach ($members["result"] as $mbr)
	{
		if ($mbr->renewal_needed)
		{
			$mbrb = new U3A_SPAN($mbr->get_formal_name() . " (" . $mbr->membership_number . ")", null, "u3a-red");
			$text = $mbrb->to_html();
		}
		else
		{
			$text = $mbr->get_formal_name() . " (" . $mbr->membership_number . ")";
		}
		$ital = $mbr->wpid ? " u3a-italic" : "";
		$id = new U3A_INPUT("hidden", "members_id", "u3a-get-member-details-id-" . $mbr->membership_number,
		  "u3a-get-member-details-id-class", $mbr->id);
		$a = new U3A_A("#", $text, NULL, "u3a-get-member-details-link-class$ital",
		  "u3a_get_member_details('" . $mbr->membership_number . "')");
		$div = new U3A_DIV(null, "u3a-member-details-" . $mbr->membership_number,
		  "u3a-member-list-details-class u3a-invisible");
		$links[] = new U3A_DIV([$a, $id, $div], null, "u3a-get-member-details-div-class");
	}
	echo U3A_HTML::to_html([$h, $links]);
	wp_die();
}

add_action("wp_ajax_u3a_change_documents", "u3a_change_documents");

function u3a_change_documents()
{
	$alldocs = U3A_Row::load_array_of_objects("U3A_Documents");
	foreach ($alldocs["result"] as $doc)
	{
		$dcrel = new U3A_Document_Category_Relationship(["documents_id" => $doc->id, "document_categories_id" => $doc->category]);
		$dcrel->save();
	}
	$result = ["success" => 1, "message" => "conversion complete"];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_download_group_table", "u3a_download_group_table");

function u3a_download_group_table()
{
	$grps1 = U3A_Row::load_array_of_objects("U3A_Groups", null, "name");
	$grps = [];
	foreach ($grps1["result"] as $grp)
	{
		if (!U3A_Utilities::starts_with(strtoupper($grp->name), "PROPOSED "))
		{
			$grps[] = $grp;
		}
	}
	$ugt = new U3A_Group_Table();
	$url = $ugt->write_table($grps);
	$result = ["success" => 1, "arg" => $url];
//	write_log("url: " . $url);
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_set_preferred_role", "u3a_set_preferred_role");

function u3a_set_preferred_role()
{
//	write_log($_POST);
	if (isset($_POST["member_id"]) && isset($_POST["committee_id"]))
	{
		U3A_Preferred_Role::set_preferred_role($_POST["member_id"], $_POST["committee_id"]);
	}
	$result = ["success" => 1];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_add_member_to_group", "u3a_get_add_member_to_group_action");

function u3a_get_add_member_to_group_action()
{
	$grpid = isset($_POST["groups_id"]) ? $_POST["groups_id"] : 0;
	if ($grpid)
	{
		$result = ["success" => 1, "message" => do_shortcode('[u3a_add_member_to_group group="' . $grpid . '"]')];
	}
	else
	{
		$result = ["success" => 0, "message" => "no group selected"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_do_remove_member_from_group", "u3a_do_remove_member_from_group_action");

function u3a_do_remove_member_from_group_action()
{
	$grpid = isset($_POST["groups_id"]) ? $_POST["groups_id"] : 0;
	if ($grpid)
	{
		$result = ["success" => 1, "message" => do_shortcode('[u3a_remove_member_from_group group="' . $grpid . '"]')];
	}
	else
	{
		$result = ["success" => 0, "message" => "no group selected"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a-sort-documents", "u3a_sort_documents");

function u3a_sort_documents()
{
//    [type] => 1
//    [groups_id] => 25
//    [category] => 4
//    [documents] => 65,62,66,64,22,21,20
	$type = isset($_POST["type"]) ? $_POST["type"] : 0;
	$category = isset($_POST["category"]) ? $_POST["category"] : 0;
	$documents = isset($_POST["documents"]) ? $_POST["documents"] : "";
	$docs = explode(",", $documents);
	$tp = U3A_Documents::get_type_description($type);
	if ($docs)
	{
		$dcrel = U3A_Row::load_hash_of_all_objects("U3A_Document_Category_Relationship",
			 ["document_categories_id" => $category], "documents_id");
		for ($n = 0; $n < count($docs); $n++)
		{
			if (isset($dcrel[$docs[$n]]))
			{
				$dcrel[$docs[$n]]->sort_order = $n + 1;
				$dcrel[$docs[$n]]->save();
				unset($dcrel[$docs[$n]]);
			}
		}
		foreach ($dcrel as $docid => $dcr)
		{
			$dcr->sort_order = 0;
			$dcr->save();
		}
		$result = ["success" => 1, "message" => "sorted!"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no $tp" . "s to sort"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_document_details", "u3a_get_document_details");

function u3a_get_document_details()
{
	$id = isset($_POST["document"]) ? $_POST["document"] : 0;
	$doc = U3A_Row::load_single_object("U3A_Documents", ["id" => $id]);
	if ($doc)
	{
		$result = ["success" => 1, "title" => $doc->title, "author" => $doc->author, "visibility" => $doc->visibility];
	}
	else
	{
		$result = ["success" => 0];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_edit_document_details", "u3a_edit_document_details");

function u3a_edit_document_details()
{
	$id = isset($_POST["document"]) ? $_POST["document"] : 0;
	$title = isset($_POST["title"]) ? $_POST["title"] : "";
	$author = isset($_POST["author"]) ? $_POST["author"] : "";
	$visibility = U3A_Utilities::get_post("visibility", 0);
	$doc = U3A_Row::load_single_object("U3A_Documents", ["id" => $id]);
	if ($doc && $title)
	{
		$doc->title = $title;
		$doc->author = $author;
		$doc->visibility = $visibility;
		$attachment_title = $title . ($author ? " by $author" : "");
		$doc->save();
		$doc_meta = array(
			'ID'				 => $doc->attachment_id, // Specify the image (ID) to be updated
			'post_title'	 => $attachment_title, // Set image Title to sanitized title
			'post_excerpt'	 => $attachment_title, // Set image Caption (Excerpt) to sanitized title
			'post_content'	 => $attachment_title, // Set image Description (Content) to sanitized title
		);
		if ($doc->document_type == U3A_Documents::GROUP_IMAGE_TYPE || $doc->document_type == U3A_Documents::COMMITTEE_IMAGE_TYPE)
		{
			// Set the image Alt-Text
			update_post_meta($doc->attachment_id, '_wp_attachment_image_alt', $attachment_title);
		}

// Set the image meta (e.g. Title, Excerpt, Content)
		wp_update_post($doc_meta);
		$result = ["success" => 1, "message" => "document edited"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no document found with id $id"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_test_membership_card", "u3a_test_membership_card");

function u3a_test_membership_card()
{
	$mnum = $_POST["membership_number"];
	$mbr = U3A_Members::get_member_from_membership_number($mnum);
	$path = U3A_PDF::get_membership_card($mbr);
	if ($path)
	{
		$result = ["success" => 1, "message" => $path["path"]];
	}
	else
	{
		$result = ["success" => 0, "message" => "no path"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_address_labels", "u3a_address_labels");

function u3a_address_labels()
{
	$mbrs = U3A_Members::get_all_members(["newsletter" => 1, "status" => "Current"]);
	$path = U3A_PDF_Label::get_address_labels($mbrs);
	if ($path)
	{
		$result = ["success" => 1, "arg" => $path];
	}
	else
	{
		$result = ["success" => 0, "message" => "no path"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_test_mailing_list", "u3a_test_mailing_list");

function u3a_test_mailing_list()
{
	$mailer = U3A_Mail::get_the_mailer();
	$mailer->get_mailing_lists();
	$which_list = $_POST["mailing_list"];
	$mailing_list = $which_list . "@" . U3A_Information::u3a_get_mailing_list_domain();
	$members = $mailer->mailing_list_members($mailing_list);
}

add_action("wp_ajax_u3a_create_mailing_list", "u3a_create_mailing_list");

function u3a_create_mailing_list()
{
	$groups_id = U3A_Utilities::get_post("group");
	$name = U3A_Utilities::get_post("name");
	$result = ["success" => 0, "message" => "invalid call to mailing list create"];
	if ($name)
	{
		$config = U3A_CONFIG::get_the_config();
		$list_address = $name . '@' . $config->MAILING_LIST_DOMAIN;
		$mailer = U3A_Mail::get_the_mailer();
		if ($mailer->mailing_list_exists($list_address))
		{
			$result = ["success" => 0, "message" => "list with that name already exists"];
		}
		else
		{
			if ($groups_id)
			{
				$grp = U3A_Groups::get_group($groups_id);
				$members = U3A_Group_Members::get_mailing_list_members($grp);
				$gname = $grp->name;
//				$list = null;
//				write_log("about to create list", $gname, $list_address, $members);
				$list = $mailer->create_mailing_list($gname, "The $gname group at " . $config->U3ANAME . " U3A", $list_address,
				  $members);
				if ($list)
				{
					$grp->set_mailing_list($name);
					$result = ["success" => 1, "message" => "list $list_address successfully created"];
					send_mailing_list_email($name);
				}
				else
				{
					$result = ["success" => 0, "message" => "list $list_address not created"];
				}
			}
			else
			{
				$mbrs = U3A_Utilities::get_post("members");
				if ($mbrs)
				{
					$member_ids = explode(',', $mbrs);
					$members = [];
					foreach ($member_ids as $id)
					{
						$mbr = U3A_Members::get_member($id);
						if ($mbr)
						{
							$member = $mbr->get_mailing_list_member();
							if ($member)
							{
								$members[] = $member;
							}
						}
					}
					$list = null;
					write_log("about to create list", $name, $list_address, $members);
//					$list = $mailer->create_mailing_list($name, "The $name group at " . $cfg->U3ANAME . " U3A", $list_address, $members);
					if ($list)
					{
						$result = ["success" => 1, "message" => "list $list_address successfully created"];
						send_mailing_list_email($name);
					}
					else
					{
						$result = ["success" => 0, "message" => "list $list_address not created"];
					}
				}
				else
				{
					$result = ["success" => 0, "message" => "no members supplied for list"];
				}
			}
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no name supplied"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_set_option", "u3a_set_option");

function u3a_set_option()
{
	$optname = $_POST["option"];
	$optval = $_POST["optval"];
	$done = update_option($optname, $optval);
//	write_log("$optname set to $optval: " . $done);
	if ($done)
	{
		$result = ["success" => 1, "message" => "option $optname updated to $optval"];
	}
	else
	{
		$result = ["success" => 0, "message" => "option $optname not updated"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_mailing_list", "u3a_mailing_list");

function u3a_mailing_list()
{

}

add_action("wp_ajax_u3a_contact_details", "u3a_contact_details");

function u3a_contact_details()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	$html = "";
	if ($members_id)
	{
		$html = do_shortcode('[u3a_view_member_contact_details member="' . $members_id . '"]');
	}
	$result = ["success" => 1, "arg" => $html];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_accept_from_waiting_list", "u3a_accept_from_waiting_list");

function u3a_accept_from_waiting_list()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	$groups_id = U3A_Utilities::get_post("group", 0);
	if ($members_id && $groups_id)
	{
		$gm = U3A_Row::load_single_object("U3A_Group_Members",
			 ["groups_id" => $groups_id, "members_id" => $members_id, "status" => 4]);
		if ($gm)
		{
			$gm->status = 0;
			$gm->save();
			$result = ["success" => 1, "message" => "member accepted into group"];
		}
		else
		{
			["success" => 0, "message" => "member not on waiting list"];
		}
	}
	{
		["success" => 0, "message" => "member or group not specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_remove_from_waiting_list", "u3a_remove_from_waiting_list");

function u3a_remove_from_waiting_list()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	$groups_id = U3A_Utilities::get_post("group", 0);
	if ($members_id && $groups_id)
	{
		$gm = U3A_Row::load_single_object("U3A_Group_Members",
			 ["groups_id" => $groups_id, "members_id" => $members_id, "status" => 4]);
		if ($gm)
		{
			$gm->status = 0;
			$gm->delete();
			$result = ["success" => 1, "message" => "member removed from group waiting list"];
		}
		else
		{
			["success" => 0, "message" => "member not on waiting list"];
		}
	}
	{
		["success" => 0, "message" => "member or group not specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_test_reply_preference", "u3a_test_reply_preference");

function u3a_test_reply_preference()
{
	$email = U3A_Utilities::get_post("email", null);
	$mailer = U3A_Mail::get_the_mailer();
//	$mailer->update_reply_preference($email);
	$mailer->update_all_reply_preferences();
	$result = ["success" => 1, "message" => "reply preference updated"];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_download_address_list", "u3a_download_address_list");

function u3a_download_address_list()
{
	$where1 = U3A_Utilities::get_post("where", null);
	$where = ["status" => "Current"];
	$where1a = explode("&", $where1);
	foreach ($where1a as $w)
	{
		$wa = explode("=", $w);
		$where[$wa[0]] = $wa[1];
	}
	$mbrs = U3A_Members::get_all_members($where);
	$al = new U3A_Address_List();
	$path = $al->write_list($mbrs);
	if ($path)
	{
		$result = ["success" => 1, "arg" => $path];
	}
	else
	{
		$result = ["success" => 0, "message" => "no path"];
	}
	echo json_encode($result);
	wp_die();
//	write_log($where);
}

add_action("wp_ajax_u3a_download_gift_aid", "u3a_download_gift_aid");

function u3a_download_gift_aid()
{
	$from_date = U3A_Utilities::get_post("from", null);
	$to_date = U3A_Utilities::get_post("to", null);
	if ($from_date)
	{
		$payments = U3A_Subscriptions::get_payments($from_date, $to_date, TRUE);
		$ga = new U3A_GiftAid_List();
		$path = $ga->write_list($payments);
		if ($path)
		{
			$result = ["success" => 1, "arg" => $path];
		}
		else
		{
			$result = ["success" => 0, "message" => "no path"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no from date set"];
	}
	echo json_encode($result);
	wp_die();
//	write_log($where);
}

add_action("wp_ajax_u3a_members_download", "u3a_members_download");

function u3a_members_download()
{
	$memberids = U3A_Utilities::get_post("members", null);
	$column_names = U3A_Utilities::get_post("colnames", null);
	$column_headers = U3A_Utilities::get_post("colhdrs", null);
	$fmt = U3A_Utilities::get_post("format", null);
	$member_ids = explode(",", $memberids);
	$members = U3A_Members::get_members($member_ids);
	$colnames = explode(",", $column_names);
	$colhdrs = explode(",", $column_headers);
	$al = new U3A_Members_List();
	$path = $al->write_list($members, $colnames, $colhdrs, $fmt);
	if ($path)
	{
		$result = ["success" => 1, "arg" => $path];
	}
	else
	{
		$result = ["success" => 0, "message" => "no path"];
	}
	echo json_encode($result);
	wp_die();
//	write_log($where);
}

add_action("wp_ajax_u3a_renew_membership", "u3a_renew_membership");

function u3a_renew_membership()
{
	write_log($_POST);
	$members_id = U3A_Utilities::get_post("member", 0);
	if ($members_id)
	{
		$mbr = U3A_Members::get_member($members_id);
	}
	else
	{
		$mnum = U3A_Utilities::get_post("membership_number", 0);
		if ($mnum)
		{
			$mbr = U3A_Members::get_member_from_membership_number($mnum);
			$members_id = $mbr->id;
		}
	}
	if ($mbr)
	{
		$renew = $mbr->renew_membership();
		send_renewal_email($mbr);
		$amount = isset($_POST["amount"]) ? $_POST["amount"] : U3A_Information::u3a_get_renewal_rate($mbr->associate !== null);
		$mth = U3A_Timestamp_Utilities::month_from_0();
		$renewmth = U3A_Timestamp_Utilities::month_from_0(U3A_CONFIG::u3a_get_as_timestamp("RENEWALS_FROM"));
		$subyr = U3A_Timestamp_Utilities::year();
		if ($mth < $renewmth)
		{
			$subyr++;
		}
		$hash = ["members_id" => $members_id, "amount" => $amount, "subscription_year" => $subyr];
		$sub = U3A_Row::load_single_object("U3A_Subscriptions", $hash);
		if (!$sub)
		{
			$sub = new U3A_Subscriptions($hash);
			$sub->save();
		}
		$result = ["success" => 1, "message" => "membership has been renewed"];
	}
	else
	{
		$result = ["success" => 0, "message" => "membership has not been renewed, please contact the membership secretary"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_update_information", "u3a_update_information");

function u3a_update_information()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	$info = U3A_Utilities::get_post("info", null);
	write_log("info", $info);
	if ($members_id)
	{
		$mi = U3A_Row::load_single_object("U3A_Members_Information", ["members_id" => $members_id]);
		if ($mi)
		{
			$mi->information = $info;
		}
		else
		{
			$mi = new U3A_Members_Information(["members_id" => $members_id, "information" => $info]);
		}
		$mi->save();
		$result = ["success" => 1, "message" => "your information has been saved"];
	}
	else
	{
		$result = ["success" => 0, "message" => "your information has not been saved, no member specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_personal_page", "u3a_get_personal_page");

function u3a_get_personal_page()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	$manage = U3A_Utilities::get_post("manage", "no");
	if ($members_id)
	{
		$html = do_shortcode('[u3a_members_personal manage="' . $manage . '" member="' . $members_id . '"]');
		$result = ["success" => 1, "arg" => $html];
	}
	else
	{
		$result = ["success" => 0, "message" => "failed to return personal page, no member specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_header_image", "u3a_get_header_image");
add_action("wp_ajax_nopriv_u3a_get_header_image", "u3a_get_header_image");

function u3a_get_header_image()
{
	$type = U3A_Utilities::get_post("type", U3A_Documents::COMMITTEE_IMAGE_TYPE);
	$categories_id = U3A_Utilities::get_post("cat", 0);
	$groups_id = U3A_Utilities::get_post("group", 0);
	$members_id = U3A_Utilities::get_post("member", 0);
	$ndx = U3A_Utilities::get_post("index", null);
	$total = U3A_Utilities::get_post("total", null);
	$imgs1 = U3A_Documents::get_header_images($groups_id, $members_id);
	$imgs = $imgs1["images"];
	$categories_id = $imgs1["categories_id"];
//	if ($members_id)
//	{
//		$imgs = U3A_Documents::get_attachment_ids_for_member($members_id, $type, $categories_id);
//	}
//	else
//	{
//		$imgs = U3A_Documents::get_attachment_ids_for_group($groups_id, $type, $categories_id);
//	}
	if ($imgs)
	{
		if ($ndx === null)
		{
			$ndx = rand(0, count($imgs) - 1);
		}
		else
		{
			$ndx = ($ndx + 1) % count($imgs);
		}
		$src = wp_get_attachment_url($imgs[$ndx]);
		$title = get_the_title($imgs[$ndx]);
		$result = ["success" => 1, "arg" => $src . '|' . $title . '|' . $ndx . '|' . count($imgs)];
	}
	else
	{
		$result = ["success" => 0, "message" => "no header images found"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_add_post", "u3a_add_post");

function u3a_add_post()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	$text = U3A_Utilities::get_post("text", null);
	$replyto = U3A_Utilities::get_post("replyto", 0);
	$title = U3A_Utilities::get_post("title", null);
	$mbr = U3A_Information::u3a_logged_in_user();
	if (!$title)
	{
		$title = date('\O\n jS F \a\t G:i ') . $mbr->get_name() . " said....";
	}
	else
	{
		$title = date('jS F G:i ') . $mbr->get_name() . ": <b>" . $title . "</b>";
	}
	write_log($mbr, $groups_id, $text);
	if ($mbr && $groups_id && $text)
	{
		$hash = [
			"groups_id"	 => $groups_id,
			"members_id" => $mbr->id,
			"title"		 => $title,
			"contents"	 => $text
		];
		if ($replyto)
		{
			$hash["reply_to"] = $replyto;
		}
		$post = new U3A_Forum_Posts($hash);
		$post->save();
		$result = ["success" => 1, "message" => "your message has been posted"];
	}
	else
	{
		$result = ["success" => 0, "message" => "message not posted"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_delete_post", "u3a_delete_post");

function u3a_delete_post()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	$thread_id = U3A_Utilities::get_post("thread", 0);
	if ($groups_id && $thread_id)
	{
		U3A_Forum_Posts::delete_thread($groups_id, $thread_id);
		$result = ["success" => 1, "message" => "thread has been deleted"];
	}
	else
	{
		$result = ["success" => 0, "message" => "thread not deleted"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_add_friend", "u3a_add_friend");

function u3a_add_friend()
{
	$member = U3A_Utilities::get_post("member", 0);
	$friend = U3A_Utilities::get_post("friend", 0);
	if ($member && $friend)
	{
		$fname = U3A_Members::get_member_name($friend);
		$fr = U3A_Row::load_single_object("U3A_Friends", ["members_id" => $member, "friends_id" => $friend]);
		if ($fr)
		{
			$result = ["success" => 0, "message" => "$fname is already your friend"];
		}
		else
		{
			$fr = new U3A_Friends(["members_id" => $member, "friends_id" => $friend]);
			$fr->save();
			$result = ["success" => 1, "message" => "$fname is now your friend"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "member ($member) or friend ($friend) not specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_remove_friend", "u3a_remove_friend");

function u3a_remove_friend()
{
	$member = U3A_Utilities::get_post("member", 0);
	$friend = U3A_Utilities::get_post("friend", 0);
	if ($member && $friend)
	{
		$fname = U3A_Members::get_member_name($friend);
		$fr = U3A_Row::load_single_object("U3A_Friends", ["members_id" => $member, "friends_id" => $friend]);
		if ($fr)
		{
			$fr->delete();
			$result = ["success" => 1, "message" => "$fname removed as friend"];
		}
		else
		{
			$result = ["success" => 0, "message" => "$fname is not your friend"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "member ($member) or friend ($friend) not specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_group_mailing_list", "u3a_group_mailing_list");

function u3a_group_mailing_list()
{
	$name = U3A_Utilities::get_post("name", null);
	$members = U3A_Utilities::get_post("members", null);
	$operatiom = U3A_Utilities::get_post("op", "save");
	$groups_id = U3A_Utilities::get_post("group", 0);
	$list_id = U3A_Utilities::get_post("list", 0);
	if ($name || $list_id)
	{
		switch ($operatiom) {
			case "save":
				{
					if ($members && $name)
					{
						$list = U3A_Row::load_single_object("U3A_Email_Lists", ["name" => $name, "groups_id" => $groups_id]);
						if ($list)
						{
							$result = ["success" => 0, "message" => "a list $name already exists"];
						}
						else
						{
							$list = new U3A_Email_Lists(["name" => $name, "groups_id" => $groups_id]);
							$list_id = $list->save();
							$members_ids = explode(",", $members);
							foreach ($members_ids as $members_id)
							{
								$m = new U3A_Email_List_Members(["email_lists_id" => $list_id, "members_id" => $members_id]);
								$m->save();
							}
							$result = ["success" => 1, "message" => "list $name has been created!"];
						}
					}
					else
					{
						$result = ["success" => 0, "message" => "no name or no members specified"];
					}
					break;
				}
			case "update":
				{
					if ($members && $list_id)
					{
						$list = U3A_Row::load_single_object("U3A_Email_Lists", ["id" => $list_id]);
						if (!$list)
						{
							$result = ["success" => 0, "message" => "list $name does not exist"];
						}
						else
						{
							$members_ids = explode(",", $members);
							$current = U3A_Email_Lists::get_list_member_ids($list->id);
							$to_add = array_diff($members_ids, $current);
							$to_delete = array_diff($current, $members_ids);
							foreach ($to_add as $members_id)
							{
								$m = new U3A_Email_List_Members(["email_lists_id" => $list_id, "members_id" => $members_id]);
								$m->save();
							}
							foreach ($to_delete as $members_id)
							{
								$m = U3A_Row::load_single_object("U3A_Email_List_Members",
									 ["email_lists_id" => $list_id, "members_id" => $members_id]);
								$m->delete();
							}
							$result = ["success" => 1, "message" => "list $name has been updated!"];
						}
					}
					else
					{
						$result = ["success" => 0, "message" => "no members specified"];
					}
					break;
				}
			case "delete":
				{
					if ($list_id)
					{
						$list = U3A_Row::load_single_object("U3A_Email_Lists", ["id" => $list_id]);
						if ($list)
						{
							$name = $list->name;
						}
					}
					elseif ($name)
					{
						$list = U3A_Row::load_single_object("U3A_Email_Lists", ["name" => $name]);
					}
					if (!$list)
					{
						$it = $name ? $name : $list_id;
						$result = ["success" => 0, "message" => "list $it does not exist"];
					}
					else
					{
						$list_id = $list->id;
						$list->delete();
						$members = U3A_Email_Lists::get_list_members($list_id);
						foreach ($members as $member)
						{
							$member->delete();
						}
						$result = ["success" => 1, "message" => "list $name has been deleted!"];
					}
					break;
				}
			default:
				{
					$result = ["success" => 0, "message" => "invalid operation"];
					break;
				}
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no list name specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_load_group_mailing_list", "u3a_load_group_mailing_list");

function u3a_load_group_mailing_list()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	$list_id = U3A_Utilities::get_post("list", 0);
	if ($list_id)
	{
		$members = U3A_Email_Lists::get_list_member_ids($list_id);
		$result = ["success" => 1, "message" => implode(",", $members)];
	}
	else
	{
		$result = ["success" => 0, "message" => "no list specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_new_link_section", "u3a_new_link_section");

function u3a_new_link_section()
{
	$name = U3A_Utilities::get_post("name", null);
	$groups_id = U3A_Utilities::get_post("group", 0);
	$members_id = U3A_Utilities::get_post("member", 0);
	if ($name)
	{
		$linksec = new U3A_Link_Sections(["name" => $name, "groups_id" => $groups_id, "members_id" => $members_id]);
		$linksec->save();
		$result = ["success" => 1, "message" => "section $name created"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no section name specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_new_link", "u3a_new_link");

function u3a_new_link()
{
	$section = U3A_Utilities::get_post("section", null);
	$description = U3A_Utilities::get_post("description", null);
	$url = U3A_Utilities::get_post("url", null);
	write_log($section, $description, $url);
	if ($section && $url)
	{
		if (!$description)
		{
			$description = $url;
		}
		$link = new U3A_Links(["description" => $description, "url" => $url, "sections_id" => U3A_Link_Sections::get_sections_id($section)]);
		$link->save();
		$result = ["success" => 1, "message" => "link to $url created"];
	}
	elseif ($url)
	{
		$result = ["success" => 0, "message" => "no section specified"];
	}
	elseif ($section)
	{
		$result = ["success" => 0, "message" => "no url specified"];
	}
	else
	{
		$result = ["success" => 0, "message" => "no section or url specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_reload_manage_links", "u3a_reload_manage_links");

function u3a_reload_manage_links()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	$members_id = U3A_Utilities::get_post("member", 0);
	echo do_shortcode('[u3a_manage_links member="' . $members_id . '" group="' . $groups_id . '"]');
	wp_die();
}

add_action("wp_ajax_u3a_reload_links", "u3a_reload_links");

function u3a_reload_links()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	$members_id = U3A_Utilities::get_post("member", 0);
	echo do_shortcode('[u3a_links member="' . $members_id . '" group="' . $groups_id . '"]');
	wp_die();
}

add_action("wp_ajax_u3a_get_option_value", "u3a_get_option_value");

function u3a_get_option_value()
{
	$category = U3A_Utilities::get_post("category", 0);
	$option_name = U3A_Utilities::get_post("name", null);
	$memgrp_id = U3A_Utilities::get_post("id", 0);
	$val = U3A_Options_Values::get_option_value($category, $memgrp_id, $option_name, "", true);
	echo $val;
	wp_die();
}

add_action("wp_ajax_u3a_set_option_value", "u3a_set_option_value");

function u3a_set_option_value()
{
	$category = U3A_Utilities::get_post("category", 0);
	$name = U3A_Utilities::get_post("name", null);
	$options_id = U3A_Utilities::get_post("option", 0);
	$type = U3A_Utilities::get_post("type", 0);
	$memgrp_id = U3A_Utilities::get_post("memgrp", 0);
	$val = U3A_Utilities::get_post("value", null);
	if ($name || $options_id)
	{
		if ($options_id)
		{
			$opt = U3A_Row::load_single_object("U3A_Options", ["id" => $options_id]);
		}
		else
		{
			$opt = U3A_Row::load_single_object("U3A_Options", ["category" => $category, "name" => $name]);
		}
		if ($opt)
		{
			$optid = $opt->id;
			$type = intval($opt->option_type);
		}
		else
		{
			$opt = new U3A_Options(["category" => $category, "name" => $name, "option_type" => $type]);
			$optid = $opt->save();
		}
		if ($val)
		{
			if (U3A_Options::validate_string_value_as_type($val, $type, $memgrp_id))
			{
				$optval = U3A_Row::load_single_object("U3A_Options_Values", ["memgrp_id" => $memgrp_id, "options_id" => $optid]);
				if ($optval)
				{
					$optval->value = $val;
				}
				else
				{
					$optval = new U3A_Options_Values(["memgrp_id" => $memgrp_id, "options_id" => $optid, "value" => $val]);
				}
				$optval->save();
				$result = ["success" => 1, "message" => "option value set!"];
			}
			else
			{
				$result = ["success" => 0, "message" => "value given is not valid for option type"];
			}
		}
		else
		{
			$optval = U3A_Row::load_single_object("U3A_Options_Values", ["memgrp_id" => $memgrp_id, "options_id" => $optid]);
			if ($optval)
			{
				$optval->delete();
			}
			$result = ["success" => 1, "message" => "option value reset!"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no name specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_move_members", "u3a_move_members");

function u3a_move_members()
{
	$from = U3A_Utilities::get_post("from", 0);
	$to = U3A_Utilities::get_post("to", 0);
	$mbrsa = U3A_Utilities::get_post("members", null);
	$mbrs = $mbrsa ? explode(",", $mbrsa) : [];
	if ($from)
	{
		if ($to)
		{
			if ($mbrs)
			{
				foreach ($mbrs as $mbr)
				{
					if ($mbr)
					{
						$gm = U3A_Row::load_single_object("U3A_Group_Members", ["members_id" => $mbr, "groups_id" => $to]);
						if ($gm)
						{
							//already a member so just delete
							$gm1 = U3A_Row::load_single_object("U3A_Group_Members", ["members_id" => $mbr, "groups_id" => $from]);
							if ($gm1)
							{
								$gm1->delete();
							}
						}
						else
						{
							$gm = U3A_Row::load_single_object("U3A_Group_Members", ["members_id" => $mbr, "groups_id" => $from]);
							if ($gm)
							{
								$gm->groups_id = $to;
								$gm->status = U3A_Group_Members::MEMBER;
								$gm->save();
							}
						}
					}
				}
				$result = ["success" => 1, "message" => "members moved"];
			}
			else
			{
				$result = ["success" => 0, "message" => "no members to move"];
			}
		}
		else
		{
			$result = ["success" => 0, "message" => "no destination group"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no source group"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_is_live", "u3a_is_live");

function u3a_is_live()
{
	echo U3A_Information::u3a_is_live_server() ? "1" : "0";
	wp_die();
}

add_action("wp_ajax_u3a_looking_for_members", "u3a_looking_for_members");

function u3a_looking_for_members()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	if ($groups_id)
	{
		$lfm = U3A_Utilities::get_post("lfm", null);
		$newlfm = 1 - $lfm;
		if ($lfm !== null)
		{
			$group = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
			if ($group)
			{
				$group->looking_for_members = $newlfm;
				$group->save();
				$msg = "group " . $group->name . " is now" . ($newlfm ? "" : " no longer") . " accepting for new members";
				$result = ["success" => 1, "message" => $msg];
			}
			else
			{
				$result = ["success" => 0, "message" => "no group found with id $groups_id"];
			}
		}
		else
		{
			$result = ["success" => 0, "message" => "no lfm value provided"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no group provided"];
	}
}

add_action("wp_ajax_u3a_virtual_meetings", "u3a_virtual_meetings");

function u3a_virtual_meetings()
{
	$groups_id = U3A_Utilities::get_post("group", 0);
	if ($groups_id)
	{
		$vm = U3A_Utilities::get_post("vm", null);
		$newvm = 1 - $vm;
		if ($vm !== null)
		{
			$group = U3A_Row::load_single_object("U3A_Groups", ["id" => $groups_id]);
			if ($group)
			{
				$group->virtual_meetings = $newvm;
				$group->save();
				$msg = "group " . $group->name . " is now" . ($newvm ? "" : " no longer") . " holding virtual meetings";
				$result = ["success" => 1, "message" => $msg];
			}
			else
			{
				$result = ["success" => 0, "message" => "no group found with id $groups_id"];
			}
		}
		else
		{
			$result = ["success" => 0, "message" => "no vm value provided"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no group provided"];
	}
}

add_action("wp_ajax_u3a_get_card", "u3a_get_card");

function u3a_get_card()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	if ($members_id)
	{
		$mbr = U3A_Members::get_member($members_id);
		if ($mbr)
		{
			$mcard = U3A_PDF::get_membership_card($mbr);
			$result = ["success" => 1, "arg" => $mcard["url"]];
		}
		else
		{
			$result = ["success" => 0, "message" => "no member found with id $members_id"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no member provided"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_edit_venue", "u3a_edit_venue");

function u3a_edit_venue()
{
	write_log($_POST);
	$venues_id = U3A_Utilities::get_post("venue", 0);
	$op = U3A_Utilities::get_post("op", "add");
	if ($venues_id)
	{
		$venue = U3A_Row::load_single_object("U3A_Venues", ["id" => $venues_id]);
	}
	else
	{
		$venue = new U3A_Venues();
	}
	$cols = U3A_Venues::get_the_column_names("u3a_venues");
	write_log($cols);
	foreach ($cols as $col)
	{
		if ($col !== 'id')
		{
			$val = U3A_Utilities::get_post('field_' . $col, null);
			$venue->$col = stripslashes($val);
		}
	}
	write_log($venue);
	if ($venue->save())
	{
		$result = ["success" => 1, "message" => "venue ${op}ed"];
	}
	else
	{
		$result = ["success" => 0, "message" => "venue not ${op}ed"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_remove_venue", "u3a_remove_venue");

function u3a_remove_venue()
{
	$venues_id = U3A_Utilities::get_post("venue", 0);
	if ($venues_id)
	{
		$venue = U3A_Row::load_single_object("U3A_Venues", ["id" => $venues_id]);
		if ($venue)
		{
			$venue->delete();
			$result = ["success" => 1, "message" => "venue deleted"];
		}
		else
		{
			$result = ["success" => 0, "message" => "venue not deleted"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no venue specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_get_venue", "u3a_get_venue");

function u3a_get_venue()
{
	$venues_id = U3A_Utilities::get_post("venue", 0);
	if ($venues_id)
	{
		$venue = U3A_Row::load_single_object("U3A_Venues", ["id" => $venues_id]);
		if ($venue)
		{
			$ret = U3A_Venue_Utilities::get_venue_editor("edit", $venue);
			$result = ["success" => 1, "message" => U3A_HTML::to_html($ret)];
		}
		else
		{
			$result = ["success" => 0, "message" => "no such venue"];
		}
	}
	else
	{
		$result = ["success" => 0, "message" => "no venue specified"];
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_renewal_needed", "u3a_renewal_needed");

function u3a_renewal_needed()
{
	U3A_Members::set_renewal_needed();
}

add_action("wp_ajax_u3a_lapse_members", "u3a_lapse_members");

function u3a_lapse_members()
{
	$where = ["status" => "Current", "class<>" => "System", "renewal_needed" => 1];
	$mbrs = U3A_Members::get_all_members($where);
	$count = 0;
	foreach ($mbrs as $mbr)
	{
		$mbr->status = 'Lapsed';
		$mbr->save();
		$count++;
	}
	$result = ["success" => 1, "message" => "$count members lapsed"];
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_unlapse_member", "u3a_unlapse_member");

function u3a_unlapse_member()
{
	$members_id = U3A_Utilities::get_post("member", 0);
	if ($members_id)
	{
		$mbr = U3A_Members::get_member($members_id);
		if ($mbr)
		{
			if ($mbr->status === 'Current')
			{
				$result = ["success" => 0, "message" => "member $members_id  is current"];
			}
			else
			{
				$mbr->status = 'Current';
				$mbr->save();
				$result = ["success" => 1, "message" => "member $members_id unlapsed"];
			}
		}
		else
		{
			$result = ["success" => 0, "message" => "member $members_id  not found"];
		}
	}
	echo json_encode($result);
	wp_die();
}

add_action("wp_ajax_u3a_check_wpid", "u3a_check_wpid");

function u3a_check_wpid()
{
	$all_members = U3A_Members::get_all_members(null, true);
	$count = 0;
	foreach ($all_members as $mbr)
	{
		$wpid = $mbr->get_the_wpid();
		If ($wpid)
		{
			$count++;
		}
	}
	$result = ["success" => 1, "message" => "$count members registered"];
	echo json_encode($result);
	wp_die();
}
