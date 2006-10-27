<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: root_index.php.t,v 1.25 2003/06/05 23:07:56 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	require('./GLOBALS.php');

	if ($PHP_COMPRESSION_ENABLE == 'Y') {
		ob_start(array('ob_gzhandler', (int)$PHP_COMPRESSION_LEVEL));
	}
	
/*{PRE_HTML_PHP}*/
/*{POST_HTML_PHP}*/

	fud_use('err.inc');

	/* before we go on, we need to do some very basic activation checks */
	if ($FORUM_ENABLED != 'Y') {
		fud_use('cfg.inc', true);
		fud_use('errmsg.inc');
		exit(cfg_dec($DISABLED_REASON) . __fud_ecore_adm_login_msg);
	}
	if (!$FORUM_TITLE && @file_exists($WWW_ROOT_DISK.'install.php')) {
		fud_use('errmsg.inc');
	        exit(__fud_e_install_script_present_error);
	}

	if (isset($_GET['t'])) {
		$t = $_GET['t'];
	} else if (isset($_POST['t'])) {
		$t = $_POST['t'];
	}
	if (!isset($t) || preg_match('/[^A-Za-z0-9_]/', $t) || !@file_exists($WWW_ROOT_DISK . fud_theme . $t . '.php')) {
		$t = 'index';
	} else if ($t == 'rview') {
		if (isset($_GET['th']) || isset($_GET['goto'])) {
			$t = $_GET['t'] = d_thread_view;
		} else if (isset($_GET['frm_id'])) {
			$t = $_GET['t'] = t_thread_view;
		} else {
			$t = $_GET['t'] = 'index';
		}
	}
	if ($GLOBALS['BUST_A_PUNK'] == 'Y' && isset($_COOKIE[$GLOBALS['COOKIE_NAME'].'1']) && $t != 'error') {
		setcookie($GLOBALS['COOKIE_NAME'].'1', 'd34db33fd34db33fd34db33fd34db33f', __request_timestamp__+63072000, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
		fud_use('errmsg.inc');
		exit(__fud_banned__);
	}

	define('__index_page_start__', true);
	require($WWW_ROOT_DISK . fud_theme . $t . '.php');
?>