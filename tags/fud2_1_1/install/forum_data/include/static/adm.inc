<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: adm.inc,v 1.3 2002/06/21 15:49:22 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

if( !defined('shell_script') ) {
	fud_use('time.inc');
	fud_use("db.inc");
	fud_use('cookies.inc');
	fud_use('users.inc');
}
	
function initadm()
{
	global $usr;
	global $ses;
	
	if ( $usr->is_mod != 'A' ) {
		header("Location: admloginuser.php?"._rsid);
		exit();
	}
	
	$arr[0] = $ses;
	$arr[1] = $usr;
	
	return $arr;
} 

function filetomem($fn)
{
	if ( !@is_file($fn) ) exit("FATAL ERROR: '<b>$fn</b>' is not a file in (<b>".getcwd()."</b>), can't open<br>\n");
	$fp = fopen($fn, 'rb');
        $st = fstat($fp);
	$sz = isset($st['size']) ? $st['size'] : $st[7];
        $str = fread($fp, $sz);
        fclose($fp);
        
        return $str;
}

function rebuildmodlist()
{
	$ar = array();
	$ar2 = array();
	$r = q("SELECT ".$GLOBALS['MYSQL_TBL_PREFIX']."users.id,".$GLOBALS['MYSQL_TBL_PREFIX']."users.login,".$GLOBALS['MYSQL_TBL_PREFIX']."mod.forum_id FROM ".$GLOBALS['MYSQL_TBL_PREFIX']."mod LEFT JOIN ".$GLOBALS['MYSQL_TBL_PREFIX']."users ON ".$GLOBALS['MYSQL_TBL_PREFIX']."mod.user_id=".$GLOBALS['MYSQL_TBL_PREFIX']."users.id ORDER BY forum_id");
	while ( $obj = db_rowobj($r) ) {
		if( empty($ar[$obj->forum_id]) ) {
			$ar[$obj->forum_id]='';
			$ar2[$obj->forum_id]=0;
		}
		if( $ar2[$obj->forum_id] >= $GLOBALS['SHOW_N_MODS'] ) continue;
				
		$ar2[$obj->forum_id]++;	
		$ar[$obj->forum_id] .= $obj->id."\n".htmlspecialchars(trim_show_len($obj->login,'LOGIN'))."\n\n";
	}
	qf($r);
	
	reset($ar);
	while( list($k,$v) = each($ar) ) {
		if( $k ) q("UPDATE ".$GLOBALS['MYSQL_TBL_PREFIX']."forum SET moderators='".substr($v, 0, -1)."' WHERE id=".$k);	
	}

	$r = q("SELECT ".$GLOBALS['MYSQL_TBL_PREFIX']."forum.id FROM ".$GLOBALS['MYSQL_TBL_PREFIX']."forum LEFT JOIN ".$GLOBALS['MYSQL_TBL_PREFIX']."mod ON ".$GLOBALS['MYSQL_TBL_PREFIX']."mod.forum_id=".$GLOBALS['MYSQL_TBL_PREFIX']."forum.id WHERE forum_id IS NULL");
	while( list($fid) = db_rowarr($r) ) 
		q("UPDATE ".$GLOBALS['MYSQL_TBL_PREFIX']."forum SET moderators='' WHERE id=".$fid);
	qf($r);
}
?>