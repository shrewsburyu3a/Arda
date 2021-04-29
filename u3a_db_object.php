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

define('__ROOT__', dirname(__FILE__));

class U3ADatabaseObject
{

	private $thedb;

	public function __construct()
	{
		global $wpdb;
		$this->thedb = $wpdb;
	}

	public function query($sql)
	{
		return $this->thedb->get_results($sql);
	}

	/**
	 * This global function loads the first field of the first row returned by the query.
	 *
	 * @param string The SQL query
	 * @return The value returned in the query or null if the query failed.
	 */
	public function loadResult($sql)
	{
		return $this->thedb->get_var($sql);
	}

	/**
	 * This global function return a result row as an associative array
	 *
	 * @param string The SQL query
	 * @param array An array for the result to be return in
	 * @return <b>True</b> is the query was successful, <b>False</b> otherwise
	 */
	public function loadHash($sql, &$hash)
	{
		$ret = $this->thedb->get_row($sql, ARRAY_A);
		if ($ret)
		{
			foreach ($ret as $k => $v)
			{
				$hash[$k] = $v;
			}
		}
		return $ret;
	}

	/**
	 * Document::db_loadList()
	 *
	 * { Description }
	 *
	 * @param [type] $maxrows
	 */
	public function loadList($sql)
	{
		return $this->thedb->get_results($sql, ARRAY_A);
	}

	/**
	 * Document::db_loadColumn()
	 *
	 * { Description }
	 *
	 * @param [type] $maxrows
	 */
	public function loadColumn($sql)
	{
		return $this->thedb->get_col($sql);
	}

	/**
	 * Document::db_insertArray()
	 *
	 * { Description }
	 *
	 * @param [type] $verbose
	 */
	public function insertArray($table, &$hash)
	{
//		write_log("insert $table", $hash);
		$this->thedb->insert($table, $hash);
		return $this->thedb->insert_id;
	}

	/**
	 * Document::db_updateArray()
	 *
	 * { Description }
	 *
	 * @param [type] $verbose
	 */
	public function updateArray($table, &$hash, $keyName)
	{
//		write_log("update $table $keyname", $hash);
		$hash1 = [];
		$where = [];
		$tabformat = [];
		$whereformat = [];
		foreach ($hash as $k => $v)
		{
			if ($k == $keyName)
			{
				$where[$keyName] = $v;
				if (is_numeric($v))
				{
					$whereformat[] = "%d";
				}
				else
				{
					$whereformat[] = "%s";
				}
			}
			else
			{
				$hash1[$k] = $v;
				if (is_numeric($v))
				{
					$tabformat[] = "%d";
				}
				else
				{
					$tabformat[] = "%s";
				}
			}
		}
		if (count($where) > 0)
		{
			$this->thedb->update($table, $hash1, $where, $tabformat, $whereformat);
		}
	}

	public function delete($table, $where)
	{
		if (count($where) > 0)
		{
			$this->thedb->delete($table, $where);
//			echo $table . " \n";
//			var_dump($where);
		}
		return true;
	}

}

?>
