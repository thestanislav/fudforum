<?php
/**
* copyright            : (C) 2001-2006 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: ratethread.php.t,v 1.18 2006/04/02 18:46:18 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
**/

/*{PRE_HTML_PHP}*/
/*{POST_HTML_PHP}*/

	if (isset($_GET['rate_thread_id'], $_GET['sel_vote']) && ($rt = (int) $_GET['sel_vote'])) {
		$th = (int) $_GET['rate_thread_id'];

		/* determine if the user has permission to rate the thread */
		if (!q_singleval('SELECT t.id
				FROM {SQL_TABLE_PREFIX}thread t
				LEFT JOIN {SQL_TABLE_PREFIX}mod m ON t.forum_id=m.forum_id AND m.user_id='._uid.'
				INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id='.(_uid ? 2147483647 : 0).' AND g1.resource_id=t.forum_id
				'.(_uid ? ' LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=t.forum_id ' : '').'
				WHERE t.id='.$th.($is_a ? '' : ' AND (m.id IS NOT NULL OR ('.(_uid ? 'COALESCE(g2.group_cache_opt, g1.group_cache_opt)' : 'g1.group_cache_opt').' & 1024) > 0)')  . ' LIMIT 1')) {
			std_error('access');
		}

		if (db_li('INSERT INTO {SQL_TABLE_PREFIX}thread_rate_track (thread_id, user_id, stamp, rating) VALUES('.$th.', '._uid.', '.__request_timestamp__.', '.$rt.')', $ef)) {
			$rt = db_saq('SELECT count(*), ROUND(AVG(rating)) FROM {SQL_TABLE_PREFIX}thread_rate_track WHERE thread_id='.$th);
			q('UPDATE {SQL_TABLE_PREFIX}thread SET rating='.(int)$rt[1].', n_rating='.(int)$rt[0].' WHERE id='.$th);

			$frm = new StdClass;
			$frm->n_rating = (int) $rt[0];
			$frm->rating = (int) $rt[1];

			exit('{TEMPLATE: thread_rating}');
		}
	}