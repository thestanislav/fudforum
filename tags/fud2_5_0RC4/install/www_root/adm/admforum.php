<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: admforum.php,v 1.14 2003/05/26 11:15:04 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

function get_max_upload_size()
{
	$us = strtolower(ini_get('upload_max_filesize'));
	$size = (int) $us;
	if (strpos($us, 'm') !== FALSE) {
		$size *= 1024 * 1024;
	} else if (strpos($us, 'k') !== FALSE) {
		$size *= 1024;
	}
	return $size;
}

	require('./GLOBALS.php');

	/* this is here so we get the cat_id when cancel button is clicked */
	$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : (isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : '');

	fud_use('adm.inc', true);
	fud_use('forum_adm.inc', true);
	fud_use('widgets.inc', true);
	fud_use('logaction.inc');

	$tbl = $GLOBALS['DBHOST_TBL_PREFIX'];
	$max_upload_size = get_max_upload_size();
	
	if (!$cat_id || ($cat_name = q_singleval('SELECT name FROM '.$tbl.'cat WHERE id='.$cat_id)) === NULL) {
		exit('no such category');
	}

	$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : (isset($_POST['edit']) ? (int)$_POST['edit'] : '');

	if (isset($_POST['frm_submit'])) {
		$frm = new fud_forum;
		if ($_POST['frm_max_attach_size'] > $max_upload_size) {
			$_POST['frm_max_attach_size'] = floor($max_upload_size / 1024);
		}

		if (!$edit) {
			fud_use('groups_adm.inc', true);
			fud_use('groups.inc');
			$frm->cat_id = $cat_id;
			$frm->add($_POST['frm_pos']);
			logaction(_uid, 'ADDFORUM', $frm->id);
		} else {
			$frm->sync($edit, $cat_id);
			logaction(_uid, 'SYNCFORUM', $edit);
			$edit = '';
		}
	}
	if ($edit && ($c = db_arr_assoc('SELECT * FROM '.$tbl.'forum WHERE id='.$edit))) {
		foreach ($c as $k => $v) {
			${'frm_'.$k} = $v;
		}
	} else {
		$c = get_class_vars('fud_forum');
		foreach ($c as $k => $v) {
			${'frm_'.$k} = '';
		}

		/* some default values for new forums */
		$frm_pos = 'LAST';
		$frm_max_attach_size = floor($max_upload_size / 1024);
		$frm_message_threshold = '0';
		$frm_max_file_attachments = '1';
		$frm_moderated = $frm_passwd_posting = 'N';
	}

	if (isset($_GET['chpos'], $_GET['newpos'])) {
		frm_change_pos((int)$_GET['chpos'], (int)$_GET['newpos'], $cat_id);
		unset($_GET['chpos'], $_GET['newpos']);
	} else if (isset($_GET['del'])) {
		if (frm_move_forum((int)$_GET['del'], 0, $cat_id)) {
			logaction(_uid, 'FRMMARKDEL', q_singleval('SELECT name FROM '.$tbl.'forum WHERE id='.(int)$_GET['del']));
		}
	} else if (isset($_POST['btn_chcat'], $_POST['frm_id'], $_POST['cat_id'], $_POST['dest_cat'])) {
		if (frm_move_forum((int)$_POST['frm_id'], (int)$_POST['dest_cat'], $cat_id)) {
			$r = db_saq('SELECT f.name, c1.name, c2.name FROM '.$tbl.'forum f INNER JOIN '.$tbl.'cat c1 ON c1.id='.$cat_id.' INNER JOIN '.$tbl.'cat c2 ON c2.id='.(int)$_POST['dest_cat'].' WHERE f.id='.(int)$_POST['frm_id']);
			logaction(_uid, 'CHCATFORUM', 'Moved forum "'.addslashes($r[0]).'" from category: "'.addslashes($r[1]).'" to category: "'.addslashes($r[2]).'"');
		}
	}

	require($WWW_ROOT_DISK . 'adm/admpanel.php');
?>
<h2>Editing forums for <?php echo $cat_name; ?></h2>
<?php
if (!isset($_GET['chpos'])) {
?> 
<a href="admcat.php?<?php echo _rsidl; ?>">Back to categories</a><br>

<form method="post" name="frm_forum" action="admforum.php">
<?php echo _hs; ?>
<table border=0 cellspacing=1 cellpadding=3>
	<tr bgcolor="#bff8ff">
		<td>Forum Name:</td>
		<td><input type="text" name="frm_name" value="<?php echo htmlspecialchars($frm_name); ?>" maxlength=100></td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td valign=top>Description</td>
		<td><textarea nowrap name="frm_descr" cols=25 rows=5><?php echo htmlspecialchars($frm_descr); ?></textarea>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Tag Style</td>
		<td><?php draw_select('frm_tag_style', "FUD ML\nHTML\nNone", "ML\nHTML\nNONE", $frm_tag_style); ?></td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Password Posting<br><font size=-2>Posting is only allowed with a knowledge of a password</font></td>
		<td><?php draw_select('frm_passwd_posting', "No\nYes", "N\nY", $frm_passwd_posting); ?></td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Posting Password</td>
		<td><input type="passwd" maxLength=32 name="frm_post_passwd" value="<?php echo htmlspecialchars($frm_post_passwd); ?>"></td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Moderated Forum</td>
		<td><?php draw_select('frm_moderated', "No\nYes", "N\nY", yn($frm_moderated)); ?></td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Max Attachment Size:<br><font size="-1">Your php's maximum file upload size is <b><?php echo floor($max_upload_size / 1024); ?></b> KB.<br />You cannot set the forum's attachment size limit higher than that.</font></td>
		<td><input type="text" name="frm_max_attach_size" value="<?php echo $frm_max_attach_size; ?>" maxlength=100 size=5>kb</td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Max Number of file Attachments:</td>
		<td><input type="text" name="frm_max_file_attachments" value="<?php echo $frm_max_file_attachments; ?>" maxlength=100 size=5></td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td>Message Threshold<br><font size=-1>Maximum size of the message DISPLAYED<br>without the reveal link (0 == unlimited) </font></td>
		<td><input type="text" name="frm_message_threshold" value="<?php echo $frm_message_threshold; ?>" size=5> bytes</td>
	</tr>
	
	<tr bgcolor="#bff8ff">
		<td><a name="frm_icon_pos">Forum Icon</a></td>
		<td><input type="text" name="frm_forum_icon" value="<?php echo $frm_forum_icon; ?>"> <a href="javascript://" onClick="javascript:window.open('admiconsel.php', 'admiconsel', 'menubar=false,scrollbars=yes,resizable=yes,height=300,width=500,screenX=100,screenY=100')">[SELECT ICON]</a></td>
	</tr>
	
<?php if (!$edit) { ?>
	<tr bgcolor="#bff8ff">
		<td>Insert Position</td>
		<td><?php draw_select('frm_pos', "Last\nFirst", "LAST\nFIRST", ''); ?></td>
	</tr>
<?php } ?>
	
	<tr bgcolor="#bff8ff">
		<td colspan=2 align=right>
<?php
	if ($edit) {
		echo '<input type="submit" value="Cancel" name="btn_cancel"> ';
	}
?>
			<input type="submit" value="<?php echo ($edit ? 'Update Forum' : 'Add Forum'); ?>" name="frm_submit">
		</td>
	</tr>
		
</table>
<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
<?php
	if ($edit) {
		echo '<input type="hidden" name="edit" value="'.$edit.'">';
	}
	echo '</form>';
} else {
	echo '<a href="admforum.php?cat_id='.$cat_id.'&'._rsidl.'">Cancel</a>';
}
?>
<br>
<table border=0 cellspacing=3 cellpadding=2>
<tr bgcolor="#e5ffe7">
	<td nowrap><font size=-2>Forum name</font></td>
	<td><font size=-2>Description</font></td>
	<td nowrap><font size=-2>Password Posting</font></td>
	<td align="center"><font size=-2>Action</font></td>
	<td><font size=-2>Category</font></td>
	<td><font size=-2>Position</font></td>
</tr>
<?php
	$move_ct = create_cat_select('dest_cat', '', $cat_id);

	$i = 1;
	$c = uq('SELECT id, name, descr, passwd_posting, view_order FROM '.$tbl.'forum WHERE cat_id='.$cat_id.' ORDER BY view_order');
	while ($r = db_rowobj($c)) {
		if ($edit == $r->id) {
			$bgcolor = ' bgcolor="#ffb5b5"';
		} else {
			$bgcolor = ($i++%2) ? ' bgcolor="#fffee5"' : '';
		}
		if (isset($_GET['chpos'])) {
			if ($_GET['chpos'] == $r->view_order) {
				$bgcolor = ' bgcolor="#ffb5b5"';
			} else if ($_GET['chpos'] != ($r->view_order - 1)) {
				echo '<tr bgcolor="#efefef"><td align=center colspan=9><a href="admforum.php?chpos='.$_GET['chpos'].'&newpos='.($r->view_order - ($_GET['chpos'] < $r->view_order ? 1 : 0)).'&cat_id='.$cat_id.'&'._rsidl.'">Place Here</a></td></tr>';
			} else {
				$lp = $r->view_order;
			}
		}
		$c_name = !$move_ct ? $c_name : '<form method="post" action="admforum.php">'._hs.'<input type="hidden" name="frm_id" value="'.$r->id.'"><input type="hidden" name="cat_id" value="'.$cat_id.'"><input type="submit" name="btn_chcat" value="Move To: "> '.$move_ct.'</form>';
		echo '<tr '.$bgcolor.'><td>'.$r->name.'</td><td><font size="-2">'.substr($r->descr, 0, 30).'</font></td><td>'.($r->passwd_posting == 'Y' ? 'Yes' : 'No').'</td><td nowrap>[<a href="admforum.php?cat_id='.$cat_id.'&edit='.$r->id.'&'._rsidl.'">Edit</a>] [<a href="admforum.php?cat_id='.$cat_id.'&del='.$r->id.'&'._rsidl.'">Delete</a>]</td><td nowrap>'.$c_name.'</td><td nowrap>[<a href="admforum.php?chpos='.$r->view_order.'&cat_id='.$cat_id.'&'._rsidl.'">Change</a>]</td></tr>';
	}
	qf($c);
	if (isset($lp)) {
		echo '<tr bgcolor="#efefef"><td align=center colspan=9><a href="admforum.php?chpos='.$_GET['chpos'].'&newpos='.($lp + 1).'&cat_id='.$cat_id.'&'._rsid.'">Place Here</a></td></tr>';
	}
?>
</table>
<?php require($WWW_ROOT_DISK . 'adm/admclose.html'); ?>