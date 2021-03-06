<?php
/**
* copyright            : (C) 2001-2011 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

/*{PRE_HTML_PHP}*/
/*{POST_HTML_PHP}*/

function th_frm_last_post_id($id, $th)
{
	return (int) q_singleval(q_limit('SELECT t.last_post_id FROM {SQL_TABLE_PREFIX}thread t INNER JOIN {SQL_TABLE_PREFIX}msg m ON t.root_msg_id=m.id WHERE t.forum_id='. $id .' AND t.id!='. $th .' AND t.moved_to=0 AND m.apr=1 ORDER BY t.last_post_date DESC', 1));
}

	$th = isset($_GET['th']) ? (int)$_GET['th'] : (isset($_POST['th']) ? (int)$_POST['th'] : 0);
	if (!$th) {
		invl_inp_err();
	}

	/* permission check */
	if (!$is_a) {
		$perms = db_saq('SELECT mm.id, '. (_uid ? ' COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS gco ' : ' g1.group_cache_opt AS gco '). '
				FROM {SQL_TABLE_PREFIX}thread t
				LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.user_id='._uid.' AND mm.forum_id=t.forum_id
				'.(_uid ? 'INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=t.forum_id LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=t.forum_id' : 'INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id=0 AND g1.resource_id=t.forum_id').'
				WHERE t.id='. $th);
		if (!$perms || (!$perms[0] && !($perms[1] & 2048))) {
			std_error('access');
		}
	}

	$forum = isset($_POST['forum']) ? (int)$_POST['forum'] : 0;

	if ($forum && !empty($_POST['new_title']) && is_string($_POST['new_title']) && !empty($_POST['sel_th']) && is_array($_POST['sel_th'])) {
		/* We need to make sure that the user has access to destination forum. */
		if (!$is_a && !q_singleval('SELECT f.id FROM {SQL_TABLE_PREFIX}forum f LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.user_id='. _uid .' AND mm.forum_id=f.id '. (_uid ? 'INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=f.id LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id' : 'INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id=0 AND g1.resource_id=f.id') .' WHERE f.id='. $forum .' AND (mm.id IS NOT NULL OR '. q_bitand(_uid ? 'COALESCE(g2.group_cache_opt, g1.group_cache_opt)' : '(g1.group_cache_opt)', 4) .' > 0)')) {
			std_error('access');
		}

		$m = array();
		foreach ($_POST['sel_th'] as $v) {
			if ((int)$v) {
				$m[] = (int) $v;
			}
		}

		/* sanity check */
		if (!$m) {
			if ($FUD_OPT_2 & 32768) {
				header('Location: {FULL_ROOT}{ROOT}/t/'. $th .'/'. _rsidl);
			} else {
				header('Location: {FULL_ROOT}{ROOT}?t='. d_thread_view .'&th='. $th .'&'. _rsidl);
			}
			exit;
		}

		$mc = count($m);
		if (isset($_POST['btn_selected'])) {
			sort($m);
			$mids = implode(',', $m);
			$start = $m[0];
			$end = $m[($mc - 1)];
		} else {
			$a = db_all('SELECT id FROM {SQL_TABLE_PREFIX}msg WHERE thread_id='. $th .' AND id NOT IN('. implode(',', $m) .') AND apr=1 ORDER BY post_stamp ASC');
			/* sanity check */
			if (!$a) {
				if ($FUD_OPT_2 & 32768) {
					header('Location: {FULL_ROOT}{ROOT}/t/'. $th .'/'. _rsidl);
				} else {
					header('Location: {FULL_ROOT}{ROOT}?t='. d_thread_view .'&th='. $th .'&'. _rsidl);
				}
				exit;
			}
			$mids = implode(',', $a);
			$mc = count($a);
			$start = $a[0];
			$end = $a[($mc - 1)];
		}

		/* Fetch all relevant information. */
		$data = db_sab('SELECT
				t.id, t.forum_id, t.replies, t.root_msg_id, t.last_post_id, t.last_post_date, t.tdescr,
				m1.post_stamp AS new_th_lps, m1.id AS new_th_lpi,
				m2.post_stamp AS old_fm_lpd,
				f1.last_post_id AS src_lpi,
				f2.last_post_id AS dst_lpi
				FROM {SQL_TABLE_PREFIX}thread t
				INNER JOIN {SQL_TABLE_PREFIX}forum f1 ON t.forum_id=f1.id
				INNER JOIN {SQL_TABLE_PREFIX}forum f2 ON f2.id='. $forum .'
				LEFT JOIN {SQL_TABLE_PREFIX}msg m1 ON m1.id='. $end .'
				LEFT JOIN {SQL_TABLE_PREFIX}msg m2 ON m2.id=f2.last_post_id
		WHERE t.id='. $th);

		if (!$data) {
			invl_inp_err();
		}

		/* Sanity check. */
		if (!$data->replies) {
			if ($FUD_OPT_2 & 32768) {
				header('Location: {FULL_ROOT}{ROOT}/t/'. $th .'/'. _rsidl);
			} else {
				header('Location: {FULL_ROOT}{ROOT}?t='. d_thread_view .'&th='. $th .'&'. _rsidl);
			}
			exit;
		}

		apply_custom_replace($_POST['new_title']);

		if ($mc != ($data->replies + 1)) { /* Check that we need to move the entire thread. */
			if ($forum != $data->forum_id) {
				$lk_pfx = '{SQL_TABLE_PREFIX}tv_'. $forum .' WRITE,{SQL_TABLE_PREFIX}thread t WRITE,{SQL_TABLE_PREFIX}msg m WRITE,';
			} else {
				$lk_pfx = '';
			}
			db_lock($lk_pfx .'{SQL_TABLE_PREFIX}tv_'. $data->forum_id .' WRITE, {SQL_TABLE_PREFIX}thread WRITE, {SQL_TABLE_PREFIX}forum WRITE, {SQL_TABLE_PREFIX}msg WRITE, {SQL_TABLE_PREFIX}poll WRITE');

			$new_th = th_add($start, $forum, (int)$data->new_th_lps, 0, 0, ($mc - 1), 0, (int)$data->new_th_lpi);

			/* Deal with the new thread. */
			q('UPDATE {SQL_TABLE_PREFIX}msg SET thread_id='. $new_th .' WHERE id IN ('. $mids .')');
			q('UPDATE {SQL_TABLE_PREFIX}msg SET reply_to='. $start .' WHERE thread_id='. $new_th .' AND reply_to NOT IN ('. $mids .')');
			q('UPDATE {SQL_TABLE_PREFIX}msg SET reply_to=0, subject='. _esc(htmlspecialchars($_POST['new_title'])) .' WHERE id='. $start);

			/* Deal with the old thread. */
			list($lpi, $lpd) = db_saq(q_limit('SELECT id, post_stamp FROM {SQL_TABLE_PREFIX}msg WHERE thread_id='. $data->id .' AND apr=1 ORDER BY post_stamp DESC', 1));
			$old_root_msg_id = q_singleval(q_limit('SELECT id FROM {SQL_TABLE_PREFIX}msg WHERE thread_id='. $data->id .' AND apr=1 ORDER BY post_stamp ASC', 1));
			q('UPDATE {SQL_TABLE_PREFIX}msg SET reply_to='. $old_root_msg_id .' WHERE thread_id='. $data->id .' AND reply_to IN('. $mids .')');
			q('UPDATE {SQL_TABLE_PREFIX}msg SET reply_to=0 WHERE id='. $old_root_msg_id);
			q('UPDATE {SQL_TABLE_PREFIX}thread SET root_msg_id='. $old_root_msg_id .', replies=replies-'. $mc .', last_post_date='. $lpd .', last_post_id='. $lpi .' WHERE id='. $data->id);

			if ($forum != $data->forum_id) {
				$p = db_all('SELECT poll_id FROM {SQL_TABLE_PREFIX}msg WHERE thread_id='. $new_th .' AND apr=1 AND poll_id>0');
				if ($p) {
					q('UPDATE {SQL_TABLE_PREFIX}poll SET forum_id='. $data->forum_id .' WHERE id IN('. implode(',', $p) .')');
				}

				/* deal with the source forum */
				if ($data->src_lpi != $data->last_post_id || $data->last_post_date <= $lpd) {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count-'. $mc .' WHERE id='. $data->forum_id);
				} else {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count-'. $mc .', last_post_id='. th_frm_last_post_id($data->forum_id, $data->id) .' WHERE id='. $data->forum_id);
				}

				/* Deal with destination forum. */
				if ($data->old_fm_lpd > $data->new_th_lps) {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count+'. $mc .', thread_count=thread_count+1 WHERE id='. $forum);
				} else {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET post_count=post_count+'. $mc .', thread_count=thread_count+1, last_post_id='. $data->new_th_lpi .' WHERE id='. $forum);
				}

				rebuild_forum_view_ttl($forum);
			} else {
				if ($data->src_lpi == $data->last_post_id && $data->last_post_date >= $lpd) {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET thread_count=thread_count+1 WHERE id='. $data->forum_id);
				} else {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET thread_count=thread_count+1, last_post_id='. $data->new_th_lpi .' WHERE id='. $data->forum_id);
				}
			}
			rebuild_forum_view_ttl($data->forum_id);
			db_unlock();
			index_text(q_singleval('SELECT subject FROM {SQL_TABLE_PREFIX}msg WHERE id='. $start), '', $start);
			logaction(_uid, 'THRSPLIT', $new_th);
			$th_id = $new_th;
		} else { /* Moving entire thread. */
			q('UPDATE {SQL_TABLE_PREFIX}msg SET subject='. _esc(htmlspecialchars($_POST['new_title'])) .' WHERE id='. $data->root_msg_id);
			if ($forum != $data->forum_id) {
				th_move($data->id, $forum, $data->root_msg_id, $thr->forum_id, $data->last_post_date, $data->last_post_id, $data->tdescr);

				if ($data->src_lpi == $data->last_post_id) {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET last_post_id='. th_frm_last_post_id($data->forum_id, $data->id) .' WHERE id='. $data->forum_id);
				}
				if ($data->old_fm_lpd < $data->last_post_date) {
					q('UPDATE {SQL_TABLE_PREFIX}forum SET last_post_id='. $data->last_post_id .' WHERE id='. $forum);
				}

				logaction(_uid, 'THRMOVE', $th);
			}
			$th_id = $data->id;
		}
		if ($FUD_OPT_2 & 32768) {
			header('Location: {FULL_ROOT}{ROOT}/t/'. $th_id .'/'. _rsidl);
		} else {
			header('Location: {FULL_ROOT}{ROOT}?t='. d_thread_view .'&th='. $th_id .'&'. _rsidl);
		}
		exit;
	}
	/* Fetch a list of accesible forums. */
	$c = uq('SELECT f.id, f.name
			FROM {SQL_TABLE_PREFIX}forum f
			INNER JOIN {SQL_TABLE_PREFIX}fc_view v ON v.f=f.id
			INNER JOIN {SQL_TABLE_PREFIX}cat c ON c.id=f.cat_id
			LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .'
			INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.resource_id=f.id AND g1.user_id='. (_uid ? '2147483647' : '0') .'
			'. (_uid ? ' LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.resource_id=f.id AND g2.user_id='. _uid : '') .'
			'. ($is_a ? '' : ' WHERE mm.id IS NOT NULL OR (
			'. q_bitand(_uid ? 'COALESCE(g2.group_cache_opt, g1.group_cache_opt)' : 'g1.group_cache_opt', 4) .' > 0)') .'
			ORDER BY v.id');
	$vl = $kl = '';
	while ($r = db_rowarr($c)) {
		$vl .= $r[0] ."\n";
		$kl .= $r[1] ."\n";
	}
	unset($c);

	if (!$forum) {
		$forum = q_singleval('SELECT forum_id FROM {SQL_TABLE_PREFIX}thread WHERE id='. $th);
	}

	$forum_sel = tmpl_draw_select_opt(rtrim($vl), rtrim($kl), $forum);
	$anon_alias = htmlspecialchars($ANON_NICK);
	$msg_entry = '';

	$c = q('SELECT m.id, m.foff, m.length, m.file_id, m.subject, m.post_stamp, u.alias FROM {SQL_TABLE_PREFIX}msg m LEFT JOIN {SQL_TABLE_PREFIX}users u ON m.poster_id=u.id WHERE m.thread_id='. $th .' AND m.apr=1 ORDER BY m.post_stamp ASC');
	while ($r = db_rowobj($c)) {
		$msg_entry .= '{TEMPLATE: msg_entry}';
	}
	unset($c);

/*{POST_PAGE_PHP_CODE}*/
?>
{TEMPLATE: SPLIT_TH_PAGE}
