<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("u3a_base_classes.php");

class U3A_CONFIG extends U3A_Object
{

	private static $_the_config = null;

	public static function get_the_config()
	{
		if (self::$_the_config === null)
		{
			self::$_the_config = new U3A_Config();
		}
		return self::$_the_config;
	}

	public static function u3a_get_as_timestamp($config_value, $year_modifier = 0)
	{
		$cfg = self::get_the_config();
		$the_year = $year_modifier > 2000 ? $year_modifier : (U3A_Timestamp_Utilities::year() + $year_modifier);
		$the_date = $the_year . '-' . $cfg->$config_value;
		return strtotime($the_date);
	}

	public static function u3a_get_formatted_date($config_value, $date_format = null, $year_modifier = 0)
	{
		$cfg = self::get_the_config();
		$the_year = U3A_Timestamp_Utilities::year() + $year_modifier;
		$the_date = $the_year . '-' . $cfg->$config_value;
		if ($date_format)
		{
			$tm = strtotime($the_date);
			$ret = date($date_format, $tm);
		}
		else
		{
			$ret = $the_date;
		}
		return $ret;
	}

	public function __construct()
	{
		parent::__construct();
		$contents = file(dirname(__FILE__) . '/config.txt', FILE_IGNORE_NEW_LINES);
		$live = [];
		$test = [];
		foreach ($contents as $line)
		{
			if ($line[0] === '#')
			{
				if (U3A_Utilities::starts_with($line, '#live:'))
				{
					$live[] = substr($line, 6);
				}
				elseif (U3A_Utilities::starts_with($line, '#test:'))
				{
					$test[] = substr($line, 6);
				}
			}
			else
			{
				$eq = strpos($line, '=');
				if ($eq)
				{
					$p = trim(substr($line, 0, $eq));
					$v = trim(substr($line, $eq + 1));
					$this->_data[$p] = $v;
//				$l = explode('=', $line);
//					$this->_data[$l[0]] = $l[1];
				}
			}
		}
		$usethis1 = $this->_data["DOMAIN_NAME"] === $_SERVER["SERVER_NAME"] ? $live : $test;
		if ($usethis1)
		{
			foreach ($usethis1 as $line)
			{
				$eq = strpos($line, '=');
				if ($eq)
				{
					$p = trim(substr($line, 0, $eq));
					$v = trim(substr($line, $eq + 1));
					$this->_data[$p] = $v;
//				$l = explode('=', $line);
//					$this->_data[$l[0]] = $l[1];
				}
			}
		}
//		write_log($this->_data);
//		write_log($_SERVER);
	}

}
