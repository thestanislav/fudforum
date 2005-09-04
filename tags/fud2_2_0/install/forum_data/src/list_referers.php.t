<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: list_referers.php.t,v 1.3 2002/06/18 18:26:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	include_once "GLOBALS.php";
	{PRE_HTML_PHP}
	if ( isset($ses) ) $ses->update('{TEMPLATE: list_referers_update}');
	
	{POST_HTML_PHP}

	$returnto = urlencode($GLOBALS["REQUEST_URI"]);

	if( empty($start) || $start>$ttl ) $start=0;

	$res = q("SELECT fud_users_ref.login,fud_users_ref.id,SUM(1) AS cnt FROM {SQL_TABLE_PREFIX}users LEFT JOIN {SQL_TABLE_PREFIX}users AS fud_users_ref ON fud_users_ref.id={SQL_TABLE_PREFIX}users.referer_id WHERE {SQL_TABLE_PREFIX}users.referer_id>0 AND fud_users_ref.id IS NOT NULL GROUP BY fud_users_ref.id ORDER BY cnt DESC");
	$ttl = db_count($res);
	if( $ttl ) { 
		if( $start ) db_seek($res, $start);
		$i=0;
		$referer_entry_data = '';
		while ( ($obj = db_rowobj($res)) && $i<$GLOBALS['MEMBERS_PER_PAGE'] ) {	
			$r_list='';		
			$r = q("SELECT login,id FROM {SQL_TABLE_PREFIX}users WHERE referer_id=".$obj->id);
			$refered_entry_data = '';
			while ( list($rf_login,$rf_id) = db_rowarr($r) ) {
				$rf_login = htmlspecialchars($rf_login);
				$refered_entry_data .= '{TEMPLATE: refered_entry}';
			}
			qf($r);
			$user_login = htmlspecialchars($obj->login);
			$referer_entry_data .= '{TEMPLATE: referer_entry}';

			$i++;
		}

		$page_pager = tmpl_create_pager($start, $GLOBALS['MEMBERS_PER_PAGE'],$ttl,'{ROOT}?t=referals&id='.$id.'&'._rsid);
	}
	qf($res);

	{POST_PAGE_PHP_CODE}
?>
{TEMPLATE: REFERALS_PAGE}