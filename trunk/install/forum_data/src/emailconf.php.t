<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: emailconf.php.t,v 1.1.1.1 2002/06/17 23:00:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	include_once "GLOBALS.php";
	
	{PRE_HTML_PHP}
	$usr = fud_user_to_reg($usr);
	{POST_HTML_PHP}

	if ( !empty($conf_key) ) {
		$r = Q("SELECT * FROM {SQL_TABLE_PREFIX}users WHERE conf_key='".$conf_key."'");
		if ( !IS_RESULT($r) ) {
			error_dialog('{TEMPLATE: emailconf_err_invkey_title}', '{TEMPLATE: emailconf_err_invkey_msg}', NULL, 'FATAL');
			exit();
		}
		
		$conf_usr = DB_SINGLEOBJ($r);
		$usr = new fud_user_reg;
		$ses = new fud_session;
		$ses->cookie_get_session();
		
		$ses->save_session($conf_usr->id);		
		$usr->get_user_by_id($conf_usr->id);

		if ( $usr->conf_key == $conf_key ) $usr->email_confirm();
		
		check_return();
	}
	else {
		error_dialog('{TEMPLATE: emailconf_err_invkey_title}', '{TEMPLATE: emailconf_err_invkey_msg}', NULL, 'FATAL');
		exit();
	}
?>