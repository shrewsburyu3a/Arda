<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require 'vendor/autoload.php';
require_once 'u3a_config.php';
require_once 'u3a_mailing_lists.php';

use Mailgun\Mailgun;

class U3A_Mail
{

	private static $publicapikey = null;
	private static $privateapikey = null;
	private static $webhookkey = null;
	private static $apiurl = null;
	private static $the_mailer = null;

	const MAX_ATTACHMENTS = 20;
	const MAX_CC = 200;

	private static function get_public_api_key()
	{
		if (!self::$publicapikey)
		{
			self::$publicapikey = U3A_CONFIG::get_the_config()->PUBLIC_API_KEY;
		}
		return self::$publicapikey;
	}

	private static function get_private_api_key()
	{
		if (!self::$privateapikey)
		{
			self::$privateapikey = U3A_CONFIG::get_the_config()->PRIVATE_API_KEY;
		}
		return self::$privateapikey;
	}

	private static function get_webhook_key()
	{
		if (!self::$webhookkey)
		{
			self::$webhookkey = U3A_CONFIG::get_the_config()->WEBHOOK_KEY;
		}
		return self::$webhookkey;
	}

	private static function get_api_url()
	{
		if (!self::$apiurl)
		{
			self::$apiurl = U3A_CONFIG::get_the_config()->API_URL;
		}
		return self::$apiurl;
	}

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
		$this->_mailgun = Mailgun::create(self::get_private_api_key(), self::get_api_url());
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
			$results = wp_remote_get($nextpage, [ "headers" => [ "Authorization" => "Basic " . base64_encode("api:" . self::get_private_api_key())]]);
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
				$results = wp_remote_get($nextpage, [ "headers" => [ "Authorization" => "Basic " . base64_encode("api:" . self::get_private_api_key())]]);
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
		$ret = 0;
		if (is_array($cc) && count($cc) > self::MAX_CC)
		{
			$ccs = U3A_Utilities::chop_array($cc, self::MAX_CC);
			write_log("sending split " . count($cc) . " cc in " . count($ccs) . " lots of " . self::MAX_CC);
			foreach ($ccs as $ccc)
			{
				if ($this->sendmail1($to, $subject, $contents, $ccc, $bcc, $from, $reply_to, $attachments, $html))
				{
					$ret += count($ccc);
					sleep(1);
				}
				else
				{
					write_log("not sent");
					write_log($ccc);
				}
			}
		}
		elseif ((is_array($bcc) && count($bcc) > self::MAX_CC))
		{
			$bccs = U3A_Utilities::chop_array($bcc, self::MAX_CC);
			write_log("sending split " . count($bcc) . " bcc in " . count($bccs) . " lots of " . self::MAX_CC);
			foreach ($bccs as $bccc)
			{
				if ($this->sendmail1($to, $subject, $contents, $cc, $bccc, $from, $reply_to, $attachments, $html))
				{
					$ret += count($bccc);
					sleep(1);
				}
				else
				{
					write_log("not sent");
					write_log($bccc);
				}
			}
		}
		else
		{
			write_log("sending all");
			if (!$this->sendmail1($to, $subject, $contents, $cc, $bcc, $from, $reply_to, $attachments, $html))
			{
				write_log("cc", $cc);
				write_log("bcc", $bcc);
			}
			else
			{
				if (is_array($bcc))
				{
					$ret += count($bcc);
				}
				elseif (is_array($cc))
				{
					$ret += count($cc);
				}
				else
				{
					$ret = 1;
				}
			}
		}
		return $ret;
	}

	private function sendmail1($to, $subject, $contents, $cc = null, $bcc = null, $from = null, $reply_to = null, $attachments = null, $html = true)
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
