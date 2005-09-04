<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: forum.inc.t,v 1.2 2002/06/18 18:26:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/
	
class fud_forum
{
	var $id=NULL;
	var $cat_id=NULL;
	var $name=NULL;
	var $descr=NULL;
	var $passwd_posting=NULL;
	var $post_passwd=NULL;
	var $anon_forum=NULL;
	var $forum_icon=NULL;
	var $tag_style=NULL;
	var $last_post_id=NULL;
	
	var $allow_polls=NULL;
	var $date_created=NULL;
	var $thread_count=NULL;
	var $view_order=NULL;
	var $message_threshold=NULL;
	
	var $moderated=NULL;
	var $max_attach_size=NULL;
	var $max_file_attachments=NULL;
	
	var $forums=NULL;
	var $cur_frm=NULL;
	
	var $locked=NULL;
	
	
	function get($id)
	{
		qobj("SELECT * FROM {SQL_TABLE_PREFIX}forum WHERE {SQL_TABLE_PREFIX}forum.id=".$id, $this);
		if( empty($this->id) ) invl_inp_err();
			
		return $id;		
	}
	
	function is_moderator($user_id)
	{
		return q_singleval("SELECT id FROM {SQL_TABLE_PREFIX}mod WHERE user_id=".$user_id." AND forum_id=".$this->id);
	}
	
	function inc_reply_count($val)
	{
		q("UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count+".$val." WHERE id=".$this->id);
	}
	
	function dec_reply_count($val)
	{
		q("UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count-".$val." WHERE id=".$this->id);
	}
	
	function inc_thread_count($val)
	{
		q("UPDATE {SQL_TABLE_PREFIX}forum SET thread_count=thread_count+".$val." WHERE id=".$this->id);
	}
	
	function dec_thread_count($val)
	{
		q("UPDATE {SQL_TABLE_PREFIX}forum SET thread_count=thread_count-".$val." WHERE id=".$this->id);
	}
	
	function get_notify_list($user_id)
	{
		$r = q("SELECT 
				{SQL_TABLE_PREFIX}users.email, 
				{SQL_TABLE_PREFIX}users.icq, 
				{SQL_TABLE_PREFIX}users.notify_method 
			FROM 
				{SQL_TABLE_PREFIX}forum_notify 
				INNER JOIN {SQL_TABLE_PREFIX}users 
					ON {SQL_TABLE_PREFIX}forum_notify.user_id={SQL_TABLE_PREFIX}users.id 
			WHERE 
				{SQL_TABLE_PREFIX}forum_notify.forum_id=".$this->id." 
				AND {SQL_TABLE_PREFIX}forum_notify.user_id!=".$user_id);
		
		$p = forum_perm_array($this->id);
		$to=NULL;
		while ( $obj = db_rowobj($r) ) {
			if ( !is_allowed($obj->user_id, $p) ) continue;
			
			$to[$obj->notify_method][] = ( $obj->notify_method == 'EMAIL' ) ? $obj->email : $obj->icq.'@pager.icq.com';
		}

		qf($r);
				
		return $to;
	}
	
}

if ( defined('admin_form') && !defined("_forum_adm_inc_") ) fud_use('forum_adm.inc');
?>