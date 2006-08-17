<?php
/**
* copyright            : (C) 2001-2006 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: tree_msg.php.t,v 1.4 2006/08/07 19:01:54 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
**/

/*{PRE_HTML_PHP}*/
if (empty($_GET['id']) || ($mid = (int)$_GET['id']) < 1) {
	invl_inp_err();
}

	make_perms_query($fields, $join);

$msg_obj = db_sab('SELECT
	m.*,
	t.thread_opt, t.root_msg_id, t.last_post_id, t.forum_id,
	f.message_threshold,
	u.id AS user_id, u.alias AS login, u.avatar_loc, u.email, u.posted_msg_count, u.join_date, u.location,
	u.sig, u.custom_status, u.icq, u.jabber, u.affero, u.aim, u.msnm, u.yahoo, u.skype, u.google, u.last_visit AS time_sec, u.users_opt,
	l.name AS level_name, l.level_opt, l.img AS level_img,
	p.max_votes, p.expiry_date, p.creation_date, p.name AS poll_name, p.total_votes,
	'.(_uid ? ' pot.id AS cant_vote, r.last_view, r2.last_view AS last_forum_view ' : ' 1 AS cant_vote ').',
	'.$fields.', mo.id AS md
FROM
	{SQL_TABLE_PREFIX}msg m
	INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id
	INNER JOIN {SQL_TABLE_PREFIX}forum f ON t.forum_id=f.id
	'.$join.'
	LEFT JOIN {SQL_TABLE_PREFIX}mod mo ON mo.user_id='._uid.' AND mo.forum_id=t.forum_id
	LEFT JOIN {SQL_TABLE_PREFIX}users u ON m.poster_id=u.id
	LEFT JOIN {SQL_TABLE_PREFIX}level l ON u.level_id=l.id
	LEFT JOIN {SQL_TABLE_PREFIX}poll p ON m.poll_id=p.id'.
	(_uid ? ' 
		LEFT JOIN {SQL_TABLE_PREFIX}poll_opt_track pot ON pot.poll_id=p.id AND pot.user_id='._uid.'
		LEFT JOIN {SQL_TABLE_PREFIX}read r ON r.thread_id=t.id AND r.user_id='._uid.'
		LEFT JOIN {SQL_TABLE_PREFIX}forum_read r2 ON r2.forum_id=t.forum_id AND r2.user_id='._uid
	 : ' ').'
WHERE
	m.id='.$mid.' AND m.apr=1');

	if (!$msg_obj) { // invalid message id
		invl_inp_err();
	}

	$perms = perms_from_obj($msg_obj, $is_a);
	if (!($perms & 2)) {
		exit;
	}

	$n = 0;
	$pn = array(
		q_singleval('SELECT m.id FROM {SQL_TABLE_PREFIX}thread t INNER JOIN {SQL_TABLE_PREFIX}msg m ON m.thread_id=t.id WHERE t.id='.$msg_obj->thread_id.' AND m.apr=1 AND m.post_stamp < '.$msg_obj->post_stamp.' ORDER BY m.post_stamp DESC LIMIT 1')
		,
		q_singleval('SELECT m.id FROM {SQL_TABLE_PREFIX}thread t INNER JOIN {SQL_TABLE_PREFIX}msg m ON m.thread_id=t.id WHERE t.id='.$msg_obj->thread_id.' AND m.apr=1 AND m.post_stamp > '.$msg_obj->post_stamp.' ORDER BY m.post_stamp ASC LIMIT 1') 
	);
	$usr->md = $msg_obj->md;

	$simple_lang = array("dutch"=>1,"english"=>1,"finnish"=>1,"french"=>1,"german"=>1,"german_formal"=>1,"indonesian"=>1,"italian"=>1,"norwegian"=>1,"portuguese"=>1,"portuguese_br"=>1,"spanish"=>1,"swedish"=>1);
	if (isset($simple_lang[$usr->lang])) {
		$charset = 'ISO-8859-15';
	} else {
		$charset = trim(file_get_contents($DATA_DIR.'thm/default/i18n/'.$usr->lang.'/charset'));
		if (!$charset) {
			$charset = 'ISO-8859-15';
		}
	}
	header("Content-Type: text/html; charset=".$charset);

/*{POST_HTML_PHP}*/
/*{POST_PAGE_PHP_CODE}*/
?>
{TEMPLATE: TREE_MSG_PAGE}
<?php
	while (@ob_end_flush());
	th_inc_view_count($msg_obj->thread_id);
	if (_uid && $msg_obj) {
		if ($msg_obj->last_forum_view < $msg_obj->post_stamp) {
			user_register_forum_view($msg_obj->forum_id);
		}
		if ($msg_obj->last_view < $msg_obj->post_stamp) {
			user_register_thread_view($msg_obj->thread_id, $msg_obj->post_stamp, $msg_obj->id);
		}
	}
?>