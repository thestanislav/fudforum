<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: mlist.inc,v 1.3 2002/07/24 16:39:44 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

class fud_mlist
{
	var $id, $forum_id, $name;
	var $mlist_post_apr, $allow_frm_post, $frm_post_apr, $allow_mlist_attch, $allow_mlist_html, $complex_reply_match;
	var $subject_regex_haystack, $subject_regex_needle, $body_regex_haystack, $body_regex_needle;

	function get($id)
	{
		qobj("SELECT * FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."mlist WHERE id=".$id, $this);
		if( empty($this->id) ) exit("Fatal Error, no mailing list with id of $id\n");
	}
	
	function add()
	{
		$r = q("INSERT INTO ".$GLOBALS['DBHOST_TBL_PREFIX']."mlist(
			forum_id,
			name,
			mlist_post_apr,
			allow_frm_post,
			frm_post_apr,
			allow_mlist_attch,
			allow_mlist_html,
			complex_reply_match,
			subject_regex_haystack,
			subject_regex_needle,
			body_regex_haystack,
			body_regex_needle
			)
			VALUES(
			".$this->forum_id.",
			'".$this->name."',
			'".yn($this->mlist_post_apr)."',
			'".yn($this->allow_frm_post)."',
			'".yn($this->frm_post_apr)."',
			'".yn($this->allow_mlist_attch)."',
			'".yn($this->allow_mlist_html)."',
			'".yn($this->complex_reply_match)."',
			'".$this->subject_regex_haystack."',
			'".$this->subject_regex_needle."',
			'".$this->body_regex_haystack."',
			'".$this->body_regex_needle."'
			)");

		$this->id = db_lastid($GLOBALS['DBHOST_TBL_PREFIX']."mlist", $r);
		$this->Update_forum_settings();
		
		return $this->id;
	}
	
	function sync()
	{
		q("UPDATE ".$GLOBALS['DBHOST_TBL_PREFIX']."mlist SET
			forum_id=".$this->forum_id.",
			name='".$this->name."',
			mlist_post_apr='".yn($this->mlist_post_apr)."',
			allow_frm_post='".yn($this->allow_frm_post)."',
			frm_post_apr='".yn($this->frm_post_apr)."',
			allow_mlist_attch='".yn($this->allow_mlist_attch)."',
			allow_mlist_html='".yn($this->allow_mlist_html)."',
			complex_reply_match='".yn($this->complex_reply_match)."',
			subject_regex_haystack='".$this->subject_regex_haystack."',
			subject_regex_needle='".$this->subject_regex_needle."',
			body_regex_haystack='".$this->body_regex_haystack."',
			body_regex_needle='".$this->body_regex_needle."'
		WHERE id=".$this->id);
		
		$this->Update_forum_settings();
	}
	
	function del($id='')
	{
		if( $id ) $this->get($id);
	
		q("DELETE FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."mlist WHERE id=".$this->id);
		if( yn($this->frm_post_apr) ) {
			$this->frm_post_apr = 'N';
			$this->update_forum_settings();
		}
	}
	
	function update_forum_settings()
	{
		if( !db_locked() ) {
			$ll = 1;
			db_lock($GLOBALS['DBHOST_TBL_PREFIX']."forum+");
		}
		
		q("UPDATE ".$GLOBALS['DBHOST_TBL_PREFIX']."forum SET moderated='".yn($this->frm_post_apr)."' WHERE id=".$this->forum_id);
		
		if( $ll ) db_unlock();
	}
}
?>