<?php
/**
* copyright            : (C) 2001-2011 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	require('./GLOBALS.php');
	fud_use('adm.inc', true);
	fud_use('spider_adm.inc', true);
	fud_use('widgets.inc', true);
	fud_use('logaction.inc');

	$tbl = $GLOBALS['DBHOST_TBL_PREFIX'];

	require($WWW_ROOT_DISK .'adm/header.php');
	if (!empty($_POST['btn_cancel'])) {
		unset($_POST);
	}

	$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : (isset($_POST['edit']) ? (int)$_POST['edit'] : '');

	// Add or edit a spider.
	if (isset($_POST['frm_submit']) && !empty($_POST['spider_botname'])) {
		$error = 0;

		if ($edit && !$error) {
			$spider = new fud_spider;
			$spider->sync($edit);
			$edit = '';	
			echo successify('Spider was successfully updated.');
			logaction(_uid, 'Update spider', 0, $_POST['spider_botname']);
		} else if (!$error) {
			$spider = new fud_spider;
			$spider->add();
			echo successify('Spider was successfully added.');
			logaction(_uid, 'Add spider', 0, $_POST['spider_botname']);
		}
	}

	/* Remove a spider. */
	if (isset($_GET['del'])) {
		$id = (int)$_GET['del'];
		$spider = new fud_spider();
		$spider->delete($id);
		echo successify('Spider was successfully deleted.');
		logaction(_uid, 'Delete spider', 0, $id);
	}

	/* Set defaults. */
	if ($edit && ($c = db_arr_assoc('SELECT * FROM '. $tbl .'spiders WHERE id='. $edit))) {
		foreach ($c as $k => $v) {
			${'spider_'.$k} = $v;
		}
	} else {
		$c = get_class_vars('fud_spider');
		foreach ($c as $k => $v) {
			${'spider_'. $k} = '';
		}
	}
?>
<h2>Spiders, Bots and Crawlers</h2>

<div class="tutor">
Detect and auto-login the spiders, bots and crawlers that are typically used by search engines to index your site.
</div>

<?php
echo '<h3>'. ($edit ? '<a name="edit">Edit Spider:</a>' : 'Add New Spider:') .'</h3>';
?>
<form method="post" id="frm_forum" action="admspiders.php">
<?php echo _hs; ?>
<table class="datatable">
	<tr class="field">
		<td>Bot name:<br /><font size="-2">Name of the spider.</font></td>
		<td><input type="text" name="spider_botname" value="<?php echo $spider_botname; ?>" /></td>
	</tr>

	<tr class="field">
		<td>Useragent:<br /><font size="-2">Spider's useragent string (partial matches are accepted).</font></td>
		<td><input type="text" name="spider_useragent" value="<?php echo $spider_useragent; ?>" /></td>
	</tr>

	<tr class="field">
		<td>Bot's IP Address:<br /><font size="-2">IP Address of the spider.</font></td>
		<td><input type="text" name="spider_bot_ip" value="<?php echo $spider_bot_ip; ?>" /></td>
	</tr>

<!-- TODO: Remove?
	<tr class="field">
		<td>Theme:<br /><font size="-2">What theme should the spider see when they visit your site.</font></td>
		<td><select name="spider_theme">
		<?php
			$c = uq('SELECT id, name FROM '. $tbl .'themes ORDER BY name');
			while ($r = db_rowarr($c)) {
				echo '<option value="'. $r[0] .'"'.($r[0] != $spider_theme ? '' : ' selected="selected"') .'>'. $r[1] .'</option>';
			}
			unset($c);
		?>
		</select></td>
	</tr>
-->

	<tr class="field">
		<td>Enabled:<br /><font size="-2">Enable or disable detection of this bot.</font></td>
		<td><?php draw_select('spider_bot_opts', "Disabled\nEnabled", "1\n0", ($spider_bot_opts & (1))); ?></td>
	</tr>

	<tr class="fieldaction">
		<td colspan="2" align="right">
<?php
	if ($edit) {
		echo '<input type="hidden" name="edit" value="'. $edit .'" />';
		echo '<input type="submit" value="Cancel" name="btn_cancel" /> ';
	}
?>
			<input type="submit" value="<?php echo ($edit ? 'Update Spider' : 'Add Spider'); ?>" name="frm_submit" />
		</td>
	</tr>
</table>
</form>

<h3>Defined spiders:</h3>
<table class="resulttable fulltable">
<thead><tr class="resulttopic">
	<th>Bot Name</th><th>Useragent</th><th>IP Address</th><th>Action</th>
</tr></thead>
<?php
	$i = 0;
	$c = uq(q_limit('SELECT * FROM '. $tbl .'spiders ORDER BY botname', 100));
	while ($r = db_rowobj($c)) {
		$i++;
		$bgcolor = ($edit == $r->id) ? ' class="resultrow3"' : (($i%2) ? ' class="resultrow1"' : ' class="resultrow2"');
		echo '<tr'. $bgcolor .'><td>'. $r->botname .'</td><td>'. $r->useragent .'</td><td>'. $r->bot_ip .'</td>';
		echo '<td><a href="admspiders.php?edit='. $r->id .'&amp;'. __adm_rsid .'#edit">Edit</a> | <a href="admspiders.php?del='. $r->id .'&amp;'. __adm_rsid .'">Delete</a></td></tr>';
	}
	unset($c);
	if (!$i) {
		echo '<tr class="field"><td colspan="4"><center>No spiders found. Define some above.</center></td></tr>';
	}
?>
</table>

<?php require($WWW_ROOT_DISK .'adm/footer.php'); ?>
