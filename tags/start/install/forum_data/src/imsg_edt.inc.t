<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: imsg_edt.inc.t,v 1.1.1.1 2002/06/17 23:00:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/
class fud_msg_edit extends fud_msg
{
	function add_thread($forum_id, $autoapprove=TRUE)
	{
		return $this->add($forum_id, $autoapprove);
	}
	
	function add_reply($reply_to, $th_id=NULL, $autoapprove=TRUE)
	{
		if( !empty($reply_to) ) {
			$this->reply_to=$reply_to;
			$forum_id = Q_SINGLEVAL("SELECT {SQL_TABLE_PREFIX}thread.forum_id FROM {SQL_TABLE_PREFIX}msg LEFT JOIN {SQL_TABLE_PREFIX}thread ON {SQL_TABLE_PREFIX}msg.thread_id={SQL_TABLE_PREFIX}thread.id WHERE {SQL_TABLE_PREFIX}msg.id=".$reply_to);
		}
		else
			$forum_id = Q_SINGLEVAL("SELECT {SQL_TABLE_PREFIX}thread.forum_id FROM {SQL_TABLE_PREFIX}thread WHERE {SQL_TABLE_PREFIX}thread.id=".$th_id);
		
		return $this->add($forum_id, $autoapprove);
	}
	
	function add($forum_id, $autoapprove=TRUE)
	{
		if ( empty($this->attachment_id) ) $this->attachment_id = 0;
		if ( isset($GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR']) ) $this->ip_addr = $GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR'];
		
		$this->post_stamp = __request_timestamp__;
		if ( $GLOBALS['PUBLIC_RESOLVE_HOST'] == 'Y' ) $this->host_name = get_host($this->ip_addr);
		
		$this->thread_id = isset($this->thread_id) ? $this->thread_id : 0;
		$this->reply_to = isset($this->reply_to) ? $this->reply_to : 0;
		
		$frm = new fud_forum;
		$frm->get($forum_id);

		/* determine if preview needs building */
		if ( $frm->message_threshold && $frm->message_threshold < strlen($this->body) )
			$thres_body = trim_html($this->body, $frm->message_threshold);
		
		if ( !db_locked() ) {
			DB_LOCK('
				{SQL_TABLE_PREFIX}thread_view+, 
				{SQL_TABLE_PREFIX}level+, 
				{SQL_TABLE_PREFIX}cat+, 
				{SQL_TABLE_PREFIX}users+, 
				{SQL_TABLE_PREFIX}forum+, 
				{SQL_TABLE_PREFIX}thread+, 
				{SQL_TABLE_PREFIX}msg+
			');
			$ll = 1;
		}
		
		/* length+offset are returned by ref, thank php-devs for dumbass syntax */
		$file_id = write_body($this->body, $length, $offset);
		if ( $thres_body ) $file_id_preview = write_body($thres_body, $length_preview, $offset_preview);
	
		Q("INSERT INTO {SQL_TABLE_PREFIX}msg (
			thread_id, 
			poster_id, 
			reply_to, 
			ip_addr,
			host_name,
			post_stamp, 
			subject, 
			attach_cnt, 
			poll_id, 
			icon, 
			show_sig,
			smiley_disabled,
			file_id,
			offset,
			length,
			file_id_preview,
			offset_preview,
			length_preview
		) 
		VALUES(
			".$this->thread_id.",
			".$this->poster_id.",
			".INTZERO($this->reply_to).",
			".IFNULL($this->ip_addr, "'0.0.0.0'").",
			".STRNULL($this->host_name).",
			".$this->post_stamp.",
			".STRNULL($this->subject).",
			".INTZERO($this->attach_cnt).",
			".INTZERO($this->poll_id).",
			".STRNULL($this->icon).",
			'".YN($this->show_sig)."',
			'".YN($this->smiley_disabled)."',
			".$file_id.",
			".INTZERO($offset).",
			".INTZERO($length).",
			".INTZERO($file_id_preview).",
			".INTZERO($offset_preview).",
			".INTZERO($length_preview)."
		)");

		$this->id = DB_LASTID();

		$thr = new fud_thread;
		if ( !$this->thread_id ) { /* new thread */
			$thr->last_post = $this->post_stamp;
			
			/* if moderator is creating a thread consider a number of other properties */
			if ( isset($GLOBALS['MOD']) ) {
				if ( $GLOBALS['HTTP_POST_VARS']['thr_ordertype'] != 'NONE' ) 
					$is_sticky = 'Y';
				else
					$is_sticky = 'N';
				$this->thread_id = $thr->add($this->id, $forum_id, $GLOBALS['HTTP_POST_VARS']['thr_locked'], $is_sticky, $GLOBALS['HTTP_POST_VARS']['thr_ordertype'], $GLOBALS['HTTP_POST_VARS']['thr_orderexpiry']);
			}
			else
				$this->thread_id = $thr->add($this->id, $forum_id);
	
			Q("UPDATE {SQL_TABLE_PREFIX}msg SET thread_id=".$this->thread_id." WHERE id=".$this->id);
		}
		else {
			$thr->get_by_id($this->thread_id);
			if ( $GLOBALS['HTTP_POST_VARS']['thr_locked']=='Y' ) 
				$thr->lock(); 
			else
				$thr->unlock();
		}
		
		if ( $autoapprove && (empty($frm->moderated) || $frm->moderated != 'Y') ) $this->approve(NULL, TRUE);
		if ( $ll && db_locked() ) DB_UNLOCK();

		return $this->id;
	}
	
	function sync($id)
	{
		if ( !db_locked() ) {
			DB_LOCK('{SQL_TABLE_PREFIX}cat+, {SQL_TABLE_PREFIX}forum+, {SQL_TABLE_PREFIX}msg+, {SQL_TABLE_PREFIX}thread+, {SQL_TABLE_PREFIX}index+, {SQL_TABLE_PREFIX}title_index+, {SQL_TABLE_PREFIX}thread_view+');
			$ll=1;
		}
		$file_id = write_body($this->body, $length, $offset);
		/* determine if preview needs building */
		$frm = new fud_forum;
		$frm->get(Q_SINGLEVAL("SELECT forum_id FROM {SQL_TABLE_PREFIX}thread WHERE id=".$this->thread_id));
		
		if ( $frm->message_threshold && $frm->message_threshold < strlen($this->body) ) {
			$thres_body = trim_html($this->body, $frm->message_threshold);
			$file_id_preview = write_body($thres_body, $length_preview, $offset_preview);
		}
		Q("UPDATE {SQL_TABLE_PREFIX}msg SET 
			file_id=".$file_id.", 
			offset=".INTZERO($offset).", 
			length=".INTZERO($length).", 
			file_id_preview=".INTZERO($file_id_preview).",
			offset_preview=".INTZERO($offset_preview).",
			length_preview=".INTZERO($length_preview).",
			smiley_disabled='".YN($this->smiley_disabled)."', 
			updated_by=".$id.", show_sig='".YN($this->show_sig)."', 
			subject='".$this->subject."', 
			attach_cnt=".INTZERO($this->attach_cnt).", 
			poll_id=".INTZERO($this->poll_id).", 
			update_stamp=".__request_timestamp__.", 
			icon=".STRNULL($this->icon)." 
		WHERE id=".$this->id);
		
		delete_msg_index($this->id);
		$root_msg_id = Q_SINGLEVAL("SELECT root_msg_id FROM {SQL_TABLE_PREFIX}thread WHERE id=".$this->thread_id);		
		if ( isset($GLOBALS['MOD']) ) {
			$thr = new fud_thread;
			$thr->id = $this->thread_id;
			if ( $root_msg_id==$this->id ) {
				if ( $GLOBALS['HTTP_POST_VARS']['thr_ordertype'] != 'NONE' ) 
					$thr->is_sticky = 'Y';
				else
					$thr->is_sticky = 'N';
				$thr->ordertype = $GLOBALS['HTTP_POST_VARS']['thr_ordertype'];
				$thr->orderexpiry = $GLOBALS['HTTP_POST_VARS']['thr_orderexpiry'];
				$thr->locked = YN($GLOBALS['HTTP_POST_VARS']['thr_locked']);
				$thr->sync();
			}
			else {
				if ( $GLOBALS['HTTP_POST_VARS']['thr_locked']=='Y' ) 
					$thr->lock();
				else
					$thr->unlock();
			}
		}
		if ( $ll ) DB_UNLOCK();
		$s_text = preg_match("!^Re: !i", $this->subject) ? '': $this->subject;
		if( $GLOBALS['FORUM_SEARCH'] == 'Y' ) index_text($s_text, $this->body, $this->id);
	}
	
	function fetch_vars($array, $prefix)
	{
		$this->subject = $array[$prefix.'subject'];
		$this->body = $array[$prefix.'body'];
		$this->icon = isset($array[$prefix.'icon'])?$array[$prefix.'icon']:'';
		$this->show_sig = isset($array[$prefix.'show_sig'])?$array[$prefix.'show_sig']:'';
	}
	
	function export_vars($prefix)
	{	
		$GLOBALS[$prefix.'subject'] = $this->subject;
		$GLOBALS[$prefix.'body'] = $this->body;
		$GLOBALS[$prefix.'icon'] = $this->icon;
		$GLOBALS[$prefix.'show_sig'] = $this->show_sig;
	}
	
	function delete($rebuild_view=TRUE)
	{
		if ( !db_locked() ) {
			DB_LOCK('{SQL_TABLE_PREFIX}thread_view+, {SQL_TABLE_PREFIX}level+, {SQL_TABLE_PREFIX}forum+, {SQL_TABLE_PREFIX}forum_read+, {SQL_TABLE_PREFIX}thread+, {SQL_TABLE_PREFIX}msg+, {SQL_TABLE_PREFIX}attach+, {SQL_TABLE_PREFIX}poll+, {SQL_TABLE_PREFIX}poll_opt+, {SQL_TABLE_PREFIX}poll_opt_track+, {SQL_TABLE_PREFIX}users+, {SQL_TABLE_PREFIX}thread_notify+, {SQL_TABLE_PREFIX}msg_report+, {SQL_TABLE_PREFIX}thread_rate_track+');
			$local_lock = 1;
		}
		
		$res = Q("SELECT 
				{SQL_TABLE_PREFIX}msg.*,
				{SQL_TABLE_PREFIX}thread.root_msg_id AS root_msg_id, 
				{SQL_TABLE_PREFIX}thread.last_post_id AS thread_lip,
				{SQL_TABLE_PREFIX}forum.last_post_id AS forum_lip,
				forum_id 
			FROM {SQL_TABLE_PREFIX}msg 
			LEFT JOIN {SQL_TABLE_PREFIX}thread 
				ON {SQL_TABLE_PREFIX}msg.thread_id={SQL_TABLE_PREFIX}thread.id 
			LEFT JOIN {SQL_TABLE_PREFIX}forum 
				ON {SQL_TABLE_PREFIX}thread.forum_id={SQL_TABLE_PREFIX}forum.id 
			WHERE 
				{SQL_TABLE_PREFIX}msg.id=".$this->id);
		if ( !IS_RESULT($res) ) exit("no such message");
		$del = DB_SINGLEOBJ($res);
		
		if ( $del->attach_cnt ) {
			$res = Q("SELECT location FROM {SQL_TABLE_PREFIX}attach WHERE message_id=".$this->id." AND private='N'");
			if( DB_COUNT($res) ) {
				while ( list($loc) = DB_ROWARR($res) ) {
					if( file_exists($loc) ) unlink($loc);				
				}
			}
			QF($res);
			Q("DELETE FROM {SQL_TABLE_PREFIX}attach WHERE message_id=".$this->id." AND private='N'");
		}
		
		Q("DELETE FROM {SQL_TABLE_PREFIX}msg_report WHERE msg_id=".$this->id);
		
		if ( $del->poll_id ) {
			$poll = new fud_poll;
			$poll->get($del->poll_id);
			$poll->delete();
		}

		if( $del->root_msg_id==$del->id ) {
			$rmsg = Q("SELECT * FROM {SQL_TABLE_PREFIX}msg WHERE thread_id=".$del->thread_id." AND id!=".$del->id);
			$d_msg = new fud_msg_edit;
			while ( $obj = DB_ROWOBJ($rmsg) ) {
				QOBJ("SELECT * FROM {SQL_TABLE_PREFIX}msg WHERE id=".$obj->id, $d_msg);
				$d_msg->delete($rebuild_view);
			}
			QF($rmsg);
			Q("DELETE FROM {SQL_TABLE_PREFIX}thread_notify WHERE thread_id=".$del->thread_id);
			$rthd=Q("SELECT forum_id, root_msg_id FROM {SQL_TABLE_PREFIX}thread WHERE root_msg_id=".$del->root_msg_id);
			$i = 0;
			while ( list($th_forum_id, $th_root_msg_id) = DB_ROWARR($rthd) ) {
				Q("UPDATE {SQL_TABLE_PREFIX}forum SET thread_count=thread_count-1 WHERE id=".$th_forum_id);
				$lip_update[$i]['forum_id'] = $th_forum_id;
				$lip_update[$i]['msg_id'] = $th_root_msg_id;
				++$i;
			}
			QF($rthd);
			
			Q("DELETE FROM {SQL_TABLE_PREFIX}thread WHERE root_msg_id=".$del->root_msg_id);
			Q("DELETE FROM {SQL_TABLE_PREFIX}thread_rate_track WHERE thread_id=".$del->thread_id);
			if( $del->approved!='N' && $del->forum_id ) Q("UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count-1 WHERE id=".$del->forum_id);
		}
		else {
			if( $del->approved!='N' ) {
				Q("UPDATE {SQL_TABLE_PREFIX}thread SET replies=replies-1 WHERE id=".$del->thread_id);
				if( $del->forum_id ) {
					Q("UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count-1 WHERE id=".$del->forum_id);
					$lip_update[0]['forum_id'] = $del->forum_id;
					$lip_update[0]['msg_id'] = $del->id;
				}	
			}	
		}
		
		Q("UPDATE {SQL_TABLE_PREFIX}msg SET reply_to=".$this->reply_to." WHERE thread_id=".$this->thread_id." AND reply_to=".$this->id);
		Q("DELETE FROM {SQL_TABLE_PREFIX}msg WHERE id=".$this->id);
		
		if ( $this->poster_id && $del->approved!='N' ) {
			$u = new fud_user;
			$u->id = $this->poster_id;
			$u->set_post_count(-1);
		}
		
		if( $del->root_msg_id!=$this->id && $del->thread_lip == $this->id ) {
			$mid = Q_SINGLEVAL("SELECT id FROM {SQL_TABLE_PREFIX}msg WHERE thread_id=".$del->thread_id." AND approved='Y' ORDER BY post_stamp DESC LIMIT 1");
			Q("UPDATE {SQL_TABLE_PREFIX}thread SET last_post_id=".$mid." WHERE id=".$del->thread_id);
		}
		
		if ( is_array($lip_update) ) {
			reset($lip_update);
			while ( list($k, $v) = each($lip_update) ) {
				if( $v['msg_id'] == $this->id ) {
					$mid = Q_SINGLEVAL("SELECT last_post_id FROM {SQL_TABLE_PREFIX}thread INNER JOIN {SQL_TABLE_PREFIX}msg ON {SQL_TABLE_PREFIX}thread.last_post_id={SQL_TABLE_PREFIX}msg.id WHERE forum_id=".$v['forum_id']." AND {SQL_TABLE_PREFIX}msg.approved='Y' ORDER BY last_post_id DESC LIMIT 1");
					if( !$mid ) $mid = 0;
			
					Q("UPDATE {SQL_TABLE_PREFIX}forum SET last_post_id=".$mid." WHERE id=".$v['forum_id']);
					if( $rebuild_view ) rebuild_forum_view($v['forum_id']);
				}
			}
		}
		if ( $local_lock ) DB_UNLOCK();
	}	
	
	function approve($id=NULL, $unlock_safe=FALSE)
	{
		if( !db_locked() ) {
			DB_LOCK('
				{SQL_TABLE_PREFIX}thread_view+, 
				{SQL_TABLE_PREFIX}level+, 
				{SQL_TABLE_PREFIX}cat+, 
				{SQL_TABLE_PREFIX}users+, 
				{SQL_TABLE_PREFIX}forum+, 
				{SQL_TABLE_PREFIX}thread+, 
				{SQL_TABLE_PREFIX}msg+
				');
			$ll = 1;
		}
		
		if( $id ) 
			$this->get_by_id($id);
		else 
			$id = $this->id;
		
		if ( Q_SINGLEVAL("SELECT approved FROM {SQL_TABLE_PREFIX}msg WHERE id=$id")!='Y' ) {
			$thr = new fud_thread;
			$frm = new fud_forum;
			
			$thr->get_by_id($this->thread_id);
			$frm->get($thr->forum_id);
			
			Q("UPDATE {SQL_TABLE_PREFIX}msg SET approved='Y' WHERE id=".$this->id);
			
			if ( $this->poster_id ) {
				$u = new fud_user;
				$u->id = $this->poster_id;
				$u->set_post_count(1,$this->id);
			}
		
			if ( $thr->last_post_id <= $this->id ) { 
				Q("UPDATE {SQL_TABLE_PREFIX}thread SET last_post_id=".$this->id.", last_post_date=".$this->post_stamp." WHERE id=".$this->thread_id);
				
				if( Q_SINGLEVAL("SELECT last_post_id FROM {SQL_TABLE_PREFIX}forum WHERE id=".$thr->forum_id) < $this->id ) 
					Q("UPDATE {SQL_TABLE_PREFIX}forum SET last_post_id=".$this->id." WHERE id=".$thr->forum_id);
			}	

			if( $thr->root_msg_id == $this->id ) {/* new thread */
				Q("UPDATE {SQL_TABLE_PREFIX}forum SET thread_count=thread_count+1 WHERE id=".$frm->id);
				rebuild_forum_view($thr->forum_id);
			}	
			else {/* reply to thread */
				$thr->inc_post_count(1);
				rebuild_forum_view($thr->forum_id, Q_SINGLEVAL("SELECT page FROM {SQL_TABLE_PREFIX}thread_view WHERE forum_id=".$thr->forum_id." AND thread_id=".$this->thread_id));
			}	
				
			$frm->inc_reply_count(1);
			if( db_locked() && ($unlock_safe || $ll) ) DB_UNLOCK();
			
			if( $GLOBALS['FORUM_SEARCH'] == 'Y' ) {
				$s_text = preg_match("!Re: !i", $this->subject) ? '': $this->subject;
				index_text($s_text, $this->body, $this->id);
			}

			if ( $thr->root_msg_id == $this->id ) 
				send_notifications($frm->get_notify_list(INTZERO($this->poster_id)), $this->id, $thr->subject, ($GLOBALS['usr']->login?$GLOBALS['usr']->login:$GLOBALS['ANON_NICK']), 'frm', $frm->id, $frm->name);
			else
				send_notifications($thr->get_notify_list(INTZERO($this->poster_id)), $this->id, $thr->subject, ($GLOBALS['usr']->login?$GLOBALS['usr']->login:$GLOBALS['ANON_NICK']), 'thr', $thr->id);
		}
		
		if( db_locked() && !empty($ll) ) DB_UNLOCK();
	}
	
	function unapprove()
	{
		Q("UPDATE {SQL_TABLE_PREFIX}msg SET approved='N' WHERE id=".$this->id);
	}
	
}

function flood_check()
{
	$check_time = __request_timestamp__-$GLOBALS['FLOOD_CHECK_TIME'];
	
	if( ($v = Q_SINGLEVAL("SELECT post_stamp FROM {SQL_TABLE_PREFIX}msg WHERE ip_addr='".$GLOBALS['HTTP_SERVER_VARS']['REMOTE_ADDR']."' AND poster_id=".(($GLOBALS["usr"]->id)?$GLOBALS["usr"]->id:0)." AND post_stamp>".$check_time." ORDER BY post_stamp DESC LIMIT 1")) ) {
		$v += $GLOBALS['FLOOD_CHECK_TIME']-__request_timestamp__;
		if( $v<1 ) $v=1;
		return $v;
	}
	
	return;		
}

function write_body($data, &$len, &$offset)
{
	$MAX_FILE_SIZE = 2147483647;
	$curdir = getcwd();
	chdir($GLOBALS["MSG_STORE_DIR"]);

	$len = strlen($data);
	$i=1;
	while( $i<100 ) {
		$fp = fopen('msg_'.$i, 'ab');
		if( !($off = ftell($fp)) ) $off = __ffilesize($fp);
		if( !$off || sprintf("%u", $off+$len)<$MAX_FILE_SIZE ) break;
		fclose($fp);
		$i++;
	}
	
	$len = fwrite($fp, $data);
	fclose($fp);
	
	if( !$off ) @chmod('msg_'.$i, 0600);
	
	chdir($curdir);
	
	if( $len == -1 ) exit("FATAL ERROR: system has ran out of disk space<br>\n");
	$offset = $off;
	
	return $i;
}

function trim_html($str, $maxlen)
{
	$n = strlen($str);
	$ln = 0;
	for ( $i=0; $i<$n; $i++ ) {
		if ( $str[$i] != '<' ) {
			$ln++;
			if( $ln > $maxlen ) break;
			continue;
		}
		
		if( ($p = strpos($str, '>', $i)) === FALSE ) break;
		
		for ( $k=$i; $k<$p; $k++ ) {
			switch ( $str[$k] ) 
			{
				case ' ':
				case "\r":
				case "\n":
				case "\t":
				case ">":
					break 2;
			}
		}
		
		if ( $str[$i+1] == '/' ) {
			$tagname = strtolower(substr($str, $i+2, $k-$i-2));	
			if( @end($tagindex[$tagname]) ) {
				$k = key($tagindex[$tagname]);
				unset($tagindex[$tagname][$k]);
				unset($tree[$k]);
			}	
		}
		else {
			$tagname = strtolower(substr($str, $i+1, $k-$i-1));
			switch ( $tagname ) 
			{
				case "br":
				case "img":
				case "meta":
					break;
				default:
					$tree[] = $tagname;
					end($tree);
					$tagindex[$tagname][key($tree)] = 1;
			}
		}
		$i = $p;
	}
	
	$data = substr($str, 0, $i);
	if ( is_array($tree) ) {
		$tree = array_reverse($tree);
		reset($tree);	
		while( list(,$v) = each($tree) ) $data .= '</'.$v.'>';
	}

	return $data;
}

?>