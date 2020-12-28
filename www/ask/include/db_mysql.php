<?php
/*
	[CYASK] (C)2009 Cyask.com
	Revision: 3.2
	Date: 2009-1-19
	Author: zhaoshunyao
	QQ: 240508015
*/

if(!defined('IN_CYASK'))
{
	exit('Access Denied');
}

class db_sql
{
	var $querynum = 0;
	var $link;
	var $dbhost;
	var $dbuser;
	var $dbpw;
	var $dbcharset;
	var $pconnect;
	
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset = '', $pconnect = 0)
	{
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		$this->$dbcharset = $dbcharset;
		$this->pconnect = $pconnect;
		
		if($pconnect) 
		{
			if(!$this->link = mysql_pconnect($dbhost, $dbuser, $dbpw)) 
			{
				$this->halt('Can not connect to MySQL server');
			}
		} 
		else 
		{
			if(!$this->link = mysql_connect($dbhost, $dbuser, $dbpw)) 
			{
				$this->halt('Can not connect to MySQL server');
			}
		}
	
		if($this->version() > '4.1') 
		{
			if($dbcharset) 
			{
				mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->link);
			}

			if($this->version() > '5.0.1') 
			{
				mysql_query("SET sql_mode=''", $this->link);
			}
		}
		
		if($dbname) 
		{
			mysql_select_db($dbname, $this->link);
		}
	}

	function select_db($dbname)
	{
		return mysql_select_db($dbname, $this->link);
	}
	
	function escape_string($str)
	{
		return mysql_real_escape_string($str, $this->link);
	}
	
	function query($sql, $type = '') 
	{
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') 
		{
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querynum++;
		return $query;
	}
	
	function fetch_array($query, $result_type = MYSQL_ASSOC)
	{
		return mysql_fetch_array($query, $result_type);
	}
	
	function result($query, $row)
	{
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query)
	{
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query)
	{
		return mysql_num_fields($query);
	}

	function free_result($query)
	{
		mysql_free_result($query);
	}

	function insert_id()
	{
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query)
	{
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query)
	{
		return mysql_fetch_field($query);
	}

	function version()
	{
		return mysql_get_server_info($this->link);
	}
	
	function close()
	{
		return mysql_close($this->link);
	}

	function error() 
	{
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() 
	{
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}
	
	function halt($message = '', $sql = '')
	{
		require_once CYASK_ROOT.'./include/db_mysql_error.php';
	}
}

?>