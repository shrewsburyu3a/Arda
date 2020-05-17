<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require 'vendor/autoload.php';

use Mailgun\Mailgun;

class U3A_Mailing_List_Member
{

	private $_name;
	private $_email;
	private $_membership_number;

	public function __construct($name, $email, $membership_number)
	{
		$this->_name = $name;
		$this->_email = $email;
		$this->_membership_number = $membership_number;
	}

	public function name()
	{
		return $this->_name;
	}

	public function email()
	{
		return $this->_email;
	}

	public function membership_number()
	{
		return $this->_membership_number;
	}

}

class U3A_Mailing_List
{

	private $_name;
	private $_email;
	private $_description;
	private $_members;

	public function __construct($name, $email, $description, $members = null)
	{
		$this->_name = $name;
		$this->_email = $email;
		$this->_description = $description;
		$this->_members = $members;
	}

	public function name()
	{
		return $this->_name;
	}

	public function description()
	{
		return $this->_description;
	}

	public function email()
	{
		return $this->_email;
	}

	public function members()
	{
		if (!$this->_members)
		{
			$mailer = U3A_Mail::get_the_mailer();
			$this->_members = $mailer->mailing_list_members($this->_email);
		}
		return $this->_members;
	}

	public function size()
	{
		return count(array_values($this->members()));
	}

	public function has_member($email)
	{
		return array_key_exists($email, $this->members());
	}

	public function add_member($member)
	{
		$ret = false;
		if ($member)
		{
			$email = $member->email();
			if (!$this->has_member($email))
			{
				$this->_members[$email] = $member;
				$ret = true;
			}
		}
		return $ret;
	}

}

class U3A_Mail
{

	private static $publicapikey = "pubkey-d48f79b43644e3bd675e53ca63cc9403";
	private static $privateapikey = "6a0dc783048ce353fa48c7f46ca98a32-acb0b40c-c3e8208d";
	private static $webhookkey = "6a0dc783048ce353fa48c7f46ca98a32-acb0b40c-c3e8208d";
	private static $apiurl = "https://api.eu.mailgun.net/v3/mg.shrewsburyu3a.org.uk";
	private static $the_mailer = null;

	const MAX_ATTACHMENTS = 20;

	public static function get_audit_mailbox()
	{
		return get_option("audit_mailbox");
	}

	public static function get_no_reply_mailbox()
	{
		return get_option("no_reply_mailbox");
	}

	public static function get_the_mailer()
	{
		if (!self::$the_mailer)
		{
			self::$the_mailer = new U3A_Mail();
		}
		return self::$the_mailer;
	}

	private $_mailgun;
	private $_mailing_lists = null;

	private function __construct()
	{
		$this->_mailgun = Mailgun::create(self::$privateapikey, self::$apiurl);
	}

	public function get_mailing_lists()
	{
		if (!$this->_mailing_lists)
		{
			$this->_mailing_lists = $this->list_mailing_lists();
		}
		return $this->_mailing_lists;
	}

	private function get_name_from_address($address)
	{
		$ret = "";
		if ($address)
		{
			$address_bits = explode("@", $address);
			$ret = $address_bits[0];
		}
		return $ret;
	}

	public function mailing_list_exists($email)
	{
		return array_key_exists($email, $this->get_mailing_lists());
	}

	public function get_mailing_list($email)
	{
		$ret = null;
		$mlists = $this->get_mailing_lists();
		if (array_key_exists($email, $mlists))
		{
			$ret = $mlists["$email"];
		}
		return $ret;
	}

	private function list_mailing_lists()
	{
		$ret = null;
		try
		{
			$ret = [];
			$response = $this->_mailgun->mailingList()->pages();
//			write_log($response);
			$items = $response->getLists();
			foreach ($items as $item)
			{
				$email = $item->getAddress();
				$ml = new U3A_Mailing_List($item->getName(), $email, $item->getDescription());
//				$ml["name"] = $item->getName();
//				$ml["address"] = $item->getAddress();
//				$ml["description"] = $item->getDescription();
//				$ml["count"] = $item->getMembersCount();
//				$nm = $this->get_name_from_address($ml["address"]);
				$ret[$email] = $ml;
			}
			$nextpage = $response->getNextUrl();
			write_log($nextpage);
			$results = wp_remote_get($nextpage, [ "headers" => [ "Authorization" => "Basic " . base64_encode("api:" . self::$privateapikey)]]);
			$body = json_decode($results["body"]);
			write_log(count($body->items));
			$count = 0;
			while ($count < 10 && $body->items)
			{
//			write_log($body->items);
				foreach ($body->items as $item)
				{
					$email = $item->address;
					$ml = new U3A_Mailing_List($item->name, $email, $item->description);
//					$ml = [];
//					$ml["name"] = $item->name;
//					$ml["address"] = $item->address;
//					$ml["description"] = $item->description;
//					$ml["count"] = $item->members_count;
////					$nm = explode("@", $ml["address"])[0];
					$ret[$email] = $ml;
				}
				// do something with items
				$nextpage = $body->paging->next;
				$results = wp_remote_get($nextpage, [ "headers" => [ "Authorization" => "Basic " . base64_encode("api:" . self::$privateapikey)]]);
				$body = json_decode($results["body"]);
//				write_log($nextpage);
				$count++;
			}
//			write_log("returning");
//			write_log($ret);
		}
		catch (Exception $ex)
		{
			write_log($ex);
		}
		return $ret;
	}

	/**
	 *
	 * @param type $name mailing list name
	 * @param type $description mailing list description
	 * @param type $address mailing list email address
	 * @param type $listmembers an array of U3A_Mailing_List_Member
	 */
	public function create_mailing_list($name, $description, $address, $listmembers)
	{
		$ret = null;
		try
		{
			$result1 = $this->_mailgun->mailingList()->create($address, $name, $description, "members");
			$members = [];
			foreach ($listmembers as $email => $lm)
			{
				$members[] = ['address' => $lm->email(), 'name' => $lm->name(), $vars => ["id" => $lm->membership_number()]];
				$result2 = $this->_mailgun->mailingList()->member()->createMultiple($address, $members);
			}
			$this->update_reply_preference($address);
			$ret = new U3A_Mailing_List($name, $address, $description);
		}
		catch (Exception $ex)
		{
			write_log($ex);
		}
		return $ret;
	}

	public function delete_mailing_list($address)
	{
		$ret = false;
		try
		{
			$result = $this->_mailgun->mailingList()->delete($address);
			$ret = true;
		}
		catch (Exception $ex)
		{
			write_log($ex);
		}
		return $ret;
	}

	public function mailing_list_members($mailing_list)
	{
		$response = $this->_mailgun->mailingList()->member()->index($mailing_list, 1000);
		write_log($response);
		$items = $response->getItems();
		$ret = [];
		foreach ($items as $item)
		{
			$vars = $item->getVars();
			if ($vars && isset($vars["id"]))
			{
				$mnum = intval($vars["id"]);
			}
			else
			{
				$mnum = 0;
			}
			$email = $item->getAddress();
			$ret[$email] = new U3A_Mailing_List_Member($item->getName(), $email, $mnum);
		}
		write_log($ret);
		return $ret;
//		$nextResponse = $this->_mailgun->mailingList()->member()->nextPage($response);
//		write_log($nextResponse);
	}

	public function add_member_to_list($member, $mailing_list)
	{
		if ($mailing_list->add_member($member))
		{
			$this->_mailgun->mailingList()->member()->create($mailing_list->email(), $member->email(), $member->name(), ["id" => $member->membership_number()]);
		}
//create(string $list, string $address, string $name = null, array $vars = [], bool $subscribed = true, bool $upsert = false)
	}

	public function update_member_on_list($member, $email, $mailing_list)
	{
		$mbremail = is_string($member) ? $member : $member->email();
		$mlemail = is_string($mailing_list) ? $mailing_list : $mailing_list->email();
		$this->_mailgun->mailingList()->member()->update($mlemail, $email, ["name" => $member->name(), "address" => $mbremail, "vars" => ["id" => $member->membership_number()]]);
	}

	public function update_reply_preference($mailing_list)
	{
		if (is_string($mailing_list))
		{
			$this->_mailgun->mailingList()->update($mailing_list, ["reply_preference" => "sender"]);
		}
		else
		{
			$this->_mailgun->mailingList()->update($mailing_list->email(), ["reply_preference" => "sender"]);
		}
	}

	public function update_all_reply_preferences()
	{
		$response = $this->_mailgun->mailingList()->pages();
		$items = $response->getLists();
		foreach ($items as $item)
		{
			$email = $item->getAddress();
			$this->update_reply_preference($email);
		}
	}

	public function remove_member_from_list($member, $mailing_list)
	{
		$mbremail = is_string($member) ? $member : $member->email();
		$mlemail = is_string($mailing_list) ? $mailing_list : $mailing_list->email();
		$this->_mailgun->mailingList()->member()->delete($mlemail, $mbremail);
	}

	private function add_to_headers(&$headers, $hdrname, $hdr)
	{
		if ($hdr)
		{
			if (is_string($hdr))
			{
				$headers[] = "$hdrname: " . $hdr;
			}
			elseif (is_array($hdr))
			{
				foreach ($hdr as $hdr_item)
				{
					$headers[] = "$hdrname: " . $hdr_item;
				}
			}
		}
	}

	public function sendmail($to, $subject, $contents, $cc = null, $bcc = null, $from = null, $reply_to = null, $attachments = null, $html = true)
	{
		if ($html)
		{
			$headers = ['Content-Type: text/html; charset=UTF-8'];
			$contents_array = explode("\n", stripslashes($contents));
			$newcontents = "<p>" . implode("</p><p>", $contents_array) . "</p>";
//			$newcontents = stripslashes(str_replace("\n", "<br/>", $contents));
		}
		else
		{
			$headers = [];
			$newcontents = stripslashes($contents);
		}
		$this->add_to_headers($headers, "From", $from);
		$this->add_to_headers($headers, "Cc", $cc);
		$this->add_to_headers($headers, "Bcc", $bcc);
		$this->add_to_headers($headers, "Reply-to", $reply_to);
//		$attachments = [];
		$testing = intval(get_option("u3a_testing_email", "0"));
		if ($testing)
		{
			write_log("u3a_testing_email: $testing");
			write_log($to);
			write_log($subject);
			write_log($contents);
			write_log($attachments);
			write_log("headers");
			write_log($headers);
			$ret = false;
		}
		else
		{
			$ret = wp_mail($to, stripslashes($subject), $newcontents, $headers, $attachments);
		}
		return $ret;
	}

}
