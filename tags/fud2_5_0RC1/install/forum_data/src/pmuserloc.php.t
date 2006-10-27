<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: pmuserloc.php.t,v 1.10 2003/05/02 15:21:58 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	define('plain_form', 1);

/*{PRE_HTML_PHP}*/
	
	if (empty($_GET['js_redr'])) {
		exit;
	}

	if ($MEMBER_SEARCH_ENABLED != 'Y') {
		std_error('disabled');
	}

/*{POST_HTML_PHP}*/

	$usr_login = isset($_GET['usr_login']) ? trim($_GET['usr_login']) : '';
	$usr_email = isset($_GET['usr_email']) ? trim($_GET['usr_email']) : '';
	$overwrite = isset($_GET['overwrite']) ? (int)$_GET['overwrite'] : 0;
	$js_redr = $_GET['js_redr'];

	if ($usr_login || $usr_email) {
		if ($usr_login) {
			$qry = "WHERE alias LIKE '".addslashes(str_replace('\\', '\\\\', $usr_login))."%'";
		} else {
			$qry = "WHERE email LIKE '".addslashes(str_replace('\\', '\\\\', $usr_email))."%'";
		}
		$find_user_data = '';
		$c = uq('SELECT alias FROM {SQL_TABLE_PREFIX}users '.$qry.' AND id>1');
		$i = 0;
		while ($r = db_rowarr($c)) {
			if ($overwrite) {
				$retlink = 'javascript: window.opener.document.'.$js_redr.'.value=\''.addcslashes($r[0], "'\\").'\'; window.close();';
			} else {
				$retlink = 'javascript: 
						if (!window.opener.document.'.$js_redr.'.value) {
							window.opener.document.'.$js_redr.'.value = \''.addcslashes($r[0], "'\\").'\';
						} else {
							window.opener.document.'.$js_redr.'.value = window.opener.document.'.$js_redr.'.value + \'; \' + \''.addcslashes($r[0], "'\\").'; \';
						}
					window.close();';
			}
			$find_user_data .= '{TEMPLATE: user_result_entry}';
			$i++;
		}
		qf($c);
		if (!$find_user_data) {
			$find_user_data = '{TEMPLATE: no_result_entry}';
		}
	} else {
		$find_user_data = '';
	}

/*{POST_PAGE_PHP_CODE}*/
?>
{TEMPLATE: PMUSERLOC_PAGE}