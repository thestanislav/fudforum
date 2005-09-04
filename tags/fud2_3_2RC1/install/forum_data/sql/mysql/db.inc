<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: db.inc,v 1.12 2002/09/09 23:47:47 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

if( !function_exists("fud_sql_error_handler") ) fud_use('err.inc'); 

if ( !defined('_db_connection_ok_') ) {
	$connect_func = ( $GLOBALS['DBHOST_PERSIST'] == 'Y' ) ? 'mysql_pconnect' : 'mysql_connect';
		
	$GLOBALS['__DB_INC__']['SQL_LINK'] = $connect_func($GLOBALS['DBHOST'], $GLOBALS['DBHOST_USER'], $GLOBALS['DBHOST_PASSWORD'])
		or die (fud_sql_error_handler("Initiating $connect_func", mysql_error($GLOBALS['__DB_INC__']['SQL_LINK']), mysql_errno($GLOBALS['__DB_INC__']['SQL_LINK']), "Unknown"));
		
	mysql_select_db($GLOBALS['DBHOST_DBNAME'],$GLOBALS['__DB_INC__']['SQL_LINK'])
		or die (fud_sql_error_handler("Opening database ".$GLOBALS['DBHOST_DBNAME'], mysql_error($GLOBALS['__DB_INC__']['SQL_LINK']), mysql_errno($GLOBALS['__DB_INC__']['SQL_LINK']), q_singleval("SELECT VERSION()")));
		
	define('_db_connection_ok_', 1); 
	define('__dbtype__', 'mysql');
}

function yn($val) 
{
	return ( strlen($val) && strtolower($val) != 'n' ) ? 'Y' : 'N';
} 

function intnull($val)
{
	return ( strlen($val) ) ? $val : 'NULL';
}

function intzero($val)
{
	return ( !empty($val) ) ? $val : '0';
}

function ifnull($val, $alt)
{
	return ( strlen($val) ) ? "'".$val."'" : $alt;
}

function strnull($val)
{
	return ( strlen($val) ) ? "'".$val."'" : 'NULL';
}

function db_lock($tables)
{
	if ( !empty($GLOBALS['__DB_INC_INTERNALS__']['db_locked']) ) {
		fud_sql_error_handler("Recursive Lock", "internal", "internal", q_singleval("SELECT VERSION()"));
	}

	$tables = str_replace("\t", '', $tables);
	
	$tbl_arr = explode(',', $tables);
	$tbl_n = count($tbl_arr);
	
	$sql_str='';
	for ( $i=0; $i<$tbl_n; $i++ ) {
		$tbl_arr[$i] = trim($tbl_arr[$i]);
		if ( substr($tbl_arr[$i], -1) == '+' ) {
			$mode = ' WRITE';
			$tbl_arr[$i] = substr($tbl_arr[$i], 0, strlen($tbl_arr[$i])-1);
		}
		else {
			$mode = ' READ';
		}
		$sql_str .= ' '.$tbl_arr[$i].$mode.',';
	}
	
	$sql_str = substr($sql_str, 0, strlen($sql_str)-1);
	
	q("LOCK TABLES".$sql_str);
	
	$GLOBALS['__DB_INC_INTERNALS__']['db_locked'] = 1;	
}

function db_unlock()
{
	q('UNLOCK TABLES',$GLOBALS['__DB_INC__']['SQL_LINK']);
	
	if ( !isset($GLOBALS['__DB_INC_INTERNALS__']['db_locked']) ) {
		fud_sql_error_handler("DB_UNLOCK: no previous lock established", "internal", "internal", q_singleval("SELECT VERSION()"));
	}
	
	if ( --$GLOBALS['__DB_INC_INTERNALS__']['db_locked'] < 0 ) {
		fud_sql_error_handler("DB_UNLOCK: unlock overcalled", "internal", "internal", q_singleval("SELECT VERSION()"));
	}
}

function db_locked()
{
	return isset($GLOBALS['__DB_INC_INTERNALS__']['db_locked'])?$GLOBALS['__DB_INC_INTERNALS__']['db_locked']:NULL;
}

function db_affected()
{
	return mysql_affected_rows($GLOBALS['__DB_INC__']['SQL_LINK']);	
}

if( !defined("fud_query_stats") ) {
	function q($query) 
	{
		$result = mysql_query($query,$GLOBALS['__DB_INC__']['SQL_LINK']) 
			or die (fud_sql_error_handler($query, mysql_error($GLOBALS['__DB_INC__']['SQL_LINK']), mysql_errno($GLOBALS['__DB_INC__']['SQL_LINK']), q_singleval("SELECT VERSION()")));
	
		$GLOBALS['__DB_INC_INTERNALS__']['last_query'] = $query;
		
		if( defined('debug') ) 
			echo "--<br>\n<b>(--</b>&nbsp;&nbsp; ".htmlspecialchars($query)." &nbsp;&nbsp;<b>--)</b><br><br>\n\n";
	
		return $result; 
	}	
}
else {
	function q($query)
	{
		if ( !isset($GLOBALS['__DB_INC_INTERNALS__']['query_count']) )
			$GLOBALS['__DB_INC_INTERNALS__']['query_count'] = 1;
		else 
			++$GLOBALS['__DB_INC_INTERNALS__']['query_count'];
	
		if ( !isset($GLOBALS['__DB_INC_INTERNALS__']['total_sql_time']) ) $GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] = 0;
	
		$ts = db_getmicrotime();

		$result = mysql_query($query,$GLOBALS['__DB_INC__']['SQL_LINK']) 
			or die (fud_sql_error_handler($query, mysql_error($GLOBALS['__DB_INC__']['SQL_LINK']), mysql_errno($GLOBALS['__DB_INC__']['SQL_LINK']), q_singleval("SELECT VERSION()")));
		
		$te = db_getmicrotime(); 
	
		$GLOBALS['__DB_INC_INTERNALS__']['last_time'] = $te-$ts;
		$GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] += $GLOBALS['__DB_INC_INTERNALS__']['last_time'];
		$GLOBALS['__DB_INC_INTERNALS__']['last_query'] = $query;
		
		if( defined('debug') ) 
			echo "--<br>\n<b>(--</b>&nbsp;&nbsp; ".htmlspecialchars($query)." &nbsp;&nbsp;<b>--) <b>(</b>".$GLOBALS['__DB_INC_INTERNALS__']['last_time']."<b>)</b></b><br><br>\n\n";
	
		return $result; 
	}
}

function qf(&$result)
{
	unset($result);
}

function query_count()
{
	return $GLOBALS['__DB_INC_INTERNALS__']['query_count'];
}

function last_query($filter='')
{
	if ( $filter ) 
		return str_replace("\t", "", str_replace("\n", " ", $GLOBALS['__DB_INC_INTERNALS__']['last_query']));
	else
		return $GLOBALS['__DB_INC_INTERNALS__']['last_query'];
}

function last_time()
{
	return $GLOBALS['__DB_INC_INTERNALS__']['last_time'];
}

function total_time()
{
	return $GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'];
}

function db_count($result)
{
	if ( $n=@mysql_num_rows($result) ) 
		return $n;
	else
		return 0;
}

function db_lastid($dummy=FALSE, $dummy2=FALSE)
{
	return mysql_insert_id($GLOBALS['__DB_INC__']['SQL_LINK']);
}

function db_seek($result,$pos)
{
	return mysql_data_seek($result,$pos);
}
function db_rowobj($result)
{
	return mysql_fetch_object($result);
}

function db_rowarr($result)
{
	return mysql_fetch_row($result);
}

function bq($query)
{
	$res = q($query);
	if ( is_result($res) ) { qf($res); return 1; }
	return 0;
}

function qobj($qry, &$obj)
{
	$r = q($qry);
	$robj = db_singleobj($r);
	if ( !$robj ) return;

	foreach($robj as $k => $v) $obj->{$k} = $v;
	
	return $robj;
}

function is_result($res)
{
	if ( db_count($res) ) 
		return $res;
	
	qf($res);

	return;
}

function db_singleobj($res)
{
	$obj = db_rowobj($res);
	qf($res);
	return $obj;
}

function db_singlearr($res)
{
	$arr = db_rowarr($res);
	qf($res);
	return $arr;
}

function q_singleval($query)
{
	$r = q($query);
	if( !is_result($r) ) return;
	
	list($val) = db_singlearr($r);
	
	return $val;
}

function get_field_list($tbl)
{
	$r = q("show fields from ".$tbl);
	return $r;
}

function qry_limit($limit, $off)
{
	return $off.','.$limit;
}

function get_fud_table_list()
{
	$ret = array();
	$r = q("show tables LIKE '".$GLOBALS['DBHOST_TBL_PREFIX']."%'");
	while( list($name) = db_rowarr($r) ) $ret[] = $name;
	qf($r);
	
	return $ret;	
}

function optimize_tables($tbl_list=NULL)
{
	if( !$tbl_list ) $tbl_list = get_fud_table_list();

	foreach($tbl_list as $v) q("OPTIMIZE TABLE ".$v);
}
?>