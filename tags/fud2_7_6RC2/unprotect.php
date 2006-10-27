<?php
/***************************************************************************
* copyright            : (C) 2001-2006 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: unprotect.php,v 1.12 2006/09/05 13:48:43 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it 
* under the terms of the GNU General Public License as published by the 
* Free Software Foundation; either version 2 of the License. 
***************************************************************************/

function fud_ini_get($opt)
{
	return (ini_get($opt) == '1' ? 1 : 0);
}

	if (fud_ini_get('safe_mode') && basename(__FILE__) != 'safe_unprotect.php') {
		$c = getcwd();
		copy($c . '/unprotect.php', $c . '/safe_unprotect.php');
		header('Location: safe_unprotect.php');
		exit;
	}
?>
<html>
<head>
	<title>FUDforum Unlock Files Script</title>
</head>
<body bgcolor="white">
<?php

function fud_unlock($dir)
{
	$dirs = array(realpath($dir));

	while (list(,$v) = each($dirs)) {
		if (!($files = glob($v.'{.h*,.p*,.n*,.m*,*}', GLOB_BRACE|GLOB_NOSORT))) {
			continue;
		}
		foreach ($files as $file) {
			if (is_dir($file) && !is_link($file)) {
				$perm = 0777;
				$dirs[] = $file;
			} else {
				$perm = 0666;
			}
			if (!chmod($file, $perm)) {
				echo '<b>Could not unlock path "'.$file.'"<br>';
			}
		}
	}
}

	require "GLOBALS.php";
	
	umask(0);
	fud_unlock(substr($DATA_DIR, 0, -1));
	if ($DATA_DIR != $WWW_ROOT_DISK) {
		fud_unlock(substr($WWW_ROOT_DISK, 0, -1));
	}
	@unlink($GLOBALS['ERROR_PATH'].'FILE_LOCK');
	if (basename(__FILE__) == 'safe_unprotect.php') {
		@unlink('safe_unprotect.php');
	}
?>
<div align="center">
Your forum is now unlocked.
</div>
</body>
</html>