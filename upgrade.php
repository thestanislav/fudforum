<?php
/* ���ظ ���� )�)����  	
 * First 20 bytes of linux 2.4.18, so various windows utils think
 * this is a binary file and don't apply CRLF logic
 */

/***************************************************************************
* copyright            : (C) 2001-2003 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: upgrade.php,v 1.206 2003/10/13 18:23:39 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it 
* under the terms of the GNU General Public License as published by the 
* Free Software Foundation; either version 2 of the License, or 
* (at your option) any later version.
***************************************************************************/

$__UPGRADE_SCRIPT_VERSION = 1300;

/* 4.3.0+ Functions */

if (!function_exists("array_diff_assoc")) {
	function array_diff_assoc($a1, $a2)
	{
		ksort($a1); ksort($a2);
		return array_diff(array_keys($a1), array_keys($a2));
	}
}

if (!function_exists('file_get_contents')) {
	function file_get_contents($path)
	{
		if (!($fp = fopen($path, 'rb'))) {
			return FALSE;
		}
		$data = fread($fp, filesize($path));
		fclose($fp);

		return $data;
	}
}

if (!function_exists('glob')) {
	function glob($path)
	{
		$d = dirname($path);
		$ext = substr(str_replace($d, '', $path), 2);
		$ext = '!\.'.$ext.'$!';
		$ret = array();

		if (!($dir = opendir($d))) {
			return $ret;
		}
		readdir($dir); readdir($dir);
		while (($f = readdir($dir))) {
			if (preg_match($ext, $f)) {
				$ret[] = $d . '/' . $f;
			}		
		}
		closedir($dir);

		return $ret;
	}
}

/* END: 4.3.0+ Functions */

/* Sql Upgrade Functions */

function queries()
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}group_members SET user_id=2147483647 WHERE user_id=4294967295");
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}group_cache SET user_id=2147483647 WHERE user_id=4294967295");
}

function users_alias($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}users SET alias=login");
}

function users_users_opt($flds)
{
	$tmp  = isset($flds['private_messages']) ? "(CASE WHEN private_messages='Y' THEN 16 ELSE 0 END)" : "| (CASE WHEN email_messages='Y' THEN 16 ELSE 0 END)";
	$tmp .= isset($flds['show_sigs']) ? "| (CASE WHEN show_sigs='Y' THEN 4096 ELSE 0 END)" : "|4096";
	$tmp .= isset($flds['show_avatars']) ? "| (CASE WHEN show_avatars='Y' THEN 8192 ELSE 0 END)" : "|8192";
	$tmp .= isset($flds['pm_messages']) ? "| (CASE WHEN pm_messages='Y' THEN 32 ELSE 0 END)" : "|32";
	$tmp .= isset($flds['show_im']) ? "| (CASE WHEN show_im='Y' THEN 16384 ELSE 0 END)" : "|16384";
	$tmp .= isset($flds['pm_notify']) ? "| (CASE WHEN pm_notify='Y' THEN 64 ELSE 0 END)" : "|64";
	$tmp .= isset($flds['acc_status']) ? "| (CASE WHEN acc_status='P' THEN 2097152 ELSE 0 END)" : "|2097152";

	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}users SET users_opt={$tmp} | (CASE WHEN display_email='Y' THEN 1 ELSE 0 END) | (CASE WHEN notify='Y' THEN 2 ELSE 0 END) | (CASE WHEN notify_method='EMAIL' THEN 4 ELSE 0 END) | (CASE WHEN ignore_admin='Y' THEN 8 ELSE 0 END) | (CASE WHEN email_messages='Y' THEN 16 ELSE 0 END) | (CASE WHEN default_view='msg' OR default_view='msg_tree' THEN 128 ELSE 0 END) | (CASE WHEN default_view='tree' OR default_view='tree_msg' THEN 256 ELSE 0 END) | (CASE WHEN gender='UNSPECIFIED' THEN 512 ELSE 0 END) | (CASE WHEN gender='MALE' THEN 1024 ELSE 0 END) | (CASE WHEN append_sig='Y' THEN 2048 ELSE 0 END) | (CASE WHEN invisible_mode='Y' THEN 32768 ELSE 0 END) | (CASE WHEN blocked='Y' THEN 65536 ELSE 0 END) | (CASE WHEN email_conf='Y' THEN 131072 ELSE 0 END) | (CASE WHEN coppa='Y' THEN 262144 ELSE 0 END) | (CASE WHEN is_mod='Y' THEN 524288 ELSE 0 END) | (CASE WHEN is_mod='A' THEN 1048576 ELSE 0 END) | (CASE WHEN avatar_approved='NO' THEN 4194304 ELSE 0 END) | (CASE WHEN avatar_approved='Y' THEN 8388608 ELSE 16777216 END)");
}

function replace_replace_opt($flds)
{
	q("UPDATE mm_replace SET replace_opt=0 WHERE type='PERL'");
}

function users_bio($flds)
{
	show_debug_message('Moving homepage & signature to database');
	$list = glob($GLOBALS['USER_SETTINGS_PATH'].'*.fud');
	foreach ($list as $f) {
		$raw = file_get_contents($f);
		$l = (int) substr($raw, 0, 8);
		$bio = substr($raw, $l + 16);
		$id = basename($f, '.fud');
		q("UPDATE ".$DBHOST_TBL_PREFIX."users SET home_page='".addslashes($www)."', bio='".addslashes($bio)."' WHERE id=".$id);
	}
	show_debug_message('Done: Moving homepage & signature to database');
}

function group_members_group_members_opt($flds)
{
	$tmp  = "262144 | "; /* search permission */
	$tmp .= isset($flds['up_VISIBLE']) ? "(CASE WHEN up_VISIBLE='Y' THEN 1 ELSE 0 END) " : "1";
	$tmp .= isset($flds['up_VIEW']) ? "| (CASE WHEN up_VIEW='Y' THEN 2 ELSE 0 END)" : "| (CASE WHEN up_READ='Y' THEN 2 ELSE 0 END)";
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}group_members SET group_members_opt={$tmp} | (CASE WHEN up_POST='Y' THEN 4 ELSE 0 END) | (CASE WHEN up_REPLY='Y' THEN 8 ELSE 0 END) | (CASE WHEN up_EDIT='Y' THEN 16 ELSE 0 END) | (CASE WHEN up_DEL='Y' THEN 32 ELSE 0 END) | (CASE WHEN up_STICKY='Y' THEN 64 ELSE 0 END) | (CASE WHEN up_POLL='Y' THEN 128 ELSE 0 END) | (CASE WHEN up_FILE='Y' THEN 256 ELSE 0 END) | (CASE WHEN up_VOTE='Y' THEN 512 ELSE 0 END) | (CASE WHEN up_RATE='Y' THEN 1024 ELSE 0 END) | (CASE WHEN up_SPLIT='Y' THEN 2048 ELSE 0 END) | (CASE WHEN up_LOCK='Y' THEN 4096 ELSE 0 END) | (CASE WHEN up_MOVE='Y' THEN 8192 ELSE 0 END) | (CASE WHEN up_SML='Y' THEN 16384 ELSE 0 END) | (CASE WHEN up_IMG='Y' THEN 32768 ELSE 0 END) | (CASE WHEN approved='Y' THEN 65536 ELSE 0 END) | (CASE WHEN group_leader='Y' THEN 131072 ELSE 0 END)");
}

function group_cache_group_cache_opt($flds)
{
	$tmp  = isset($flds['p_VISIBLE']) ? "(CASE WHEN p_VISIBLE='Y' THEN 1 ELSE 0 END) " : "1";
	$tmp .= isset($flds['p_VIEW']) ? "| (CASE WHEN p_VIEW='Y' THEN 2 ELSE 0 END)" : "| (CASE WHEN p_READ='Y' THEN 2 ELSE 0 END)";
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}group_members SET group_members_opt={$tmp} | (CASE WHEN p_POST='Y' THEN 4 ELSE 0 END) | (CASE WHEN p_REPLY='Y' THEN 8 ELSE 0 END) | (CASE WHEN p_EDIT='Y' THEN 16 ELSE 0 END) | (CASE WHEN p_DEL='Y' THEN 32 ELSE 0 END) | (CASE WHEN p_STICKY='Y' THEN 64 ELSE 0 END) | (CASE WHEN p_POLL='Y' THEN 128 ELSE 0 END) | (CASE WHEN p_FILE='Y' THEN 256 ELSE 0 END) | (CASE WHEN p_VOTE='Y' THEN 512 ELSE 0 END) | (CASE WHEN p_RATE='Y' THEN 1024 ELSE 0 END) | (CASE WHEN p_SPLIT='Y' THEN 2048 ELSE 0 END) | (CASE WHEN p_LOCK='Y' THEN 4096 ELSE 0 END) | (CASE WHEN p_MOVE='Y' THEN 8192 ELSE 0 END) | (CASE WHEN p_SML='Y' THEN 16384 ELSE 0 END) | (CASE WHEN p_IMG='Y' THEN 32768 ELSE 0 END)");
}

function groups_groups_opt($flds)
{
	$tmp = isset($flds['p_VIEW']) ? " (CASE WHEN p_VIEW='Y' THEN 2 ELSE 0 END)" : " (CASE WHEN p_READ='Y' THEN 2 ELSE 0 END)";	
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}group SET groups_opt={$tmp} | (CASE WHEN p_VISIBLE='Y' THEN 1 ELSE 0 END) | (CASE WHEN p_POST='Y' THEN 4 ELSE 0 END) | (CASE WHEN p_REPLY='Y' THEN 8 ELSE 0 END) | (CASE WHEN p_EDIT='Y' THEN 16 ELSE 0 END) | (CASE WHEN p_DEL='Y' THEN 32 ELSE 0 END) | (CASE WHEN p_STICKY='Y' THEN 64 ELSE 0 END) | (CASE WHEN p_POLL='Y' THEN 128 ELSE 0 END) | (CASE WHEN p_FILE='Y' THEN 256 ELSE 0 END) | (CASE WHEN p_VOTE='Y' THEN 512 ELSE 0 END) | (CASE WHEN p_RATE='Y' THEN 1024 ELSE 0 END) | (CASE WHEN p_SPLIT='Y' THEN 2048 ELSE 0 END) | (CASE WHEN p_LOCK='Y' THEN 4096 ELSE 0 END) | (CASE WHEN p_MOVE='Y' THEN 8192 ELSE 0 END) | (CASE WHEN p_SML='Y' THEN 16384 ELSE 0 END) | (CASE WHEN p_IMG='Y' THEN 32768 ELSE 0 END) | (CASE WHEN p_IMG='Y' THEN 32768 ELSE 0 END) | (CASE WHEN p_IMG='Y' THEN 32768 ELSE 0 END) | (CASE WHEN p_IMG='Y' THEN 32768 ELSE 0 END)");
}

function groups_groups_opti($flds)
{
	$tmp = isset($flds['p_VIEW']) ? " (CASE WHEN p_VIEW='I' THEN 2 ELSE 0 END)" : " (CASE WHEN p_READ='I' THEN 2 ELSE 0 END)";	
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}group SET groups_opti={$tmp} | (CASE WHEN p_VISIBLE='I' THEN 1 ELSE 0 END) | (CASE WHEN p_POST='I' THEN 4 ELSE 0 END) | (CASE WHEN p_REPLI='I' THEN 8 ELSE 0 END) | (CASE WHEN p_EDIT='I' THEN 16 ELSE 0 END) | (CASE WHEN p_DEL='I' THEN 32 ELSE 0 END) | (CASE WHEN p_STICKI='I' THEN 64 ELSE 0 END) | (CASE WHEN p_POLL='I' THEN 128 ELSE 0 END) | (CASE WHEN p_FILE='I' THEN 256 ELSE 0 END) | (CASE WHEN p_VOTE='I' THEN 512 ELSE 0 END) | (CASE WHEN p_RATE='I' THEN 1024 ELSE 0 END) | (CASE WHEN p_SPLIT='I' THEN 2048 ELSE 0 END) | (CASE WHEN p_LOCK='I' THEN 4096 ELSE 0 END) | (CASE WHEN p_MOVE='I' THEN 8192 ELSE 0 END) | (CASE WHEN p_SML='I' THEN 16384 ELSE 0 END) | (CASE WHEN p_IMG='I' THEN 32768 ELSE 0 END) | (CASE WHEN p_IMG='I' THEN 32768 ELSE 0 END) | (CASE WHEN p_IMG='I' THEN 32768 ELSE 0 END) | (CASE WHEN p_IMG='I' THEN 32768 ELSE 0 END)");
}

function nntp_nntp_opt($flds)
{
	$tmp = isset($flds['create_users']) ? " (CASE WHEN create_users='Y' THEN 32 ELSE 0 END)" : "32";
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}nntp SET nntp_opt={$tmp} | (CASE WHEN nntp_post_apr='Y' THEN 1 ELSE 0 END) | (CASE WHEN allow_frm_post='Y' THEN 2 ELSE 0 END) | (CASE WHEN frm_post_apr='Y' THEN 4 ELSE 0 END) | (CASE WHEN allow_nntp_attch='Y' THEN 8 ELSE 0 END) | (CASE WHEN complex_reply_match='Y' THEN 16 ELSE 0 END) | (CASE WHEN auth='NONE' THEN 64 ELSE 0 END) | (CASE WHEN auth='ORIGINAL' THEN 128 ELSE 0 END)");
}

function mlist_mlist_opt($flds)
{
	$tmp = isset($flds['create_users']) ? " (CASE WHEN create_users='Y' THEN 64 ELSE 0 END)" : "64";
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}mlist SET mlist_opt={$tmp} | (CASE WHEN mlist_post_apr='Y' THEN 1 ELSE 0 END) | (CASE WHEN allow_frm_post='Y' THEN 2 ELSE 0 END) | (CASE WHEN frm_post_apr='Y' THEN 4 ELSE 0 END) | (CASE WHEN allow_mlist_attch='Y' THEN 8 ELSE 0 END) | (CASE WHEN allow_mlist_html='Y' THEN 16 ELSE 0 END) | (CASE WHEN complex_reply_match='Y' THEN 32 ELSE 0 END)");
}

function attach_attach_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}attach SET attach_opt=1 WHERE private='Y'");
}

function cat_cat_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}cat SET cat_opt=(CASE WHEN allow_collapse='Y' THEN 1 ELSE 0 END) | (CASE WHEN default_view='OPEN' THEN 2 ELSE 0 END)");
}

function email_block_email_block_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}email_block SET email_block_opt=0 WHERE type='REGEX'");
}

function forum_forum_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}forum SET forum_opt=(CASE WHEN anon_forum='Y' THEN 1 ELSE 0 END) | (CASE WHEN moderated='Y' THEN 2 ELSE 0 END) | (CASE WHEN passwd_posting='Y' THEN 4 ELSE 0 END) | (CASE WHEN tag_style='NONE' THEN 8 ELSE 0 END) | (CASE WHEN tag_style='ML' THEN 16 ELSE 0 END)");
}

function level_level_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}level SET level_opt=(CASE WHEN pri='B' THEN 0 ELSE (CASE WHEN level_opt='A' THEN 1 ELSE 2 END) END)");
}

function msg_msg_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}msg SET msg_opt=(CASE WHEN show_sig='Y' THEN 1 ELSE 0 END) | (CASE WHEN smiley_disabled='Y' THEN 2 ELSE 0 END)");
}

function msg_apr($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}msg SET apr=1 WHERE approved='Y'");
}

function pmsg_pmsg_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET pmsg_opt=(CASE WHEN show_sig='Y' THEN 1 ELSE 0 END) | (CASE WHEN smiley_disabled='Y' THEN 2 ELSE 0 END) | (CASE WHEN track='Y' THEN 4 ELSE 0 END) | (CASE WHEN track='SENT' THEN 8 ELSE 0 END) | (CASE WHEN mailed='Y' THEN 16 ELSE 0 END) | (CASE WHEN nrf_status='N' THEN 32 ELSE 0 END) | (CASE WHEN nrf_status='R' THEN 64 ELSE 0 END)");
}

function pmsg_fldr($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET fldr=0 WHERE folder_id='PROC'");
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET fldr=1 WHERE folder_id='INBOX'");
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET fldr=2 WHERE folder_id='SAVED'");
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET fldr=3 WHERE folder_id='SENT'");
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET fldr=4 WHERE folder_id='DRAFT'");
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}pmsg SET fldr=5 WHERE folder_id='TRASH'");
}

function themes_theme_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}themes SET theme_opt=(CASE WHEN enabled='Y' THEN 1 ELSE 0 END) | (CASE WHEN t_default='Y' THEN 2 ELSE 0 END) | (CASE WHEN theme='path_info' THEN 4 ELSE 0 END)");
}

function thread_thread_opt($flds)
{
	q("UPDATE {$GLOBALS['DBHOST_TBL_PREFIX']}thread SET thread_opt=(CASE WHEN locked='Y' THEN 1 ELSE 0 END) | (CASE WHEN thread_opt='ANNOUNCE' AND is_sticky='Y' THEN 2 ELSE 0 END) | (CASE WHEN thread_opt='STICKY' AND is_sticky='Y' THEN 4 ELSE 0 END)");
}

/* END: Sql Upgrade Functions */

function fud_ini_get($opt)
{
	return (ini_get($opt) == '1' ? 1 : 0);
}

function change_global_settings($list)
{
	$settings = file_get_contents($GLOBALS['INCLUDE'] . 'GLOBALS.php');
	foreach ($list as $k => $v) {
		if (($p = strpos($settings, '$' . $k)) === false) {
			$pos = strpos($settings, '$ADMIN_EMAIL');
			if (is_int($v)) {
				$settings = substr_replace($settings, "\t{$k}\t= {$v};\n", $p, 0);
			} else {
				$v = addcslashes($v, '\\"');
				$settings = substr_replace($settings, "\t{$k}\t= \"{$v}\";\n", $p, 0);
			}
		} else {
			$p = strpos($settings, '=', $p) + 1;
			$e = $p + strrpos(substr($settings, $p, (strpos($settings, "\n", $p) - $p)), ';');

			if (is_int($v)) {
				$settings = substr_replace($settings, ' '.$v, $p, ($e - $p));
			} else {
				$v = addcslashes($v, '\\"');
				$settings = substr_replace($settings, ' "'.$v.'"', $p, ($e - $p));
			}
		}
	}

	$fp = fopen($GLOBALS['INCLUDE'].'GLOBALS.php', 'w');
	fwrite($fp, $settings);
	fclose($fp);
}

function show_debug_message($msg)
{
	echo $msg . '<br>';
	flush();
}

function upgrade_error($msg)
{
	exit('<font color="red">'.$msg.'</font></body></html>');
}

function get_stbl_from_file($file)
{
	$data = str_replace('{SQL_TABLE_PREFIX}', $pfx, file_get_contents($file));
	$tbl = array('name'=>'', 'index'=>array(), 'flds'=>array());

	/* fetch table name */
	if (!preg_match('!CREATE TABLE '.$GLOBALS['DBHOST_TBL_PREFIX'].'([a-z_]+)!', $data, $m)) {
		return;
	}
	$tbl['name'] = $GLOBALS['DBHOST_TBL_PREFIX'] . rtrim($m[1]);

	/* match fields */
	if (!preg_match("!\(([^;]+)\);!", $data, $m)) {
		return;
	}
	$m = explode("\n", $m[1]);
	foreach ($m as $v) {
		if (!($v = trim($v))) {
			continue;
		}
		if (preg_match("!([a-z_]+)\s([^\n,]+)!", $v, $r)) {
			if (strpos($r[2], ' NOT NULL') !== false) {
				$r[2] = str_replace(' NOT NULL', '', $r[2]);
				$not_null = 1;
			} else {
				$not_null = 0;
			}

			if (strpos($r[2], ' AUTO_INCREMENT') !== false) {
				$r[2] = str_replace(' AUTO_INCREMENT', '', $r[2]);
				$auto = 1;
			} else {
				$auto = 0;
			}

			if (preg_match('! DEFAULT (.*)$!', $r[2], $d)) {
				$default = str_replace("'", '', $d[1]);
				$r[2] = str_replace(' DEFAULT '.$d[1], '', $r[2]);
			} else {
				$default = null;
			}

			if (strpos($r[2], ' PRIMARY KEY') !== false) {
				$r[2] = str_replace(' PRIMARY KEY', '', $r[2]);
				$key = 1;
			} else {
				$key = 0;
			}

			$tbl['flds'][$r[1]] = array('type'=>trim($r[2]), 'not_null'=>$not_null, 'primary'=>$key, 'default'=>$default, 'auto'=>$auto); 
		}
	}

	if (preg_match_all('!CREATE ?(UNIQUE|) INDEX ([a-x_]+) ON '.$tbl['name'].' \(([^;]+)\);!', $data, $m)) {
		$c = count($m[0]);
		for ($i = 0; $i < $c; $i++) {
			$tbl['index'][$m[2][$i]] = array('unique'=>(empty($m[1][$i]) ? 0 : 1), 'cols'=>str_replace(' ', '', $m[3][$i]));
		}
	}

	return $tbl;
}

function get_fud_table_list()
{
	if (__dbtype__ == 'mysql') {
		$c = q("show tables LIKE '".str_replace('_', '\\_', $GLOBALS['DBHOST_TBL_PREFIX'])."%'");
	} else {
		$c = q("SELECT relname FROM pg_class WHERE relkind='r' AND relname LIKE '".str_replace('_', '\\\\_', $GLOBALS['DBHOST_TBL_PREFIX'])."%'");
	}
	while ($r = db_rowarr($c)) {
		$ret[] = $r[0];
	}	
	qf($c);

	return $ret;
}

function get_fud_col_list($table)
{
	if (__dbtype__ == 'mysql') {
		$c = q("show fields from {$table}");
		while ($r = db_rowobj($c)) {
			$type = strtoupper(preg_replace('!(int|bigint)\(([0-9]+)\)!', '\1', $r->Type));
			$not_null = empty($r->Null) ? 1 : 0;
			$key = $r->Key == 'PRI' ? 1 : 0;
			$default = (!is_null($r->Default) && $r->Default != 'NULL') ? $r->Default : '';
			$auto = $r->Extra ? 1 : 0;

			$ret[$r->Field] = array('type'=>$type, 'not_null'=>$not_null, 'primary'=>$key, 'default'=>$default, 'auto'=>$auto); 
		}
		qf($c);
	} else {
		$c = q("SELECT a.attname, pg_catalog.format_type(a.atttypid, a.atttypmod), a.attnotnull, a.atthasdef, substring(d.adsrc for 128) FROM pg_catalog.pg_class c INNER JOIN pg_catalog.pg_attribute a ON  a.attrelid = c.oid LEFT JOIN pg_catalog.pg_attrdef d ON d.adnum=a.attnum AND d.adrelid = c.oid WHERE c.relname ~ '^{$table}\$' AND a.attnum > 0 AND NOT a.attisdropped");		
		while ($r = db_rowarr($c)) {
			$auto = !strncmp($r[4], 'nextval', 7) ? 1 : 0;
			if (!$auto) {
				$key = 1;
				$type = 'INT';
				$not_null = 1;
				$default = null;
			} else {
				$key = 0;
				$not_null = $r[2] == 't' ? 1 : 0;
				$default = $r[3] == 't' ? trim(str_replace("'", '', $r[3])) : null;
				$type = strtoupper(preg_replace(array('!character varying!','!integer!'), array('VARCHAR', 'INT'), $r[1]));
			}
		
			$ret[$r[0]] = array('type'=>$type, 'not_null'=>$not_null, 'primary'=>$key, 'default'=>$default, 'auto'=>$auto);
		}
		qf($r);
	}
	return $ret;
}

function get_fud_idx_list($table)
{
	$tbl = array();

	if (__dbtype__ == 'mysql') {
		$c = q("show index from {$table}");
		while ($r = db_rowobj($c)) {
			if ($r->Key_name == 'PRIMARY') {
				continue;
			}
			if (!isset($tbl[$r->Key_name])) {
				$tbl[$r->Key_name] = array('unique'=>!$r->Non_unique, 'cols'=>array($r->Column_name));
			} else {
				$tbl[$r->Key_name]['cols'][] = $r->Column_name;
			}
		}
		qf($c);

		foreach ($tbl as $k => $v) {
			$tbl[$k]['cols'] = implode(',', $v['cols']);
		}
	} else {
		$c = q("SELECT pg_catalog.pg_get_indexdef(i.indexrelid) FROM pg_catalog.pg_class c, pg_catalog.pg_class c2, pg_catalog.pg_index i WHERE c.relname ~ '^{$table}\$' AND c.oid= i.indrelid AND i.indexrelid = c2.oid");
		while ($r = db_rowarr($r)) {
			if (preg_match('!CREATE ?(UNIQUE|) INDEX ([a-x_]+) ON '.$tbl['name'].' .*\(([^;]+)\);!', $data, $m)) {
				$tbl[$m[2]] = array('unique'=>(empty($m[1]) ? 0 : 1), 'cols'=>$m[3]);
			}
		}
		qf($c);
	}
	return $tbl;
}

function add_table($file)
{
	$src = array("!#.*\n!", '!{SQL_TABLE_PREFIX}!', '!UNIX_TIMESTAMP!');
	$dst = array('', $GLOBALS['DBHOST_TBL_PREFIX'], time());
	if (__dbtype__ != 'mysql') {
		array_push($src, '!BINARY!', '!DROP TABLE IF EXISTS ([^;]+);!', '!INT NOT NULL AUTO_INCREMENT!');
		array_push($dst, '', '', 'SERIAL');
	}

	$data = file_get_contents($file);
	$ql = explode(';', trim(preg_replace($src, $dst, $data)));
	foreach ($ql as $q) {
		q($q);
	}
}

function drop_table($name)
{
	q("DROP TABLE {$name}");
}

function add_index($tbl, $name, $unique, $flds)
{
	$unique = $unique ? 'UNIQUE' : '';
	q("CREATE {$unique} INDEX {$name} ON {$tbl} ({$flds})");
}

function drop_index($tbl, $name)
{
	if (__dbtype__ != 'mysql') {
		echo("DROP INDEX {$name}");
	} else {
		echo("ALTER TABLE {$tbl} DROP INDEX {$name}");
	}
}

function drop_field($tbl, $name)
{
	q("ALTER TABLE {$tbl} DROP {$name}");
}

function init_sql_func()
{
	if (__dbtype__ == 'mysql') {
		mysql_connect($GLOBALS['DBHOST'], $GLOBALS['DBHOST_USER'], $GLOBALS['DBHOST_PASSWORD']) or upgrade_error('MySQL Error: #'.mysql_errno().' ('.mysql_error().')');
		mysql_select_db($GLOBALS['DBHOST_DBNAME']) or upgrade_error('MySQL Error: #'.mysql_errno().' ('.mysql_error().')');

		function q($query) 
		{
			$r = mysql_query($query) or upgrade_error('MySQL Error: #'.mysql_errno().' ('.mysql_error().'): '.htmlspecialchars($query));
			return $r;
		}
		
		function qf(&$r)
		{
			unset($r);
		}
		
		function db_rowobj($result)
		{
			return mysql_fetch_object($result);
		}

		function db_rowarr($result)
		{
			return mysql_fetch_row($result);
		}
		
		function q_singleval($query)
		{
			return @current(mysql_fetch_row(q($query)));
		}
		
		function check_sql_perms()
		{
			mysql_query('DROP TABLE IF EXISTS fud_forum_upgrade_test_table');
			if (!mysql_query('CREATE TABLE fud_forum_upgrade_test_table (test_val INT)')) {
				upgrade_error('FATAL ERROR: your forum\'s MySQL account does not have permissions to create new MySQL tables.<br>Enable this functionality and restart the script.');
			}	
			if (!mysql_query('ALTER TABLE fud_forum_upgrade_test_table ADD test_val2 INT')) {
				upgrade_error('FATAL ERROR: your forum\'s MySQL account does not have permissions to run ALTER queries on existing MySQL tables<br>Enable this functionality and restart the script.');
			}	
			if (!mysql_query('DROP TABLE fud_forum_upgrade_test_table')) {
				upgrade_error('FATAL ERROR: your forum\'s MySQL account does not have permissions to run DROP TABLE queries on existing MySQL tables<br>Enable this functionality and restart the script.');
			}
		}
	} else if (__dbtype__ == 'pgsql') {
		$connect_str = '';
		if (!empty($GLOBALS['DBHOST'])) {
			$connect_str .= 'host='.$GLOBALS['DBHOST'];
		}
		if (!empty($GLOBALS['DBHOST_USER'])) {
			$connect_str .= ' user='.$GLOBALS['DBHOST_USER'];
		}
		if (!empty($GLOBALS['DBHOST_PASSWORD'])) {
			$connect_str .= ' password='.$GLOBALS['DBHOST_PASSWORD'];
		}
		if (!empty($GLOBALS['DBHOST_DBNAME'])) {
			$connect_str .= ' dbname='.$GLOBALS['DBHOST_DBNAME'];
		}
		if (!($conn = pg_connect(ltrim($connect_str)))) {
			upgrade_error('Failed to establish database connection to '.$GLOBALS['DBHOST']);
		}
		define('__FUD_SQL_LNK__', $conn);
		
		function q($query)
		{
			$r = pg_query(__FUD_SQL_LNK__, $query) or upgrade_error('PostgreSQL Error: '.pg_last_error(__FUD_SQL_LNK__).'<br>Query: '.htmlspecialchars($query));
			return $r;
		}
		
		function qf(&$r)
		{
			unset($r);			
		}
		
		function db_rowobj($result)
		{
			return pg_fetch_object($result);
		}
		
		function db_rowarr($result)
		{
			return pg_fetch_array($result);
		}
		
		function q_singleval($query)
		{
			return @current(pg_fetch_row(q($query)));
		}
		
		function check_sql_perms()
		{
			@pg_query(__FUD_SQL_LNK__, 'DROP TABLE fud_forum_upgrade_test_table');
			if (!pg_query(__FUD_SQL_LNK__, 'CREATE TABLE fud_forum_upgrade_test_table (test_val INT)')) {
				upgrade_error('FATAL ERROR: your forum\'s PostgreSQL account does not have permissions to create new PostgreSQL tables.<br>Enable this functionality and restart the script.');
			}	
			if (!pg_query(__FUD_SQL_LNK__, 'ALTER TABLE fud_forum_upgrade_test_table ADD test_val2 INT')) {
				upgrade_error('FATAL ERROR: your forum\'s PostgreSQL account does not have permissions to run ALTER queries on existing PostgreSQL tables<br>Enable this functionality and restart the script.');
			}	
			if (!pg_query(__FUD_SQL_LNK__, 'DROP TABLE fud_forum_upgrade_test_table')) {
				upgrade_error('FATAL ERROR: your forum\'s PostgreSQL account does not have permissions to run DROP TABLE queries on existing PostgreSQL tables<br>Enable this functionality and restart the script.');
			}
		}
		

		function pgsql_rebuild_table($table, $newc=NULL, $oldc=NULL)
		{
			if (isset($GLOBALS['REBUILT_TABLES'][$table])) {
				return;
			} else {
				$GLOBALS['REBUILT_TABLES'][$table] = 1;	
			}
			$tmp_prefix = $GLOBALS['DBHOST_TBL_PREFIX'] . 'tmp_';

			pgsql_drop_indexes($GLOBALS['DBHOST_TBL_PREFIX'] . $table);
			if (pgsql_drop_if_exists($tmp_prefix . $table)) {
				pgsql_drop_sequences($tmp_prefix . $table);
			}
			if (q_singleval("SELECT c.relname FROM pg_catalog.pg_class c WHERE c.relkind='S' AND c.relname='".$GLOBALS['DBHOST_TBL_PREFIX'] . $table."_id_seq'")) {
				q('ALTER TABLE '.$GLOBALS['DBHOST_TBL_PREFIX'] . $table.'_id_seq RENAME TO '.$tmp_prefix.$table.'_id_seq');
			}
			
			$tmp = get_table_defenition($table);
			if (count($tmp)) {
				foreach ($tmp as $ent) { 
					if (trim($ent)) {
						if (strpos($ent, 'CREATE TABLE') !== false) {
							q('ALTER TABLE '.$GLOBALS['DBHOST_TBL_PREFIX'].$table.' RENAME TO '.$tmp_prefix.$table);

							q(str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $ent)); 
					
							$fls = $fld = pgsql_make_field_lst($GLOBALS['DBHOST_TBL_PREFIX'] . $table);
							if (is_null($newc) && $oldc) {
								$fls = preg_replace('!^|,'.$newc.',|$!', ',', $fls);
								$fls = $fld = trim(str_replace(',,', ',', $fls), ',');
							} else if (!is_null($newc) && !is_null($oldc)) {
								$fls = preg_replace('!(^|,)('.$newc.')(,|$)!', '\\1'.$oldc.'\\3', $fls);
							}

							q('INSERT INTO ' . $GLOBALS['DBHOST_TBL_PREFIX'] . $table . ' (' . $fld . ') SELECT ' . $fls . ' FROM ' . $tmp_prefix . $table);
					
							if (strpos($ent, 'SERIAL PRIMARY KEY')) {
								if (!($m = q_singleval('SELECT MAX(id) FROM '.$GLOBALS['DBHOST_TBL_PREFIX'] . $table))) {
									$m = 1;
								}
								q('SELECT setval(\''.$GLOBALS['DBHOST_TBL_PREFIX'].$table.'_id_seq\', '.$m.')');
							}
							q('DROP TABLE '.$tmp_prefix . $table);
							pgsql_drop_sequences($tmp_prefix . $table);
						} else {
							q(str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $ent));
						}	
					}
				}
			}
		}
		
		function pgsql_add_column($tbl, $name, $type, $vals, $is_null, $default, $auto_inc)
		{
			if ($auto_inc) {
				q('ALTER TABLE '.$tbl.' ADD COLUMN '.$name.' INT');
				q('CREATE SEQUENCE '.$tbl.'_'.$name.'_seq START 1');
				q('ALTER TABLE '.$tbl.' ALTER COLUMN '.$name.' SET DEFAULT nextval(\''.$tbl.'_'.$name.'_seq\'::text)');
				q('ALTER TABLE '.$tbl.' ALTER COLUMN '.$name.' SET NOT NULL');
				return;
			}
		
			if ($type != 'ENUM') {
				if ($vals == 'UNSIGNED') {
					$type = 'int8';
					$vals = '';
				}
				q('ALTER TABLE '.$tbl.' ADD COLUMN '.$name.' '.$type.' '.$vals);
			} else {
				$vals = explode(',', preg_replace('!\s+!', '', trim($vals, '()')));
				$max_l = 0;
				foreach ($vals as $v) {
					if ($max_l < strlen($v)) {
						$max_l = strlen($v);
					}
				}

				q('ALTER TABLE '.$tbl.' ADD COLUMN '.$name.' VARCHAR('.$max_l.')');

				$def = !is_null($default) ? $default : $vals[0];
				q('UPDATE '.$tbl.' SET '.$name.'='.$default.' WHERE '.$name.' NOT IN('.implode(',', $vals).') OR '.$name.' IS NULL');				

				$chk = $name . '=' . implode(' OR ' . $name . '=', $vals);

				/* check if we have an old constraint we need to delete */
				$tbl_oid = q_singleval("SELECT c.oid FROM pg_catalog.pg_class c WHERE pg_catalog.pg_table_is_visible(c.oid) AND c.relname='".$tbl."'");
				if (q_singleval("SELECT conname FROM pg_catalog.pg_constraint WHERE conrelid = ".$tbl_oid." AND contype = 'c' AND conname = '".$name."_cnt'")) {
					q('ALTER TABLE '.$tbl.' DROP CONSTRAINT '.$name.'_cnt');
				}

				q('ALTER TABLE '.$tbl.' ADD CONSTRAINT '.$name.'_cnt CHECK ('.$chk.')');
			}
			if (strlen($default)) {
				q('ALTER TABLE '.$tbl.' ALTER COLUMN '.$name.' SET DEFAULT '.$default);
				q('UPDATE '.$tbl.' SET '.$name.'='.$default);
			}
			if (!$is_null) {
				q('ALTER TABLE '.$tbl.' ALTER COLUMN '.$name.' SET NOT NULL');
			}
		}
	} else { 
		pgrade_error('NO VALID DATABASE TYPE SPECIFIED');
	}	
}

function fetch_cvs_id($data)
{
	if (($s = strpos($data, '$Id')) === false) {
		return;
	}
	if (($e = strpos($data, 'Exp $', $s)) === false) {
		return;
	}
	return substr($data, $s, ($e - $s));
}

function backupfile($source)
{
	copy($source, $GLOBALS['ERROR_PATH'] . '.backup/' . basename($source) . '_' . __time__);
}

function __mkdir($dir)
{
	if (@is_dir($dir)) {
		return 1;
	}
	$u = umask(($GLOBALS['FUD_OPT_2'] & 8388608 ? 0077 : 0));
	$ret = (mkdir($dir) || mkdir(dirname($dir)));
	umask($u);

	return $ret;
}

function htaccess_handler($web_root, $ht_pass)
{
	if (!fud_ini_get('allow_url_fopen')) {
		unlink($ht_pass);
	}
	if (version_compare(PHP_VERSION, "4.3.0", ">=")) {
		/* opening a connection to itself should not take more then 5 seconds */
		ini_set("default_socket_timeout", 5);
		if (@fopen($web_root . 'index.php', 'r') === FALSE) {
			unlink($ht_pass);
		}
	} else {
		$url = parse_url($web_root);
		if (!($fp = @fsockopen($url['host'], (isset($url['port']) ? $url['port'] : 80), $err, $err2, 5))) {
			unlink($ht_pass);
			return;
		}
		socket_set_timeout($fp, 5, 0);
		if (!@fwrite($fp, "GET {$url['path']}/index.php HTTP/1.0\r\nHost: {$url['host']}\r\n\r\n")) {
			unlink($ht_pass);
			return;
		}
		if (strpos(@fgets($fp, 1024), "200") === FALSE) {
			unlink($ht_pass);
			return;
		}
	}
}

function upgrade_decompress_archive($data_root, $web_root)
{
	if ($GLOBALS['no_mem_limit']) {
		$data = file_get_contents("./fudforum_archive");
	} else {
		$data = extract_archive(0);
	}

	$pos = 0;
	$u = umask(($GLOBALS['FUD_OPT_2'] & 8388608 ? 0177 : 0111));

	do  {
		$end = strpos($data, "\n", $pos+1);
		$meta_data = explode('//',  substr($data, $pos, ($end-$pos)));
		$pos = $end;

		if ($meta_data[1] == 'GLOBALS.php') {
			continue;
		}

		if (!strncmp($meta_data[3], 'install/forum_data', 18)) {
			$path = $data_root . substr($meta_data[3], 18);
		} else if (!strncmp($meta_data[3], 'install/www_root', 16)) {
			$path = $web_root . substr($meta_data[3], 16);
		} else {
			continue;
		}
		$path .= '/' . $meta_data[1];

		$path = str_replace('//', '/', $path);

		if (isset($meta_data[5])) {
			$file = substr($data, ($pos + 1), $meta_data[5]);
			if (md5($file) != $meta_data[4]) {
				upgrade_error('ERROR: file '.$meta_data[1].' was not read properly from archive');
			}
			if (@file_exists($path)) {
				if (md5_file($path) == $meta_data[4]) {
					// file did not change
					continue;
				}
				// Compare CVS Id to ensure we do not pointlessly replace files modified by the user
				if (($cvsid = fetch_cvs_id($file)) && $cvsid == fetch_cvs_id(file_get_contents($path))) {
					continue;
				}

				backupfile($path);
			}
		
			if (!($fp = @fopen($path, 'wb'))) {
				upgrade_error('Couldn\'t open "'.$path.'" for write');
			}	
			fwrite($fp, $file);
			fclose($fp);
		} else {
			if (!__mkdir(preg_replace('!/+$!', '', $path))) {
				upgrade_error('failed creating "'.$path.'" directory');
			}	
		}
	} while (($pos = strpos($data, "\n//", $pos)) !== false);
	umask($u);
}

function parse_todo_entry($line)
{
	if (!($line = trim($line))) {
		return;
	}
	if (!isset($GLOBALS['table_list'])) {
		$tmp = get_fud_table_list();
		foreach ($tmp as $tmp_val) {
			$GLOBALS['table_list'][$tmp_val] = $tmp_val;
		}
	}
	$table_list =& $GLOBALS['table_list'];

	$tmp = explode('::', $line);
	if (($c = count($tmp)) < 2) {
		echo 'Bad SQL change line "'.htmlspecialchars($line).'"<br>';
		flush();
		return;
	}
	
	$table_name = $GLOBALS['DBHOST_TBL_PREFIX'] . $tmp[0];
	$action = $tmp[1];

	switch ($action) {
		case 'ADD_TABLE_DB':
			if (isset($table_list[$table_name])) {
				break;
			}

			$tmp = get_table_defenition($tmp[0]);
			if (count($tmp)) {
				foreach ($tmp as $ent) { 
					if (trim($ent)) {
						q(str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $ent));
					}
				}
			} else {
				echo 'bad table defenition for '.$table_name.'<br>';
				flush();
			}
			break;

		case 'DROP_TABLE':
			if (!isset($table_list[$table_name])) {
				break;
			}
			
			q('DROP TABLE '.$table_name);
			if (__dbtype__ == 'pgsql') {
				pgsql_drop_sequences($table_name);
			}
			unset($table_list[$table_name]);
			break;

		case 'ADD_COLUMN': 
			 #	$tmp[2] -> column_name
			 #	$tmp[3] -> column_type
			 #	$tmp[4] -> column_value
			 #	$tmp[5] -> is_null
			 #	$tmp[6] -> default_value
			 #	$tmp[7] -> auto_increment
			 #	$tmp[8] -> trigger queries, separated by ;

			if (__dbtype__ == 'mysql') {
				if (mysql_row_exists($table_name, $tmp[2])) {
					break;
				}
				$query = 'ALTER TABLE '.$table_name.' ADD '.$tmp[2].' '.$tmp[3].' '.$tmp[4];
				if (empty($tmp[5])) {
					$query .= ' NOT NULL';
				}
				if (isset($tmp[6]) && strlen($tmp[6])) {
					$query .= ' DEFAULT '.$tmp[6];
				}
				if (isset($tmp[7]) && strlen($tmp[7])) {
					$query .= ' AUTO_INCREMENT';
				}

				q($query);
			} else if (__dbtype__ == 'pgsql') {
				if (q_singleval("SELECT a.attname AS Field FROM pg_class c, pg_attribute a WHERE c.relname = '".$table_name."' AND a.attnum>0 AND a.attrelid=c.oid AND a.attname=lower('".$tmp[2]."')")) {
					break;
				}
				@pgsql_add_column($GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0], $tmp[2], $tmp[3], $tmp[4], !empty($tmp[5]), $tmp[6], !empty($tmp[7]));
			}
			
			if (isset($tmp[8])) {
				$tmp = explode(';', $tmp[8]);
				foreach($tmp as $qy) {
					if (trim($qy)) {
						q(str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $qy));
					}
				}
			}
			break;

		case 'DROP_COLUMN': // $tmp[2] -> column_name
			if (__dbtype__ == 'mysql' && !mysql_row_exists($table_name, $tmp[2])) {
				break;
			}
			if (__dbtype__ == 'pgsql' && !q_singleval("SELECT a.attname AS Field FROM pg_class c, pg_attribute a WHERE c.relname = '".$table_name."' AND a.attnum>0 AND a.attrelid=c.oid AND a.attname='".$tmp[2]."'")) {
				break;
			}
			
			q('ALTER TABLE '.$table_name.' DROP '.$tmp[2]);	
			break;

		case 'ALTER_COLUMN':
			 #	$tmp[2] -> old_column_name
			 #	$tmp[3] -> column_name 
			 #	$tmp[4] -> column_type
			 #	$tmp[5] -> column_value
			 #	$tmp[6] -> is_null
			 #	$tmp[7] -> default_value
			 #	$tmp[8] -> auto_increment
			 #	$tmp[9] -> remove all data from table

			if (__dbtype__ == 'mysql') {
				if ($tmp[2] != $tmp[3] && !mysql_row_exists($table_name, $tmp[2])) {
					break;
				}
				$query = 'ALTER TABLE '.$table_name.' CHANGE '.$tmp[2].' '.$tmp[3].' '.$tmp[4].' '.$tmp[5];
				if (empty($tmp[6])) {
					$query .= ' NOT NULL';
				}
				if (isset($tmp[7]) && strlen($tmp[7])) {
					$query .= ' DEFAULT '.$tmp[7];
				}
				if (isset($tmp[8]) && strlen($tmp[8])) {
					$query .= ' AUTO_INCREMENT';
				}
				q($query);
			} else if (__dbtype__ == 'pgsql') {
				if ($tmp[2] != $tmp[3] && q_singleval("SELECT a.attname AS Field FROM pg_class c, pg_attribute a WHERE c.relname = '".$table_name."' AND a.attnum > 0 AND a.attrelid = c.oid AND a.attname=lower('".$tmp[3]."')")) {
					break;				
				}
				if (isset($tmp[8]) && q_singleval("SELECT c.relname FROM pg_catalog.pg_class c WHERE c.relkind='S' AND c.relname='".$GLOBALS['DBHOST_TBL_PREFIX'] . $tmp[0]."_".$tmp[3]."_seq'")) {
					break;
				}

				if ($tmp[2] == $tmp[3]) {
					q('ALTER TABLE '.$GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0].' RENAME COLUMN '.$tmp[2].' TO tmp_'.$tmp[3]);
					$tmp[2] = 'tmp_' . $tmp[2]; 
				}
				if (!empty($tmp[9])) {
					q('DELETE FROM '.$GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0]);
				}
				pgsql_add_column($GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0], $tmp[3], $tmp[4], $tmp[5], !empty($tmp[6]), (isset($tmp[7]) ? $tmp[7] : ''), isset($tmp[8]));
				if ($tmp[4] == 'ENUM') {
					q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0].' SET '.$tmp[3].'='.$tmp[2].' WHERE '.$tmp[2].' IN('.implode(',', explode(',', preg_replace('!\s+!', '', trim($tmp[5], '()')))).') AND '.$tmp[2].' IS NOT NULL');
				} else {
					q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0].' SET '.$tmp[3].'='.$tmp[2]);
				}
				q('ALTER TABLE '.$GLOBALS['DBHOST_TBL_PREFIX'].$tmp[0].' DROP '.$tmp[2]);
			}
			break;

		case 'ADD_INDEX':
			 #	$tmp[2] -> index_type
			 #	$tmp[3] -> index_defenition
			 #	$tmp[4] -> index_name (pgsql only)
			 #	$tmp[5] -> name of a function to execute 

			if (isset($tmp[5])) {
				call_user_func(trim($tmp[5]));
			}

			if (__dbtype__ == 'mysql') {
			 	if (match_mysql_index($table_name, $tmp[3], $tmp[2])) {
			 		break;
			 	}
			 	q('ALTER TABLE '.$table_name.' ADD '.$tmp[2].'('.$tmp[3].')');
			} else if (__dbtype__ == 'pgsql') {
				$index_name = str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $tmp[4]);
			
				if (q_singleval("SELECT * FROM pg_stat_user_indexes WHERE relname='".$table_name."' AND indexrelname='".$index_name."'")) {
					break;
				}
				if ($tmp[2] == 'INDEX') {
					q('CREATE INDEX '.$index_name.' ON '.$table_name.' ('.$tmp[3].')');
				} else {
					q('CREATE UNIQUE INDEX '.$index_name.' ON '.$table_name.' ('.$tmp[3].')');	
				}
			}
			break;

		case 'DROP_INDEX':
			 #	$tmp[2] -> index_type
			 #	$tmp[3] -> index_defenition
			 #	$tmp[4] -> index_name (pgsql only)

			if (__dbtype__ == 'mysql') {
				if (!($tmp[4] = match_mysql_index($table_name, $tmp[3], $tmp[2]))) {
					break;
				}
			 	q('ALTER TABLE '.$table_name.' DROP INDEX '.$tmp[4]);
			} else if (__dbtype__ == 'pgsql') {
				$index_name = str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $tmp[4]);
				if (!q_singleval("SELECT * FROM pg_stat_user_indexes WHERE relname='".$table_name."' AND indexrelname='".$index_name."'")) {
					break;
				}
				q('DROP INDEX '.$index_name);
			}
			break;

		case 'RENAME_INDEX':
			# only applicable to PostgreSQL
			#
			# $tmp[2] -> old index_name
			# $tmp[3] -> new index_name 
			if (__dbtype__ == 'pgsql') {
				$old_name = $GLOBALS['DBHOST_TBL_PREFIX'].$tmp[2];
				if (!q_singleval("SELECT * FROM pg_stat_user_indexes WHERE relname='".$table_name."' AND indexrelname='".$old_name."'")) {
					break;
				}
				$new_name = $GLOBALS['DBHOST_TBL_PREFIX'].$tmp[3];
				if (!q_singleval("SELECT * FROM pg_stat_user_indexes WHERE relname='".$table_name."' AND indexrelname='".$new_name."'")) {
					q("UPDATE pg_class SET relname='".$new_name."' WHERE relname='".$old_name."' AND relkind='i'");
				} else {
					q("DROP INDEX ".$old_name);
				}
			}
			break;

		case 'QUERY':
			 #      $tmp[2] -> query
		  	 #      $tmp[3] -> version

			q(str_replace('{SQL_TABLE_PREFIX}', $GLOBALS['DBHOST_TBL_PREFIX'], $tmp[2]));
			break;	
	}	
}

function cache_avatar_image($url, $user_id)
{
	$ext = array(1=>'gif', 2=>'jpg', 3=>'png', 4=>'swf');
	if (!isset($GLOBALS['AVATAR_ALLOW_SWF'])) {
		$GLOBALS['AVATAR_ALLOW_SWF'] = 'N';
	}
	if (!isset($GLOBALS['CUSTOM_AVATAR_MAX_DIM'])) {
		$max_w = $max_y = 64;
	} else {
		list($max_w, $max_y) = explode('x', $GLOBALS['CUSTOM_AVATAR_MAX_DIM']);
	}

	if (!($img_info = @getimagesize($url)) || $img_info[0] > $max_w || $img_info[1] > $max_y || $img_info[2] > ($GLOBALS['AVATAR_ALLOW_SWF']!='Y'?3:4)) {
		return;
	}
	if (!($img_data = file_get_contents($url)) || strlen($img_data) > $GLOBALS['CUSTOM_AVATAR_MAX_SIZE']) {
		return;
	}
	if (!($fp = fopen($GLOBALS['WWW_ROOT_DISK'] . 'images/custom_avatars/' . $user_id . '.' . $ext[$img_info[2]], 'wb'))) {
		return;
	}
	fwrite($fp, $img_data);
	fclose($fp);

	return '<img src="'.$GLOBALS['WWW_ROOT'].'images/custom_avatars/'.$user_id . '.' . $ext[$img_info[2]].'" '.$img_info[3].' />';
}

function syncronize_theme_dir($theme, $dir, $src_thm)
{
	$path = $GLOBALS['DATA_DIR'].'thm/'.$theme.'/'.$dir;
	$spath = $GLOBALS['DATA_DIR'].'thm/'.$src_thm.'/'.$dir;

	if (!__mkdir($path)) {
		upgrade_error('Directory "'.$path.'" does not exist, and the upgrade script failed to create it.');	
	}
	if (!($d = opendir($spath))) {
		upgrade_error('Failed to open "'.$spath.'"');
	}
	readdir($d); readdir($d);
	$path .= '/';
	$spath .= '/';
	while ($f = readdir($d)) {
		if (@is_dir($spath . $f) && !is_link($spath . $f)) {
			syncronize_theme_dir($theme, $dir . '/' . $f, $src_thm);
			continue;
		}	
		if (!@file_exists($path . $f) && !copy($spath . $f, $path . $f)) {
			upgrade_error('Failed to copy "'.$spath . $f.'" to "'.$path . $f.'", check permissions then run this scripts again.');			
		} else {
			// Skip images, we do not need to replace them.
			if (preg_match('!/images/.*\.gif!', $path)) {
				continue;
			}
			if (md5_file($path . $f) == md5_file($spath . $f) || fetch_cvs_id(file_get_contents($path . $f)) == fetch_cvs_id(file_get_contents($spath . $f))) {
				continue;
			}

			backupfile($path . $f);
			copy($spath . $f, $path . $f);
		}
			
	}
	closedir($d);
}

function syncronize_theme($theme)
{
	if ($theme == 'path_info' || @file_exists($GLOBALS['DATA_DIR'].'thm/'.$theme.'/.path_info')) {
		$src_thm = 'path_info';
	} else {
		$src_thm = 'default';
	}

	syncronize_theme_dir($theme, 'tmpl', $src_thm);
	syncronize_theme_dir($theme, 'i18n', $src_thm);
}

function clean_read_table()
{
	$tbl &= $GLOBALS['DBHOST_TBL_PREFIX'];

	$r = q('SELECT thread_id, user_id, count(*) AS cnt FROM '.$tbl.'read GROUP BY thread_id,user_id ORDER BY cnt DESC');
	while ($o = db_rowobj($r)) {
		if ($o->cnt == "1") {
			break;
		}
		q('DELETE FROM '.$tbl.'read WHERE thread_id='.$o->thread_id.' AND user_id='.$o->user_id.' LIMIT '.($o->cnt - 1));
	}
	unset($r);
}

function clean_forum_read_table()
{
	$tbl &= $GLOBALS['DBHOST_TBL_PREFIX'];

	$r = q('SELECT forum_id, user_id, count(*) AS cnt FROM '.$tbl.'forum_read GROUP BY forum_id, user_id ORDER BY cnt DESC');
	while ($o = db_rowobj($r)) {
		if ($o->cnt == "1") {
			break;
		}
		q('DELETE FROM '.$tbl.'forum_read WHERE forum_id='.$o->forum_id.' AND user_id='.$o->user_id.' LIMIT '.($o->cnt - 1));
	}
	unset($r);
}

function extract_archive($memory_limit)
{
	$fsize = filesize(__FILE__);
	$l = strlen("2105111608_\\ARCH_START_HERE");

	if ($fsize < 200000 && !@file_exists("./fudforum_archive")) {
		upgrade_error('The upgrade script is missing the data archive, cannot run.');
	} else if ($fsize > 200000 || !$memory_limit) {
		if ($memory_limit) {
			if (!($fp = fopen("./fudforum_archive", "wb"))) {
				$err = 'Please make sure that the intaller has permission to write to the current directory ('.getcwd().')';
				if (!SAFE_MODE) {
					$err .= '<br/ >or create a "fudforum_archive" file inside the current directory and make it writable to the webserver.';
				}
				upgrade_error($err);
			}
			$main = '';

			$fp2 = fopen(__FILE__, 'rb');
			while (($line = fgets($fp2, 10000))) {
				$main .= $line;
				if (!strncmp($line, "2105111608_\\ARCH_START_HERE", $l)) {
					break;
				}
			}
			$p = strlen($main);
			$checksum = fread($fp2, 32);
			
			$tmp = fread($fp2, 20000);
			if (($zl = strpos($tmp, 'RAW_PHP_OPEN_TAG')) === FALSE && !extension_loaded('zlib')) {
				upgrade_error('The upgrade script uses zlib compression, however your PHP was not compiled with zlib support or the zlib extension is not loaded. In order to get the upgrade script to work you\'ll need to enable the zlib extension or download a non compressed upgrade script from <a href="http://fud.prohost.org/forum/">http://fud.prohost.org/forum/</a>');
			}
			fseek($fp2, (ftell($fp2) - 20000), SEEK_SET);
			if ($zl) {
				while (($tmp = fgets($fp2, 20000))) {
					fwrite($fp, str_replace('RAW_PHP_OPEN_TAG', '<?', $tmp));
				}
			} else {
				$data_len = (int) fread($fp2, 10);
				fwrite($fp, gzuncompress(str_replace('PHP_OPEN_TAG', '<?', fread($fp2, $data_len)), $data_len));
			}
			fclose($fp);

			if (md5_file("./fudforum_archive") != $checksum) {
				upgrade_error('Archive did not pass checksum test, CORRUPT ARCHIVE!<br>If you\'ve encountered this error it means that you\'ve:<br>&nbsp;&nbsp;&nbsp;&nbsp;downloaded a corrupt archive<br>&nbsp;&nbsp;&nbsp;&nbsp;uploaded the archive in ASCII and not BINARY mode<br>&nbsp;&nbsp;&nbsp;&nbsp;your FTP Server/Decompression software/Operating System added un-needed cartrige return (\'\r\') characters to the archive, resulting in archive corruption.');	
			}

			/* move the data from upgrade script. */
			$fp2 = fopen(__FILE__, "wb");
			fwrite($fp2, $main);
			fclose($fp2);
			unset($main, $tmp);
		} else {
			$data = file_get_contents(__FILE__);
			$p = strpos($data, "2105111608_\\ARCH_START_HERE") + $l + 1;
			if (($zl = strpos($data, 'RAW_PHP_OPEN_TAG', $p)) === FALSE && !extension_loaded('zlib')) {
				upgrade_error('The upgrade script uses zlib compression, however your PHP was not compiled with zlib support or the zlib extension is not loaded. In order to get the upgrade script to work you\'ll need to enable the zlib extension or download a non compressed upgrade script from <a href="http://fud.prohost.org/forum/">http://fud.prohost.org/forum/</a>');
			}
			$checksum = substr($data, $p, 32);
			$p += 32;
			if (!$zl) {
				$data_len = (int) substr($data, $p, 10);
				$p += 10;
				$data = gzuncompress(str_replace('PHP_OPEN_TAG', '<?', substr($data, $p)), $data_len);
			} else {
				$data = str_replace('RAW_PHP_OPEN_TAG', '<?', substr($data, $p));
			}
			if (md5($data) != $checksum) {
				upgrade_error('Archive did not pass checksum test, CORRUPT ARCHIVE!<br>If you\'ve encountered this error it means that you\'ve:<br>&nbsp;&nbsp;&nbsp;&nbsp;downloaded a corrupt archive<br>&nbsp;&nbsp;&nbsp;&nbsp;uploaded the archive in ASCII and not BINARY mode<br>&nbsp;&nbsp;&nbsp;&nbsp;your FTP Server/Decompression software/Operating System added un-needed cartrige return (\'\r\') characters to the archive, resulting in archive corruption.');
			}
			return $data;
		}
	}	
}

	error_reporting(E_ALL);
	if (ini_get("memory_limit") && !@ini_set('memory_limit', '64M')) {
		$no_mem_limit = 1;
	} else {
		$no_mem_limit = 0;
	}
	
	ignore_user_abort(true);
	set_magic_quotes_runtime(0);
	@set_time_limit(600);

	if (ini_get('error_log')) {
		@ini_set('error_log', '');
	}
	if (!fud_ini_get('display_errors')) {
		ini_set('display_errors', 1);
	}
	if (!fud_ini_get('track_errors')) {
		ini_set('track_errors', 1);
	}

	// php version check
	if (!version_compare(PHP_VERSION, '4.2.0', '>=')) {
		echo '<html><body bgcolor="white">';
		upgrade_error('The upgrade script requires that you have php version 4.2.0 or higher');
	}

	// Determine SafeMode limitations
	define('SAFE_MODE', fud_ini_get('safe_mode'));
	if (SAFE_MODE && basename(__FILE__) != 'upgrade_safe.php') {
		if ($no_mem_limit) {
			extract_archive($no_mem_limit);
		}
		$c = getcwd();
		if (copy($c . '/upgrade.php', $c . '/upgrade_safe.php')) {
			header('Location: '.dirname($_SERVER['SCRIPT_NAME']).'/upgrade_safe.php');
		}
		exit;
	}

	echo '<html><body bgcolor="white">';
	// we need to verify that GLOBALS.php exists in current directory & that we can open it
	$gpath = getcwd() . '/GLOBALS.php';
	if (!@file_exists($gpath)) {
		upgrade_error('Unable to find GLOBALS.php inside the current ('.getcwd().') directory. Please place the upgrade ('.basename(__FILE__).') script inside main web directory of your forum');
	} else if (!@is_writable($gpath)) {
		upgrade_error('No permission to read/write to '.getcwd().' /GLOBALS.php. Please make sure this script had write access to all of the forum files.');
	}

	if (preg_match('!win!i', PHP_OS)) {
		preg_match('!include_once "(.*)"; !', file_get_contents($gpath), $m);
		$gpath = $m[1];
	}

	preg_match_all('!(\$([A-Z_\s]+)=\s*([^\s]*)+;)!', file_get_contents($gpath), $m);
	eval(implode("", $m[1]));

	/* this check is here to ensure the data from GLOBALS.php was parsed correctly */
	if (!isset($GLOBALS['COOKIE_NAME'])) {
		upgrade_error('Failed to parse GLOBALS.php at "'.$gpath.'" correctly');	
	}

	/* database variable conversion */
	if (!isset($GLOBALS['DBHOST_TBL_PREFIX'])) {
		$DBHOST_TBL_PREFIX 	= $MYSQL_TBL_PREFIX;
		$DBHOST 		= $MYSQL_SERVER;
		$DBHOST_USER 		= $MYSQL_LOGIN;
		$DBHOST_PASSWORD 	= $MYSQL_PASSWORD;
		$DBHOST_DBNAME 		= $MYSQL_DB;
		define('__dbtype__', 'mysql');
	}

	if (!isset($GLOBALS['DATA_DIR'])) {
		$GLOBALS['DATA_DIR'] = realpath($GLOBALS['INCLUDE'] . '../') . '/';
		$no_data_dir = 1;
	}

	/* Determine Database Type */
	if (!defined('__dbtype__')) {
		if (strpos(@file_get_contents($GLOBALS['DATA_DIR'] . 'include/theme/default/db.inc'), 'pg_connect') === false) {
			define('__dbtype__', 'mysql');
		} else {
			define('__dbtype__', 'pgsql');
		}
	}

	/* include appropriate database functions */
	init_sql_func();

	/* only allow the admin user to upgrade the forum */
	$auth = 0;
	if (count($_POST)) {
		if (get_magic_quotes_gpc()) {
			$_POST['login'] = stripslashes($_POST['login']);
			$_POST['passwd'] = stripslashes($_POST['passwd']);
		}

		if (mysql_row_exists($GLOBALS['DBHOST_TBL_PREFIX'] . 'users', 'is_mod')) {
			$auth = q_singleval("SELECT id FROM ".$DBHOST_TBL_PREFIX."users WHERE login='".addslashes($_POST['login'])."' AND passwd='".md5($_POST['passwd'])."' AND is_mod='A'");
		} else {
			$auth = q_singleval("SELECT id FROM ".$DBHOST_TBL_PREFIX."users WHERE login='".addslashes($_POST['login'])."' AND passwd='".md5($_POST['passwd'])."' AND (users_opt & 1048576) > 0");
		}
	}
	if (!$auth) {
		if ($no_mem_limit && !@is_writeable(__FILE__)) {
?>
<html>
<body bgcolor="white">
You need to chmod the <?php echo __FILE__; ?> file 666 (-rw-rw-rw-), so that the upgrade script can modify itself.
</body>
</html>
<?php
			exit;
		}
		if ($no_mem_limit) {
			extract_archive($no_mem_limit);
		}
?>		
<div align="center">
<form name="upgrade" action="<?php echo basename(__FILE__); ?>" method="post">
<table cellspacing=1 cellpadding=3 border=0 style="border: 1px dashed #1B7CAD;">
<tr bgcolor="#dee2e6">
	<th colspan=2>Please enter the login &amp; password of the administration account.</th>
</tr>
<tr bgcolor="#eeeeee">
	<td><b>Login:</b></td>
	<td><input type="text" name="login" value=""></td>
</tr>
<tr bgcolor="#eeeeee">
	<td><b>Password:</b></td>
	<td><input type="password" name="passwd" value=""></td>
</tr>
<tr bgcolor="#dee2e6">
	<td align="right" colspan=2><input type="submit" name="submit" value="Authenticate"></td>
</tr>
</table>
</form>
</div>
</body>
</html>
<?php
		exit;
	}

	if (!isset($GLOBALS['FUD_OPT_2']) && (!isset($GLOBALS['FILE_LOCK']) || $GLOBALS['FILE_LOCK'] == 'Y')) {
		$GLOBALS['FUD_OPT_2'] = 8388608;
	}

	// Determine open_basedir limitations
	define('open_basedir', ini_get('open_basedir'));
	if (open_basedir) {
		if (!preg_match('!win!i', PHP_OS)) { 
			$dirs = explode(':', open_basedir);
		} else {
			$dirs = explode(';', open_basedir);
		}
		$safe = 1;
		foreach ($dirs as $d) {
			if (!strncasecmp($GLOBALS['DATA_DIR'], $d, strlen($d))) {
			        $safe = 0;
			        break;
			}
		}
		if ($safe) {
			upgrade_error('Your php\'s open_basedir limitation ('.open_basedir.') will prevent the upgrade script from writing to ('.$GLOBALS['DATA_DIR'].'). Please make sure that access to ('.$GLOBALS['DATA_DIR'].') is permitted.');
		}
		if ($GLOBALS['DATA_DIR'] != $GLOBALS['WWW_ROOT_DISK']) {
			$safe = 1;
			foreach ($dirs as $d) {
				if (!strncasecmp($GLOBALS['WWW_ROOT_DISK'], $d, strlen($d))) {
				        $safe = 0;
					break;
				}
			}
			if ($safe) {
				upgrade_error('Your php\'s open_basedir limitation ('.open_basedir.') will prevent the upgrade script from writing to ('.$GLOBALS['WWW_ROOT_DISK'].'). Please make sure that access to ('.$GLOBALS['WWW_ROOT_DISK'].') is permitted.');
			}
		}
	}

	/* determine if this upgrade script was previously ran */
	if (@file_exists($GLOBALS['ERROR_PATH'] . 'UPGRADE_STATUS') && (int) trim(file_get_contents($ERROR_PATH . 'UPGRADE_STATUS')) >= $__UPGRADE_SCRIPT_VERSION) {
		upgrade_error('THIS UPGRADE SCRIPT HAS ALREADY BEEN RUN, IF YOU WISH TO RUN IT AGAIN USE THE FILE MANAGER TO REMOVE THE "'.$GLOBALS['ERROR_PATH'].'UPGRADE_STATUS" FILE.');
	}

	/* check that we can do all needed database operations */
	show_debug_message('Check if SQL permissions to perform the upgrade are avaliable');
	check_sql_perms();

	show_debug_message('Disable the forum');
	if (isset($GLOBALS['FUD_OPT_1'])) {
		change_global_settings(array('FUD_OPT_1' => ($GLOBALS['FUD_OPT_1'] &~ 1)));
	} else {
		change_global_settings(array('FORUM_ENABLED' => 'N'));
	}
	show_debug_message('Forum is now disabled');
	
	/* Upgrade Files */
	show_debug_message('Beginning the file upgrade process');
	__mkdir($GLOBALS['ERROR_PATH'] . '.backup');
	define('__time__', time());
	show_debug_message('Begining to decompress the archive');
	upgrade_decompress_archive($GLOBALS['DATA_DIR'], $GLOBALS['WWW_ROOT_DISK']);
	/* determine if this host can support .htaccess directives */
	htaccess_handler($GLOBALS['WWW_ROOT'], $GLOBALS['WWW_ROOT_DISK'] . '.htaccess');
	show_debug_message('Finished decompressing the archive');
	show_debug_message('File Upgrade Complete');
	show_debug_message('<font color="#ff0000">Any changed files were backed up to: "'.$GLOBALS['ERROR_PATH'].'.backup/"</font><br>');

	/* Update SQL */
	show_debug_message('Beginning SQL Upgrades');
	$db_tables = array_flip(get_fud_table_list());

	$files = glob("{$GLOBALS['DATA_DIR']}/sql/*.tbl");
	foreach ($files as $v) {
		$tbl = get_stbl_from_file($v);
		if (!isset($db_tables[$tbl['name']])) {
			/* add new table */
			echo "New table {$tbl['name']}\n";
		} else {
			/* special hack for thread_view table that is different for MySQL installs */
			if ($tbl['name'] === "{$DBHOST_TBL_PREFIX}thread_view" && __dbtype__ == 'mysql') {
				$tbl['flds']['forum_id']['primary'] = $tbl['flds']['page']['primary'] = $tbl['flds']['pos']['primary'] = 1;
				$tbl['flds']['pos']['auto'] = 1;
				$tbl['flds']['pos']['default'] = null;
			}

			/* handle fields */
			$db_col = get_fud_col_list($tbl['name']);
			foreach ($tbl['flds'] as $k => $v2) {
				if (!isset($db_col[$k])) {
					/* new field */
					echo "New field {$k} inside {$tbl['name']}\n";
				} else if (array_diff_assoc($db_col[$k], $v2)) {
					/* field definition has changed */
					echo "Field defintion of {$k} inside {$tbl['name']} ({$v}) changed\n";
					if (__dbtype__ == 'mysql') {
						$tmp = "ALTER TABLE {$tbl['name']} CHANGE {$k} {$k} {$v2['type']} ";
						if ($v2['not_null']) {
							$tmp .= " NOT NULL ";
						}
						if (!is_null($v2['default'])) {
							$tmp .= " DEFAULT " . ((strpos($v2['type'], 'INT') === false) ? "'{$v2['default']}'" : $v2['default']);
						}
						if ($v2['auto']) {
							$tmp .= " AUTO_INCREMENT ";
						}
						if ($v2['primary'] && !$db_col[$k]['primary']) {
							$tmp .= " PRIMARY KEY ";
						}
						q($tmp);
					} else {
					
					}
				}
			}

			/* handle indexes */
			$idx_l = get_fud_idx_list($tbl['name']);
			foreach ($tbl['index'] as $k => $v) {
				/* possibly new index */
				if (!isset($idx_l[$k])) {
					add_index($tbl['name'], $k, $v['unique'], $v['cols']);
				} else {
					unset($idx_l[$k]);
				}
			}

			/* remove old un-unsed indexes */
			foreach ($idx_l as $k => $v) {
				drop_index($tbl['name'], $k, $db);
			}

			unset($db_tables[$tbl['name']]);
		}
	}
	show_debug_message('SQL Upgrades Complete');

	/* convert avatars 
	 * At one point we linked to remote avatars and the URL was stored inside avatar_loc
	 * then in 2.5.0 we've began using avatar_loc to store cached <img src>
	*/
	if (!isset($GLOBALS['ENABLE_THREAD_RATING']) && !isset($GLOBALS['FUD_OPT_1'])) { /* < 2.5.0 */
		show_debug_message('Creating Avatar Cache');

		if (q_singleval('select count(*) FROM '.$DBHOST_TBL_PREFIX.'users WHERE avatar_loc LIKE \'http://%\'')) { /* < 2.1.3 */
			$c = q('SELECT id, avatar_loc FROM '.$DBHOST_TBL_PREFIX.'users WHERE avatar_loc IS NOT NULL AND avatar_loc!=\'\'');
			while ($r = db_rowarr($c)) {
				$path = cache_avatar_image($r[1], $r[0]);
				if ($path) {
					q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET avatar_loc=\''.addslashes($path).'\' WHERE id='.$r[0]);
				} else {
					q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET avatar_loc=NULL, users_opt=((users_opt & ~ 8388608) & ~ 16777216) | 4194304 WHERE id='.$r[0]);
				}
			}
			unset($c);
		}
		$ext = array(1=>'gif', 2=>'jpg', 3=>'png', 4=>'swf');
		$c = q('SELECT u.id, u.avatar, a.img, u.users_opt FROM '.$DBHOST_TBL_PREFIX.'users u LEFT JOIN '.$DBHOST_TBL_PREFIX.'avatar a ON u.avatar=a.id WHERE ((u.users_opt & 4194304)=0 AND (u.avatar_loc IS NULL OR u.avatar_loc=\'\')) OR u.avatar>0');
		while ($r = db_rowarr($c)) {
			if ($r[1]) { /* built-in avatar */
				if (!isset($av_cache[$r[1]])) {
					$im = getimagesize($GLOBALS['WWW_ROOT_DISK'] . 'images/avatars/' . $r[2]);
					$av_cache[$r[1]] = '<img src="'.$GLOBALS['WWW_ROOT'].'images/avatars/'. $r[2] .'" '.$im[3].' />';
				}
				$path = $av_cache[$r[1]];
				$avatar_approved = 8388608;
			} else if (($im = getimagesize($GLOBALS['WWW_ROOT_DISK'] . 'images/custom_avatars/' . $r[0]))) { /* custom avatar */
				$path = '<img src="'.$GLOBALS['WWW_ROOT'].'images/custom_avatars/'. $r[0] . '.' . $ext[$im[2]].'" '.$im[3] .' />';
				rename($GLOBALS['WWW_ROOT_DISK'] . 'images/custom_avatars/' . $r[0], $GLOBALS['WWW_ROOT_DISK'] . 'images/custom_avatars/' . $r[0] . '.' . $ext[$im[2]]);
				$avatar_approved = $r[3] & 8388608;
			}
			if ($path) {
				q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET avatar_loc=\''.addslashes($path).'\', users_opt=(users_opt & ~ 8388608) | '.$avatar_approved.' WHERE id='.$r[0]);
			} else {
				q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET avatar_loc=NULL, users_opt=((users_opt & ~ 8388608) & ~ 16777216) WHERE id='.$r[0]);
			}
		}
		unset($c);

		/* Add data into pdest field of pmsg table */
		if (q_singleval('SELECT count(*) FROM '.$DBHOST_TBL_PREFIX.'pmsg WHERE pdest>0')) {
			show_debug_message('Populating pdest field for private messages');
			$r = q("SELECT to_list, id FROM ".$DBHOST_TBL_PREFIX."pmsg WHERE folder_id='SENT' AND duser_id=ouser_id");
			while (list($l, $id) = db_rowarr($r)) {
				if (!($uname = strtok($l, ';'))) {
					continue;
				}
				if (!($uid = q_singleval("select id from ".$DBHOST_TBL_PREFIX."users where login='".addslashes($uname)."'"))) {
					continue;
				}
		
				q('UPDATE '.$DBHOST_TBL_PREFIX.'pmsg SET pdest='.$uid.' WHERE id='.$id);
			}
			unset($r);
		}
	}

	if (!q_singleval("SELECT id FROM ".$DBHOST_TBL_PREFIX."themes WHERE (theme_opt & 3) > 0 LIMIT 1")) {
		show_debug_message('Setting default theme');
		$pspell_lang = @trim(file_get_contents($GLOBALS['DATA_DIR'] . '/thm/default/i18n/' . $GLOBALS['LANGUAGE'] . '/pspell_lang'));
		if (!q_singleval("SELECT id FROM ".$DBHOST_TBL_PREFIX."themes WHERE id=1")) {
			q("INSERT INTO ".$DBHOST_TBL_PREFIX."themes (id, name, theme, lang, locale, theme_opt, pspell_lang) VALUES(1, 'default', 'default', '".$GLOBALS['LANGUAGE']."', '".$GLOBALS['LOCALE']."', 3, '".$pspell_lang."')");
		} else {
			q("UPDATE ".$DBHOST_TBL_PREFIX."themes SET name='default', theme='default', lang='{$GLOBALS['LANGUAGE']}', locale='{$GLOBALS['LOCALE']}', theme_opt=3, pspell_lang='{$pspell_lang}'");
		}
		q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET theme=1');
	}

	/* theme fixer upper for the admin users lacking a proper theme
	 * this is essential to ensure the admin user can login
	 */
	$df_theme = q_singleval("SELECT id FROM ".$DBHOST_TBL_PREFIX."themes WHERE theme_opt & 3) > 0 LIMIT 1");
	$c = q('SELECT u.id FROM '.$DBHOST_TBL_PREFIX.'users u LEFT JOIN '.$DBHOST_TBL_PREFIX.'themes t ON t.id=u.theme WHERE (u.users_opt & 1048576) > 0 AND t.id IS NULL');
	while ($r = db_rowarr($c)) {
		$bt[] = $r[0];
	}
	unset($c);
	if (isset($bt)) {
		q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET theme='.$df_theme.' WHERE id IN('.implode(',', $bt).')');
	}

	if (!isset($GLOBALS['FUD_OPT_1'])) {
		/* encode user alias according to new format */
		if (!isset($GLOBALS['USE_ALIASES'])) {
			show_debug_message('Updating aliases');
			$c = q('SELECT id, alias FROM '.$DBHOST_TBL_PREFIX.'users');
			while ($r = db_rowarr($c)) {
				$alias = htmlspecialchars((strlen($r[1]) > $GLOBALS['MAX_LOGIN_SHOW'] ? substr($r[1], 0, $GLOBALS['MAX_LOGIN_SHOW']) : $r[1]));
				if ($alias != $r[1]) {
					q('UPDATE '.$DBHOST_TBL_PREFIX.'users SET alias=\''.addslashes($alias).'\' WHERE id='.$r[0]);
				}
			}
			unset($c);
		}

		/* store file attachment sizes inside db */
		if (q_singleval('select count(*) from '.$DBHOST_TBL_PREFIX.'attach WHERE fsize=0')) {
			show_debug_message('Updating file sizes of attachments');
			$c = q('SELECT id, location FROM '.$DBHOST_TBL_PREFIX.'attach WHERE fsize=0');
			while ($r = db_rowarr($c)) {
				q('UPDATE '.$DBHOST_TBL_PREFIX.'attach SET fsize='.(int)@filesize($r[1]).' WHERE id='.$r[0]);
			}
			unset($c);
		}

		/* since 2.5.0 for each poll tracking entry we store the id for the voter */
		if (!isset($GLOBALS['ENABLE_THREAD_RATING'])) { /* < 2.5.0 */
			$c = q('SELECT id, poll_id, count FROM '.$DBHOST_TBL_PREFIX.'poll_opt WHERE count>0');
			while ($r = db_rowarr($c)) {
				q('UPDATE '.$DBHOST_TBL_PREFIX.'poll_opt_track SET poll_opt='.$r[0].' WHERE poll_id='.$r[1].' AND poll_opt=0 LIMIT '.$r[2]);
			}
			unset($c);
		}

		if (!q_singleval('SELECT id FROM '.$DBHOST_TBL_PREFIX.'users WHERE id=1 AND email=\'dev@null\' AND (users_opt & 1048576)=0')) {
			show_debug_message('Reserving id for anon users');
			if (($u = (array) @db_rowobj(q('SELECT * FROM '.$DBHOST_TBL_PREFIX.'users WHERE id=1'))) && !isset($u[0])) {
				q('DELETE FROM '.$DBHOST_TBL_PREFIX.'users WHERE id=1');
				unset($u['id']);
				q("INSERT INTO ".$DBHOST_TBL_PREFIX."users (".implode(',', array_keys($u)).") VALUES('".implode("',", $u)."')");
				$new_id = q_singleval('SELECT id FROM '.$DBHOST_TBL_PREFIX.'users WHERE login=\''.addslashes($u['login']).'\'');
		
				$tbl_list = array('action_log', 'buddy', 'custom_tags', 'forum_notify', 'forum_read', 'group_cache', 'group_members', 'mod', 'msg_report', 'poll_opt_track', 'read', 'ses', 'thread_notify', 'thread_rate_track', 'user_ignore');
				foreach ($tbl_list as $t) {
					q('UPDATE '.$DBHOST_TBL_PREFIX.$t.' SET user_id='.$new_id.' WHERE user_id=1');
				}
				q('UPDATE '.$DBHOST_TBL_PREFIX.'pmsg SET ouser_id='.$new_id.' WHERE ouser_id=1');
				q('UPDATE '.$DBHOST_TBL_PREFIX.'pmsg SET duser_id='.$new_id.' WHERE duser_id=1');
				q('UPDATE '.$DBHOST_TBL_PREFIX.'poll SET owner='.$new_id.' WHERE owner=1');
				q('UPDATE '.$DBHOST_TBL_PREFIX.'poll_opt_track SET user_id='.$new_id.' WHERE user_id=1');
				q('UPDATE '.$DBHOST_TBL_PREFIX.'attach SET owner='.$new_id.' WHERE owner=1');
				q('UPDATE '.$DBHOST_TBL_PREFIX.'msg SET poster_id='.$new_id.' WHERE poster_id=1');
				q('UPDATE '.$DBHOST_TBL_PREFIX.'msg SET updated_by='.$new_id.' WHERE updated_by=1');
			}
			q("INSERT INTO ".$DBHOST_TBL_PREFIX."users (id, login, alias, time_zone, theme, email, passwd, name, users_opt) VALUES(1, 'Anonymous Coward', 'Anonymous Coward', 'America/Montreal', 1, 'dev@null', '1', 'Anonymous Coward', 4488117)");
		}
	}

	if (!q_singleval('SELECT * FROM '.$DBHOST_TBL_PREFIX.'stats_cache')) {
		q('INSERT INTO '.$DBHOST_TBL_PREFIX.'stats_cache VALUES(0,0,0,0,0,0,0)');
	}

	show_debug_message('Adding GLOBAL Variables');
	$gl = read_help();
	/* handle forums that do not use bitmasks just yet */
	$special = array(
		'CUSTOM_AVATARS' => array('OFF'=>0, 'BUILT'=>16, 'URL'=>4, 'UPLOAD'=>8, 'BUILT_URL'=>20, 'BUILT_UPLOAD'=>24, 'URL_UPLOAD'=>12, 'ALL'=>28),
		'PRIVATE_TAGS' => array('N'=>2048, 'ML'=>4096, 'HTML'=>0),
		'FORUM_CODE_SIG' => array('N'=>131072, 'ML'=>65536, 'HTML'=>0),
		'DEFAULT_THREAD_VIEW' => array('tree'=>0, 'msg'=>12, 'msg_tree'=>4, 'tree_msg'=>8),
		'MEMBER_SEARCH_ENABLED' => array('Y'=>8388608, 'N'=>0)
	);
	if (!isset($GLOBALS['FUD_OPT_1'])) {
		$FUD_OPT_1 = $FUD_OPT_2 = $FUD_OPT_3 = 0;
		foreach ($gl as $k => $v) {
			if (isset($v[1])) {
				if (isset($GLOBALS[$k])) {
					${$v[1][0]} |= !isset($v[1][2]) ? $v[1][1] : $special[$k][$GLOBALS[$k]];
				}
				unset($gl[$k]);
			}
		}
	}
	$gll = array_keys($gl);
	array_push($gll, 'FUD_OPT_2', 'FUD_OPT_1', 'FUD_OPT_3', 'INCLUDE', 'ERROR_PATH', 'MSG_STORE_DIR', 'TMP', 'FILE_STORE', 'FORUM_SETTINGS_PATH');

	$default = array(
		'CUSTOM_AVATAR_MAX_SIZE'	=> 10000,
		'CUSTOM_AVATAR_MAX_DIM'		=> '64x64',
		'COOKIE_TIMEOUT'		=> 604800,
		'SESSION_TIMEOUT'		=> 1800,
		'DBHOST_TBL_PREFIX'		=> 'fud26_',
		'FUD_SMTP_TIMEOUT'		=> 10,
		'PRIVATE_ATTACHMENTS'		=> 5,
		'PRIVATE_ATTACH_SIZE'		=> 1000000,
		'MAX_PMSG_FLDR_SIZE'		=> 300000,
		'FORUM_IMG_CNT_SIG'		=> 2,
		'FORUM_SIG_ML'			=> 256,
		'UNCONF_USER_EXPIRY'		=> 7,
		'MOVED_THR_PTR_EXPIRY'		=> 3,
		'MAX_SMILIES_SHOWN'		=> 15,
		'POSTS_PER_PAGE'		=> 40,
		'THREADS_PER_PAGE'		=> 40,
		'WORD_WRAP'			=> 60,
		'ANON_NICK'			=> 'Anonymous Coward',
		'FLOOD_CHECK_TIME'		=> 60,
		'SEARCH_CACHE_EXPIRY'		=> 172800,
		'MEMBERS_PER_PAGE'		=> 40,
		'POLLS_PER_PAGE'		=> 40,
		'THREAD_MSG_PAGER'		=> 5,
		'GENERAL_PAGER_COUNT'		=> 15,
		'EDIT_TIME_LIMIT'		=> 0,
		'LOGEDIN_TIMEOUT'		=> 5,
		'MAX_IMAGE_COUNT'		=> 10,
		'STATS_CACHE_AGE'		=> 600,
		'MAX_LOGIN_SHOW'		=> 25,
		'MAX_LOCATION_SHOW'		=> 25,
		'SHOW_N_MODS'			=> 2,
		'TREE_THREADS_MAX_DEPTH'	=> 15,
		'TREE_THREADS_MAX_SUBJ_LEN'	=> 75,
		'REG_TIME_LIMIT'		=> 60,
		'POST_ICONS_PER_ROW'		=> 9,
		'MAX_LOGGEDIN_USERS'		=> 25,
		'PHP_COMPRESSION_LEVEL'		=> 9,
		'MNAV_MAX_DATE'			=> 31,
		'MNAV_MAX_LEN'			=> 256,
		'AUTH_ID'			=> 0,
		'MAX_N_RESULTS'			=> 100,
		'PDF_PAGE'			=> 'letter',
		'PDF_WMARGIN'			=> 15,
		'PDF_HMARGIN'			=> 15,
		'PDF_MAX_CPU'			=> 60
	);

	$data = "<?php\n";
	foreach ($gll as $v) {
		if (!isset($GLOBALS[$v])) {
			$GLOBALS[$v] = isset($default[$v]) ? $default[$v] : '';
		}
		if (is_numeric($GLOBALS[$v])) {
			$data .= "\t\${$v} = {$GLOBALS[$v]};\n";
		} else {
			$data .= "\t\${$v} = \"".addcslashes($GLOBALS[$v], '"')."\";\n";
		}
	}
	$data .= "\nrequire(\$INCLUDE.'core.inc');\n?>";
	
	$fp = fopen($GLOBALS['INCLUDE'] . 'GLOBALS.php', 'wb');
	fwrite($fp, $gf);
	fclose($fp);

	if (@file_exists($GLOBALS['WWW_ROOT_DISK'] . 'thread.php')) { /* remove useless files from old installs */
		show_debug_message('Removing bogus files');
		$d = opendir(rtrim($GLOBALS['WWW_ROOT_DISK'], '/'));
		readdir($d); readdir($d);
		while ($f = readdir($d)) {
			if (!is_file($GLOBALS['WWW_ROOT_DISK'] . $f)) {
				continue;
			}
			switch ($f) {
				case 'index.php':
				case 'GLOBALS.php':
				case 'upgrade.php':
				case 'upgrade_safe.php':
				case 'lib.js':
				case 'blank.gif':
				case 'php.php':
					break;
				default:
					unlink($GLOBALS['WWW_ROOT_DISK'] . $f);
				
			}
		}
		closedir($d);
		if (@is_dir(rtrim($GLOBALS['TEMPLATE_DIR'], '/'))) {
			rename(rtrim($GLOBALS['TEMPLATE_DIR'], '/'), $GLOBALS['ERROR_PATH'].'.backup/template_'.__time__);
		}
	}

	/* Compile The Forum */
	require($GLOBALS['DATA_DIR'] . 'include/compiler.inc');

	/* list of absolete template files that should be removed */
	$rm_tmpl = array('rview.tmpl', 'allperms.tmpl','avatar.tmpl','cat.tmpl','cat_adm.tmpl','customtags.tmpl','forum_adm.tmpl','ilogin.tmpl','init_errors.tmpl', 'ipfilter.tmpl','mime.tmpl','msgreport.tmpl','objutil.tmpl','que.tmpl', 'theme.tmpl', 'time.tmpl', 'url.tmpl', 'users_adm.tmpl', 'util.tmpl', 'core.tmpl', 'path_info.tmpl');

	$c = q("SELECT theme, lang, name FROM ".$DBHOST_TBL_PREFIX."themes WHERE (theme_opt & 1) > 0 OR id=1");
	while ($r = db_rowobj($c)) {
		// See if custom themes need to have their files updated
		if ($r->theme != 'default' && $r->theme != 'path_info') {
			syncronize_theme($r->theme);
		}
		foreach ($rm_tmpl as $f) {
			@unlink($GLOBALS['DATA_DIR'].'thm/'.$r->theme.'/tmpl/' . $f);
		}
		show_debug_message('Compiling theme '.$r->name);
		compile_all($r->theme, $r->lang, $r->name);
	}
	unset($c);

	/* Insert update script marker */
	$fp = fopen($GLOBALS['ERROR_PATH'] . 'UPGRADE_STATUS', 'wb');
	fwrite($fp, $__UPGRADE_SCRIPT_VERSION);
	fclose($fp);

	if (SAFE_MODE && basename(__FILE__) == 'upgrade_safe.php') {
		unlink(__FILE__);
	}
	if ($no_mem_limit) {
		@unlink("./fudforum_archive");
	}
?>
<br>Executing Consistency Checker (if the popup with the consistency checker failed to appear you <a href="javascript://" onClick="javascript: window.open(\'adm/consist.php?enable_forum=1\');">MUST click here</a><br>
<script>
	window.open('adm/consist.php?enable_forum=1');
</script>
<font color="red" size="4">PLEASE REMOVE THIS FILE (<?php echo realpath('./upgrade.php'); ?>) UPON COMPLETION OF THE UPGRADE PROCESS.<br>THIS IS IMPERATIVE, OTHERWISE ANYONE COULD RUN THIS SCRIPT!</font>
</body>
</html>
<?php exit; ?>
2105111608_\ARCH_START_HERE
