<?php
/* ���ظ ���� )�)����  	
 * First 20 bytes of linux 2.4.18, so various windows utils think
 * this is a binary file and don't apply CR/LF logic
 */

/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: install.php,v 1.2 2002/06/18 18:28:41 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	if( !ini_get("track_errors") ) ini_set("track_errors", 1);
	if( !ini_get("display_errors") ) ini_set("display_errors", 1);
	
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set("memory_limit", "20M");
	ignore_user_abort(true);
	@set_time_limit(600);
	
	$SAFE_MODE = ini_get("safe_mode");
	$SLASH = ( !empty($GLOBALS['HTTP_ENV_VARS']['OS']) && preg_match('!win!i', $GLOBALS['HTTP_ENV_VARS']['OS']) ) ? '\\\\' : '/';

	if( !isset($HTTP_SERVER_VARS['PATH_TRANSLATED']) && isset($HTTP_SERVER_VARS['SCRIPT_FILENAME']) ) 
		$HTTP_SERVER_VARS['PATH_TRANSLATED'] = $GLOBALS['HTTP_SERVER_VARS']['PATH_TRANSLATED'] = $HTTP_SERVER_VARS['SCRIPT_FILENAME'];
	
	
function mod_arr_val(&$val, $key, $fmt_func)
{
	if( !is_array($val) ) {
		if( isset($GLOBALS[$key]) ) return;
		
		if( $fmt_func ) $val = $fmt_func($val);
		$GLOBALS[$key] = $val;
	}
	else {	
		reset($val);
		while( list($k,$v) = each($val) ) {
			if( !isset($GLOBALS[$key]) ) $GLOBALS[$key][$k] = $fmt_func ? $fmt_func($v) : $v;
		}
		reset($val);
	}
}

if( !ini_get('register_globals') || !ini_get('magic_quotes_gpc') ) {

function gpc_fmt($str)
{
	return addslashes($str);
}
	$fmt_func = ini_get('magic_quotes_gpc') ? '' : 'gpc_fmt'; 

	reset($GLOBALS['HTTP_GET_VARS']);
	array_walk($GLOBALS['HTTP_GET_VARS'], 'mod_arr_val', $fmt_func);
	reset($GLOBALS['HTTP_GET_VARS']);

	reset($GLOBALS['HTTP_POST_VARS']);
	array_walk($GLOBALS['HTTP_POST_VARS'], 'mod_arr_val', $fmt_func);
	reset($GLOBALS['HTTP_POST_VARS']);
	
	reset($GLOBALS['HTTP_POST_FILES']);
	while ( list($k, $v) = each($GLOBALS['HTTP_POST_FILES']) ) {
		while ( list($k2, $v2) = each($v) ) {
			$GLOBALS[$k.'_'.$k2] = !$fmt_func ? $v2 : $fmt_func($v2);
		}
		$GLOBALS[$k] = $v['tmp_name'];
	}
	reset($GLOBALS['HTTP_POST_FILES']);
	unset($k); unset($v); unset($k2); unset($v2);

	reset($GLOBALS['HTTP_COOKIE_VARS']);
	array_walk($GLOBALS['HTTP_COOKIE_VARS'], 'mod_arr_val', $fmt_func);
	reset($GLOBALS['HTTP_COOKIE_VARS']);
}

	if( empty($GLOBALS["HTTP_SERVER_VARS"]["DOCUMENT_ROOT"]) ) $GLOBALS["HTTP_SERVER_VARS"]["DOCUMENT_ROOT"] = preg_replace('!\\\\[^\\\\]*$!', '\\\\', $GLOBALS["HTTP_SERVER_VARS"]["PATH_TRANSLATED"]);
function filetomem($fn)
{
	$fp = fopen($fn, 'rb');
	$st = fstat($fp);
	$size = isset($st['size']) ? $st['size'] : $st[7];
	$str = fread($fp, $size);
	fclose($fp);
	
	return $str;
}

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

function __mkdir($dir)
{
	if( @is_dir($dir) ) return 1;
	
	if( !($ret = mkdir($dir, 0700)) ) $ret = mkdir(dirname($dir),0700);
	return $ret;
}

function draw_row($title, $var, $def, $descr=NULL)
{
	echo '<tr bgcolor="#bff8ff"><td valign="top"><b>'.$title.'</b>'.(($descr)?'<br><font size=-1>'.$descr.'</font>':'').'</td><td>'.(!empty($GLOBALS["errors"][$var])?$GLOBALS["errors"][$var]:'').'<input type="text" name="'.$var.'" value="'.htmlspecialchars(stripslashes($def)).'" size=40></td></tr>';
}

function draw_row_sel($title, $var, $opt_list, $val_list, $descr=NULL, $def=NULL)
{
	$val_list = explode("\n", $val_list);
	$opt_list = explode("\n", $opt_list);

	if( count($val_list) != count($opt_list) ) 
		exit("Value list does not match option count<br>\n");

	echo '<tr bgcolor="#bff8ff"><td valign="top"><b>'.$title.'</b>'.(($descr)?'<br><font size=-1>'.$descr.'</font>':'').'</td><td><select name="'.$var.'">';
	for( $i=0; $i<count($val_list); $i++ ) {
		$sel = ( $def == $val_list[$i] ) ? ' selected' : '';
		echo '<option value="'.htmlspecialchars($val_list[$i]).'"'.$sel.'>'.htmlspecialchars($opt_list[$i]).'</option>';
	}
	echo '</select></td></tr>';
}

function draw_dialog_start($title, $help)
{
	echo '
	<table bgcolor="#000000" align="center" border="0" cellspacing="0" cellpadding="1">
		<tr>
			<td>
				<table bgcolor="#FFFFFF" border=0 cellspacing=1 cellpadding=4 align="center">
					<tr><td colspan=2 bgcolor="#e5ffe7">'.$title.'</td></tr>
					<tr><td colspan=2 bgcolor="#fffee5">'.$help.'</td></tr>
	';
}

function draw_dialog_end()
{	
	echo '<tr bgcolor="#FFFFFF">
			'.($GLOBALS["section"]!='stor_path'?'<td align="left"><input type="button" onClick="history.go(-1)" name="buttn" value="&lt;&lt; Back"></td>':'<td>&nbsp;</td>').'
			<td align="right"><input type="submit" name="submit" value="Next &gt;&gt;"></td>
	</tr></table></td></tr></table>';			
}

function IFSTR($val, $alt)
{
	return (empty($alt))?$val:$alt;
}

function seterr($name, $text)
{
	$GLOBALS["errors"][$name] = '<font color="#ff0000">'.$text.'</font><br>';
}

function chkslash(&$val)
{
	if ( !empty($val) ) {
		$last_char = substr($val,-1);
		if( $last_char != '/' && $last_char != '\\' ) $val .= $GLOBALS['SLASH'];
	}
	return $val;
}

function decompress_archive($data_root, $web_root)
{
	$data = filetomem($GLOBALS['HTTP_SERVER_VARS']['PATH_TRANSLATED']);

	$pos = strpos($data, "2105111608_\\ARCH_START_HERE");
	if( $pos === false ) 
		exit("Couldn't locate start of archive<br>\n");
	
	$data = substr($data, $pos+strlen("2105111608_\\ARCH_START_HERE"));
	$data = base64_decode($data);
	
	$pos=0;
	
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
			
			$fp = @fopen($path, 'wb');
			if( !$fp ) exit("Couldn't open $path for write<br>\n");
				fwrite($fp, $file);
			fclose($fp);
			
			@chmod($file, 0600);
		}
		else {
			if( substr($path, -1) == '/' ) $path = preg_replace('!/+$!', '', $path);
			if( !@is_dir($path) && !__mkdir($path) ) 
				exit("ERROR: failed creating $path directory<br>\n");
		}
	}
}

function php_which($prg)
{
	if( $GLOBALS["HTTP_SERVER_VARS"]["PATH"][0] == '/' ) 
		$sep = ':';
	else
		$sep = ';';
		
	$path = explode($sep, $GLOBALS["HTTP_SERVER_VARS"]["PATH"]);
	
	while( list(,$v) = each($path) ) {
		chkslash($v);
		if( @file_exists($v.$prg) ) return $v.$prg;
	}
	
	return;
}

function make_into_query(&$data)
{
	global $MYSQL_TBL_PREFIX;

	$data = preg_replace('!\#.*$!s', '', $data);
	$data = preg_replace('! +!', ' ', $data);
	$data = trim(str_replace('{SQL_TABLE_PREFIX}', $MYSQL_TBL_PREFIX, $data));
}

function make_windows_link($src, $dest)
{
	$fp = fopen($dest, 'wb');
		fwrite($fp, '<?php include_once "'.$src.'"; ?>');	
	fclose($fp);
}

switch ( $section ) 
{
	case "stor_path":
		
		$SERVER_ROOT = str_replace('\\', '/', $SERVER_ROOT);
		chkslash($SERVER_ROOT);
		chkslash($SERVER_DATA_ROOT);
		
		if ( $SAFE_MODE && !$HTTP_GET_VARS['sfh'] ) {
			$oldumask = umask(0);
			if ( $st=@stat($SERVER_ROOT) ) {
				$srvuid = posix_geteuid();
				if ( $st[4] != $srvuid ) {
					seterr('SERVER_ROOT', 
					'
					The directory you have specified (<b>'.$SERVER_ROOT.'</b>) already exists
					and is not owned by the web server.<br><br>Choose another directory
					');
					$err = 1;
				}
			}
			else {
				if ( !mkdir(substr($SERVER_ROOT, 0, -1), 0755) ) {
					seterr('SERVER_ROOT', 
					'
					Unable to create (<b>'.$SERVER_ROOT.'</b>),
					Please make sure the parent directory is chmoded 777
					');
					$err = 1;
				}
			}
			
			if ( $st=@stat($SERVER_DATA_ROOT) ) {
				$srvuid = posix_geteuid();
				if ( $st[4] != $srvuid ) {
					seterr('SERVER_DATA_ROOT', 
					'
					The directory you have specified (<b>'.$SERVER_DATA_ROOT.'</b>) already exists
					and is not owned by the web server.<br><br>Choose another directory
					');
					$err = 1;
				}
			}
			else {
				if ( !mkdir(substr($SERVER_DATA_ROOT, 0, -1), 0755) ) {
					seterr('SERVER_DATA_ROOT', 
					'
					Unable to create (<b>'.$SERVER_DATA_ROOT.'</b>),
					Please make sure the parent directory is chmoded 777
					');
					$err = 1;
				}
			}
			
			if( !$err ) {
				umask(0177);
				copy("install.php", "install_safe.php");
				umask($oldumask);
				header("Location: install_safe.php?SERVER_ROOT=".urlencode($SERVER_ROOT)."&SERVER_DATA_ROOT=".urlencode($SERVER_DATA_ROOT)."&WWW_ROOT=".urlencode($WWW_ROOT)."&section=stor_path&sfh=1");
				exit();
			}
			umask($oldumask);
		}
		else {
		if( @!is_dir($SERVER_ROOT) )
			seterr('SERVER_ROOT', 'The directory where web browseable files are to be kept does not exist. Please create it.');
		else if( @!is_writable($SERVER_ROOT) )
			seterr('SERVER_ROOT', 'The webserver does not have write permissions to this directory. Please chmod it 1777 or 777.');
		else if ( !$HTTP_GET_VARS['sfh'] ) {
			$check_tm = time();
		
			$fp = fopen($SERVER_ROOT.'WWW_ROOT_CHECK', 'wb');
				fwrite($fp, $check_tm);
			fclose($fp);
		
			$url_data = parse_url($WWW_ROOT);
			if( empty($url_data['port']) ) $url_data['port'] = 80;
			
			if( !($fs = fsockopen($url_data['host'], $url_data['port'], $errno, $errstr, 10)) ) {
				echo "<br>\nWARNING: Couldn't connect to ".$url_data['host']." on port ".$url_data['port']."<br>\nSocket Error #: $errno<br>Socket Error Reason: $errstr<br>\n";
				@unlink($SERVER_ROOT.'WWW_ROOT_CHECK');
			}
			else {
				fwrite($fs, "GET ".$url_data['path']."WWW_ROOT_CHECK HTTP/1.0\r\nHost: ".$url_data['host']."\r\n\r\n");
				if( function_exists("socket_set_timeout") ) @socket_set_timeout($fs, 10);
				$ret_code = fgets($fs, 1024);
				fclose($fs);
				@unlink($SERVER_ROOT.'WWW_ROOT_CHECK');
		
				if( !strstr($ret_code, "200") ) 
					seterr('WWW_ROOT', 'Your WWW_ROOT does not correspond with the SERVER_ROOT path you have specified. (unable to retrive: '.$WWW_ROOT.'WWW_ROOT_CHECK, on disk as: '.$SERVER_ROOT.'WWW_ROOT_CHECK, received: '.$ret_code);
			}
		}	
		
		if( @!is_dir($SERVER_DATA_ROOT) )
			seterr('SERVER_DATA_ROOT', 'The directory where forum data files are to be kept does not exist. Please create it.');
		else if( @!is_writable($SERVER_DATA_ROOT) )
			seterr('SERVER_DATA_ROOT', 'The webserver does not have write permissions to this directory. Please chmod it 1777 or 777.');
	
		if( empty($GLOBALS["errors"]) ) {
			$oldumask = umask(0);
	
			decompress_archive($SERVER_DATA_ROOT, $SERVER_ROOT);

			if( $HTTP_GET_VARS['sfh'] ) { /* Cause PHP Core Developers are retards!!!! */
				$pwd = getcwd();
				$dir_ar = array('include', 'errors', 'messages', 'files', 'template', 'tmp', 'cache');
				
				chdir($SERVER_DATA_ROOT);
				while( list(,$v) = each($dir_ar) ) __mkdir($v);
				@chdir($pwd);
			}

			$INCLUDE = $SERVER_DATA_ROOT.'include/';
			$ERROR_PATH  = $SERVER_DATA_ROOT.'errors/';
			$MSG_STORE_DIR = $SERVER_DATA_ROOT.'messages/';
			$FILE_STORE = $SERVER_DATA_ROOT.'files/';
			$TMP = $SERVER_DATA_ROOT.'tmp/';
			$FORUM_SETTINGS_PATH = $SERVER_DATA_ROOT.'cache/';
			$MOGRIFY_BIN = php_which('mogrify');
			
			if( !@is_dir($INCLUDE) && !__mkdir($INCLUDE) ) 
				exit("FATAL ERROR: Couldn't create INCLUDE ($INCLUDE) directory.<br>You can try creating it manually. If you do, be sure to chmod the directory 777.\n");
			if( !@is_dir($ERROR_PATH) && !__mkdir($ERROR_PATH) ) 
				exit("FATAL ERROR: Couldn't create ERRORS ($ERROR_PATH) directory.<br>You can try creating it manually. If you do, be sure to chmod the directory 777.\n");
			if( !@is_dir($MSG_STORE_DIR) && !__mkdir($MSG_STORE_DIR) ) 
				exit("FATAL ERROR: Couldn't create MSG_STORE_DIR ($MSG_STORE_DIR) directory.<br>You can try creating it manually. If you do, be sure to chmod the directory 777.\n");
			if( !@is_dir($FILE_STORE) && !__mkdir($FILE_STORE) ) 
				exit("FATAL ERROR: Couldn't create FILE_STORE ($FILE_STORE) directory.<br>You can try creating it manually. If you do, be sure to chmod the directory 777.\n");
			if( !@is_dir($TMP) && !__mkdir($TMP) ) 
				exit("FATAL ERROR: Couldn't create TMP ($TMP) directory.<br>You can try creating it manually. If you do, be sure to chmod the directory 777.\n");
			if( !@is_dir($FORUM_SETTINGS_PATH) && !__mkdir($FORUM_SETTINGS_PATH) ) 
				exit("FATAL ERROR: Couldn't create FORUM_SETTINGS_PATH ($FORUM_SETTINGS_PATH) directory.<br>You can try creating it manually. If you do, be sure to chmod the directory 777.\n");
			
			chmod($INCLUDE.'GLOBALS.php', 0600);
		
			if( @is_link($SERVER_ROOT.'GLOBALS.php') ) unlink($SERVER_ROOT.'GLOBALS.php');
			if( @is_link($SERVER_ROOT.'adm/GLOBALS.php') ) unlink($SERVER_ROOT.'adm/GLOBALS.php');
			
			if( !@symlink($INCLUDE.'GLOBALS.php', $SERVER_ROOT.'GLOBALS.php') && !@is_link($SERVER_ROOT.'GLOBALS.php') ) 
				make_windows_link($INCLUDE.'GLOBALS.php', $SERVER_ROOT.'GLOBALS.php');
			
			if( !@symlink($INCLUDE.'GLOBALS.php', $SERVER_ROOT.'adm/GLOBALS.php') && !@is_link($SERVER_ROOT.'adm/GLOBALS.php') ) 
				make_windows_link($INCLUDE.'GLOBALS.php', $SERVER_ROOT.'adm/GLOBALS.php');
			
			$url_parts = parse_url($WWW_ROOT);
			
			$GLOBALS['__GLOBALS.INC__'] = $INCLUDE.'GLOBALS.php';
			$global_config = read_global_config();
			change_global_val('INCLUDE', $INCLUDE, $global_config);
			change_global_val('ERROR_PATH', $ERROR_PATH, $global_config);
			change_global_val('MSG_STORE_DIR', $MSG_STORE_DIR, $global_config);
			change_global_val('FILE_STORE', $FILE_STORE, $global_config);
			change_global_val('TMP', $TMP, $global_config);
			change_global_val('WWW_ROOT', $WWW_ROOT, $global_config);
			change_global_val('WWW_ROOT_DISK', $SERVER_ROOT, $global_config);
			change_global_val('MOGRIFY_BIN', $MOGRIFY_BIN, $global_config);
			change_global_val('FORUM_SETTINGS_PATH', $FORUM_SETTINGS_PATH, $global_config);
			change_global_val('COOKIE_NAME', 'fud_session_'.time(), $global_config);
			change_global_val('SPELL_CHECK_ENABLED', (function_exists('pspell_new_config') ? 'Y' : 'N'), $global_config);
			change_global_val('COOKIE_PATH', $url_parts['path'], $global_config);
			change_global_val('DATA_DIR', $SERVER_DATA_ROOT, $global_config);
			write_global_config($global_config);
			
			@touch($ERROR_PATH.'FILE_LOCK');
			
			umask($oldumask);
		
			$section = 'mysql';	
		}
		}
		break;
	case "mysql" :
		if( @!mysql_connect($MYSQL_SERVER, $MYSQL_LOGIN, $MYSQL_PASSWORD) ) {
			seterr('MYSQL_SERVER', 'Failed to connect to the MySQL Server, SQL Reason: '.mysql_error());
		}
		else if( @!mysql_select_db($MYSQL_DB) ) {
			seterr('MYSQL_DB', 'Could not open the database you\'ve specified, SQL Reason: '.mysql_error());
		}

		if( empty($GLOBALS["errors"]) ) {
			$tables = $def_data = array();
			
			$olddir = getcwd();
			if( !@chdir($SERVER_DATA_ROOT.'sql/') ) 
				exit("ERROR: failed to chdir to ".$SERVER_DATA_ROOT."sql/<br>\n");
			$dir = opendir('.');
			readdir($dir); readdir($dir);
			while( ($file = readdir($dir)) ) {
				if( preg_match('!\.tbl$!', $file) ) 
					$tables[] = $file;
				else if( preg_match('!\.sql$!', $file) ) 
					$def_data[] = $file;
			}
			closedir($dir);

			$error=NULL;
			
			while( list(,$v) = each($tables) ) {
				$tmp = array();
				
				$data = filetomem($v);
				
				$data = preg_replace('!#.*?\n!s', '', $data);
				$tmp = explode(';', $data);
				
				while( list(, $v2) = each($tmp) ) {
					if( trim($v2) ) {
						make_into_query($v2);
						if( $v2 ) {
							if( !mysql_query($v2) ) {
								$error = 1;
								seterr('MYSQL_DB', 'Failed to create table '.preg_replace('!.*/(.*?)\.tbl!','\1',$v).' ('.$v2.'), SQL Reason: '.mysql_error());
								break;
							}
						}	
					}	
				}
				if( $error ) break;
			}
			
			if( !$error ) {
				while( list(,$v) = each($def_data) ) {
					$tmp = array();
				
					$data = filetomem($v);
				
					$data = preg_replace('!\#.*$!s', '', $data);
					$tmp = preg_split('!;[\r\n]+!', $data);
				
					while( list(, $v2) = each($tmp) ) {
						if( trim($v2) ) {
							make_into_query($v2);
							
							if( $v2 ) {
								if( !mysql_query($v2) ) {
									$error = 1;
									seterr('MYSQL_DB', 'Failed to import default data ('.$v2.') into table '.preg_replace('!.*/(.*?)\.sql!','\1',$v).', SQL Reason: '.mysql_error());
									break;
								}
							}	
						}	
					}
					if( $error ) break;
				}
			}
			
			@chdir($olddir);
			
			if( empty($GLOBALS["errors"]) ) { 
				$GLOBALS['__GLOBALS.INC__'] = $SERVER_DATA_ROOT.'include/GLOBALS.php';
				$global_config = read_global_config();
				change_global_val('MYSQL_SERVER', $MYSQL_SERVER, $global_config);
				change_global_val('MYSQL_LOGIN', $MYSQL_LOGIN, $global_config);
				change_global_val('MYSQL_PASSWORD', $MYSQL_PASSWORD, $global_config);
				change_global_val('MYSQL_DB', $MYSQL_DB, $global_config);
				change_global_val('MYSQL_TBL_PREFIX', $MYSQL_TBL_PREFIX, $global_config);
				write_global_config($global_config);
			
				$section = 'cookies';	
			}	
		}
		break;
	case "cookies":
		if( empty($COOKIE_DOMAIN) ) {
			seterr('COOKIE_DOMAIN', 'You must enter a cookie domain in order for cookies to work properly.');
		}
		
		if( empty($GLOBALS["errors"]) ) {
			$GLOBALS['__GLOBALS.INC__'] = $SERVER_DATA_ROOT.'include/GLOBALS.php';
			$global_config = read_global_config();
			change_global_val('COOKIE_DOMAIN', $COOKIE_DOMAIN, $global_config);
			write_global_config($global_config);
		
			$section = 'language';
		}	
		break;	
	case "language":
		mysql_connect($MYSQL_SERVER, $MYSQL_LOGIN, $MYSQL_PASSWORD);
		mysql_select_db($MYSQL_DB);
		
		list($la, $lc) = explode("::", $GLOBALS['LANGUAGE']);
		
		mysql_query("INSERT INTO ".$MYSQL_TBL_PREFIX."themes(id, name, theme, lang, locale, enabled, t_default)
				VALUES(1, 'default', 'default', '$la', '$lc', 'Y', 'Y')");
		$section = 'admin';
		break;	
		
	case "admin":
		if( empty($ROOT_PASS) )
			seterr('ROOT_PASS', 'You must enter a password for the administrator account.');
		else if( $ROOT_PASS != $ROOT_PASS_C ) 
			seterr('ROOT_PASS', 'Your passwords do not match.');	
			
		if( empty($ROOT_LOGIN) ) 
			seterr('ROOT_LOGIN', 'You must enter a user name for the administrator account.');
		if( empty($ADMIN_EMAIL) ) 
			seterr('ADMIN_EMAIL', 'You must enter a valid email address for the administrator account.');
	
		if( empty($GLOBALS["errors"]) ) {
			mysql_connect($MYSQL_SERVER, $MYSQL_LOGIN, $MYSQL_PASSWORD);
			mysql_select_db($MYSQL_DB);
			
			mysql_query("INSERT INTO ".$MYSQL_TBL_PREFIX."users (login, passwd, name, email, email_conf, coppa, join_date,is_mod) VALUES('".$ROOT_LOGIN."','".md5($ROOT_PASS)."','Administrator', '".$ADMIN_EMAIL."','Y', 'N', ".time().", 'A')");
			
			$GLOBALS['__GLOBALS.INC__'] = $SERVER_DATA_ROOT.'include/GLOBALS.php';
			$global_config = read_global_config();
			change_global_val('ADMIN_EMAIL', $ADMIN_EMAIL, $global_config);
			change_global_val('NOTIFY_FROM', $ADMIN_EMAIL, $global_config);
			write_global_config($global_config);
		
			$section = 'done';
		}
		break;
	case "done":
		header("Location: ".$url);
		exit;
		break;	
}	
?>
<html>
<form name="install" action="<?php echo $HTTP_SERVER_VARS['PHP_SELF'].'?'.rand(); ?>" method="post">
<?php
	if ( empty($section) ) 
		$section = "stor_path";
	
	switch ( $section )
	{
		case "stor_path":
			if( empty($PHP_SELF) ) {
				if( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) 
					$PHP_SELF = $HTTP_SERVER_VARS['PHP_SELF'];
				else if( !empty($HTTP_SERVER_VARS['SCRIPT_NAME']) ) 
					$PHP_SELF = $HTTP_SERVER_VARS['SCRIPT_NAME'];
				else if( !empty($HTTP_SERVER_VARS['REQUEST_URI']) ) 
					$PHP_SELF = substr($HTTP_SERVER_VARS['REQUEST_URI'], 0, strpos($HTTP_SERVER_VARS['REQUEST_URI'], '?'));
			}
			$PHP_CUR_DIR = substr($PHP_SELF, 0, strrpos($PHP_SELF, '/')+1);

			if( !isset($HTTP_POST_VARS['WWW_ROOT']) ) $WWW_ROOT = 'http://'.$HTTP_SERVER_VARS['HTTP_HOST'].$PHP_CUR_DIR;
			if( !isset($HTTP_POST_VARS['SERVER_ROOT']) ) $SERVER_ROOT = dirname($GLOBALS["HTTP_SERVER_VARS"]["PATH_TRANSLATED"]).'/';
			if( !isset($HTTP_POST_VARS['SERVER_DATA_ROOT']) ) {
				$SERVER_DATA_ROOT = getcwd().'/'.str_repeat('../', substr_count(dirname($PHP_SELF), '/'));
				$SERVER_DATA_ROOT = realpath($SERVER_DATA_ROOT).'/forum/';
				$SERVER_DATA_ROOT = addslashes($SERVER_DATA_ROOT);
			}
			
			if ( !$SAFE_MODE ) {
				draw_dialog_start('PATH OF SYSTEM FILES AND DIRECTORIES&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 1 of 5</b></font> ', 'First, you need to specify the directories where the forum files will be stored.  In order for the forum installation to work you need to chmod the directories <b>Server Root</b> &amp; <b>Forum Data Root</b> in such a way that the webserver can write to them. I suggest chmoding the directories to 777.<br>
			If you have shell access, you can change the directory permission by typing "<b>chmod 777 directory_name</b>"<br>
			In CuteFTP, you can chmod a directory by selecting it and then pressing Ctrl+Shift+A. In the Manual checkbox, enter 777 and then press OK.<br>
			In WS_FTP, right-click on the directory and choose the chmod UNIX option. In the dialog, select all the checkboxes and click OK. This will chmod the directory 777.<br>');
			}
			else {
				draw_dialog_start('<div align=middle><font color=red><b>SAFEMODE is ENABLED!</b></font></div><br>PATH OF SYSTEM FILES AND DIRECTORIES&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 1 of 5</b></font>', 
					'
					Your PHP has <b><font color=red>SAFE MODE</font></b> enabled. Pay careful attention to the intructions below:<br><br>
					Due to the brain dead nature of PHP\'s safemode we <font color=red>can not</font> install the forum in a directory
					created by you. Therefor you must install the forum into a directory with <font color=red>does not yet exist</font>. 
					
					');
					
				if ( !count($HTTP_POST_VARS) ) {
					$WWW_ROOT .= 'forum/';
					$SERVER_ROOT .= 'forum/';
				}
			}
			
			draw_row('Server Root', 'SERVER_ROOT', $SERVER_ROOT, 'The path on the server where the browseable files of the forum (*.php) will be stored.');
			draw_row('Forum Data Root', 'SERVER_DATA_ROOT', $SERVER_DATA_ROOT, 'The path on the server where the <b>NON-</b>browseable files of the forum will be stored.');
			draw_row('Forum WWW Root', 'WWW_ROOT', $WWW_ROOT, 'This is the URL of your forum, and should point to the forum\'s front page.  This is also the URL people will need to use to get to your forum.');
			break;
		case "mysql":
			draw_dialog_start('MySQL Settings&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 2 of 5</b></font>', 'FUDforum uses MySQL to store much of the data used in the forum. Please use the form below to enter information that will allow FUDforum to access a MySQL database. It is recommended you create a separate MySQL database for the forum.');
			
			draw_row('MySQL Server', 'MYSQL_SERVER', IFSTR($def_mysql, $MYSQL_SERVER), 'The IP address (or unix domain socket) of the MySQL server.');
			draw_row('MySQL Login', 'MYSQL_LOGIN', IFSTR('', $MYSQL_LOGIN), 'The MySQL login name for the database you intend to use the system with.');
			draw_row('MySQL Password', 'MYSQL_PASSWORD', IFSTR('', $MYSQL_PASSWORD), 'The MySQL password for the login name.');
			draw_row('MySQL Database', 'MYSQL_DB', IFSTR('', $MYSQL_DB), 'The name of the MySQL database where forum data will be stored.');
			draw_row('FUDforum SQL Table Prefix', 'MYSQL_TBL_PREFIX', IFSTR('fud2_', $MYSQL_TBL_PREFIX), 'A string of text that will be appended to each MySQL table name.');
			break;
		case "cookies":
			$url_parts = parse_url($WWW_ROOT);
			
			draw_dialog_start('Cookie Domain&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 3 of 5</b></font>', 'The domain of the cookie that will be used by the forum.');
			draw_row('Cookie Domain', 'COOKIE_DOMAIN', IFSTR(preg_replace('!^www\.!i', '.', $url_parts['host']), $COOKIE_DOMAIN));
			break;
		case "language":
			draw_dialog_start('Forum Language&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 4 of 5</b></font>', 'Choose the language for your forum.<br><font size="-1">If the language you require is not avaliable, please go to <a href="http://fud.prohost.org/forum/" target="_new">FUDforum\'s website</a> and read about translating the forum to other languages.</font>');	
			$pwd = getcwd();
			$oldpwd = getcwd();
			chdir($SERVER_DATA_ROOT.'/thm/default/i18n');
			$dp = opendir('.');
			readdir($dp); readdir($dp);
			$selopt = '';
			while ( $de = readdir($dp) ) {
				if ( $de == 'CVS' || !is_dir($de) ) continue;
				$selnames .= "$de\n";
				$locale = trim(filetomem($de.'/locale'));
				$selopts .= "$de::$locale\n";
			}
			closedir($dp);
			chdir($pwd);
			$selnames = substr($selnames, 0, -1);
			$selopts = substr($selopts, 0, -1);
			draw_row_sel('Language', 'LANGUAGE', $selnames, $selopts, NULL, 'english::english');
			break;
			
		case "admin":
			draw_dialog_start('Admin Account&nbsp;&nbsp;&nbsp;&nbsp;<font size="-1"><b>Step 5 of 5</b></font>', 'This creates the "root" user account, which is an unrestricted account that can do anything on the forum. You must use this account to edit &amp; customize the forum.');
			
			draw_row('Login Name', 'ROOT_LOGIN', IFSTR('root', $ROOT_LOGIN));
			draw_row('Root Password', 'ROOT_PASS', IFSTR('', $ROOT_PASS));
			draw_row('Confirm Password', 'ROOT_PASS_C', IFSTR('', $ROOT_PASS_C));
			draw_row('Admin Email', 'ADMIN_EMAIL', IFSTR('', $ADMIN_EMAIL));
			break;
		case "done":
			global $WWW_ROOT_DISK;
			global $LANGUAGE;
			global $INCLUDE;
			
			$GLOBALS['WWW_ROOT_DISK'] = $SERVER_ROOT;
			$GLOBALS['DATA_DIR'] = $SERVER_DATA_ROOT.'/';
			$GLOBALS['INCLUDE'] = $SERVER_DATA_ROOT.'/include/';
			
			include_once $SERVER_DATA_ROOT."include/static/compiler.inc";

			list($la) = explode('::', $GLOBALS['LANGUAGE']);

			compile_all('default', $la);

			draw_dialog_start('Installation Complete', 'You have now completed the basic installation of the forum. To continue configuring your forum, you must login and use the administrator control panel.<br>
				Clicking "Next" will take you to the login form.  After you login, you will be taken to the administrator control panel.<br>
				<font color="#ff0000">Before you do, however, you must delete this <b>install.php</b> script, because it can be used to overwrite your forum.  You will not be able to login until you do.</font>
			');	

			/* Remove the install_safe for safe_mode users, because they will not be able to remove it themselves */
			if( $SAFE_MODE ) unlink($HTTP_SERVER_VARS['PATH_TRANSLATED']);
			
			echo '<tr>
				<td colspan=2 align="center">
					<input type="submit" name="submit" value="Finished" 
			onClick="javascript: window.location=\''.$WWW_ROOT.'index.php?t=login&adm=1\'; return false;">
					</td></tr></table></td></tr></table></form></html>';
			exit;
			break;
	}
	draw_dialog_end(); 
	
	echo '<input type="hidden" name="section" value="'.stripslashes($section).'">';
	if( $section != 'stor_path' ) echo '<input type="hidden" name="WWW_ROOT" value="'.stripslashes($WWW_ROOT).'">';
	if( $section != 'stor_path' ) echo '<input type="hidden" name="SERVER_DATA_ROOT" value="'.stripslashes($SERVER_DATA_ROOT).'"><input type="hidden" name="SERVER_ROOT" value="'.stripslashes($SERVER_ROOT).'">';
	if( $section != 'language' ) echo '<input type="hidden" name="LANGUAGE" value="'.stripslashes($LANGUAGE).'">';
	
	switch( $section ) 
	{
		case 'cookies':
		case 'language':
		case 'admin':
			echo '<input type="hidden" name="MYSQL_SERVER" value="'.$MYSQL_SERVER.'"><input type="hidden" name="MYSQL_PASSWORD" value="'.$MYSQL_PASSWORD.'"><input type="hidden" name="MYSQL_LOGIN" value="'.$MYSQL_LOGIN.'"><input type="hidden" name="MYSQL_DB" value="'.$MYSQL_DB.'"><input type="hidden" name="MYSQL_TBL_PREFIX" value="'.$MYSQL_TBL_PREFIX.'">';
			break;
	}
?>
</form>
</html>
<?php exit; ?>
2105111608_\ARCH_START_HERE
