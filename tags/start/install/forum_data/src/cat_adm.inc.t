<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: cat_adm.inc.t,v 1.1.1.1 2002/06/17 23:00:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

class fud_cat_adm extends fud_cat
{
	function make_space($view_order, $max)
	{
		$result = Q("SELECT id FROM {SQL_TABLE_PREFIX}cat WHERE view_order=".$view_order);
		
		if ( IS_RESULT($result) ) {
			QF($result);
			$max++;
			Q("UPDATE {SQL_TABLE_PREFIX}cat SET view_order=view_order+1 WHERE view_order>=".$view_order." AND view_order<".$max);
		}
		return;
	}
	
	function get_max_view()
	{
		return Q_SINGLEVAL("SELECT max(view_order) FROM {SQL_TABLE_PREFIX}cat");
	}
	
	function add_cat($pos)
	{
		
		$creation_date = __request_timestamp__;		
		
		if( !db_locked() ) {
			DB_LOCK('{SQL_TABLE_PREFIX}cat+');
			$local_lock = 1;
		}	

		$max = $this->get_max_view();
		if ( $pos == "FIRST" ) {
			$this->make_space(1, $max);
			$this->view_order = 1;
		}
		else if ( $max ) {
			$this->view_order = $max+1;
		}
		else $this->view_order = 1;
		
		Q("INSERT INTO {SQL_TABLE_PREFIX}cat (name, description, allow_collapse, default_view, creation_date, view_order) VALUES('".$this->name."','".$this->description."','".$this->allow_collapse."','".$this->default_view."','".$creation_date."','".$this->view_order."')");
		
		$this->id = DB_LASTID();
		
		if ( $local_lock ) DB_UNLOCK();
		
		return $this->id;
	}
	
	function sync()
	{
		Q("UPDATE {SQL_TABLE_PREFIX}cat SET name='".$this->name."',description='".$this->description."',allow_collapse='".$this->allow_collapse."',default_view='".$this->default_view."',view_order='".$this->view_order."' WHERE id=".$this->id);
	}
	
	function change_pos($cur, $new)
	{
		if( !db_locked() ) {
			DB_LOCK('{SQL_TABLE_PREFIX}cat+');
			$local_lock = 1;
		}

		$max = $this->get_max_view();
		Q("UPDATE {SQL_TABLE_PREFIX}cat SET view_order=420000000 WHERE view_order=$cur");
		
		if ( $new < $cur ) {
			$this->make_space($new, $cur);			
		}
		else {
			$this->move_down($cur, $new);
		}
		
		Q("UPDATE {SQL_TABLE_PREFIX}cat SET view_order=$new WHERE view_order=420000000");		
		if ( $local_lock ) DB_UNLOCK();
	}
	
	function move_down($from, $max)
	{
		Q("UPDATE {SQL_TABLE_PREFIX}cat SET view_order=view_order-1 WHERE view_order>=$from AND view_order<=$max");
	}
	
	function get_all_cat()
	{
		$result = Q("SELECT * FROM {SQL_TABLE_PREFIX}cat ORDER BY view_order");
		unset($this->cat_list);
		
		$i=0;
		while ( $obj = DB_ROWOBJ($result) ) {
			$this->cat_list[$i++] = $obj;
		}
		
		QF($result);
		$this->cur_cat = 0;
	}
	
	function resetcat()
	{
		$this->cur_cat = 0;
	}
	
	function countcat()
	{
		return @count($this->cat_list);
	}
	
	function nextcat()
	{
		if ( !isset($this->cat_list[$this->cur_cat]) ) return;
	
		$this->id = $this->cat_list[$this->cur_cat]->id;
		$this->name = $this->cat_list[$this->cur_cat]->name;
		$this->description = $this->cat_list[$this->cur_cat]->description;
		$this->allow_collapse = $this->cat_list[$this->cur_cat]->allow_collapse;
		$this->default_view = $this->cat_list[$this->cur_cat]->default_view;
		$this->creation_date = $this->cat_list[$this->cur_cat]->creation_date;
		$this->view_order = $this->cat_list[$this->cur_cat]->view_order;
		
		$this->cur_cat++;
		
		return 1;
	}
	
	function fetch_vars($array, $prefix)
	{
		$this->name = $array[$prefix.'name'];
		$this->description = $array[$prefix.'description'];
		$this->allow_collapse = $array[$prefix.'allow_collapse'];
		$this->default_view = $array[$prefix.'default_view'];
	}	
	
	function export_vars($prefix)
	{	
		$GLOBALS[$prefix.'name'] = $this->name;
		$GLOBALS[$prefix.'description'] = $this->description;
		$GLOBALS[$prefix.'allow_collapse'] = $this->allow_collapse;
		$GLOBALS[$prefix.'default_view'] = $this->default_view;
		$GLOBALS[$prefix.'creation_date'] = $this->creation_date;
		$GLOBALS[$prefix.'view_order'] = $this->view_order;
	}

	function delete($id)
	{
		DB_LOCK('{SQL_TABLE_PREFIX}cat+, {SQL_TABLE_PREFIX}forum+');
		
		$view_order = Q_SINGLEVAL("SELECT view_order FROM {SQL_TABLE_PREFIX}cat WHERE id=".$id);
		
		Q("UPDATE {SQL_TABLE_PREFIX}forum SET cat_id=0 WHERE cat_id=".$id);
		$max = $this->get_max_view();
		
		$this->move_down($view_order+1, $max);
		
		Q("DELETE FROM {SQL_TABLE_PREFIX}cat WHERE id=".$id);
		DB_UNLOCK();
	}

}

function create_cat_select($name, $def, $blocked)
{
	$result = Q("SELECT id, name FROM {SQL_TABLE_PREFIX}cat WHERE id!='".$blocked."' ORDER BY view_order");

	if( !IS_RESULT($result) ) return;
	
	$sel = '<select name="'.$name.'">';
	
	while ( $obj = DB_ROWOBJ($result) ) {
		$selected = ( $obj->id == $def ) ? ' selected':'';
		$sel .= '<option value="'.$obj->id.'"'.$selected.'>'.$obj->name."\n";
	}
	QF($result);
	$sel .= '</select>';
	return $sel;
}

function draw_cat_select($name, $def)
{
	echo create_cat_select($name, $def, 0);
}
?>