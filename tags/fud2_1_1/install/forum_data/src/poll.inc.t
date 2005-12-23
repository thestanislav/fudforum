<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: poll.inc.t,v 1.2 2002/06/18 18:26:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	
class fud_poll
{
	var $id;
	var $name=NULL;
	var $owner=NULL;
	var $creation_date=NULL;
	var $expiry_date=NULL;
	var $max_votes=NULL;
	
	function add()
	{
		q("INSERT INTO {SQL_TABLE_PREFIX}poll (
			name, 
			owner, 
			creation_date, 
			expiry_date,
			max_votes
			) 
			VALUES(
			'".$this->name."',
			".$this->owner.",
			".__request_timestamp__.",
			".intzero($this->expiry_date).",
			0
			)");
		$this->id = db_lastid();
		
		return $this->id;
	}
	
	function sync()
	{
		q("UPDATE {SQL_TABLE_PREFIX}poll SET 
			name='".$this->name."',
			expiry_date=".intzero($this->expiry_date).",
			max_votes=".intzero($this->max_votes)."
		WHERE id=".$this->id);
	}
	
	function get($id) 
	{
		qobj("SELECT * FROM {SQL_TABLE_PREFIX}poll WHERE id=".$id, $this);
	}
	
	function delete()
	{
		q("DELETE FROM {SQL_TABLE_PREFIX}poll_opt WHERE poll_id=".$this->id);
		q("DELETE FROM {SQL_TABLE_PREFIX}poll_opt_track WHERE poll_id=".$this->id);
		q("DELETE FROM {SQL_TABLE_PREFIX}poll WHERE id=".$this->id);
	}
	
	function regvote($user_id)
	{
		q("INSERT INTO {SQL_TABLE_PREFIX}poll_opt_track(poll_id, user_id) VALUES(".$this->id.", ".$user_id.")");
	}
	
	function voted($user_id)
	{
		return q_singleval("SELECT id FROM {SQL_TABLE_PREFIX}poll_opt_track WHERE poll_id=".$this->id." AND user_id=".$user_id);
	}
}

class fud_poll_opt
{
	var $id=NULL;
	var $poll_id=NULL;
	var $name=NULL;
	var $count=NULL;
	
	var $all=NULL;
	var $all_c=NULL;
	
	function add()
	{
		q("INSERT INTO {SQL_TABLE_PREFIX}poll_opt (poll_id, name, count) VALUES (".$this->poll_id.",'".$this->name."',".intzero($this->count).")");
		return db_lastid();
	}
	
	function sync()
	{
		q("UPDATE {SQL_TABLE_PREFIX}poll_opt SET name='".$this->name."' WHERE id=".$this->id);
	}
	
	function get($id)
	{
		qobj("SELECT * FROM {SQL_TABLE_PREFIX}poll_opt WHERE id=".$id, $this);
	}
	
	function delete()
	{
		q("DELETE FROM {SQL_TABLE_PREFIX}poll_opt WHERE id=".$this->id);
	}
	
	function fetch_vars($array, $prefix)
	{
		$this->name = $array[$prefix.'name'];
	}
	
	function increase()
	{
		q("UPDATE {SQL_TABLE_PREFIX}poll_opt SET count=count+1 WHERE id=".$this->id);
	}
	
	function get_poll($pl_id) 
	{
		$res = q("SELECT * FROM {SQL_TABLE_PREFIX}poll_opt WHERE poll_id=".$pl_id." ORDER BY id");
		if ( !is_result($res) ) return;
		
		unset($this->all);
		$this->all_c = 0;
		while ( $obj=db_rowobj($res) ) {
			$this->all[] = $obj;
		}
		
		qf($res);
	}
	
	function reset_opt()
	{
		if ( isset($this->all) ) reset($this->all);
	}
	
	function count_opt()
	{
		if ( isset($this->all) ) return count($this->all);
		return;
	}
	
	function next_opt()
	{
		if ( $this->all_c > count($this->all) ) return;
		return ( isset($this->all[$this->all_c])?$this->all[$this->all_c++]:'');
	}
}
?>