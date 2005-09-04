<?php
/**
* copyright            : (C) 2001-2004 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: msg_icon_cache.inc,v 1.6 2004/12/08 16:23:51 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
**/

function rebuild_icon_cache()
{
	$iconl = array();
	foreach ((array)glob($GLOBALS['WWW_ROOT_DISK'] . 'images/message_icons/{*.gif,*.jpg,*.png,*.jpeg}', GLOB_BRACE|GLOB_NOSORT) as $file) {
		$iconl[] = basename($file);
	}

	$fp = fopen($GLOBALS['TMP'].'icon_cache', "w");
	fwrite($fp, '<?php $ICON_L = '.var_export($iconl, 1).'; ?>');
	fclose($fp);

	rename($GLOBALS['TMP'].'icon_cache', $GLOBALS['FORUM_SETTINGS_PATH'].'icon_cache');
}
?>