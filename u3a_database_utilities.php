<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'U3ADatabase.php';

class U3A_Document_Utilities
{

	private static $visibilities = [
		[U3A_Documents::VISIBILITY_GROUP, "group"],
		[U3A_Documents::VISIBILITY_U3A, "u3a"],
		[U3A_Documents::VISIBILITY_PUBLIC, "public"]
	];
	private static $document_upload_button_parameters = [
		"by"		 => "author",
		"accept"	 => ".pdf,.doc,.docx,.epub,.mobi,.azw3,.xls,.xlsx,.odf,.ppt,.pptx,.pps,.ppsx,application/vnd.ms-powerpoint,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/epub+zip,application/pdf,"
		. "application/vnd.oasis.opendocument.text,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.presentationml.slideshow,application/vnd.openxmlformats-officedocument.presentationml.presentation"
	];
	private static $image_upload_button_parameters = [
		"by"		 => "photographer",
		"accept"	 => ".png,.jpg,.jpeg,image/png,image/jpeg"
	];

	private static function get_visibility_select($groups_id, $type, $op, $selected = U3A_Documents::VISIBILITY_U3A)
	{
		$options = [];
		for ($n = 0; $n < count(self::$visibilities); $n++)
		{
			$options[$n] = new U3A_OPTION(self::$visibilities[$n][1], self::$visibilities[$n][0], self::$visibilities[$n][0] === $selected);
		}
//		$options[1] = new U3A_OPTION("u3a", self::VISIBILITY_U3A, false);
//		$options[2] = new U3A_OPTION("public", self::VISIBILITY_PUBLIC, false);
		$ret = new U3A_SELECT($options, "visibility", "u3a-visibility-$op-$groups_id-$type", "u3a-visibility-select-class u3a-width-5-em");
		return $ret;
	}

	public static function get_document_management($id, $mbrgrp, $type1, $selected_category_id = 0)
	{
		$type = intval($type1);
		$category_label = "category";
		if (($type === U3A_Documents::GROUP_IMAGE_TYPE) || ($type === U3A_Documents::COMMITTEE_IMAGE_TYPE) || ($type === U3A_Documents::COORDINATORS_IMAGE_TYPE) || ($type === U3A_Documents::PERSONAL_IMAGE_TYPE))
		{
			$params = self::$image_upload_button_parameters;
			$category_label = "album";
		}
		else
		{
			$params = self::$document_upload_button_parameters;
		}
		if ($mbrgrp == U3A_Document_Categories::MEMBER_CATEGORY)
		{
			$is_group = 0;
			$groups_id = -1;
			$alldocs = U3A_Documents::get_all_documents_for_member($id, $type);
		}
		else
		{
			$is_group = 1;
			$groups_id = $id;
			$alldocs = U3A_Documents::get_all_documents_for_group($id, $type);
		}
		$type_name = U3A_Documents::get_type_name($type);
		$type_name1 = U3A_Documents::get_type_title_indefinite($type);
		$file_input_id = "upload-document-file-" . $id . "-" . $type;
		$file_input = new U3A_INPUT("file", "u3a-upload-document-file", $file_input_id);
		$file_input->add_attribute("accept", $params["accept"]);
		$file_input->add_attribute("onchange", "upload_file_changed('" . $file_input_id . "')");
// get_select_list($grp, $type = 0, $id = "", $onchange = null, $selected1 = null, $include_default = false, $include = null, $omit = null)
		$select = U3A_Document_Categories::get_select_list($id, $mbrgrp, $type, "manage-documents", "u3a_document_category_change");
		if ($selected_category_id)
		{
			if ($select)
			{
				if ($select["select"])
				{
					$select["select"]->select_by_value($selected_category_id);
				}
			}
			$select["selected"] = $selected_category_id;
		}
//		write_log($select);
		$selid = $select["id"];
		if ($selid)
		{
			$sel = U3A_HTML::labelled_html_object("$category_label: ", $select["select"], null, "u3a-input-label-class", false, true, null);
		}
		else
		{
			$sel = new U3A_INPUT("hidden", "category", "u3a-upload-category-" . $id . "-" . $type, "u3a-upload-category-class", "0");
		}
		$div = new U3A_DIV($sel, "u3a-select-list-div-$id-$type", "u3a-select-list-div-class u3a-bottom-margin-5 u3a-top-margin-5");
		$btn = new U3A_BUTTON("button", "upload", "upload-document-post-button-" . $id . "-" . $type, "u3a-upload-document-post-button-class u3a-button", "u3a_upload_document_from_form($id, '$type', $is_group)");
		$visibility = null;
		if ($type === U3A_Documents::NEWSLETTER_TYPE)
		{
			$num = U3A_Documents::get_latest_newsletter_number();
			if (!$num)
			{
				$num = 300;
			}
			$titlebit = [
				new U3A_SPAN("number:", null, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5"),
				U3A_HTML_Utilities::get_large_number_select("title1", "u3a-newsletter-number", "u3a-inline-block u3a-width-5-em", $num + 1, $num - 100, $num + 100),
				new U3A_SPAN("year:", null, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5"),
				U3A_HTML_Utilities::get_year_select("title2", "u3a-newsletter-year", "u3a-inline-block u3a-width-5-em", 8),
				new U3A_SPAN("month:", null, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5"),
				U3A_HTML_Utilities::get_month_select("title3", "u3a-newsletter-month", "u3a-inline-block u3a-width-8-em")
			];
//			$titlebit = [
//				U3A_HTML::labelled_html_object("number: ", new U3A_DIV($inps, null, "u3a-inline-block"), null, null, false, true)
//			];
		}
		else
		{
			$titlebit = [
				U3A_HTML::labelled_html_object("title: ", new U3A_INPUT("string", "title", null, "u3a-input-title-class"), null, "u3a-input-label-class", false, true, "Give a title, default is file name"),
				U3A_HTML::labelled_html_object($params["by"] . ": ", new U3A_INPUT("string", "by", null, "u3a-input-by-class"), null, "u3a-input-label-class", false, true)
			];
			if (($type === U3A_Documents::GROUP_DOCUMENT_TYPE) || ($type === U3A_Documents::GROUP_IMAGE_TYPE) || ($type === U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($type === U3A_Documents::PERSONAL_IMAGE_TYPE))
			{
				$visibility = U3A_HTML::labelled_html_object("visibility: ", self::get_visibility_select($id, $type, "add"), null, "u3a-input-label-class", false, true, null);
			}
		}
		$contents = [
			$div,
			new U3A_H(4, "Upload $type_name1"),
			$visibility,
			new U3A_DIV($file_input, null, "upload-document-file-div-class u3a-file-div-class"),
			$titlebit,
			new U3A_INPUT("hidden", "action", null, null, "u3a_upload_document"),
			new U3A_INPUT("hidden", "group", "u3a-manage-documents-group-$id-$type", null, $groups_id),
			new U3A_INPUT("hidden", "type", "u3a-manage-documents-type-$id-$type", null, $type),
//			new U3A_INPUT("hidden", "category", "u3a-upload-category-" . $action . "-" . $memgrp . "-" . $type, "u3a-upload-category-class", "0"),
			new U3A_DIV($btn, null, "u3a-upload-document-button-div-class u3a-button-div-class")
		];
		$uplf = new U3A_FORM($contents, "/wp-admin/admin-ajax.php", "POST", "upload-document-form-" . $id . "-" . $type, "u3a-upload-document-form-class");
		$uplf->add_attribute("enctype", "multipart/form-data");
		$upldocs = new U3A_DIV($uplf, null, "u3a-upload-div-class");
		$del = [];
		if ($alldocs["total"])
		{
			$op = $type === U3A_Documents::NEWSLETTER_TYPE ? "Delete" : "Move/Copy/Sort";
			foreach ($alldocs["documents"] as $catname => $catdocs)
			{
				$cat = $catdocs["category"];
				$catid = $cat ? $cat->id : 0;
				if ($catdocs["count"])
				{
					$docs = $catdocs["documents"];
					$opts = U3A_HTML_Utilities::get_options_array_from_object_array($docs, "title", "id");
// edit a document
					$opts1 = $opts;
					$editid = "u3a-copy-document-" . $id . "-" . $type . "-" . $catid;
					$editsel = new U3A_SELECT($opts1, "u3a-" . $type_name . "-select", $editid, "u3a-" . $type_name . "-select-class");
					$editsel->add_attribute("onchange", "u3a_edit_document_changed($id, '" . $type . "')");
					$editseldiv = new U3A_DIV($editsel, "u3a-edit-select-list-div-$id-$type", "u3a-select-list-div-class u3a-bottom-margin-5 u3a-top-margin-5");
					$editbtn = new U3A_BUTTON("button", "edit", "edit-document-post-button-" . $id . "-" . $type, "u3a-edit-document-post-button-class u3a-button", "u3a_edit_document($id, '$type', $is_group)");
					$titlebit1 = [
						U3A_HTML::labelled_html_object("title: ", new U3A_INPUT("string", "title", "u3a-edit-title-$id-$type", "u3a-input-title-class", $docs[0]->title), null, "u3a-input-label-class", false, true, "Give a new title"),
						U3A_HTML::labelled_html_object($params["by"] . ": ", new U3A_INPUT("string", "by", "u3a-edit-by-$id-$type", "u3a-input-by-class", $docs[0]->author), null, "u3a-input-label-class", false, true)
					];
					if (($type === U3A_Documents::GROUP_DOCUMENT_TYPE) || ($type === U3A_Documents::GROUP_IMAGE_TYPE) || ($type === U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($type === U3A_Documents::PERSONAL_IMAGE_TYPE))
					{
						$titlebit1[] = U3A_HTML::labelled_html_object("visibility: ", self::get_visibility_select($id, $type, "edit"), null, "u3a-input-label-class", false, true, null);
					}
					$edith = new U3A_H(4, "Edit " . $type_name1);
					$editdiv = new U3A_DIV([$edith, $editseldiv, $titlebit1, $editbtn], null, "u3a-edit-document-div-class");
//					$del[] = $editdiv;
// move
					$oph = new U3A_H(4, "$op " . $type_name1);
					$def = new U3A_OPTION("all", 0);
					array_unshift($opts, $def);
					$delid = "u3a-delete-document-" . $id . "-" . $type . "-" . $catid;
					$cpid = "u3a-copy-document-" . $id . "-" . $type . "-" . $catid;
					$sel = new U3A_SELECT($opts, "u3a-" . $type_name . "-select", $delid, "u3a-" . $type_name . "-select-class");
					$cpsel = new U3A_SELECT($opts, "u3a-" . $type_name . "-select", $cpid, "u3a-" . $type_name . "-select-class");
					$sel1 = U3A_Document_Categories::get_select_list($id, $id, $type, "select-category-move-$catid", null, -1, true, "trash", $catid);
					$cpsel1 = U3A_Document_Categories::get_select_list($id, $id, $type, "select-category-copy-$catid", null, -1, true, null, $catid);
					if ($type === U3A_Documents::NEWSLETTER_TYPE)
					{
						$lbl = [
							new U3A_SPAN("select " . $type_name . " to delete: ", null, "u3a-block u3a-margin-right-5"),
							$sel,
							new U3A_BUTTON("button", "delete", "u3a-" . $type_name . "-delete-button", "u3a-select-button-class u3a-button u3a-margin-left-5", "u3a_move_document('$delid', '$type_name', '" . $sel1["id"] . "', '$catid', '$id', $is_group)")
						];
					}
					else
					{
						$mv = [
							new U3A_SPAN("select " . $type_name . " to move: ", null, "u3a-block u3a-margin-right-5"),
							$sel,
							new U3A_SPAN("to", null, "u3a-inline-block u3a-margin-right-5 u3a-margin-left-5"),
							$sel1["select"],
							new U3A_BUTTON("button", "move", "u3a-" . $type_name . "-move-button", "u3a-select-button-class u3a-button u3a-margin-left-5", "u3a_move_document('$delid', '$type_name', '" . $sel1["id"] . "', '$catid', '$id', $is_group)")
						];
						$mvdiv = new U3A_DIV($mv, "u3a-move-document-div-" . $id . "-" . $type . "-" . $catid, "u3a-move-document-div-class-$type");
						$cp = [
							new U3A_SPAN("select " . $type_name . " to copy: ", null, "u3a-block u3a-margin-right-5"),
							$cpsel,
							new U3A_SPAN("to", null, "u3a-inline-block u3a-margin-right-5 u3a-margin-left-5"),
							$cpsel1["select"],
							new U3A_BUTTON("button", "copy", "u3a-" . $type_name . "-copy-button", "u3a-select-button-class u3a-button u3a-margin-left-5", "u3a_copy_document('$cpid', '$type_name', '" . $cpsel1["id"] . "', '$catid', '$id', $is_group)")
						];
						$cpdiv = new U3A_DIV($cp, "u3a-copy-document-div-" . $id . "-" . $type . "-" . $catid, "u3a-copy-document-div-class-$type");
						$sortlist = U3A_HTML_Utilities::get_list_from_object_array($docs, "title", "id", false, "u3a-sort-list-$id-$type-$catid", "u3a-sort-list", "u3a-sort-list-item");
						$instruct = new U3A_DIV("use mouse to move up and down", "u3a-instruction-$id-$type-$catid", "u3a-border-top u3a-margin-top-5");
						$cls = '<span class="dashicons dashicons-yes-alt"></span>';
						$close = new U3A_A('#', $cls, "u3a-close-sort-list-$id-$type-$catid", null, "u3a_sort_list_close('$id', '$type', '$catid', $is_group);");
						$close->add_attribute("rel", "modal:close");
						$sortdiv = new U3A_DIV([$sortlist, $instruct, $close], "u3a-sort-list-div-$id-$type-$catid", "modal u3a-sort-list-div");
						$open = new U3A_A("#u3a-sort-list-div-$id-$type-$catid", 'sort', null, "u3a-button u3a-block");
						$open->add_attribute("role", "button");
						$open->add_attribute("rel", "modal:open");
						$lbl = [$editdiv, $oph, $mvdiv, $cpdiv, $open, $sortdiv];
					}
				}
				else
				{
					$lbl = new U3A_SPAN("There are no " . $type_name . "s in this $category_label.", null, "u3a-inline-block");
				}
				$div = new U3A_DIV($lbl, "u3a-manage-document-div-" . $id . "-" . $type . "-" . $catid, "u3a-manage-document-div-class-$type u3a-border-top");
				if ($catid && $catid != $select["selected"])
				{
					$div->add_class("u3a-invisible");
				}
				$del[] = $div;
			}
		}
		else
		{
			$del[] = new U3A_DIV("No " . $type_name . "s found", "u3a-manage-documents-div-" . $id . "-" . $type, "u3a-manage-document-div-class-$type u3a-border-top");
		}
		return [$upldocs, $del];
	}

	public static function get_document_table($documents, $type)
	{
		if ($type === U3A_Documents::NEWSLETTER_TYPE)
		{
			$headers = [
				new U3A_TH("Title"),
				new U3A_TH("Uploaded"),
				new U3A_TH("")
			];
		}
		else
		{
			$headers = [
				new U3A_TH("Title"),
				new U3A_TH("Author"),
				new U3A_TH("Uploaded"),
				new U3A_TH("")
			];
		}
		$rows = [];
		$thead = new U3A_THEAD(new U3A_TR($headers));
		foreach ($documents as $doc)
		{
			$a = new U3A_A(wp_get_attachment_url($doc->attachment_id), "download");
			$a->add_attribute("data-popup", "true");
			$type = intval($doc->document_type);
			if ($type === U3A_Documents::NEWSLETTER_TYPE)
			{
				$td = [
					new U3A_TD($doc->get_title()),
					new U3A_TD(date("d/m/Y", strtotime($doc->added))),
					new U3A_TD($a)
				];
			}
			else
			{
				$td = [
					new U3A_TD($doc->get_title()),
					new U3A_TD($doc->author),
					new U3A_TD(date("d/m/Y", strtotime($doc->added))),
					new U3A_TD($a)
				];
			}
			$rows[] = new U3A_TR($td);
		}
		$tbl = new U3A_TABLE([$thead, new U3A_TBODY($rows)]);
		return '[su_table responsive="yes" alternate="yes"]' . U3A_HTML::to_html($tbl) . "[/su_table]";
	}

	public static function get_document_lists($grp = null, $type = 0)
	{
		$alldocs = U3A_Documents::get_all_documents_for_group($grp, $type);
		$ret = null;
		if ($alldocs["total"])
		{
			$alldocuments = $alldocs["documents"];
			$cats = [];
			$docs = [];
			$first = true;
			$m = 0;
			foreach ($alldocuments as $catname => $documents)
			{
				if ($documents["count"] > 0)
				{
					if ($first)
					{
						$sel = " u3a-category-selected";
						$vis = " u3a-inline-block";
						$first = false;
					}
					else
					{
						$sel = "";
						$vis = " u3a-invisible";
					}
					$div = new U3A_BUTTON("button", $catname, "u3a-category-name-$grp-$type-$m", "u3a-block u3a-category-name-class$sel", "u3a_category_name_clicked($grp, $type, $m)");
					$cats[] = $div;
					$catdocs = [];
					$n = 0;
					foreach ($documents["documents"] as $doc)
					{
						$cb = new U3A_INPUT("checkbox", null, "u3a-document-checkbox-$grp-$type-$m-$n", "u3a-document-checkbox-class", $doc->attachment_id);
						$lbl = new U3A_LABEL("u3a-document-checkbox-$grp-$type-$m-$n", $doc->get_title(), "u3a-document-checkbox-label-$grp-$type-$m-$n", "u3a-inline-block u3a-margin-left-5");
						$catdocs[] = new U3A_DIV([$cb, $lbl], null, "u3a-document-name-class");
						$n++;
					}
					$docs[] = new U3A_DIV($catdocs, "u3a-category-documents-$grp-$type-$m", "u3a-va-top u3a-category-documents-class$vis");
					$m++;
				}
			}
			$catlist = new U3A_DIV($cats, "u3a-category-list-$grp-$type", "u3a-inline-block u3a-va-top");
			$div1 = new U3A_DIV([$catlist, $docs], "u3a-document-select-lists-div-$grp-$type", "u3a-document-select-lists-div-class");
			$okbtn = new U3A_BUTTON("button", "OK", "u3a-document-select-button-$grp-$type", "u3a-button", "u3a_document_selected($grp, $type)");
			$okdiv = new U3A_DIV($okbtn, "u3a-document-select-button-div-$grp-$type", "u3a-document-select-button-class");
			$ret = new U3A_DIV([$div1, $okdiv], "u3a-document-select-group-$grp-$type", "u3a-document-select-class u3a-invisible");
		}
		return $ret;
	}

	public static function get_category_list_item($cat, $list_idprefix, $list_cssclass, $item_cssclass, $text_cssclass, $strict = false)
	{
		$contents = null;
		if ($strict)
		{
			$contents = [new U3A_SPAN($cat->name, null, $text_cssclass), new U3A_INPUT("hidden", null, null, $item_cssclass . "-id", $cat->id)];
		}
		else
		{
			$subcats = U3A_Category_Category_Relationship::get_children_of($cat->id);
			write_log("subcats of " . $cat->name, $subcats);
			if ($subcats)
			{
				$strict1 = false;
				if (U3A_Document_Category_Relationship::has_documents($cat->id))
				{
					array_unshift($subcats, $cat);
					$strict1 = true;
				}
				$contents = get_category_list($subcats, $strict1, $list_idprefix . $cat->id . "-", $list_cssclass . "-sub", $item_cssclass, $text_cssclass);
			}
			else
			{
				$contents = [new U3A_SPAN($cat->name, null, $text_cssclass), new U3A_INPUT("hidden", null, null, $item_cssclass . "-id", $cat->id)];
			}
		}
		return new U3A_LI($contents, null, $item_cssclass);
	}

	public static function get_category_list($cats, $strict1, $list_idprefix, $list_cssclass, $item_cssclass, $text_cssclass)
	{
		$li = [];
		if ($cats)
		{
			$li[0] = self::get_category_list_item($cats[0], $list_idprefix, $list_cssclass, $item_cssclass, $text_cssclass, $strict1);
			if (count($cats) > 1)
			{
				for ($n = 1; $n < count($cats); $n++)
				{
					$li[$n] = self::get_category_list_item($cats[$n], $list_idprefix, $list_cssclass, $item_cssclass, $text_cssclass, false);
				}
			}
		}
		write_log("items", $li);
		return new U3A_LIST($li, false, $list_idprefix . "list", $list_cssclass);
	}

	public static function get_category_list_group($groups_id, $type, $list_idprefix = "u3a-category-list-", $list_cssclass = "u3a-category-list", $item_cssclass = "u3a-category-listitem", $text_cssclass = "u3a-category-list-text")
	{
		$cats = U3A_Document_Categories::get_categories_for_group($groups_id, $type, false);
		$list = self::get_category_list($cats, false, $list_idprefix, $list_cssclass, $item_cssclass, $text_cssclass);
		$id = new U3A_INPUT("hidden", "listid", $list_idprefix . "id-value-$groups_id-$type", "u3a-category-list-id-input", $groups_id);
		$typ = new U3A_INPUT("hidden", "listtype", $list_idprefix . "type-value-$groups_id-$type", "u3a-category-list-type-input", $type);
		return [$id, $typ, $list];
	}

	public static function get_category_list_member($members_id, $type, $list_idprefix = "u3a-category-list-", $list_cssclass = "u3a-category-list", $item_cssclass = "u3a-category-listitem", $text_cssclass = "u3a-category-list-text")
	{
		$cats = U3A_Document_Categories::get_categories_for_member($members_id, $type, false);
		write_log("cats", $cats);
		$list = self::get_category_list($cats, false, $list_idprefix, $list_cssclass, $item_cssclass, $text_cssclass);
		$id = new U3A_INPUT("hidden", "listid", $list_idprefix . "id-value-$members_id-$type", "u3a-category-list-id-input", $members_id);
		$typ = new U3A_INPUT("hidden", "listtype", $list_idprefix . "type-value-$members_id-$type", "u3a-category-list-type-input", $type);
		return [$id, $typ, $list];
	}

}

class U3A_Email_Utilities
{

	public static function get_group_mailing_sublist_select($groups_id)
	{
		$ret = null;
		$lists = U3A_Email_Lists::get_lists_for_group($groups_id);
		if ($lists)
		{
			$opts = [new U3A_OPTION("", 0, true)];
			foreach ($lists as $list)
			{
				$opts[] = new U3A_OPTION($list->name, $list->id);
			}
			$ret = new U3A_SELECT($opts, "sublist_select", "u3a-sublist-select-$groups_id", "u3a-sublist-select u3a-width-12-em");
			$ret->add_attribute("onchange", "u3a_sublist_select_changed($groups_id)");
		}
		return $ret;
	}

}

class U3A_Link_Utilities
{

	public static function get_section_div($sec)
	{
		$ret = null;
		$section = U3A_Link_Sections::get_section($sec);
		if ($section)
		{
			$h = new U3A_H(6, $section->name);
			$links = $section->get_links();
			$divs = [];
			if ($links)
			{
				foreach ($links as $link)
				{
					$a = new U3A_A($link->url, $link->description, "u3a-link-a-" . $link->id, "u3a-link-link");
					$a->add_attribute("data-popup", "true");
					$divs[] = new U3A_DIV($a, "u3a-link-div-" . $link->id, "u3a-link-div u3a-margin-left-10");
				}
			}
			$ret = new U3A_DIV([$h, $divs], "u3a-section-div-" . $section->id, "u3a-section-div");
		}
		return $ret;
	}

	public static function get_section_divs($groups_id, $members_id)
	{
		$ret = [];
		$sections = U3A_Link_Sections::get_sections($groups_id, $members_id);
		foreach ($sections as $section)
		{
			$ret[] = self::get_section_div($section);
		}
		return $ret;
	}

	public static function get_section_select($groups_id, $members_id)
	{
		$sections = U3A_Link_Sections::get_sections($groups_id, $members_id);
		$opts = [new U3A_OPTION("", 0, true)];
		foreach ($sections as $section)
		{
			$opts[] = new U3A_OPTION($section->name, $section->id);
		}
		$ret = new U3A_SELECT($opts, "link_section_select", "u3a-link-section-select-$groups_id-$members_id", "u3a-link-section-select u3a-width-30-em");
		$ret->add_attribute("onchange", "u3a_link_section_select_changed($groups_id, $members_id)");
		return $ret;
	}

}
