<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: threadt.php.t,v 1.8 2002/11/04 18:54:10 hackie Exp $
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

	if( $TREE_THREADS_ENABLE == 'N' ) {
		error_dialog('{TEMPLATE: threadt_disabled_ttl}', '{TEMPLATE: threadt_disabled_desc}', '{ROOT}?t=thread&frm_id='.$frm_id.'&'._rsid);
		exit;		
	}
	if( empty($frm_id) || !is_numeric($frm_id) ) invl_inp_err();
	
	$frm = new fud_forum;
	$frm->get($frm_id);
	
	if( empty($frm->cat_id) ) invl_inp_err();
	
	$GLOBALS['__RESOURCE_ID'] = $frm->id;	
	
	if ( isset($ses) ) $ses->update('{TEMPLATE: threadt_update}', $GLOBALS['__RESOURCE_ID']);
	
	if( !is_perms(_uid, $GLOBALS['__RESOURCE_ID'], 'READ') ) {
		if( !isset($HTTP_GET_VARS['logoff']) )
			error_dialog('{TEMPLATE: permission_denied_title}', '{TEMPLATE: permission_denied_msg}', '');
		else {
			header("Location: {ROOT}");
			exit;
		}	
	}	

	if ( isset($usr) ) {
		if ( $frm->is_moderator($usr->id) || $usr->is_mod == 'A' ) $MOD = 1;
		$ppg = $usr->posts_ppg;
		
		fud_use('forum_notify.inc');
		$frm_not = new fud_forum_notify;
		
		if ( isset($sub) && $sub ) 
			$frm_not->add($usr->id, $frm->id);
		else if ( isset($unsub) && $unsub ) 
			$frm_not->delete($usr->id, $frm->id);
	}
	
	if ( $MOD ) {
		fud_use('imsg.inc');
		fud_use('imsg_edt.inc');
	}
	
	{POST_HTML_PHP}
	$TITLE_EXTRA = ': {TEMPLATE: thread_title}';

	if ( !isset($cat) ) {
		$cat = new fud_cat;
		$cat->get_cat($frm->cat_id);
	}
	
	$returnto = 'returnto='.urlencode("{ROOT}?t=threadt&amp;frm_id=".$frm_id.'&amp;'._rsid);

	if ( isset($usr) ) {
		if ( is_forum_notified($usr->id, $frm->id) ) 
			$subscribe = '{TEMPLATE: unsubscribe_link}';
		else 
			$subscribe = '{TEMPLATE: subscribe_link}';
	}

	if ( empty($start) || !is_numeric($start) ) $start = 0;

	$r = q("SELECT 
			{SQL_TABLE_PREFIX}thread.moved_to,
			{SQL_TABLE_PREFIX}thread.locked,
			{SQL_TABLE_PREFIX}thread.is_sticky,
			{SQL_TABLE_PREFIX}thread.ordertype,
			{SQL_TABLE_PREFIX}thread.root_msg_id,
			{SQL_TABLE_PREFIX}read.last_view,
			{SQL_TABLE_PREFIX}msg.*,
			{SQL_TABLE_PREFIX}users.alias
			FROM
				{SQL_TABLE_PREFIX}thread_view
			INNER JOIN {SQL_TABLE_PREFIX}thread 
				ON {SQL_TABLE_PREFIX}thread_view.thread_id={SQL_TABLE_PREFIX}thread.id
			INNER JOIN {SQL_TABLE_PREFIX}msg
				ON {SQL_TABLE_PREFIX}thread.id={SQL_TABLE_PREFIX}msg.thread_id AND {SQL_TABLE_PREFIX}msg.approved='Y'
			LEFT JOIN {SQL_TABLE_PREFIX}users
				 ON {SQL_TABLE_PREFIX}msg.poster_id={SQL_TABLE_PREFIX}users.id
			LEFT JOIN {SQL_TABLE_PREFIX}read 
				ON {SQL_TABLE_PREFIX}thread.id={SQL_TABLE_PREFIX}read.thread_id AND {SQL_TABLE_PREFIX}read.user_id="._uid."
			WHERE
				{SQL_TABLE_PREFIX}thread_view.forum_id=".$frm->id." AND {SQL_TABLE_PREFIX}thread_view.page=".($start + 1)."
			ORDER by pos ASC");
	
	if ( !db_count($r) ) {
		$no_messages = '{TEMPLATE: no_messages}';
	} else {
		$thread_list_table_data='';
		$p = $cur_th_id = 0;
		
		while ( $obj = db_rowobj($r) ) {
			unset($stack, $tree, $arr, $cur);
			db_seek($r, $p);
			
			$cur_th_id = $obj->thread_id;
			while ($obj = db_rowobj($r)) {
				if ($obj->thread_id != $cur_th_id) {
					db_seek($r, $p);
					break;
				}	
				
				$arr[$obj->id] = $obj;
				$arr[$obj->reply_to]->kiddie_count++;
				$arr[$obj->reply_to]->kiddies[] = &$arr[$obj->id];
		
				if ( $obj->reply_to == 0 ) {
					$tree->kiddie_count++;
					$tree->kiddies[] = &$arr[$obj->id];
				}
				
				$p++;	
			}
			
			if( @is_array($tree->kiddies) ) {
				reset($tree->kiddies);
				$stack[0] = &$tree;
				$stack_cnt = $tree->kiddie_count;
				$j = $lev = 0;
	
				if( $thread_list_table_data ) $thread_list_table_data .= '{TEMPLATE: thread_sep}';
			
				while ($stack_cnt>0) {
					$cur = &$stack[$stack_cnt-1];
		
					if( isset($cur->subject) && empty($cur->sub_shown) ) {
						if( $TREE_THREADS_MAX_DEPTH > $lev ) {
							$thread_poll_indicator = ( $cur->poll_id ) ? '{TEMPLATE: thread_poll_indicator}' : '';
							$thread_attach_indicator = ( $cur->attach_cnt ) ? '{TEMPLATE: thread_attach_indicator}' : '';
							$thread_icon = ( $cur->th_icon ) ? '{TEMPLATE: thread_icon}' : '{TEMPLATE: thread_icon_none}';
							if ($lev == 1 && $cur->is_sticky == 'Y') {
								$cur->subject .= ($cur->ordertype == 'STICKY' ? '{TEMPLATE: sticky}' : '{TEMPLATE: announcement}');
							} 
							
							if( _uid ) {
								if( $usr->last_read < $cur->post_stamp && $cur->post_stamp>$obj->last_view )
									$thread_read_status = ( $obj->locked == 'N' ) ? '{TEMPLATE: thread_unread}' : '{TEMPLATE: thread_unread_locked}';
								else
									$thread_read_status = ( $obj->locked == 'N' ) ? '{TEMPLATE: thread_read}' : '{TEMPLATE: thread_read_locked}';
							} else {
								$thread_read_status = '{TEMPLATE: thread_read_unreg}';
							}
							$user_link = ( $cur->poster_id ) ? '{TEMPLATE: reg_user_link}' : '{TEMPLATE: unreg_user_link}';
						
							if( isset($cur->subject[$TREE_THREADS_MAX_SUBJ_LEN]) ) $cur->subject = substr($cur->subject, 0, $TREE_THREADS_MAX_SUBJ_LEN).'...';
						
							$width = 20*($lev-1);
						
							$thread_list_table_data .= '{TEMPLATE: thread_row}';
						}
						if( $TREE_THREADS_MAX_DEPTH == $lev ) {
							$width += 20;
							$thread_list_table_data .= '{TEMPLATE: max_depth_reached}';
						}	
			
						$cur->sub_shown = 1;
					}
		
					if( !isset($cur->kiddie_count) ) $cur->kiddie_count = 0;
		
					if ( $cur->kiddie_count && isset($cur->kiddie_pos) )
						++$cur->kiddie_pos;	
					else
						$cur->kiddie_pos = 0;
		
					if ( $cur->kiddie_pos < $cur->kiddie_count ) {
						++$lev;
						$stack[$stack_cnt++] = &$cur->kiddies[$cur->kiddie_pos];
					} else { // unwind the stack if needed
						unset($stack[--$stack_cnt]);
						--$lev;
				}
				unset($cur);
			}
		}
	}
	$thread_list_table_data = '{TEMPLATE: thread_list_wmsg}';
	qf($r); 	
}

	$page_pager = tmpl_create_pager($start, 1, ceil($frm->thread_count / $GLOBALS['THREADS_PER_PAGE']), '{ROOT}?t=threadt&amp;frm_id='.$frm_id.'&amp;'._rsid);

	{POST_PAGE_PHP_CODE}
?>	
{TEMPLATE: THREAD_PAGE}	
<?php	
	if ( isset($usr) ) $usr->register_forum_view($frm->id);
?>