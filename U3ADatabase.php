<?php

require_once("u3a_mail.php");
require_once("u3a_base_classes.php");
require_once("project.php");
require_once 'u3a_config.php';

class U3A_Database_Table_information
{

	private static $instance;

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new U3A_Database_Table_information();
		}
		return self::$instance;
	}

	private $parameter_type_table;

	private function __construct()
	{
		$this->parameter_type_table = ["image"		 => "text", "icon"		 => "text", "URL"			 => "string", "file"		 => "string", "folder"		 => "string", "audio"		 => "string",
			"video"		 => "string", "email"		 => "string", "datetime"	 => "date_Time", "date"		 => "date_Time", "time"		 => "date_Time"];
	}

	public function get_tablename_from_classname($classname)
	{
		return strtolower($classname);
	}

	public function get_classname_from_tablename($tablename, $modifier = null)
	{
		return 'U3A_' . ucwords(substr($tablename, 4), '_');
	}

	public function get_where_array($tablename, $key)
	{
		return null;
	}

	public function get_select_array($tablename, $key)
	{
		$ret = null;
		switch ($key) {
			default:
				break;
		}
		return $ret;
	}

	public function get_number_array($tablename, $key)
	{
		return null;
	}

	public function get_user_object($tablename, $key)
	{
		return null;
	}

	public function get_user_post($tablename, $post, $exclude = null, $uploads = null, $datadir = null)
	{
		return null;
	}

	public function get_description($tablename, $columnname)
	{
		return null;
	}

	public function get_table_equivalent($tablename, $columnname)
	{
		$lp = strpos($columnname, '(');
		if ($lp > 0)
		{
			$columnname = substr($columnname, 0, $lp);
		}
		$ret = $columnname;
		$table = $this->$tablename;
		if (array_key_exists($columnname, $table))
		{
			$ret = $table[$columnname];
		}
		return $ret;
	}

}

class U3A_Database_Row extends U3A_Row
{

	public static function u3a_logged_in_member()
	{
		$current_user = self::u3a_real_member();
		if (U3A_Committee::is_webmanager($current_user))
		{
			$assume = get_option("assumed_identity", 0);
			if ($assume)
			{
				$mbr = U3A_Members::get_member_from_membership_number($assume);
				if ($mbr)
				{
					$mbr->set_real_member($current_user);
					$current_user = $mbr;
				}
			}
		}
//		write_log("current user ");
//		write_log($current_user);
		return $current_user;
	}

	public static function u3a_real_member()
	{
		$current_wp_user = wp_get_current_user();
		$current_user = null;
		if ($current_wp_user && $current_wp_user->ID)
		{
			$lg = $current_wp_user->user_login;
			if (is_numeric($lg))
			{
				$current_user = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $lg]);
			}
			else
			{
				$cttee = U3A_Row::load_single_object("U3A_Committee", ["login" => $lg]);
				if ($cttee)
				{
					$current_user = U3A_Row::load_single_object("U3A_Members", ["id" => $cttee->members_id]);
				}
			}
		}
		return $current_user;
	}

	public static function convert_smart_quotes($string)
	{
		$search = [// www.fileformat.info/info/unicode/<NUM>/ <NUM> = 2018
			"\xC2\xAB", // « (U+00AB) in UTF-8
			"\xC2\xBB", // » (U+00BB) in UTF-8
			"\xC3\xA9", // é U+00E9 in UTF-8
			"\xE2\x80\x98", // ‘ (U+2018) in UTF-8
			"\xE2\x80\x99", // ’ (U+2019) in UTF-8
			"\xE2\x80\x9A", // ‚ (U+201A) in UTF-8
			"\xE2\x80\x9B", // ‛ (U+201B) in UTF-8
			"\xE2\x80\x9C", // “ (U+201C) in UTF-8
			"\xE2\x80\x9D", // ” (U+201D) in UTF-8
			"\xE2\x80\x9E", // „ (U+201E) in UTF-8
			"\xE2\x80\x9F", // ‟ (U+201F) in UTF-8
			"\xE2\x80\xB9", // ‹ (U+2039) in UTF-8
			"\xE2\x80\xBA", // › (U+203A) in UTF-8
			"\xE2\x80\x93", // – (U+2013) in UTF-8
			"\xE2\x80\x94", // — (U+2014) in UTF-8
			"\xE2\x80\xA6"  // … (U+2026) in UTF-8
		];

		$replace = [
			"<<",
			">>",
			"e",
			"'",
			"'",
			"'",
			"'",
			'"',
			'"',
			'"',
			'"',
			"<",
			">",
			"-",
			"-",
			"..."
		];
//	$search = array(chr(145),
//		chr(146),
//		chr(147),
//		chr(148),
//		chr(151));
//
		//	$replace = array("'",
//		"'",
//		'"',
//		'"',
//		'-');

		return str_replace($search, $replace, $string);
	}

	public static function get_row_id($classname, $row)
	{
		$ret = $row;
		if (!is_numeric($row))
		{
			if (is_string($row))
			{
				$row1 = U3A_Row::load_single_object($classname, ["name" => $row]);
				$ret = $row1->id;
			}
			else
			{
				$ret = $row->id;
			}
		}
		return $ret;
	}

	public function get_table_information()
	{
		return U3A_Database_Table_information::get_instance();
	}

	public function filter_hash($hash)
	{
		return $hash;
	}

	public function get_name()
	{
		$ret = null;
		if (array_key_exists("name", $this->_data))
		{
			$ret = $this->_data['name'];
		}
		return $ret;
	}

	public function get_value()
	{
		$ret = null;
		if (array_key_exists("value", $this->_data))
		{
			$ret = $this->_data['value'];
		}
		elseif (array_key_exists("values_id", $this->_data) && array_key_exists("table_name", $this->_data))
		{
			$id = $this->_data['values_id'];
			if ($id > 0)
			{
				$tabname = $this->_data['table_name'];
				$tname = $tabname . "_values";
				$cname = U3A_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$ret = $obj->get_value();
				}
			}
		}
		if (($ret == null) && method_exists($this, "get_default_value"))
		{
			$ret = $this->get_default_value();
		}
		return $ret;
	}

	public function set_value($val)
	{
		if (array_key_exists("value", $this->_data))
		{
			$this->_data['value'] = $val;
			$this->save();
		}
		elseif (array_key_exists("values_id", $this->_data) && array_key_exists("table_name", $this->_data))
		{
			$id = $this->_data['values_id'];
			if ($id > 0)
			{
				$tabname = $this->_data['table_name'];
				$tname = $tabname . "_values";
				$cname = U3A_Database_Table_Information::get_instance()->get_classname_from_tablename($tname);
				if (class_exists($cname))
				{
					$obj = new $cname($id);
					$obj->set_value($val);
				}
			}
		}
	}

}

class U3A_Members extends U3A_Database_Row
{

	public static $status_values = [
		"Provisional",
		"Current",
		"Lapsed",
		"Retired",
		"Deceased"
	];
	public static $display_column_names = [
		"membership number"		 => "membership_number",
		"name"						 => [
			"default"					 => "full name",
			"title"						 => "title",
			"forename"					 => "forename",
			"first name"				 => "first_name",
			"initials"					 => "initials",
			"surname"					 => "surname",
			"full name"					 => "name",
			"formal name"				 => "formal_name",
			"initialed name"			 => "initialed_name",
			"titled, initialed name" => "titled_initialed_name"
		],
		"address"					 => [
			"default"	 => "address",
			"house"		 => "house",
			"address1"	 => "address1",
			"address2"	 => "address2",
			"address3"	 => "address3",
			"town"		 => "town",
			"county"		 => "county",
			"postcode"	 => "postcode",
			"address"	 => "full_address"
		],
		"email"						 => "email",
		"telephone"					 => "telephone",
		"mobile"						 => "mobile",
		"joined date"				 => "joined",
		"renewal date"				 => "renew",
		"in emergency contact"	 => "emergency_contact",
		"gift aid"					 => "gift_aid",
		"payment type"				 => "payment_type",
		"Third Age Matters"		 => "TAM",
		"newsletter"				 => "newsletter",
		"home U3A"					 => "affiliation",
		"notes"						 => "notes"
	];

	public static function get_member_from_membership_number($mnum)
	{
		$ret = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mnum]);
		return $ret;
	}

	/**
	 *
	 * @param type $ent can be id, name or email or object
	 * @return type U3A_Members object
	 */
	public static function get_member($ent)
	{
		$ret = $ent;
		if (is_numeric($ent))
		{
			$ret = U3A_Row::load_single_object("U3A_Members", ["id" => $ent]);
		}
		else if (is_string($ent))
		{
			if (strpos($ent, "@"))
			{
				$ret = U3A_Row::load_single_object("U3A_Members", ["email" => $ent]);
			}
			elseif (U3A_Utilities::starts_with($ent, "01") || U3A_Utilities::starts_with($ent, "02"))
			{
				$ret = U3A_Row::load_single_object("U3A_Members", ["telephone" => $ent]);
			}
			elseif (U3A_Utilities::starts_with($ent, "07"))
			{
				$ret = U3A_Row::load_single_object("U3A_Members", ["mobile" => $ent]);
			}
			else
			{
				$enta = explode(" ", $ent);
				$cnt = count($enta);
				if ($cnt === 1)
				{
					$ret = U3A_Row::load_single_object("U3A_Members", ["forename" => $ent]);
				}
				else
				{
					$ret = U3A_Row::load_single_object("U3A_Members", ["forename~" => $enta[0], "surname~" => $enta[$cnt - 1]]);
				}
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Members object
	 * @return type numeric
	 */
	public static function get_member_id($ent)
	{
		$ret = 0;
		if (is_numeric($ent))
		{
			$ret = $ent;
		}
		else
		{
			if (is_string($ent))
			{
				$enta = explode(" ", $ent);
				$cnt = count($enta);
				if ($cnt === 1)
				{
					$entity = U3A_Row::load_single_object("U3A_Members", ["forename" => $ent]);
				}
				else
				{
					$entity = U3A_Row::load_single_object("U3A_Members", ["forename~" => $enta[0], "surname~" => $enta[$cnt - 1]]);
				}
				if ($entity)
				{
					$ret = $entity->id;
				}
				else
				{
					$cm = U3A_Row::load_single_object("U3A_Committee", ["login" => $ent]);
					if (!$cm)
					{
						$cm = U3A_Row::load_single_object("U3A_Committee", ["role" => $ent]);
					}
					if ($cm)
					{
						$ret = $cm->members_id;
					}
				}
			}
			else if ($ent)
			{
				$ret = $ent->id;
			}
		}
		return intval($ret);
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Members object
	 * @return type numeric
	 */
	public static function get_member_name($ent)
	{
		$ret = $ent;
		if (!is_string($ent) || is_numeric($ent))
		{
			if (is_numeric($ent))
			{
				$entity = U3A_Row::load_single_object("U3A_Members", ["id" => $ent]);
				$ret = $entity->forename . " " . $entity->surname;
			}
			else
			{
				$ret = $ent->forename . " " . $ent->surname;
			}
		}
		return $ret;
	}

	public static function get_members($mbrs)
	{
		$ret = [];
		if (is_array($mbrs))
		{
			foreach ($mbrs as $mbr)
			{
				$ret[] = self::get_member($mbr);
			}
		}
		else
		{
			$ret[] = self::get_member($mbrs);
		}
		return $ret;
	}

	public static function get_all_members($where = null, $include_test = false)
	{
		if (!$include_test && !array_key_exists("class", $where))
		{
			$where["class<>"] = "System";
		}
		$mbrs = U3A_Row::load_array_of_objects("U3A_Members", $where, "surname,forename");
		return $mbrs["result"];
	}

	public static function get_email_address($mbr)
	{
		$ret = null;
//		write_log($mbr);
		if (is_string($mbr))
		{
			if (U3A_Utilities::is_email($mbr))
			{
				$ret = $mbr;
			}
			else
			{
				$p = strpos($mbr, ",");
				if ($p !== FALSE)
				{
					$mbrs = explode(',', $mbr);
					$emls = self::get_email_addresses($mbrs);
					$ret = implode(',', $emls);
				}
				else
				{
					$m = self::get_member($mbr);
					if ($m)
					{
						$ret = $m->email;
					}
				}
			}
		}
		else
		{
			$m = self::get_member($mbr);
			if ($m)
			{
				$ret = $m->email;
			}
		}
		return $ret;
	}

	public static function get_email_addresses($mbrs)
	{
		$ret = [];
		if (is_array($mbrs))
		{
			foreach ($mbrs as $mbr)
			{
				$eml = self::get_email_address($mbr);
				if ($eml)
				{
					$ret[] = $eml;
				}
			}
		}
		return $ret;
	}

	public static function is_system($mbr)
	{
		$ret = false;
		$member = self::get_member($mbr);
		if ($member)
		{
			$ret = $member->membership_number > 100000;
		}
		return $ret;
	}

	public static function compare($mbr1, $mbr2)
	{
		$ret = 0;
		if ($mbr1)
		{
			if ($mbr2)
			{
				$s1 = strtolower($mbr1->surname);
				$s2 = strtolower($mbr2->surname);
				$ret = $s1 < $s2 ? -1 : ($s1 > $s2 ? 1 : 0);
				if (!$ret)
				{
					$f1 = strtolower($mbr1->forename);
					$f2 = strtolower($mbr2->forename);
					$ret = $f1 < $f2 ? -1 : ($f1 > $f2 ? 1 : 0);
				}
			}
			else
			{
				$ret = 1;
			}
		}
		elseif ($mbr2)
		{
			$ret = -1;
		}
		return $ret;
	}

	public static function get_maximum_membership_number()
	{
		return U3A_Row::get_max("u3a_members", "membership_number", ["membership_number<" => 10000]);
	}

	public static function send_mail_to_all($from_member, $subject, $contents, $attachments = [], $use_no_reply = false, $use_reply_to = false)
	{
		$to_members = U3A_Row::load_array_of_objects("U3A_Members");
		return $this->send_mail_to_some($to_members["result"], $from_member, $subject, $contents, $attachments, $use_no_reply, $use_reply_to);
	}

	public static function send_mail_to_some($to_members, $from_member, $subject, $contents, $attachments = [], $use_no_reply = false, $use_reply_to = false)
	{
		$to_members1 = self::get_members($to_members);
		$fromm = self::get_member($from_member);
		$committee = U3A_Committee::get_preferred_committee_role($fromm->id);
//		write_log("members send");
//		write_log($to_members1);
//		write_log($fromm);
//		write_log($committee);
		$from = $committee ? $committee->get_full_email_address() : $fromm->get_full_email_address();
		$nr = "ShrewsburyU3A <" . U3A_Mail::get_no_reply_mailbox() . ">";
		$nsent = "";
		if (strpos($contents, "%%") === FALSE)
		{
			$bcc = [];
			foreach ($to_members1 as $mbr)
			{
				$bcc[] = $mbr->get_full_email_address();
			}
			$sent = U3A_Sent_Mail::send($fromm->id, $nr, $subject, $contents, null, $bcc, $use_no_reply ? $nr : $from, $use_reply_to ? $from : null, $attachments, true, false);
//			write_log("db sent");
//			write_log($sent);
			if (!$sent)
			{
				$nsent = " to members";
			}
		}
		else
		{
			foreach ($to_members1 as $member)
			{
				$mmcontents = U3A_Sent_Mail::mail_merge($contents, $member, 0, $committee);
				if (!self::send_mail_to_one($member, $fromm, $subject, $mmcontents["contents"], $attachments))
				{
					$nsent .= " " . U3A_Members::get_member_name($member);
				}
			}
			if ($nsent)
			{
				$nsent = " to" . $nsent;
			}
		}
		return $nsent;
	}

	public static function send_mail_to_one($to_member, $from_member, $subject, $contents, $attachments = [])
	{
		$to_member1 = self::get_member($to_member);
		$tom = $to_member1->get_full_email_address();
		$fromm = self::get_member($from_member);
		$from = $fromm->get_full_email_address();
		$nr = "ShrewsburyU3A <" . U3A_Mail::get_no_reply_mailbox() . ">";
		return U3A_Sent_Mail::send($fromm->id, $tom, $subject, $contents, null, null, $nr, $from, $attachments);
	}

	public static function can_renew($when1 = null)
	{
		if (!$when1)
		{
			$when = time();
		}
		elseif (is_string($when1) && !is_numeric($when1))
		{
			$when = strtotime($when1);
		}
		else
		{
			$when = $when1;
		}
		$when_year = U3A_Timestamp_Utilities::year($when);
		$subs_due = U3A_CONFIG::u3a_get_as_timestamp("SUBSCRIPTIONS_DUE", $when_year);
		$renew_from = U3A_CONFIG::u3a_get_as_timestamp("RENEWALS_FROM", $when_year);
		$renew_to = U3A_CONFIG::u3a_get_as_timestamp("MEMBERSHIP_LAPSES", $when_year);
		if ($renew_to < $renew_from)
		{
			$renew_to1 = U3A_CONFIG::u3a_get_as_timestamp("MEMBERSHIP_LAPSES", $when_year + 1);
			$renew_from1 = U3A_CONFIG::u3a_get_as_timestamp("RENEWALS_FROM", $when_year - 1);
			// different years
			$ret = (($when >= $renew_from1) && ($when <= $renew_to)) || (($when >= $renew_from) && ($when <= $renew_to1));
		}
		else
		{
			// both in same year
			$ret = ($when >= $renew_from) && ($when <= $renew_to);
		}
		$ret = ($when >= $renew_from) || ($when <= $renew_to);
		return $ret;
	}

	public static function get_next_renewal_date()
	{
		$now = time();
		$rnw = U3A_CONFIG::u3a_get_as_timestamp("SUBSCRIPTIONS_DUE", 0);
		if ($now > $rnw)
		{
			$rnw = U3A_CONFIG::u3a_get_as_timestamp("SUBSCRIPTIONS_DUE", 1);
		}
		return date('Y-m-d', $rnw);
	}

	private $_real_member = null;

	public function __construct($param = null)
	{
		parent::__construct("u3a_members", "id", $param, null, null, null);
		$this->_must_be_set_to_save[] = 'membership_number';
//		if ($this->_data)
//		{
//			$name = $this->_data["surname"];
//			if (isset($this->_data["forename"]) && $this->_data["forename"])
//			{
//				$name .= ", " . $this->_data["forename"];
//			}
//			$fname = $this->_data["forename"];
//			if (isset($this->_data["forename"]) && $this->_data["forename"])
//			{
//				$fname = $this->_data["forename"] . " " . $this->_data["surname"];
//			}
//			else
//			{
//				$fname = $this->_data["surname"];
//			}
//			$this->_data["name"] = $name;
//			$this->_data["fullname"] = $fname;
//		}
	}

	public function get_first_name()
	{
		$ret = "";
		if (isset($this->_data["known_as"]) && $this->_data["known_as"])
		{
			$ret = $this->_data["known_as"];
		}
		elseif (isset($this->_data["forename"]) && $this->_data["forename"])
		{
			$ret = $this->_data["forename"];
		}
		return $ret;
	}

	public function get_initials()
	{
		$ret = array_key_exists("initials", $this->_data) && $this->_data["initials"] ? $this->_data["initials"] : substr($this->forename, 0, 1);
		return $ret;
	}

	public function get_name()
	{
		return $this->get_first_name() . " " . $this->_data["surname"];
	}

	public function get_initialed_name()
	{
		return $this->get_initials() . " " . $this->_data["surname"];
	}

	public function get_titled_initials()
	{
		return $this->_data["title"] . " " . $this->get_initials();
	}

	public function get_titled_name()
	{
		return $this->_data["title"] . " " . $this->get_name();
	}

	public function get_titled_initialed_name()
	{
		return $this->_data["title"] . " " . $this->get_initialed_name();
	}

	public function get_formal_name()
	{
		$name = $this->_data["surname"];
		if (isset($this->_data["forename"]) && $this->_data["forename"])
		{
			$name .= ", " . $this->_data["forename"];
		}
		return $name;
	}

	public function get_phone()
	{
		$ret = "";
		if (isset($this->_data["mobile"]) && $this->_data["mobile"])
		{
			$ret = $this->_data["mobile"];
		}
		elseif (isset($this->_data["telephone"]) && $this->_data["telephone"])
		{
			$ret = $this->_data["telephone"];
		}
		return $ret;
	}

	public function get_full_email_address()
	{
		$ret = "";
		if (isset($this->_data["email"]) && $this->_data["email"])
		{
			$ret = $this->get_name() . " <" . $this->_data["email"] . ">";
		}
		return $ret;
	}

	public function get_gift_aid_text($val = null)
	{
		if (!$val)
		{
			$val = $this->_data["gift_aid"];
		}
		if ($val)
		{
			$ret = "on from " . date("d F Y", strtotime($val));
		}
		else
		{
			$ret = "off";
		}
		return $ret;
	}

	public function get_full_address($seperator = "\n")
	{
		$address = [];
		$cols = [
			"house",
			"address1",
			"address2",
			"address3",
			"town",
			"county",
			"postcode"
		];
		foreach ($cols as $col)
		{
			if (isset($this->_data[$col]) && $this->_data[$col])
			{
				$address[] = $this->_data[$col];
			}
		}
		return implode($seperator, $address);
	}

	public function get_mailing_list_member()
	{
		$ret = null;
		if (isset($this->_data["email"]) && $this->_data["email"])
		{
			$ret = new U3A_Mailing_List_Member($this->get_name(), $this->_data["email"], $this->_data["membership_number"]);
		}
		return $ret;
	}

	public function get_information()
	{
		$info = U3A_Row::load_single_object("U3A_Members_Information", ["members_id" => $this->_data["id"]]);
		$ret = null;
		if ($info)
		{
			$ret = stripslashes($info->information);
		}
		return $ret;
	}

	public function set_information($text)
	{
		if (is_array($text))
		{
			$text = implode("\n", $text);
		}
		$info = U3A_Row::load_single_object("U3A_Members_Information", ["members_id" => $this->_data["id"]]);
		if ($info)
		{
			$info->information = addslashes($info);
		}
		else
		{
			$info = new U3A_Members_Information(["members_id" => $this->_data["id"], "information" => addslashes($info)]);
		}
		$info->save();
	}

//	public function set_all($hash)
//	{
//		parent::set_all($hash);
//		$name = "";
//		$fname = "";
//		if (isset($this->_data["surname"]))
//		{
//			$name = $this->_data["surname"];
//			if (isset($this->_data["forename"]) && $this->_data["forename"])
//			{
//				$name .= ", " . $this->_data["forename"];
//			}
//		}
//		if (isset($this->_data["forename"]))
//		{
//			$fname = $this->_data["forename"];
//			if (isset($this->_data["forename"]) && $this->_data["forename"])
//			{
//				$fname = $this->_data["forename"] . " " . $this->_data["surname"];
//			}
//			else
//			{
//				$fname = $this->_data["surname"];
//			}
//			$this->_data["name"] = $name;
//			$this->_data["fullname"] = $fname;
//		}
//	}

	public function get_real_member()
	{
		if ($this->_real_member)
		{
			$ret = $this->_real_member;
		}
		else
		{
			$ret = $this;
		}
		return $ret;
	}

	public function set_real_member($mbr)
	{
		$this->_real_member = $mbr;
	}

	public function reset_real_member()
	{
		$this->set_real_member(null);
	}

	public function get_assumed_member()
	{
		$ret = null;
		if ($this->_real_member)
		{
			$ret = $this;
		}
		return $ret;
	}

	public function get_assumed_membership_number()
	{
		$ret = null;
		if ($this->_real_member)
		{
			$ret = $this->_data["membership_number"];
		}
		return $ret;
	}

	public function renew_membership()
	{
		$rnw = $this->_data["renew"];
		$rnwa = explode('-', $rnw);
		$rnwa[0] = intval($rnwa[0]) + 1;
		$this->_data["renew"] = implode('-', $rnwa);
		$this->save();
	}

	public function shares_group_with($mbr)
	{
		$members_id = self::get_member_id($mbr);
		return U3A_Group_Members::share_a_group($members_id, $this->_data["id"]);
	}

}

class U3A_Member_Table_Column_Correspondence extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_member_table_column_correspondence", "id", $param, null, null, null);
	}

}

class U3A_Groups extends U3A_Database_Row
{

	const NO_MEMBER_CATEGORIES = 0;
	const MEMBER_DOCUMENT_CATEGORIES = 1;
	const MEMBER_IMAGE_ALBUMS = 2;
	const MEMBER_BOTH = 3;

	/**
	 *
	 * @param type $ent can be id, name or email or object
	 * @return type U3A_Members object
	 */
	public static function get_group($ent)
	{
		$ret = $ent;
		if (is_numeric($ent))
		{
			$ret = U3A_Row::load_single_object("U3A_Groups", ["id" => $ent]);
		}
		else if (is_string($ent))
		{
			$ret = U3A_Row::load_single_object("U3A_Groups", ["name" => addslashes($ent)]);
		}
		return $ret;
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Groups object
	 * @return type numeric
	 */
	public static function get_group_id($ent)
	{
		$ret = $ent;
		if (!is_numeric($ent))
		{
			if (is_string($ent))
			{
				$where = ["name" => $ent];
				$entity = U3A_Row::load_single_object("U3A_Groups", $where);
				$ret = $entity->id;
			}
			else if ($ent != null)
			{
				$ret = $ent->id;
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Groups object
	 * @return type numeric
	 */
	public static function get_group_name($ent)
	{
		$ret = $ent;
		if (!is_string($ent) || is_numeric($ent))
		{
			if (is_numeric($ent))
			{
				$entity = U3A_Row::load_single_object("U3A_Groups", ["id" => $ent]);
				$ret = $entity->name;
			}
			else
			{
				$ret = $ent->name;
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Groups object
	 * @return type string
	 */
	public static function get_group_value($ent, $column)
	{
		$grp = self::get_group($ent);
//		var_dump($grp);
		$ret = null;
		if ($grp && array_key_exists($column, $grp->_data))
		{
			$ret = $grp->$column;
		}
		return $ret;
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Groups object
	 * @param columns an array of column names
	 * @return type array
	 */
	public static function get_group_values($ent, $columns)
	{
		$grp = self::get_group($ent);
//		var_dump($grp);
		$ret = [];
		if ($grp)
		{
			for ($n = 0; $n < count($columns); $n++)
			{
				$column = $columns[$n];
				if (array_key_exists($column, $grp->_data))
				{
					$ret[$n] = $grp->$column;
				}
			}
		}
		return $ret;
	}

//	public static function get_coordinators($grp)
//	{
//		$groups_id = self::get_group_id($grp);
//		$coords = U3A_Row::load_array_of_objects("U3A_Group_Members", ["groups_id" => $groups_id, "status" => [1, 3]]);
//		$ret = null;
//		if ($coords && $coords["total_number_of_rows"])
//		{
//			$ret = $coords["result"];
//		}
//		return $ret;
//	}

	public static function get_coordinator_ids($grp)
	{
		$groups_id = self::get_group_id($grp);
		return U3A_Row::load_column("u3a_group_members", "members_id", ["groups_id" => $groups_id, "status" => [1, 3]]);
	}

	public static function get_coordinators($grp = null)
	{
		$ret = [];
		if ($grp)
		{
			$coord_ids = self::get_coordinator_ids($grp);
			foreach ($coord_ids as $cid)
			{
				$ret[] = U3A_Members::get_member($cid);
			}
		}
		else
		{
			$sql = "SELECT u3a_members.* FROM u3a_members JOIN u3a_group_members ON u3a_members.id = u3a_group_members.members_id WHERE u3a_group_members.status = 1 OR u3a_group_members.status = 3";
			$list = Project_Details::get_db()->loadList($sql);
			$num = count($list);
			for ($n = 0; $n < $num; $n++)
			{
				$obj = new $objclass();
				$obj->set_all($list[$n]);
				$ret[] = $obj;
			}
		}
		return $ret;
	}

	public static function get_all_coordinators_details()
	{
		$ret = [];
		$sql = "SELECT u3a_members.id AS id, u3a_members.forename AS forename, u3a_members.surname AS surname, u3a_members.email AS email, u3a_members.membership_number AS membership_number,"
		  . " u3a_groups.name AS group_name FROM u3a_members INNER JOIN u3a_group_members ON u3a_members.id = u3a_group_members.members_id INNER JOIN u3a_groups ON u3a_group_members.groups_id = u3a_groups.id"
		  . " WHERE u3a_group_members.status = 1 OR u3a_group_members.status = 3";
		$ret = Project_Details::get_db()->loadList($sql);
//		$num = count($list);
//		for ($n = 0; $n < $num; $n++)
//		{
//			$obj = new $objclass();
//			$obj->set_all($list[$n]);
//			$ret[] = $obj;
//		}
		return $ret;
	}

	public static function meeting_time_to_string($meet1)
	{
//		$val = [
//			"ntimes"	 => 1,
//			"every"	 => "month",
//			"onweek"	 => [
//				[
//					"ord"	 => 0 (every), 1 (alternate)
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
		$ret = "";
		if ($meet1)
		{
			if (is_object($meet1))
			{
				$meet = get_object_vars($meet1);
			}
			elseif (is_array($meet1))
			{
				$meet = $meet1;
			}
//			var_dump($meet);
			if (array_key_exists("ntimes", $meet))
			{
				$nt = $meet["ntimes"];
				if (array_key_exists("every", $meet) && (array_key_exists("onweek", $meet) || array_key_exists("onmonth", $meet)))
				{
					$every = $meet["every"];
					$ret = U3A_Utilities::number_to_adverb($nt) . " every " . $every . " on ";
					if ($every === "week")
					{
						$ret .= U3A_Utilities::days_to_string($meet["onweek"], $nt);
					}
					elseif ($every === "month")
					{
						$ret .= U3A_Utilities::ordinal_days_to_string($meet["onmonth"], $nt);
					}
				}
			}
		}
		return $ret;
	}

	public static function convert_meets_when($when, $from, $to)
	{
		$notes = "";
		$meets_when = "";
		$meets_when_as_string = "";
		if (!trim($when))
		{
			$when = "Unknown";
		}
		if (U3A_Utilities::starts_with($when, '{') || U3A_Utilities::starts_with($when, '['))
		{
			$meets_when = $when;
			$meets_when_as_string = self::meeting_time_to_string(json_decode(stripslashes($when)));
		}
		else
		{
			$val = [];
			$leftp = strpos($when, "(");
			if ($leftp !== FALSE)
			{
				$rightp = strpos($when, ")", $leftp);
				if ($rightp > $leftp)
				{
					$notes = "meeting times: " . substr($when, $leftp + 1, $rightp - $leftp - 1);
				}
				else
				{
					$notes = "meeting times: " . substr($when, $leftp + 1);
				}
				$when = trim(substr($when, 0, $leftp));
			}
			$whenbits = explode(" ", strtolower($when));
			if (count($whenbits) === 2)
			{
				$val["ntimes"] = 1;
				if ($whenbits[0] === "every")
				{
					$val["every"] = "week";
					$val["onmonth"] = [];
					$val["onweek"] = [[
						 "ord"	 => 0,
						 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
						 "from" => $from,
						 "to"	 => $to
					]];
				}
				elseif ($whenbits[0] === "alt" || $whenbits[0] === "alternate")
				{
					$val["every"] = "week";
					$val["onmonth"] = [];
					$val["onweek"] = [[
						 "ord"	 => 1,
						 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
						 "from" => $from,
						 "to"	 => $to
					]];
				}
				elseif (is_numeric($whenbits[0]))
				{
					$val["every"] = "month";
					$val["onweek"] = [];
					$val["onmonth"] = [[
						 "ord"	 => intval($whenbits[0]),
						 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
						 "from" => $from,
						 "to"	 => $to
					]];
				}
				elseif (array_search($whenbits[0], U3A_Utilities::$first_few_ordinals) !== FALSE)
				{
					$val["every"] = "month";
					$val["onweek"] = [];
					$val["onmonth"] = [[
						 "ord"	 => array_search($whenbits[0], U3A_Utilities::$first_few_ordinals) + 1,
						 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
						 "from" => $from,
						 "to"	 => $to
					]];
				}
				else
				{
					$notes = "meeting times: " . $when;
				}
			}
			elseif (count($whenbits) === 4)
			{
				if ($whenbits[1] === '&' || $whenbits[1] === 'and')
				{
					$val["ntimes"] = 2;
					if (is_numeric($whenbits[0]) && is_numeric($whenbits[2]))
					{
						$val["every"] = "month";
						$val["onweek"] = [];
						$val["onmonth"] = [[
							 "ord"	 => intval($whenbits[0]),
							 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[3]),
							 "from" => $from,
							 "to"	 => $to
							],
							[
								"ord"	 => intval($whenbits[2]),
								"day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[3]),
								"from" => $from,
								"to"	 => $to
							]
						];
					}
					else
					{
						$notes = "meeting times: " . $when;
					}
				}
				elseif (($whenbits[0] === "alt" || $whenbits[0] === "alternate") && ($whenbits[2] === '&' || $whenbits[2] === 'and'))
				{
					$val["ntimes"] = 1;
					$val["every"] = "week";
					$val["onmonth"] = [];
					$val["onweek"] = [[
						 "ord"	 => 1,
						 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
						 "from" => $from,
						 "to"	 => $to
						],
						[
							"ord"	 => 2,
							"day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[3]),
							"from" => $from,
							"to"	 => $to
					]];
				}
				else
				{
					$notes = "meeting times: " . $when;
				}
			}
			elseif (count($whenbits) === 5)
			{
				if ($whenbits[2] === '&' || $whenbits[2] === 'and')
				{
					$val["ntimes"] = 2;
					if (is_numeric($whenbits[0]) && is_numeric($whenbits[3]))
					{
						$val["every"] = "month";
						$val["onweek"] = [];
						$val["onmonth"] = [[
							 "ord"	 => intval($whenbits[0]),
							 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
							 "from" => $from,
							 "to"	 => $to
							],
							[
								"ord"	 => intval($whenbits[3]),
								"day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[4]),
								"from" => $from,
								"to"	 => $to
							]
						];
					}
					elseif (array_search($whenbits[0], U3A_Utilities::$first_few_ordinals) !== FALSE && array_search($whenbits[3], U3A_Utilities::$first_few_ordinals) !== FALSE)
					{
						$val["every"] = "month";
						$val["onweek"] = [];
						$val["onmonth"] = [[
							 "ord"	 => array_search($whenbits[0], U3A_Utilities::$first_few_ordinals),
							 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[1]),
							 "from" => $from,
							 "to"	 => $to
							],
							[
								"ord"	 => array_search($whenbits[3], U3A_Utilities::$first_few_ordinals),
								"day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[4]),
								"from" => $from,
								"to"	 => $to
							]
						];
					}
					else
					{
						$notes = "meeting times: " . $when;
					}
				}
				else
				{
					$notes = "meeting times: " . $when;
				}
			}
			elseif (count($whenbits) === 6)
			{
				if (($whenbits[1] === '&' || $whenbits[1] === 'and') && ($whenbits[3] === '&' || $whenbits[3] === 'and'))
				{
					$val["ntimes"] = 3;
					if (is_numeric($whenbits[0]) && is_numeric($whenbits[2]) && is_numeric($whenbits[4]))
					{
						$val["every"] = "month";
						$val["onweek"] = [];
						$val["onmonth"] = [[
							 "ord"	 => intval($whenbits[0]),
							 "day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[5]),
							 "from" => $from,
							 "to"	 => $to
							],
							[
								"ord"	 => intval($whenbits[2]),
								"day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[5]),
								"from" => $from,
								"to"	 => $to
							],
							[
								"ord"	 => intval($whenbits[4]),
								"day"	 => U3A_Timestamp_Utilities::get_day_of_week($whenbits[5]),
								"from" => $from,
								"to"	 => $to
							]
						];
					}
					else
					{
						$notes = "meeting times: " . $when;
					}
				}
				else
				{
					$notes = "meeting times: " . $when;
				}
			}
			else
			{
				$notes = "meeting times: " . $when;
			}
			$meets_when = json_encode($val);
			$meets_when_as_string = self::meeting_time_to_string($val);
			$notes .= " (original meeting times given as [$when/$from/$to])";
//			var_dump($val);
		}
		return ["when" => $meets_when, "whenfordb" => $meets_when_as_string, "notes" => $notes];
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_groups", "id", $param, null, null, null);
		$this->_must_be_set_to_save[] = 'name';
	}

	public function get_meets_when()
	{
		$meets_when = $this->_data["meets_when"];
//		print ("MEETS WHEN " . $meets_when . "\n");
		if (U3A_Utilities::starts_with($meets_when, "{") || U3A_Utilities::starts_with($meets_when, "["))
		{
			$ret = self::meeting_time_to_string(json_decode(stripslashes($meets_when)));
		}
		else
		{
			$ret = $meets_when;
		}
		return $ret;
	}

	public function get_venue_name()
	{
		$ret = null;
		$venues_id = $this->_data["venue"];
		if ($venues_id)
		{
			$venue = U3A_Row::load_single_object("U3A_Venues", ["id" => $venues_id]);
			if ($venue)
			{
				$ret = $venue->venue;
			}
		}
		return $ret;
	}

	public function send_mail_to_all($from_member, $subject, $contents, $attachments = [], $use_cc = false, $use_no_reply = true, $use_reply_to = true)
	{
		$to_members = U3A_Group_Members::get_members_in_group($this->_data["id"]);
		return $this->send_mail_to_some($to_members, $from_member, $subject, $contents, $attachments, $use_cc, $use_no_reply, $use_reply_to);
	}

	public function send_mail_to_some($to_members, $from_member, $subject, $contents, $attachments = [], $use_cc = false, $use_no_reply = true, $use_reply_to = true)
	{
		$to_members1 = U3A_Members::get_members($to_members);
		$fromm = U3A_Members::get_member($from_member);
		$from = $fromm->get_full_email_address();
		$nr = $this->_data["name"] . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
		$nsent = "";
		if (U3A_Group_Members::is_coordinator($fromm, $this))
		{
			$committee = 0;
		}
		else
		{
			$committee = U3A_Committee::get_preferred_committee_role($fromm);
			if ($committee)
			{
				$from = $committee->get_full_email_address();
			}
		}
		if (strpos($contents, "%%") === FALSE)
		{
			$to = [];
			foreach ($to_members1 as $mbr)
			{
				$to[] = $mbr->get_full_email_address();
			}
			if ($use_cc)
			{
				$cc = array_unique($to);
				$bcc = null;
			}
			else
			{
				$bcc = array_unique($to);
				$cc = null;
			}
			//send($sender_id, $to, $subject1, $contents, $cc = null, $bcc = null, $from = null, $reply_to = null, $attachments = null, $html = true, $committee = false)
			$sent = U3A_Sent_Mail::send($fromm->id, $nr, $subject, $contents, $cc, $bcc, $use_no_reply ? $nr : $from, $use_reply_to ? $from : null, $attachments, true, false);
			if (!$sent)
			{
				$nsent = " to group";
			}
		}
		else
		{
			foreach ($to_members1 as $member)
			{
//				write_log($contents);
				$mmcontents = U3A_Sent_Mail::mail_merge($contents, $member, 0, $committee);
//				write_log($mmcontents);
				if (!$this->send_mail_to_one($member, $fromm, $subject, $mmcontents["contents"], $attachments))
				{
					$nsent .= " " . U3A_Members::get_member_name($m);
				}
			}
			if ($nsent)
			{
				$nsent = " to" . $nsent;
			}
		}
		return $nsent;
	}

	public function send_mail_to_one($to_member, $from_member, $subject, $contents, $attachments = [])
	{
		$to_member1 = U3A_Members::get_member($to_member);
		$to = $to_member1->get_full_email_address();
		$fromm = U3A_Members::get_member($from_member);
		$from = $fromm->get_full_email_address();
		$nr = $this->_data["name"] . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
		return U3A_Sent_Mail::send($fromm->id, $to, $subject, $contents, null, null, $nr, $from, $attachments, true, false);
	}

	public function get_number_of_members()
	{
		$sql = "SELECT COUNT(*) FROM u3a_group_members WHERE groups_id = " . $this->_data["id"] . " AND status <> 4";
		$ret = Project_Details::get_db()->query($sql);
//		write_log($ret);
		return $ret ? intval(array_values(get_object_vars($ret[0]))[0]) : 0;
	}

	public function set_mailing_list($name)
	{
		if (!isset($this->_data["mailing_list"]) || !$this->_data["mailing_list"])
		{
			$this->_data["mailing_list"] = $name;
			$this->save();
		}
	}

	public function get_mailing_list()
	{
		$ret = null;
		$address = $this->get_mailing_list_address();
		$mailer = U3A_Mail::get_the_mailer();
		if ($address && $mailer->mailing_list_exists($address))
		{
			$nm = $this->_data["name"];
			$cfg = U3A_CONFIG::get_the_config();
			$u3a = $cfg->U3ANAME;
			$ret = new U3A_Mailing_List($nm, $address, "The $nm group at $u3a U3A.");
		}
		return $ret;
	}

	public function get_mailing_list_name()
	{
		if (isset($this->_data["mailing_list"]) && $this->_data["mailing_list"])
		{
			$ret = $this->_data["mailing_list"];
		}
		else
		{
			$ret = str_replace(" ", ".", strtolower($this->_data["name"]));
		}
		return $ret;
	}

	public function get_mailing_list_address()
	{
		$ret = null;
		$ml = $this->has_mailing_list();
		if ($ml)
		{
			$config = U3A_CONFIG::get_the_config();
			$ret = $ml . "@" . $config->MAILING_LIST_DOMAIN;
		}
		return $ret;
	}

	public function has_mailing_list()
	{
		if (isset($this->_data["mailing_list"]) && $this->_data["mailing_list"])
		{
			$ret = $this->_data["mailing_list"];
		}
		else
		{
			$ret = "";
		}
		return $ret;
	}

	public function members_can_create_document_categories()
	{
		$cando = intval($this->_data["personal_document_categories"]);
		return $cando === self::MEMBER_DOCUMENT_CATEGORIES || $cando === self::MEMBER_BOTH;
	}

	public function members_can_create_image_albums()
	{
		$cando = intval($this->_data["personal_document_categories"]);
		return $cando === self::MEMBER_IMAGE_ALBUMS || $cando === self::MEMBER_BOTH;
	}

	public function has_u3a_docs_or_images()
	{
		$ndocs = U3A_Row::count_rows("U3A_Documents", ["groups_id" => $this->_data["id"], "visibility>" => U3A_Documents::VISIBILITY_GROUP]);
		return $ndocs > 0;
	}

	public function has_public_docs_or_images()
	{
		$ndocs = U3A_Row::count_rows("U3A_Documents", ["groups_id" => $this->_data["id"], "visibility>" => U3A_Documents::VISIBILITY_U3A]);
		return $ndocs > 0;
	}

	public function has_u3a_docs()
	{
		$ndocs = U3A_Row::count_rows("U3A_Documents", ["groups_id" => $this->_data["id"], "document_type" => U3A_Documents::GROUP_DOCUMENT_TYPE, "visibility>" => U3A_Documents::VISIBILITY_GROUP]);
		return $ndocs > 0;
	}

	public function has_public_docs()
	{
		$ndocs = U3A_Row::count_rows("U3A_Documents", ["groups_id" => $this->_data["id"], "document_type" => U3A_Documents::GROUP_DOCUMENT_TYPE, "visibility>" => U3A_Documents::VISIBILITY_U3A]);
		return $ndocs > 0;
	}

	public function has_u3a_images()
	{
		$ndocs = U3A_Row::count_rows("U3A_Documents", ["groups_id" => $this->_data["id"], "document_type" => U3A_Documents::GROUP_IMAGE_TYPE, "visibility>" => U3A_Documents::VISIBILITY_GROUP]);
		return $ndocs > 0;
	}

	public function has_public_images()
	{
		$ndocs = U3A_Row::count_rows("U3A_Documents", ["groups_id" => $this->_data["id"], "document_type" => U3A_Documents::GROUP_IMAGE_TYPE, "visibility>" => U3A_Documents::VISIBILITY_U3A]);
		return $ndocs > 0;
	}

}

class U3A_Group_Members extends U3A_Database_Row
{
	/*
	 * status 0 - ordinary member
	 * 1 -coordinator
	 * 2 - contact
	 * 3 - coordinator and contact
	 * 4 - waiting
	 */

	const MEMBER = 0;
	const COORDINATOR = 1;
	const CONTACT = 2;
	const CONTACT_COORDINATOR = 3;
	const WAITING = 4;

	public static function get_first_id($grp, $sorted = false)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$w = self::WAITING;
		if ($sorted)
		{
			$sql = "SELECT u3a_members.id FROM u3a_members JOIN u3a_group_members ON u3a_group_members.members_id = u3a_members.id WHERE u3a_group_members.groups_id = $groups_id AND u3a_group_members.status <> $w ORDER BY u3a_members.surname, u3a_members.forename LIMIT 1";
		}
		else
		{
			$sql = "SELECT u3a_members.id FROM u3a_members JOIN u3a_group_members ON u3a_group_members.members_id = u3a_members.id WHERE u3a_group_members.groups_id = $groups_id AND u3a_group_members.status <> $w LIMIT 1";
		}
		return Project_Details::get_db()->loadResult($sql);
	}

	public static function get_members_in_group($grp, $sorted = false)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$w = self::WAITING;
		if ($sorted)
		{
			$sql = "SELECT u3a_members.* FROM u3a_members JOIN u3a_group_members ON u3a_group_members.members_id = u3a_members.id WHERE u3a_group_members.groups_id = $groups_id AND u3a_group_members.status <> $w ORDER BY u3a_members.surname, u3a_members.forename";
		}
		else
		{
			$sql = "SELECT u3a_members.* FROM u3a_members JOIN u3a_group_members ON u3a_group_members.members_id = u3a_members.id WHERE u3a_group_members.groups_id = $groups_id AND u3a_group_members.status <> $w";
		}
		$mbrs_hashlist = Project_Details::get_db()->loadList($sql);
		$mbrs = [];
		foreach ($mbrs_hashlist as $mbhl)
		{
			$mbr = new U3A_Members();
			$mbr->set_all($mbhl);
			$mbrs[] = $mbr;
		}
		return $mbrs;
	}

	public static function get_waiting_list($grp, $sorted = false)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$w = self::WAITING;
		if ($sorted)
		{
			$sql = "SELECT u3a_members.* FROM u3a_members JOIN u3a_group_members ON u3a_group_members.members_id = u3a_members.id WHERE u3a_group_members.groups_id = $groups_id AND u3a_group_members.status = $w ORDER BY u3a_members.surname, u3a_members.forename";
		}
		else
		{
			$sql = "SELECT u3a_members.* FROM u3a_members JOIN u3a_group_members ON u3a_group_members.members_id = u3a_members.id WHERE u3a_group_members.groups_id = $groups_id AND u3a_group_members.status = $w";
		}
		$mbrs_hashlist = Project_Details::get_db()->loadList($sql);
		$mbrs = [];
		foreach ($mbrs_hashlist as $mbhl)
		{
			$mbr = new U3A_Members();
			$mbr->set_all($mbhl);
			$mbrs[] = $mbr;
		}
		return $mbrs;
	}

	public static function get_mailing_list_members($grp)
	{
		$members = self::get_members_in_group($grp);
		$ret = [];
		foreach ($members as $mbr)
		{
			$email = U3A_Members::get_email_address($mbr);
			if ($email)
			{
				$ret[$email] = $mbr->get_mailing_list_member();
			}
		}
		return $ret;
	}

	public static function get_groups_for_member($mbr, $sorted = true)
	{
		$w = self::WAITING;
		$members_id = U3A_Members::get_member_id($mbr);
		if ($sorted)
		{
			$sql = "SELECT u3a_groups.* FROM u3a_groups JOIN u3a_group_members ON u3a_group_members.groups_id = u3a_groups.id WHERE u3a_group_members.members_id = $members_id AND u3a_group_members.status <> $w ORDER BY u3a_groups.name";
		}
		else
		{
			$sql = "SELECT u3a_groups.* FROM u3a_groups JOIN u3a_group_members ON u3a_group_members.groups_id = u3a_groups.id WHERE u3a_group_members.members_id = $members_id AND u3a_group_members.status <> $w";
		}
		$grps_hashlist = Project_Details::get_db()->loadList($sql);
		$grps = [];
		foreach ($grps_hashlist as $gphl)
		{
			$grp = new U3A_Groups();
			$grp->set_all($gphl);
			$grps[] = $grp;
		}
		return $grps;
	}

	public static function is_a_coordinator($member)
	{
		$ret = false;
		$members_id = U3A_Members::get_member_id($member);
		if ($members_id)
		{
			$grpmbr = U3A_Row::load_single_object("U3A_Group_Members", ["members_id" => $members_id, "status" => [1, 3]]);
			$ret = $grpmbr != null;
		}
		return $ret;
	}

	public static function is_coordinator($member, $grp = null)
	{
		$ret = false;
		if ($grp)
		{
			$groups_id = U3A_Groups::get_group_id($grp);
			$members_id = U3A_Members::get_member_id($member);
			$grpmbr = U3A_Row::load_single_object("U3A_Group_Members", ["groups_id" => $groups_id, "members_id" => $members_id]);
			if ($grpmbr)
			{
				$ret = $grpmbr->status == 1 || $grpmbr->status == 3;
			}
		}
//		else
//		{
//			$ret = U3A_Permissions::has_permission("upload_newsletter", $member);
//		}
		return $ret;
	}

	public static function is_contact($member, $grp)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$members_id = U3A_Members::get_member_id($member);
		$ret = false;
		$grpmbr = U3A_Row::load_single_object("U3A_Group_Members", ["groups_id" => $groups_id, "members_id" => $members_id]);
		if ($grpmbr)
		{
			$ret = $grpmbr->status == 2 || $grpmbr->status == 1;
		}
		return $ret;
	}

	public static function is_member($member, $grp)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$members_id = U3A_Members::get_member_id($member);
		$ret = false;
		$grpmbr = U3A_Row::load_single_object("U3A_Group_Members", ["groups_id" => $groups_id, "members_id" => $members_id, "status<>" => self::WAITING]);
		if ($grpmbr)
		{
			$ret = true;
		}
		return $ret;
	}

	public static function remove_from_group($mbr, $grp)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$groups_id = U3A_Groups::get_group_id($grp);
		U3A_Row::delete_rows("u3a_group_members", ["members_id" => $members_id, "groups_id" => $groups_id]);
	}

	public static function share_a_group($members_id1, $members_id2)
	{
		$sql = "SELECT COUNT(*) FROM u3a_group_members g1 JOIN u3a_group_members g2 ON g1.groups_id = g2.groups_id WHERE g1.members_id = $members_id1 AND g2.members_id = $members_id2";
		$ret = Project_Details::get_db()->query($sql);
//		write_log($ret);
		return $ret ? intval(array_values(get_object_vars($ret[0]))[0]) : 0;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_group_members", "id", $param, null, null, null);
	}

}

class U3A_Venues extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_venues", "id", $param, null, null, null);
	}

}

class U3A_Committee extends U3A_Database_Row
{

	public static function get_all_members()
	{
		$sql = "SELECT u3a_committee.id AS id, u3a_committee.role AS role, u3a_committee.members_id AS members_id, u3a_members.forename AS forename, u3a_members.surname AS surname, u3a_committee.email AS email FROM " .
		  "u3a_committee JOIN u3a_members ON u3a_committee.members_id = u3a_members.id ORDER BY u3a_committee.role";
		return Project_Details::get_db()->loadList($sql);
	}

	public static function get_committee_from_id($id)
	{
		$ret = U3A_Row::load_single_object("U3A_Committee", ["id" => $id]);
		return $ret;
	}

	public static function get_committee($ent, $include_unfilled = false)
	{
		$ret = null;
		if (is_numeric($ent))
		{
			$ret = U3A_Row::load_single_object("U3A_Committee", ["members_id" => $ent]);
		}
		else if (is_string($ent))
		{
			$where = ["role" => $ent];
			if (!$include_unfilled)
			{
				$where["members_id>"] = 0;
			}
			$ret = U3A_Row::load_single_object("U3A_Committee", $where);
			if (!$ret)
			{
				$where = ["login" => $ent];
				if (!$include_unfilled)
				{
					$where["members_id>"] = 0;
				}
				$ret = U3A_Row::load_single_object("U3A_Committee", ["login" => $ent]);
			}
		}
		else if (is_a($ent, "U3A_Committee"))
		{
			$ret = $ent;
		}
		else if (is_a($ent, "U3A_Members"))
		{
			$ret = U3A_Row::load_single_object("U3A_Committee", ["members_id" => $ent->id]);
		}
		return $ret;
	}

	public static function get_committee_member($ent)
	{
		$ret = null;
		$cmttee = self::get_committee($ent);
		if ($cmttee)
		{
			$ret = U3A_Members::get_member($cmttee->members_id);
		}
		return $ret;
	}

	public static function get_committee_role($ent)
	{
		$ret = null;
		$crole = self::get_committee($ent);
		if ($crole)
		{
			$ret = $crole->role;
		}
		return $ret;
	}

	public static function get_committee_id($ent)
	{
		$ret = 0;
		$crole = self::get_committee($ent);
		if ($crole)
		{
			$ret = intval($crole->id);
		}
		return $ret;
	}

	public static function get_committee_ids_for_member($members_id)
	{
		return U3A_Row::load_column("u3a_committee", "id", ["members_id" => $members_id]);
	}

	public static function get_committee_for_member($member)
	{
		$mbr = U3A_Members::get_member($member);
		if (($mbr->membership_number > 990000) && ($mbr->forename === "Committee") && ($mbr->surname === "System"))
		{
			$cmbr = new U3A_System_Test_Committee([
				"role"		 => "System Committee Member",
				"login"		 => "systemcommitteemember",
				"members_id" => $mbr->id,
				"email"		 => "system.committee@" . U3A_Information::u3a_get_domain_name()
			]);
			$ret = [$cmbr];
//				write_log("ret", $ret);
		}
		else
		{
			$cm = U3A_Row::load_array_of_objects("U3A_Committee", ["members_id" => $mbr->id]);
			$ret = $cm["result"];
		}
		return $ret;
	}

	public static function get_webmanager()
	{
		return U3A_Row::load_single_object("U3A_Committee", ["login" => "webmanager"]);
	}

	public static function get_membership_secretary()
	{
		return U3A_Row::load_single_object("U3A_Committee", ["login" => "membershipsecretary"]);
	}

	public static function get_treasurer()
	{
		return U3A_Row::load_single_object("U3A_Committee", ["login" => "treasurer"]);
	}

	public static function get_chairperson()
	{
		return U3A_Row::load_single_object("U3A_Committee", ["login" => "chairperson"]);
	}

	public static function is_webmanager($mbr1)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$wm = self::get_webmanager();
		return $wm && $wm->members_id == $mbr;
	}

	public static function is_membership_secretary($mbr1)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$wm = self::get_membership_secretary();
		return $wm && $wm->members_id == $mbr;
	}

	public static function is_treasurer($mbr1)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$wm = self::get_treasurer();
		return $wm && $wm->members_id == $mbr;
	}

	public static function is_chairperson($mbr1)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$wm = self::get_chairperson();
		return $wm && $wm->members_id == $mbr;
	}

	public static function get_role($role)
	{
		$ret = null;
		$rl = U3A_Row::load_single_object("U3A_Committee", ["role" => $role]);
		if ($rl)
		{
			$ret = $role;
		}
		else
		{
			$rl = U3A_Row::load_single_object("U3A_Committee", ["login" => $role]);
			if ($rl)
			{
				$ret = $rl->role;
			}
		}
		return $role;
	}

	public static function is_committee_member($mbr1)
	{
		$mbr = U3A_Members::get_member($mbr1);
		$ret = null;
//		write_log($mbr);
		if ($mbr)
		{
			$ret = U3A_Row::load_single_object("U3A_Committee", ["members_id" => $mbr->id]);
//			write_log($ret);
			if (!$ret && ($mbr->membership_number > 990000) && ($mbr->forename === "Committee") && ($mbr->surname === "System"))
			{
				$ret = new U3A_System_Test_Committee([
					"role"		 => "System Committee Member",
					"login"		 => "systemcommitteemember",
					"members_id" => $mbr->id,
					"email"		 => "system.committee@" . U3A_Information::u3a_get_domain_name()
				]);
//				write_log("ret", $ret);
			}
		}
//		$ret = 0;
//		$cmt = U3A_Row::load_single_object("U3A_Committee", ["members_id" => $mbr]);
//		if ($cmt)
//		{
//			$ret = $cmt->id;
//		}
		return $ret;
	}

	public static function has_role($mbr1, $role)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$cm = U3A_Row::load_array_of_objects("U3A_Committee", ["members_id" => $mbr, "login" => $role]);
		$ret = false;
		if ($cm && $cm["total_number_of_rows"])
		{
			$ret = true;
		}
		else
		{
			$cm = U3A_Row::load_array_of_objects("U3A_Committee", ["members_id" => $mbr, "role" => $role]);
			$ret = $cm && $cm["total_number_of_rows"];
		}
		return $ret;
	}

	public static function get_preferred_committee_role($mbr)
	{
		$ret = null;
		$croles = self::list_roles($mbr);
		if ($croles)
		{
			$ret = $croles[0];
		}
		return $ret;
	}

	public static function list_roles($mbr)
	{
		$ret = [];
		$members_id = U3A_Members::get_member_id($mbr);
		$roles = self::get_committee_for_member($mbr);
		$nr = count($roles);
		if ($nr)
		{
			$ret = $roles;
			if ($nr > 1)
			{
				$pr = U3A_Row::load_single_object("U3A_Preferred_Role", ["members_id" => $members_id]);
				if ($pr)
				{
					$cmpr = $pr->committee_id;
					$ret = [];
					for ($n = 0; $n < $nr; $n++)
					{
						if ($roles[$n]->id == $cmpr)
						{
							array_unshift($ret, $roles[$n]);
						}
						else
						{
							array_push($ret, $roles[$n]);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function is_preferred_role($mbr, $cm)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$roles = self::get_committee_for_member($mbr);
		$committee_id = self::get_committee_id($cm);
		$ret = false;
		$has_this_role = false;
		$nr = count($roles);
		if ($roles)
		{
			for ($n = 0; ($n < $nr) && !$has_this_role; $n++)
			{
				$has_this_role = $roles[$n]->id == $committee_id;
			}
		}
		if ($has_this_role)
		{
			if ($nr === 1)
			{
				$ret = true;
			}
			else
			{
				$ret = U3A_Row::has_rows("U3A_Preferred_Role", ["members_id" => $members_id, "committee_id" => $committee_id]);
			}
		}
		return $ret;
	}

	public static function send_mail_to_all($committee_id, $from_member, $subject, $contents, $attachments = [], $use_private_email = false, $use_cc = false, $use_no_reply = true, $use_reply_to = true)
	{
		$to_members = U3A_Row::load_array_of_objects("U3A_Committee");
		return self::send_mail_to_some($committee_id, $to_members["result"], $from_member, $subject, $contents, $attachments, $use_private_email, $use_cc, $use_no_reply, $use_reply_to);
	}

	public static function send_mail_to_some($committee_id, $to_members, $from_member, $subject, $contents, $attachments = [], $use_private_email = false, $use_cc = false, $use_no_reply = true, $use_reply_to = true)
	{
//		write_log($from_member);
		$to_members1 = U3A_Members::get_members($to_members);
		if ($committee_id)
		{
			$fromm = self::get_committee_from_id($committee_id);
			$nr = "ShrewsburyU3A " . $fromm->role . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		else
		{
			$fromm = self::get_preferred_committee_role($from_member);
			$nr = "ShrewsburyU3A Committee <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		$from = $fromm->get_full_email_address();
		$nsent = "";
		if (strpos($contents, "%%") === FALSE)
		{
			$to = [];
			foreach ($to_members1 as $mbr)
			{
				if ($use_private_email)
				{
					$to[] = $mbr->get_full_email_address();
				}
				else
				{
					$cm = U3A_Committee::get_committee_for_member($mbr);
					if ($cm)
					{
						foreach ($cm as $c)
						{
							$to[] = $c->get_full_email_address();
						}
					}
					else
					{
						$to[] = $mbr->get_full_email_address();
					}
				}
			}

			if ($use_cc)
			{
				$cc = array_unique($to);
				$bcc = null;
			}
			else
			{
				$bcc = array_unique($to);
				$cc = null;
			}
			$sent = U3A_Sent_Mail::send($fromm->id, $nr, $subject, $contents, $cc, $bcc, $use_no_reply ? $nr : $from, $use_reply_to ? $from : null, $attachments, true, true);
			if (!$sent)
			{
				$nsent = " to committee";
			}
		}
		else
		{
			foreach ($to_members1 as $member)
			{
				$mmcontents = U3A_Sent_Mail::mail_merge($contents, $member, 0, $fromm);
				if (!self::send_mail_to_one($committee_id, $member, $fromm, $subject, $mmcontents["contents"], $attachments))
				{
					$nsent .= " " . U3A_Members::get_member_name($member);
				}
			}
			if ($nsent)
			{
				$nsent = " to" . $nsent;
			}
		}
		return $nsent;
	}

	public static function send_mail_to_one($committee_id, $to_member, $from_member, $subject, $contents, $attachments = [])
	{
		if ($committee_id)
		{
			$fromm = self::get_committee_from_id($committee_id);
			$nr = "ShrewsburyU3A " . $fromm->role . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		else
		{
			$fromm = self::get_preferred_committee_role($from_member);
			$nr = "ShrewsburyU3A Committee <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		$from = $fromm->get_full_email_address();
		$mbr = self::get_committee($to_member);
		$to = $mbr->get_full_email_address();
		return U3A_Sent_Mail::send($fromm->id, $to, $subject, $contents, null, null, $nr, $from, $attachments, true, true);
//		$mailer = U3A_Mail::get_the_mailer();
//		return $mailer->sendmail($nr, $subject, $contents, null, $bcc, $nr, $from, $attachments);
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_committee", "id", $param, null, null, null);
	}

	public function get_full_email_address()
	{
		$ret = "";
		if (isset($this->_data["email"]) && $this->_data["email"])
		{
			$ret = "ShrewsburyU3A " . $this->_data["role"] . " <" . $this->_data["email"] . ">";
		}
		return $ret;
	}

	public function get_the_member()
	{
		return self::get_committee_member($this);
	}

	public function get_name()
	{
		$ret = "";
		$mbr = $this->get_the_member();
		if ($mbr)
		{
			$ret = $mbr->get_name();
		}
		return $ret;
	}

}

class U3A_System_Test_Committee extends U3A_Committee
{

	public function save($allow_null = false, $checkfirst = false, $checkid = true)
	{
		// do nothing
	}

}

class U3A_Roles extends U3A_Database_Row
{

	public static function get_all_members()
	{
		$sql = "SELECT u3a_roles.id AS id, u3a_roles.role AS role, u3a_roles.members_id AS members_id, u3a_members.forename AS forename, u3a_members.surname AS surname, u3a_roles.email AS email FROM " .
		  "u3a_roles JOIN u3a_members ON u3a_roles.members_id = u3a_members.id ORDER BY u3a_roles.role";
		return Project_Details::get_db()->loadList($sql);
	}

	public static function get_roles_from_id($id)
	{
		$ret = U3A_Row::load_single_object("U3A_Roles", ["id" => $id]);
		return $ret;
	}

	public static function get_roles($ent, $include_unfilled = false)
	{
		$ret = null;
		if (is_numeric($ent))
		{
			$ret = U3A_Row::load_single_object("U3A_Roles", ["members_id" => $ent]);
		}
		else if (is_string($ent))
		{
			$where = ["role" => $ent];
			if (!$include_unfilled)
			{
				$where["members_id>"] = 0;
			}
			$ret = U3A_Row::load_single_object("U3A_Roles", $where);
			if (!$ret)
			{
				$where = ["login" => $ent];
				if (!$include_unfilled)
				{
					$where["members_id>"] = 0;
				}
				$ret = U3A_Row::load_single_object("U3A_Roles", ["login" => $ent]);
			}
		}
		else if (is_a($ent, "U3A_Roles"))
		{
			$ret = $ent;
		}
		else if (is_a($ent, "U3A_Members"))
		{
			$ret = U3A_Row::load_single_object("U3A_Roles", ["members_id" => $ent->id]);
		}
		return $ret;
	}

	public static function get_roles_member($ent)
	{
		$ret = null;
		$cmttee = self::get_roles($ent);
		if ($cmttee)
		{
			$ret = U3A_Members::get_member($cmttee->members_id);
		}
		return $ret;
	}

	public static function get_roles_role($ent)
	{
		$ret = null;
		$crole = self::get_roles($ent);
		if ($crole)
		{
			$ret = $crole->role;
		}
		return $ret;
	}

	public static function get_roles_id($ent)
	{
		$ret = 0;
		$crole = self::get_roles($ent);
		if ($crole)
		{
			$ret = intval($crole->id);
		}
		return $ret;
	}

	public static function get_roles_ids_for_member($members_id)
	{
		return U3A_Row::load_column("u3a_roles", "id", ["members_id" => $members_id]);
	}

	public static function get_roles_for_member($member)
	{
		$members_id = U3A_Members::get_member_id($member);
		$cm = U3A_Row::load_array_of_objects("U3A_Roles", ["members_id" => $members_id]);
		return $cm["result"];
	}

	public static function get_role($role)
	{
		$ret = null;
		$rl = U3A_Row::load_single_object("U3A_Roles", ["role" => $role]);
		if ($rl)
		{
			$ret = $role;
		}
		else
		{
			$rl = U3A_Row::load_single_object("U3A_Roles", ["login" => $role]);
			if ($rl)
			{
				$ret = $rl->role;
			}
		}
		return $role;
	}

	public static function is_roles_member($mbr1)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$ret = null;
		if ($mbr)
		{
			$ret = U3A_Row::load_single_object("U3A_Roles", ["members_id" => $mbr]);
		}
//		$ret = 0;
//		$cmt = U3A_Row::load_single_object("U3A_Roles", ["members_id" => $mbr]);
//		if ($cmt)
//		{
//			$ret = $cmt->id;
//		}
		return $ret;
	}

	public static function has_role($mbr1, $role)
	{
		$mbr = U3A_Members::get_member_id($mbr1);
		$cm = U3A_Row::load_array_of_objects("U3A_Roles", ["members_id" => $mbr, "login" => $role]);
		$ret = false;
		if ($cm && $cm["total_number_of_rows"])
		{
			$ret = true;
		}
		else
		{
			$cm = U3A_Row::load_array_of_objects("U3A_Roles", ["members_id" => $mbr, "role" => $role]);
			$ret = $cm && $cm["total_number_of_rows"];
		}
		return $ret;
	}

	public static function get_preferred_roles_role($mbr)
	{
		$ret = null;
		$croles = self::list_roles($mbr);
		if ($croles)
		{
			$ret = $croles[0];
		}
		return $ret;
	}

	public static function list_roles($mbr)
	{
		$ret = [];
		$members_id = U3A_Members::get_member_id($mbr);
		$roles = self::get_roles_for_member($mbr);
		$nr = count($roles);
		if ($nr)
		{
			$ret = $roles;
			if ($nr > 1)
			{
				$pr = U3A_Row::load_single_object("U3A_Preferred_Role", ["members_id" => $members_id]);
				if ($pr)
				{
					$cmpr = $pr->committee_id;
					$ret = [];
					for ($n = 0; $n < $nr; $n++)
					{
						if ($roles[$n]->id == $cmpr)
						{
							array_unshift($ret, $roles[$n]);
						}
						else
						{
							array_push($ret, $roles[$n]);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function is_preferred_role($mbr, $cm)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$roles = self::get_roles_for_member($mbr);
		$roles_id = self::get_roles_id($cm);
		$ret = false;
		$has_this_role = false;
		$nr = count($roles);
		if ($roles)
		{
			for ($n = 0; ($n < $nr) && !$has_this_role; $n++)
			{
				$has_this_role = $roles[$n]->id == $roles_id;
			}
		}
		if ($has_this_role)
		{
			if ($nr === 1)
			{
				$ret = true;
			}
			else
			{
				$ret = U3A_Row::has_rows("U3A_Preferred_Role", ["members_id" => $members_id, "committee_id" => $roles_id]);
			}
		}
		return $ret;
	}

	public static function send_mail_to_all($roles_id, $from_member, $subject, $contents, $attachments = [], $use_private_email = false, $use_cc = false, $use_no_reply = false, $use_reply_to = false)
	{
		$to_members = U3A_Row::load_array_of_objects("U3A_Roles");
		return self::send_mail_to_some($roles_id, $to_members["result"], $from_member, $subject, $contents, $attachments, $use_private_email, $use_cc, $use_no_reply, $use_reply_to);
	}

	public static function send_mail_to_some($roles_id, $to_members, $from_member, $subject, $contents, $attachments = [], $use_private_email = false, $use_cc = false, $use_no_reply = false, $use_reply_to = false)
	{
//		write_log($from_member);
		$to_members1 = U3A_Members::get_members($to_members);
		if ($roles_id)
		{
			$fromm = self::get_roles_from_id($roles_id);
			$nr = "ShrewsburyU3A " . $fromm->role . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		else
		{
			$fromm = self::get_preferred_roles_role($from_member);
			$nr = "ShrewsburyU3A Committee <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		$from = $fromm->get_full_email_address();
		$nsent = "";
		if (strpos($contents, "%%") === FALSE)
		{
			$to = [];
			foreach ($to_members1 as $mbr)
			{
				if ($use_private_email)
				{
					$to[] = $mbr->get_full_email_address();
				}
				else
				{
					$cm = U3A_Roles::get_roles_for_member($mbr);
					if ($cm)
					{
						foreach ($cm as $c)
						{
							$to[] = $c->get_full_email_address();
						}
					}
					else
					{
						$to[] = $mbr->get_full_email_address();
					}
				}
			}

			if ($use_cc)
			{
				$cc = array_unique($to);
				$bcc = null;
			}
			else
			{
				$bcc = array_unique($to);
				$cc = null;
			}
			$sent = U3A_Sent_Mail::send($fromm->id, $nr, $subject, $contents, $cc, $bcc, $use_no_reply ? $nr : $from, $use_reply_to ? $from : null, $attachments, true, true);
			if (!$sent)
			{
				$nsent = " to roles";
			}
		}
		else
		{
			foreach ($to_members1 as $member)
			{
				$mmcontents = U3A_Sent_Mail::mail_merge($contents, $member, 0, $fromm);
				if (!self::send_mail_to_one($roles_id, $member, $fromm, $subject, $mmcontents["contents"], $attachments))
				{
					$nsent .= " " . U3A_Members::get_member_name($member);
				}
			}
			if ($nsent)
			{
				$nsent = " to" . $nsent;
			}
		}
		return $nsent;
	}

	public static function send_mail_to_one($roles_id, $to_member, $from_member, $subject, $contents, $attachments = [])
	{
		if ($roles_id)
		{
			$fromm = self::get_roles_from_id($roles_id);
			$nr = "ShrewsburyU3A " . $fromm->role . " <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		else
		{
			$fromm = self::get_preferred_roles_role($from_member);
			$nr = "ShrewsburyU3A Committee <" . U3A_Mail::get_no_reply_mailbox() . ">";
		}
		$from = $fromm->get_full_email_address();
		$mbr = self::get_roles($to_member);
		$to = $mbr->get_full_email_address();
		return U3A_Sent_Mail::send($fromm->id, $to, $subject, $contents, null, null, $nr, $from, $attachments, true, true);
//		$mailer = U3A_Mail::get_the_mailer();
//		return $mailer->sendmail($nr, $subject, $contents, null, $bcc, $nr, $from, $attachments);
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_roles", "id", $param, null, null, null);
	}

	public function get_full_email_address()
	{
		$ret = "";
		if (isset($this->_data["email"]) && $this->_data["email"])
		{
			$ret = "ShrewsburyU3A " . $this->_data["role"] . " <" . $this->_data["email"] . ">";
		}
		return $ret;
	}

}

class U3A_Documents extends U3A_Database_Row
{

	const GROUP_DOCUMENT_TYPE = 0;
	const GROUP_IMAGE_TYPE = 1;
	const NEWSLETTER_TYPE = 2;
	const PUBLIC_DOCUMENT_TYPE = 3;
	const PRIVATE_DOCUMENT_TYPE = 4;
	const COORDINATORS_DOCUMENT_TYPE = 5;
	const COMMITTEE_IMAGE_TYPE = 6;
	const COORDINATORS_IMAGE_TYPE = 7;
	const USERGUIDE_DOCUMENT_TYPE = 8;
	const USERGUIDE_COORDINATORS_DOCUMENT_TYPE = 9;
	const USERGUIDE_COMMITTEE_DOCUMENT_TYPE = 10;
	const PERSONAL_DOCUMENT_TYPE = 11;
	const PERSONAL_IMAGE_TYPE = 12;
	const COMMITTEE_GROUP = 0;
	const VISIBILITY_GROUP = 0;
	const VISIBILITY_U3A = 1;
	const VISIBILITY_PUBLIC = 2;

	private static $visibilities = [
		self::VISIBILITY_GROUP,
		self::VISIBILITY_U3A,
		self::VISIBILITY_PUBLIC
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

	public static function get_type_description($type1)
	{
		$type = intval($type1);
		switch ($type) {
			case self::COMMITTEE_IMAGE_TYPE:
			case self::COORDINATORS_IMAGE_TYPE:
			case self::GROUP_IMAGE_TYPE:
				{
					$ret = "image";
					break;
				}
			case self::NEWSLETTER_TYPE:
				{
					$ret = "newsletter";
					break;
				}
			case self::PUBLIC_DOCUMENT_TYPE:
				{
					$ret = "public document";
					break;
				}
			case self::PRIVATE_DOCUMENT_TYPE:
				{
					$ret = "private document";
					break;
				}
			case self::COORDINATORS_DOCUMENT_TYPE:
				{
					$ret = "coordinators document";
					break;
				}
			default:
				{
					$ret = "document";
					break;
				}
		}
		return $ret;
	}

	public static function get_type_name($type1)
	{
		$type = intval($type1);
		switch ($type) {
			case self::COMMITTEE_IMAGE_TYPE:
			case self::COORDINATORS_IMAGE_TYPE:
			case self::GROUP_IMAGE_TYPE:
				{
					$ret = "image";
					break;
				}
			case self::NEWSLETTER_TYPE:
				{
					$ret = "newsletter";
					break;
				}
			default:
				{
					$ret = "document";
					break;
				}
		}
		return $ret;
	}

	public static function get_type_name_uc1($type)
	{
		return ucfirst(self::get_type_name($type));
	}

	public static function get_type_name_indefinite($type)
	{
		return U3A_Utilities::add_indefinite_article(self::get_type_name($type));
	}

	public static function get_type_name_definite($type)
	{
		return U3A_Utilities::add_definite_article(self::get_type_name($type));
	}

	public static function get_type_title($type1)
	{
		$type = intval($type1);
		switch ($type) {
			case self::COMMITTEE_IMAGE_TYPE:
			case self::COORDINATORS_IMAGE_TYPE:
			case self::GROUP_IMAGE_TYPE:
				{
					$ret = "image";
					break;
				}
			case self::NEWSLETTER_TYPE:
				{
					$ret = "newsletter";
					break;
				}
			case self::PRIVATE_DOCUMENT_TYPE:
				{
					$ret = "private document";
					break;
				}
			case self::PUBLIC_DOCUMENT_TYPE:
				{
					$ret = "public document";
					break;
				}
			default:
				{
					$ret = "document";
					break;
				}
		}
		return $ret;
	}

	public static function get_type_title_for_id($type1)
	{
		return str_replace(' ', '-', self::get_type_title($type1));
	}

	public static function get_type_title_uc1($type)
	{
		return ucfirst(self::get_type_title($type));
	}

	public static function get_type_title_indefinite($type)
	{
		return U3A_Utilities::add_indefinite_article(self::get_type_title($type));
	}

	public static function get_type_title_uc1_indefinite($type)
	{
		return U3A_Utilities::add_indefinite_article(self::get_type_title_uc1($type));
	}

	public static function get_attachment_ids_for_group($grp = null, $type = 0, $category = 0, $visibility = self::VISIBILITY_GROUP)
	{
		$groups_id = $grp ? U3A_Groups::get_group_id($grp) : 0;
		$sql = "SELECT u3a_documents.attachment_id FROM u3a_documents JOIN u3a_document_category_relationship ON u3a_documents.id = u3a_document_category_relationship.documents_id WHERE "
		  . "u3a_documents.groups_id = $groups_id AND u3a_documents.document_type = $type AND u3a_documents.visibility >= $visibility AND u3a_document_category_relationship.document_categories_id = $category"
		  . " AND u3a_document_category_relationship.sort_order > 0 ORDER BY u3a_document_category_relationship.sort_order, u3a_documents.title";
		$ret = Project_Details::get_db()->loadColumn($sql);
		return $ret;
	}

	public static function get_attachment_ids_for_member($mbr, $type = 0, $category = 0, $visibility = self::VISIBILITY_GROUP)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$sql = "SELECT u3a_documents.attachment_id FROM u3a_documents JOIN u3a_document_category_relationship ON u3a_documents.id = u3a_document_category_relationship.documents_id WHERE "
		  . "u3a_documents.members_id = $members_id AND u3a_documents.groups_id = -1 AND u3a_documents.visibility >= $visibility AND u3a_documents.document_type = $type AND u3a_document_category_relationship.document_categories_id = $category"
		  . " AND u3a_document_category_relationship.sort_order > 0 ORDER BY u3a_document_category_relationship.sort_order, u3a_documents.title";
		$ret = Project_Details::get_db()->loadColumn($sql);
		return $ret;
	}

	public static function get_latest_newsletter_number()
	{
		$ret = 0;
		$title = U3A_Row::get_single_value("U3A_Documents", "title", ["document_type" => self::NEWSLETTER_TYPE], "title", true);
		if ($title)
		{
			$titlebits = explode(" ", $title);
			$ret = intval($titlebits[0]);
		}
		return $ret;
	}

	public static function compare_docs1($doc1, $doc2)
	{
		$a = strtolower($doc1->get_title());
		$b = strtolower($doc2->get_title());
		return $a < $b ? -1 : ($a == $b ? 0 : 1);
	}

	public static function compare_docs2($doc1, $doc2)
	{
		$a = explode("-", $doc1->get_title());
		if (count($a) < 4)
		{
			$a = explode("_", $doc1->get_title());
		}
		$b = explode("-", $doc2->get_title());
		if (count($b) < 4)
		{
			$b = explode("_", $doc2->get_title());
		}
		return $a[3] < $b[3] ? -1 : ($a[3] == $b[3] ? 0 : 1);
	}

	public static function get_documents_for_group($grp, $type = 0, $category = 0, $visibility = self::VISIBILITY_GROUP)
	{
		$groups_id = intval($grp);
		$desc = "";
		if ($type == self::NEWSLETTER_TYPE)
		{
			$desc = " DESC";
		}
		elseif ($category)
		{
			$cat = U3A_Document_Categories::get_category($category);
			if ($cat && $cat->sort_direction)
			{
				$desc = " DESC";
			}
		}
		$sql = "SELECT u3a_documents.* FROM u3a_documents JOIN u3a_document_category_relationship ON u3a_documents.id = u3a_document_category_relationship.documents_id WHERE "
		  . "u3a_documents.groups_id = $groups_id AND u3a_documents.visibility >= $visibility AND u3a_documents.document_type = $type AND u3a_document_category_relationship.document_categories_id = $category ORDER BY " .
		  "u3a_document_category_relationship.sort_order, u3a_documents.title$desc";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = U3A_Row::get_objects_from_list("U3A_Documents", $list);
		return $ret;
	}

	public static function get_documents_for_member($mbr, $type = U3A_Documents::PERSONAL_DOCUMENT_TYPE, $category = 0, $visibility = self::VISIBILITY_GROUP)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$desc = "";
		if ($type == self::NEWSLETTER_TYPE)
		{
			$desc = " DESC";
		}
		elseif ($category)
		{
			$cat = U3A_Document_Categories::get_category($category);
			if ($cat && $cat->sort_direction)
			{
				$desc = " DESC";
			}
		}
		$sql = "SELECT u3a_documents.* FROM u3a_documents JOIN u3a_document_category_relationship ON u3a_documents.id = u3a_document_category_relationship.documents_id WHERE "
		  . "u3a_documents.members_id = $members_id AND u3a_documents.groups_id = -1 AND u3a_documents.visibility >= $visibility AND u3a_documents.document_type = $type AND u3a_document_category_relationship.document_categories_id = $category ORDER BY " .
		  "u3a_document_category_relationship.sort_order, u3a_documents.title$desc";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = U3A_Row::get_objects_from_list("U3A_Documents", $list);
		return $ret;
	}

	public static function get_all_documents_for_group($grp = 0, $type = 0, $visibility = self::VISIBILITY_GROUP)
	{
		$cats = U3A_Document_Categories::get_categories_for_group($grp, $type);
		$ret = [];
		$total = 0;
		$groups_id = intval($grp);
		$first_non_empty = null;
		$docs = self::get_documents_for_group($groups_id, $type, 0);
		if ($docs)
		{
			$ret["default"] = ["category" => null, "documents" => $docs, "count" => count($docs)];
			$total += count($docs);
			$first_non_empty = "default";
		}
		$ncats = 0;
		$ncats1 = 0;
		foreach ($cats as $cat)
		{
			$docs = self::get_documents_for_group($groups_id, $type, $cat->id, $visibility);
			$ret[stripslashes($cat->name)] = ["category" => $cat, "documents" => $docs, "count" => count($docs)];
			$total += count($docs);
			if ($docs)
			{
				if (!$first_non_empty)
				{
					$first_non_empty = $cat->name;
				}
				$ncats1++;
			}
			$ncats++;
		}
		return ["total" => $total, "number_of_categories" => $ncats, "number_of_non_empty_categories" => $ncats1, "first_non_empty" => $first_non_empty, "documents" => $ret];
	}

	public static function get_all_documents_for_member($mbr, $type = U3A_Documents::PERSONAL_DOCUMENT_TYPE, $visibility = self::VISIBILITY_GROUP)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$cats = U3A_Document_Categories::get_categories_for_member($members_id, $type);
		$ret = [];
		$total = 0;
		$first_non_empty = null;
		$docs = self::get_documents_for_member($members_id, $type, 0, $visibility);
		if ($docs)
		{
			$ret["default"] = ["category" => null, "documents" => $docs, "count" => count($docs)];
			$total += count($docs);
			$first_non_empty = "default";
		}
		$ncats = 0;
		$ncats1 = 0;
		foreach ($cats as $cat)
		{
			$docs = self::get_documents_for_member($members_id, $type, $cat->id);
			$ret[stripslashes($cat->name)] = ["category" => $cat, "documents" => $docs, "count" => count($docs)];
			$total += count($docs);
			if ($docs)
			{
				if (!$first_non_empty)
				{
					$first_non_empty = $cat->name;
				}
				$ncats1++;
			}
			$ncats++;
		}
		return ["total" => $total, "number_of_categories" => $ncats, "number_of_non_empty_categories" => $ncats1, "first_non_empty" => $first_non_empty, "documents" => $ret];
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

	public static function get_document_table($documents, $type)
	{
		if ($type === self::NEWSLETTER_TYPE)
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
			if ($type === self::NEWSLETTER_TYPE)
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

	private static function get_visibility_select($groups_id, $type, $op, $selected = self::VISIBILITY_GROUP)
	{
		$options = [];
		for ($n = 0; $n < count(self::$visibilities); $n++)
		{
			$options[$n] = new U3A_OPTION("group", self::$visibilities[$n], self::$visibilities[$n] === $selected);
		}
//		$options[1] = new U3A_OPTION("u3a", self::VISIBILITY_U3A, false);
//		$options[2] = new U3A_OPTION("public", self::VISIBILITY_PUBLIC, false);
		$ret = new U3A_SELECT($options, "visibility", "u3a-visibility-$op-$groups_id-$type", "u3a-visibility-select-class");
		return $ret;
	}

	public static function get_document_management($memgrp, $type1, $selected_category_id = 0)
	{
		$type = intval($type1);
		$category_label = "category";
		if (($type === self::GROUP_IMAGE_TYPE) || ($type === self::COMMITTEE_IMAGE_TYPE) || ($type === self::COORDINATORS_IMAGE_TYPE) || ($type === self::PERSONAL_IMAGE_TYPE))
		{
			$params = self::$image_upload_button_parameters;
			$category_label = "album";
		}
		else
		{
			$params = self::$document_upload_button_parameters;
		}
		if (($type === self::PERSONAL_IMAGE_TYPE) || ($type === self::PERSONAL_DOCUMENT_TYPE))
		{
			$is_group = 0;
		}
		else
		{
			$is_group = 1;
		}
		$type_name = U3A_Documents::get_type_name($type);
		$type_name1 = U3A_Documents::get_type_title_indefinite($type);
		$file_input_id = "upload-document-file-" . $memgrp . "-" . $type;
		$file_input = new U3A_INPUT("file", "u3a-upload-document-file", $file_input_id);
		$file_input->add_attribute("accept", $params["accept"]);
		$file_input->add_attribute("onchange", "upload_file_changed('" . $file_input_id . "')");
// get_select_list($grp, $type = 0, $id = "", $onchange = null, $selected1 = null, $include_default = false, $include = null, $omit = null)
		$select = U3A_Document_Categories::get_select_list($memgrp, $type, "manage-documents", "u3a_document_category_change");
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
			$sel = new U3A_INPUT("hidden", "category", "u3a-upload-category-" . $memgrp . "-" . $type, "u3a-upload-category-class", "0");
		}
		$div = new U3A_DIV($sel, "u3a-select-list-div-$memgrp-$type", "u3a-select-list-div-class u3a-bottom-margin-5 u3a-top-margin-5");
		$btn = new U3A_BUTTON("button", "upload", "upload-document-post-button-" . $memgrp . "-" . $type, "u3a-upload-document-post-button-class u3a-button", "u3a_upload_document_from_form($memgrp, '$type', $is_group)");
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
				U3A_HTML_Utilities::get_large_number_select("title1", "u3a-newletter-number", "u3a-inline-block", $num + 1, $num - 100, $num + 100),
				new U3A_SPAN("year:", null, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5"),
				U3A_HTML_Utilities::get_year_select("title2", "u3a-newletter-year", "u3a-inline-block", 8),
				new U3A_SPAN("month:", null, "u3a-inline-block u3a-margin-left-5 u3a-margin-right-5"),
				U3A_HTML_Utilities::get_month_select("title3", "u3a-newletter-month", "u3a-inline-block")
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
				$visibility = U3A_HTML::labelled_html_object("visibility: ", self::get_visibility_select($memgrp, $type, "add"), null, "u3a-input-label-class", false, true, null);
			}
		}
		$contents = [
			$div,
			new U3A_H(4, "Upload $type_name1"),
			$visibility,
			new U3A_DIV($file_input, null, "upload-document-file-div-class u3a-file-div-class"),
			$titlebit,
			new U3A_INPUT("hidden", "action", null, null, "u3a_upload_document"),
			new U3A_INPUT("hidden", "group", "u3a-manage-documents-group-$memgrp-$type", null, $memgrp),
			new U3A_INPUT("hidden", "type", "u3a-manage-documents-type-$memgrp-$type", null, $type),
//			new U3A_INPUT("hidden", "category", "u3a-upload-category-" . $action . "-" . $memgrp . "-" . $type, "u3a-upload-category-class", "0"),
			new U3A_DIV($btn, null, "u3a-upload-document-button-div-class u3a-button-div-class")
		];
		$uplf = new U3A_FORM($contents, "/wp-admin/admin-ajax.php", "POST", "upload-document-form-" . $memgrp . "-" . $type, "u3a-upload-document-form-class");
		$uplf->add_attribute("enctype", "multipart/form-data");
		$upldocs = new U3A_DIV($uplf, null, "u3a-upload-div-class");
		$del = [];
		$alldocs = U3A_Documents::get_all_documents_for_group($memgrp, $type);
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
					$editid = "u3a-copy-document-" . $memgrp . "-" . $type . "-" . $catid;
					$editsel = new U3A_SELECT($opts1, "u3a-" . $type_name . "-select", $editid, "u3a-" . $type_name . "-select-class");
					$editsel->add_attribute("onchange", "u3a_edit_document_changed($memgrp, '" . $type . "')");
					$editseldiv = new U3A_DIV($editsel, "u3a-edit-select-list-div-$memgrp-$type", "u3a-select-list-div-class u3a-bottom-margin-5 u3a-top-margin-5");
					$editbtn = new U3A_BUTTON("button", "edit", "edit-document-post-button-" . $memgrp . "-" . $type, "u3a-edit-document-post-button-class u3a-button", "u3a_edit_document($memgrp, '$type', $is_group)");
					$titlebit1 = [
						U3A_HTML::labelled_html_object("title: ", new U3A_INPUT("string", "title", "u3a-edit-title-$memgrp-$type", "u3a-input-title-class", $docs[0]->title), null, "u3a-input-label-class", false, true, "Give a new title"),
						U3A_HTML::labelled_html_object($params["by"] . ": ", new U3A_INPUT("string", "by", "u3a-edit-by-$memgrp-$type", "u3a-input-by-class", $docs[0]->author), null, "u3a-input-label-class", false, true)
					];
					if (($type === U3A_Documents::GROUP_DOCUMENT_TYPE) || ($type === U3A_Documents::GROUP_IMAGE_TYPE) || ($type === U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($type === U3A_Documents::PERSONAL_IMAGE_TYPE))
					{
						$titlebit1[] = U3A_HTML::labelled_html_object("visibility: ", self::get_visibility_select($memgrp, $type, "edit"), null, "u3a-input-label-class", false, true, null);
					}
					$edith = new U3A_H(4, "Edit " . $type_name1);
					$editdiv = new U3A_DIV([$edith, $editseldiv, $titlebit1, $editbtn], null, "u3a-edit-document-div-class");
//					$del[] = $editdiv;
					// move
					$oph = new U3A_H(4, "$op " . $type_name1);
					$def = new U3A_OPTION("all", 0);
					array_unshift($opts, $def);
					$delid = "u3a-delete-document-" . $memgrp . "-" . $type . "-" . $catid;
					$cpid = "u3a-copy-document-" . $memgrp . "-" . $type . "-" . $catid;
					$sel = new U3A_SELECT($opts, "u3a-" . $type_name . "-select", $delid, "u3a-" . $type_name . "-select-class");
					$cpsel = new U3A_SELECT($opts, "u3a-" . $type_name . "-select", $cpid, "u3a-" . $type_name . "-select-class");
					$sel1 = U3A_Document_Categories::get_select_list($memgrp, $type, "select-category-move-$catid", null, -1, true, "trash", $catid);
					$cpsel1 = U3A_Document_Categories::get_select_list($memgrp, $type, "select-category-copy-$catid", null, -1, true, null, $catid);
					if ($type === U3A_Documents::NEWSLETTER_TYPE)
					{
						$lbl = [
							new U3A_SPAN("select " . $type_name . " to delete: ", null, "u3a-block u3a-margin-right-5"),
							$sel,
							new U3A_BUTTON("button", "delete", "u3a-" . $type_name . "-delete-button", "u3a-select-button-class u3a-button u3a-margin-left-5", "u3a_move_document('$delid', '$type_name', '" . $sel1["id"] . "', '$catid', '$memgrp', $is_group)")
						];
					}
					else
					{
						$mv = [
							new U3A_SPAN("select " . $type_name . " to move: ", null, "u3a-block u3a-margin-right-5"),
							$sel,
							new U3A_SPAN("to", null, "u3a-inline-block u3a-margin-right-5 u3a-margin-left-5"),
							$sel1["select"],
							new U3A_BUTTON("button", "move", "u3a-" . $type_name . "-move-button", "u3a-select-button-class u3a-button u3a-margin-left-5", "u3a_move_document('$delid', '$type_name', '" . $sel1["id"] . "', '$catid', '$memgrp', $is_group)")
						];
						$mvdiv = new U3A_DIV($mv, "u3a-move-document-div-" . $memgrp . "-" . $type . "-" . $catid, "u3a-move-document-div-class-$type");
						$cp = [
							new U3A_SPAN("select " . $type_name . " to copy: ", null, "u3a-block u3a-margin-right-5"),
							$cpsel,
							new U3A_SPAN("to", null, "u3a-inline-block u3a-margin-right-5 u3a-margin-left-5"),
							$cpsel1["select"],
							new U3A_BUTTON("button", "copy", "u3a-" . $type_name . "-copy-button", "u3a-select-button-class u3a-button u3a-margin-left-5", "u3a_copy_document('$cpid', '$type_name', '" . $cpsel1["id"] . "', '$catid', '$memgrp', $is_group)")
						];
						$cpdiv = new U3A_DIV($cp, "u3a-copy-document-div-" . $memgrp . "-" . $type . "-" . $catid, "u3a-copy-document-div-class-$type");
						$sortlist = U3A_HTML_Utilities::get_list_from_object_array($docs, "title", "id", false, "u3a-sort-list-$memgrp-$type-$catid", "u3a-sort-list", "u3a-sort-list-item");
						$instruct = new U3A_DIV("use mouse to move up and down", "u3a-instruction-$memgrp-$type-$catid", "u3a-border-top u3a-margin-top-5");
						$cls = '<span class="dashicons dashicons-yes-alt"></span>';
						$close = new U3A_A('#', $cls, "u3a-close-sort-list-$memgrp-$type-$catid", null, "u3a_sort_list_close('$memgrp', '$type', '$catid', $is_group);");
						$close->add_attribute("rel", "modal:close");
						$sortdiv = new U3A_DIV([$sortlist, $instruct, $close], "u3a-sort-list-div-$memgrp-$type-$catid", "modal u3a-sort-list-div");
						$open = new U3A_A("#u3a-sort-list-div-$memgrp-$type-$catid", 'sort', null, "u3a-button u3a-block");
						$open->add_attribute("role", "button");
						$open->add_attribute("rel", "modal:open");
						$lbl = [$editdiv, $oph, $mvdiv, $cpdiv, $open, $sortdiv];
					}
				}
				else
				{
					$lbl = new U3A_SPAN("There are no " . $type_name . "s in this $category_label.", null, "u3a-inline-block");
				}
				$div = new U3A_DIV($lbl, "u3a-manage-document-div-" . $memgrp . "-" . $type . "-" . $catid, "u3a-manage-document-div-class-$type u3a-border-top");
				if ($catid && $catid != $select["selected"])
				{
					$div->add_class("u3a-invisible");
				}
				$del[] = $div;
			}
		}
		else
		{
			$del[] = new U3A_DIV("No " . $type_name . "s found", "u3a-manage-documents-div-" . $memgrp . "-" . $type, "u3a-manage-document-div-class-$type u3a-border-top");
		}
		return [$upldocs, $del];
	}

	public static function get_document($doc)
	{
		$ret = null;
		if (is_a($doc, "U3A_Documents"))
		{
			$ret = $doc;
		}
		else
		{
			$where = [];
			if (is_numeric($doc))
			{
				$where["id"] = $doc;
			}
			elseif (is_string($doc))
			{
				$where["title"] = $doc;
			}
			$ret = U3A_Row::load_single_object("U3A_Documents", $where);
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_documents", "id", $param, null, null, null);
	}

	public function get_title()
	{
		$ret = $this->_data["title"];
		if (!$ret)
		{
			$ret = str_replace('_', ' ', U3A_File_Utilities::remove_extension($this->_data["file"]));
		}
		return stripslashes($ret);
	}

	public function get_full_title()
	{
		$ret = $this->get_title();
		if (isset($this->_data["author"]) && $this->_data["author"])
		{
			$ret .= " by " . $this->_data["author"];
		}
		return $ret;
	}

}

class U3A_Permission_Types extends U3A_Database_Row
{

	const COMMITTEE_TYPE = 0;
	const GROUP_TYPE = 1;

	public static function get_permission_types_id($name, $type, $management_enabled = 0)
	{
		$ret = 0;
		if (is_numeric($name))
		{
			$ret = intval($name);
		}
		else
		{
			$ret1 = U3A_Row::get_single_value("U3A_Permission_Types", "id", ["name" => $name, "permission_type" => $type]);
			$ret = $ret1 ? intval($ret1) : 0;
		}
		return $ret;
	}

	public static function list_permission_type_names($type, $management_enabled = 0)
	{
		$ret = U3A_Row::load_column("u3a_permission_types", "name", ["permission_type" => $type, "management_enabled<=" => $management_enabled], true, true);
		return $ret;
	}

	public static function list_permission_types($type, $management_enabled = 0)
	{
//load_array_of_objects($objclass, $where = null, $orderby = null, $from = 0, $to = -1, $groupby = null, $distinct = false)
		$ret = U3A_Row::load_array_of_objects("U3A_Permission_Types", ["permission_type" => $type, "management_enabled<=" => $management_enabled], null, 0, -1, null, true);
		return $ret["result"];
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_permission_types", "id", $param, null, null, null);
	}

}

class U3A_Permissions extends U3A_Database_Row
{

	public static function has_permission($permit, $mbr, $grp = null, $management_enabled = 0)
	{
		if ("webmanager" === $mbr || "Web Manager" === $mbr)
		{
			$ret = true;
		}
		else
		{
			$ret = false;
			if ($grp)
			{
				$type = U3A_Permission_Types::GROUP_TYPE;
				$groups_id = U3A_Groups::get_group_id($grp);
			}
			else
			{
				$type = U3A_Permission_Types::COMMITTEE_TYPE;
				$groups_id = 0;
			}
			$permissions_types_id = U3A_Permission_Types::get_permission_types_id($permit, $type, $management_enabled);
			if ($permissions_types_id)
			{
				if (is_string($mbr) && !is_numeric($mbr))
				{
					$ret = U3A_Row::count_rows("U3A_Permissions", ["permission_types_id" => $permissions_types_id, "groups_id" => $groups_id, "committee_id" => U3A_Committee::get_committee_id($mbr)]) > 0;
				}
				if (!$ret)
				{
					$members_id = U3A_Members::get_member_id($mbr);
					if ($members_id)
					{
						if (U3A_Group_Members::is_coordinator($members_id, $groups_id) || U3A_Committee::is_webmanager($members_id))
						{
							$ret = true;
						}
						else
						{
							$ret = U3A_Row::count_rows("U3A_Permissions", ["permission_types_id" => $permissions_types_id, "groups_id" => $groups_id, "members_id" => $members_id]) > 0;
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function allow($permit, $mbr, $grp = null)
	{
		if ($grp)
		{
			$type = U3A_Permission_Types::GROUP_TYPE;
			$groups_id = U3A_Groups::get_group_id($grp);
		}
		else
		{
			$type = U3A_Permission_Types::COMMITTEE_TYPE;
			$groups_id = 0;
		}
		$permissions_types_id = U3A_Permission_Types::get_permission_types_id($permit, $type);
		if ($permissions_types_id)
		{
			$hash = [
				"permission_types_id" => $permissions_types_id,
				"groups_id"				 => $groups_id
			];
			if (is_string($mbr) && !is_numeric($mbr))
			{
				$rl = U3A_Committee::get_committee_id($mbr);
				if ($rl)
				{
					$hash["committee_id"] = $rl;
				}
				else
				{
					$members_id = U3A_Members::get_member_id($mbr);
					$hash["members_id"] = $members_id;
				}
			}
			else
			{
				$members_id = U3A_Members::get_member_id($mbr);
				$hash["members_id"] = $members_id;
			}
			$pm = new U3A_Permissions($hash);
			$pm->save();
		}
	}

	public static function get_permissions_for_group($grp, $management_enabled = 0)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
//		$sql = "SELECT u3a_permissions.*, u3a_permission_types.name as permission_name FROM u3a_permissions JOIN u3a_permission_types ON u3a_permissions.permission_types_id = u3a_permission_types.id WHERE"
//		  . " u3a_permission_types.management_enabled <= $management_enabled AND u3a_permissions.groups_id = $groups_id";
		$sql = "SELECT u3a_permissions.*, u3a_permission_types.name AS permission_name, u3a_committee.role AS committee_role, u3a_members.forename AS forename, u3a_members.surname AS surname"
		  . " FROM u3a_permissions LEFT OUTER JOIN u3a_permission_types ON u3a_permissions.permission_types_id = u3a_permission_types.id"
		  . " LEFT OUTER JOIN u3a_committee ON u3a_permissions.committee_id = u3a_committee.id LEFT OUTER JOIN u3a_members ON u3a_permissions.members_id = u3a_members.id"
		  . " WHERE u3a_permission_types.management_enabled <= $management_enabled AND u3a_permissions.groups_id = $groups_id";
		$list = Project_Details::get_db()->loadList($sql);
		$num = count($list);
		$ret = [];
		for ($n = 0; $n < $num; $n++)
		{
			$obj = new U3A_Permissions( );
			if (!$obj->forename && !$obj->surname)
			{
				$obj->surname = "everyone";
			}
			$obj->set_all($list[$n]);
			$ret[] = $obj;
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_permissions", "id", $param, null, null, null);
	}

}

class U3A_Text extends U3A_Database_Row
{

	public static function get_text($name)
	{
		$text = U3A_Row::get_single_value("U3A_Text", "the_text", ["name" => $name]);
		return $text ? stripslashes($text) : "";
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_text", "id", $param, null, null, null);
	}

}

class U3A_Videos extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_videos", "id", $param, null, null, null);
	}

}

class U3A_Help_Videos extends U3A_Database_Row
{

	const ALLMEMBERS = 0;
	const COORDINATORS = 1;
	const COMMITTEE = 2;

	public function __construct($param = null)
	{
		parent::__construct("u3a_help_videos", "id", $param, null, null, null);
	}

}

class U3A_News extends U3A_Database_Row
{

	public static function get_current_news($public)
	{
		$ret = U3A_Row::load_array_of_objects("U3A_News", ["expires>" => "now()", "public" => ($public ? 1 : 0)], "created");
		return $ret["result"];
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_news", "id", $param, null, null, null);
	}

}

class U3A_Document_Categories extends U3A_Database_Row
{

	public static function create_category_for_member($groups_id, $members_id, $type = 0)
	{
		if ($groups_id)
		{

		}
	}

	public static function number_of_categories_for_group($grp, $type = 0)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$cats = U3A_Row::load_array_of_objects("U3A_Document_Categories", ["groups_id" => $groups_id, "document_type" => $type], "name");
		return $cats["total_number_of_rows"];
	}

	public static function get_categories_for_group($grp, $type = 0)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$cats = U3A_Row::load_array_of_objects("U3A_Document_Categories", ["groups_id" => $groups_id, "document_type" => $type], "name");
		return $cats["result"];
	}

	public static function get_categories_for_member($mbr, $type = U3A_Documents::PERSONAL_DOCUMENT_TYPE)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$cats = U3A_Row::load_array_of_objects("U3A_Document_Categories", ["members_id" => $members_id, "groups_id" => -1, "document_type" => $type], "name");
		return $cats["result"];
	}

	public static function get_non_empty_categories_for_group_by_name($grp, $type = 0)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$sql = "SELECT DISTINCT u3a_document_categories.id AS id, u3a_document_categories.name AS name FROM u3a_document_categories JOIN u3a_document_category_relationship ON "
		  . "u3a_document_categories.id = u3a_document_category_relationship.document_categories_id"
		  . " WHERE u3a_document_categories.groups_id = $groups_id AND u3a_document_categories.document_type = $type";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = [];
		for ($n = 0; $n < count($list); $n++)
		{
			$obj = new U3A_Document_Categories();
			$obj->set_all($list[$n]);
			$obj->groups_id = $groups_id;
			$obj->document_type = $type;
			$ret[$obj->name] = $obj;
		}
		return $ret;
	}

	public static function get_non_empty_categories_for_member_by_name($mbr, $type = U3A_Documents::PERSONAL_DOCUMENT_TYPE)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$sql = "SELECT DISTINCT u3a_document_categories.id AS id, u3a_document_categories.name AS name FROM u3a_document_categories JOIN u3a_document_category_relationship ON "
		  . "u3a_document_categories.id = u3a_document_category_relationship.document_categories_id"
		  . " WHERE u3a_document_categories.members_id = $members_id AND u3a_document_categories.groups_id = -1 AND u3a_document_categories.document_type = $type";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = [];
		for ($n = 0; $n < count($list); $n++)
		{
			$obj = new U3A_Document_Categories();
			$obj->set_all($list[$n]);
			$obj->groups_id = $groups_id;
			$obj->document_type = $type;
			$ret[$obj->name] = $obj;
		}
		return $ret;
	}

	public static function get_empty_categories_for_group_by_name($grp, $type = 0)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$sql = "SELECT DISTINCT u3a_document_categories.id AS id, u3a_document_categories.name AS name FROM u3a_document_categories LEFT JOIN u3a_document_category_relationship ON "
		  . "u3a_document_categories.id = u3a_document_category_relationship.document_categories_id"
		  . " WHERE u3a_document_category_relationship.document_categories_id IS NULL AND u3a_document_categories.groups_id = $groups_id AND u3a_document_categories.document_type = $type";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = [];
		for ($n = 0; $n < count($list); $n++)
		{
			$obj = new U3A_Document_Categories();
			$obj->set_all($list[$n]);
			$obj->groups_id = $groups_id;
			$obj->document_type = $type;
			$ret[$obj->name] = $obj;
		}
		return $ret;
	}

	public static function get_empty_categories_for_member_by_name($mbr, $type = U3A_Documents::PERSONAL_DOCUMENT_TYPE)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$sql = "SELECT DISTINCT u3a_document_categories.id AS id, u3a_document_categories.name AS name FROM u3a_document_categories LEFT JOIN u3a_document_category_relationship ON "
		  . "u3a_document_categories.id = u3a_document_category_relationship.document_categories_id"
		  . " WHERE u3a_document_category_relationship.document_categories_id IS NULL AND u3a_document_categories.members_id = $members_id AND u3a_document_categories.groups_id = -1 AND u3a_document_categories.document_type = $type";
		$list = Project_Details::get_db()->loadList($sql);
		$ret = [];
		for ($n = 0; $n < count($list); $n++)
		{
			$obj = new U3A_Document_Categories();
			$obj->set_all($list[$n]);
			$obj->groups_id = $groups_id;
			$obj->document_type = $type;
			$ret[$obj->name] = $obj;
		}
		return $ret;
	}

	public static function get_categories_for_group_by_name($grp, $type = 0)
	{
		$groups_id = U3A_Groups::get_group_id($grp);
		$cats = U3A_Row::load_hash_of_all_objects("U3A_Document_Categories", ["groups_id" => $groups_id, "document_type" => $type], "name", "name");
		return $cats;
	}

	public static function get_categories_for_member_by_name($mbr, $type = U3A_Documents::PERSONAL_DOCUMENT_TYPE)
	{
		$members_id = U3A_Members::get_member_id($mbr);
		$cats = U3A_Row::load_hash_of_all_objects("U3A_Document_Categories", ["members_id" => $members_id, "groups_id" => -1, "document_type" => $type], "name", "name");
		return $cats;
	}

	public static function get_options_array_for_objects($object_array, $selected1 = null, $include_default = null, $include = null, $omit = null)
	{
		$opts = null;
		$selected = null;
//		write_log("selected1 $selected1");
//		write_log($object_array);
		if ($selected1 && $object_array && isset($object_array[$selected1]))
		{
			$obj = $object_array[$selected1];
			$selected = $obj->id;
		}
		if ($object_array || $include_default || $include)
		{
			if ($object_array)
			{
				$objects1 = array_values($object_array);
			}
			else
			{
				$objects1 = [];
			}
			$objects = [];
			foreach ($objects1 as $o)
			{
				if ($o->id != $omit)
				{
					$objects[] = $o;
				}
			}
			if (!$selected)
			{
				if ($objects && $selected1 !== "default")
				{
					$selected = $objects[0]->id;
				}
				else
				{
					$selected = -1;
				}
			}
			$opts = U3A_HTML_Utilities::get_options_array_from_object_array($objects, "name", "id", $selected, null);
			if ($include_default)
			{
				$def = new U3A_OPTION("default", 0, $selected1 == "default");
				array_unshift($opts, $def);
			}
			if ($include)
			{
				$defname = null;
				if (is_string($include))
				{
					$defname = $include;
					$defid = -1;
				}
				elseif (is_array($include) && (count($include) === 2) && is_string($include[0]) && is_numeric($include[1]))
				{
					$defname = $include[0];
					$defid = intval($include[1]);
				}
				if ($defname)
				{
					$def = new U3A_OPTION($defname, $defid);
					array_unshift($opts, $def);
				}
			}
		}
		return ["options" => $opts, "selected" => $selected];
	}

	public static function get_options_array($mbrgrp, $type = 0, $selected1 = null, $include_default = null, $include = null, $omit = null)
	{
		if (($type == U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($type == U3A_Documents::PERSONAL_IMAGE_TYPE))
		{
			$object_array = self::get_categories_for_member_by_name($mbrgrp, $type);
		}
		else
		{
			$object_array = self::get_categories_for_group_by_name($mbrgrp, $type);
		}
		return self::get_options_array_for_objects($object_array, $selected1, $include_default, $include, $omit);
	}

	public static function get_empty_select_list($mbrgrp, $type = 0, $id = "", $onchange = null, $selected1 = null, $include_default = true, $include = null)
	{
		if (($type == U3A_Documents::PERSONAL_DOCUMENT_TYPE) || ($type == U3A_Documents::PERSONAL_IMAGE_TYPE))
		{
			$object_array = self::get_empty_categories_for_member_by_name($mbrgrp, $type);
		}
		else
		{
			$object_array = self::get_empty_categories_for_group_by_name($mbrgrp, $type);
		}
		$opts = self::get_options_array_for_objects($object_array, $selected1, $include_default, $include);
		return self::get_select_list_from_options_list($opts, $mbrgrp, $type, $id, $onchange, $selected1, $include_default, $include);
	}

	public static function get_select_list($mbrgrp, $type = 0, $id = "", $onchange = null, $selected1 = null, $include_default = false, $include = null, $omit = null)
	{
//		write_log($selected1);
		$opts = self::get_options_array($mbrgrp, $type, $selected1, $include_default, $include, $omit);
		return self::get_select_list_from_options_list($opts, $mbrgrp, $type, $id, $onchange, $selected1, $include_default, $include);
	}

	public static function get_select_list_from_options_list($opts, $mbrgrp, $type = 0, $id = "", $onchange = null, $selected1 = null, $include_default = false, $include = null)
	{
		$ret = null;
		$selid = null;
		if ($opts["options"])
		{
			$selid = "u3a-document-category-select-" . $id;
			if (!U3A_Utilities::ends_with($selid, "-"))
			{
				$selid .= "-";
			}
			$selid .= $mbrgrp . "-" . $type;
			$ret = new U3A_SELECT($opts["options"], "category", $selid, "u3a-document-category-select");
			if ($onchange)
			{
				$ret->add_attribute("onchange", "$onchange(" . $mbrgrp . ', ' . $type . ", '" . $selid . "')");
			}
		}
		return ["id" => $selid, "select" => $ret, "selected" => $opts["selected"]];
	}

	/**
	 *
	 * @param type $ent can be id, name or U3A_Document_Categories object
	 * @return type numeric
	 */
	public static function get_category_id($ent, $type = -1)
	{
		$ret = $ent;
		if (!is_numeric($ent))
		{
			if (is_string($ent))
			{
				$where = ["name" => $ent];
				if ($type >= 0)
				{
					$where["document_type"] = $type;
				}
				$entity = U3A_Row::load_single_object("U3A_Document_Categories", $where);
				if ($entity)
				{
					$ret = $entity->id;
				}
				else
				{
					$ret = 0;
				}
			}
			else if ($ent != null)
			{
				$ret = $ent->id;
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param type $cat can be id, name or U3A_Document_Categories object
	 * @return type U3A_Document_Categories object
	 */
	public static function get_category($cat)
	{
		$ret = $cat;
		if (is_numeric($cat))
		{
			$ret = U3A_Row::load_single_object("U3A_Document_Categories", ["id" => $cat]);
		}
		elseif (is_string($cat))
		{
			$ret = U3A_Row::load_single_object("U3A_Document_Categories", ["name" => $cat]);
		}
		return $ret;
	}

	public static function get_category_name($ent)
	{
		$ret = $ent;
		if (!is_string($ent) || is_numeric($ent))
		{
			if (is_numeric($ent))
			{
				$entity = U3A_Row::load_single_object("U3A_Document_Categories", ["id" => $ent]);
				$ret = $entity->name;
			}
			else
			{
				$ret = $ent->name;
			}
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_document_categories", "id", $param, null, null, null);
	}

}

class WP_Options extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("wp_options", "option_id", $param, null, null, null);
	}

}

class WP_Posts extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("wp_posts", "ID", $param, null, null, null);
	}

}

class WP_Users extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("wp_users", "ID", $param, null, null, null);
	}

}

class U3A_Members_Deleted extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_members_deleted", "id", $param, null, null, null);
	}

}

class U3A_Groups_Deleted extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_groups_deleted", "id", $param, null, null, null);
	}

}

class U3A_Document_Category_Relationship extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_document_category_relationship", "id", $param, null, null, null);
	}

}

class U3A_Slideshows extends U3A_Database_Row
{

	public static function get_attachment_ids($groups_id, $name)
	{
		$ret = [];
		$attachments = U3A_Row::get_single_value("U3A_Slideshows", "attachments", ["name" => $name]);
		if ($attachments)
		{
			$ret = explode(",", $attachments);
		}
		else
		{
			$category = U3A_Document_Categories::get_category_id($name);
			if ($category)
			{
				$ret = U3A_Documents::get_attachment_ids_for_group($groups_id, $groups_id ? U3A_Documents::GROUP_IMAGE_TYPE : U3A_Documents::COMMITTEE_IMAGE_TYPE, $name);
			}
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_slideshows", "id", $param, null, null, null);
	}

}

class U3A_Preferred_Role extends U3A_Database_Row
{

	public static function set_preferred_role($members_id, $committee_id)
	{
		$hash = ["members_id" => $members_id];
		$pr = U3A_Row::load_single_object("U3A_Preferred_Role", $hash);
		if ($pr)
		{
			$pr->committee_id = $committee_id;
		}
		else
		{
			$hash["committee_id"] = $committee_id;
			$pr = new U3A_Preferred_Role($hash);
		}
		$pr->save();
	}

	public static function get_preferred_role_id($members_id)
	{
		$ret = 0;
		$hash = ["members_id" => $members_id];
		$pr = U3A_Row::load_single_object("U3A_Preferred_Role", $hash);
		if ($pr)
		{
			$ret = $pr->committee_id;
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_preferred_role", "id", $param
		  , null, null, null);
	}

}

class U3A_Sent_Mail extends U3A_Database_Row
{

	public static function mail_merge($contents, $member, $group, $committee)
	{
		$mbr = $member ? U3A_Members::get_member($member) : null;
		$grp = $group ? U3A_Groups::get_group($group) : null;
		$cttee = $committee ? U3A_Committee::get_committee($committee) : null;
		$last1 = 0;
		$pc = strpos($contents, "%%", $last1);
		$changed = false;
		if (($pc === FALSE) || (!$mbr && !$gro && !$cttee))
		{
			$ret = $contents;
		}
		else
		{
			$ret = "";
			while ($pc !== FALSE)
			{
				$ret .= substr($contents, $last1, $pc - $last1);
				$pc1 = strpos($contents, "%%", $pc + 2);
				if ($pc1 === FALSE)
				{
					$ret .= substr($contents, $pc);
					$pc = FALSE;
				}
				else
				{
					$changed = true;
					$lookup = substr($contents, $pc + 2, $pc1 - $pc - 2);
					$obj = $mbr;
					$colon = strpos($lookup, ":");
					if ($colon !== FALSE)
					{
						$which1 = substr($lookup, 0, $colon);
						switch (strtolower($which1)) {
							case "member":
								{
									$obj = $mbr;
									break;
								}
							case "group":
								{
									$obj = $grp;
									break;
								}
							case "committee":
								{
									$obj = $cttee;
									break;
								}
						}
						$field = substr($lookup, $colon + 1);
					}
					else
					{
						$field = $lookup;
					}
					$val = $obj->get_field($field);
					$ret .= $val;
					$last1 = $pc1 + 2;
					$pc = strpos($contents, "%%", $last1);
				}
			}
			if ($last1)
			{
				$ret .= substr($contents, $last1);
			}
		}
		return ["contents" => $ret, "changed" => $changed];
	}

	public static function send($sender_id, $to, $subject1, $contents, $cc = null, $bcc = null, $from = null, $reply_to = null, $attachments = null, $html = true, $committee = false)
	{
		$mailer = U3A_Mail::get_the_mailer();
		$config = U3A_CONFIG::get_the_config();
		$subject = '[' . $config->U3ANAME . ' U3A] ' . $subject1;
		$ret = $mailer->sendmail($to, $subject, $contents, $cc, $bcc, $from, $reply_to, $attachments, $html);
		if ($ret)
		{
			$usm = new U3A_Sent_Mail([
				"sender_id"		 => $sender_id,
				"sent_to"		 => $to,
				"subject"		 => $subject,
				"contents"		 => $contents,
				"cc"				 => json_encode($cc),
				"bcc"				 => json_encode($bcc),
				"sent_from"		 => $from,
				"reply_to"		 => $reply_to,
				"attachments"	 => json_encode($attachments),
				"html"			 => $html ? 1 : 0,
				"committee"		 => $committee ? 1 : 0
			]);
			$usm->save();
		}
		return $ret;
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_sent_mail", "id", $param, null, null, null);
	}

}

class U3A_Meetings extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_meetings", "id", $param, null, null, null);
	}

}

class U3A_Members_Information extends U3A_Database_Row
{

	public function __construct($param = null)
	{
		parent::__construct("u3a_members_information", "id", $param, null, null, null);
	}

}

class U3A_Tasks extends U3A_Database_Row
{

	public static function get_to_do()
	{
		$ret = [];
		$sql = "SELECT * FROM `u3a_tasks` WHERE UNIX_TIMESTAMP(last_done) + number_of * every_seconds < CURRENT_TIMESTAMP";
		$list = Project_Details::get_db()->loadList($sql);
		$num = count($list);
		for ($n = 0; $n < $num; $n++)
		{
			$obj = new U3A_Tasks();
			$obj->set_all($list[$n]);
			$ret[] = $obj;
		}
		return $ret;
	}

	public static function get_to_do_procedures_and_update()
	{
		$ret = [];
		$todo = self::get_to_do();
		foreach ($todo as $td)
		{
			$ret = str_replace(" ", "_", strtolower($td->name));
			$td->last_done = date("Y-m-d");
			$td->save();
		}
		return $ret;
	}

	public static function get_to_do_procedures_run_and_update()
	{
		$todo = self::get_to_do();
		foreach ($todo as $td)
		{
			$func = "u3a_" . str_replace([" ", "-"], "_", strtolower($td->name));
			$td->last_done = date("Y-m-d");
			$td->save();
			$func();
		}
	}

	public function __construct($param = null)
	{
		parent::__construct("u3a_tasks", "id", $param, null, null, null);
	}

}
