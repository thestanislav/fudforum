<?php
/**
* copyright            : (C) 2001-2006 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: buddy.inc.t,v 1.15 2005/12/07 18:07:45 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
**/

function buddy_add($user_id, $bud_id)
{
	q('INSERT INTO {SQL_TABLE_PREFIX}buddy (bud_id, user_id) VALUES ('.$bud_id.', '.$user_id.')');
	return buddy_rebuild_cache($user_id);
}

function buddy_delete($user_id, $bud_id)
{
	q('DELETE FROM {SQL_TABLE_PREFIX}buddy WHERE user_id='.$user_id.' AND bud_id='.$bud_id);
	return buddy_rebuild_cache($user_id);
}

function buddy_rebuild_cache($uid)
{
	$arr = array();
	$q = uq('SELECT bud_id FROM {SQL_TABLE_PREFIX}buddy WHERE user_id='.$uid);
	while ($ent = db_rowarr($q)) {
		$arr[$ent[0]] = 1;
	}
	unset($q);

	if ($arr) {
		q('UPDATE {SQL_TABLE_PREFIX}users SET buddy_list='._esc(serialize($arr)).' WHERE id='.$uid);
		return $arr;
	}
	q('UPDATE {SQL_TABLE_PREFIX}users SET buddy_list=NULL WHERE id='.$uid);
}
?>