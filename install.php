<?php
/* ���ظ ���� )�)����  
 * First 20 bytes of linux 2.4.18, so various windows utils think
 * this is a binary file and don't apply CR/LF logic
 */

/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: install.php,v 1.79 2003/10/05 20:25:35 hackie Exp $
****************************************************************************

****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

function fud_ini_get($opt)
{
	return (ini_get($opt) == '1' ? 1 : 0);
}

	if (!fud_ini_get('track_errors')) {
		ini_set('track_errors', 1);
	}
	if (!fud_ini_get('display_errors')) {
		ini_set('display_errors', 1);
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
	/* opening a connection to itself should not take more then 5 seconds */
	ini_set("default_socket_timeout", 5);

	/* Determine SafeMode limitations */
	define('SAFE_MODE', fud_ini_get('safe_mode'));

	/* Determine open_basedir limitations */
	define('open_basedir', ini_get('open_basedir'));

	/* determine if magic_guotes_gpc are off */
	$magic_guotes_gpc = get_magic_quotes_gpc();

	if (!isset($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['PATH_TRANSLATED'] = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : realpath(__FILE__);
	}
	$module_status = module_check();

function module_check()
{
	$modules = array('zlib', 'mysql', 'pgsql', 'pcre', 'tokenizer', 'pspell', 'pdf', 'posix');
	foreach ($modules as $m) {
		$status[$m] = extension_loaded($m);
	}
	return $status;
}

function initdb()
{
	switch ($_POST['DBTYPE']) {
		case 'mysql':
			if (!($conn = mysql_connect($_POST['DBHOST'], $_POST['DBHOST_USER'], $_POST['DBHOST_PASSWORD']))) {
				seterr('DBHOST', 'Failed to connect to the MySQL Server, SQL Reason: '.mysql_error());
			}
			define('__FUD_SQL_LNK__', $conn);
			if (!mysql_select_db($_POST['DBHOST_DBNAME'], __FUD_SQL_LNK__)) {
				seterr('DBHOST_DBNAME', 'Could not open the database you\'ve specified, SQL Reason: '.mysql_error());
			}
			break;
		case 'pgsql':
			$connect_str = '';
			if (!empty($_POST['DBHOST'])) {
				$connect_str .= 'host='.$_POST['DBHOST'];
			}
			if (!empty($_POST['DBHOST_USER'])) {
				$connect_str .= ' user='.$_POST['DBHOST_USER'];
			}
			if (!empty($_POST['DBHOST_PASSWORD'])) {
				$connect_str .= ' password='.$_POST['DBHOST_PASSWORD'];
			}
			if (!empty($_POST['DBHOST_DBNAME'])) {
				$connect_str .= ' dbname='.$_POST['DBHOST_DBNAME'];
			}
			if (!($conn = pg_connect(ltrim($connect_str)))) {
				seterr('DBHOST', 'Failed to establish database connection to '.$_POST['DBHOST']);
			}
			define('__FUD_SQL_LNK__', $conn);
			break;
	}
}

function dbquery($qry)
{
	if (!defined('__FUD_SQL_LNK__')) {
		return FALSE;
	}

	switch ($_POST['DBTYPE']) {
		case 'mysql':
			return mysql_query($qry, __FUD_SQL_LNK__);
			break;
		case 'pgsql':
			return pg_query(__FUD_SQL_LNK__, $qry);
			break;
	}
}

function dberror()
{
	switch ($_POST['DBTYPE']) {
		case 'mysql':
			return mysql_error(__FUD_SQL_LNK__);
			break;
		case 'pgsql':
			return pg_last_error(__FUD_SQL_LNK__);
			break;
	}
}

function dbperms_check()
{
	switch ($_POST['DBTYPE']) {
		case 'mysql':
			mysql_query('DROP TABLE IF EXISTS fud_forum_install_test_table');
			if (!mysql_query('CREATE TABLE fud_forum_install_test_table (test_val INT)')) {
				seterr('DBHOST', 'FATAL ERROR: your MySQL account does not have permissions to create new MySQL tables.<br>Enable this functionality and restart the script.');
			}
			if (!mysql_query('ALTER TABLE fud_forum_install_test_table ADD test_val2 INT')) {
				seterr('DBHOST', 'FATAL ERROR: your MySQL account does not have permissions to run ALTER queries on existing MySQL tables<br>Enable this functionality and restart the script.');
			}
			if (!mysql_query('LOCK TABLES fud_forum_install_test_table WRITE')) {
				seterr('DBHOST', 'FATAL ERROR: your MySQL account does not have permissions to run LOCK queries on existing MySQL tables<br>Enable this functionality and restart the script.');
			}
			mysql_query('UNLOCK TABLES');
			if (!mysql_query('DROP TABLE fud_forum_install_test_table')) {
				seterr('DBHOST', 'FATAL ERROR: your MySQL account does not have permissions to run DROP TABLE queries on existing MySQL tables<br>Enable this functionality and restart the script.');
			}
			break;
		case 'pgsql':
			@pg_query(__FUD_SQL_LNK__, 'DROP TABLE fud_forum_install_test_table');
			if (!pg_query(__FUD_SQL_LNK__, 'CREATE TABLE fud_forum_install_test_table (test_val INT)')) {
				seterr('DBHOST', 'FATAL ERROR: your PostgreSQL account does not have permissions to create new PostgreSQL tables.<br>Enable this functionality and restart the script.');
			}
			if (!pg_query(__FUD_SQL_LNK__, 'BEGIN WORK') || !pg_query(__FUD_SQL_LNK__, 'ALTER TABLE fud_forum_install_test_table ADD test_val2 INT')) {
				seterr('DBHOST', 'FATAL ERROR: your PostgreSQL account does not have permissions to run ALTER queries on existing PostgreSQL tables<br>Enable this functionality and restart the script.');
			}
			pg_query(__FUD_SQL_LNK__, 'COMMIT WORK');

			if (!pg_query(__FUD_SQL_LNK__, 'DROP TABLE fud_forum_install_test_table')) {
				seterr('DBHOST', 'FATAL ERROR: your PostgreSQL account does not have permissions to run DROP TABLE queries on existing PostgreSQL tables<br>Enable this functionality and restart the script.');
			}
			break;
	}
}

function make_into_query($data)
{
	return trim(str_replace('{SQL_TABLE_PREFIX}', $_POST['DBHOST_TBL_PREFIX'], preg_replace('!\s+!', ' ', preg_replace('!\#.*$!s', '', $data))));
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
			$e = strpos($settings, ';', $p);

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

/* needed for php versions (<4.3.0) lacking this function */
if (!function_exists('file_get_contents')) {
	function file_get_contents($path)
	{
		if (!($fp = fopen($path, 'rb'))) {
			return FALSE;
		}
		if (($size = @filesize($path)) === FALSE) {
			/* probably a URL */
			$size = 1024 * 1024;
		}

		$data = fread($fp, $size);
		fclose($fp);

		return $data;
	}
}

/* un-quote the string if it is quotes */
if ($magic_guotes_gpc) {
	function strip_quotes(&$var)
	{
		$var = stripslashes($var);
	}

	if (@count($_GET)) {
		array_walk($_GET, 'strip_quotes');
	}
        if (@count($_POST)) {
		array_walk($_POST, 'strip_quotes');
	}
}

/* Perform various sanity checks, which check for required components */
if (!count($_POST)) {
	/* php version check */
	if (!version_compare(PHP_VERSION, '4.2.0', '>=')) {
?>
<html>
<body bgcolor="white">
Your php version <b>(<?php echo PHP_VERSION; ?>)</b> is older then the minimum required version <b>(4.2.0)</b>.
Please install the newer version and try again.<br>The reasons for this restriction are numerous, most important ones
being security &amp; performance. Also, if you are using PostgreSQL, the API of the PostgreSQL interface functions
had changed drastically in PHP version, 4.2.0 hence this requirment.
</body>
</html>
<?php
		exit;
	}

	/* file permission check */
	if ($no_mem_limit && !@is_writeable(__FILE__)) {
?>
<html>
<body bgcolor="white">
You need to chmod the <?php echo __FILE__; ?> file 666 (-rw-rw-rw-), so that the installer can modify itself. This
is needed to avoid problems since your PHP installation enforces memory limit setting.
</body>
</html>
<?php
		exit;
	}


	/* check for installer validatity */
	$fsize = filesize(__FILE__);
	if ($fsize < 200000 && !file_exists("./fudforum_archive")) {
?>
<html>
<body bgcolor="white">
The installer is missing the data archive, append the archive to the installer and try again.
</body>
</html>
<?php
		exit;
	} else if ($fsize > 200000) {
		/* zlib check */
		if (($zl = ($fsize < 3500000)) && !$module_status['zlib']) {
?>
<html>
<body bgcolor="white">
zlib extension required to decompress the archive is not loaded.
Please recompile your PHP with zlib support or load the zlib extension, in the event this is not possible download
the non-zlib version of the install or upgrade script from FUDforum's website at: <a href="http://fud.prohost.org/forum/">http://fud.prohost.org/forum/</a>.
</body>
</html>
<?php
			exit;
		}
	}

	/* database check */
	if (!$module_status['mysql'] && !$module_status['pgsql']) {
?>
<html>
<body bgcolor="white">
FUDforum can utilize either MySQL or PosgreSQL database to store it's data, unfortunately, your PHP does not have
support for either one. Please install or load the appropriate database extension and then re-run the install script.
</body>
</html>
<?php
		exit;
	}
	/* pcre check */
	if (!$module_status['pcre']) {
?>
<html>
<body bgcolor="white">
PCRE (Perl Compatible Regular Expression) extension required for proper forum operation is not avaliable, please load or install
this extension and then re-run the installer.
</body>
</html>
<?php
		exit;
	}

	if (isset($zl) && $no_mem_limit) {
		/* move archive to separate file */
		if (!($fp = @fopen("./fudforum_archive", "wb"))) {
			echo '<html><body bgcolor="white">Please make sure that the intaller has permission to write to the current directory ('.getcwd().')';
			if (!SAFE_MODE) {
				echo '<br/ >or create a "fudforum_archive" file inside the current directory and make it writable to the webserver.';
			}
			exit('</body></html>');
		}
		$main = '';
		$l = strlen("2105111608_\\ARCH_START_HERE");

		$fp2 = fopen(__FILE__, 'rb');
		while (($line = fgets($fp2, 10000))) {
			$main .= $line;
			if (!strncmp($line, "2105111608_\\ARCH_START_HERE", $l)) {
				break;
			}
		}

		while (($tmp = fread($fp2, 20000))) {
			fwrite($fp, $tmp);
		}
		fclose($fp);
		fclose($fp2);

		$fp = fopen(__FILE__, "wb");
		fwrite($fp, $main);
		fclose($fp);

		unset($main, $tmp);
	}
}

	/* determine directory separator */
	$WINDOWS = preg_match('!win!i', PHP_OS);
	define('SLASH', $WINDOWS ? '\\\\' : '/');

function __mkdir($dir)
{
	if (@is_dir($dir)) {
		return 1;
	}
	$u = umask(0077);
	$ret = (mkdir($dir) || mkdir(dirname($dir)));
	umask($u);

	return $ret;
}

function draw_row($title, $var, $def, $descr=NULL)
{
	echo '<tr bgcolor="#bff8ff"><td valign="top"><b>'.$title.'</b>'.($descr ? '<br><font size=-1>'.$descr.'</font>' : '').'</td><td>'.(isset($GLOBALS['errors'][$var]) ? $GLOBALS['errors'][$var] : '').'<input type="text" name="'.$var.'" value="'.htmlspecialchars($def).'" size=40></td></tr>';
}

function draw_row_sel($title, $var, $opt_list, $val_list, $descr=NULL, $def=NULL)
{
	$val_list = explode("\n", $val_list);
	$opt_list = explode("\n", $opt_list);

	if (($c = count($val_list)) != count($opt_list)) {
		exit('Value list does not match option count');
	}

	echo '<tr bgcolor="#bff8ff"><td valign="top"><b>'.$title.'</b>'.($descr ? '<br><font size=-1>'.$descr.'</font>' : '').'</td><td><select name="'.$var.'">';
	for ($i = 0; $i < $c; $i++) {
		echo '<option value="'.htmlspecialchars($val_list[$i]).'"'.($def == $val_list[$i] ? ' selected' : '').'>'.htmlspecialchars($opt_list[$i]).'</option>';
	}
	echo '</select></td></tr>';
}

function draw_dialog_start($title, $help)
{
?>
<table bgcolor="#000000" align="center" border="0" cellspacing="0" cellpadding="1">
<tr><td><table bgcolor="#FFFFFF" border=0 cellspacing=1 cellpadding=4 align="center">
	<tr><td colspan=2 bgcolor="#e5ffe7"><?php echo $title; ?></td></tr>
	<tr><td colspan=2 bgcolor="#fffee5"><?php echo $help; ?></td></tr>
<?php
}

function draw_dialog_end($section)
{
	if ($section != 'done') {
		echo '<tr bgcolor="#FFFFFF">';
		if ($section != 'stor_path') {
			echo '<td align="left"><input type="button" onClick="history.go(-1)" name="buttn" value="&lt;&lt; Back"></td>';
		} else {
			echo '<td>&nbsp;</td>';
		}
		echo '<td align="right"><input type="submit" name="submit" value="Next &gt;&gt;"></td></tr></table></td></tr></table>';
	}
}

function seterr($name, $text)
{
	$GLOBALS['errors'][$name] = '<font color="#ff0000">'.$text.'</font><br>';
}

function chkslash(&$val)
{
	if (!empty($val)) {
		$last_char = substr($val, -1);
		if ($last_char != '/' && $last_char != '\\') {
			$val .= SLASH;
		}
	}
	return $val;
}

function decompress_archive($data_root, $web_root)
{
	if ($GLOBALS['no_mem_limit']) {
		$size = filesize("./fudforum_archive");
		$fp = fopen("./fudforum_archive", "rb");
		$checksum = fread($fp, 32);
		$tmp = fread($fp, 20000);
		fseek($fp, (ftell($fp) - 20000), SEEK_SET);
		if (strpos($tmp, 'RAW_PHP_OPEN_TAG') !== FALSE) {
			$data = '';
			while (($tmp = fgets($fp, 20000))) {
				$data .= str_replace('RAW_PHP_OPEN_TAG', '<?', $tmp);
			}
		} else {
			$data_len = fread($fp, 10);
			$data = gzuncompress(str_replace('PHP_OPEN_TAG', '<?', fread($fp, $data_len)), $data_len);
		}
		fclose($fp);
	} else {
		$data = file_get_contents(__FILE__);
		$p = strpos($data, "2105111608_\\ARCH_START_HERE") + strlen("2105111608_\\ARCH_START_HERE") + 1;
		$checksum = substr($data, $p, 32);
		$data = substr($data, $p + 32);
		if (strpos($data, 'RAW_PHP_OPEN_TAG') !== FALSE) { /* no compression */
			$data = str_replace('RAW_PHP_OPEN_TAG', '<?', $data);
		} else {
			$data_len = (int) substr($data, 0, 10);
			$data = str_replace('PHP_OPEN_TAG', '<?', substr($data, 10));

			if (!($data = gzuncompress($data, $data_len))) { /* compression */
				exit('Failed decompressing the archive');
			}
		}
	}

	if (md5($data) != $checksum) {
		exit("Archive did not pass checksum test, CORRUPT ARCHIVE!<br>\nIf you've encountered this error it means that you've:<br>\n&nbsp;&nbsp;&nbsp;&nbsp;downloaded a corrupt archive<br>\n&nbsp;&nbsp;&nbsp;&nbsp;uploaded the archive in ASCII and not BINARY mode<br>\n&nbsp;&nbsp;&nbsp;&nbsp;your FTP Server/Decompression software/Operating System added un-needed cartrige return ('\r') characters to the archive, resulting in archive corruption.<br>\n");
	}

	$pos = 0;

	do {
		$end = strpos($data, "\n", $pos+1);
		$meta_data = explode('//',  substr($data, $pos, ($end-$pos)));
		$pos = $end;

		if ($meta_data[3] == '/install' || !isset($meta_data[3])) {
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
				exit('ERROR: file '.$meta_data[1].' was not read properly from archive');
			}

			$fp = @fopen($path, 'wb');
			if (!$fp) {
				exit('Couldn\'t open '.$path.' for write');
			}
				fwrite($fp, $file);
			fclose($fp);

			@chmod($file, 0600);
		} else {
			if (substr($path, -1) == '/') {
				$path = preg_replace('!/+$!', '', $path);
			}
			if (!__mkdir($path)) {
				exit('ERROR: failed creating '.$path.' directory');
			}
		}
	} while (($pos = strpos($data, "\n//", $pos)) !== false);
}

/* win32 does not have symlinks, so we use this crude emulation */
if (!function_exists('symlink')) {
	function symlink($src, $dest)
	{
		if (!($fp = fopen($dest, 'wb'))) {
			return FALSE;
		}
		fwrite($fp, '<?php include_once "'.$src.'"; ?>');
		fclose($fp);
	}
}

function get_server_uid_gid()
{
	if ($GLOBALS['module_status']['posix']) {
		$u = posix_getpwuid(posix_getuid());
		$g = posix_getgrgid($u['gid']);
		return '(' . $u['name'] . '/' . $g['name'] . ')';
	}
	return;
}

function check_perimary_dir($dir, $type)
{
	global $WINDOWS;

	if (!__mkdir($dir)) {
		seterr($type, 'Install script failed to create "'.$dir.'". Create it manually and chmod it 777 or make it\'s user/group same as the web-server '.get_server_uid_gid());
		return 1;
	}
	if (!@is_writable($dir)) {
		seterr($type, 'Directory "'.$dir.'" exist, however install script has no permission to write to this directory. Chmod it 777 or make it\'s user/group same as the '.get_server_uid_gid());
		return 1;
	}
	if (SAFE_MODE) {
		if (($safe = $st = @stat($dir))) {
			if (!ini_get('safe_mode_gid')) {
				$safe = (getmyuid() == $st['uid']);
			} else {
				$safe = (getmygid() == $st['gid']);
			}
		}
		if (!$safe && basename(__FILE__) != 'install.php') {
			seterr($type, 'Safe mode limitation prevents the install script from writing to "'.$dir.'". Please make sure that this directory is owned by the same user/group same as the web-server '.get_server_uid_gid());
			return 1;
		}
	}
	if (open_basedir) {
		if (!$WINDOWS) {
			$dirs = explode(':', open_basedir);
		} else {
			$dirs = explode(';', open_basedir);
		}
		$safe = 1;
		foreach ($dirs as $d) {
			if (!strncasecmp($dir, $d, strlen($d))) {
				$safe = 0;
				break;
			}
		}
		if ($safe) {
			seterr($type, 'open_basedir limitation "'.open_basedir.'" prevents the install script from writing to "'.$dir.'". Please ensure that the specified directory is inside the directories listed in the open_basedir directive');
			return 1;
		}
	}
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

	$section = isset($_POST['section']) ? $_POST['section'] : (isset($_GET['section']) ? $_GET['section'] : '');

	switch ($section) {
		case 'stor_path':
			if (isset($_GET['sfh'])) {
				$_POST['SERVER_ROOT'] = $_GET['SERVER_ROOT'];
				$_POST['SERVER_DATA_ROOT'] = $_GET['SERVER_DATA_ROOT'];
				$_POST['WWW_ROOT'] = $_GET['WWW_ROOT'];
			}
			$SERVER_ROOT = str_replace('\\', '/', $_POST['SERVER_ROOT']);
			$SERVER_DATA_ROOT = str_replace('\\', '/', $_POST['SERVER_DATA_ROOT']);
			$WWW_ROOT = $_POST['WWW_ROOT'];
			if (substr($WWW_ROOT, -1) != '/') {
				$WWW_ROOT .= '/';
			}
			chkslash($SERVER_ROOT);
			chkslash($SERVER_DATA_ROOT);

			$_POST['SERVER_ROOT'] = $SERVER_ROOT;
			$_POST['SERVER_DATA_ROOT'] = $SERVER_DATA_ROOT;
			$_POST['WWW_ROOT'] = $WWW_ROOT;

			$err = check_perimary_dir($SERVER_ROOT, 'SERVER_ROOT');
			if ($SERVER_ROOT != $SERVER_DATA_ROOT) {
				if (!check_perimary_dir($SERVER_DATA_ROOT, 'SERVER_DATA_ROOT') && !$err) {
					$err = 1;
				}
			}

			if (!$err) {
				if (SAFE_MODE && !isset($_GET['sfh'])) {
					$u = umask(0177);
					$s = realpath(__FILE__);
					$d = dirname($s) . '/install_safe.php';
					if (!copy($s, $d)) {
						exit('Failed to copy "'.$s.'" to "'.$d.'"');
					}
					umask($u);
					header('Location: install_safe.php?SERVER_ROOT='.urlencode($SERVER_ROOT).'&SERVER_DATA_ROOT='.urlencode($SERVER_DATA_ROOT).'&WWW_ROOT='.urlencode($WWW_ROOT).'&section=stor_path&sfh=1');
					exit;
				}

				/* try to ensure that SERVER_ROOT resolves to WWW_ROOT */
				if (fud_ini_get('allow_url_fopen')) {
					$check_tm = time();

					$fp = fopen($SERVER_ROOT . 'WWW_ROOT_CHECK', 'wb');
					fwrite($fp, $check_tm);
					fclose($fp);

					if (($d = @file_get_contents($WWW_ROOT . 'WWW_ROOT_CHECK')) != $check_tm) {
						seterr('WWW_ROOT', 'Your WWW_ROOT does not correspond with the SERVER_ROOT path you have specified. (unable to retrive: '.$WWW_ROOT.'WWW_ROOT_CHECK, on disk as: '.$SERVER_ROOT.'WWW_ROOT_CHECK, received data: '.$d.' w/error: ' . $php_errormsg);
					}
					unlink($SERVER_ROOT . 'WWW_ROOT_CHECK');
				}
			}
			if (!isset($GLOBALS['errors'])) {
				$u = umask(0177);
				decompress_archive($SERVER_DATA_ROOT, $SERVER_ROOT);
				/* verify that all the important directories exist (old php bug) */
				$dir_ar = array('include', 'errors', 'messages', 'files', 'template', 'tmp', 'cache', 'errors/.nntp', 'errors/.mlist');
				foreach ($dir_ar as $v) {
					if (!__mkdir($SERVER_DATA_ROOT . $v)) {
						exit('FATAL ERROR: Couldn\'t create "'.$SERVER_DATA_ROOT . $v.'".<br>You can try creating it manually. If you do, be sure to chmod the directory 777.');
					}
				}
				/* determine if this host can support .htaccess directives */
				htaccess_handler($WWW_ROOT, $SERVER_ROOT . '.htaccess');

				$INCLUDE = $SERVER_DATA_ROOT.'include/';
				$ERROR_PATH  = $SERVER_DATA_ROOT.'errors/';
				$MSG_STORE_DIR = $SERVER_DATA_ROOT.'messages/';
				$FILE_STORE = $SERVER_DATA_ROOT.'files/';
				$TMP = $SERVER_DATA_ROOT.'tmp/';
				$FORUM_SETTINGS_PATH = $SERVER_DATA_ROOT.'cache/';

				@chmod($INCLUDE . 'GLOBALS.php', 0600);
				touch($ERROR_PATH . 'FILE_LOCK');

				/* ensure we don't have any bogus symlinks (re-installing over old forum) */
				@unlink($SERVER_ROOT . 'GLOBALS.php');
				@unlink($SERVER_ROOT . 'adm/GLOBALS.php');
				@unlink($SERVER_DATA_ROOT . 'scripts/GLOBALS.php');

				/* make symlinks to GLOBALS.php */
				symlink($INCLUDE . 'GLOBALS.php', $SERVER_ROOT . 'GLOBALS.php');
				symlink($INCLUDE . 'GLOBALS.php', $SERVER_ROOT . 'adm/GLOBALS.php');
				symlink($INCLUDE . 'GLOBALS.php', $SERVER_DATA_ROOT . 'scripts/GLOBALS.php');

				$url_parts = parse_url($WWW_ROOT);
				/* default bitmask values */
				$FUD_OPT_1 = 1744762047;
				if (!$module_status['pspell']) {
					$FUD_OPT_1 ^= 2097152;
				}
				$FUD_OPT_2 = 695676991 | 8388608;

				change_global_settings(array(
					'INCLUDE' => $INCLUDE,
					'ERROR_PATH' => $ERROR_PATH,
					'MSG_STORE_DIR' => $MSG_STORE_DIR,
					'FILE_STORE' => $FILE_STORE,
					'TMP' => $TMP,
					'WWW_ROOT' => $WWW_ROOT,
					'WWW_ROOT_DISK' => $SERVER_ROOT,
					'FORUM_SETTINGS_PATH' => $FORUM_SETTINGS_PATH,
					'COOKIE_NAME' => 'fud_session_'.time(),
					'FUD_OPT_2' => $FUD_OPT_2,
					'FUD_OPT_1' => $FUD_OPT_1,
					'COOKIE_PATH' => $url_parts['path'],
					'DATA_DIR' => $SERVER_DATA_ROOT));

				umask($u);
				$section = 'db';
			}
			break;

		case 'db':
			if (empty($_POST['DBHOST_TBL_PREFIX']) || preg_match('![^[:alnum:]_]!', $_POST['DBHOST_TBL_PREFIX'])) {
				seterr('DBHOST_TBL_PREFIX', 'SQL prefix cannot be empty or contain non A-Za-z0-9_ characters');
			} else {
				/* verify that we can connect to database & validate version @ the same time*/
				initdb();
				if (($r = dbquery('SELECT VERSION()'))) {
					$fetch = $_POST['DBTYPE'] == 'pgsql' ? 'pg_fetch_row' : 'mysql_fetch_row';
					if (preg_match('!((3|4|7)\.([0-9]+)(\.([0-9]+))?)!', @current($fetch($r)), $m)) {
						$version = $m[1];
					} else {
						$version = 0;
					}
					if ($_POST['DBTYPE'] == 'mysql' && !version_compare($version, '3.23.0', '>=')) {
						seterr('DBHOST', 'The specified MySQL server is running version "'.$version.'", which is older then the minimum required version "3.23.0"');
					} else if ($_POST['DBTYPE'] == 'pgsql' && !version_compare($version, '7.2.0', '>=')) {
						seterr('DBHOST', 'The specified PostgreSQL server is running version "'.$version.'", which is older then the minimum required version "7.2.0"');
					}
				}
				if (!isset($GLOBALS['errors'])) {
					dbperms_check();
				}

				if (!isset($GLOBALS['errors'])) {
					$tables = $def_data = array();
					if ($_POST['DBTYPE'] == 'pgsql') {
						$prefix = $_POST['DBHOST_TBL_PREFIX'];
						$preflen = strlen($prefix);

						/* remove possibly conflicting tables */
						$c = dbquery("select relname from pg_class WHERE relkind='r' AND relname LIKE '".str_replace('_', '\\\\_', $prefix)."%'");
						while ($r = pg_fetch_row($c)) {
							if (!strncmp($r[0], $prefix, $preflen)) {
								if (!dbquery('DROP TABLE '.$r[0])) {
									echo dberror();
								}
							}
						}
						unset($c);

						/* remove possibly conflicting sequences */
						$c = dbquery("select relname from pg_class WHERE relkind='S' AND relname LIKE '".str_replace('_', '\\\\_', $prefix)."%'");
						while ($r = pg_fetch_row($c)) {
							if (!strncmp($r[0], $prefix, $preflen)) {
								if (!dbquery('DROP SEQUENCE '.$r[0])) {
									echo dberror();
								}
							}
						}
						unset($c);
					}
					$sql_dir = $_POST['SERVER_DATA_ROOT'] . 'sql/';
					if (!($d = opendir($sql_dir))) {
						exit('ERROR: failed to open SQL table definition directory, "'.$sql_dir.'"');
					}
					$sql_dir .= '/';
					readdir($d); readdir($d);
					while ($f = readdir($d)) {
						switch (strrchr($f, '.')) {
							case '.tbl':
								$tbl[] = $sql_dir . $f;
								break;
							case '.sql':
								$sql[] = $sql_dir . $f;
								break;
						}
					}
					closedir($d);

					/* import tables */
					foreach ($tbl as $t) {
						$data = explode(';', preg_replace('!#.*?\n!s', '', file_get_contents($t)));
						foreach ($data as $q) {
							if ($_POST['DBTYPE'] != 'mysql') {
								if (!strncmp($q, 'DROP TABLE IF EXISTS', strlen('DROP TABLE IF EXISTS'))) {
									continue;
								}
								$q = strtr(array('BINARY'=>'', 'INT NOT NULL AUTO_INCREMENT'=>'SERIAL'), $q);
							}
							if (($q = make_into_query(trim($q)))) {
								if (!dbquery($q)) {
									seterr('DBHOST_DBNAME', 'Failed to create table "'.basename($t, '.tbl').'" ("'.$q.'"), SQL Reason: '.dberror());
									break 2;
								}
							}
						}
					}
					if (!isset($GLOBALS['errors'])) {
						/* import table data */
						foreach ($sql as $t) {
							$data = explode(";\n", file_get_contents($t));
							foreach ($data as $q) {
								if (strpos($q, 'UNIX_TIMESTAMP') !== false) {
									$q = str_replace('UNIX_TIMESTAMP', time(), $q);
								}
								if (($q = make_into_query(trim($q)))) {
									if (!dbquery($q)) {
										seterr('DBHOST_DBNAME', 'Failed to import default data ("'.$q.'") into table '.basename($t, '.sql').', SQL Reason: '.dberror());
										break 2;
									}
								}
							}
						}

						if (!isset($GLOBALS['errors'])) {
							change_global_settings(array(
								'DBHOST' => $_POST['DBHOST'],
								'DBHOST_USER' => $_POST['DBHOST_USER'],
								'DBHOST_PASSWORD' => $_POST['DBHOST_PASSWORD'],
								'DBHOST_DBNAME' => $_POST['DBHOST_DBNAME'],
								'DBHOST_TBL_PREFIX' => $_POST['DBHOST_TBL_PREFIX']
							));
							$section = 'cookies';
						}
					}
				}
			}
			break;

		case 'cookies':
			if (empty($_POST['COOKIE_DOMAIN'])) {
				seterr('COOKIE_DOMAIN', 'You must enter a cookie domain in order for cookies to work properly.');
			} else {
				 change_global_settings(array('COOKIE_DOMAIN' => $_POST['COOKIE_DOMAIN']));
				 $section = 'language';
			}
			break;

		case 'language':
			list($la, $lc, $lp) = explode('::', $_POST['LANGUAGE']);
			initdb();
			if (!dbquery("INSERT INTO ".$_POST['DBHOST_TBL_PREFIX']."themes(id, name, theme, lang, locale, theme_opt, pspell_lang) VALUES(1, 'default', 'default', '".addslashes($la)."', '".addslashes($lc)."', 3, '".addslashes($lp)."')")) {
				echo dberror();
			} else {
				$section = 'admin';
			}
			break;

		case 'admin':
			if (empty($_POST['ROOT_PASS'])) {
				seterr('ROOT_PASS', 'You must enter a password for the administrator account.');
			} else if ($_POST['ROOT_PASS'] != $_POST['ROOT_PASS_C']) {
				seterr('ROOT_PASS', 'Your passwords do not match.');
			}
			if (empty($_POST['ROOT_LOGIN'])) {
				seterr('ROOT_LOGIN', 'You must enter a user name for the administrator account.');
			}
			if (empty($_POST['ADMIN_EMAIL'])) {
				seterr('ADMIN_EMAIL', 'You must enter a valid email address for the administrator account.');
			}

			if(!isset($GLOBALS['errors'])) {
				initdb();
				if (!dbquery("INSERT INTO ".$_POST['DBHOST_TBL_PREFIX']."users (login, alias, passwd, name, email, users_opt, join_date, theme) VALUES('".addslashes($_POST['ROOT_LOGIN'])."', '".addslashes(htmlspecialchars($_POST['ROOT_LOGIN']))."', '".md5($_POST['ROOT_PASS'])."', 'Administrator', '".addslashes($_POST['ADMIN_EMAIL'])."', 5405687, ".time().", 1)")) {
					seterr('ROOT_LOGIN', dberror());
				} else {
					change_global_settings(array(
						'ADMIN_EMAIL' => addslashes($_POST['ADMIN_EMAIL']),
						'NOTIFY_FROM' => addslashes($_POST['ADMIN_EMAIL'])
						));
					$section = 'done';
				}
			}
			break;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<body bgcolor="white">
<form name="install" action="<?php echo basename(__FILE__) . '?' . rand(); ?>" method="post">
<?php
	if (!$section) {
		$section = 'stor_path';
	}

	switch ($section) {
		case 'stor_path':
			if (count($_POST)) {
				$WWW_ROOT = $_POST['WWW_ROOT'];
				$SERVER_ROOT = $_POST['SERVER_ROOT'];
				$SERVER_DATA_ROOT = $_POST['SERVER_DATA_ROOT'];
			} else {
				$SERVER_ROOT = dirname(realpath(__FILE__)) . '/';
				$WWW_ROOT = 'http://' . $_SERVER['SERVER_NAME'];
				if (($d = dirname($_SERVER['SCRIPT_NAME']))) {
					$WWW_ROOT .= dirname($_SERVER['SCRIPT_NAME']);
					if ($d != '/') {
						$WWW_ROOT .= '/';
					}
				}
				$SERVER_DATA_ROOT = realpath(str_replace(dirname($_SERVER['SCRIPT_NAME']) . '/', '', $SERVER_ROOT) . '/../') . '/FUDforum/';
				if (open_basedir && strpos(open_basedir, $SERVER_DATA_ROOT) === FALSE) {
					$SERVER_DATA_ROOT = $SERVER_ROOT;
				}
			}

			if (!SAFE_MODE) {
				draw_dialog_start('PATH OF SYSTEM FILES AND DIRECTORIES&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 1 of 5</b></font> ', 'First, you need to specify the directories where the forum files will be stored.  In order for the forum installation to work you need to chmod the directories <b>Server Root</b> &amp; <b>Forum Data Root</b> in such a way that the webserver can write to them. I suggest chmoding the directories to 777.<br>
			If you have shell access, you can change the directory permission by typing "<b>chmod 777 directory_name</b>"<br>
			In CuteFTP, you can chmod a directory by selecting it and then pressing Ctrl+Shift+A. In the Manual checkbox, enter 777 and then press OK.<br>
			In WS_FTP, right-click on the directory and choose the chmod UNIX option. In the dialog, select all the checkboxes and click OK. This will chmod the directory 777.<br>');
			} else {
				draw_dialog_start('<div align=middle><font color=red><b>SAFEMODE is ENABLED!</b></font></div><br>PATH OF SYSTEM FILES AND DIRECTORIES&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 1 of 5</b></font>',
					'
					Your PHP has <b><font color=red>SAFE MODE</font></b> enabled. Pay careful attention to the intructions below:<br><br>
					Due to the brain dead nature of PHP\'s safemode we <font color=red>can not</font> install the forum in a directory
					created by you. Therefor you must install the forum into a directory, which <font color=red>does not yet exist</font>, so that
					the install script can be the one to create it and thus bypass the safe_mode checks.<br>For example, if you wanted to install
					your forum to "/my/home/dir/www/forum", you will need to make sure that "/my/home/dir/www/forum" does not exist and that the
					file permissions of "/my/home/dir/www" allow install script to create "forum" directory inside "/my/home/dir/www".

					');

				if (!count($_POST)) {
					$WWW_ROOT .= 'forum/';
					$SERVER_ROOT .= 'forum/';
					$SERVER_DATA_ROOT = $SERVER_ROOT;
				}
			}

			draw_row('Server Root', 'SERVER_ROOT', $SERVER_ROOT, 'The path on the server where the browseable files of the forum (*.php) will be stored.');
			draw_row('Forum Data Root', 'SERVER_DATA_ROOT', $SERVER_DATA_ROOT, 'The path on the server where the <b>NON-</b>browseable files of the forum will be stored.');
			draw_row('Forum WWW Root', 'WWW_ROOT', $WWW_ROOT, 'This is the URL of your forum, and should point to the forum\'s front page.  This is also the URL people will need to use to get to your forum.');
			break;

		case 'db':
			draw_dialog_start('Database Settings&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 2 of 5</b></font>', 'FUDforum uses the database to store much of the data used in the forum. Please use the form below to enter information that will allow FUDforum to access the database. It is recommended you create a separate database for the forum.');

			if ($module_status['mysql'] && $module_status['pgsql']) {
				draw_row_sel('Database Type','DBTYPE', "MySQL\nPostgreSQL", "mysql\npgsql", '', (isset($_POST['DBTYPE']) ? $_POST['DBTYPE'] : 'mysql'));
			} else {
				echo '<input type="hidden" name="DBTYPE" value="'.($module_status['mysql'] ? 'mysql' : 'pgsql').'">';
			}

			if (isset($_POST['DBHOST'])) {
				$DBHOST = $_POST['DBHOST'];
				$DBHOST_USER = $_POST['DBHOST_USER'];
				$DBHOST_PASSWORD = $_POST['DBHOST_PASSWORD'];
				$DBHOST_DBNAME = $_POST['DBHOST_DBNAME'];
				$DBHOST_TBL_PREFIX = $_POST['DBHOST_TBL_PREFIX'];
			} else {
				$DBHOST = $DBHOST_USER = $DBHOST_PASSWORD = $DBHOST_DBNAME = '';
				$DBHOST_TBL_PREFIX = 'fud26_';
			}

			draw_row('Host', 'DBHOST', $DBHOST, 'The IP address (or unix domain socket) of the database server.');
			draw_row('User', 'DBHOST_USER', $DBHOST_USER, 'The user name for the database you intend to use the system with.');
			draw_row('Password', 'DBHOST_PASSWORD', $DBHOST_PASSWORD, 'The password for the user name.');
			draw_row('Database', 'DBHOST_DBNAME', $DBHOST_DBNAME, 'The name of the database where forum data will be stored.');
			draw_row('FUDforum SQL Table Prefix', 'DBHOST_TBL_PREFIX', $DBHOST_TBL_PREFIX, 'A string of text that will be appended to each table name.');
			break;

		case 'cookies':
			if (isset($_POST['COOKIE_DOMAIN'])) {
				$COOKIE_DOMAIN = $_POST['COOKIE_DOMAIN'];
			} else {
				$url_parts = parse_url($_POST['WWW_ROOT']);
				$COOKIE_DOMAIN = preg_replace('!^www\.!i', '.', $url_parts['host']);
			}

			draw_dialog_start('Cookie Domain&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 3 of 5</b></font>', 'The domain of the cookie that will be used by the forum.');
			draw_row('Cookie Domain', 'COOKIE_DOMAIN', $COOKIE_DOMAIN);
			break;

		case 'language':
			draw_dialog_start('Forum Language&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 4 of 5</b></font>', 'Choose the language for your forum.<br><font size="-1">If the language you require is not avaliable, please go to <a href="http://fud.prohost.org/forum/" target="_new">FUDforum\'s website</a> and read about translating the forum to other languages.</font>');
			$path = $_POST['SERVER_DATA_ROOT'] . '/thm/default/i18n';
			$d = opendir($path);
			readdir($d); readdir($d);
			$selnames = $selopts = '';
			$path .= '/';
			while ($f = readdir($d)) {
				if ($f == 'CVS' || !@is_dir($path . $f)) {
					continue;
				}
				$selnames .= $f . "\n";
				$selopts .= $f . '::' . @trim(file_get_contents($path . $f . '/locale')) . '::' . @trim(file_get_contents($path . $f . '/pspell_lang')) . "\n";
			}
			closedir($d);
			draw_row_sel('Language', 'LANGUAGE', rtrim($selnames), rtrim($selopts), NULL, 'english::english::en');
			break;

		case 'admin':
			draw_dialog_start('Admin Account&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 5 of 5</b></font>', 'This creates the "root" user account, which is an unrestricted account that can do anything on the forum. You must use this account to edit &amp; customize the forum.');

			if (!isset($_POST['ROOT_LOGIN'])) {
				$ROOT_LOGIN = 'admin';
				$ROOT_PASS = $ROOT_PASS_C = '';
				$ADMIN_EMAIL = get_current_user() . '@' . $_SERVER['SERVER_NAME'];
			} else {
				$ROOT_LOGIN = $_POST['ROOT_LOGIN'];
				$ROOT_PASS = $_POST['ROOT_PASS'];
				$ROOT_PASS_C = $_POST['ROOT_PASS_C'];
				$ADMIN_EMAIL = $_POST['ADMIN_EMAIL'];
			}

			draw_row('Login Name', 'ROOT_LOGIN', $ROOT_LOGIN);
			draw_row('Admin Password', 'ROOT_PASS', $ROOT_PASS);
			draw_row('Confirm Password', 'ROOT_PASS_C', $ROOT_PASS_C);
			draw_row('Admin Email', 'ADMIN_EMAIL', $ADMIN_EMAIL);
			break;

		case 'done':
			$GLOBALS['WWW_ROOT_DISK']	= $_POST['SERVER_ROOT'];
			$GLOBALS['DATA_DIR'] 		= $_POST['SERVER_DATA_ROOT'];
			$GLOBALS['INCLUDE'] 		= $_POST['SERVER_DATA_ROOT'] . '/include/';
			$GLOBALS['WWW_ROOT'] 		= $_POST['WWW_ROOT'];
			$GLOBALS['DBHOST_TBL_PREFIX']	= $_POST['DBHOST_TBL_PREFIX'];
			$GLOBALS['FUD_OPT_2'] = 8388608;
			define('__dbtype__', $_POST['DBTYPE']);
			$lang = strtok($_POST['LANGUAGE'], '::');

			require($_POST['SERVER_DATA_ROOT'] . 'include/compiler.inc');

			compile_all('default', $lang);

			draw_dialog_start('Installation Complete', 'You have now completed the basic installation of the forum. To continue configuring your forum, you must login and use the administrator control panel.
				Clicking "Finished" will take you to the login form.  After you login, you will be taken to the administrator control panel.<br>
				<font color="#ff0000">Before you do, however, you must delete this <b>install.php</b> script, because it can be used to overwrite your forum.  You will not be able to login until you do.</font>
			');

			/* Remove the install_safe for safe_mode users, because they will not be able to remove it themselves */
			if (SAFE_MODE) {
				unlink(__FILE__);
			}
			if ($no_mem_limit) {
				unlink("./fudforum_archive");
			}

			echo ('<tr><td colspan=2 align="center"><input type="submit" name="submit" value="Finished" onClick="javascript: window.location=\''.$_POST['WWW_ROOT'].'index.php?t=login&adm=1\'; return false;"></td></tr></table></td></tr></table></form></html>');
			break;
	}
	draw_dialog_end($section);

	/* display some system information on the 1st page of the installer */
	if ($section == 'stor_path') {
?>
	<br><table bgcolor="#000000" align="center" border="0" cellspacing="0" cellpadding="1">
	<tr><td><table bgcolor="#FFFFFF" border=0 cellspacing=1 cellpadding=4 align="center">
		<th align="left" colspan=2 bgcolor="#e5ffe7">System Information</th>
		<tr bgcolor="#fffee5"><td><b>PHP Version:</b></td><td><?php echo PHP_VERSION; ?></td></tr>
<?php
	if (open_basedir) {
		echo '<tr bgcolor="#fffee5"><td><b>Open_basedir restriction:</b><br><font size="-1">You will not be able to use PHP to create files outside of the specified directories.</font></td><td>'.open_basedir.'</td></tr>';
	}
	if (fud_ini_get('register_globals')) {
		echo '<tr bgcolor="#fffee5"><td><b>Register Globals:</b><br><font size="-1">For performance &amp; security reasons we recommend keeping this option OFF.</font></td><td><font color="green">enabled</font></td></tr>';
	}
	if ($magic_guotes_gpc) {
		echo '<tr bgcolor="#fffee5"><td><b>Magic quotes gpc:</b><br><font size="-1">For performance reasons we recommend keeping this option OFF.</font></td><td><font color="green">enabled</font></td></tr>';
	}
?>
		<tr bgcolor="#fffee5"><td><b>MySQL Extension:</b><br><font size="-1">Interface to the MySQL server, which is the recommended database for FUDforum.</font></td><td><?php echo ($module_status['mysql'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>
		<tr bgcolor="#fffee5"><td><b>PostgreSQL Extension:</b><br><font size="-1">Interface to the PostgreSQL server.</font></td><td><?php echo ($module_status['pgsql'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>
		<tr bgcolor="#fffee5"><td><b>PCRE Extension:</b><br><font size="-1">Perl Compatible Regular Expression (required).</font></td><td><?php echo ($module_status['pcre'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>

		<tr bgcolor="#fffee5"><td><b>Zlib Extension:</b><br><font size="-1">zlib extension is optional, however we recommend enabling it. This extension allow you to compress your forum backups as well as use zlib compression for your pages.</font></td><td><?php echo ($module_status['zlib'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>
		<tr bgcolor="#fffee5"><td><b>Pspell Extension:</b><br><font size="-1">Pspell extension is optional, this extension is needed by the FUDforum's built-in spellchecker. If you want to allow users to spell check their messages, enable this extension.</font></td><td><?php echo ($module_status['pspell'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>
		<tr bgcolor="#fffee5"><td><b>Tokenizer Extension:</b><br><font size="-1">Tokenizer extension is optional, if enabled, it will allow you to optimize the compiled themes.</font></td><td><?php echo ($module_status['tokenizer'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>
		<tr bgcolor="#fffee5"><td><b>PDF Extension:</b><br><font size="-1">PDF extension is optional, if enabled, users will be able to generate PDF files based on the forum data.</font></td><td><?php echo ($module_status['pdf'] ? '<font color="green">enabled</font>' : '<font color="red">disabled</font>'); ?></td></tr>
	</table</td></tr></table>
<?php
	}

	echo '<input type="hidden" name="section" value="'.$section.'">';

	if (isset($_POST['WWW_ROOT']) && $section != 'stor_path') {
		echo '<input type="hidden" name="WWW_ROOT" value="'.$_POST['WWW_ROOT'].'"><input type="hidden" name="SERVER_DATA_ROOT" value="'.$_POST['SERVER_DATA_ROOT'].'"><input type="hidden" name="SERVER_ROOT" value="'.$_POST['SERVER_ROOT'].'">';
	}
	if (isset($_POST['LANGUAGE'])) {
		echo '<input type="hidden" name="LANGUAGE" value="'.$_POST['LANGUAGE'].'">';
	}

	switch ($section) {
		case 'cookies':
		case 'language':
		case 'admin':
		case 'done':
			echo '	<input type="hidden" name="DBHOST" value="'.$_POST['DBHOST'].'">
				<input type="hidden" name="DBHOST_PASSWORD" value="'.$_POST['DBHOST_PASSWORD'].'">
				<input type="hidden" name="DBHOST_USER" value="'.$_POST['DBHOST_USER'].'">
				<input type="hidden" name="DBHOST_DBNAME" value="'.$_POST['DBHOST_DBNAME'].'">
				<input type="hidden" name="DBTYPE" value="'.$_POST['DBTYPE'].'">
				<input type="hidden" name="DBHOST_TBL_PREFIX" value="'.$_POST['DBHOST_TBL_PREFIX'].'">';
			break;
	}
?>
</form>
</body>
</html>
<?php exit; ?>
2105111608_\ARCH_START_HERE
