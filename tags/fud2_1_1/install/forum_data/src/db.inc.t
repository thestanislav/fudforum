<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: db.inc.t,v 1.2 2002/06/18 18:26:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

if ( !function_exists('error_handler') ) fud_use('err.inc');

if ( !defined('_db_connection_ok_') ) {
	$connect_func = ( $GLOBALS['MYSQL_PERSIST'] == 'Y' ) ? 'mysql_pconnect' : 'mysql_connect';
		
	if ( !($GLOBALS['__DB_INC__']['SQL_LINK']=$connect_func($GLOBALS['MYSQL_SERVER'], $GLOBALS['MYSQL_LOGIN'], $GLOBALS['MYSQL_PASSWORD'])) ) {
		error_handler("db.inc", "unable to establish mysql connection on ".$GLOBALS['MYSQL_SERVER'], 0);
	}
		
	if ( !@mysql_select_db($GLOBALS['MYSQL_DB'],$GLOBALS['__DB_INC__']['SQL_LINK']) ) {
		error_handler("db.inc", "unable to connect to database", 0);
	}
		
	define('_db_connection_ok_', 1); 
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
		exit("recursive lock");
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
	$query = "LOCK TABLES".$sql_str;
	
	if ( !q($query) ) {
		exit("db_lock() error (".mysql_error($GLOBALS['__DB_INC__']['SQL_LINK']).")\n"); 
	}
	
	$GLOBALS['__DB_INC_INTERNALS__']['db_locked'] = 1;	
}

function db_unlock()
{
	if ( !q('UNLOCK TABLES',$GLOBALS['__DB_INC__']['SQL_LINK']) ) {
		exit("DB_UNLOCK FAILED\n");
	}
	
	if ( !isset($GLOBALS['__DB_INC_INTERNALS__']['db_locked']) ) {
		exit("DB_UNLOCK: no previous lock established\n");
	}
	
	if ( --$GLOBALS['__DB_INC_INTERNALS__']['db_locked'] < 0 ) {
		exit("DB_UNLOCK: unlock overcalled\n");
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

function q($query)
{
	if ( !isset($GLOBALS['__DB_INC_INTERNALS__']['query_count']) )
		$GLOBALS['__DB_INC_INTERNALS__']['query_count'] = 1;
	else 
		++$GLOBALS['__DB_INC_INTERNALS__']['query_count'];
	
	if ( !isset($GLOBALS['__DB_INC_INTERNALS__']['total_sql_time']) ) $GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] = 0;
	
	$ts = db_getmicrotime();
	if ( !($result=mysql_query($query,$GLOBALS['__DB_INC__']['SQL_LINK'])) ) {
		$error_reason = mysql_error($GLOBALS['__DB_INC__']['SQL_LINK']);
		error_handler("db.inc", "query failed: %( $query )% because %( $error_reason )%", 1);
		echo "<b>Query Failed:</b> ".htmlspecialchars($query)."<br>\n<b>Reason:</b> ".$error_reason."<br>\n<b>From:</b> ".$GLOBALS['SCRIPT_FILENAME']."<br>\n<b>Server Version:</b> ".q_singleval("SELECT VERSION()")."<br>\n";
		if( db_locked() ) db_unlock();
		exit;
	}
	$te = db_getmicrotime(); 
	
	$GLOBALS['__DB_INC_INTERNALS__']['last_time'] = $te-$ts;
	$GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] += $GLOBALS['__DB_INC_INTERNALS__']['last_time'];
	$GLOBALS['__DB_INC_INTERNALS__']['last_query'] = $query;
	
	return $result; 
}

function qf($result)
{
	mysql_free_result($result);
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

function db_lastid()
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

	reset($robj);
	while ( list($k, $v) = each($robj) ) {
		$obj->{$k} = $v;
	}
	
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
?>