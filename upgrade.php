<?php

/* ���ظ ���� )�)����  	
 * First 20 bytes of linux 2.4.18, so various windows utils think
 * this is a binary file and don't apply CR/LF logic
 */

/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: upgrade.php,v 1.41 2002/07/16 23:57:18 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

$__UPGRADE_SCRIPT_VERSION = 221;

define('ADMIN_FORM', '1', 1);

function glob_inc()
{
if ( !function_exists('read_global_config') ) 
{

function read_global_config()
{
	return filetomem($GLOBALS['__GLOBALS.INC__']);
}

function write_global_config($data)
{
	$fp = fopen($GLOBALS['__GLOBALS.INC__'], 'wb');
		fwrite($fp, $data);
	fclose($fp);
}

function change_global_val($name, $val, &$data)
{
	if( ($s=strpos($data, '$'.$name." ")) === false ) $s=strpos($data, '$'.$name."\t");

	if( $s !== false ) {
		$s = strpos($data, '"', $s)+1;
		$e = strpos($data, '";', $s);
		
		$data = substr_replace($data, $val, $s, ($e-$s));
	}
	else { /* Adding new option */
		$s = strpos($data, '$ALLOW_REGISTRATION')-1;
		$data = substr_replace($data, "\t\$$name\t= \"$val\";\n", $s, 0);
	}
}

function global_config_ar($data)
{
	$ar = array();

	while( ($pos = strpos($data, '$')) ) {
		$line = substr($data, $pos, (($le=strpos($data, "\n", $pos))-$pos));
		
		$tp = strpos($line, "\t");
		$ts = strpos($line, " ");
		
		if( $tp === false )
			$key_end = $ts;
		else if( $ts === false )
			$key_end = $tp;	
		else if( $ts > $tp ) 
			$key_end = $tp;	
		else if( $tp > $ts )
			$key_end = $ts;	
		
		$key = rtrim(substr($line, 1, $key_end-1));	
		if( $key == strtoupper($key) && !strpos($key, ']') ) {
			if( ($vs = strpos($line, '"', $key_end)) ) {
				$vs++;
				$ve = strpos($line, '";', $vs);
				$val = substr($line, $vs, ($ve-$vs));
				
				$ar[$key]=$val;
			}
		}
		
		$data = substr($data, $le+1);
	}
	
	return $ar;
}


}
}

function filetomem($fn)
{
	if ( !($fp = @fopen($fn, 'rb')) ) {
		echo "Unable to open (<b>$fn</b>) in (<b>".getcwd()."</b>)<br>";
		return;
	}
	$st = fstat($fp);
	$size = isset($st['size']) ? $st['size'] : $st[7];
	$str = fread($fp, $size);
	fclose($fp);
	
	return $str;
}

function versiontoint($str)
{
	$modifier = 0;
	if( preg_match('!RC([0-9]+)!', $str, $ret) ) {
		$modifier = (float) (100-$ret[1])/100000;
		$str = str_replace($ret[0], '', $str);
	}	

	$str = preg_replace('![^0-9]!', '', $str);
	return (float) substr_replace($str, '.', 1, 0)-$modifier;
}

function backupfile($source)
{
	$dir = $GLOBALS['ERROR_PATH'].'.backup';
	if( !@is_dir($dir) ) { 
		$m = umask(0);
		mkdir($dir, 0700);
		umask($m);
	}	

	copy($source, $dir.'/'.basename($source).'.'.get_random_value());
}

function __mkdir($dir)
{
	clearstatcache();
	
	if( @is_dir($dir) ) return 1;
	
	$m = umask(0);
	if( !($ret = @mkdir($dir, 0700)) ) $ret = @mkdir(dirname($dir),0700);
	umask($m);
	
	return $ret;
}

function fetch_cvs_id($data)
{
	if( ($s = strpos($data, '$Id')) === FALSE ) return;
	if( ($e = strpos($data, 'Exp $', $s)) === FALSE ) return;
	return substr($data, $s, ($e-$s));
}

function upgrade_decompress_archive($data_root, $web_root, $data)
{
	$pos = strpos($data, "2105111608_\\ARCH_START_HERE");

	if( $pos === false ) exit("Couldn't locate start of archive<br>\n");
	
	$data = substr($data, $pos+strlen("2105111608_\\ARCH_START_HERE"));
	$data = "\n".base64_decode($data);
	
	$pos=0;
	
	$oldmask = umask(0177);
	
	while( ($pos = strpos($data, "\n//", $pos)) !== false ) {
		$end = strpos($data, "\n", $pos+1);
		$meta_data = explode('//',  substr($data, $pos, ($end-$pos)));
		$pos = $end;
		
		if( $meta_data[3] == '/install' || !isset($meta_data[3]) ) continue;
		
		$path = preg_replace('!^/install/forum_data!', $data_root, $meta_data[3]);
		$path = preg_replace('!^/install/www_root!', $web_root, $path);
		$path .= "/".$meta_data[1];
		
		$path = str_replace("//", "/", $path);
		
		if( isset($meta_data[5]) ) {
			$file = substr($data, ($pos+1), $meta_data[5]);
			if( md5($file) != $meta_data[4] ) exit("ERROR: file ".$meta_data[1]." not read properly from archive\n");
			
			if( $meta_data[1] == 'GLOBALS.php' ) continue;
			
			if( @file_exists($path) ) {
				$ofile=filetomem($path);
				/* Skip the file because it is the same */
				if( md5($file) == md5($ofile) ) continue;
			
				/* Compare CVS Id to ensure we do not pointlessly replace files modified by the user */
				if( ($cvsid=fetch_cvs_id($file)) == fetch_cvs_id($ofile) && $cvsid ) continue;
				
				backupfile($path);
			}
			
			$fp = @fopen($path, 'wb');
			if( !$fp ) exit("Couldn't open $path for write<br>\n");
				fwrite($fp, $file);
			fclose($fp);
		}
		else {
			if( substr($path, -1) == '/' ) $path = preg_replace('!/+$!', '', $path);
			clearstatcache();
			if( !@is_dir($path) && !__mkdir($path) ) 
				exit("ERROR: failed creating $path directory<br>\n");
		}
	}
	umask($oldmask);
	unset($ofile);
}

function fetch_img($url)
{
	$ub = parse_url($url);
	
	if( empty($ub['port']) ) $ub['port'] = 80;
	if( !empty($ub['query']) ) $ub['path'] .= '?'.$ub['query'];
	
	$fs = fsockopen($ub['host'], $ub['port'], $errno, $errstr, 10);
	if( !$fs ) return;
	
	fputs($fs, "GET ".$ub['path']." HTTP/1.0\r\nHost: ".$ub['host']."\r\n\r\n");
	
	$ret_code = fgets($fs, 255);
	
	if( !strstr($ret_code, '200') ) {
		fclose($fs);
		return;
	}
	
	$img_str = '';
	
	while( !feof($fs) && strlen($img_str)<$GLOBALS['CUSTOM_AVATAR_MAX_SIZE'] ) 
		$img_str .= fread($fs, $GLOBALS['CUSTOM_AVATAR_MAX_SIZE']);
	fclose($fs);
	
	$img_str = substr($img_str, strpos($img_str, "\r\n\r\n")+4);

	$fp = FALSE;
	do {
		if ( $fp ) fclose($fp);
		$fp = fopen(($path=tempnam($GLOBALS['TMP'],getmypid())), 'ab');
	} while ( ftell($fp) );
	
	fwrite($fp, $img_str);
	fclose($fp);
	
	if( function_exists("GetImageSize") && !@GetImageSize($path) ) { unlink($path); return; }
		
	return $path;
}

	if( !ini_get("track_errors") ) ini_set("track_errors", 1);
	if( !ini_get("display_errors") ) ini_set("display_errors", 1);
	
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set("memory_limit", "20M");
	ignore_user_abort(true);
	@set_time_limit(6000);
	
	if( !isset($HTTP_SERVER_VARS['PATH_TRANSLATED']) && isset($HTTP_SERVER_VARS['SCRIPT_FILENAME']) ) 
		$HTTP_SERVER_VARS['PATH_TRANSLATED'] = $GLOBALS['HTTP_SERVER_VARS']['PATH_TRANSLATED'] = $HTTP_SERVER_VARS['SCRIPT_FILENAME'];
	
	/* Safe Mode sucks, now the user is instructed to jump through hoops */
	$st = stat('index.php');
	$uid = isset($st['uid']) ? $st['uid'] : $st[4];
	if( ini_get("safe_mode") && getmyuid() != $uid && $GLOBALS['HTTP_SERVER_VARS']['PATH_TRANSLATED'][0] == '/' ) {
		if( basename($HTTP_SERVER_VARS['SCRIPT_FILENAME']) != 'upgrade_safe.php' ) {
			if( @copy($HTTP_SERVER_VARS['SCRIPT_FILENAME'], 'upgrade_safe.php') ) {
				header("Location: upgrade_safe.php");
				exit;
			}
		}
		
		echo '<font color="#FF0000">SAFE_MODE is enabled!<br>Use the file manager to upload this script into the WWW_SERVER_ROOT directory</font>';
		exit;
	}

echo '<html><body bgcolor="#FFFFFF">';

	if( !@file_exists('GLOBALS.php') ) {
		echo '<font color="#FF0000">Cannot open GLOBALS.php, this does not appear to be a forum directory. You need to upload the file in to the forum\'s WWW_SERVER_ROOT directory</font>';
		exit;	
	}
	
	include_once "GLOBALS.php";
	
	/* Upgrade Marker Check */
	
	if( @file_exists($ERROR_PATH.'UPGRADE_STATUS') ) {
		$marker = filetomem($ERROR_PATH.'UPGRADE_STATUS');
		if( $marker && $marker >= $__UPGRADE_SCRIPT_VERSION ) {
			echo '<font color="#FF0000">THIS UPGRADE SCRIPT HAS ALREADY BEEN RUN, IF YOU WISH TO RUN IT AGAIN USE THE FILE MANAGER TO REMOVE THE "'.$ERROR_PATH.'UPGRADE_STATUS" FILE.</font>';
			exit;	
		}
	}	
	
	$data = filetomem($GLOBALS['HTTP_SERVER_VARS']['PATH_TRANSLATED']);
	
	$CUR_FORUM_VERSION = versiontoint($FORUM_VERSION);
	
	if( $CUR_FORUM_VERSION < versiontoint('2.1.2') ) {
		if( @file_exists($GLOBALS['INCLUDE'].'static/glob.inc') ) fud_use('static/glob.inc');
	}	
	else
		fud_use('glob.inc', TRUE);

	glob_inc();
	
	$GLOBALS_FILE = read_global_config();
	change_global_val('FORUM_ENABLED', 'N', $GLOBALS_FILE);
	write_global_config($GLOBALS_FILE);
	
	$GLOBALS_FILE_B = $GLOBALS_FILE;
	
	/* Upgrade Files */
	
	echo "Beginning the file upgrade process<br>\n";
	$d_dir = isset($GLOBALS['DATA_DIR']) ? $GLOBALS['DATA_DIR'] : realpath($TEMPLATE_DIR.'../');
	
	upgrade_decompress_archive($d_dir , $WWW_ROOT_DISK, $data);	
	echo "File Upgrade Complete<br>\n";
	echo '<font color="#ff0000">Any changed files were backed up to: "'.$GLOBALS['ERROR_PATH'].'.backup/"</font><br>';
	flush();
	
	/* Update SQL */
	/* SQL FORMAT version//query */	
	
	$s = strpos($data, "042252166145_\\SQL_START_HERE") + strlen("042252166145_\\SQL_START_HERE");
	$e = strpos($data, "042252166145_\\SQL_END_HERE", $s);
	$sql_data = substr($data, $s, ($e-$s));
	
	/* database variable conversion */
	if( !isset($DBHOST_TBL_PREFIX) ) 
		$DBHOST_TBL_PREFIX = $MYSQL_TBL_PREFIX;
	else if( !defined('__dbtype__') && isset($DBHOST_TBL_PREFIX) ) {
		$MYSQL_SERVER 		= $DBHOST;
		$MYSQL_LOGIN 		= $DBHOST_USER;
		$MYSQL_PASSWORD 	= $DBHOST_PASSWORD;
		$MYSQL_DB 		= $DBHOST_DBNAME;
		$MYSQL_PERSIST 		= $DBHOST_PERSIST;
		$MYSQL_TBL_PREFIX 	= $DBHOST_TBL_PREFIX;
	}	
	
	fud_use('db.inc');
	
	/* Verify that we go permissions to CREATE/ALTER MySQL tables */
	mysql_query("DROP TABLE upgrade_test_table");
	if( !mysql_query("CREATE TABLE upgrade_test_table (test_val INT)") ) 
		exit("FATAL ERROR: your forum's MySQL account does not have permissions to create new MySQL tables<br>\nEnable this functionality and restart the script.<br>\n");
	if( !mysql_query("ALTER TABLE upgrade_test_table ADD test_val2 INT") ) 
		exit("FATAL ERROR: your forum's MYSQL account does not have permissions to run ALTER queries on existing MySQL tables<br>\nEnable this functionality and restart the script.<br>\n");
	mysql_query("DROP TABLE upgrade_test_table");
	
	echo "\n<br>Beginning SQL Upgrades<br>\n";
	$qry = explode("\n", $sql_data);
	while( list(,$v) = each($qry) ) {
		if( !trim($v) ) continue;
	
		list($version,$q) = explode("//", $v, 2);
		
		if ( $CUR_FORUM_VERSION > versiontoint($version) ) continue;
		
		$q = str_replace('{SQL_TABLE_PREFIX}', $DBHOST_TBL_PREFIX, $q);
		
		if( !mysql_query($q) ) echo "<pre>	".mysql_error()."</pre>";
	}
	unset($sql_data); unset($qry); unset($q);
	echo "SQL Upgrade Complete<br>\n";
	flush();

	if ( !($r=mysql_query("SELECT home_page FROM ".$DBHOST_TBL_PREFIX."users LIMIT 1")) ) {
		$curdir = getcwd();
		chdir($GLOBALS['USER_SETTINGS_PATH']);
		$dir = opendir('.');
		readdir($dir); readdir($dir);
		while( $file = readdir($dir) ) {
			if( substr($file, -4) != '.fud' ) continue;
			list($www, $bio) = read_ext_set($file);
			$id = substr($file, 0, strpos($file, '.'));
			q("UPDATE ".$DBHOST_TBL_PREFIX."users SET home_page='".addslashes($www)."', bio='".addslashes($bio)."' WHERE id=$id");
		}
		closedir($dir);
		chdir($curdir);
		
	}
	else qf($r);
	if ( !($r3=mysql_query("SELECT p_VISIBLE FROM ".$DBHOST_TBL_PREFIX."groups LIMIT 1")) ) {
		echo "adding visble permisson<Br>";
		q("ALTER TABLE ".$DBHOST_TBL_PREFIX."groups ADD p_VISIBLE ENUM('I', 'Y', 'N') NOT NULL DEFAULT 'N'");
		q("UPDATE ".$DBHOST_TBL_PREFIX."groups SET p_VISIBLE='Y' WHERE p_READ='Y'");
		q("UPDATE ".$DBHOST_TBL_PREFIX."group_members SET up_VISIBLE='Y' WHERE up_READ='Y'");
		
		$r = q("SHOW FIELDS FROM ".$DBHOST_TBL_PREFIX."groups");
		while ( $obj = db_rowobj($r) ) {
			if ( substr($obj->Field, 0, 2) != 'p_' ) continue; 
			if ( $obj->Field == 'p_VISIBLE' ) break;
		
			$r2 = q("SELECT $obj->Field, id FROM ".$DBHOST_TBL_PREFIX."groups");
			while ( $obj2 = db_rowobj($r2) ) {
				$vals[$obj2->id] = $obj2->{$obj->Field};
			}
		
			q("ALTER TABLE ".$DBHOST_TBL_PREFIX."groups DROP $obj->Field");
			q("ALTER TABLE ".$DBHOST_TBL_PREFIX."groups ADD $obj->Field ENUM('I', 'Y', 'N') NOT NULL DEFAULT 'N'");
			reset($vals);
			while ( list($k, $v) = each($vals) ) {
				q("UPDATE ".$DBHOST_TBL_PREFIX."groups SET $obj->Field='$v' WHERE id=$k");
			}
			qf($r2);
		}
		qf($r);

		$r = q("SHOW FIELDS FROM ".$DBHOST_TBL_PREFIX."group_members");
		while ( $obj = db_rowobj($r) ) {
			if ( substr($obj->Field, 0, 3) != 'up_' ) continue; 
			if ( $obj->Field == 'up_VISIBLE' ) break;
		
			$r2 = q("SELECT $obj->Field, id FROM ".$DBHOST_TBL_PREFIX."group_members");
			while ( $obj2 = db_rowobj($r2) ) {
				$vals[$obj2->id] = $obj2->{$obj->Field};
			}
			
			q("ALTER TABLE ".$DBHOST_TBL_PREFIX."group_members DROP $obj->Field");
			q("ALTER TABLE ".$DBHOST_TBL_PREFIX."group_members ADD $obj->Field ENUM('Y', 'N') NOT NULL DEFAULT 'N'");
			reset($vals);
			while ( list($k, $v) = each($vals) ) {
				q("UPDATE ".$DBHOST_TBL_PREFIX."group_members SET $obj->Field='$v' WHERE id=$k");
			}
			qf($r2);
		}
		qf($r);
	}
	else qf($r3);

	/* convert the replacement system into the new format */
	if ( $CUR_FORUM_VERSION < versiontoint('2.0') ) {
		$r = q("SELECT * FROM ".$DBHOST_TBL_PREFIX."replace WHERE type='REPLACE'");
		while ( $obj = db_rowobj($r) ) {
			$obj->replace_str = addslashes(preg_quote($obj->replace_str));
			$obj->replace_str = '/'.str_replace('/', '\\\\/',  $obj->replace_str).'/i';
			$obj->with_str = str_replace('\\', "\\\\", $obj->with_str);
			q("UPDATE ".$DBHOST_TBL_PREFIX."replace SET replace_str='$obj->replace_str', with_str='$obj->with_str' WHERE id=$obj->id");
		}
		qf($r);
	}
	
	/* Convert old url based avatars to new format */
	$r = q("SELECT id,avatar_loc FROM ".$DBHOST_TBL_PREFIX."users WHERE avatar_loc!=''");
	while( $obj = db_rowobj($r) ) {
		if( ($avt_path = fetch_img($obj->avatar_loc)) )
			copy($avt_path, $GLOBALS['WWW_ROOT_DISK'].'images/custom_avatars/'.$obj->id);
		else
			q("UPDATE ".$DBHOST_TBL_PREFIX."users SET avatar_approved='NO' WHERE id=".$obj->id);
	}
	qf($r);
	
	q("UPDATE ".$DBHOST_TBL_PREFIX."users SET avatar_loc='' WHERE avatar_loc!=''");
	
	/* Add data into pdest field of pmsg table */
	if ( $CUR_FORUM_VERSION < versiontoint('2.2.1') ) {
		$r = q("SELECT to_list,id FROM ".$DBHOST_TBL_PREFIX."pmsg WHERE folder_id='SENT' AND duser_id=ouser_id");
		while( list($l,$id) = db_rowarr($r) ) {
			if( ($p=strpos($l, ';')) ) 
				$uname = substr($l, 0, $p);
			else
				$uname = $l;
		
			if( !trim($uname) ) continue;		
			if( !($uid = q_singleval("select id from ".$DBHOST_TBL_PREFIX."users where login='".addslashes($uname)."'")) ) continue;
		
			q("UPDATE ".$DBHOST_TBL_PREFIX."pmsg SET pdest=".$uid." WHERE id=".$id);
		}
		qf($r);
	}
	if ( !bq("SELECT id FROM ".$DBHOST_TBL_PREFIX."themes WHERE t_default='Y'") ) {
		$pspell_lang = (@file_exists($d_dir.'/thm/default/i18n/'.$GLOBALS['LANGUAGE'].'/pspell_lang')) ? trim(filetomem($d_dir.'/thm/default/i18n/'.$GLOBALS['LANGUAGE'].'/pspell_lang')) : '';
	
		q("INSERT INTO ".$DBHOST_TBL_PREFIX."themes(id, name, theme, lang, locale, enabled, t_default, pspell_lang)
		VALUES(1, 'default', 'default', '".$GLOBALS['LANGUAGE']."', '".$GLOBALS['LOCALE']."', 'Y', 'Y', '$pspell_lang')");
		Q("UPDATE ".$DBHOST_TBL_PREFIX."users SET theme=1");
	}
	
	/* Add any needed GLOBAL OPTIONS */
	
	echo "\n<br>Adding GLOBAL Variables<br>\n";
	$s = strpos($data, "116304110503_\\GLOBAL_VARS_START_HERE") + strlen("116304110503_\\GLOBAL_VARS_START_HERE");
	$e = strpos($data, "116304110503_\\GLOBAL_VARS_END_HERE", $s);
	$gvar_data = substr($data, $s, ($e-$s));
	$gvars = explode("\n", $gvar_data);
	
	if ( !isset($GLOBALS['DATA_DIR']) ) $gvars[] = '$DATA_DIR	= "'.realpath($GLOBALS['TEMPLATE_DIR'].'../').'/";';
	while( list(,$v) = each($gvars) ) {
		if( !($v = ltrim($v)) ) continue;
		$e = strpos($v, ' ');
		
		$varname = trim(substr($v, 1, ($e-1)));
		
		if( isset(${$varname}) ) continue;
		
		$GLOBALS_FILE = substr_replace($GLOBALS_FILE, $v."\n\t", strpos($GLOBALS_FILE, '$ADMIN_EMAIL'), 0);
	}
	
	/* convert the name of the database related global variables */
	if( isset($MYSQL_SERVER) ) {
		$GLOBALS_FILE = str_replace('MYSQL_SERVER', 'DBHOST', $GLOBALS_FILE);
		$GLOBALS_FILE = str_replace('MYSQL_LOGIN', 'DBHOST_USER', $GLOBALS_FILE);
		$GLOBALS_FILE = str_replace('MYSQL_PASSWORD', 'DBHOST_PASSWORD', $GLOBALS_FILE);
		$GLOBALS_FILE = str_replace('MYSQL_DB', 'DBHOST_DBNAME', $GLOBALS_FILE);
		$GLOBALS_FILE = str_replace('MYSQL_PERSIST', 'DBHOST_PERSIST', $GLOBALS_FILE);
		$GLOBALS_FILE = str_replace('MYSQL_TBL_PREFIX', 'DBHOST_TBL_PREFIX', $GLOBALS_FILE);
	}
	
	if( $GLOBALS_FILE_B != $GLOBALS_FILE ) {
		$fp = fopen($GLOBALS['__GLOBALS.INC__'], 'wb');
			fwrite($fp, $GLOBALS_FILE);
		fclose($fp);
	}
	unset($gvars); unset($gvar_data);
	
	echo "Finished Adding GLOBAL Variables<br>\n";
	flush();
	
	if ( $CUR_FORUM_VERSION < versiontoint('2.1') ) {
		$oldcwd = getcwd();
		chdir($GLOBALS['WWW_ROOT_DISK']);
	
		$dp = opendir('.');
		readdir($dp); readdir($dp);
		$arr = array('index.php'=>1, 'GLOBALS.php'=>1, 'php.php'=>1, basename($HTTP_SERVER_VARS['PATH_TRANSLATED'])=>1);
		while ( $de = readdir($dp) ) 
		{
			if ( substr($de, -4) != '.php' || isset($arr[$de]) || !@is_file($de) ) continue;
			unlink($de);
		}
		closedir($dp);
		chdir($oldcwd);
		$u = umask(0);
		if ( !@is_dir($GLOBALS['ERROR_PATH'].'.backup') ) mkdir($GLOBALS['ERROR_PATH'].'.backup', '0700');
		umask($u);
		$src = substr($GLOBALS['TEMPLATE_DIR'], 0, -1);
		$dst = $GLOBALS['ERROR_PATH'].'.backup/template_'.time();
		if ( !rename($src, $dst) )
			echo "unable to rename (<b>$src</b>) to (<b>$dst</b>)<br>\n";
	}
	/* Compile The Forum */
	if( !defined('__dbtype__') ) define('__dbtype__', 'mysql');
	
	fud_use('compiler.inc', TRUE);

	$r = q("SELECT * FROM ".$DBHOST_TBL_PREFIX."themes WHERE enabled='Y'");
	if ( !isset($GLOBALS['DATA_DIR']) ) $GLOBALS['DATA_DIR'] = $d_dir;
	if ( substr($GLOBALS['DATA_DIR'], -1) != '/' ) $GLOBALS['DATA_DIR'] .= '/';
	echo "DATADIR: ".$GLOBALS['DATA_DIR']."<br>\n";
	
	/* if need be remove core.inc.t */
	if( @file_exists($GLOBALS['DATA_DIR'].'src/core.inc.t') ) unlink($GLOBALS['DATA_DIR'].'src/core.inc.t');
	
	while ( $obj = db_rowobj($r) ) {
		/* remove core.tmpl if need be */
		if( @file_exists($GLOBALS['DATA_DIR'].'thm/'.$obj->theme.'/tmpl/core.tmpl') ) 
			unlink($GLOBALS['DATA_DIR'].'thm/'.$obj->theme.'/tmpl/core.tmpl');
		
		echo "Compiling $obj->name<br>\n";
		compile_all($obj->theme, $obj->lang, $obj->name);
	}
	qf($r);
	/* Insert update script marker */
	$fp = fopen($ERROR_PATH.'UPGRADE_STATUS', 'wb');
		fwrite($fp, $__UPGRADE_SCRIPT_VERSION);
	fclose($fp);
	
	echo '<br>Executing Consistency Checker (if the popup with the consistency checker failed to appear you <a href="javascript://" onClick="javascript: window.open(\'adm/consist.php?enable_forum=1\');">MUST click here</a><br>';
	echo "
		<script>
			window.open('adm/consist.php?enable_forum=1');
		</script>";
		
	if( basename($HTTP_SERVER_VARS['SCRIPT_FILENAME']) == 'upgrade_safe.php' ) {
		unlink('upgrade_safe.php');
		$HTTP_SERVER_VARS['PATH_TRANSLATED'] = realpath('upgrade.php');
	}
		
	echo '<font color="red" size="4">PLEASE REMOVE THIS FILE('.$HTTP_SERVER_VARS['PATH_TRANSLATED'].') UPON COMPLETION OF THE UPGRADE PROCESS.<br>THIS IS IMPERATIVE, OTHERWISE ANYONE COULD RUN THIS SCRIPT!</font>';
?>
</body>
</html>
<?php exit; ?>
042252166145_\SQL_START_HERE
1.9.8RC1//ALTER TABLE {SQL_TABLE_PREFIX}msg ADD offset_preview  INT UNSIGNED NOT NULL DEFAULT 0;
1.9.8RC1//ALTER TABLE {SQL_TABLE_PREFIX}msg ADD length_preview  INT UNSIGNED NOT NULL DEFAULT 0;
1.9.8RC1//ALTER TABLE {SQL_TABLE_PREFIX}thread DROP replyallowed; 
1.9.8RC1//ALTER TABLE {SQL_TABLE_PREFIX}users CHANGE private_messages email_messages ENUM('Y', 'N') NOT NULL DEFAULT 'Y';
1.9.8RC1//ALTER TABLE {SQL_TABLE_PREFIX}msg ADD file_id_preview INT UNSIGNED NOT NULL DEFAULT 0;
1.9.8RC4//ALTER TABLE {SQL_TABLE_PREFIX}pmsg ADD INDEX(duser_id, folder_id, id);
1.9.8RC4//ALTER TABLE {SQL_TABLE_PREFIX}smiley ADD vieworder INT UNSIGNED NOT NULL;
1.9.8RC4//ALTER TABLE {SQL_TABLE_PREFIX}users ADD show_sigs ENUM('Y', 'N') NOT NULL DEFAULT 'Y';
1.9.8RC4//ALTER TABLE {SQL_TABLE_PREFIX}users ADD show_avatars ENUM('Y', 'N') NOT NULL DEFAULT 'Y';
1.9.8//ALTER TABLE {SQL_TABLE_PREFIX}action_log CHANGE logaction logaction CHAR(100);
1.9.8//ALTER TABLE {SQL_TABLE_PREFIX}action_log ADD a_res CHAR(100);
1.9.8//ALTER TABLE {SQL_TABLE_PREFIX}action_log ADD a_res_id INT UNSIGNED NOT NULL DEFAULT 0;
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}replace CHANGE type type ENUM('REPLACE', 'PERL') NOT NULL DEFAULT 'REPLACE';
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}cat DROP hidden;
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}forum DROP hidden;
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}forum ADD message_threshold INT UNSIGNED NOT NULL DEFAULT 0;
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}forum ADD INDEX(hidden);
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}forum ADD INDEX(last_post_id);
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}group_members ADD up_VISIBLE ENUM('Y', 'N') NOT NULL DEFAULT 'N';
1.9.9RC1//ALTER TABLE {SQL_TABLE_PREFIX}group_cache ADD p_VISIBLE ENUM('Y', 'N') NOT NULL DEFAULT 'N';
1.9.9//ALTER TABLE {SQL_TABLE_PREFIX}groups CHANGE p_VIEW p_READ ENUM('I', 'Y', 'N') NOT NULL DEFAULT 'N';
1.9.9//ALTER TABLE {SQL_TABLE_PREFIX}group_members CHANGE up_VIEW up_READ ENUM('Y', 'N') NOT NULL DEFAULT 'N';
1.9.9//ALTER TABLE {SQL_TABLE_PREFIX}group_cache CHANGE p_VIEW p_READ ENUM('Y', 'N') NOT NULL DEFAULT 'N';
2.1//ALTER TABLE {SQL_TABLE_PREFIX}users DROP style;
2.1//ALTER TABLE {SQL_TABLE_PREFIX}users ADD theme INT UNSIGNED NOT NULL DEFAULT 0;
2.1//CREATE TABLE {SQL_TABLE_PREFIX}themes(id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,name CHAR(255) NOT NULL,theme CHAR(255) NOT NULL,lang CHAR(255) NOT NULL,locale CHAR(32) NOT NULL,enabled ENUM('Y', 'N') NOT NULL DEFAULT 'Y',t_default	ENUM('Y', 'N') NOT NULL DEFAULT 'N',INDEX(enabled),INDEX(t_default));
2.1.1//ALTER TABLE {SQL_TABLE_PREFIX}themes ADD pspell_lang CHAR(32) NULL;
2.1.2//ALTER TABLE {SQL_TABLE_PREFIX}msg CHANGE offset foff INT UNSIGNED NOT NULL DEFAULT 0;
2.1.2//ALTER TABLE {SQL_TABLE_PREFIX}pmsg CHANGE offset foff INT UNSIGNED NOT NULL DEFAULT 0;
2.1.2//ALTER TABLE {SQL_TABLE_PREFIX}pmsg CHANGE length length INT UNSIGNED NOT NULL DEFAULT 0;
2.1.2//CREATE TABLE {SQL_TABLE_PREFIX}announce_tmp ( id INT, date_started DATE, date_ended DATE,subject VARCHAR(255),text TEXT );
2.1.2//INSERT INTO {SQL_TABLE_PREFIX}announce_tmp SELECT * FROM {SQL_TABLE_PREFIX}announce;
2.1.2//ALTER TABLE {SQL_TABLE_PREFIX}announce CHANGE date_started date_started INT NOT NULL;
2.1.2//ALTER TABLE {SQL_TABLE_PREFIX}announce CHANGE date_ended date_ended INT NOT NULL;
2.1.2//DELETE FROM {SQL_TABLE_PREFIX}announce;
2.1.2//INSERT INTO {SQL_TABLE_PREFIX}announce SELECT id,REPLACE(date_started,'-',''),REPLACE(date_ended,'-',''),subject,text FROM {SQL_TABLE_PREFIX}announce_tmp;
2.1.2//DROP TABLE {SQL_TABLE_PREFIX}announce_tmp;
2.1.2//ALTER TABLE {SQL_TABLE_PREFIX}cat DROP creation_date;
2.1.2//UPDATE {SQL_TABLE_PREFIX}group_members SET user_id=2147483647 WHERE user_id=4294967295;
2.1.2//UPDATE {SQL_TABLE_PREFIX}group_cache SET user_id=2147483647 WHERE user_id=4294967295;
2.2.1//ALTER TABLE {SQL_TABLE_PREFIX}thread_view ADD tmp INT UNSIGNED;
2.2.1//ALTER TABLE {SQL_TABLE_PREFIX}thread_view CHANGE pos pos INT UNSIGNED NOT NULL AUTO_INCREMENT;
2.2.1//ALTER TABLE {SQL_TABLE_PREFIX}thread ADD INDEX(is_sticky,orderexpiry);
2.2.1//ALTER TABLE {SQL_TABLE_PREFIX}pmsg ADD pdest INT UNSIGNED NOT NULL DEFAULT 0;
2.2.2//ALTER TABLE {SQL_TABLE_PREFIX}users ADD alias CHAR(50) NOT NULL;
2.2.2//UPDATE {SQL_TABLE_PREFIX}users SET alias=login;
2.2.2//ALTER TABLE {SQL_TABLE_PREFIX}users ADD UNIQUE(alias);
2.2.4//ALTER TABLE {SQL_TABLE_PREFIX}ses ADD forum_id INT NOT NULL DEFAULT 0;
042252166145_\SQL_END_HERE

116304110503_\GLOBAL_VARS_START_HERE
$FORUM_IMG_CNT_SIG      = "2";          /* int */
$MAX_SMILIES_SHOWN      = "15";         /* int */
$SHOW_N_MODS            = "2";
$NOTIFY_WITH_BODY       = "N";          /* boolean */
$USE_ALIASES		= "N";		/* boolean */
$MULTI_HOST_LOGIN	= "N";		/* boolean */
$USE_SMTP		= "N";		/* boolean */
$FUD_SMTP_SERVER	= "";
$FUD_SMTP_TIMEOUT	= "10";		/* seconds */
$FUD_SMTP_LOGIN		= "";
$FUD_SMTP_PASS		= "";
116304110503_\GLOBAL_VARS_END_HERE

2105111608_\ARCH_START_HERE
