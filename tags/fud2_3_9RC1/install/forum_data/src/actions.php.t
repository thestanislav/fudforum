<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: actions.php.t,v 1.16 2003/01/12 13:21:27 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	{PRE_HTML_PHP}
	
	if ( $GLOBALS['ACTION_LIST_ENABLED'] != 'Y' ) std_error('disabled');
	
	if ( isset($ses) ) $ses->update('{TEMPLATE: actions_update}');

	{POST_HTML_PHP}
	
	$rand_val = get_random_value();
	
	$limit = array();
	if( $usr->is_mod != 'A' ) {
		$r = q("SELECT resource_id,p_READ FROM {SQL_TABLE_PREFIX}group_cache WHERE user_id="._uid." AND resource_type='forum'");
		while( list($fid,$pr) = db_rowarr($r) ) $limit[$fid] = ( $pr == 'Y' ) ? 1 : 0;
		qf($r);

		if( _uid ) {
			$r = q("SELECT resource_id FROM {SQL_TABLE_PREFIX}group_cache WHERE user_id=2147483647 AND resource_type='forum' AND p_READ='Y'");
			while( list($fid) = db_rowarr($r) ) {
				if( !isset($limit[$fid]) ) $limit[$fid] = 1;
			}	
			qf($r);
		}
	}
	
	$r = q("SELECT 
			{SQL_TABLE_PREFIX}ses.action,
			{SQL_TABLE_PREFIX}ses.user_id,
			{SQL_TABLE_PREFIX}ses.forum_id AS action_forum_id,
			{SQL_TABLE_PREFIX}users.alias,
			{SQL_TABLE_PREFIX}users.is_mod,
			{SQL_TABLE_PREFIX}users.custom_color,
			{SQL_TABLE_PREFIX}ses.time_sec,
			{SQL_TABLE_PREFIX}users.invisible_mode,
			{SQL_TABLE_PREFIX}msg.id AS mid,
			{SQL_TABLE_PREFIX}msg.subject,
			{SQL_TABLE_PREFIX}msg.post_stamp,
			{SQL_TABLE_PREFIX}thread.forum_id
		FROM {SQL_TABLE_PREFIX}ses 
		LEFT JOIN {SQL_TABLE_PREFIX}users 
			ON {SQL_TABLE_PREFIX}ses.user_id={SQL_TABLE_PREFIX}users.id
		LEFT JOIN {SQL_TABLE_PREFIX}msg
			ON {SQL_TABLE_PREFIX}users.u_last_post_id={SQL_TABLE_PREFIX}msg.id
		LEFT JOIN {SQL_TABLE_PREFIX}thread
			ON {SQL_TABLE_PREFIX}msg.thread_id={SQL_TABLE_PREFIX}thread.id
		WHERE {SQL_TABLE_PREFIX}ses.time_sec>".(__request_timestamp__-($GLOBALS['LOGEDIN_TIMEOUT']*60))." AND {SQL_TABLE_PREFIX}ses.ses_id!='".$ses->ses_id."' ORDER BY {SQL_TABLE_PREFIX}users.alias, {SQL_TABLE_PREFIX}ses.time_sec DESC");
		
	$action_data='';
	while ( $obj = db_rowobj($r) ) {
		if( $obj->invisible_mode == 'Y' && $usr->is_mod != 'A' ) continue;

		if ( isset($obj->alias) ) {
			$user_login = draw_user_link($obj->alias, $obj->is_mod, $obj->custom_color);
			$user_login = '{TEMPLATE: reg_user_link}';
			
			if( empty($obj->post_stamp) )
				$last_post = '{TEMPLATE: last_post_na}';
			else {
				if( $usr->is_mod != 'A' && empty($limit[$obj->forum_id]) ) 
					$last_post = '{TEMPLATE: no_view_perm}';
				else 
					$last_post = '{TEMPLATE: last_post}';
			}
		} else {
			$user_login = '{TEMPLATE: anon_user}';
			$last_post = '{TEMPLATE: last_post_na}';
		}
		
		if( !empty($limit[$obj->action_forum_id]) || !$obj->action_forum_id || $usr->is_mod=='A' ) {
			if( ($p=strpos($obj->action, '?')) ) 
				$action = substr_replace($obj->action, '?'._rsid.'&', $p, 1);
			else if( ($p=strpos($obj->action, '.php')) ) 
				$action = substr_replace($obj->action, '.php?'._rsid.'&', $p, 4);
			else
				$action = $obj->action;	
		}
		else 
			$action = '{TEMPLATE: no_view_perm}';	
				
		$action_data .= '{TEMPLATE: action_entry}';
	}
	qf($r);	

	{POST_PAGE_PHP_CODE}
?>
{TEMPLATE: ACTION_PAGE}