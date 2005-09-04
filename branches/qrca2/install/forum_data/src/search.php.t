<?php
/***************************************************************************
* copyright            : (C) 2001-2004 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: search.php.t,v 1.47.2.3 2004/10/13 22:55:54 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
***************************************************************************/

/*{PRE_HTML_PHP}*/

	if (!($FUD_OPT_1 & 16777216)) {
		std_error('disabled');
	}
	if (!isset($_GET['start']) || !($start = (int)$_GET['start'])) {
		$start = 0;
	}

	$ppg = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;
	$srch = isset($_GET['srch']) ? trim($_GET['srch']) : '';
	$forum_limiter = isset($_GET['forum_limiter']) ? $_GET['forum_limiter'] : '';
	$field = !isset($_GET['field']) ? 'all' : ($_GET['field'] == 'subject' ? 'subject' : 'all');
	$search_logic = (isset($_GET['search_logic']) && $_GET['search_logic'] == 'AND') ? 'AND' : 'OR';
	$sort_order = (isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') ? 'ASC' : 'DESC';
	$rng = isset($_GET['rng']) ? (float) $_GET['rng'] : 0;
	$rng2 = !empty($_GET['rng2']) ? (float) $_GET['rng2'] : 0;
	$unit = !empty($_GET['u']) ? (int) $_GET['u'] : 86400;

	if (!empty($_GET['author'])) {
		$author = $_GET['author'];
		$author_id = array();
		$c = uq("SELECT id FROM {SQL_TABLE_PREFIX}users WHERE alias LIKE '".addslashes(str_replace('*', '%', $author))."'");
		while (($r = db_rowarr($c))) {
			$author_id[] = $r[0];
		}
		if (!$author_id) {
			$author_id[] = 0;
		}
	} else {
		$author = $author_id = '';
	}

	$date_limit = '';
	if ($rng) {
		$date_limit .= ' AND m.post_stamp > '.(__request_timestamp__ - round($rng * $unit)).' ';
	}
	if ($rng2) {
		$date_limit .= ' AND m.post_stamp < '.(__request_timestamp__ - round($rng2 * $unit)).' ';
	} 

function fetch_search_cache($qry, $start, $count, $logic, $srch_type, $order, $forum_limiter, &$total)
{
	$wa = text_to_worda($qry);
	$lang =& $GLOBALS['usr']->lang;
	
	if ($lang != 'chinese_big5' && $lang != 'chinese' && $lang != 'japanese') {
		if (count($wa) > 10) {
			$wa = array_slice($wa, 0, 10);
		}
	}
	$qr = implode(',', $wa);
	$i = count($wa);

	if ($srch_type == 'all') {
		$tbl = 'index';
		$qt = '0';
	} else {
		$tbl = 'title_index';
		$qt = '1';
	}

	if (empty($qr)) {
		return;
	}

	$qry_lck = md5($qr);

	/* remove expired cache */
	q('DELETE FROM {SQL_TABLE_PREFIX}search_cache WHERE expiry<'.(__request_timestamp__ - $GLOBALS['SEARCH_CACHE_EXPIRY']));

	if (!($total = q_singleval("SELECT count(*) FROM {SQL_TABLE_PREFIX}search_cache WHERE query_type=".$qt." AND srch_query='".$qry_lck."'"))) {
		if (__dbtype__ == 'mysql') {
			q("INSERT IGNORE INTO {SQL_TABLE_PREFIX}search_cache (srch_query, query_type, expiry, msg_id, n_match) SELECT '".$qry_lck."', ".$qt.", ".__request_timestamp__.", msg_id, count(*) as word_count FROM {SQL_TABLE_PREFIX}search s INNER JOIN {SQL_TABLE_PREFIX}".$tbl." i ON i.word_id=s.id WHERE word IN(".$qr.") GROUP BY msg_id ORDER BY word_count DESC LIMIT 500");
			if (!($total = (int) db_affected())) {
				return;
			}
		} else {
			q("BEGIN; DELETE FROM {SQL_TABLE_PREFIX}search_cache; INSERT INTO {SQL_TABLE_PREFIX}search_cache (srch_query, query_type, expiry, msg_id, n_match) SELECT '".$qry_lck."', ".$qt.", ".__request_timestamp__.", msg_id, count(*) as word_count FROM {SQL_TABLE_PREFIX}search s INNER JOIN {SQL_TABLE_PREFIX}".$tbl." i ON i.word_id=s.id WHERE word IN(".$qr.") GROUP BY msg_id ORDER BY word_count DESC LIMIT 500; COMMIT;");
		}
	}

	if ($forum_limiter) {
		if ($forum_limiter[0] != 'c') {
			$qry_lmt = ' AND f.id=' . (int)$forum_limiter . ' ';
		} else {
			$qry_lmt = ' AND c.id=' . (int)substr($forum_limiter, 1) . ' ';
		}
	} else {
		$qry_lmt = '';
	}

	if ($GLOBALS['date_limit']) {
		 $qry_lmt .= $GLOBALS['date_limit'];
	}

	if ($GLOBALS['author_id']) {
		$qry_lmt = ' AND m.poster_id IN ('.implode(',', $GLOBALS['author_id']).') ';
	}

	$qry_lck = "'" . $qry_lck . "'";

	$total = q_singleval('SELECT count(*)
		FROM {SQL_TABLE_PREFIX}search_cache sc
		INNER JOIN {SQL_TABLE_PREFIX}msg m ON m.id=sc.msg_id
		INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id
		INNER JOIN {SQL_TABLE_PREFIX}forum f ON t.forum_id=f.id
		INNER JOIN {SQL_TABLE_PREFIX}cat c ON f.cat_id=c.id
		INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id='.(_uid ? '2147483647' : '0').' AND g1.resource_id=f.id
		LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=f.id AND mm.user_id='._uid.'
		LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=f.id
		WHERE
			sc.query_type='.$qt.' AND sc.srch_query='.$qry_lck.$qry_lmt.'
			'.($logic == 'AND' ? ' AND sc.n_match>='.$i : '').'
			'.($GLOBALS['usr']->users_opt & 1048576 ? '' : ' AND (mm.id IS NOT NULL OR ((CASE WHEN g2.id IS NOT NULL THEN g2.group_cache_opt ELSE g1.group_cache_opt END) & 262146) >= 262146)'));
	if (!$total) {
		return;
	}

	return uq('SELECT u.alias, f.name AS forum_name, f.id AS forum_id,
			m.poster_id, m.id, m.thread_id, m.subject, m.poster_id, m.foff, m.length, m.post_stamp, m.file_id, m.icon
		FROM {SQL_TABLE_PREFIX}search_cache sc
		INNER JOIN {SQL_TABLE_PREFIX}msg m ON m.id=sc.msg_id
		INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id
		INNER JOIN {SQL_TABLE_PREFIX}forum f ON t.forum_id=f.id
		INNER JOIN {SQL_TABLE_PREFIX}cat c ON f.cat_id=c.id
		INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id='.(_uid ? '2147483647' : '0').' AND g1.resource_id=f.id
		LEFT JOIN {SQL_TABLE_PREFIX}users u ON m.poster_id=u.id
		LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=f.id AND mm.user_id='._uid.'
		LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=f.id
		WHERE
			sc.query_type='.$qt.' AND sc.srch_query='.$qry_lck.$qry_lmt.'
			'.($logic == 'AND' ? ' AND sc.n_match>='.$i : '').'
			'.($GLOBALS['usr']->users_opt & 1048576 ? '' : ' AND (mm.id IS NOT NULL OR ((CASE WHEN g2.id IS NOT NULL THEN g2.group_cache_opt ELSE g1.group_cache_opt END) & 262146) >= 262146)').'
		ORDER BY sc.n_match DESC, m.post_stamp '.$order.' LIMIT '.qry_limit($count, $start));
}

function by_author_search($author_id, $order, $forum_limiter, $start, $count, &$total)
{
	if ($forum_limiter) {
		if ($forum_limiter[0] != 'c') {
			$qry_lmt = ' AND f.id=' . (int)$forum_limiter . ' ';
		} else {
			$qry_lmt = ' AND c.id=' . (int)substr($forum_limiter, 1) . ' ';
		}
	} else {
		$qry_lmt = '';
	}

	if ($GLOBALS['date_limit']) {
		 $qry_lmt .= $GLOBALS['date_limit'];
	}

	if (!($total = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}msg m
			INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id
			INNER JOIN {SQL_TABLE_PREFIX}forum f ON t.forum_id=f.id
			INNER JOIN {SQL_TABLE_PREFIX}cat c ON f.cat_id=c.id
			LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=f.id AND mm.user_id='._uid.'
			LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=f.id
			WHERE
				m.poster_id IN('.implode(',', $author_id).') AND m.apr=1'.$qry_lmt.
				($GLOBALS['usr']->users_opt & 1048576 ? '' : ' AND (mm.id IS NOT NULL OR ((CASE WHEN g2.id IS NOT NULL THEN g2.group_cache_opt ELSE g1.group_cache_opt END) & 262146) >= 262146)')
			)
	)) {
		return;			
	}				

	return uq('SELECT u.alias, f.name AS forum_name, f.id AS forum_id,
			m.poster_id, m.id, m.thread_id, m.subject, m.poster_id, m.foff, m.length, m.post_stamp, m.file_id, m.icon
		FROM {SQL_TABLE_PREFIX}msg m
		INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id
		INNER JOIN {SQL_TABLE_PREFIX}forum f ON t.forum_id=f.id
		INNER JOIN {SQL_TABLE_PREFIX}cat c ON f.cat_id=c.id
		INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id='.(_uid ? '2147483647' : '0').' AND g1.resource_id=f.id
		LEFT JOIN {SQL_TABLE_PREFIX}users u ON m.poster_id=u.id
		LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=f.id AND mm.user_id='._uid.'
		LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=f.id
		WHERE
			m.poster_id IN('.implode(',', $author_id).') AND m.apr=1
			'.$qry_lmt.'
			'.($GLOBALS['usr']->users_opt & 1048576 ? '' : ' AND (mm.id IS NOT NULL OR ((CASE WHEN g2.id IS NOT NULL THEN g2.group_cache_opt ELSE g1.group_cache_opt END) & 262146) >= 262146)').'
		ORDER BY m.poster_id, m.post_stamp '.$order.' LIMIT '.qry_limit($count, $start));	
}

/*{POST_HTML_PHP}*/

	$search_options = tmpl_draw_radio_opt('field', "all\nsubject", "{TEMPLATE: search_entire_msg}\n{TEMPLATE: search_subect_only}", $field, '{TEMPLATE: radio_button}', '{TEMPLATE: radio_button_selected}', '{TEMPLATE: radio_button_separator}');
	$logic_options = tmpl_draw_select_opt("OR\nAND", "{TEMPLATE: search_or}\n{TEMPLATE: search_and}", $search_logic, '{TEMPLATE: search_normal_option}', '{TEMPLATE: search_selected_option}');
	$sort_options = tmpl_draw_select_opt("DESC\nASC", "{TEMPLATE: search_desc_order}\n{TEMPLATE: search_asc_order}", $sort_order, '{TEMPLATE: search_normal_option}', '{TEMPLATE: search_selected_option}');
	$mnav_time_unit = tmpl_draw_select_opt("60\n3600\n86400\n604800\n2635200", "{TEMPLATE: mnav_minute}\n{TEMPLATE: mnav_hour}\n{TEMPLATE: mnav_day}\n{TEMPLATE: mnav_week}\n{TEMPLATE: mnav_month}", $unit, '', '');
	$rng_sel = tmpl_draw_select_opt("0\n365\n93\n31", "Forever all messages\nWithin the last 12 months\nWithin the last 3 months\nWithin the last month", $rng, '', '');

	$TITLE_EXTRA = ': {TEMPLATE: search_title}';

	ses_update_status($usr->sid, '{TEMPLATE: search_update}');

	$page_pager = '';
	if ($srch || $author_id) {
		if (
			($srch && !($c =& fetch_search_cache($srch, $start, $ppg, $search_logic, $field, $sort_order, $forum_limiter, $total))) || 
			(!$srch && $author_id && !($c =& by_author_search($author_id, $sort_order, $forum_limiter, $start, $ppg, $total)))	
		) {
			$search_data = '{TEMPLATE: no_search_results}';
		} else {
			$i = 0;
			$search_data = '';
			while ($r = db_rowobj($c)) {
				$body = trim_body(read_msg_body($r->foff, $r->length, $r->file_id));
				$poster_info = !empty($r->poster_id) ? '{TEMPLATE: registered_poster}' : '{TEMPLATE: unregistered_poster}';
				++$i;
				$search_data .= '{TEMPLATE: search_entry}';
			}
			un_register_fps();
			$search_data = '{TEMPLATE: search_results}';
			if ($FUD_OPT_2 & 32768) {
				$page_pager = tmpl_create_pager($start, $ppg, $total, '{ROOT}/s/'.urlencode($srch).'/'.$field.'/'.$search_logic.'/'.$sort_order.'/'.$forum_limiter.'/', '/'.urlencode($author).'/'._rsid);
			} else {
				$page_pager = tmpl_create_pager($start, $ppg, $total, '{ROOT}?t=search&amp;srch='.urlencode($srch).'&amp;field='.$field.'&amp;'._rsid.'&amp;search_logic='.$search_logic.'&amp;sort_order='.$sort_order.'&amp;forum_limiter='.$forum_limiter.'&amp;author='.urlencode($author));
			}
		}
	} else {
		$search_data = '';
	}

/*{POST_PAGE_PHP_CODE}*/
?>
{TEMPLATE: SEARCH_PAGE}