<?php

require_once('u3a_db_object.php');
require_once('project.php');
require_once('u3a_utility_classes.php');
require_once('u3a_mail.php');

interface U3A_Table_Information
{

	public function get_tablename_from_classname($classname);

	public function get_classname_from_tablename($tablename);

	public function get_where_array($tablename, $key);

	public function get_select_array($tablename, $key);

	public function get_number_array($tablename, $key);

	public function get_user_object($tablename, $key);

	public function get_user_post($tablename, $post);

	public function get_description($tablename, $key);

	public function get_table_equivalent($tablename, $columnname);
}

class U3A_HTML_Utilities
{

	public static $honorifics = [
		"Mr",
		"Mrs",
		"Miss",
		"Ms",
		"Mx",
		"Dr",
		"Professor",
		"Sister",
		"Reverend",
		"The Rt Revd Dr",
		"The Most Revd",
		"The Rt Revd",
		"The Revd Canon",
		"The Revd",
		"The Rt Revd Professor",
		"The Ven",
		"The Most Revd Dr",
		"Very Revd",
		"Lord",
		"Lady",
		"Major",
		"Captain",
		"Colonel",
		"General",
		"Field Marshal",
		"Rabbi",
		"Canon",
		"Dame",
		"Chief",
		"Cllr",
		"Sir",
		"Rt Hon Lord",
		"Rt Hon",
		"Viscount",
		"Viscountess",
		"Baroness",
		"Other"
	];
	public static $counties = ["England"			 => [
			"Bedfordshire",
			"Berkshire",
			"Bristol",
			"Buckinghamshire",
			"Cambridgeshire",
			"Cheshire",
			"City of London",
			"Cornwall",
			"Cumbria",
			"Derbyshire",
			"Devon",
			"Dorset",
			"Durham",
			"East Riding of Yorkshire",
			"East Sussex",
			"Essex",
			"Gloucestershire",
			"Greater London",
			"Greater Manchester",
			"Hampshire",
			"Herefordshire",
			"Hertfordshire",
			"Isle of Wight",
			"Kent",
			"Lancashire",
			"Leicestershire",
			"Lincolnshire",
			"Merseyside",
			"Norfolk",
			"North Yorkshire",
			"Northamptonshire",
			"Northumberland",
			"Nottinghamshire",
			"Oxfordshire",
			"Powys",
			"Rutland",
			"Shropshire",
			"Somerset",
			"South Yorkshire",
			"Staffordshire",
			"Suffolk",
			"Surrey",
			"Tyne and Wear",
			"Warwickshire",
			"West Midlands",
			"West Sussex",
			"West Yorkshire",
			"Wiltshire",
			"Worcestershire"
		],
		"Wales"				 => [
			"Anglesey",
			"Brecknockshire",
			"Caernarfonshire",
			"Carmarthenshire",
			"Cardiganshire",
			"Denbighshire",
			"Flintshire",
			"Glamorgan",
			"Merioneth",
			"Monmouthshire",
			"Montgomeryshire",
			"Pembrokeshire",
			"Radnorshire"
		],
		"Scotland"			 => [
			"Aberdeenshire",
			"Angus",
			"Argyllshire",
			"Ayrshire",
			"Banffshire",
			"Berwickshire",
			"Buteshire",
			"Cromartyshire",
			"Caithness",
			"Clackmannanshire",
			"Dumfriesshire",
			"Dunbartonshire",
			"East Lothian",
			"Fife",
			"Inverness-shire",
			"Kincardineshire",
			"Kinross",
			"Kirkcudbrightshire",
			"Lanarkshire",
			"Midlothian",
			"Morayshire",
			"Nairnshire",
			"Orkney",
			"Peeblesshire",
			"Perthshire",
			"Renfrewshire",
			"Ross-shire",
			"Roxburghshire",
			"Selkirkshire",
			"Shetland",
			"Stirlingshire",
			"Sutherland",
			"West Lothian",
			"Wigtownshire"
		],
		"Northern Ireland" => [
			"Antrim",
			"Armagh",
			"Down",
			"Fermanagh",
			"Londonderry",
			"Tyrone"
	]];
	public static $days_of_week = [
		"monday",
		"tuesday",
		"wednesday",
		"thursday",
		"friday",
		"saturday",
		"sunday"
	];
	public static $months = [
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December"
	];
	public static $alphabet_upper = [
		'A',
		'B',
		'C',
		'D',
		'E',
		'F',
		'G',
		'H',
		'I',
		'J',
		'K',
		'L',
		'M',
		'N',
		'O',
		'P',
		'Q',
		'R',
		'S',
		'T',
		'U',
		'V',
		'W',
		'X',
		'Y',
		'Z'
	];
	public static $alphabet_lower = [
		'a',
		'b',
		'c',
		'd',
		'e',
		'f',
		'g',
		'h',
		'i',
		'j',
		'k',
		'l',
		'm',
		'n',
		'o',
		'p',
		'q',
		'r',
		's',
		't',
		'u',
		'v',
		'w',
		'x',
		'y',
		'z'
	];

	public static function get_county_select($id = null, $cssclass = null, $selected = "Shropshire")
	{
		$optgroups = [];
		foreach (self::$counties as $label => $county_array)
		{
			$options = [];
			foreach ($county_array as $county)
			{
				$options[] = new U3A_OPTION($county, $county, $selected === $county);
			}
			$optgroups[] = new U3A_OPTGROUP($options, $label);
		}
		return new U3A_SELECT($optgroups, "county-select", $id, $cssclass);
	}

	public static function get_gender_select($id = null, $cssclass = null, $selected = "N")
	{
		$sel = strtoupper($selected);
		$options = [
			new U3A_OPTION("prefer not to say", "N", $sel === "N"),
			new U3A_OPTION("female", "F", $sel === "F"),
			new U3A_OPTION("male", "M", $sel === "M")
		];
		return new U3A_SELECT($options, "gender-select", $id, $cssclass);
	}

	public static function get_payment_type_select($id = null, $cssclass = null, $selected = "Cheque")
	{
		$options = [
			new U3A_OPTION("Cheque", "Cheque", $selected === "Cheque"),
			new U3A_OPTION("Cash", "Cash", $selected === "Cash"),
			new U3A_OPTION("PayPal", "PayPal", $selected === "PayPal"),
			new U3A_OPTION("CreditCard", "CreditCard", $selected === "CreditCard")
		];
		$ret = new U3A_SELECT($options, "payment-type-select", $id, $cssclass);
		return $ret;
	}

	public static function get_honorific_select($id = null, $cssclass = null, $selected = "Mr")
	{
		$ret = [];
		for ($n = 0; $n < count(self::$honorifics); $n++)
		{
			$h = self::$honorifics[$n];
			$ret[] = new U3A_OPTION($h, $h, $selected == $h);
		}
		return new U3A_SELECT($ret, "title-select", $id, $cssclass);
	}

	public static function get_number_select($name, $id, $cssclass, $selected = 1, $min = 1, $max = 5)
	{
		$options = [];
		for ($n = $min; $n <= $max; $n++)
		{
			$str = U3A_Utilities::number_to_string($n);
			$options[] = new U3A_OPTION($str, $n, $selected == $n);
		}
		return new U3A_SELECT($options, $name, $id, $cssclass);
	}

	public static function get_large_number_select($name, $id, $cssclass, $selected = 1, $min = 1, $max = 5)
	{
		$options = [];
		for ($n = $min; $n <= $max; $n++)
		{
			$str = "$n";
			$options[] = new U3A_OPTION($str, $n, $selected == $n);
		}
		return new U3A_SELECT($options, $name, $id, $cssclass);
	}

	public static function get_ordinal_select($name, $id, $cssclass, $selected = 1, $min = 1, $max = 5)
	{
		$options = [];
		for ($n = $min; $n <= $max; $n++)
		{
			$str = U3A_Utilities::ordinal_to_string($n);
			$options[] = new U3A_OPTION($str, $n, $selected == $n);
		}
		return new U3A_SELECT($options, $name, $id, $cssclass);
	}

	public static function get_day_of_week_select($name, $id, $cssclass, $selected = "monday", $short = false)
	{
		$options = [];
		foreach (self::$days_of_week as $dw)
		{
			$txt = $short ? substr($dw, 0, 3) : $dw;
			$options[] = new U3A_OPTION($txt, $dw, $selected == $dw);
		}
		return new U3A_SELECT($options, $name, $id, $cssclass);
	}

	public static function get_month_select($name, $id, $cssclass, $selected = null)
	{
		$sel = "January";
		if ($selected)
		{
			$sel = ucfirst(strtolower($selected));
		}
		else
		{
			$sel = date("F");
		}
		$options = [];
		foreach (self::$months as $m)
		{
			$options[] = new U3A_OPTION($m, $m, $sel == $m);
		}
		return new U3A_SELECT($options, $name, $id, $cssclass);
	}

	public static function get_year_select($name, $id, $cssclass, $from = 4, $to = 3, $selected = 0)
	{
		$sel = 2019;
		if ($selected)
		{
			$sel = intval($selected);
		}
		else
		{
			$sel = intval(date("Y"));
		}
		$options = [];
		for ($y = $sel - $from; $y <= $sel + $to; $y++)
		{
			$str = "$y";
			$options[] = new U3A_OPTION($str, $y, $sel == $y);
		}
		return new U3A_SELECT($options, $name, $id, $cssclass);
	}

	public static function get_select_list_from_array($array, $name, $selected_value = null, $id = null, $cssclass = null)
	{
		$opts = array();
		if (array_key_exists(0, $array))
		{
			foreach ($array as $k)
			{
				$opt = new U3A_OPTION($k, $k);
				if (($selected_value != null) && ($selected_value == $k))
				{
					$opt->add_attribute('selected', 'selected');
				}
				$opts[] = $opt;
			}
		}
		else
		{
			foreach ($array as $k => $v)
			{
				$opt = new U3A_OPTION($k, $v);
				if (($selected_value != null) && ($selected_value == $v))
				{
					$opt->add_attribute('selected', 'selected');
				}
				$opts[] = $opt;
			}
		}
		return new U3A_SELECT($opts, $name, $id, $cssclass);
	}

	public static function get_select_list_from_hash_array($hash_array, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
	{
		$opts = array();
		foreach ($hash_array as $hash)
		{
			$opt = new U3A_OPTION($hash[$text_key], $hash[$value_key]);
			if (($selected_value != null) && ($selected_value == $hash[$value_key]))
			{
				$opt->add_attribute('selected', 'selected');
			}
			if ($tooltip_key != null)
			{
				$opt->add_tooltip($hash[$tooltip_key]);
			}
			$opts[] = $opt;
		}
		return new U3A_SELECT($opts, $name, $id, $cssclass);
	}

	private static function get_key_value($obj, $objhash, $key)
	{
		$ret = "";
		if (array_key_exists($key, $objhash))
		{
			$ret = $objhash[$key];
		}
		else
		{
			$method_name = "get_" . $key;
			if (method_exists($obj, $method_name))
			{
				$ret = $obj->$method_name();
			}
		}
		return $ret;
	}

	public static function get_option_from_object($obj, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $usetext = null)
	{
		$hash = $obj->get_as_hash();
		$val = stripslashes($hash[$value_key]);
		$txt = $usetext ? $usetext : self::get_key_value($obj, $hash, $text_key);
//			if (array_key_exists($text_key, $hash))
//			{
//				$txt =//				write_log($text_key);
////				write_log($hash);
//			}
		$opt = new U3A_OPTION($txt, $val, $val == $selected_value);
		if ($tooltip_key != null)
		{
			$opt->add_tooltip($hash[$tooltip_key]);
		}
		return $opt;
	}

	public static function get_options_array_from_object_array($object_array, $text_key, $value_key, $selected_value = null, $tooltip_key = null)
	{
		$opts = [];
		if (U3A_Utilities::has_string_keys($object_array))
		{
			foreach ($object_array as $label => $objs)
			{
				if (is_array($objs))
				{
					$opts1 = [];
					foreach ($objs as $obj)
					{
						$opts1[] = self::get_option_from_object($obj, $text_key, $value_key, $selected_value, $tooltip_key);
					}
					$opts[] = new U3A_OPTGROUP($opts1, $label);
				}
				else
				{
					$opts[] = self::get_option_from_object($objs, $text_key, $value_key, $selected_value, $tooltip_key, $label);
				}
			}
		}
		else
		{
			foreach ($object_array as $obj)
			{
				$opts[] = self::get_option_from_object($obj, $text_key, $value_key, $selected_value, $tooltip_key);
			}
		}
		return $opts;
	}

	public static function get_list_from_object_array($object_array, $text_key, $value_key = null, $ordered = false, $id = null, $cssclass = null, $itemcssclass = null)
	{
		$li = [];
		$n = 0;
		foreach ($object_array as $obj)
		{
			$span = new U3A_SPAN($obj->$text_key, $id ? "$id-span-$n" : null, $itemcssclass ? "$itemcssclass-span" : null);
			if ($value_key)
			{
				$inp = new U3A_INPUT("hidden", null, $id ? "$id-value-$n" : null, $itemcssclass ? "$itemcssclass-value" : null, $obj->$value_key);
			}
			else
			{
				$inp = null;
			}
			$li[] = new U3A_LI([$span, $inp], $id ? "$id-li-$n" : null, $itemcssclass ? "$itemcssclass-li" : null);
		}
		return new U3A_LIST($li, $ordered, $id, $cssclass);
	}

	public static function get_select_list_from_object_array($object_array, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
	{
		$opts = self::get_options_array_from_object_array($object_array, $text_key, $value_key, $selected_value, $tooltip_key);
		return new U3A_SELECT($opts, $name, $id, $cssclass);
	}

	public static function get_select_list_of_all($classname, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
	{
		$object_array = U3A_Row::load_array_of_objects($classname);
		return self::get_select_list_from_object_array($object_array['result'], $name, $text_key, $value_key, $selected_value, $tooltip_key, $id, $cssclass);
	}

	public static function get_select_list_of_some($classname, $where, $name, $text_key, $value_key, $selected_value = null, $tooltip_key = null, $id = null, $cssclass = null)
	{
		$object_array = U3A_Row::load_array_of_objects($classname, $where);
//        U3A_Utilities::var_dump_pre($object_array);
		return self::get_select_list_from_object_array($object_array['result'], $name, $text_key, $value_key, $selected_value, $tooltip_key, $id, $cssclass);
	}

	public static function get_define_row_form($tablename, $classname, $action = null, $instance = null, $omit_columns = null)
	{
		$fid = U3A_Form_Input_Detail::get_form_details_for_table($tablename, $classname, $omit_columns);
//        U3A_Utilities::var_dump_pre($fid);echo $fid->tablename."<br/>";
		$ul = strpos($tablename, '_');
		if ($action == null)
		{
			if ($ul === FALSE)
			{
				$act = $tablename . "_define_form_action";
			}
			else
			{
				$act = substr($tablename, 0, $ul) . "_define_form_action";
			}
		}
		else
		{
			$act = $action;
		}
		$tname = str_replace('_', '-', $tablename);
		$id = "oj-" . $tname . "-define-form";
		$cssclass = "oj-" . $tname . "-form oj-form";
//        U3A_Utilities::var_dump_pre($fid);exit;
		return U3A_Form_Input_Detail::get_form($fid, $act, "POST", $id, $cssclass, $instance);
	}

	public static function u3a_get_document_section($doctypename, $catname, $groups_id, $doctype, $mbrgrp)
	{
		$namelc = strtolower($doctypename);
		$nameUc1 = ucwords($namelc);
		$catlc = strtolower($catname);
		$catuc1 = ucwords($catname);
		$h0 = new U3A_DIV("enter new $catlc name then press 'create'", "u3a-category-create-header-" . $groups_id . "-" . $doctype, "u3a-margin-bottom-2");
		$h1 = null;
		$h2 = null;
		$span0 = new U3A_SPAN("New $nameUc1 $catuc1 ", null, "u3a-document-category-span-class");
		$txt0 = new U3A_INPUT("text", "document-category-name", "u3a-category-name-" . $groups_id . "-" . $doctype, "u3a-document-name-class u3a-name-input-class u3a-category-name-class");
		$btn0 = new U3A_BUTTON("button", "create", "u3a-category-button-" . $groups_id . "-" . $doctype, "u3a-document-button-class u3a-button", "u3a_create_new_category('" . $groups_id . "', '" . $doctype . "')");
		$div0 = new U3A_DIV([$span0, $txt0, $btn0], "u3a-category-div-" . $groups_id . "-" . $doctype, "u3a-category-div");
		$sel1 = U3A_Document_Categories::get_select_list($groups_id, $mbrgrp, $doctype, "rename-$catlc", null, null, false, null);
		if ($sel1["select"])
		{
			$h1 = new U3A_DIV("select $catlc, enter new $catlc name then press 'rename'", "u3a-category-rename-header-" . $groups_id . "-" . $doctype, "u3a-margin-bottom-2");
			$span1a = new U3A_SPAN("Rename $nameUc1 $catuc1 ");
			$span1b = new U3A_SPAN(" to ");
			$lbl1a = new U3A_LABEL($sel1["id"], $span1a, null, "u3a-document-category-span-class");
			$txt1 = new U3A_INPUT("text", "document-category-rename", "u3a-category-rename-" . $groups_id . "-" . $doctype, "u3a-document-name-class u3a-name-input-class");
			$lbl1b = new U3A_LABEL("u3a-category-rename-" . $groups_id . "-" . $doctype, $span1b, null, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5");
			$btn1 = new U3A_BUTTON("button", "rename", "u3a-category-button-" . $groups_id . "-" . $doctype, "u3a-document-button-class u3a-button", "u3a_rename_category('" . $groups_id . "', '" . $doctype . "', '" . $sel1["id"] . "')");
			$div1 = new U3A_DIV([$lbl1a, $sel1["select"], $lbl1b, $txt1, $btn1], "u3a-rename-category-div-" . $groups_id . "-" . $doctype, "u3a-category-div");
			$sel2 = U3A_Document_Categories::get_empty_select_list($groups_id, $mbrgrp, $doctype, "delete-empty-$catlc", null, null, false, null);
			if ($sel2["select"])
			{
				$h2 = new U3A_DIV("select empty $catlc to delete then press 'delete'", "u3a-category-delete-header-" . $groups_id . "-" . $doctype, "u3a-margin-bottom-2");
				$span2 = new U3A_SPAN("Delete $nameUc1 $catuc1 ");
				$lbl2 = new U3A_LABEL($sel2["id"], $span2, null, "u3a-document-category-span-class");
				$btn2 = new U3A_BUTTON("button", "delete", "u3a-category-delete-button-" . $groups_id . "-" . $doctype, "u3a-document-button-class u3a-button u3a-margin-left-5", "u3a_delete_category('" . $groups_id . "', '" . $doctype . "', '" . $sel2["id"] . "')");
				$div2 = new U3A_DIV([$lbl2, $sel2["select"], $btn2], "u3a-delete-category-div-" . $groups_id . "-" . $doctype, "u3a-category-div u3a-border-bottom u3a-margin-bottom-5");
			}
			else
			{
				$div2 = null;
				$div1->add_class("u3a-border-bottom u3a-margin-bottom-5");
			}
		}
		else
		{
			$div1 = null;
			$div2 = null;
			$div0->add_class("u3a-border-bottom u3a-margin-bottom-5");
		}
		return U3A_HTML::to_html([$h0, $div0, $h1, $div1, $h2, $div2]);
	}

	public static function get_the_news($public, $mbr)
	{
		$ret = "";
		$news = U3A_News::get_current_news($public);
//		write_log($news);
		if ($news)
		{
			$newscontents = [
				new U3A_DIV(new U3A_B("News"), "u3a-news-header")
			];
			foreach ($news as $n)
			{
				$item = [];
				if ($n->title)
				{
					$inp = new U3A_INPUT("text", "news-title", "news-title-" . $n->id, "u3a-news-title u3a-inline-block u3a-margin-bottom-5", $n->title);
					$inp->add_attribute("readonly", "readonly");
					$item[] = $inp;
//				$item[] = new U3A_H(6, $n->title . " (" . date("d M Y", strtotime($n->created)) . ")");
				}
				$txtarea = new U3A_TEXTAREA("news-item", "news-item-" . $n->id, "u3a-news-item-div", $n->item);
				$txtarea->add_attribute("readonly", "readonly");
				$item[] = $txtarea;
				if ($mbr && (($n->members_id == $mbr->id) || U3A_Information::u3a_has_permission($mbr, "edit news")))
				{
					$editbtn = new U3A_BUTTON("button", "Edit", "news-edit-" . $n->id, "u3a-button u3a-edit-news-button", "edit_news(" . $n->id . ")");
					$editdiv = new U3A_DIV($editbtn, "news-edit-div-" . $n->id, "news-edit-div");
					$item[] = $editdiv;
				}
				$newscontents[] = new U3A_DIV($item, null, "u3a-news-div u3a-border-bottom u3a-padding-bottom-5 u3a-margin-bottom-5");
			}
			$ret .= U3A_HTML::to_html($newscontents);
		}
		return $ret;
	}

	public static function get_mail_document_select_lists($mailtype, $grp, $type, $n)
	{
		$tname = U3A_Documents::get_type_name($type);
		$alldocs = U3A_Documents::get_all_documents_for_group($grp, $type);
		$ret = null;
		if ($alldocs["total"])
		{
			$alldocuments = $alldocs["documents"];
			$li0 = new U3A_LI("attach $tname", null, null);
//			$optgroups = [new U3A_OPTION("attach $tname", 0, false)];
			$optgroups = [];
			foreach ($alldocuments as $catname => $documents)
			{
				if ($documents["count"] > 0)
				{
					$options = [];
					foreach ($documents["documents"] as $doc)
					{
//						$options[] = new U3A_OPTION(new U3A_BUTTON("button", $doc->get_title(), null, null, "attach_document1('" . $doc->get_title() . "', '" . $doc->attachment_id . "')"), $doc->attachment_id, false);
						$options[] = new U3A_LI(new U3A_BUTTON("button", $doc->get_title(), null, "u3a-mail-document-select-button-class", "attach_document1('" . $mailtype . "', '" . $grp . "', '" . $n . "', '" . $doc->get_title() . "', '" . $doc->attachment_id . "')"), null, null);
					}
					$optgroups[] = new U3A_LI([
						$catname,
						new U3A_LIST($options, false, null, null)
					  ], null, null);
//					$optgroups[] = new U3A_OPTGROUP($options, $catname);
				}
			}
//			$ret = new U3A_SELECT($optgroups, "$tname-$n", "u3a-mail-$tname-$mailtype-$grp-$n", "u3a-file-input-label-class");
			$ret = new U3A_LIST($optgroups, false, null, "u3a-download-$tname");
		}
		return $ret;
	}

	public static function get_mail_contents_div($sender_id, $mailtype, $id, $cssclass = "", $ndocs = null, $nimgs = null, $subject = null, $rel = null)
	{
		// from the contact form, the sender_id is an email address and id is a role name - the recipient
		$rcptval = "";
		if (U3A_Utilities::is_email($mailtype))
		{
			$rcptval = $mailtype;
			$mailtype = "individual";
		}
		elseif (is_array($mailtype))
		{
			$mt = [];
			foreach ($mailtype as $m)
			{
				if (U3A_Utilities::is_email($m))
				{
					$mt[] = $m;
				}
			}
			if ($mt)
			{
				$rcptval = implode(',', $mt);
				$mailtype = "individual";
			}
		}
		elseif ("contact" === $mailtype && !is_numeric($id))
		{
			$cm = U3A_Committee::get_committee($id);
			if ($cm)
			{
				$rcptval = $cm->email;
			}
			else
			{
				$wm = U3A_Committee::get_webmanager();
				$rcptval = $wm->email;
			}
		}
		if (U3A_Utilities::is_email($sender_id))
		{
			$sender_committee_id = 0;
			$sender_member_id = 0;
		}
		else
		{
			$plus = strpos($sender_id, "+");
			if ($plus !== FALSE)
			{
				$sender_member_id = intval(substr($sender_id, 0, $plus));
				$sender_committee_id = intval(substr($sender_id, $plus + 1));
			}
			elseif (is_numeric($sender_id))
			{
				$sender_member_id = intval($sender_id);
				$sender_committee_id = 0;
			}
			else
			{
				$mbr = U3A_Members::get_member($sender_id);
				if ($mbr)
				{
					$sender_member_id = intval($mbr->id);
				}
				else
				{
					$sender_member_id = $sender_id;
				}
				$sender_committee_id = 0;
			}
		}
		$iswm = U3A_Committee::is_webmanager($sender_member_id);
		$rcptinp = new U3A_INPUT("hidden", "recipient", null, NULL, $rcptval);
		$senderinp = new U3A_INPUT("hidden", "sender", null, NULL, $sender_id);
		$mbrsinp = new U3A_INPUT("hidden", "members", "u3a-mail-members-of-" . $mailtype, NULL, "");
		$mtypeinp = new U3A_INPUT("hidden", "mailtype", null, NULL, $mailtype);
		$mtypeidinp = new U3A_INPUT("hidden", $mailtype, null, NULL, $id);
		$actioninp = new U3A_INPUT("hidden", "action", null, NULL, "u3a_send_" . $mailtype . "_mail");
		if (!$sender_id)
		{
			$frominp = new U3A_INPUT("email", "from", "u3a-mail-from-" . $mailtype . "-" . $id, "u3a-inline-block u3a-mail-input-class u3a-va-top u3a-width-40-pc");
			$fromlbl = new U3A_LABEL("u3a-mail-from-" . $mailtype . "-" . $id, "Your email:", "u3a-mail-from-label-" . $mailtype . "-" . $id, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5 u3a-width-10-em u3a-va-top");
			$fromdiv = new U3A_DIV([$fromlbl, $frominp], "u3a-mail-from-div-" . $mailtype . "-" . $id, "u3a-margin-bottom-5");
		}
		else
		{
			$fromdiv = null;
		}
		$subjectinp = new U3A_INPUT("text", "subject", "u3a-mail-subject-" . $mailtype . "-" . $id, "u3a-inline-block u3a-mail-input-class u3a-va-top u3a-width-40-pc", $subject);
		$subjectlbl = new U3A_LABEL("u3a-mail-subject-" . $mailtype . "-" . $id, "Subject:", "u3a-mail-subject-label-" . $mailtype . "-" . $id, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5 u3a-width-10-em u3a-va-top");
		$subjectdiv = new U3A_DIV([$subjectlbl, $subjectinp], "u3a-mail-subject-div-" . $mailtype . "-" . $id, "u3a-margin-bottom-5");
		$contentsinp = new U3A_TEXTAREA("contents", "u3a-mail-contents-" . $mailtype . "-" . $id, "u3a-inline-block u3a-width-80-pc u3a-height-10-em u3a-va-top");
		$contentslbl = new U3A_LABEL("u3a-mail-contents", "Contents:", "u3a-mail-contents-label-" . $mailtype . "-" . $id, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5 u3a-width-10-em u3a-va-top");
		$contentsdiv = new U3A_DIV([$contentslbl, $contentsinp], "u3a-mail-contents-div-" . $mailtype . "-" . $id, "u3a-margin-bottom-5");
		$attachinp = new U3A_DIV(null, "u3a-mail-attach-" . $mailtype . "-" . $id, "u3a-inline-block u3a-width-80-pc u3a-height-5-em u3a-va-top");
		$attachinp->add_attribute("readonly", "readonly");
		$attachlbl = new U3A_LABEL("u3a-mail-attach-" . $mailtype . "-" . $id, "Attachments:", "u3a-mail-attach-label-" . $mailtype . "-" . $id, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5 u3a-width-10-em u3a-va-top");
		$attachfile = [];
		$attachdoc = [];
		$attachimg = [];
		$dtype = $id == 0 ? U3A_Documents::PRIVATE_DOCUMENT_TYPE : U3A_Documents::GROUP_DOCUMENT_TYPE;
		$mtype = $id == 0 ? U3A_Documents::COMMITTEE_IMAGE_TYPE : U3A_Documents::GROUP_IMAGE_TYPE;
		for ($n = 0; $n < U3A_Mail::MAX_ATTACHMENTS; $n++)
		{
			$attachfileinp = new U3A_INPUT("file", "attachment-$n", "u3a-mail-attachment-$mailtype-$id-$n", "u3a-invisible");
			$attachfileinp->add_attribute("onchange", "attach_file_changed('" . $mailtype . "', '" . $id . "', '" . $n . "')");
			$attachfilelbl = new U3A_LABEL("u3a-mail-attachment-$mailtype-$id-$n", "attach file", null, "u3a-file-input-label-class");
			$attachfile[] = new U3A_DIV([$attachfileinp, $attachfilelbl], "u3a-attachment-div-$mailtype-$id-$n", $n === 0 ? "u3a-va-top u3a-padding-left-5 u3a-margin-right-5 u3a-inline-block" : "u3a-va-top u3a-padding-left-5 u3a-margin-right-5 u3a-invisible");
			if ($ndocs)
			{
				$attachdocinp = self::get_mail_document_select_lists($mailtype, $id, $dtype, $n);
				$attachdoc[] = new U3A_DIV($attachdocinp, "u3a-mail-document-div-$mailtype-$id-$n", $n === 0 ? "u3a-select-dropdown-class u3a-va-top u3a-margin-right-5 u3a-inline-block" : "u3a-select-dropdown-class u3a-va-top u3a-margin-right-5 u3a-invisible");
			}
			if ($nimgs)
			{
				$attachimginp = self::get_mail_document_select_lists($mailtype, $id, $mtype, $n);
				$attachimg[] = new U3A_DIV($attachimginp, "u3a-mail-image-div-$mailtype-$id-$n", $n === 0 ? "u3a-select-dropdown-class u3a-va-top u3a-margin-right-5 u3a-inline-block" : "u3a-select-dropdown-class u3a-va-top u3a-margin-right-5 u3a-invisible");
			}
		}
		$attachfiles = new U3A_DIV([$attachfile, $attachdoc, $attachimg], "u3a-mail-attachments-div-" . $mailtype . "-" . $id, "u3a-mail-attachment-divs-class");
		$attachdiv = new U3A_DIV([$attachlbl, $attachinp, $attachfiles], "u3a-mail-attach-div-" . $mailtype . "-" . $id, "u3a-margin-bottom-5");
//		$send = new U3A_BUTTON("button", "send", "u3a-send-$mailtype-mail-button", "u3a-button", "u3a_send_mail('" . $mailtype . "')");
		$send = new U3A_A("#", "send", "u3a-send-$mailtype-mail-button", "u3a-button", "u3a_send_mail('" . $mailtype . "')");
		$send->add_attribute("disabled", "disabled");
		if ($rel)
		{
			$send->add_attribute("rel", $rel);
		}
		$clear = new U3A_A("#", "clear", "u3a-clear-$mailtype-mail-button", "u3a-button u3a-margin-left-10", "u3a_clear_mail('" . $mailtype . "', '" . $id . "')");
//		write_log("sender $sender_id wm $iswm mailtype $mailtype");
		$extradiv = null;
		$extras = [];
		// no extras for contact
//		if ($mailtype !== 'contact')
//		{
//			$noreply = new U3A_INPUT("checkbox", "sendmailnr", "u3a-sendmail-nr-checkbox-" . $mailtype . "-" . $id, "u3a-sendmail-extra-checkbox", null);
//			$noreply->add_attribute("checked", "checked");
//			$nrlbl = new U3A_LABEL("u3a-sendmail-nr-checkbox-" . $mailtype . "-" . $id, "from 'no reply'", "u3a-sendmail-nr-label", "u3a-sendmail-extra-checkbox-label");
//			$nrdiv = new U3A_DIV([$nrlbl, $noreply], "u3a-sendmail-nr-div", "u3a-inline-block u3a-margin-right-5 u3a-sendmail-extra-checkbox-div");
//			$nrdiv->add_attribute("title", "set the 'From:' header to be no-reply so it can not be used for replying to the email");
//			$extras[] = $nrdiv;
//			$usert = new U3A_INPUT("checkbox", "sendmailrt", "u3a-sendmail-rt-checkbox-" . $mailtype . "-" . $id, "u3a-sendmail-extra-checkbox", null);
//			$usert->add_attribute("checked", "checked");
//			$rtlbl = new U3A_LABEL("u3a-sendmail-rt-checkbox-" . $mailtype . " - " . $id, "set 'reply to'", "u3a-sendmail-rt-label", "u3a-sendmail-extra-checkbox-label");
//			$rtdiv = new U3A_DIV([$rtlbl, $usert], "u3a-sendmail-rt-div", "u3a-inline-block u3a-margin-right-5");
//			$rtdiv->add_attribute("title", "set the 'Reply-to:' header to the senders address so it can be used for replying to the email");
//			$extras[] = $rtdiv;
//		}
		if (($mailtype === "committee") && $sender_committee_id)
		{
			$useprivate = new U3A_INPUT("checkbox", "sendmailprivate", "u3a-sendmail-private-checkbox", "u3a-sendmail-extra-checkbox", null);
			$uplbl = new U3A_LABEL("u3a-sendmail-private-checkbox", "use private email", "u3a-sendmail-private-label", "u3a-sendmail-extra-checkbox-label");
			$updiv = new U3A_DIV([$uplbl, $useprivate], "u3a-sendmail-private-div", "u3a-inline-block u3a-margin-right-5");
			$updiv->add_attribute("title", "use committee members private email addresses, rather than the official ones for their role.");
			$extras[] = $updiv;
			$usecc = new U3A_INPUT("checkbox", "sendmailcc", "u3a-sendmail-cc-checkbox", "u3a-sendmail-extra-checkbox", null);
			$usecc->add_attribute("onchange", "u3a_use_cc_changed('u3a-sendmail-cc-checkbox')");
			$uclbl = new U3A_LABEL("u3a-sendmail-cc-checkbox", "use cc instead of bcc", "u3a-sendmail-cc-label", "u3a-sendmail-extra-checkbox-label");
			$ucdiv = new U3A_DIV([$uclbl, $usecc], "u3a-sendmail-cc-div", "u3a-inline-block u3a-margin-right-5");
			$ucdiv->add_attribute("title", "Use 'cc' for the recipients instead of 'bcc'. WARNING - this should not be done without the consent of ALL addressees; their address will be visible to others");
			$extras[] = $ucdiv;
		}
		if (($mailtype === "group") && $id)
		{
			$usecc = new U3A_INPUT("checkbox", "sendmailcc", "u3a-sendmail-cc-checkbox", "u3a-sendmail-extra-checkbox", null);
			$usecc->add_attribute("onchange", "u3a_use_cc_changed('u3a-sendmail-cc-checkbox')");
			$uclbl = new U3A_LABEL("u3a-sendmail-cc-checkbox", "use cc instead of bcc", "u3a-sendmail-cc-label", "u3a-sendmail-extra-checkbox-label");
			$ucdiv = new U3A_DIV([$uclbl, $usecc], "u3a-sendmail-cc-div", "u3a-inline-block u3a-margin-right-5");
			$ucdiv->add_attribute("title", "Use 'cc' for the recipients instead of 'bcc'. WARNING - this should not be done without the consent of ALL addressees; their address will be visible to others");
			$extras[] = $ucdiv;
		}
		if ($iswm)
		{
			$testcb = new U3A_INPUT("checkbox", "sendmailtest", "u3a-sendmail-test-checkbox-" . $mailtype . "-" . $id, "u3a-sendmail-extra-checkbox", null);
			$testlbl = new U3A_LABEL("u3a-sendmail-test-checkbox-" . $mailtype . "-" . $id, "test, do not send", "u3a-sendmail-test-label", "u3a-sendmail-extra-checkbox-label");
			$extras[] = new U3A_DIV([$testlbl, $testcb], "u3a-sendmail-test-div", "u3a-inline-block u3a-margin-right-5");
		}
		$extradiv = new U3A_DIV($extras, "u3a-extras-div", "u3a-margin-bottom-5");
		$sendiv = new U3A_DIV([$send, $clear], "u3a-send-mail-button-div-" . $mailtype . "-" . $id, "u3a-button-div-class u3a-margin-top-5");
		$form = new U3A_FORM([$rcptinp, $senderinp, $mtypeidinp, $mtypeinp, $actioninp, $mbrsinp, $fromdiv, $subjectdiv, $contentsdiv, $attachdiv, $extradiv], "/wp-admin/admin-ajax.php", "POST", "u3a-send-mail-form-" . $mailtype, "u3a-send-mail-form-class");
		$divs = [$form, $sendiv];
		return new U3A_DIV($divs, "u3a-send-mail-" . $mailtype . "-" . $id, "u3a-mail-div-class $cssclass");
	}

	public static function get_coordinators_for_group_edit($grp)
	{
		$idsuffix = "-edit";
		$group_id = $grp->id;
		$n = 0;
		$coordnames = [];
		$coords = U3A_Groups::get_coordinators($group_id);
		$mbrs = U3A_Group_Members::get_members_in_group($grp, true);
		foreach ($mbrs as $mbr)
		{
			$coordfname1 = new U3A_INPUT("text", "group-coordinator-forename", "u3a-group-coordinator-forename" . $idsuffix . "-" . $n, "u3a-input-class u3a-name-input-class", $mbr->forename);
			$coordfname1->add_attribute("readonly", "readonly");
			$coordsname1 = new U3A_INPUT("text", "group-coordinator-surname", "u3a-group-coordinator-surname" . $idsuffix . "-" . $n, "u3a-input-class u3a-name-input-class", $mbr->surname);
			$coordsname1->add_attribute("readonly", "readonly");
			$coordmnum = new U3A_INPUT("hidden", "group-coordinator-mnum", "u3a-group-coordinator-mnum" . $idsuffix . "-" . $n, "u3a-group-coordinator-mnum-class", $mbr->membership_number);
			$coordid = new U3A_INPUT("hidden", "group-coordinator-id", "u3a-group-coordinator-id" . $idsuffix . "-" . $n, "u3a-group-coordinator-id-class", $mbr->id);
//			$coorddel = new U3A_A('#u3a-group-coordinator-a-' . $n, '<span class="dashicons dashicons-no"></span>', "u3a-group-del-coord" . $idsuffix . "-" . $n, "u3a-group-del-coord-class", "u3a_remove_div('u3a-group-coordinator-outer-div-class', " . $n . ", 1)");
			$coorddel = new U3A_BUTTON("button", '<span class="dashicons dashicons-no"></span>', "u3a-group-del-coord" . $idsuffix . "-" . $n, "u3a-group-del-coord-button-class u3a-button u3a-inline-block", "u3a_remove_coordinator($n)");
			if (count($coords) === 1)
			{
				$coorddel->add_attribute("disabled", "disabled");
			}
			$coorddel->add_attribute("title", "remove as coordinator");
			$coord = new U3A_DIV([ $coordfname1, $coordsname1, $coordmnum, $coordid, $coorddel], "u3a-group-coordinator-div" . $idsuffix . "-" . $n, "u3a-group-coordinator-div-class u3a-inline-block");
			$coordlab = U3A_HTML :: labelled_html_object("coordinator:", $coord, "u3a-group-coord-label" . $idsuffix . "-" . $n, "u3a-group-coord-label-class", false, false);
			$vis = U3A_Group_Members::is_coordinator($mbr, $group_id) ? "u3a-visible" : "u3a-invisible";
			$coordnames[] = new U3A_DIV($coordlab, "u3a-group-coordinator-outer-div" . $idsuffix . "-" . $n, "u3a-group-coordinator-outer-div-class $vis");
			$n++;
		}
//		$coordplusa = new U3A_A('#u3a-group-plus-div', '<span id="u3a-group-plus" class="dashicons dashicons-plus"></span>', "u3a-group-add-coord-be-coordinator-edit" . $idsuffix, "u3a-group-add-coord-class", "u3a_show_hide_plus_minus('u3a-group-cordinator', 'u3a-group-plus')");
//		$coordplusa = new U3A_BUTTON("button", '<span id="u3a-group-plus" class="dashicons dashicons-plus"></span>', "u3a-group-add-coord-be-coordinator-edit" . $idsuffix, "u3a-group-add-coord-button-class u3a-button", "u3a_add_coordinator_block()");
//		$coordplus = new U3A_DIV($coordplusa, "u3a-group-plus-div-be-coordinator-edit" . $idsuffix, "u3a-group-div-class");
//		$coordfname1 = new U3A_INPUT("text", "group-coordinator-forename-be-coordinator-edit", "u3a-group-coordinator-forename" . $idsuffix, "u3a-input-class u3a-name-input-class");
//		$coordfname1->add_attribute("readonly", "readonly");
//		$coordsname1 = new U3A_INPUT("text", "group-coordinator-surname-be-coordinator-edit", "u3a-group-coordinator-surname" . $idsuffix, "u3a-input-class u3a-name-input-class");
//		$coordsname1->add_attribute("readonly", "readonly");
//		$coordmnum = new U3A_INPUT("hidden", "group-coordinator-mnum-be-coordinator-edit", "u3a-group-coordinator-mnum" . $idsuffix, null, "");
//		$coordsearch = do_shortcode('[u3a_find_member_dialog group="' . $grp->id . '" next_action="be_coordinator" close="tick" op="edit"]');
//		$coord = new U3A_DIV([ $coordfname1, $coordsname1, $coordsearch], "u3a-group-coordinator-div-be-coordinator-edit", "u3a-group-coordinator-div-class u3a-inline-block");
//		$coordname = new U3A_DIV(U3A_HTML::labelled_html_object("coordinator:", $coord, "u3a-group-coord-label-be-coordinator-edit", "u3a-group-coord-label-class", false, true), "u3a-group-cordinator", "u3a-invisible");
		$coordplus = do_shortcode('[u3a_find_member_dialog group="' . $group_id . '" next_action="be_coordinator" close="tick" op="edit" icon="plus" title="add another coordinator"]');
		return [$coordnames, $coordplus];
	}

}

abstract class U3A_Object implements Iterator
{

	public static function get_as_keyed_hash($objects, $key = "name")
	{
		$ret = [];
		foreach ($objects as $obj)
		{
			$k = $obj->_data[$key];
			$ret[$k] = $obj->get_as_hash($key);
		}
		return $ret;
	}

	public static function extract_field_array($object_array, $fieldname)
	{
		$ret = [];
		foreach ($object_array as $obj)
		{
			if (is_a($obj, "U3A_Object"))
			{
				$ret[] = $obj->$fieldname;
			}
			else
			{
				$ret[] = null;
			}
		}
		return $ret;
	}

	public $_data;

	public function __construct()
	{
		$this->_data = array();
	}

// magic methods!
	public function __set($property, $value)
	{
		return $this->_data[$property] = $value;
	}

	public function __get($property)
	{
//        echo "here get ".$property."<br/>";
		$ret = null;
		$mname = "get_" . $property;
		if (method_exists($this, $mname))
		{
			$ret = $this->$mname();
		}
		elseif (array_key_exists($property, $this->_data))
		{
			$ret = $this->_data[$property];
		}
		return $ret;
	}

	public function get_as_hash($exclude = null)
	{
		$ret = array();
		foreach ($this->_data as $k => $v)
		{
			if (($exclude == null) || ($k != $exclude))
			{
				$ret[$k] = $v;
			}
		}
		return $ret;
	}

	protected function adjust_hash($hash)
	{
		return $hash;
	}

	public function set_all($hash)
	{
		$h = $this->adjust_hash($hash);
		foreach ($h as $k => $v)
		{
			$this->_data[$k] = stripslashes($v);
		}
	}

	public function rewind()
	{
//        echo "rewinding\n";
		reset($this->_data);
	}

	public function current()
	{
		$var = current($this->_data);
//        echo "current: $var\n";
		return $var;
	}

	public function key()
	{
		$var = key($this->_data);
//        echo "key: $var\n";
		return $var;
	}

	public function next()
	{
		$var = next($this->_data);
//        echo "next: $var\n";
		return $var;
	}

	public function valid()
	{
		$key = key($this->_data);
		$var = ($key !== NULL && $key !== FALSE);
//        echo "valid: $var\n";
		return $var;
	}

	public function field_is_set($fieldname)
	{
		$ret = false;
		if (is_array($fieldname))
		{
			$len = count($fieldname);
			if ($len > 0)
			{
				$ret = $this->field_is_set($fieldname[0]);
				for ($n = 1; $n < $len && $ret; $n++)
				{
					$ret = $ret && $this->field_is_set($fieldname[$n]);
				}
			}
		}
		else
		{
			$ret = isset($this->_data[$fieldname]) && $this->_data[$fieldname];
		}
		return $ret;
	}

	public function get_field($field_name)
	{
		$ret = null;
		if ($field_name)
		{
			if (array_key_exists($field_name, $this->_data))
			{
				$ret = $this->_data[$field_name];
			}
			elseif (method_exists($this, "get_" . $field_name))
			{
				$method_name = "get_" . $field_name;
				$ret = $this->$method_name();
			}
		}
		return $ret;
	}

}

class U3A_String_Set
{

	public static function union($a, $b)
	{
		$ret = $a->copy();
		$ret->add($b);
		return $ret;
	}

	public static function intersection($a, $b)
	{
		$ret = new U3A_String_Set();
		foreach ($a->get_as_array() as $v)
		{
			if ($b->contains($v))
			{
				$ret->add($v);
			}
		}
		return $ret;
	}

	public static function difference($a, $b)
	{
		$ret = new U3A_String_Set();
		foreach ($a->get_as_array() as $v)
		{
			if (!$b->contains($v))
			{
				$ret->add($v);
			}
		}
		return $ret;
	}

	protected $_data;

	public function __construct($param = null)
	{
		$this->_data = array();
		if ($param !== null)
		{
			if (is_string($param))
			{
				$this->_data[$param] = null;
			}
			elseif (is_array($param))
			{
				foreach ($param as $v)
				{
					$this->_data[$v] = null;
				}
			}
		}
	}

	public function add($value)
	{
		if (is_string($value))
		{
			$this->_data[$value] = null;
		}
		elseif (is_array($value))
		{
			foreach ($value as $v)
			{
				$this->_data[$v] = null;
			}
		}
		elseif ($value instanceof U3A_String_Set)
		{
			foreach ($value->get_as_array() as $v)
			{
				$this->_data[$v] = null;
			}
		}
	}

	public function contains($value)
	{
		return array_key_exists($value, $this->_data);
	}

	public function remove($value)
	{
		if (array_key_exists($value, $this->_data))
		{
			unset($this->_data[$value]);
		}
	}

	public function get_as_array()
	{
		$ret = array();
		foreach ($this->_data as $k => $v)
		{
			$ret[] = $k;
		}
		return $ret;
	}

	public function copy()
	{
		return new U3A_String_Set($this->get_as_array());
	}

	public function size()
	{
		return count($this->_data);
	}

}

abstract class U3A_Row extends U3A_Object
{

	public static $_testing = false;
	private static $_test_counter = 1;

	public static function get_single_value($objclass, $column_name, $where, $orderby = null, $desc = false)
	{
		$ret = null;
		$tname = null;
		if (class_exists($objclass))
		{
			$obj = new $objclass();
			if ($obj instanceof U3A_Row)
			{
				$tname = $obj->_tablename;
			}
		}
		else
		{
			$tname = $objclass;
		}
		if ($tname != null)
		{
			$whereclause = null;
			if ($where != null)
			{
				if (is_array($where))
				{
					if (count($where) > 0)
					{
						$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
					}
				}
				else
				{
					$whereclause = " WHERE " . $where;
				}
			}
			$order = "";
			if ($orderby)
			{
				$order = " ORDER BY $orderby";
				if ($desc)
				{
					$order .= " DESC";
				}
			}
			$sql = "SELECT $column_name FROM " . $tname . ($whereclause = null ? "" : $whereclause) . $order . " LIMIT 1";
			$ret = Project_Details::get_db()->loadResult($sql);
		}
		return $ret;
	}

	public static function load_single_object($objclass, $where, $orderby = null, $desc = false)
	{
//	var_dump($where);
		$obj = new $objclass();
		$whereclause = null;
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
//		write_log($whereclause);
		if ($obj instanceof U3A_Row)
		{
			$order = "";
			if ($orderby)
			{
				$order = " ORDER BY $orderby";
				if ($desc)
				{
					$order .= " DESC";
				}
			}
			$sql = "SELECT * FROM " . $obj->_tablename . ($whereclause = null ? "" : $whereclause) . $order . " LIMIT 1";
//            echo $sql;//exit;
			$hash = array();
//			U3A_Logger::get_logger()->ojdebug1("load single ".$sql);
			$loaded = Project_Details::get_db()->loadHash($sql, $hash);
			if ($loaded)
			{
				$obj->set_all($hash);
			}
			else
			{
				$obj = null;
			}
		}
		else
		{
			$obj = null;
		}
		return $obj;
	}

	public static function load_column($tablename, $colname, $where = null, $distinct = false, $ordered = false)
	{
		$whereclause = "";
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
		$sel = $distinct ? "SELECT DISTINCT " : "SELECT ";
		$sql = $sel . $colname . " FROM " . $tablename . $whereclause . ($ordered ? (" ORDER BY " . $colname) : "");
//		U3A_Logger::get_logger()->ojdebug1("load_column sql ".$sql);
//		echo $sql."<br/>";
		$ret = Project_Details::get_db()->loadColumn($sql);
		return $ret;
	}

	public static function load_column_by_id($tablename, $colname, $where = null)
	{
		$whereclause = null;
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
		$sql = "SELECT id, " . $colname . " FROM " . $tablename . ($whereclause = null ? "" : $whereclause);
		U3A_Logger::get_logger()->ojdebug1("load_column sql " . $sql);
//		echo $sql."<br/>";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = [];
		foreach ($list as $el)
		{
			$ret[$el["id"]] = $el[$colname];
		}
		return $ret;
	}

	public static function has_rows($objclass, $where = null)
	{
		return self::count_rows($objclass, $where) > 0;
	}

	public static function count_rows($objclass, $where = null)
	{
		$obj = new $objclass();
		$whereclause = null;
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
		$ret = 0;
		if ($obj instanceof U3A_Row)
		{
			$sql = "SELECT * FROM " . $obj->_tablename . ($whereclause == null ? "" : $whereclause);
			$ret1 = Project_Details::get_db()->loadList($sql);
//			var_dump($ret1);
			$ret = $ret1 ? count($ret1) : 0;
		}
		return $ret;
	}

	public static function load_array_of_objects($objclass, $where = null, $orderby = null, $from = 0, $to = -1, $groupby = null, $distinct = false)
	{
		$obj = new $objclass();
		$ret = array();
		$whereclause = null;
//		var_dump($where);
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
//		write_log($where);
//		write_log($whereclause);
		$sel = $distinct ? "SELECT DISTINCT" : "SELECT";
		$num = 0;
		if ($obj instanceof U3A_Row)
		{
			$sql = "$sel * FROM " . $obj->_tablename . ($whereclause == null ? "" : $whereclause) . ($groupby == null ? "" : (" GROUP BY " . $groupby)) . ($orderby == null ? "" : (" ORDER BY " . $orderby));
//			echo $sql . "<br/>";
			$list = Project_Details::get_db()->loadList($sql);
			$ret = self::get_objects_from_list($objclass, $list, $from, $to);
			$num = count($list);
		}
		return array("total_number_of_rows" => $num, "result" => $ret);
	}

	public static function get_objects_from_list($objclass, $list, $from = 0, $to = -1)
	{
		$ret = [];
		if ($list)
		{
			if ($to < 0)
			{
				$to = count($list);
			}
			for ($n = $from; $n < $to; $n++)
			{
				$obj = new $objclass();
				$obj->set_all($list[$n]);
				$ret[] = $obj;
			}
		}
		return $ret;
	}

	public static function load_hash_of_all_objects($objclass, $where = null, $key = "id", $orderby = null, $saveall = false)
	{
		$obj = new $objclass();
		$ret = array();
		$num = 0;
		$ret = array();
		if (($where != null) && !is_string($where))
		{
			$whereclause = " WHERE" . U3A_Utilities::get_where_clause($where);
		}
		else
		{
			$whereclause = $where;
		}
		if ($obj instanceof U3A_Row)
		{
			$sql = "SELECT * FROM " . $obj->_tablename . ($whereclause = null ? "" : $whereclause) . ($orderby == null ? "" : (" ORDER BY " . $orderby));
//			U3A_Logger::get_logger()->ojdebug("sql", $sql);
			$list = Project_Details::get_db()->loadList($sql);
			foreach ($list as $hash)
			{
				$obj = new $objclass();
				$obj->set_all($hash);
				$k = $obj->$key;
				if ($k != null)
				{
					if ($key == 'id')
					{
						$ret[$k] = $obj;
						if ($obj->updateable())
						{
							$pk = $obj->get_parent_id();
							if (($pk > 0) && array_key_exists($pk, $ret))
							{
								unset($ret[$pk]);
							}
						}
					}
					elseif ($saveall)
					{
						if (array_key_exists($k, $ret))
						{
							array_push($ret[$k], $obj);
						}
						else
						{
							$ret[$k] = array($obj);
						}
					}
					elseif (!array_key_exists($k, $ret))
					{
						$ret[$k] = $obj;
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * returns a hash of objects keyed by the column named in $key. If this is "id", the values in the
	 * hash will be single objects. If it is anything else it will be an array of objects all of which have
	 * the same value for that key
	 */
	public static function load_paged_hash_of_unique_objects($objclass, $where = null, $key = "id", $orderby = null, $pageno = 0, $pagesize = 30)
	{
		$obj = new $objclass();
		$tablename = $obj->_tablename;
		$whereclause = null;
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
		$sql = "SELECT * FROM " . $tablename . ($whereclause = null ? "" : $whereclause) . ($orderby == null ? "" : (" ORDER BY " . $orderby));
		if (isset($_SESSION[$sql]))
		{
			$list = $_SESSION[$sql];
		}
		else
		{
			$list = self::load_hash_of_all_objects($objclass, $whereclause, $key, $orderby, false);
			$_SESSION[$sql] = $list;
//			echo "loaded<br/>";
		}
		if (isset($_SESSION[$sql . "-keys"]))
		{
			$keys = $_SESSION[$sql . "-keys"];
		}
		else
		{
			$keys = array_keys($list);
			$_SESSION[$sql . "-keys"] = $keys;
		}
		$maxnum = count($keys);
		$maxpageno = floor(($maxnum - 1) / $pagesize) + 1;
		$from = $pageno * $pagesize;
		$to = $from + $pagesize;
		if ($to > $maxnum)
		{
			$to = $maxnum;
		}

		$ret1 = array();
//		echo "here ".$maxnum." ".$maxpageno." ".$from." ".$to."<br/>";//exit;
		for ($n = $from; $n < $to; $n++)
		{
			$k = $keys[$n];
//			echo $k;
//			var_dump($list[$k]); exit;
			if ($k != null)
			{
				$ret1[$k] = $list[$k];
			}
		}
		$ret = array("total_number_of_rows" => $maxnum, "result" => $ret1);
//		echo "here "."<br/>";exit;
		return $ret;
	}

	public static function delete_rows($tablename, $where)
	{
		return Project_Details::get_db()->delete($tablename, $where);
	}

	public static function save_from_form($post, $tablename, $classname, $editof)
	{
//        echo $tablename."<br/>".$classname."<br/>".$editof."<br/>";
//        U3A_Utilities::var_dump_pre($post);//exit;
		$hash = array();
		$len = strlen($tablename) + 4;
		foreach ($post as $k => $v)
		{
			if (U3A_Utilities::starts_with($k, "oj-"))
			{
				$key = str_replace('-', '_', substr($k, $len));
			}
			else
			{
				$key = $k;
			}
			$hash[$key] = $v;
		}
//        echo $tablename. " ".$classname."<br/>";
//        U3A_Utilities::var_dump_pre($hash);exit;
		$obj = new $classname($editof);
//        U3A_Utilities::var_dump_pre($obj);
//        U3A_Utilities::var_dump_pre($hash);
		$obj->set_all($hash);
//        U3A_Utilities::var_dump_pre($obj);exit;
		return $obj->save();
	}

	public static function insert_multiple($tablename, $rows)
	{
		$toinsert = [];
		foreach ($rows as $row)
		{
			if (is_array($row))
			{
				$toinsert[] = $row;
			}
			else
			{
				$toinsert[] = $row->get_insert_array();
			}
		}
		Project_Details::get_db()->insert($tablename, $toinsert);
		$id = Project_Details::get_db()->insertId();
		$ret = [];
		for ($n = 0; $n < count($rows); $n++)
		{
			$ret = $id + $n;
		}
		return $ret;
	}

	public static function get_max($tablename, $colname = "id", $where = null)
	{
		$whereclause = null;
		if ($where != null)
		{
			if (is_array($where))
			{
				if (count($where) > 0)
				{
					$whereclause = " WHERE " . U3A_Utilities::get_where_clause($where);
				}
			}
			else
			{
				$whereclause = " WHERE " . $where;
			}
		}
		$sql = "SELECT MAX($colname) FROM $tablename" . ($whereclause = null ? "" : $whereclause);
		$max = Project_Details::get_db()->loadResult($sql);
		return $max;
	}

	public static function get_the_column_names($tablename)
	{
		$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $tablename . "'";
		$ret = Project_Details::get_db()->loadColumn($sql);
		return $ret;
	}

	public static function is_one_of($row_key, $row_array)
	{
		$ret = -1;
		if ($row_array)
		{
			for ($n = 0; $n < count($row_array); $n++)
			{
				if ($row_key == $row_array[$n]->get_key())
				{
					$ret = $n;
					break;
				}
			}
		}
		return $ret;
	}

	protected $_keyname;
	protected $_tablename;
	protected $_must_be_set_to_save;
	protected $_valid = true;
	protected $_form_input_details;
	protected $_nameval;
	protected $_autoincrement;
	protected $_column_names = null;
	protected $_column_name_set = null;

	public function __construct($tablename, $keyname, $param = null, $checkval = 'name', $where = null, $nameval = null, $autoincrement = true)
	{
		parent::__construct();
		$this->_keyname = $keyname;
		$this->_tablename = $tablename;
		$this->_must_be_set_to_save = array();
		$this->_form_input_details = array();
		$this->_nameval = $nameval;
		$this->_autoincrement = $autoincrement;
//        $this->_must_be_set_to_save[] = 'name';
		$hash = array();
		if (!isset($param) || ($param == null))
		{
			$hash = array();
		}
		elseif (is_array($param))
		{
			if (isset($param[$keyname]) || (($checkval != null) && isset($param[$checkval])))
			{
				$hash = null;
				if (isset($param[$keyname]))
				{
					$hash1 = array();
					$pk = $param[$keyname];
					if (!is_numeric($pk))
					{
						$pk = "'" . $pk . "'";
					}
					$loaded = Project_Details::get_db()->loadHash("SELECT * FROM " . $tablename . " WHERE " . $keyname . " = " . $pk . " LIMIT 1", $hash1);
					if ($loaded)
					{
						$hash = array_merge($hash1, $param);
					}
//					else if ($param[$keyname])
//					{
//						U3A_Logger::get_logger()->ojdebug("invalid key value", $param[$keyname]);
//					}
					else
					{
						$hash = $param;
					}
				}
				elseif (isset($param[$checkval]))
				{
					$sql = "SELECT * FROM $tablename WHERE" . U3A_Utilities::get_where_clause($param) . " LIMIT 1";
					$hash1 = array();
					$loaded = Project_Details::get_db()->loadHash($sql, $hash1);
					if ($loaded)
					{
						$hash = array_merge($hash1, $param);
					}
					else
					{
//                        echo $sql;
						$hash = $param;
					}
				}
			}
			else
			{
				$hash = $param;
			}
		}
		else
		{
			$wherebit = null;
			if (is_numeric($param))
			{
				$wherebit = $keyname . " = " . $param;
			}
			else if ($checkval != null)
			{
				$wherebit = "UPPER(" . $checkval . ") = UPPER('" . $param . "')";
			}
			else
			{
				$wherebit = $keyname . " = '" . $param . "'";
			}
			if ($where != null)
			{
				$wh = U3A_Utilities::get_where_clause($where);
				if ($wherebit == null)
				{
					$wherebit = $wh;
				}
				else
				{
					$wherebit .= " AND " . $wh;
				}
			}
			if ($wherebit != null)
			{
				$hash = array();
				$sel = "SELECT * FROM " . $tablename . " WHERE " . $wherebit . " LIMIT 1";
//                echo $sel;
				$loaded = Project_Details::get_db()->loadHash($sel, $hash);
			}
		}
		$this->_valid = $hash != null;
		if ($this->_valid)
		{
			$this->_data = array();
			foreach ($hash as $key => $value)
			{
				$this->_data[$key] = $value;
			}
		}
	}

	public function is_valid()
	{
		return $this->_valid;
	}

	protected function saveable()
	{
		$ret = true;
		foreach ($this->_must_be_set_to_save as $musthave)
		{
			$ret = $ret && isset($this->_data[$musthave]);
		}
		return $ret;
	}

	private function get_insert_array()
	{
		$ret = [];
		foreach ($this->_data as $key => $value)
		{
			if (($value !== null) && ($this->_keyname != $key))
			{
				if (is_string($value))
				{
					$val = trim($value);
					if (strlen($val) > 0)
					{
						$save[$key] = addslashes($val);
					}
				}
				else
				{
					$save[$key] = $value;
				}
			}
		}
		return $ret;
	}

	public function save($allow_null = false, $checkfirst = false, $checkid = true)
	{
		$ret = 0;
		if (!$checkid && !$this->_autoincrement)
		{
			$checkfirst = true;
		}
		if ($this->saveable())
		{
			$save = array();
			$id = '0';
			$saveid = '0';
//			write_log($this->_data);
			$colset = $this->get_column_name_set();
			foreach ($this->_data as $key => $value)
			{
				if ($colset->contains($key))
				{
					if ($allow_null || $value !== null)
					{
						if ($this->_keyname == $key)
						{
							if ($checkid && (!$this->_autoincrement || (is_numeric($value) && ($value > 0))))
							{
								$saveid = $value;
								$id = $value;
							}
							$save[$key] = $value;
						}
						elseif (is_string($value))
						{
							$val = trim($value);
							if (strlen($val) > 0)
							{
								$save[$key] = addslashes($val);
							}
						}
						else
						{
							$save[$key] = $value;
						}
					}
				}
			}
			$update = false;
			if ($id)
			{
				$update = true;
				$ret = $id;
			}
//			write_log("update $update, id $id");
//			write_log($save);
			if ($update)
			{
				if (self::$_testing)
				{
					print "update " . $this->_tablename . "\n";
					var_dump($save);
				}
				else
				{
					Project_Details::get_db()->updateArray($this->_tablename, $save, $this->_keyname);
//					write_log("updated");
				}
			}
			else
			{
				if ($checkfirst)
				{
					$save1 = true;
					if (!$checkid && $saveid)
					{
						$sql = "SELECT COUNT(*) FROM $this->_tablename WHERE $this->_keyname = $saveid";
						$num1 = Project_Details::get_db()->query($sql);
						$num = array_values($num1[0])[0];
						if ($num > 0)
						{
							Project_Details::get_db()->updateArray($this->_tablename, $save, $this->_keyname);
							$save1 = false;
						}
					}
					if ($save1)
					{
						$whereclause = " WHERE " . U3A_Utilities::get_where_clause($save);
						$sql = "SELECT $this->_keyname FROM " . $this->_tablename . $whereclause . " LIMIT 1";
						$ret1 = Project_Details::get_db()->loadResult($sql);
						if ($ret1 == null)
						{
							if (self::$_testing)
							{
								print "1.insert " . $this->_tablename . "\n";
								var_dump($save);
								$ret = self::$_test_counter++;
							}
							else
							{
								$ret = Project_Details::get_db()->insertArray($this->_tablename, $save);
							}
						}
						else
						{
							$ret = $ret1;
						}
					}
				}
				else
				{
					if (self::$_testing)
					{
						print "2.insert " . $this->_tablename . "\n";
						var_dump($save);
						$ret = self::$_test_counter++;
					}
					else
					{
						$ret = Project_Details::get_db()->insertArray($this->_tablename, $save);
					}
				}
			}
		}
		if ($ret > 0)
		{
			$this->_data[$this->_keyname] = $ret;
		}
		return $ret;
	}

	public function get_table_name()
	{
		return $this->_tablename;
	}

	public function get_column_names()
	{
		if ($this->_column_names == null)
		{
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $this->_tablename . "'";
			$this->_column_names = Project_Details::get_db()->loadColumn($sql);
		}
		return $this->_column_names;
	}

	public function get_column_name_set()
	{
		if ($this->_column_name_set == null)
		{
			$this->_column_name_set = new U3A_String_Set($this->get_column_names());
		}
		return $this->_column_name_set;
	}

	public function updateable()
	{
		$cns = $this->get_column_name_set();
		return $cnc->contains($this->_tablename . '$parent_id');
	}

	public function get_parent_id()
	{
		$ret = 0;
		$fname = $this->_tablename . '$parent_id';
		if (array_key_exists($fname, $this->_data))
		{
			$ret = $this->_data[$fname];
		}
		return $ret;
	}

	public function get_key()
	{
		$ret = null;
		if (array_key_exists($this->_keyname, $this->_data))
		{
			$ret = $this->_data[$this->_keyname];
		}
		return $ret;
	}

	public function delete()
	{
		$ret = false;
		$key = $this->get_key();
		if ($key != null)
		{
//			if (is_numeric($key))
//			{
//				$where = $this->_keyname . " = " . $key;
//			}
//			else
//			{
//				$where = $this->_keyname . " = '" . $key . "'";
//			}
			$where = [$this->_keyname => $key];
			$r = Project_Details::get_db()->delete($this->_tablename, $where);
			$ret = $r != null;
		}
		return $ret;
	}

	public function get_name()
	{
		if ($this->_nameval == null)
		{
			$ret = strval($this->get_key());
		}
		elseif (array_key_exists($this->_nameval, $this->_data))
		{
			$ret = $this->_data[$this->_nameval];
		}
		else
		{
			$ret = strval($this->get_key());
		}
		return $ret;
	}

	public abstract function get_table_information();
}

class U3A_HTML
{

	public static function to_html($args)
	{
		$ret = null;
		if ($args !== null)
		{
			$ret = '';
			if (is_array($args))
			{
				foreach ($args as $arg)
				{
					$ret .= self::to_html($arg);
				}
			}
			elseif ($args instanceof U3A_HTMLizable)
			{
				$ret .= $args->to_html();
			}
			elseif (is_string($args))
			{
				$ret .= $args;
			}
		}
		return $ret;
	}

	public static function insert_into_paragraphs($args)
	{
		$ret = null;
		if ($args != null)
		{
			if (is_array($args))
			{
				$ret = array();
				foreach ($args as $arg)
				{
					$ret[] = new U3A_P($arg);
				}
			}
			elseif ($args instanceof U3A_HTMLizable)
			{
				$ret = new U3A_P($args);
			}
		}
		return $ret;
	}

	public static function add_line_breaks($args)
	{
		$ret = null;
		if ($args != null)
		{
			if (is_array($args))
			{
				$ret = array();
				$first = true;
				foreach ($args as $arg)
				{
					if ($first)
					{
						$first = false;
						$ret[] = $arg;
					}
					else
					{
						$ret[] = "<br/>" . $arg;
					}
				}
			}
			elseif (is_string($args))
			{
				$ret = $args . "<br/>";
			}
			else
			{
				$ret = $args;
			}
		}
		return $ret;
	}

	public static function labelled_html_object($text, $htmlobj, $lblid = null, $lblcssclass = null, $inpara = true, $indiv = false, $description = null, $small = false)
	{
		$id = $htmlobj->id;
		if ($id == null)
		{
			$id = "u3a-id-" . str_replace(' ', '-', microtime());
			$htmlobj->id = $id;
		}
		$ret = new U3A_LABEL($id, array(new U3A_SPAN($text, null, "u3a-labelled-object-text"), $htmlobj), $lblid, $lblcssclass);
//        $lbl = new U3A_LABEL($id, $text, $lblid, $lblcssclass);
//        $ret = array($lbl, $htmlobj);
		if ($inpara)
		{
			$ret = self::insert_into_paragraphs($ret);
		}
		elseif ($indiv)
		{
			$containerid = $id . '-container';
			if ($description != null)
			{
				$desc = new U3A_DIV(new U3A_P($description), null, "oj-help-tip");
				$ret = array($desc, $ret);
			}
			$css1 = $small ? "u3a-labelled-object-container-small" : "u3a-labelled-object-container";
			$ret = new U3A_DIV($ret, $containerid, $css1);
		}
		return $ret;
	}

	public static function split_uri($uri)
	{
		$q = strpos($uri, '?');
		if ($q === FALSE)
		{
			$qs = "";
			$path = $uri;
		}
		else
		{
			$qs = substr($uri, $q + 1);
			$path = substr($uri, 0, $q);
		}
		$ret = [];
		if (strlen($qs) > 0)
		{
			$qsa = explode('&', $qs);
			foreach ($qsa as $item)
			{
				$itema = explode('=', $item);
				$ret[$itema[0]] = $itema[1];
			}
		}
		return ["path" => $path, "query" => $ret];
	}

}

interface U3A_HTMLizable
{

	public function to_html();
}

class U3A_Form_Select_Detail
{

	public function __construct($form_input_detail, $class_name, $where = null, $text_key = null, $value_key = null, $tooltip_key = null)
	{
		$tabinfo = $form_input_detail->get_table_information();
		$tablename = $tabinfo->get_tablename_from_classname($class_name);
//        echo "classname ".$class_name." tablename ".$tablename."<br/>";
		$this->_data['classname'] = $class_name;
		$this->_data['where'] = $where;
		$this->_data['text_key'] = $tabinfo->get_table_equivalent($tablename, $text_key);
		$this->_data['value_key'] = $tabinfo->get_table_equivalent($tablename, $value_key);
		$this->_data['tooltip_key'] = $tabinfo->get_table_equivalent($tablename, $tooltip_key);
		$this->_data['form_input_detail'] = $form_input_detail;
	}

	public function get_input_name()
	{
		return U3A_Utilities::get_input_name_from_column_name($this->_data['form_input_detail']->tablename, $this->_data['form_input_detail']->columnname);
	}

	public function get_select_object($instance = null)
	{
		$fid = $this->_data['form_input_detail'];
//        U3A_Utilities::var_dump_pre($fid);
		$id = $fid->get_input_name();
		$cssclass = $fid->cssclass;
//        echo "here1 ".$this->_data['text_key']. " ".$this->_data['value_key']."<br/>";
		if ($this->_data['text_key'] == null)
		{
			$initial_value = null;
			if ($instance != null)
			{
				$fid = $this->_data['form_input_detail'];
				$col = $fid->columnname;
				$initial_value = $instance->$col;
			}
			$ret = U3A_HTML_Utilities::get_select_list_from_array($fid->contents, $this->classname, $initial_value, $id, $cssclass);
		}
		else
		{
			$ret = U3A_HTML_Utilities::get_select_list_of_some($this->_data['classname'], $this->_data['where'], $this->get_input_name(), $this->_data['text_key'], $this->_data['value_key'], $this->_data['value_key'], $this->_data['tooltip_key'], $id, $cssclass);
		}
		$ret->add_attribute("name", $fid->get_input_name());
		return $ret;
	}

}

class U3A_Form_Input_Detail extends U3A_Object
{

	/**
	 * returns a hash keyed by column name
	 */
	public static function get_form_details_for_table($tablename, $objclass, $omit_columns = null)
	{
		$sql = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . $tablename . "'";
		$list = Project_Details::get_db()->loadList($sql);
		$obj = new $objclass();
		$table_info = $obj->get_table_information();
		$ret = array();
		foreach ($list as $hash)
		{
			$nm = $hash['COLUMN_NAME'];
			if (($nm != 'id') && (($omit_columns == null) || (array_search($nm, $omit_columns) === FALSE)))
			{
				$nm1 = $nm;
				if (U3A_Utilities::ends_with($nm, "s_id"))
				{
					$nm1 = substr($nm, 0, -4);
				}
				else if (U3A_Utilities::ends_with($nm, "_id"))
				{
					$nm1 = substr($nm, 0, -3);
				}
				if (($dlr = strpos($nm1, '$')) !== FALSE)
				{
					$nm1 = substr($nm1, $dlr + 1);
				}
				$lbl = str_replace('_', ' ', $nm1) . ": ";
				switch ($hash['DATA_TYPE']) {
					case 'varchar':
						if (($dlr = strpos($nm, '$')) !== FALSE)
						{
							$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'user');
							$lbl = substr($nm, $dlr + 1);
						}
						elseif ((strpos($nm, 'colour') !== FALSE) || (strpos($nm, 'color') !== FALSE))
						{
							$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'color');
						}
						elseif ((strpos($nm, 'path') !== FALSE) || (strpos($nm, 'icon') !== FALSE))
						{
							$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'file');
						}
						else
						{
							$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'text');
						}
						break;
					case 'tinyint':
						$fid = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'select');
						$fid->contents = 'array:' . $nm;
						$ret[$nm] = $fid;
						break;
					case 'smallint':
						$fid = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'number');
						$fid->contents = 'num:' . $nm;
						$ret[$nm] = $fid;
						break;
					case 'int':
						if (U3A_Utilities::ends_with($nm, '_id'))
						{
							$fid = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'select');
							$fid->contents = $nm;
							$ret[$nm] = $fid;
						}
						else
						{
							$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'text');
						}
						break;
					case 'date':
					case 'time':
					case 'datetime':
						$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, $hash['DATA_TYPE']);
						break;
					case 'text':
						$ret[$nm] = new U3A_Form_Input_Detail($table_info, $obj, $nm, 'textarea');
						break;
					default:
						break;
				}
				$ret[$nm]->label = $lbl;
				$ret[$nm]->description = $table_info->get_description($tablename, $nm);
			}
		}
		return $ret;
	}

	public static function get_form($form_input_detail, $action = null, $method = null, $id = null, $cssclass = null, $instance = null)
	{
		$contents_id = $id == null ? null : $id . "-contents";
		$contents_cssclass = $cssclass == null ? "u3a-form-input" : ($cssclass . "-contents");
		if (is_array($form_input_detail))
		{
			$keys = array_keys($form_input_detail);
			$fid = $form_input_detail[$keys[0]];
			$tablename = $fid->tablename;
			$classname = $fid->classname;
		}
		else
		{
			$tablename = $form_input_detail->tablename;
			$classname = $form_input_detail->classname;
		}
		$contents = self::get_input_object($form_input_detail, $contents_id, $contents_cssclass, $instance);
		$tbl = new U3A_INPUT("hidden", "tablename", null, null, $tablename);
		$cls = new U3A_INPUT("hidden", "classname", null, null, $classname);
		if ($instance == null)
		{
			$form_contents = array($contents, $tbl, $cls);
		}
		else
		{
			$iid = $instance->get_key();
			$idinp = new U3A_INPUT("hidden", "instance", null, null, $iid);
			$form_contents = array($contents, $tbl, $cls, $idinp);
		}
		return new U3A_FORM($form_contents, $action, $method, $id, $cssclass);
	}

	public static function get_form_with_submit_button($form_input_detail, $action = null, $method = null, $id = null, $cssclass = null, $module = "sysadmin")
	{
		$querystring = "m = " . $module . "&a = " . $action;
		$encodedhref = '?' . encodeQueryString($querystring);
		$contents_id = $id == null ? null : $id . "-contents";
		$contents_cssclass = $cssclass == null ? null : $cssclass . "-contents";
		$hasfile = false;
		if (is_array($form_input_detail))
		{
			$fid = reset($form_input_detail);
			foreach ($form_input_detail as $k => $v)
			{
				$hasfile = $hasfile || $v->type == 'file';
			}
		}
		else
		{
			$hasfile = $form_input_detail->type == 'file';
			$fid = $form_input_detail;
		}
//        U3A_Utilities::var_dump_pre($form_input_detail);exit;
		$contents = self::get_input_object($form_input_detail, $contents_id, $contents_cssclass);
//        U3A_Utilities::var_dump_pre($form_input_detail);U3A_Utilities::var_dump_pre($contents);
		$tbl = new U3A_INPUT("hidden", "tablename", null, null, $fid->tablename);
		$cls = new U3A_INPUT("hidden", "classname", null, null, $fid->classname);
//        $dosql = new U3A_INPUT("hidden", "dosql", null, null, $action);
		$csrf = new U3A_INPUT("hidden", "csrf_token", null, null, $_SESSION['CSRFToken']);
		$submit_id = $id == null ? null : $id . "-submit";
		$submit_cssclass = $cssclass == null ? null : $cssclass . "-submit";
// <input type="submit" value="Submit" data-role="button" data-icon="check" data-theme="c" />
		$btn = new U3A_INPUT("submit", null, null, null, "Submit");
		$btn->add_attribute("data-role", "button");
		$btn->add_attribute("data-icon", "check");
		$btn->add_attribute("data-theme", "c");
//        $btn = new U3A_BUTTON("submit", "Submit");
//        $btn->add_attribute("style", "width:12em;");
		$submit = new U3A_DIV($btn, null, "u3a-submit-button-div");
		$ret = new U3A_FORM(array($contents, $tbl, $cls, $dosql, $csrf, $submit), $encodedhref, $method, $id, $cssclass);
		if ($hasfile)
		{
			$ret->add_attribute("enctype", "multipart/form-data");
		}
		return $ret;
	}

	public static function get_input_object($form_input_detail, $id = null, $cssclass = null, $instance = null)
	{
//        U3A_Utilities::var_dump_pre($instance);
		$ret = null;
		if (is_array($form_input_detail))
		{
			$ret1 = array();
			foreach ($form_input_detail as $fid)
			{
				$ret1[] = self::get_input_object($fid, null, null, $instance);
			}
			$ret = new U3A_DIV($ret1, $id, $cssclass);
		}
		else
		{
			if ($form_input_detail instanceof U3A_Form_Input_Detail)
			{
				$form_input_detail->set_initial_value($instance);
				$ret1 = null;
				$contents = $form_input_detail->contents;
//            echo $form_input_detail->type." <br/>";
//            U3A_Utilities::var_dump_pre($form_input_detail);
				switch ($form_input_detail->type) {
					case 'select':
						if ($contents != null)
						{
//                        U3A_Utilities::var_dump_pre($contents);
							if ($contents instanceof U3A_Form_Select_Detail)
							{
								$ret1 = $contents->get_select_object($instance);
							}
							elseif (is_array($contents))
							{
								$sel = new U3A_Form_Select_Detail($form_input_detail);
								$ret1 = $sel->get_select_object($instance);
							}
							elseif (is_string($contents))
							{
//                            echo $contents."<br/>";
								if (U3A_Utilities::starts_with($contents, "array:"))
								{
//                                U3A_Utilities::var_dump_pre($form_input_detail);
									$colon = strpos($contents, ':');
									$tabinfo = $form_input_detail->get_table_information();
									$array = $tabinfo->get_select_array($form_input_detail->tablename, substr($contents, $colon + 1));
									if ($array != null)
									{
										$form_input_detail->contents = $array;
										$sel = new U3A_Form_Select_Detail($form_input_detail);
										$ret1 = $sel->get_select_object($instance);
									}
								}
								elseif (U3A_Utilities::ends_with($contents, '_id'))
								{
									$tname = substr($contents, 0, -3);
									$dlr = strpos($tname, '$');
									$tabinfo = $form_input_detail->get_table_information();
									if ($dlr === FALSE)
									{

										$class_name = $tabinfo->get_classname_from_tablename($tname);
										$sel = new U3A_Form_Select_Detail($form_input_detail, $class_name, null, "name", "id", null);
										$ret1 = $sel->get_select_object($instance);
									}
									else
									{
										$key = substr($tname, $dlr + 1);
										$tname1 = substr($tname, 0, $dlr);
//                                    echo $tname1."__".$key."<br/>";
										$class_name = $tabinfo->get_classname_from_tablename($tname1);
//                                    echo $class_name."<br/>";
										$where = $tabinfo->get_where_array($tname1, $key);
//                                    U3A_Utilities::var_dump_pre($where);
										$sel = new U3A_Form_Select_Detail($form_input_detail, $class_name, $where, "name", "id", null);
										$ret1 = $sel->get_select_object($instance);
									}
								}
							}
						}
						break;
					case 'number':
						if ($contents !== null)
						{
							if (is_numeric($contents))
							{
								$form_input_detail->initial_value = $contents;
							}
							elseif (is_string($contents) && U3A_Utilities::starts_with($contents, "num:"))
							{
//                                U3A_Utilities::var_dump_pre($form_input_detail);
								$colon = strpos($contents, ':');
								$tabinfo = $form_input_detail->get_table_information();
								$key = substr($contents, $colon + 1);
//                            echo $form_input_detail->tablename."__".$key;
								$array = $tabinfo->get_number_array($form_input_detail->tablename, $key);
//                            U3A_Utilities::var_dump_pre($array);
								if ($array != null)
								{
									$form_input_detail->add_attributes($array);
									$form_input_detail->contents = null;
								}
							}
						}
						$ret1 = new U3A_INPUT($form_input_detail->type, $form_input_detail->get_input_name(), $form_input_detail->get_input_name(), $form_input_detail->cssclass, $form_input_detail->initial_value);
						break;
					case 'user':
//                    echo "in case user<br/>";
						if (($contents != null) && ($contents instanceof U3A_HTML_Object))
						{
							$ret1 = $contents;
						}
						else
						{
//                        echo "calling user function<br/>";
							$tabinfo = $form_input_detail->get_table_information();
							$dlr = strpos($form_input_detail->columnname, '$');
							if ($dlr === FALSE)
							{
								$cn = $form_input_detail->columnname;
							}
							elseif ($dlr == (strlen($form_input_detail->columnname) - 1))
							{
								$cn = substr($form_input_detail->columnname, 0, -1);
							}
							else
							{
								$cn = substr($form_input_detail->columnname, $dlr + 1);
							}
//                        echo $form_input_detail->tablename." ".$cn;
							$ret1 = $tabinfo->get_user_object($form_input_detail->tablename, $cn);
//                        U3A_Utilities::var_dump_pre($ret1);
						}
						$form_input_detail->label = null;
						break;
					case 'sub':
						if (($contents != null) && ($contents instanceof U3A_Form_Input_Detail))
						{
							$ret1 = new U3A_DIV(get_input_object($contents), $form_input_detail->get_input_name(), $form_input_detail->cssclass);
						}
						elseif (is_array($contents))
						{
							$tmp = array();
							foreach ($contents as $element)
							{
								if ($element instanceof U3A_Form_Input_Detail)
								{
									$tmp[] = get_input_object($element);
								}
							}
							$ret1 = new U3A_DIV($tmp, $form_input_detail->get_input_name(), $form_input_detail->cssclass);
						}
						break;
					case 'textarea':
						$ret1 = new U3A_TEXTAREA($form_input_detail->get_input_name(), $form_input_detail->get_input_name(), $form_input_detail->cssclass, $form_input_detail->initial_value);
						break;
					default:
						$ret1 = new U3A_INPUT($form_input_detail->type, $form_input_detail->get_input_name(), $form_input_detail->get_input_name(), $form_input_detail->cssclass, $form_input_detail->initial_value);
						break;
				}
				$atts = $form_input_detail->get_attributes();
				foreach ($atts as $attname => $attval)
				{
					$ret1->add_attribute($attname, $attval);
				}
				$lbl = $form_input_detail->label;
				if ($lbl == null)
				{
					$ret = $ret1;
				}
				else
				{
					$ret = U3A_HTML::labelled_html_object($lbl, $ret1, null, null, false, true, $form_input_detail->description);
					$desc = $form_input_detail->description;
				}
//            echo $lbl."<br/>";
//U3A_Utilities::var_dump_pre($ret);
			}
			elseif ($form_input_detail instanceof U3A_HTML_Object)
			{
				$ret = $form_input_detail;
			}
			if ($id != null)
			{
				$ret->add_attribute("id", $id);
			}
			if ($cssclass == null)
			{
				$cssclass = "u3a-form-input";
			}
			$ret->add_attribute("class", $cssclass);
		}
		return $ret;
	}

	private $_attributes;
	private $_table_info;

	public function __construct($table_info, $obj, $column_name, $type)
	{
		$this->_table_info = $table_info;
		if (is_string($obj))
		{
			if (class_exists($obj))
			{
				$this->_data['classname'] = $obj;
				$this->_data['tablename'] = $table_info->get_tablename_from_classname($obj);
			}
			else
			{
				$this->_data['classname'] = $table_info->get_classname_from_tablename($obj);
				$this->_data['tablename'] = $obj;
			}
		}
		else
		{
			$this->_data['classname'] = get_class($obj);
			$this->_data['tablename'] = $obj->get_table_name();
		}
		$this->_data['columnname'] = $column_name;
		$this->_data['type'] = $type;
		$this->_attributes = array();
	}

	public function set_initial_value($instance)
	{
		if ($instance != null)
		{
			$col = $this->_data['columnname'];
			$this->_data['initial_value'] = stripslashes($instance->$col);
//			echo "initial data for ".$this->_data['columnname']." set to ".$this->_data['initial_value']."\n";
		}
	}

	public function get_table_information()
	{
		return $this->_table_info;
	}

	public function get_input_name()
	{
		return U3A_Utilities::get_input_name_from_column_name($this->tablename, $this->columnname);
	}

	public function add_attribute($attname, $attval)
	{
		$this->_attributes[$attname] = $attval;
	}

	public function add_attributes($atts)
	{
		foreach ($atts as $attname => $attval)
		{
			$this->_attributes[$attname] = $attval;
		}
	}

	public function get_attribute($attname)
	{
		return array_key_exists($attname, $this->_attributes) ? $this->_attributes[$attname] : null
		;
	}

	public function get_attributes()
	{
		return $this->_attributes;
	}

}

class U3A_HTML_Object extends U3A_Object implements U3A_HTMLizable
{

	protected $_tag;
	protected $_contents;

	public function __construct($tag, $contents, $id = null, $cssclass = null)
	{
		parent::__construct();
		$this->_tag = $tag;
		$this->_contents = U3A_HTML::to_html($contents);
		if ($id != null)
		{
			$this->_data['id'] = $id;
		}
		if ($cssclass != null)
		{
			$this->_data['class'] = $cssclass;
		}
	}

	public function append_to_contents($newcontents)
	{
		$this->_contents .= U3A_HTML::to_html($newcontents);
	}

	public function prepend_to_contents($newcontents)
	{
		$this->_contents = U3A_HTML::to_html($newcontents) . $this->_contents;
	}

	public function add_attribute($attname, $attval)
	{
		if ($attval === null)
		{
			if (isset($this->_data[$attname]))
			{
				unset($this->_data[$attname]);
			}
		}
		else
		{
			$this->_data[$attname] = $attval;
		}
	}

	public function add_attributes($atts)
	{
		foreach ($atts as $attname => $attval)
		{
			$this->add_attribute($attname, $attval);
		}
	}

	public function has_attribute($attname)
	{
		return array_key_exists($attname, $this->_data);
	}

	public function to_html()
	{
		$rest = $this->_contents === null ? '/>' : ('>' . $this->_contents . '</' . $this->_tag . '>');
		$atts = '';
//		write_log($this);
//        echo "rest of ".$this->_tag." is ".$rest." rest";
		foreach ($this->_data as $key => $value)
		{
			$atts .= ' ' . $key . '="' . $value . '"';
			if (is_array($key) || is_array($value))
			{
				write_log($this);
			}
		}
		$ret = '<' . $this->_tag . $atts . $rest;
// if ($this->_tag == 'div')
// {
// $ret .= "<!--". $this->_data['id'].'-->';
// }
		return $ret;
	}

	public function get_contents_html()
	{
		return $this->_contents === null ? "" : $this->_contents;
	}

	public function is_empty()
	{
		return $this->_contents == null;
	}

	public function add_tooltip($tt)
	{
		$this->add_attribute("title", $tt);
	}

	public function add_class($cl)
	{
		if (array_key_exists("class", $this->_data))
		{
			$newcl = $this->_data['class'] . " " . $cl;
		}
		else
		{
			$newcl = $cl;
		}
		$this->_data['class'] = $newcl;
	}

	public function __toString()
	{
		return $this->to_html();
	}

}

class U3A_LABEL extends U3A_HTML_Object
{

	public function __construct($for, $text, $id = null, $cssclass = null)
	{
		parent::__construct('label', $text, $id, $cssclass);
		$forid = '';
		if ($for != null)
		{
			if (is_string($for))
			{
				$forid = $for;
			}
			elseif ($for instanceof U3A_HTML_Object)
			{
				$forid = $for->id;
			}
			$this->_data['for'] = $forid;
		}
	}

}

class U3A_A extends U3A_HTML_Object
{

	public function __construct($href, $contents, $id = null, $cssclass = null, $onclick = null)
	{
		parent::__construct('a', $contents, $id, $cssclass == null ? "u3a-link-class" : $cssclass);
		if ($href != null)
		{
			$this->_data['href'] = $href;
		}
		if ($onclick != null)
		{
			$this->_data['onclick'] = $onclick;
		}
	}

}

class U3A_IFRAME extends U3A_HTML_Object
{

	public function __construct($src, $id = null, $cssclass = null, $alt = null)
	{
		parent::__construct('iframe', new U3A_P($alt == null ? "Your browser does not support iframes." : $alt), $id, $cssclass);
		if ($src != null)
		{
			$this->_data['src'] = $src;
		}
	}

}

class U3A_IMG extends U3A_HTML_Object
{

	public function __construct($src, $id = null, $cssclass = null, $onclick = null, $alt = "no image available")
	{
		parent::__construct('img', null, $id, $cssclass);
		if ($src != null)
		{
			$this->_data['src'] = $src;
		}
		if ($onclick != null)
		{
			$this->_data['onclick'] = $onclick;
		}
		if ($alt != null)
		{
			$this->_data['alt'] = $alt;
		}
	}

}

class U3A_P extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('p', $contents, $id, $cssclass);
	}

}

class U3A_H extends U3A_HTML_Object
{

	public function __construct($num, $text)
	{
		parent::__construct('h' . $num, $text, null, null);
	}

}

class U3A_B extends U3A_HTML_Object
{

	public function __construct($text)
	{
		parent::__construct('b', $text, null, null);
	}

}

class U3A_I extends U3A_HTML_Object
{

	public function __construct($text)
	{
		parent::__construct('i', $text, null, null);
	}

}

class U3A_FIGURE extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('figure', $contents === null ? "" : $contents, $id, $cssclass);
	}

}

class U3A_DIV extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('div', $contents === null ? "" : $contents, $id, $cssclass);
	}

}

class U3A_DIV_ARRAY extends U3A_DIV
{

	private static function make_contents($contents, $vertical)
	{
		$ret = array();
		if (is_array($contents))
		{
			foreach ($contents as $arg)
			{
				if ($arg instanceof U3A_DIV)
				{
					$ret[] = $arg;
				}
				else
				{
					$ret[] = new U3A_DIV($arg);
				}
			}
		}
		elseif ($contents instanceof U3A_DIV)
		{
			$ret[] = $contents;
		}
		else
		{
			$ret[] = new U3A_DIV($contents);
		}
		if (!$vertical)
		{
			foreach ($ret as $div)
			{
				$div->add_class("u3a-inline-block");
			}
		}
		return $ret;
	}

	private $_vertical;
	private $_divs;

	public function __construct($contents, $vertical = true, $id = null, $cssclass = null)
	{
		parent::__construct(self::make_contents($contents, $vertical), $id, $cssclass);
		$this->_vertical = $vertical;
		$this->_divs = self::make_contents($contents, $vertical);
	}

	public function add_div($div)
	{
		$newdivs = self::make_contents($div, $this->_vertical);
		foreach ($newdivs as $nd)
		{
			$this->_divs[] = $nd;
		}
		$this->_contents = U3A_HTML::to_html($this->_divs);
	}

}

class U3A_FORM extends U3A_HTML_Object
{

	public function __construct($contents, $action = null, $method = null, $id = null, $cssclass = null)
	{
		parent::__construct('form', $contents, $id, $cssclass);
		if ($action != null)
		{
			$this->_data['action'] = $action;
		}
		if ($method != null)
		{
			$this->_data['method'] = $method;
		}
	}

}

class U3A_INPUT extends U3A_HTML_Object
{

	public static function yes_no_ignore_radio_array($name, $horizontal = true)
	{
		return self::radio_array($name, ["yes" => 1, "no" => 0, "ignore" => -1], $horizontal, "ignore");
	}

	public static function yes_no_ignore_value_radio_array($name, $horizontal = true)
	{
		$rad = self::radio_array($name, ["yes" => 1, "no" => 0, "ignore" => -1], $horizontal, "ignore", "u3a_enable_if_checked('$name-yes', 'u3a-filter-$name-yes-value')");
		$div0 = new U3A_DIV($rad, null, "u3a-filter-ignore-div-class");
		$val = new U3A_INPUT("text", "$name-value", "u3a-filter-$name-yes-value", "u3a-filter-ignore-value-class");
		$val->add_attribute("disabled", "disabled");
		$div1 = new U3A_DIV($val, null, "u3a-filter-ignore-div-class");
		return [$div0, $div1];
//		$ret = new U3A_DIV([$div0, $div1], "u3a-yes-no-ignore-value", "u3a-yes-no-ignore-value")
	}

	//u3a_enable_if_checked(check_if_checked, to_enable)

	public static function yes_no_radio_array($name, $horizontal = true)
	{
		return self::radio_array($name, ["yes" => 1, "no" => 0], $horizontal);
	}

	public static function radio_array($name, $labval, $horizontal = true, $selected = null, $onchange = null)
	{
		$ret = [];
		foreach ($labval as $label => $value)
		{
			$lab = strtolower(str_replace(" ", "-", $label));
			$inp = new U3A_INPUT("radio", $name, "$name-$lab", "u3a-radio-array-item", $value);
			if ($onchange)
			{
				$inp->add_attribute("onchange", $onchange);
//				$onchange = null;
			}
			if ($label === $selected)
			{
				$inp->add_attribute("checked", "checked");
			}
			if ($horizontal)
			{
				$ret[] = new U3A_DIV([new U3A_LABEL($inp, $label, "$name-$lab-div", "u3a-radio-array-div"), $inp], null, "u3a-inline-block");
			}
			else
			{
				$ret[] = new U3A_DIV([new U3A_LABEL($inp, $label, "$name-$lab-div", "u3a-radio-array-div"), $inp], null, "u3a-block");
			}
		}
		return $ret;
	}

	public static function checkbox_array($id, $labval, $horizontal = true, $label_cssclass = "")
	{
		$ret = [];
		foreach ($labval as $label => $value)
		{
			$lab = strtolower(str_replace(" ", "-", $label));
			$inp = new U3A_INPUT("checkbox", $lab, "$id-$lab", "u3a-checkbox-array-item-$id", $value);
//			if ($label === $selected)
//			{
//				$inp->add_attribute("checked", "checked");
//			}
			if ($horizontal)
			{
				$ret[] = new U3A_DIV([new U3A_LABEL($inp, $label, "$id-$lab-div", "u3a-checkbox-array-label u3a-inline-block $label_cssclass"), $inp], null, "u3a-inline-block");
			}
			else
			{
				$ret[] = new U3A_DIV([new U3A_LABEL($inp, $label, "$id-$lab-div", "u3a-checkbox-array-label u3a-inline-block $label_cssclass"), $inp], null, "u3a-block");
			}
		}
		return $ret;
	}

	public function __construct($type, $name, $id = null, $cssclass = null, $value = null)
	{
		parent::__construct('input', null, $id, $cssclass);
		$this->_data['type'] = $type;
		if ($name != null)
		{
			$this->_data['name'] = $name;
// if ($id == null)
// {
// $this->_data['id'] = $name;
// }
		}
		if ($value !== null)
		{
			if ($type == 'file')
			{
				$this->_data['title'] = $value;
			}
			else
			{
				$this->_data['value'] = $value;
			}
		}
	}

}

class U3A_TEXTAREA extends U3A_HTML_Object
{

	public function __construct($name, $id = null, $cssclass = null, $value = '')
	{
		parent::__construct('textarea', $value, $id, $cssclass);
		if ($name != null)
		{
			$this->_data['name'] = $name;
			if ($id == null)
			{
				$this->_data['id'] = $name;
			}
		}
//		if ($value !== null)
//		{
//			$this->_data['value'] = $value;
//		}
//		else
//		{
//			$this->_data['value'] = '';
//		}
	}

}

class U3A_BUTTON extends U3A_HTML_Object
{

	public function __construct($type, $text, $id = null, $cssclass = null, $onclick = null)
	{
		parent::__construct('button', $text, $id, $cssclass);
		$this->_data['type'] = $type;
		if ($onclick !== null)
		{
			$this->_data['onclick'] = $onclick;
		}
	}

}

class U3A_SPAN extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('span', $contents, $id, $cssclass);
	}

}

class U3A_LI extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('li', $contents, $id, $cssclass);
	}

}

class U3A_LEGEND extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('legend', $contents, $id, $cssclass);
	}

}

class U3A_FIELDSET extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('fieldset', $contents, $id, $cssclass);
	}

}

class U3A_LIST extends U3A_HTML_Object
{

	public function __construct($items, $ordered = false, $id = null, $cssclass = null)
	{
		parent::__construct($ordered ? 'ol' : 'ul', $items, $id, $cssclass);
	}

}

class U3A_OPTION extends U3A_HTML_Object
{

	public static function get_options($strings, $selected1 = null)
	{
		$ret = [];
		foreach ($strings as $str)
		{
			$ret[] = new U3A_OPTION($str, $str, $str === $selected1);
		}
		return $ret;
	}

	public function __construct($text, $value, $selected = false, $id = null, $cssclass = null)
	{
		parent::__construct('option', $text, $id, $cssclass);
		if ($value !== null)
		{
			$this->_data['value'] = $value;
		}
		else
		{
			$this->_data['value'] = $text;
		}
		if ($selected)
		{
			$this->_data['selected'] = 'selected';
		}
	}

	public function select()
	{
		$this->_data['selected'] = 'selected';
	}

	public function deselect()
	{
		if (isset($this->_data['selected']))
		{
			unset($this->_data['selected']);
		}
	}

}

class U3A_OPTGROUP extends U3A_HTML_Object
{

	private $_options;

	public function __construct($options, $label)
	{
		parent::__construct('optgroup', U3A_HTML::to_html($options));
		$this->_options = $options;
		if ($label != null)
		{
			$this->_data['label'] = $label;
		}
	}

	public function select_by_value($val)
	{
		if ($this->_options)
		{
			foreach ($this->_options as $opt)
			{
				if ($opt instanceof U3A_OPTION)
				{
					if ($opt->value == $val)
					{
						$opt->select();
					}
					else
					{
						$opt->deselect();
					}
				}
			}
			$this->_contents = U3A_HTML::to_html($this->_options);
		}
	}

}

class U3A_SELECT extends U3A_HTML_Object
{

	private $_options;

	public function __construct($options, $name, $id = null, $cssclass = null)
	{
		parent::__construct('select', U3A_HTML::to_html($options), $id, $cssclass);
		$this->_options = $options;
		if ($name != null)
		{
			$this->_data['name'] = $name;
			if ($id == null)
			{
				$this->_data['id'] = $name;
			}
		}
	}

	public function select_by_value($val)
	{
		if ($this->_options)
		{
			foreach ($this->_options as $opt)
			{
				if ($opt instanceof U3A_OPTGROUP)
				{
					$opt->select_by_value($val);
				}
				elseif ($opt instanceof U3A_OPTION)
				{
					if ($opt->value == $val)
					{
						$opt->select();
					}
					else
					{
						$opt->deselect();
					}
				}
			}
			$this->_contents = U3A_HTML::to_html($this->_options);
		}
	}

}

class U3A_TD extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('td', $contents, $id, $cssclass);
	}

}

class U3A_TH extends U3A_HTML_Object
{

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('th', $contents, $id, $cssclass);
	}

}

class U3A_TR extends U3A_HTML_Object
{

	private static function make_contents($contents)
	{
		$ret = '';
		if (is_array($contents) && (count($contents) > 0) && (($contents[0] instanceof U3A_TD) || ($contents[0] instanceof U3A_TH)))
		{
			$ret = $contents;
		}
		return $ret;
	}

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('tr', self::make_contents($contents), $id, $cssclass);
	}

}

class U3A_TBODY extends U3A_HTML_Object
{

	private static function make_contents($contents)
	{
//        var_dump($contents); echo "<br/>";
		$ret = '';
		if (is_array($contents) && (count($contents) > 0) && ($contents[0] instanceof U3A_TR))
		{
			$ret = $contents;
		}
//        var_dump($ret); echo "<br/>"; echo "<br/>";
		return $ret;
	}

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('tbody', self::make_contents($contents), $id, $cssclass);
	}

}

class U3A_TFOOT extends U3A_HTML_Object
{

	private static function make_contents($contents)
	{
		$ret = '';
		if (is_array($contents) && (count($contents) > 0) && ($contents[0] instanceof U3A_TR))
		{
			$ret = $contents;
		}
		return $ret;
	}

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('tfoot', self::make_contents($contents), $id, $cssclass);
	}

}

class U3A_THEAD extends U3A_HTML_Object
{

	private static function make_contents($contents)
	{
		$ret = '';
		if ((is_array($contents) && (count($contents) > 0) && ($contents[0] instanceof U3A_TR)) || ($contents instanceof U3A_TR))
		{
			$ret = $contents;
		}
		return $ret;
	}

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('thead', self::make_contents($contents), $id, $cssclass);
	}

}

class U3A_TABLE extends U3A_HTML_Object
{

	private static function make_contents($contents)
	{
		$ret = '';
		if ($contents != null)
		{
			if ($contents instanceof U3A_TBODY)
			{
				$ret = $contents;
			}
			elseif (is_array($contents) && (count($contents) > 0))
			{
				if ($contents[0] instanceof U3A_TR)
				{
					$ret = new U3A_TBODY($contents);
				}
				elseif (($contents[0] instanceof U3A_TBODY) || ($contents[0] instanceof U3A_THEAD) || ($contents[0] instanceof U3A_TFOOT))
				{
					$ret = $contents;
				}
			}
		}
		return $ret;
	}

	public function __construct($contents, $id = null, $cssclass = null)
	{
		parent::__construct('table', self::make_contents($contents), $id, $cssclass);
	}

}

class U3A_HR extends U3A_HTML_Object
{

	public function __construct()
	{
		parent::__construct('hr', null, null, null);
	}

}

class U3A_Concertina extends U3A_DIV
{

	private static function make_contents($text, $contents, $slug, $loadparam)
	{
		if (($slug == null) && is_a($contents, 'U3A_HTML_Object'))
		{
			$slug = $contents->id;
		}
		$lp = $loadparam == null ? '' : ", '" . $loadparam . "'";
		$lnk = new U3A_A("#", $text, null, "u3a-link-class", "U3A_open_div('" . $slug . "'" . $lp . ");");
		$lnkdiv = new U3A_DIV($lnk->to_html(), $slug . '-div-link', "u3a-expandable-link");
		$closelnk = new U3A_A("#", "close", null, null, "U3A_close_div('" . $slug . "');");
		$closediv = new U3A_DIV($closelnk->to_html(), $slug . '-div-close', "u3a-expandable-close");
		$div = new U3A_DIV(U3A_HTML::to_html(array($contents, $closediv)), $slug . '-div', "u3a-expandable");
		$ret = U3A_HTML::to_html(array($lnkdiv, $div));
		return $ret;
	}

	private static function make_id($contents, $slug)
	{
		if (($slug == null) && is_a($contents, 'U3A_HTML_Object'))
		{
			$slug = $contents->id;
		}
		return $slug . '-div-outer';
	}

	public function __construct($text, $contents, $slug = null, $loadparam = null)
	{
		parent::__construct(self::make_contents($text, $contents, $slug, $loadparam), self::make_id($contents, $slug), "u3a-expandable-outer");
	}

}

class U3A_Collapsible extends U3A_DIV
{

	public function __construct($contents, $id, $cssclass, $heading, $headingnum = 2)
	{
		parent::__construct(array(new U3A_H($headingnum, $heading), $contents), $id, $cssclass);
		$this->add_attribute("data-role", "collapsible");
	}

}

class U3A_CollapsibleSet extends U3A_DIV
{

	public function __construct($collapsibles, $id, $cssclass)
	{
		parent::__construct($collapsibles, $id, $cssclass);
		$this->add_attribute("data-role", "collapsible-set");
	}

}

class U3A_Collapse extends U3A_DIV
{

	private static function make_contents($contents, $id, $cssclass, $heading, $headingnum, $open, $parentid, $selectable)
	{
//	  echo "heading=".$heading."<br/>";
		$headingid = $id == null ? null : ($id . "-heading");
		$headingdivid = $id == null ? null : ($id . "-heading-div");
		$panelbodyid = $id == null ? null : ($id . "-panel-body");
		$collapseid = $id == null ? ("div-" . time() . "-collapse") : ($id . "-collapse");
		$hdga = new U3A_A('#' . $collapseid, $heading, null, null);
		$hdga->add_attribute("data-toggle", "collapse");
		if ($parentid != null)
		{
			$hdga->add_attribute("data-parent", "#" . $parentid);
		}
		if ($selectable)
		{
			$hdgs = new U3A_INPUT("checkbox", $collapseid . "-cb", $collapseid . "-cb", "u3a-select-cb oj-checkbox");
			$hdgsp = new U3A_SPAN(" ");
			$hdg = new U3A_H($headingnum, array($hdgs, $hdgsp, $hdga));
		}
		else
		{
			$hdg = new U3A_H($headingnum, $hdga);
		}
		$hdg->add_class('panel-title');
		$hdgdiv = new U3A_DIV($hdg, $headingdivid, "panel-heading");
		$panelbody = new U3A_DIV($contents, $panelbodyid, "panel-body");
		$panel = new U3A_DIV($panelbody, $collapseid, "panel-collapse collapse" . ($open ? " in" : ""));
		return array($hdgdiv, $panel);
	}

	public function __construct($contents, $id, $cssclass, $heading, $headingnum = 2, $open = false, $parentid = null, $selectable = false)
	{
		parent::__construct(self::make_contents($contents, $id, $cssclass, $heading, $headingnum, $open, $parentid, $selectable), $id, $cssclass == null ? "panel panel-default" : ("panel panel-default " . $cssclass));
	}

}

class U3A_Accordion extends U3A_DIV
{

	private static function make_contents($contentsarray, $id, $cssclass, $headingnum, $selectable)
	{
		$ret = array();
		$n = 0;
//		var_dump($contentsarray);
		foreach ($contentsarray as $heading => $contents)
		{
			$open = strpos($heading, '__open') !== false;
			$hdg1 = $open ? str_replace('__open', '', $heading) : $heading;
			$sel = strpos($hdg1, '__selectable') !== false;
			$hdg = $sel ? str_replace('__selectable', '', $hdg1) : $hdg1;
			$ret[] = new U3A_Collapse($contents, $id . "-" . $n, null, $hdg, $headingnum, $open, $id, $selectable && $sel);
			$n++;
		}
		return $ret;
	}

	/**
	 * contentsarray has form header=>contents. If header ends with '__open' then that one will be initially open
	 */
	public function __construct($contentsarray, $id, $cssclass, $headingnum = 2, $selectable = false)
	{
		parent::__construct(self::make_contents($contentsarray, $id, $cssclass, $headingnum, $selectable), $id, $cssclass);
	}

}

abstract class BasicEnum
{

	private static $constCacheArray = NULL;

	private static function getConstants()
	{
		if (self::$constCacheArray == NULL)
		{
			self::$constCacheArray = [];
		}
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$constCacheArray))
		{
			$reflect = new ReflectionClass($calledClass);
			self::$constCacheArray[$calledClass] = $reflect->getConstants();
		}
		return self::$constCacheArray[$calledClass];
	}

	public static function isValidName($name, $strict = false)
	{
		$constants = self::getConstants();

		if ($strict)
		{
			return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

	public static function isValidValue($value, $strict = true)
	{
		$values = array_values(self::getConstants());
		return in_array($value, $values, $strict);
	}

	public static function stringValue($value)
	{
		return array_search($value, self::getConstants());
	}

	public static function getValue($str)
	{
		$enum = self::getConstants();
		return array_key_exists($str, $enum) ? $enum[$str] : null;
	}

}

class U3A_Stack
{

	private $_thestack;
	private $_next1;

	public function __construct()
	{
		$this->_thestack = [];
		$this->_next1 = 0;
	}

	public function push($obj)
	{
		$this->_thestack[$this->_next1] = $obj;
		$this->_next1++;
	}

	public function pop()
	{
		$ret = null;
		if ($this->_next1 > 0)
		{
			$this->_next1--;
			$ret = $this->_thestack[$this->_next1];
		}
		return $ret;
	}

	public function peek()
	{
		$ret = null;
		if ($this->_next1 > 0)
		{
			$nxt1 = $this->_next1 - 1;
			$ret = $this->_thestack[$nxt1];
		}
		return $ret;
	}

	public function isempty()
	{
		return $this->_next1 === 0;
	}

	public function size()
	{
		return $this->_next1;
	}

	public function clear()
	{
		$this->_next1 = 0;
	}

}

class U3A_String extends U3A_Object
{

	public static function compare($ojstringa, $ojstringb)
	{
		$ret = 0;
		if ($ojstringa->starts_with_number && $ojstringa->starts_with_number)
		{
			$ret = $ojstringa->number < $ojstringb->number ? -1 : ($ojstringa->number > $ojstringb->number ? 1 : 0);
			if ($ret === 0)
			{
				$ret = strcasecmp($ojstringa->rest, $ojstringb->rest);
			}
		}
		else
		{
			$ret = strcasecmp($ojstringa->as_string(), $ojstringb->as_string());
		}
		return $ret;
	}

	public static function renumber_string($ojstring, $n)
	{
		$isastring = false;
		if (is_string($ojstring))
		{
			$ojstr = new U3A_String($ojstring);
			$isastring = true;
		}
		else
		{
			$ojstr = $ojstring;
		}
		$ojstr->number = $n;
		$ojstr->gap = " ";
		$ojstr->starts_with_number = true;
		return $isastring ? $ojstr->as_string() : $ojstr;
	}

	public static function renumber_array(&$ojstring_array, $starting_at = 1)
	{
		$n = $starting_at;
		foreach ($ojstring_array as &$ojstr)
		{
			$ojstr->number = $n;
			$n++;
			$ojstr->gap = " ";
			$ojstr->starts_with_number = true;
		}
	}

	public function __construct($str)
	{
		$length = strlen($str);
		$num = "";
		$gap = "";
		$base = "";
		$phase = 0;
		$numbase = 10;
		for ($i = 0, $int = ''; $i < $length; $i++)
		{
			if ($phase === 0)
			{
				if (ctype_xdigit($str[$i]))
				{
					if (!ctype_digit($str[$i]))
					{
						$numbase = 16;
					}
					$num .= $str[$i];
				}
				else
				{
					if (ctype_alnum($str[$i]))
					{
						$base = $num;
						$num = "";
						$phase = 2;
					}
					else
					{
						$phase++;
					}
				}
			}
			if ($phase === 1)
			{
				if (!ctype_alnum($str[$i]))
				{
					$gap .= $str[$i];
				}
				else
				{
					$phase++;
				}
			}
			if ($phase === 2)
			{
				$base .= $str[$i];
			}
//			print $phase." ".$str[$i]." ".$numbase."\n";
		}
		$swn = strlen($num) !== 0;
		$num = $swn ? intval($num, $numbase) : 0;
		$this->_data["starts_with_number"] = $swn;
		$this->_data["number"] = $num;
		$this->_data["gap"] = $gap;
		$this->_data["rest"] = $base;
		$this->_data["original"] = $str;
	}

	public function as_string()
	{
		return $this->_data["starts_with_number"] ? (sprintf('%02d', $this->_data["number"]) . $this->_data["gap"] . $this->_data["rest"]) : $this->_data["original"];
	}

	public function __toString()
	{
		$this->as_string();
	}

}

class U3A_File extends U3A_Object
{

	public static function compare($str1, $str2)
	{
		$ojs1 = new U3A_File($str1);
		$ojs2 = new U3A_File($str2);
		$n1 = $ojs1->starts_with_number();
		$n2 = $ojs2->starts_with_number();
		if (($n1 === false) || ($n2 === false))
		{
			$ret = strcasecmp($ojs1->base, $ojs2->base);
		}
		else
		{
			$ret = $n1 < $n2 ? -1 : ($n1 > $n2 ? 1 : 0);
		}
		return $ret;
	}

	public function __construct($path)
	{
		parent::__construct();
		$this->_data["dir"] = dirname($path);
		$bname = basename($path);
		$lastdot = strrpos($bname, '.');
		if ($lastdot !== FALSE)
		{
			$this->_data["extension"] = substr($bname, $lastdot + 1);
			$str = substr($bname, 0, $lastdot);
		}
		else
		{
			$this->_data["extension"] = "";
			$str = $bname;
		}
		$length = strlen($str);
		$num = "";
		$gap = "";
		$base = "";
		$phase = 0;
		$numbase = 10;
		for ($i = 0, $int = ''; $i < $length; $i++)
		{
			if ($phase === 0)
			{
				if (ctype_xdigit($str[$i]))
				{
					if (!ctype_digit($str[$i]))
					{
						$numbase = 16;
					}
					$num .= $str[$i];
				}
				else
				{
					if (ctype_alnum($str[$i]))
					{
						$base = $num;
						$num = "";
						$phase = 2;
					}
					else
					{
						$phase++;
					}
				}
			}
			if ($phase === 1)
			{
				if (!ctype_alnum($str[$i]))
				{
					$gap .= $str[$i];
				}
				else
				{
					$phase++;
				}
			}
			if ($phase === 2)
			{
				$base .= $str[$i];
			}
//			print $phase." ".$str[$i]." ".$numbase."\n";
		}
		$this->_data["number"] = $num;
		$this->_data["gap"] = $gap;
		$this->_data["name"] = $base;
		$this->_data["base"] = $numbase;
	}

	public function __toString()
	{
		$dir = (strlen($this->_data["dir"]) === 0) || ($this->_data["dir"] === '.') ? "" : ($this->_data["dir"] . DIRECTORY_SEPARATOR);
//		$num = strlen($this->_data["number"]) === 0?"":($this->_data["base"] === 16?$this->_data["number"]):$this->_data["number"]);
		$ext = strlen($this->_data["extension"]) === 0 ? "" : ('.' . $this->_data["extension"]);
		return $dir . $this->_data["number"] . $this->_data["gap"] . $this->_data["name"] . $ext;
	}

	public function get_name()
	{
		return $this->_data["number"] . $this->_data["gap"] . $this->_data["name"];
	}

	public function starts_with_number()
	{
		$ret = false;
		if (strlen($this->_data["number"]) > 0)
		{
			$ret = intval($this->_data["number"], $this->_data["base"]);
		}
		return $ret;
	}

}

class U3A_Folder extends U3A_File
{

// $restricted by of form [label=>[extensions]...]
// an empty extensions array means accept everything
	public function __construct($dirpath, $recursive = false, $restricted_by = null)
	{
		parent::__construct($dirpath);
//		echo "dirpath ".$dirpath."\n";
		if ($restricted_by === null)
		{
			$restricted_by = ["files" => []];
		}
		$this->_data["path"] = U3A_File_Utilities::check_dirname($dirpath);
//		print $this->_data["path"]."\n";
		$files = array_diff(scandir($this->_data["path"]), array('.', '..'));
		$this->_data["subdirs"] = [];
		foreach ($restricted_by as $label => $exts)
		{
			$this->_data['label_' . $label] = [];
		}
		$thisclass = get_class($this);
		foreach ($files as $f)
		{
			$file = $this->_data["path"] . $f;
			if (is_dir($file))
			{
//				echo "is_dir ".$file."\n";
				if ($recursive)
				{
					array_push($this->_data["subdirs"], new $thisclass($file, $recursive, $restricted_by));
				}
				else
				{
					array_push($this->_data["subdirs"], new U3A_File($file . DIRECTORY_SEPARATOR));
				}
			}
			else
			{
//				echo "not_dir ".$file."\n";
				foreach ($restricted_by as $label => $extensions)
				{
					if ((count($extensions) == 0) || U3A_File_Utilities::is_file_of_type($f, $extensions))
					{
						array_push($this->_data['label_' . $label], new U3A_File($file));
					}
				}
			}
		}
//		print get_class($this)."\n";
	}

	function contains_files_with_label($label)
	{
		return isset($this->_data['label_' . $label]) && (count($this->_data['label_' . $label]) > 0);
	}

	function get_files_with_label($label)
	{
		return isset($this->_data['label_' . $label]) ? $this->_data['label_' . $label] : [];
	}

	public function get_name()
	{
		$ext = strlen($this->_data["extension"]) === 0 ? "" : ('.' . $this->_data["extension"]);
		return $this->_data["number"] . $this->_data["gap"] . $this->_data["name"] . $ext;
	}

}

class U3A_Audio_Folder extends U3A_Folder
{

	public function __construct($dirpath)
	{
		parent::__construct($dirpath, true, ["audio"	 => U3A_File_Utilities::$audio_extensions, "image"	 => U3A_File_Utilities::$image_extensions,
			"m3u"		 => ["m3u", "m3u8"], "cue"		 => ["cue"], "txt"		 => ["txt"], "pdf"		 => ["pdf"]]);
		$all_start_with_number = true;
		foreach ($this->_data["label_audio"] as $f)
		{
			if ($f->starts_with_number() === false)
			{
				$all_start_with_number = false;
			}
		}
		if ($all_start_with_number)
		{
			usort($this->_data["label_audio"], "U3A_File::compare");
		}
		$nm = explode(" - ", $this->_data["name"]);
		if (count($nm) > 1)
		{
			$this->_data["artist"] = trim($nm[0]);
		}
		else
		{
			$this->_data["artist"] = "";
		}
		$this->_data["va"] = strtolower($this->_data["artist"]) == "various artists";
	}

	function get_audio_files()
	{
		return $this->get_files_with_label("audio");
	}

	function get_image_files()
	{
		return $this->get_files_with_label("image");
	}

	function get_m3u_file()
	{
		return $this->get_files_with_label("m3u");
	}

	function get_cue_file()
	{
		return $this->get_files_with_label("cue");
	}

	function get_text_file()
	{
		return $this->get_files_with_label("txt");
	}

	function get_pdf_file()
	{
		return $this->get_files_with_label("pdf");
	}

	function contains_audio_files($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("audio");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_audio_files($recurse);
			}
		}
		return $ret1;
	}

	function contains_image_files($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("image");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_image_files($recurse);
			}
		}
		return $ret1;
	}

	function contains_m3u_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("m3u");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_m3u_file($recurse);
			}
		}
		return $ret1;
	}

	function contains_cue_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("cue");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_cue_file($recurse);
			}
		}
		return $ret1;
	}

	function contains_text_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("txt");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_text_file($recurse);
			}
		}
		return $ret1;
	}

	function contains_pdf_file($recurse = false)
	{
		$ret1 = $this->contains_files_with_label("pdf");
		if (!$ret1 && $recurse)
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret1 = $ret1 || $af->contains_pdf_file($recurse);
			}
		}
		return $ret1;
	}

	function subdirs_contain_audio_files()
	{
		$ret = false;
		foreach ($this->_data["subdirs"] as $af)
		{
			if ($af->contains_audio_files())
			{
				$ret = true;
				break;
			}
		}
		return $ret;
	}

	function get_all_image_files()
	{
		$ret = [];
		if ($this->contains_image_files())
		{
			foreach ($this->_data['label_image'] as $f)
			{
				array_push($ret, $f);
			}
		}
		if ($this->contains_audio_files())
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret = array_merge($ret, $af->get_all_image_files());
			}
		}
		else
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				if (!$af->contains_audio_files())
				{
					$ret = array_merge($ret, $af->get_all_image_files());
				}
			}
		}
		return $ret;
	}

	function get_all_pdf_files()
	{
		$ret = [];
		if ($this->contains_pdf_file())
		{
			foreach ($this->_data['label_pdf'] as $f)
			{
				array_push($ret, $f);
			}
		}
		if ($this->contains_audio_files())
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				$ret = array_merge($ret, $af->get_all_pdf_files());
			}
		}
		else
		{
			foreach ($this->_data["subdirs"] as $af)
			{
				if (!$af->contains_audio_files())
				{
					$ret = array_merge($ret, $af->get_all_pdf_files());
				}
			}
		}
		return $ret;
	}

}

class U3A_Thread
{

	private $_object;
	private $_forum_id;
	private $_contents;
	private $_key;
	private $_title;
	private $_date;
	private $_subthreads = [];

	public function __construct($obj, $forum_id, $keyfield = "id", $titlefield = "title", $contentsfield = "contents", $datefield = "date_posted")
	{
		$this->_object = $obj;
		$this->_forum_id = $forum_id;
		if ($obj)
		{
			$this->_key = $obj->$keyfield;
			$this->_date = $obj->$datefield;
			$this->_title = $obj->$titlefield;
			$this->_contents = $obj->$contentsfield;
		}
	}

	public function get_key()
	{
		return $this->_key;
	}

	public function get_title()
	{
		return $this->_title;
	}

	public function get_contents()
	{
		return $this->_contents;
	}

	public function get_object()
	{
		return $this->_object;
	}

	public function get_subthreads()
	{
		return $this->_subthreads;
	}

	public function append_subthread($sub)
	{
		if (is_a($sub, "U3A_Thread"))
		{
			array_push($this->_subthreads, $sub);
		}
		elseif (is_array($sub))
		{
			foreach ($sub as $s)
			{
				$this->append_subthread($s);
			}
		}
		else
		{
			array_push($this->_subthreads, new U3A_Thread($sub, $this->_forum_id));
		}
	}

	public function prepend_subthread($sub)
	{
		if (is_a($sub, "U3A_Thread"))
		{
			array_unshift($this->_subthreads, $sub);
		}
		elseif (is_array($sub))
		{
			foreach ($sub as $s)
			{
				$this->prepend_subthread($s);
			}
		}
		else
		{
			array_unshift($this->_subthreads, new U3A_Thread($sub, $this->_forum_id));
		}
	}

	public function count()
	{
		$ret = 1;
		foreach ($this->_subthreads as $sub)
		{
			$ret += $sub->count();
		}
		return ($ret);
	}

//	public function get_key_array(&$keyarray)
//	{
//		$keyarray[$this->get_key()] = $this;
//		foreach ($this->_subthreads as $sub)
//		{
//			$sub->get_key_array($keyarray);
//		}
//	}

	public function to_html($level = 0, $candelete = false)
	{
		$k = $this->_key;
		$id = $this->_forum_id;
		$divid = "u3a-thread-sub-$k";
		$c = $this->count() - 1;
		$ct = $c ? " ($c)" : "";
//		$t = new U3A_INPUT("text", null, "u3a-thread-title-$k", "u3a-arrow-only u3a-thread-title", $this->_title . $ct);
//		$t->add_attribute("readonly", "readonly");
		$t = new U3A_SPAN($this->_title . $ct, "u3a-thread-title-$k", "u3a-arrow-only u3a-thread-title");
		$btnup = new U3A_A('#', '<span class="dashicons dashicons-arrow-up-alt2"></span>', "u3a-thread-$k-up", "u3a-invisible", "u3a_toggle_up_down('u3a-thread-$k-', 'up', '$divid')");
		$btnup->add_tooltip("hide post");
		$btndown = new U3A_A("#", '<span class="dashicons dashicons-arrow-down-alt2"></span>', "u3a-thread-$k-down", "", "u3a_toggle_up_down('u3a-thread-$k-', 'down', '$divid')");
		$btndown->add_tooltip("show post");
		$btnx = null;
		if ($candelete)
		{
			$btnx = new U3A_A("#", '<span class="dashicons dashicons-no-alt"></span>', "u3a-thread-$k-del", "", "u3a_delete_thread('$id', '$k')");
			$btnx->add_tooltip("delete post");
		}
		$titlediv = new U3A_DIV([$t, $btnup, $btndown, $btnx], "u3a-thread-title-div-$k", "u3a-thread-title-div");
		$margin = "";
		if ($level > 0)
		{
			$mg = 10 * $level;
			$margin = " u3a-margin-left-$mg";
		}
		$contentsdiv = new U3A_DIV($this->get_contents(), "u3a-thread-contents-$k", "u3a-thread-contents-div");
		$rply = new U3A_A("#", 'reply', "u3a-thread-$k-reply", "u3a-forum-link", "u3a_get_forum_post('$id', '$k')");
		$sub = [];
		$nl = $level + 1;
		foreach ($this->_subthreads as $s)
		{
			$sub[] = $s->to_html($nl, $candelete);
		}
		$subdiv = new U3A_DIV([$contentsdiv, $rply, $sub], $divid, "u3a-thread-sub-div u3a-invisible$margin");
		return U3A_HTML::to_html([$titlediv, $subdiv]);
	}

}

class U3A_Forum
{

	private $_name;
	private $_id;
	private $_keep_days;
	private $_threads = [];
	private $_keyarray = [];
	private $_keyfield;
	private $_titlefield;
	private $_contentsfield;
	private $_datefield;
	private $_replytofield;

	public function __construct($name, $id, $keep_days, $keyfield = "id", $titlefield = "title", $contentsfield = "contents", $datefield = "date_posted", $replytofield = "reply_to")
	{
		$this->_keyfield = $keyfield;
		$this->_titlefield = $titlefield;
		$this->_contentsfield = $contentsfield;
		$this->_datefield = $datefield;
		$this->_name = $name;
		$this->_id = $id;
		$this->_keep_days = $keep_days;
		$this->_replytofield = $replytofield;
		$since = time() - $keep_days * U3A_Timestamp_Utilities::DAY1;
		$posts = U3A_Forum_Posts::get_posts_for_group($id, $since);
		for ($n = 0; $n < count($posts); $n++)
		{
			$this->_threads[$n] = new U3A_Thread($posts[$n], $id, $keyfield, $titlefield, $contentsfield, $datefield);
			$this->_keyarray[$posts[$n]->$keyfield] = $n;
		}
		foreach ($this->_threads as $th)
		{
			$post = $th->get_object();
			if ($post)
			{
				$rt = $post->$replytofield;
				if ($rt && array_key_exists($rt, $this->_keyarray))
				{
					$parent = $this->_threads[$this->_keyarray[$rt]];
					$parent->append_subthread($post);
				}
			}
		}
	}

	public function add($post)
	{
		$rtf = $this->_replytofield;
		$kf = $this->_keyfield;
		if (is_array($post))
		{
			$replies = [];
			foreach ($post as $p)
			{
				if ($p->$rtf)
				{

				}
				$this->add($p);
			}
		}
		else
		{
			$rt = $post->$rtf;
			$key = $post->$kf;
			$th = new U3A_Thread($post, $this->_id, $this->_keyfield, $this->_titlefield, $this->_contentsfield, $this->_datefield);
			$nthreads = count($this->_threads);
			$this->_keyarray[$key] = $nthreads;
			$this->_threads[$nthreads] = $th;
			if ($rt && array_key_exists($rt, $this->_keyarray))
			{
				$parent = $this->_threads[$this->_keyarray[$rt]];
				$parent->append_subthread($post);
			}
		}
	}

	public function to_html($candelete = false)
	{
		$id = $this->_id;
		$newthread = new U3A_A("#", 'say something', "u3a-forum-new-thread-$id", "u3a-forum-link", "u3a_get_forum_post('$id', 0)");
		$newthreaddiv = new U3A_DIV($newthread, "u3a-forum-new-thread-div-$id", "u3a-forum-new-thread-div");
		$contents = $newthreaddiv->to_html();
		$rtf = $this->_replytofield;
		foreach ($this->_threads as $thr)
		{
			$obj = $thr->get_object();
			if (!$obj->$rtf)
			{
				$contents .= $thr->to_html(0, $candelete);
			}
		}
		return $contents;
	}

}
