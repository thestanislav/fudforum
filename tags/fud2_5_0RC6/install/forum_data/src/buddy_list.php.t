<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: buddy_list.php.t,v 1.20 2003/05/02 15:21:57 hackie Exp $
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

	if (!_uid) {
		std_error('login');
	}

	if (isset($_POST['add_login'])) {
		if (!($buddy_id = q_singleval('SELECT id FROM {SQL_TABLE_PREFIX}users WHERE alias=\''.addslashes(htmlspecialchars($_POST['add_login'])).'\''))) {
			error_dialog('{TEMPLATE: buddy_list_err_nouser_title}', '{TEMPLATE: buddy_list_err_nouser}');		
		}
		if ($buddy_id == _uid) {
			error_dialog('{TEMPLATE: err_info}', '{TEMPLATE: buddy_list_err_cantadd}');
		}

		if (!empty($usr->buddy_list)) {
			$usr->buddy_list = @unserialize($usr->buddy_list);
		}

		if (!isset($usr->buddy_list[$buddy_id])) {
			$usr->buddy_list = buddy_add(_uid, $buddy_id);
		} else {
			error_dialog('{TEMPLATE: err_info}', '{TEMPLATE: buddy_list_err_dup}');
		}
	}

	/* incomming from message display page (add buddy link) */
	if (isset($_GET['add']) && ($_GET['add'] = (int)$_GET['add'])) {
		if (!empty($usr->buddy_list)) {
			$usr->buddy_list = @unserialize($usr->buddy_list);
		}

		if (($buddy_id = q_singleval('SELECT id FROM {SQL_TABLE_PREFIX}users WHERE id='.$_GET['add'])) && !isset($usr->buddy_list[$buddy_id])) {
			buddy_add(_uid, $buddy_id);
		}
		check_return($usr->returnto);
	}

	if (isset($_GET['del']) && ($_GET['del'] = (int)$_GET['del'])) {
		buddy_delete(_uid, $_GET['del']);	
		/* needed for external links to this form */
		if (isset($_GET['redr'])) {
			check_return($usr->returnto);
		}
	}

	ses_update_status($usr->sid, '{TEMPLATE: buddy_list_update}');

	$buddy_member_search = ($MEMBER_SEARCH_ENABLED == 'Y') ? '{TEMPLATE: buddy_member_search}' : '';

/*{POST_HTML_PHP}*/

	$c = uq('SELECT b.bud_id, u.id, u.alias, u.join_date, u.bday, u.invisible_mode, u.posted_msg_count, u.home_page, u.last_visit AS time_sec
		FROM {SQL_TABLE_PREFIX}buddy b INNER JOIN {SQL_TABLE_PREFIX}users u ON b.bud_id=u.id WHERE b.user_id='._uid);
	
	$buddies = '';
	/* Result index 
	 * 0 - bud_id	1 - user_id	2 - login	3 - join_date	4 - bday	5 - invisible	6 - msg_count	
	 * 7 - home_page	8 - last_visit
	 */

	if (($r = @db_rowarr($c))) {
		do {
			$homepage_link = $r[7] ? '{TEMPLATE: homepage_link}' : '';
			if ($r[0] == 'Y' && $usr->is_mod != 'A') {
				$online_status = '';
			} else {
				$online_status = (($r[8] + $LOGEDIN_TIMEOUT * 60) > __request_timestamp__) ? '{TEMPLATE: online_indicator}' : '{TEMPLATE: offline_indicator}';
			}

			if ($r[5] && substr($r[4], 4) == date('md')) {
				$age = date('Y')  - substr($r[4], 0, 4);
				$bday_indicator = '{TEMPLATE: bday_indicator}';	
			} else {
				$bday_indicator = '';
			}

			$contact_link = $PM_ENABLED == 'Y' ? '{TEMPLATE: pm_link}' : '{TEMPLATE: email_link}';

			$buddies .= '{TEMPLATE: buddy}';
		} while (($r = db_rowarr($c)));
		$buddies = '{TEMPLATE: buddy_list}';
	}
	qf($res);
	
/*{POST_PAGE_PHP_CODE}*/
?>
{TEMPLATE: BUDDYLIST_PAGE}