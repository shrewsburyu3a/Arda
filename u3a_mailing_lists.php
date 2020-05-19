<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'u3a_mail.php';

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
