<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: admlock.php,v 1.2 2002/06/18 14:20:24 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	define('admin_form', 1);
	include_once "GLOBALS.php";

	define('S', ($HTTP_COOKIE_VARS['COOKIE_NAME']?$HTTP_COOKIE_VARS['COOKIE_NAME']:$HTTP_GET_VARS['S']));
	define('_rsid', 'S='.S);
	define('_hs', '<input type="hidden" name="S" value="'.S.'">');

function chmoddir($dirn, $dirp, $filep, $rec=FALSE)
{
	$oldcwd = getcwd();
	if ( !@chdir($dirn) ) {
		echo "unable to chdir to $dirn in ".getcwd()."<br>";
		return;
	}
	
	@chmod('.', $dirp);
	$dp = opendir('.');
	readdir($dp); readdir($dp);
	while ( $de = readdir($dp) ) 
	{
		if ( !$de ) break;
		if ( is_dir($de) ) {
			if ( !@chmod($de, $dirp) ) echo "ERROR: ".getcwd()."/$de -> d (".sprintf("%o", $dirp).")<br>";
			if ( $rec==TRUE ) chmoddir($de, $dirp, $filep, $rec);
		}
		else {
			if ( !@chmod($de, $filep) ) echo "ERROR: ".getcwd()."/$de -> f (".sprintf("%o", $dirp).")<br>";
		}
	}
	chdir($oldcwd);
}
	
	if ( ($HTTP_POST_VARS['btn_lock'] || $HTTP_POST_VARS['btn_unlock']) && $HTTP_POST_VARS['usr_passwd'] && $HTTP_POST_VARS['usr_login'] ) {
		$md5pass = md5($HTTP_POST_VARS['usr_passwd']);
		
		mysql_connect($GLOBALS['MYSQL_SERVER'], $GLOBALS['MYSQL_LOGIN'], $GLOBALS['MYSQL_PASSWORD']);
		mysql_select_db($GLOBALS['MYSQL_DB']);
		$r = mysql_query("SELECT * FROM ".$GLOBALS['MYSQL_TBL_PREFIX']."users WHERE login='".$HTTP_POST_VARS['usr_login']."' AND passwd='$md5pass' AND is_mod='A'");
		
		if ( !mysql_num_rows($r) ) {
			$err = 1;
		}
		else {
			if ( $HTTP_POST_VARS['btn_unlock'] ) {
				$dirperms = 0777;
				$fileperms = 0666;
				
				unlink($GLOBALS['ERROR_PATH'].'FILE_LOCK');
			}
			else {
				$dirperms = 0700;
				$fileperms = 0600;
				
				touch($GLOBALS['ERROR_PATH'].'FILE_LOCK');
			}

			chmoddir($GLOBALS['WWW_ROOT_DISK'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['INCLUDE'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['DATA_DIR'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['ERROR_PATH'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['MSG_STORE_DIR'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['FILE_STORE'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['TMP'], $dirperms, $fileperms, TRUE);
			chmoddir($GLOBALS['FORUM_SETTINGS_PATH'], $dirperms, $fileperms, TRUE);
		}
		mysql_free_result($r);
	}

	clearstatcache();
	$status = file_exists($GLOBALS['ERROR_PATH'].'FILE_LOCK') ? 'LOCKED' : 'UNLOCKED';

	include('admpanel.php'); 	
?>
<div align="center" style="font-size: xx-large; color: #ff0000;">
	The forum's files appear to be: <b><?php echo $status; ?></b>.<br>
	<font size="-1">If this test claims that the forum is unlocked, but you still cannot modify your files click on the "Unlock Files" button.</font><br>
	For security reasons remember to lock your forum's files after you are done editing them.
</div>
<form method="post">
<table border=0 cellspacing=0 cellpadding=3>
<?php
	if( $err ) 
		echo '<tr><td colspan=2><font color="#ff0000">Invalid Login Information</font></td></tr>';
?>
<tr><td>Login:</td><td><input type="text" name="usr_login" value="<?php echo stripslashes(htmlspecialchars($usr_login)); ?>"></td></tr>
<tr><td>Password:</td><td><input type="password" name="usr_passwd"></td></tr>
<tr><td colspan=2 algin=middle>
	<input type="submit" name="btn_lock" value="Lock Files">
	<input type="submit" name="btn_unlock" value="Unlock Files">
</td></tr>
</table>
<?php echo _hs; ?>
</form>
<?php require('admclose.html'); ?>