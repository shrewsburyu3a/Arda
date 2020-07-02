<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

require('fpdf.php');

require_once 'U3ADatabase.php';
require_once 'u3a_database_utilities.php';

if (!function_exists('write_log'))
{

	function write_log($log, ...$rest)
	{
		if (true === WP_DEBUG)
		{
			$t = "[" . gettype($log) . "] ";
			if (is_array($log) || is_object($log))
			{
				error_log($t . print_r($log, true));
			}
			else
			{
				error_log($t . $log);
			}
			if ($rest)
			{
				foreach ($rest as $r)
				{
					$t = "[" . gettype($r) . "] ";
					if (is_array($r) || is_object($r))
					{
						error_log($t . print_r($r, true));
					}
					else
					{
						error_log($t . $r);
					}
				}
			}
		}
	}

}

class U3A_Update
{

	public static $update_types = [
		"members",
		"groups",
		"group membership",
		"venues",
		"committee"
	];
	private $_file = null;
	private $_spreadsheet = null;

	public function __construct($file)
	{
		$this->_file = $file;
		$this->_spreadsheet = IOFactory::load($file);
	}

	private function u3a_get_worksheet_headers($wks)
	{
		$headers = [];
		$col = 1;
		$hdr = $wks->getCellByColumnAndRow($col, 1)->getValue();
		while ($hdr)
		{
			$headers[] = $hdr;
			$col++;
			$hdr = $wks->getCellByColumnAndRow($col, 1)->getValue();
		}
		return $headers;
	}

	private function u3a_get_worksheet_row($wks, $row, $len)
	{
		$values = [];
		for ($n = 1; $n <= $len; $n++)
		{
			$values[] = strval($wks->getCellByColumnAndRow($n, $row)->getValue());
		}
		return $values;
	}

	private function u3a_get_correspondences($tablename, $headers)
	{
		$correspondences = U3A_Row::load_array_of_objects("U3A_Member_Table_Column_Correspondence");
//        var_dump($correspondences);
		$correspondence_dbtable_spreadsheet = [];
		$correspondence_spreadsheet_dbtable = [];
		foreach ($correspondences["result"] as $corr)
		{
//            var_dump($corr);
			$correspondence_dbtable_spreadsheet[$corr->dbtable] = $corr->spreadsheet;
			$correspondence_spreadsheet_dbtable[$corr->spreadsheet] = $corr->dbtable;
		}
		$column_names = U3A_Row::get_the_column_names($tablename);
		$column = [];
		foreach ($column_names as $cn)
		{
			if ($cn !== "id")
			{
				if (array_key_exists($cn, $correspondence_dbtable_spreadsheet))
				{
					$hd = $correspondence_dbtable_spreadsheet[$cn];
				}
				else
				{
					$hd = $cn;
				}
				$colno = array_search($hd, $headers);
				if ($colno === FALSE)
				{
					write_log($cn . "/" . $hd . " not found");
				}
				else
				{
					write_log($cn . "/" . $hd . " at column " . $colno . "");
					$column[$cn] = $colno;
				}
			}
		}
		return $column;
	}

	public function u3a_clear_members()
	{
		$gone = U3A_Row::load_array_of_objects("U3A_Members", ["status<>" => "Current"]);
		foreach ($gone["result"] as $member_to_delete)
		{
			$member_hash = $member_to_delete->get_as_hash();
			$xmbr = new U3A_Members_Deleted($member_hash);
			$xmbr->save();
			$member_to_delete->delete();
		}
	}

	private function load_polls()
	{
		$ret = [];
		$polls = $this->_spreadsheet->getSheetByName("Polls");
		$row = 2;
		$line = $this->u3a_get_worksheet_row($polls, $row, 2);
		while ($line[0])
		{
			$ret[$line[0]] = $line[1];
			$row++;
			$line = $this->u3a_get_worksheet_row($polls, $row, 2);
		}
		return $ret;
	}

	private function load_poll_assignments()
	{
		$polls = $this->load_polls();
		$ret = ["TAM" => [], "newsletter" => []];
		$tamkeyg = 0;
		$tamkeyr = 0;
		$newsletterkey = 0;
		foreach ($polls as $pk => $p)
		{
			if ($p === 'Postal Newsletter Recipient')
			{
				$newsletterkey = $pk;
			}
			elseif ($p === 'TAM Consent Gained')
			{
				$tamkeyg = $pk;
			}
			elseif ($p === 'TAM Consent REFUSED')
			{
				$tamkeyr = $pk;
			}
		}
		$pa = $this->_spreadsheet->getSheetByName("Poll assignments");
		$row = 2;
		$line = $this->u3a_get_worksheet_row($pa, $row, 3);
		write_log(gettype($line[0]));
		while ($line[0])
		{
			if ($line[0] == $newsletterkey)
			{
				$ret["newsletter"][$line[2]] = 1;
			}
			elseif ($line[0] == $tamkeyg)
			{
				$ret["TAM"][$line[2]] = 1;
			}
			elseif ($line[0] == $tamkeyr)
			{
				$ret["TAM"][$line[2]] = 2;
			}
			$row++;
			$line = $this->u3a_get_worksheet_row($pa, $row, 3);
		}
		return $ret;
	}

	public function u3a_update_members($delete = false)
	{
		$pa = $this->load_poll_assignments();
//		write_log($pa);
//		return;
		$members = $this->_spreadsheet->getSheetByName("Members");
		$headers = $this->u3a_get_worksheet_headers($members);
		$column = $this->u3a_get_correspondences("u3a_members", $headers);
		$len = count($headers);
		$row = 2;
		$line = $this->u3a_get_worksheet_row($members, $row, $len);
		$count = 0;
		$memnums = [];
		while ($line[0])
		{
			$mbr = [];
			foreach ($column as $cn => $colno)
			{
				$val = trim($line[$colno]);
				if ($val)
				{
					if ($cn === "joined" || $cn === "renew" || $cn === "gift_aid")
					{
						$phpdate = strtotime($val);
						$mbr[$cn] = date('Y-m-d', $phpdate);
					}
					else
					{
						$mbr[$cn] = $val;
					}
					if ($cn === "mkey")
					{
//						write_log("mkey " . $val);
						if (array_key_exists($val, $pa["TAM"]))
						{
							$mbr["TAM"] = $pa["TAM"][$val];
						}
						if (array_key_exists($val, $pa["newsletter"]))
						{
							$mbr["newsletter"] = $pa["newsletter"][$val];
						}
					}
				}
			}
			$member = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $mbr["membership_number"]]);
			$memnums[] = $mbr["membership_number"];
			if ($member)
			{
				write_log($member->membership_number . " " . $member->forename . " " . $member->surname . " ");
				$needsave = false;
				foreach ($column as $cn => $colno)
				{
					// if new value is empty && db value is not the default
					if (array_key_exists($cn, $mbr) && ($member->$cn != $mbr[$cn]))
					{
						$member->$cn = $mbr[$cn];
						write_log(($needsave ? ", " : "") . $cn);
						$needsave = true;
					}
					elseif (!array_key_exists($cn, $mbr) && ($member->$cn))
					{
						$member->$cn = null;
					}
				}
				if (array_key_exists("TAM", $mbr) && ($member->TAM != $mbr["TAM"]))
				{
					$member->TAM = $mbr["TAM"];
					write_log(($needsave ? ", " : "") . "TAM");
					$needsave = true;
				}
				elseif (!array_key_exists("TAM", $mbr) && ($member->TAM))
				{
					$member->TAM = 0;
				}
				if (array_key_exists("newsletter", $mbr) && ($member->newsletter != $mbr["newsletter"]))
				{
					$member->newsletter = $mbr["newsletter"];
					write_log(($needsave ? ", " : "") . "newsletter");
					$needsave = true;
				}
				elseif (!array_key_exists("newsletter", $mbr) && ($member->newsletter))
				{
					$member->newsletter = 0;
				}
				if ($needsave)
				{
					$member->save();
					write_log(" updated");
				}
				else
				{
					write_log("unchanged");
				}
			}
			else
			{
				$member = new U3A_Members($mbr);
				$member->save();
				write_log($member->membership_number . " " . $member->forename . " " . $member->surname . " added");
//				var_dump($member);
			}
			$count++;
			$row++;
//	    var_dump($member);
			$line = $this->u3a_get_worksheet_row($members, $row, $len);
		}
		write_log("total: " . $count . "");
		$dbmemnums = U3A_Row::load_column("u3a_members", "membership_number");
		foreach ($dbmemnums as $dbmn)
		{
			$idx = array_search($dbmn, $memnums);
			if ($idx === FALSE)
			{
				if (($dbmn != "123456") && ($dbmn != "765432"))
				{
					// not the test member
					write_log("delete " . $dbmn . "");
					if ($delete)
					{
						$member_to_delete = U3A_Row::load_single_object("U3A_Members", ["membership_number" => $dbmn]);
						if ($member_to_delete)
						{
							$member_hash = $member_to_delete->get_as_hash();
							$perms = U3A_Row::load_array_of_objects("U3A_Permissions", ["members_id" => $member_to_delete->id]);
							if ($perms["total_number_of_rows"])
							{
								$pm = [];
								foreach ($perms["results"] as $perm)
								{
									$pm[] = $perm->groups_id . "+" . $perm->committee_id . "+" . $perm->permission_types_id;
									$perm->delete();
								}
								$member_hash["permissions"] = implode(",", $pm);
							}
							$mbrships = U3A_Row::load_array_of_objects("U3A_Group_Members", ["members_id" => $member_to_delete->id]);
							if ($mbrships["total_number_of_rows"])
							{
								$gm = [];
								foreach ($mbrships["results"] as $mbrship)
								{
									$gm[] = $mbrship->groups_id . "+" . $mbrship->status . "+" . $mbrship->added;
									$mbrship->delete();
								}
								$member_hash["groups"] = implode(",", $gm);
							}
							$xmbr = new U3A_Members_Deleted($member_hash);
							$xmbr->save();
							$member_to_delete->delete();
						}
					}
				}
			}
		}
//	fclose($members_file);
	}

	public function u3a_update_venues($delete = false)
	{
		$venues = $this->_spreadsheet->getSheetByName("Venues");
		$headers = $this->u3a_get_worksheet_headers($venues);
//	var_dump($headers);
		$column = $this->u3a_get_correspondences("u3a_venues", $headers);
		$len = count($headers);
		$row = 2;
		$line = $this->u3a_get_worksheet_row($venues, $row, $len);
		$count = 0;
		$vns = [];
		while ($line[0])
		{
			$vnu = [];
			foreach ($column as $cn => $colno)
			{
				$val = trim($line[$colno]);
				if ($val)
				{
					$vnu[$cn] = $val;
				}
			}
			$venue = U3A_Row::load_single_object("U3A_Venues", ["gvkey" => $vnu["gvkey"]]);
			$vns[] = $vnu["venue"];
			if ($venue)
			{
				write_log($venue->venue . " ");
				$needsave = false;
				foreach ($column as $cn => $colno)
				{
					// if new value is empty && db value is not the default
					if (array_key_exists($cn, $vnu) && ($venue->$cn != $vnu[$cn]))
					{
						$venue->$cn = $vnu[$cn];
						write_log(($needsave ? ", " : "") . $cn);
						$needsave = true;
					}
					elseif (!array_key_exists($cn, $vnu) && ($venue->$cn))
					{
						$venue->$cn = null;
					}
				}
				if ($needsave)
				{
					$venue->save();
					write_log("updated");
				}
				else
				{
					write_log("unchanged");
				}
			}
			else
			{
				$venue = new U3A_Venues($vnu);
				$venue->save();
				write_log($venue->venue . " added");
			}
			$count++;
			$row++;
//	    var_dump($venue);
			$line = $this->u3a_get_worksheet_row($venues, $row, $len);
		}
		write_log("total: " . $count . "");
		$dbvenues = U3A_Row::load_column("u3a_venues", "venue");
		foreach ($dbvenues as $vnunm1)
		{
			$vnunm = stripslashes($vnunm1);
			$idx = array_search($vnunm, $vns);
			if ($idx === FALSE)
			{
				write_log("delete " . $vnunm . "");
				if ($delete)
				{
					$venue_to_delete = U3A_Row::load_single_object("U3A_Venues", ["venue" => $vnunm]);
					if ($venue_to_delete)
					{
						$venue_to_delete->delete();
					}
					else
					{
						write_log("not found");
					}
				}
			}
		}
	}

	public function u3a_update_groups($delete = false)
	{
		$groups = $this->_spreadsheet->getSheetByName("Groups");
		$headers = $this->u3a_get_worksheet_headers($groups);
//	var_dump($headers);
		$column = $this->u3a_get_correspondences("u3a_groups", $headers);
		$len = count($headers);
		$row = 2;
		$line = $this->u3a_get_worksheet_row($groups, $row, $len);
		$count = 0;
		$grps = [];
		while ($line[0])
		{
			write_log("checking" . $line[1]);
			if ($line[4])
			{
//	    var_dump($line);
				$grp = [];
				foreach ($column as $cn => $colno)
				{
					$val1 = trim($line[$colno]);
					$val = $val1;
					if ($val1)
					{
						$val = U3A_Database_Row::convert_smart_quotes($val1);
						if ($cn === "venue")
						{
//						write_log("venue " . $val);
							$vnu = U3A_Row::load_single_object("U3A_Venues", ["venue" => addslashes(addslashes($val))]);
//			var_dump($vnu);
							if ($vnu)
							{
								$val = $vnu->id;
							}
							else
							{
								write_log("no venue found " . $val . "");
								$val = 0;
							}
						}
					}
					elseif (($cn === "venue") || ($cn === "max_members"))
					{
						$val = 0;
					}
					$grp[$cn] = $val;
					write_log("column[" . $colno . "]: " . $val);
				}
				$when = $grp["meets_when"];
				$from = $grp["start_time"];
				$to = $grp["end_time"];
				$result = U3A_Groups::convert_meets_when($when, $from, $to);
				write_log("meets_when", $result);
				$grp["meets_when"] = $result["when"];
				$grp["meets_when_notes"] = $result["notes"];
//			var_dump($grp);
				$group = U3A_Row::load_single_object("U3A_Groups", ["gkey" => $grp["gkey"]]);
				$grps[] = $grp["name"];
				if ($group)
				{
					write_log($group->name . " ");
					$needsave = false;
					foreach ($column as $cn1 => $colno1)
					{
						// if new value is empty && db value is not the default
						if (array_key_exists($cn1, $grp) && ($group->$cn1 != $grp[$cn1]))
						{
							$old1 = $group->$cn1;
							$group->$cn1 = $grp[$cn1];
							write_log(($needsave ? ", " : "") . $cn1 . " to '" . $group->$cn1 . "' from '" . $old1 . "'");
							$needsave = true;
						}
						elseif (!array_key_exists($cn1, $grp) && ($group->$cn1))
						{
							$group->$cn1 = null;
						}
					}
					if ($needsave)
					{
						$group->save();
						write_log(" updated");
					}
					else
					{
						write_log("unchanged");
					}
				}
				else
				{
					$group = new U3A_Groups($grp);
					$group->save();
					write_log($group->name . " added");
				}
				$count++;
			}
			else
			{
				write_log("ignored");
			}
			$row++;
//	    var_dump($group);
			$line = $this->u3a_get_worksheet_row($groups, $row, $len);
//		var_dump($line);
		}
		write_log("total: " . $count . "");
//	var_dump($grps);
		$dbgroups = U3A_Row::load_column("u3a_groups", "name");
		foreach ($dbgroups as $grpnm1)
		{
			$grpnm = stripslashes($grpnm1);
			$idx = array_search($grpnm, $grps);
			if (($idx === FALSE) && !U3A_Utilities::starts_with($grpnm, "test group"))
			{
				write_log("delete " . $grpnm . "");
				if ($delete)
				{
					$group_to_delete = U3A_Row::load_single_object("U3A_Groups", ["name" => $grpnm]);
					if ($group_to_delete)
					{
						$group_members = U3A_Row::load_array_of_objects("U3A_Group_Members", ["groups_id" => $group_to_delete->id]);
						foreach ($group_members["result"] as $gm)
						{
							write_log("deleting " . U3A_Members::get_member_name($gm->members_id) . " from $grpnm");
							$gm->delete();
						}
						$grp_hash = $group_to_delete->get_as_hash();
						$xgrp = new U3A_Groups_Deleted($grp_hash);
						$xgrp->save();
						$group_to_delete->delete();
					}
				}
			}
		}
	}

	public function u3a_update_group_membership($delete = false)
	{
//		write_log("u3a_update_group_membership");
		$groups = $this->_spreadsheet->getSheetByName("Groups");
		$group_membership = $this->_spreadsheet->getSheetByName("Group members");
		$headers = $this->u3a_get_worksheet_headers($groups);
		$len = count($headers);
		$row = 2;
		$line = $this->u3a_get_worksheet_row($groups, $row, $len);
		$count = 0;
		$groups_by_name = [];
		$groups_by_key = [];
		while ($line[0])
		{
			$groups_by_name[$line[1]] = $line[0];
			$groups_by_key[$line[0]] = $line[1];
			$row++;
			$line = $this->u3a_get_worksheet_row($groups, $row, $len);
		}
		$headers1 = $this->u3a_get_worksheet_headers($group_membership);
		$len1 = count($headers);
		$row1 = 2;
		$line1 = $this->u3a_get_worksheet_row($group_membership, $row1, $len1);
//		var_dump($line1);
//		write_log($line1);
//		exit;
		$gmem = [];
		while ($line1[0])
		{
			$gname = $line1[1];
			$gname1 = addslashes(addslashes(U3A_Database_Row::convert_smart_quotes($gname)));
			$grp = U3A_Groups::load_single_object("U3A_Groups", ["name" => $gname1]);
			$mbrs1 = U3A_Row::load_array_of_objects("U3A_Members", ["forename" => addslashes(addslashes($line1[2])), "surname" => addslashes(addslashes($line1[3])), "status" => "Current"]);
			$mbrs = $mbrs1["result"];
//			write_log($mbrs);
//			$mbr = U3A_Members::load_single_object("U3A_Members", ["forename" => addslashes(addslashes($line1[2])), "surname" => addslashes(addslashes($line1[3])), "status" => "Current"]);
			if ($grp && $mbrs1["total_number_of_rows"])
			{
				$added1 = strtotime(str_replace('/', '-', $line1[4]));
				$added = date('Y-m-d', $added1);
//				write_log($grp->id . " " . $mbr->id . " added " . $line1[4] . " " . $added1 . " " . $added . "\n";
				$status = 0;
				/*
				 * status 0 - ordinary member
				 * 1 -coordinator
				 * 2 - contact
				 * 3 - coordinator and contact
				 * 4 - waiting
				 */
				if ($line1[6] == "1")
				{
					$status = 3;
				}
				elseif ($line1[5])
				{
					$status = 4;
				}
				$grpmbr = null;
				for ($n = 0; $n < $mbrs1["total_number_of_rows"] && !$grpmbr; $n++)
				{
					$grpmbr = U3A_Row::load_single_object("U3A_Group_Members", [
						  "groups_id"	 => $grp->id,
						  "members_id" => $mbrs[$n]->id
					]);
				}
				if ($grpmbr)
				{
					$grpmbr->added = $added;
					$grpmbr->status = $status;
					write_log("updated group membership " . $grpmbr->groups_id . " member " . $grpmbr->members_id . "");
					$grpmbr->save();
					$gmem[] = intval($grpmbr->id);
				}
				else
				{
					$grpmbr = new U3A_Group_Members([
						"groups_id"	 => $grp->id,
						"members_id" => $mbrs[0]->id,
						"added"		 => $added,
						"status"		 => $status
					]);
					write_log("new group membership " . $grpmbr->groups_id . " member " . $grpmbr->members_id . "");
					$gmem[] = intval($grpmbr->save());
				}
			}
			elseif ($grp)
			{
				write_log("1.no member found $line1[2] $line1[3]");
			}
			elseif ($mbrs1["total_number_of_rows"])
			{
				write_log("1.no group found $gname $gname1");
			}
			else
			{
				write_log("2.no member found $line1[2] $line1[3]");
				write_log("2.no group found $gname $gname1");
			}
			$row1++;
			$line1 = $this->u3a_get_worksheet_row($group_membership, $row1, $len1);
		}
		$dbgmem = U3A_Row::load_column("u3a_group_members", "id");
		$testmbr = U3A_Row::load_single_object("U3A_Members", ["membership_number" => 123456]);
		$testid = $testmbr ? $testmbr->id : 0;
		foreach ($dbgmem as $dbgmid)
		{
			$idx = array_search($dbgmid, $gmem);
			if ($idx === FALSE)
			{
				$dbgm = U3A_Row::load_single_object("U3A_Group_Members", ["id" => $dbgmid]);
				write_log("delete group membership of member with id " . $dbgm->members_id . " from group with id " . $dbgm->groups_id . "");
				if ($delete && $dbgm && ($dbgm->members_id != $testid))
				{
					$dbgm->delete();
				}
			}
		}
	}

	public function u3a_update_committee()
	{
		$committee = $this->_spreadsheet->getSheetByName("U3A Officers");
		$headers = $this->u3a_get_worksheet_headers($committee);
		$len = count($headers);
		$row = 2;
		$line = $this->u3a_get_worksheet_row($committee, $row, $len);
		$count = 0;
		$croles = U3A_Row::load_hash_of_all_objects("U3A_Committee", null, "role");
		$role_set = [];
		foreach ($croles as $r => $m)
		{
			$croles[$r]->members_id = 0;
			$croles[$r]->email = null;
			$croles[$r]->notes = null;
			$croles[$r]->ofkey = 0;
			$role_set[$r] = false;
		}
//		write_log($croles);
		$nr = [];
		while ($line[0])
		{
			$ofkey = $line[0];
			$role = $line[1];
			$fname = $line[3];
			$sname = $line[4];
			$email = $line[5];
			$notes = $line[6];
//			$crole = array_key_exists($role, $croles) ? $croles[$role] : null;
//			$crole = U3A_Row::load_single_object("U3A_Committee", ["role" => $role]);
			$mbr = U3A_Row::load_single_object("U3A_Members", ["forename" => $fname, "surname" => $sname]);
			if ($mbr)
			{
				$hash = [
					"login"		 => strtolower(str_replace([" ", "-"], ["", ""], $role)),
					"members_id" => $mbr->id,
					"email"		 => $email,
					"notes"		 => $notes,
					"ofkey"		 => $ofkey
				];
				if ($role === "Nominated Role")
				{
					$nr[] = ["hash" => $hash, "fname" => $fname, "sname" => $sname];
				}
				elseif (array_key_exists($role, $croles))
				{
					$croles[$role]->set_all($hash);
					$role_set[$role] = true;
					write_log($fname . " " . $sname . $role . " updated");
				}
				else
				{
					$hash["role"] = $role;
					$croles[$role] = new U3A_Committee($hash);
					write_log($fname . " " . $sname . $role . " added");
				}
				$count++;
			}
			else
			{
				write_log("No member found $fname $sname");
			}
			$row++;
			$line = $this->u3a_get_worksheet_row($committee, $row, $len);
		}
		if ($nr)
		{
			for ($n = 1; $n <= count($nr); $n++)
			{
				$role = "Nominated Role $n";
				$nrn = $nr[$n - 1];
				$hash = $nrn["hash"];
				$hash["login"] = "nominatedrole$n";
				$fname = $nrn["fname"];
				$sname = $nrn["sname"];
				if (array_key_exists($role, $croles))
				{
					$croles[$role]->set_all($hash);
					$role_set[$role] = true;
					write_log($fname . " " . $sname . $role . " updated");
				}
				else
				{
					$hash["role"] = $role;
					$croles[$role] = new U3A_Committee($hash);
					write_log($fname . " " . $sname . $role . " added");
				}
			}
		}
		foreach ($role_set as $r => $set)
		{
			if (!$set)
			{
				$croles[$r]->delete();
				unset($croles[$r]);
			}
		}
		foreach ($croles as $r => $m)
		{
			$id = $croles[$r]->save(true);
			write_log($id);
		}
//		write_log($croles);
		write_log("total: " . $count . "");
	}

	public function u3a_update_all($delete = false)
	{
		write_log("clearing members");
		$this->u3a_clear_members();
		write_log("updating members");
		$this->u3a_update_members($delete);
		write_log("updating venues");
		$this->u3a_update_venues($delete);
		write_log("updating groups");
		$this->u3a_update_groups($delete);
		write_log("updating group_membership");
		$this->u3a_update_group_membership($delete);
		write_log("updating committee");
		$this->u3a_update_committee();
	}

}

class U3A_Video_Update
{

	private $_playlistid = "PLrM7DAAMAfswLQtvB3jY9rLyK-6eCBIsq";
	private $_key = "AIzaSyCHzrVtlUHZXytCYbn-iznEikzRAgTrKHY";
	private $_channelid = "UCGNN5yYKjW3kexi4r9AkXeQ";
	private $_source = "youtube";

	public function __construct($playlistid = null, $key = null, $channelid = null, $source = null)
	{
		if ($source)
		{
			$this->_source = $source;
		}
		else
		{
			$source1 = get_option("video_source");
			if ($source1)
			{
				$this->_source = $source1;
			}
		}
		if ($playlistid)
		{
			$this->_playlistid = $playlistid;
		}
		if ($key)
		{
			$this->_key = $key;
		}
		if ($channelid)
		{
			$this->_channelid = $channelid;
		}
	}

	public function u3a_load_videos()
	{
		if ($this->_source === "youtube")
		{
			$playlisturl1 = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults=50&playlistId=' . $this->_playlistid . '&key=' . $this->_key;
			$playlisturl = $playlisturl1;
//		print $playlisturl . "\n";
			$json = file_get_contents($playlisturl);
			$playlist = json_decode($json);
			$playlists = [$playlist];
			while (isset($playlist->nextPageToken))
			{
				$playlisturl = $playlisturl1 . "&pageToken=" . $playlist->nextPageToken;
				$json = file_get_contents($playlisturl);
				$playlist = json_decode($json);
				array_push($playlists, $playlist);
			}
			foreach ($playlists as $playlist)
			{
//		var_dump($playlist);
				foreach ($playlist->items as $item)
				{
					$snippet = $item->snippet;
					$when = date('Y-m-d H:i:s', strtotime($snippet->publishedAt));
					$title = $snippet->title;
					$desc = $snippet->description;
					$rec = strpos($desc, "Recorded");
					if ($rec !== FALSE)
					{
						$description = trim(substr($desc, 0, $rec));
					}
					else
					{
						$description = trim($desc);
					}
					$videoid = $snippet->resourceId->videoId;
					$url = 'https://www.youtube.com/watch?v=' . $videoid;
					$params = [
						"name"			 => $title,
						"source"			 => $this->_source,
						"date"			 => $when,
						"url"				 => $url,
						"description"	 => $description
					];
					$video = U3A_Row::load_single_object("U3A_Videos", ["name" => $title]);
					if ($video)
					{
						write_log("updating video " . $title);
						$video->set_all($params);
					}
					else
					{
						write_log("creating video " . $title);
						$video = new U3A_Videos($params);
					}
					$video->save();
//			var_dump($params);
				}
			}
		}
	}

}

class U3A_Help_Video_Update
{

	private $_playlistid = [
		"PL2Tr6Wbmvw7ylP5-vJ4qyqDifxba0_pyF",
		"PL2Tr6Wbmvw7xIrtc0YrB2uSVi2OWkBgUN",
		"PL2Tr6Wbmvw7zbBBFCfdi4Et1bN9RDtKmD"
	];
	private $_key = "AIzaSyCHzrVtlUHZXytCYbn-iznEikzRAgTrKHY";
	private $_channelid = "UCc_HdcYnqMUksw1HPJODkRQ";
	private $_source = "youtube";

	public function __construct($playlistid = null, $key = null, $channelid = null, $source = null)
	{
		if ($source)
		{
			$this->_source = $source;
		}
		else
		{
			$source1 = get_option("video_source");
			if ($source1)
			{
				$this->_source = $source1;
			}
		}
		if ($playlistid)
		{
			$this->_playlistid = $playlistid;
		}
		if ($key)
		{
			$this->_key = $key;
		}
		if ($channelid)
		{
			$this->_channelid = $channelid;
		}
	}

	public function u3a_load_videos()
	{
		if ($this->_source === "youtube")
		{
			$nplaylists = count($this->_playlistid);
			for ($n = 0; $n < $nplaylists; $n++)
			{
				$playlisturl1 = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults=50&playlistId=' . $this->_playlistid[$n] . '&key=' . $this->_key;
				$playlisturl = $playlisturl1;
//		print $playlisturl . "\n";
				$json = file_get_contents($playlisturl);
				$playlist = json_decode($json);
				$playlists = [$playlist];
				while (isset($playlist->nextPageToken))
				{
					$playlisturl = $playlisturl1 . "&pageToken=" . $playlist->nextPageToken;
					$json = file_get_contents($playlisturl);
					$playlist = json_decode($json);
					array_push($playlists, $playlist);
				}
				foreach ($playlists as $playlist)
				{
//		var_dump($playlist);
					foreach ($playlist->items as $item)
					{
						$snippet = $item->snippet;
						$when = date('Y-m-d H:i:s', strtotime($snippet->publishedAt));
						$title = $snippet->title;
						$desc = $snippet->description;
						$rec = strpos($desc, "Recorded");
						if ($rec !== FALSE)
						{
							$description = trim(substr($desc, 0, $rec));
						}
						else
						{
							$description = trim($desc);
						}
						$videoid = $snippet->resourceId->videoId;
						$url = 'https://www.youtube.com/watch?v=' . $videoid;
						$params = [
							"name"			 => $title,
							"category"		 => $n,
							"source"			 => $this->_source,
							"date"			 => $when,
							"url"				 => $url,
							"description"	 => $description
						];
						$video = U3A_Row::load_single_object("U3A_Help_Videos", ["name" => $title]);
						if ($video)
						{
							write_log("updating video " . $title);
							$video->set_all($params);
						}
						else
						{
							write_log("creating video " . $title);
							$video = new U3A_Help_Videos($params);
						}
						$video->save();
//			var_dump($params);
					}
				}
			}
		}
	}

}

class U3A_Slideshow
{

	public function write_page($ids, $filename = null)
	{
		if (!$filename)
		{
			$filename = "ShrewsburyU3A_slideshow_" . time();
		}
		$path = U3A_Information::get_slideshow_dir() . $filename . ".html";
		$url = U3A_Information::get_slideshow_url() . $filename . ".html";
		$contents = '<!doctype html><html><head><script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/galleria/1.5.7/galleria.min.js"></script>' .
		  '<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/galleria/1.5.7/themes/classic/galleria.classic.css">' .
		  '<style> .galleria { width:800px; height:500px; }</style>' .
		  '</head><body><div class="galleria">';
		foreach ($ids as $id)
		{
			$contents .= '<img src="' . wp_get_attachment_url($id) . '"/>';
		}
		$contents .= '</div><script>(function() {' .
		  'Galleria.loadTheme("https://cdnjs.cloudflare.com/ajax/libs/galleria/1.5.7/themes/classic/galleria.classic.min.js");' .
		  'Galleria.run(".galleria", {' .
		  '  autoplay: true,' .
		  '  responsive: true,' .
		  '           height: 0.5,' .
		  '           maxVideoSize: 1300' .
		  '       });' .
		  '}());' .
		  '</script></body></html>';
		file_put_contents($path, $contents);
		return $url;
	}

}

class U3A_Group_Table
{

	public function write_table($grps, $filename = null)
	{
		write_log(U3A_Information::get_temp_dir());
		if (!$filename)
		{
			$filename = U3A_Information::u3a_get_u3a_name() . "U3A_group_table_" . date('Ymd');
		}
		$path = U3A_Information::get_temp_dir() . $filename . ".xlsx";
		$url = U3A_Information::get_temp_url() . $filename . ".xlsx";
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getActiveSheet()->setCellValue('A1', 'Group Name');
		$spreadsheet->getActiveSheet()->setCellValue('B1', 'Day(s)');
		$spreadsheet->getActiveSheet()->setCellValue('C1', 'Time');
		$spreadsheet->getActiveSheet()->setCellValue('D1', 'Group Coord');
		$spreadsheet->getActiveSheet()->setCellValue('E1', 'Telephone');
		$n = 2;
		foreach ($grps as $grp)
		{
			if (!U3A_Utilities::starts_with($grp->name, "test group "))
			{
				$spreadsheet->getActiveSheet()->setCellValue('A' . $n, $grp->name);
				$days = "";
				$mw = $grp->meets_when_notes;
				$lb = strpos($mw, '[');
				if ($lb !== FALSE)
				{
					$mw1 = substr($mw, $lb + 1);
					$sl = strpos($mw1, '/');
					if ($sl !== FALSE)
					{
						$days = substr($mw1, 0, $sl);
					}
				}
				$spreadsheet->getActiveSheet()->setCellValue('B' . $n, $days);
				$spreadsheet->getActiveSheet()->setCellValue('C' . $n, substr($grp->start_time, 0, 5));
				$coords = U3A_Groups::get_coordinators($grp);
//				write_log($grp->name);
//			write_log($coords);
				$cd = "";
				$tp = "";
				if ($coords)
				{
					$coordnames = [];
					$tels = [];
					foreach ($coords as $coord)
					{
						$coordnames[] = $coord->get_name();
						$tels[] = $coord->get_phone();
					}
					$cd = implode("\n", $coordnames);
					$tp = implode("\n", $tels);
				}
//				write_log($cd);
//				write_log($tp);
				$spreadsheet->getActiveSheet()->setCellValue('D' . $n, $cd);
				$spreadsheet->getActiveSheet()->setCellValue('E' . $n, $tp);
				$c = count($coords);
				if ($c === 0)
				{
					$c = 1;
				}
				$spreadsheet->getActiveSheet()->getRowDimension("$n")->setRowHeight(15 * $c);
				if ($c > 1)
				{
					$spreadsheet->getActiveSheet()->getStyle('D' . $n)->getAlignment()->setWrapText(true);
					$spreadsheet->getActiveSheet()->getStyle('E' . $n)->getAlignment()->setWrapText(true);
				}
				$n++;
			}
		}
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
		$writer->save($path);
		write_log($url);
		return $url;
	}

}

class U3A_Address_List
{

	public function write_list($members, $filename = null)
	{
		write_log(U3A_Information::get_temp_dir());
		if (!$filename)
		{
			$filename = U3A_Information::u3a_get_u3a_name() . "U3A_addresses_" . date('Ymd');
		}
		$path = U3A_Information::get_temp_dir() . $filename . ".xlsx";
		$url = U3A_Information::get_temp_url() . $filename . ".xlsx";
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getActiveSheet()->setCellValue('A1', 'Title');
		$spreadsheet->getActiveSheet()->setCellValue('B1', 'Initial');
		$spreadsheet->getActiveSheet()->setCellValue('C1', 'Forename');
		$spreadsheet->getActiveSheet()->setCellValue('D1', 'Surname');
		$spreadsheet->getActiveSheet()->setCellValue('E1', 'Address 1');
		$spreadsheet->getActiveSheet()->setCellValue('F1', 'Address 2');
		$spreadsheet->getActiveSheet()->setCellValue('G1', 'Address 3');
		$spreadsheet->getActiveSheet()->setCellValue('H1', 'Address 4');
		$spreadsheet->getActiveSheet()->setCellValue('I1', 'Address 5');
		$spreadsheet->getActiveSheet()->setCellValue('J1', 'Address 6');
		$spreadsheet->getActiveSheet()->setCellValue('K1', 'Postcode');
		$spreadsheet->getActiveSheet()->setCellValue('L1', 'U3A Name');
		$n = 2;
		$addresses = [];
		foreach ($members as $mbr)
		{
			$name = [$mbr->title, $mbr->get_initials(), $mbr->get_first_name(), $mbr->surname];
			$house = $mbr->house;
			$address = [];
			$next1 = 1;
			if (!$house)
			{
				$address[0] = $mbr->address1;
			}
			elseif (is_numeric($house))
			{
				$address[0] = $house . " " . $mbr->address1;
			}
			else
			{
				$address[0] = $house;
				$address[1] = $mbr->address1;
				$next1 = 2;
			}
			if ($mbr->address2)
			{
				$address[$next1] = $mbr->address2;
				$next1++;
			}
			if ($mbr->address3)
			{
				$address[$next1] = $mbr->address3;
			}
			if ($mbr->town)
			{
				$town = $mbr->town;
			}
			else
			{
				$town = "";
			}
			if ($mbr->postcode)
			{
				$postcode = $mbr->postcode;
			}
			$address_string = implode(",", $address) . $town . $postcode;
			if (array_key_exists($address_string, $addresses))
			{
				$mbr1 = $addresses[$address_string];
				if ($name[0] === "Mrs")
				{
					$first1 = $mbr1["name"];
					$second1 = $name;
				}
				else
				{
					$first1 = $name;
					$second1 = $mbr1["name"];
				}
				$title2 = $first1[0] && $second1[0] ? ($first1[0] . " & " . $second1[0]) : "";
				if ($second1[3] == $first1[3])
				{
					// same surname
					$name2 = [$title2, $first1[1] . " & " . $second1[1], "", $first1[3]];
				}
				else
				{
					$name2 = [$title2, $first1[1] . " & " . $second1[1], "", $first1[3] . " & " . $second1[3]];
				}
				$addresses[$address_string] = ["name" => $name2, "address" => $mbr1["address"]];
			}
			else
			{
				$addresses[$address_string] = ["name" => $name, "address" => $address, "town" => $town, "postcode" => $postcode];
			}
		}
		foreach (array_values($addresses) as $mbr)
		{
			$mbrname = $mbr["name"];
			$mbraddress = $mbr["address"];
			$c = count($mbraddress);
			$spreadsheet->getActiveSheet()->setCellValue('A' . $n, $mbrname[0]);
			if (!$mbrname[2])
			{
				$spreadsheet->getActiveSheet()->setCellValue('B' . $n, $mbrname[1]);
			}
			$spreadsheet->getActiveSheet()->setCellValue('C' . $n, $mbrname[2]);
			$spreadsheet->getActiveSheet()->setCellValue('D' . $n, $mbrname[3]);
			$spreadsheet->getActiveSheet()->setCellValue('E' . $n, $mbraddress[0]);
			if ($c > 1)
			{
				$spreadsheet->getActiveSheet()->setCellValue('F' . $n, $mbraddress[1]);
				if ($c > 2)
				{
					$spreadsheet->getActiveSheet()->setCellValue('G' . $n, $mbraddress[2]);
					if ($c > 3)
					{
						$spreadsheet->getActiveSheet()->setCellValue('H' . $n, $mbraddress[3]);
						if ($c > 4)
						{
							$spreadsheet->getActiveSheet()->setCellValue('I' . $n, $mbraddress[4]);
							$spreadsheet->getActiveSheet()->setCellValue('J' . $n, $mbr["town"]);
						}
						else
						{
							$spreadsheet->getActiveSheet()->setCellValue('I' . $n, $mbr["town"]);
						}
					}
					else
					{
						$spreadsheet->getActiveSheet()->setCellValue('H' . $n, $mbr["town"]);
					}
				}
				else
				{
					$spreadsheet->getActiveSheet()->setCellValue('G' . $n, $mbr["town"]);
				}
			}
			else
			{
				$spreadsheet->getActiveSheet()->setCellValue('F' . $n, $mbr["town"]);
			}
			$spreadsheet->getActiveSheet()->setCellValue('K' . $n, $mbr["postcode"]);
			$spreadsheet->getActiveSheet()->setCellValue('L' . $n, U3A_Information::u3a_get_u3a_name());
			$n++;
		}
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
		$spreadsheet->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
		$writer = IOFactory::createWriter($spreadsheet, "Xlsx");
		$writer->save($path);
		write_log($url);
		return $url;
	}

}

class U3A_Members_List
{

	public function write_list($members, $colnames, $colhdrs, $fmt, $filename = null)
	{
		write_log(U3A_Information::get_temp_dir());
		if (!$filename)
		{
			$filename = U3A_Information::u3a_get_u3a_name() . "U3A_addresses_" . date('Ymd');
		}
		$path = U3A_Information::get_temp_dir() . $filename . ".$fmt";
		$url = U3A_Information::get_temp_url() . $filename . ".$fmt";
		$spreadsheet = new Spreadsheet();
		for ($n = 0; $n < count($colhdrs); $n++)
		{
			$ltr = U3A_HTML_Utilities::$alphabet_upper[$n];
			if ($n < 26)
			{
				$cell = $ltr . '1';
			}
			else
			{
				$initl = U3A_HTML_Utilities::$alphabet_upper[intval($n / 26)];
				$cell = $initl . $ltr . '1';
			}
			$spreadsheet->getActiveSheet()->setCellValue($cell, $colhdrs[$n]);
			$spreadsheet->getActiveSheet()->getColumnDimension($ltr)->setAutoSize(true);
		}
		$m = 2;
		$addresses = [];
		foreach ($members as $mbr)
		{
			for ($n = 0; $n < count($colhdrs); $n++)
			{
				if ($n < 26)
				{
					$cell = U3A_HTML_Utilities::$alphabet_upper[$n] . $m;
				}
				else
				{
					$initl = U3A_HTML_Utilities::$alphabet_upper[intval($n / 26)];
					$cell = $initl . U3A_HTML_Utilities::$alphabet_upper[$n] . $m;
				}
				$field = $colnames[$n];
				$spreadsheet->getActiveSheet()->setCellValue($cell, $mbr->$field);
			}
			$m++;
		}
		$writer = IOFactory::createWriter($spreadsheet, ucfirst($fmt));
		$writer->save($path);
		write_log($url);
		return $url;
	}

}

class U3A_PDF extends FPDF
{

	public static function get_membership_card($mbr)
	{
		$pdf = new U3A_PDF();
//		$hdrpath = $pdf->get_header_file();
//		write_log($hdrpath);
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetFont('Times', '', 12);
		$pdf->Cell(0, 4, 'Membership Valid until:', 0, 1);
		$renew = $mbr->renew;
//		$validuntil = U3A_Timestamp_Utilities::end_of_year(strtotime($renew));
		$validuntil = strtotime($renew);
		$vu = date("jS F Y", $validuntil);
//		$yr = U3A_Timestamp_Utilities::year();
		$pdf->Cell(0, 4, $vu, 0, 1);
		if ($mbr->affiliation)
		{
			$pdf->Cell(0, 8, 'ASSOCIATE', 0, 1);
		}
		else
		{
			$pdf->Cell(0, 8, 'INDIVIDUAL', 0, 1);
		}
		$pdf->Cell(0, 4, $mbr->get_name(), 0, 1);
		$pdf->Cell(0, 4, "Membership Number " . $mbr->membership_number, 0, 1);
		$path = U3A_Information::get_temp_dir() . "membership_card_" . $mbr->membership_number . ".pdf";
		$url = U3A_Information::get_temp_url() . "membership_card_" . $mbr->membership_number . ".pdf";
		$pdf->Output('F', $path);
		return $path;
	}

	public function get_header_file()
	{
		$args = array(
			'post_type'			 => 'attachment',
			'name'				 => 'membership_card_header',
			'posts_per_page'	 => 1,
			'post_status'		 => 'inherit',
		);
		$_header = get_posts($args);
		$header = $_header ? array_pop($_header) : null;
		$path = $header ? get_attached_file($header->ID) : '';
		return $path;
	}

	public function Header()
	{
		//Logo
		$this->Image($this->get_header_file(), 10, 8, 60);
		$this->Ln(10);
	}

}

class U3A_PDF_Label extends FPDF
{

	public static function get_address_labels($members, $format = 'J8160')
	{
		$pdf = new U3A_PDF_Label($format);
		$mbrs = [];
		foreach ($members as $mbr)
		{
			$name = [$mbr->get_titled_initials(), $mbr->surname];
			$house = $mbr->house;
			if (!$house)
			{
				$address = "";
			}
			elseif (is_numeric($house))
			{
				$address = $house . " ";
			}
			else
			{
				$address = $house . "\n";
			}
			$address .= $mbr->address1;
			if ($mbr->address2)
			{
				$address .= "\n" . $mbr->address2;
			}
			if ($mbr->address3)
			{
				$address .= "\n" . $mbr->address3;
			}
			if ($mbr->town)
			{
				$address .= "\n" . strtoupper($mbr->town);
			}
			if ($mbr->postcode)
			{
				$address .= "\n" . $mbr->postcode;
			}
			if (array_key_exists($address, $mbrs))
			{
				$mbr1 = $mbrs[$address];
				if ($mbr1[1] == $name[1])
				{
					$mbr2 = [$name[0] . " & " . $mbr1[0], $name[1]];
				}
				else
				{
					$mbr2 = ["", $mbr1[0] . " " . $mbr1[1] . " & " . $name[0] . " " . $name[1]];
				}
				$mbrs[$address] = $mbr2;
			}
			else
			{
				$mbrs[$address] = $name;
			}
		}
		$pdf->AddPage();
		foreach ($mbrs as $address => $namebits)
		{
			$name = $namebits[0] ? ($namebits[0] . " " . $namebits[1]) : $namebits[1];
			$pdf->Add_Label($name . "\n" . $address);
		}
		$path = U3A_Information::get_temp_dir() . "address_labels_" . date('YmdHi') . ".pdf";
		$url = U3A_Information::get_temp_url() . "address_labels_" . date('YmdHi') . ".pdf";
		$pdf->Output('F', $path);
		return $url;
	}

	// Private properties
	protected $_Margin_Left;  // Left margin of labels
	protected $_Margin_Top; // Top margin of labels
	protected $_X_Space; // Horizontal space between 2 labels
	protected $_Y_Space; // Vertical space between 2 labels
	protected $_X_Number; // Number of labels horizontally
	protected $_Y_Number; // Number of labels vertically
	protected $_Width;  // Width of label
	protected $_Height;  // Height of label
	protected $_Line_Height;  // Line height
	protected $_Padding; // Padding
	protected $_Metric_Doc; // Type of metric for the document
	protected $_COUNTX;  // Current x position
	protected $_COUNTY;  // Current y position
	// List of label formats
	protected $_Avery_Labels = array(
		'5160'	 => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 1.762, 'marginTop' => 10.7, 'NX' => 3, 'NY' => 10, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 66.675, 'height' => 25.4, 'font-size' => 8),
		'5161'	 => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 0.967, 'marginTop' => 10.7, 'NX' => 2, 'NY' => 10, 'SpaceX' => 3.967, 'SpaceY' => 0, 'width' => 101.6, 'height' => 25.4, 'font-size' => 8),
		'5162'	 => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 0.97, 'marginTop' => 20.224, 'NX' => 2, 'NY' => 7, 'SpaceX' => 4.762, 'SpaceY' => 0, 'width' => 100.807, 'height' => 35.72, 'font-size' => 8),
		'5163'	 => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 1.762, 'marginTop' => 10.7, 'NX' => 2, 'NY' => 5, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 101.6, 'height' => 50.8, 'font-size' => 8),
		'5164'	 => array('paper-size' => 'letter', 'metric' => 'in', 'marginLeft' => 0.148, 'marginTop' => 0.5, 'NX' => 2, 'NY' => 3, 'SpaceX' => 0.2031, 'SpaceY' => 0, 'width' => 4.0, 'height' => 3.33, 'font-size' => 12),
		'8600'	 => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 7.1, 'marginTop' => 19, 'NX' => 3, 'NY' => 10, 'SpaceX' => 9.5, 'SpaceY' => 3.1, 'width' => 66.6, 'height' => 25.4, 'font-size' => 8),
		'L7163'	 => array('paper-size' => 'A4', 'metric' => 'mm', 'marginLeft' => 5, 'marginTop' => 15, 'NX' => 2, 'NY' => 7, 'SpaceX' => 25, 'SpaceY' => 0, 'width' => 99.1, 'height' => 38.1, 'font-size' => 9),
		'J8160'	 => array('paper-size' => 'A4', 'metric' => 'mm', 'marginLeft' => 8, 'marginTop' => 14, 'NX' => 3, 'NY' => 7, 'SpaceX' => 2.5, 'SpaceY' => 0, 'width' => 63.5, 'height' => 38.1, 'font-size' => 11),
		'3422'	 => array('paper-size' => 'A4', 'metric' => 'mm', 'marginLeft' => 0, 'marginTop' => 8.5, 'NX' => 3, 'NY' => 8, 'SpaceX' => 0, 'SpaceY' => 0, 'width' => 70, 'height' => 35, 'font-size' => 9)
	);

	// Constructor
	function __construct($format, $unit = 'mm', $posX = 1, $posY = 1)
	{
		if (is_array($format))
		{
			// Custom format
			$Tformat = $format;
		}
		else
		{
			// Built-in format
			if (!isset($this->_Avery_Labels[$format]))
				$this->Error('Unknown label format: ' . $format);
			$Tformat = $this->_Avery_Labels[$format];
		}

		parent::__construct('P', $unit, $Tformat['paper-size']);
		$this->_Metric_Doc = $unit;
		$this->_Set_Format($Tformat);
		$this->SetFont('Arial');
		$this->SetMargins(0, 0);
		$this->SetAutoPageBreak(false);
		$this->_COUNTX = $posX - 2;
		$this->_COUNTY = $posY - 1;
	}

	function _Set_Format($format)
	{
		$this->_Margin_Left = $this->_Convert_Metric($format['marginLeft'], $format['metric']);
		$this->_Margin_Top = $this->_Convert_Metric($format['marginTop'], $format['metric']);
		$this->_X_Space = $this->_Convert_Metric($format['SpaceX'], $format['metric']);
		$this->_Y_Space = $this->_Convert_Metric($format['SpaceY'], $format['metric']);
		$this->_X_Number = $format['NX'];
		$this->_Y_Number = $format['NY'];
		$this->_Width = $this->_Convert_Metric($format['width'], $format['metric']);
		$this->_Height = $this->_Convert_Metric($format['height'], $format['metric']);
		$this->Set_Font_Size($format['font-size']);
		$this->_Padding = $this->_Convert_Metric(3, 'mm');
	}

	// convert units (in to mm, mm to in)
	// $src must be 'in' or 'mm'
	function _Convert_Metric($value, $src)
	{
		$dest = $this->_Metric_Doc;
		if ($src != $dest)
		{
			$a['in'] = 39.37008;
			$a['mm'] = 1000;
			return $value * $a[$dest] / $a[$src];
		}
		else
		{
			return $value;
		}
	}

	// Give the line height for a given font size
	function _Get_Height_Chars($pt)
	{
		// 11pt changed from 6
		$a = array(6 => 2, 7 => 2.5, 8 => 3, 9 => 4, 10 => 5, 11 => 5, 12 => 7, 13 => 8, 14 => 9, 15 => 10);
		if (!isset($a[$pt]))
			$this->Error('Invalid font size: ' . $pt);
		return $this->_Convert_Metric($a[$pt], 'mm');
	}

	// Set the character size
	// This changes the line height too
	function Set_Font_Size($pt)
	{
		$this->_Line_Height = $this->_Get_Height_Chars($pt);
		$this->SetFontSize($pt);
	}

	// Print a label
	function Add_Label($text)
	{
		$this->_COUNTX++;
		if ($this->_COUNTX == $this->_X_Number)
		{
			// Row full, we start a new one
			$this->_COUNTX = 0;
			$this->_COUNTY++;
			if ($this->_COUNTY == $this->_Y_Number)
			{
				// End of page reached, we start a new one
				$this->_COUNTY = 0;
				$this->AddPage();
			}
		}

		$_PosX = $this->_Margin_Left + $this->_COUNTX * ($this->_Width + $this->_X_Space) + $this->_Padding;
		$_PosY = $this->_Margin_Top + $this->_COUNTY * ($this->_Height + $this->_Y_Space) + $this->_Padding;
		$this->SetXY($_PosX, $_PosY);
		$this->MultiCell($this->_Width - $this->_Padding, $this->_Line_Height, $text, 0, 'L');
	}

	function _putcatalog()
	{
		parent::_putcatalog();
		// Disable the page scaling option in the printing dialog
		$this->_put('/ViewerPreferences <</PrintScaling /None>>');
	}

}
