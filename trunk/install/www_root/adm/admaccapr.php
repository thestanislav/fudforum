<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: admaccapr.php,v 1.10 2003/10/06 18:37:57 hackie Exp $
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
	fud_use('adm.inc', true);
	fud_use('users_adm.inc', true);

	if (isset($_GET['apr'])) {
		if (($r = db_sab("SELECT email, login FROM ".$DBHOST_TBL_PREFIX."users WHERE id=".(int)$_GET['apr']))) {
			fud_use('adm_acc.inc');
			fud_use('iemail.inc');
			q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET users_opt=users_opt & ~ 2097152 WHERE id='.(int)$_GET['apr']);
			send_email($NOTIFY_FROM, $r->email, $account_accepted_s, $account_accepted);
		}
	} else if (isset($_GET['rm'])) {
		if (($r = db_sab("SELECT email, login FROM ".$DBHOST_TBL_PREFIX."users WHERE id=".(int)$_GET['rm']))) {
			fud_use('adm_acc.inc');
			fud_use('iemail.inc');
			send_email($NOTIFY_FROM, $r->email, $account_rejected_s, $account_rejected);
			usr_delete((int)$_GET['rm']);
		}
	}

function print_if_avail($descr, $value, $no_html=1)
{
	if (!empty($value)) {
		if ($no_html) {
			$value = htmlspecialchars($value);
		}
		return $descr.': '.$value.'<br>';
	} else {
		return;
	}
}

	require($WWW_ROOT_DISK . 'adm/admpanel.php');
?>
<div style="font-size: xx-large; font-weight: bold;">Account Approval</div>
<table cellspacing=0 cellpadding=5 border=0><tr bgcolor="#bff8ff"><td><b>Account Information</b></td><td align="center"><b>Action</b></td></tr>
<?php
	$c = uq('SELECT * FROM '.$DBHOST_TBL_PREFIX.'users WHERE users_opt>=2097152 AND (users_opt & 2097152) > 0');
	while ($obj = db_rowobj($c)) {
		echo '<tr><td style="font-size: smaller; border-bottom: 3px double black">'.
		print_if_avail('Login', $obj->login) .
		print_if_avail('E-mail', $obj->email) .
		print_if_avail('Name', $obj->name) .
		print_if_avail('Location', $obj->location) .
		print_if_avail('Interests', $obj->interests) .
		print_if_avail('Occupation', $obj->occupation) .
		print_if_avail('Gender', ($obj->users_opt & 1024 ? 'Male' : ($obj->users_opt & 512 ? 'Unspecified' : 'Female'))) .
		print_if_avail('ICQ UIN', $obj->icq) .
		print_if_avail('AIM', $obj->aim) .
		print_if_avail('MSN Messanger', $obj->msnm) .
		print_if_avail('Jabber', $obj->jabber) .
		print_if_avail('Birth Date', $obj->bday) .
		print_if_avail('Signature', $obj->sig, 0) .
		'</td>
		<td valign="top" style="border-bottom: 3px double black">[ <a href="admaccapr.php?apr='.$obj->id.'&'._rsidl.'">Approve Account</a> | <a href="admaccapr.php?rm='.$obj->id.'&'._rsidl.'">Delete Account</a> ]</td></tr>';
	}
	qf($c);
?>
</table>
<?php require($WWW_ROOT_DISK . 'adm/admclose.html'); ?>