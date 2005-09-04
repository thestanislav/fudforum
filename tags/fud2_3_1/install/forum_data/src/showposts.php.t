<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: showposts.php.t,v 1.7 2002/08/05 00:47:55 hackie Exp $
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
	
	if( !is_numeric($id) ) invl_inp_err();
	
	$u = new fud_user();
	$u->get_user_by_id($id);
	
	if( empty($u->id) ) invl_inp_err();
	
	{POST_HTML_PHP}
	$TITLE_EXTRA = ': {TEMPLATE: show_posts_by}';
	
	if ( isset($ses) ) $ses->update('{TEMPLATE: showposts_update}');

	if ( !is_numeric($start) ) $start = 0;
	if ( !is_numeric($count) ) $count = $THREADS_PER_PAGE;
	
	$fids = get_all_perms(_uid);

	if( $HTTP_GET_VARS['so'] == 'asc' ) {
		$SORT_ORDER = 'ASC';
		$SORT_ORDER_R = 'desc';
	}
	else {
		$SORT_ORDER = 'DESC';
		$SORT_ORDER_R = 'asc';
	}
	
	if( !empty($fids) || $usr->mod=='A' ) {
		$qry_limit = ( $usr->mod != 'A' ) ? "{SQL_TABLE_PREFIX}forum.id IN (".$fids.") AND " : '';
	
		$total = q_singleval("SELECT 
				count(*) 
			FROM 
				{SQL_TABLE_PREFIX}msg 
			LEFT JOIN {SQL_TABLE_PREFIX}thread 
				ON {SQL_TABLE_PREFIX}msg.thread_id={SQL_TABLE_PREFIX}thread.id 
			LEFT JOIN {SQL_TABLE_PREFIX}forum 
				ON {SQL_TABLE_PREFIX}thread.forum_id={SQL_TABLE_PREFIX}forum.id 
			WHERE
				".$qry_limit."
				{SQL_TABLE_PREFIX}msg.approved='Y' AND 
				{SQL_TABLE_PREFIX}msg.poster_id=".$id);
		
		$r = q("SELECT 
				{SQL_TABLE_PREFIX}thread.id AS th_id, 
				{SQL_TABLE_PREFIX}forum.name AS forum_name, 
				{SQL_TABLE_PREFIX}forum.id as fid, 
				{SQL_TABLE_PREFIX}msg.subject AS subject, 
				{SQL_TABLE_PREFIX}msg.id AS id, 
				{SQL_TABLE_PREFIX}msg.post_stamp AS post_stamp 
			FROM 
				{SQL_TABLE_PREFIX}msg 
			LEFT JOIN {SQL_TABLE_PREFIX}thread 
				ON {SQL_TABLE_PREFIX}msg.thread_id={SQL_TABLE_PREFIX}thread.id 
			LEFT JOIN {SQL_TABLE_PREFIX}forum 
				ON {SQL_TABLE_PREFIX}thread.forum_id={SQL_TABLE_PREFIX}forum.id 
			WHERE 
				".$qry_limit."
				{SQL_TABLE_PREFIX}msg.approved='Y' AND 
				{SQL_TABLE_PREFIX}msg.poster_id=".$id." 
			ORDER BY 
				{SQL_TABLE_PREFIX}msg.post_stamp ".$SORT_ORDER." 
			LIMIT ".qry_limit($count, $start));
		
		$post_entry='';
		while ( $obj = db_rowobj($r) ) $post_entry .= '{TEMPLATE: post_entry}';
		qf($r);
	
		$pager = tmpl_create_pager($start, $count, $total, '{ROOT}?t=showposts&amp;id='.$id.'&amp;start='.$start.'&amp;'._rsid);
	}
	{POST_PAGE_PHP_CODE}
?>
{TEMPLATE: SHOWPOSTS_PAGE}