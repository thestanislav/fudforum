<?php
/**
* copyright            : (C) 2001-2006 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: admphpinfo.php,v 1.3 2006/09/19 14:37:56 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	require('./GLOBALS.php');
	fud_use('adm.inc', true);
	require($WWW_ROOT_DISK . 'adm/admpanel.php');
	phpinfo();
	require($WWW_ROOT_DISK . 'adm/admclose.html');
?>