<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: logedin.inc.t,v 1.16 2003/04/06 13:36:48 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

function rebuild_stats_cache($last_msg_id)
{
	$tm_expire = __request_timestamp__ - ($GLOBALS['LOGEDIN_TIMEOUT'] * 60);

	list($obj->last_user_id, $obj->user_count) = db_saq('SELECT MAX(id),count(*) FROM {SQL_TABLE_PREFIX}users');

	$obj->online_users_anon	= q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}ses s WHERE time_sec>'.$tm_expire.' AND user_id>2000000000');
	$obj->online_users_hidden = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}ses s INNER JOIN {SQL_TABLE_PREFIX}users u ON u.id=s.user_id WHERE s.time_sec>'.$tm_expire.' AND u.invisible_mode=\'Y\'');
	$obj->online_users_reg = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}ses s INNER JOIN {SQL_TABLE_PREFIX}users u ON u.id=s.user_id WHERE s.time_sec>'.$tm_expire.' AND u.invisible_mode=\'N\'');
	$c = uq('SELECT u.id, u.alias, u.is_mod, u.custom_color FROM {SQL_TABLE_PREFIX}ses s INNER JOIN {SQL_TABLE_PREFIX}users u ON u.id=s.user_id WHERE s.time_sec>'.$tm_expire.' AND u.invisible_mode=\'N\' ORDER BY s.time_sec DESC LIMIT '.$GLOBALS['MAX_LOGGEDIN_USERS']);
	while ($r = db_rowarr($c)) {
		$obj->online_users_text[$r[0]] = draw_user_link($r[1], $r[2], $r[3]);
	}
	qf($c);

	q('UPDATE {SQL_TABLE_PREFIX}stats_cache SET
		cache_age='.__request_timestamp__.',		
		last_user_id='.intzero($obj->last_user_id).',
		user_count='.intzero($obj->user_count).',
		online_users_anon='.intzero($obj->online_users_anon).',
		online_users_hidden='.intzero($obj->online_users_hidden).',
		online_users_reg='.intzero($obj->online_users_reg).',
		online_users_text='.strnull(addslashes(@serialize($obj->online_users_text))));

	$obj->last_user_alias = q_singleval('SELECT alias FROM {SQL_TABLE_PREFIX}users WHERE id='.$obj->last_user_id);
	$obj->last_msg_subject = q_singleval('SELECT subject FROM {SQL_TABLE_PREFIX}msg WHERE id='.$last_msg_id);

	return $obj;
}

$logedin = $forum_info = '';

if ($GLOBALS['LOGEDIN_LIST'] == 'Y' || $GLOBALS['FORUM_INFO'] == 'Y') {
	if (!($st_obj = db_sab('SELECT sc.*,m.subject AS last_msg_subject, u.alias AS last_user_alias FROM {SQL_TABLE_PREFIX}stats_cache sc INNER JOIN {SQL_TABLE_PREFIX}users u ON u.id=sc.last_user_id INNER JOIN {SQL_TABLE_PREFIX}msg m ON m.id='.$last_msg_id.' WHERE sc.cache_age>'.(__request_timestamp__ - $GLOBALS['STATS_CACHE_AGE'])))) {
		$st_obj =& rebuild_stats_cache($last_msg_id);
	} else if ($st_obj->online_users_text) {
		$st_obj->online_users_text = @unserialize($st_obj->online_users_text);
	}

	$i_spy = $GLOBALS['ACTION_LIST_ENABLED'] == 'Y' ? '{TEMPLATE: i_spy}' : '';

	if ($GLOBALS['LOGEDIN_LIST'] == 'Y' && @count($st_obj->online_users_text)) {
		foreach($st_obj->online_users_text as $k => $v) {
			$logedin .= '{TEMPLATE: online_user_link}' . '{TEMPLATE: online_user_separator}';
		}
		$logedin = '{TEMPLATE: logedin}';
	}
	if ($GLOBALS['FORUM_INFO'] == 'Y') {
		$last_msg = $last_msg_id ? '{TEMPLATE: last_msg}' : '';
		$forum_info = '{TEMPLATE: forum_info}';
	}
}

$loged_in_list = ($logedin || $forum_info) ? '{TEMPLATE: loged_in_list}' : '';
?>
