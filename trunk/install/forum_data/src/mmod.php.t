<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: mmod.php.t,v 1.11 2003/04/11 09:52:56 hackie Exp $
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
		if (!($data = db_saq('SELECT t.forum_id, m.thread_id, m.id, m.subject, t.root_msg_id, m.reply_to, t.replies FROM {SQL_TABLE_PREFIX}msg m INNER JOIN {SQL_TABLE_PREFIX}thread t ON t.id=m.thread_id WHERE m.id='.$del))) {
			check_return($usr->returnto);
		}
	} else if ($th) {
		/* confirm that the thread is indeed a valid thread */
		if (!($data = db_saq('SELECT forum_id,id FROM {SQL_TABLE_PREFIX}thread WHERE id='.$th))) {
			check_return($usr->returnto);
		}
	} else {
		check_return($usr->returnto);
	}

	if (($usr->is_mod == 'A' || is_moderator($data[0], _uid))) {
		$MOD = 1;
	} else {
		if (isset($del) && !is_perms(_uid, $data[0], 'DEL')) {
			check_return($usr->returnto);
		} else if (isset($_GET['lock']) && !is_perms(_uid, $data[0], 'LOCK')) {
			check_return($usr->returnto);
		} else {
			check_return($usr->returnto);
		}
	}
	
	if (!empty($del)) {
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

				header('Location: {ROOT}?t='.t_thread_view.'&'._rsidl.'&frm_id='.$data[0]);
				exit;
			} else {
				logaction(_uid, 'DELMSG', 0, addslashes($data[3]));
				fud_msg_edit::delete(TRUE, $data[2], 0);
			}
		}
		
		if (d_thread_view == 'tree') {
			if (!$data[5]) {
				header('Location: {ROOT}?t=tree&'._rsidl.'&th='.$data[1]);
			} else {
				header('Location: {ROOT}?t=tree&'._rsidl.'&th='.$data[1].'&mid='.$data[5]);
			}
		} else {
			$count = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;
			$pos = q_singleval('SELECT replies + 1 FROM {SQL_TABLE_PREFIX}thread WHERE id='.$data[1]);
			$start = (ceil(($pos/$count))-1)*$count;
			header('Location: {ROOT}?t=msg&th='.$data[1].'&'._rsidl.'&start='.$start);
		}
		exit;
	} else {
		if (isset($_GET['lock'])) {
			logaction(_uid, 'THRLOCK', $data[1]);
			th_lock($data[1], 'Y');
		} else {
			logaction(_uid, 'THRUNLOCK', $data[1]);
			th_lock($data[1], 'N');	
		}
	}
	check_return($usr->returnto);
?>