<?php
/***************************************************************************
* copyright            : (C) 2001-2004 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: csv.php.t,v 1.1.2.3 2004/10/08 00:05:02 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
***************************************************************************/

/*{PRE_HTML_PHP}*/
/*{POST_HTML_PHP}*/

	$_GET['id'] = isset($_GET['id']) ? (int) $_GET['id'] : 0;

	if (!($usr->users_opt & (524288|1048576))) {
		invl_inp_err();
	}
	if (!$_GET['id'] || !q_singleval("SELECT id FROM {SQL_TABLE_PREFIX}thread WHERE id={$_GET['id']}")) {
		invl_inp_err();
	}
	
	$r = uq("SELECT m.*, u.login, f.name FROM {SQL_TABLE_PREFIX}msg m
		INNER JOIN {SQL_TABLE_PREFIX}users u ON m.poster_id=u.id
		INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id
		INNER JOIN {SQL_TABLE_PREFIX}forum f ON f.id=t.forum_id
		WHERE m.thread_id=".$_GET['id']." AND m.apr=1  ORDER BY m.id ASC");
	
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: inline; filename=thread_{$_GET['id']}.csv");	

	while ($obj = db_rowobj($r)) {
		$body = html_entity_decode(strip_tags(read_msg_body($obj->foff, $obj->length, $obj->file_id)));
		$obj->login = $obj->login;
		$obj->subject = html_entity_decode($obj->subject);
		$obj->name = html_entity_decode($obj->name);
		$time = '{TEMPLATE: dmsg_post_date}';
		$body = preg_replace('!\s+!', ' ', $body);

		// author, time, forum, subject, text
		echo "{$obj->login}\t{$time}\t{$obj->name}\t{$obj->subject}\t{$body}\r\n";
	}
?>