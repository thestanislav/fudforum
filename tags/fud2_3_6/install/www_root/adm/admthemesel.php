<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: admthemesel.php,v 1.10 2002/10/26 23:32:12 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	@set_time_limit(6000);
	define('admin_form', 1);
	
	include_once "GLOBALS.php";
	fud_use('adm.inc', true);
	list($ses, $usr) = initadm();
 
	if ( $HTTP_POST_VARS['tname'] ) {
		header("Location: ".$ret.".php?tname=$tname&tlang=$tlang&rand=".get_random_value()."&"._rsidl);
		exit();
	}
	
	include('admpanel.php');
	
	list($def_thm, $def_tmpl) = db_singlearr(q("SELECT name, lang FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."themes WHERE t_default='Y'"));
?>
<h3>Template Set Selection</h3>
<form method="post" action="admthemesel.php">
<input type="hidden" name="ret" value="<?php echo $ret; ?>">
<table border=0 cellspacing=1 cellpadding=3>
<tr bgcolor="#bff8ff">
<?php
	echo _hs;
	
	$dp = opendir($GLOBALS['DATA_DIR'].'/thm');
	readdir($dp); readdir($dp);
	echo '<td>Template Set:</td><td><select name="tname">';
	while ( $de = readdir($dp) ) {
		if ( $de == 'CVS' || !@is_dir($GLOBALS['DATA_DIR'].'/thm/'.$de) ) continue;
		echo '<option value="'.$de.'"'.($de==$def_thm ? ' selected' : '').'>'.$de.'</option>';
	}
	echo '</select></td>';
?>
</tr>

<tr bgcolor="#bff8ff">
<?php	
	
	$dp = opendir($GLOBALS['DATA_DIR'].'/thm/default/i18n');
	readdir($dp); readdir($dp);
	echo '<td>Language:</td><td><select name="tlang">';
	while ( $de = readdir($dp) ) {
		if ( $de == 'CVS' || !@is_dir($GLOBALS['DATA_DIR'].'/thm/default/i18n/'.$de) ) continue;
		echo '<option value="'.$de.'"'.($de==$def_tmpl ? ' selected' : '').'>'.$de.'</option>';
	}
	echo '</select></td>';
?>
</tr>
<?php
	echo '<tr bgcolor="#bff8ff" align=right><td colspan=2><input type="submit" name="btn_submit" value="Edit"></td></td>';
?>
</tr>
</table>
</form>
<?php	
	readfile('admclose.html');
?>