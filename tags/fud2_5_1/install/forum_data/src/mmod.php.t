<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: mmod.php.t,v 1.16 2003/06/02 17:19:47 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

/*{PRE_HTML_PHP}*/
/*{POST_HTML_PHP}*/
	
	if (isset($_GET['del'])) {
		$del = (int) $_GET['del'];
	} else if (isset($_POST['del'])) {
		$del = (int) $_POST['del'];
	} else {
		$del = 0;
	}
	if (isset($_GET['th'])) {
		$th = (int) $_GET['th'];
	} else if (isset($_POST['th'])) {
		$th = (int) $_POST['th'];
	} else {
		$th = 0;
	}

	if (isset($_POST['NO'])) {
		check_return($usr->returnto);
	}

	if ($del) {
		if (!($data = db_saq('SELECT 
			t.forum_id, m.thread_id, m.id, m.subject, t.root_msg_id, m.reply_to, t.replies, mm.id,
			(CASE WHEN g2.id IS NOT NULL THEN g2.p_DEL ELSE g1.p_DEL END) AS p_del,
			(CASE WHEN g2.id IS NOT NULL THEN g2.p_LOCK ELSE g1.p_LOCK END) AS p_lock
			FROM {SQL_TABLE_PREFIX}msg m 
			INNER JOIN {SQL_TABLE_PREFIX}thread t ON t.id=m.thread_id 
			LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=t.forum_id AND mm.user_id='._uid.'
			INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id='.(_uid ? '2147483647': '0').' AND g1.resource_id=t.forum_id
			LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=t.forum_id
			WHERE m.id='.$del))) {
			check_return($usr->returnto);
		}
	
		if ($del && $data[8] != 'Y' && $usr->is_mod != 'A' && !$data[7]) {
			check_return($usr->returnto);
		}

		if (empty($_POST['confirm'])) {
			if ($data[2] != $data[4]) {
				$delete_msg = '{TEMPLATE: single_msg_delete}';
			} else {
				$delete_msg = '{TEMPLATE: thread_delete}';
			}

			?> {TEMPLATE: delete_confirm_pg} <?php 
			exit;
		}
		
		if (isset($_POST['YES'])) {
			if ($data[2] == $data[4]) {
				logaction(_uid, 'DELTHR', 0, '"'.addslashes($data[3]).'" w/'.$data[6].' replies');

				fud_msg_edit::delete(TRUE, $data[2], 1);

				if (strpos($usr->returnto, 'selmsg') === FALSE) {
					if ($GLOBALS['USE_PATH_INFO'] == 'N') {
						header('Location: {ROOT}?t='.t_thread_view.'&'._rsidl.'&frm_id='.$data[0]);
					} else {
						header('Location: {ROOT}/f/'.$data[0].'/'._rsidl);
					}
					exit;
				} else {
					check_return($usr->returnto);
				}
			} else {
				logaction(_uid, 'DELMSG', 0, addslashes($data[3]));
				fud_msg_edit::delete(TRUE, $data[2], 0);
			}
		}

		if (strpos($usr->returnto, 'selmsg') !== FALSE) {
			check_return($usr->returnto);		
		}

		if (d_thread_view == 'tree') {
			if (!$data[5]) {
				if ($GLOBALS['USE_PATH_INFO'] == 'N') {
					header('Location: {ROOT}?t=tree&'._rsidl.'&th='.$data[1]);
				} else {
					header('Location: {ROOT}/mv/tree/'.$data[1].'/'._rsidl);
				}
			} else {
				if ($GLOBALS['USE_PATH_INFO'] == 'N') {
					header('Location: {ROOT}?t=tree&'._rsidl.'&th='.$data[1].'&mid='.$data[5]);
				} else {
					header('Location: {ROOT}/mv/tree/'.$data[1].'/'.$data[5].'/'._rsidl);
				}
			}
		} else {
			if ($GLOBALS['USE_PATH_INFO'] == 'N') {
				header('Location: {ROOT}?t=msg&th='.$data[1].'&'._rsidl.'&start=end');
			} else {
				header('Location: {ROOT}/mv/msg/'.$data[1].'/end/'._rsidl);
			}
		}
		exit;
	} else if ($th) {
		if (!($data = db_saq('SELECT mm.id, (CASE WHEN g2.id IS NOT NULL THEN g2.p_LOCK ELSE g1.p_LOCK END) AS p_lock
			FROM {SQL_TABLE_PREFIX}thread t
			LEFT JOIN {SQL_TABLE_PREFIX}mod mm ON mm.forum_id=t.forum_id AND mm.user_id='._uid.'
			INNER JOIN {SQL_TABLE_PREFIX}group_cache g1 ON g1.user_id='.(_uid ? '2147483647': '0').' AND g1.resource_id=t.forum_id
			LEFT JOIN {SQL_TABLE_PREFIX}group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=t.forum_id
			WHERE t.id='.$th))) {
			check_return($usr->returnto);
		}
		if (!$data[0] && $data[1] != 'Y' && $usr->is_mod != 'A') {
			check_return($usr->returnto);
		}

		if (isset($_GET['lock'])) {
			logaction(_uid, 'THRLOCK', $th);
			th_lock($th, 'Y');
		} else {
			logaction(_uid, 'THRUNLOCK', $th);
			th_lock($th, 'N');	
		}
	}
	check_return($usr->returnto);
?>