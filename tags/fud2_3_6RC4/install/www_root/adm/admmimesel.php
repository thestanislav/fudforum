<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: admmimesel.php,v 1.1.1.1 2002/06/17 23:00:09 hackie Exp $
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
	
	fud_use('util.inc');

	cache_buster();
	
	$mime_path = '../images/mime/';
	
	echo '<html><body bgcolor="#ffffff">';
	
	if ( !@is_readable($mime_path) ) 
		echo '<br><font color="#ff0000">Can\'t read "'.$mime_path.'"</font></br>';
	else {
		$icons_per_row = 7;
		$olddir = getcwd();
		chdir($mime_path);
		$dp = opendir('.');
		readdir($dp); readdir($dp);
		
		echo '<table border=0 cellspacing=1 cellpadding=2><tr>';
		$col = $i = 0;
		while ( $de = readdir($dp) ) {
			if( is_dir($de) ) continue;
			$ext = strtolower(substr($de, -4));
			if ( $ext != '.gif' && $ext != 'jpeg' && $ext != '.jpg' && $ext != '.png' ) continue;
			if ( !($col++%$icons_per_row) ) echo '</tr><tr>';
			$bgcolor = ( !($i++%2) ) ? ' bgcolor="#f4f4f4"':'';
				
			echo '<td'.$bgcolor.' nowrap valign=center align=center><a href="javascript: window.opener.document.prev_icon.src=\''.$mime_path.$de.'\'; window.opener.document.frm_mime.mime_icon.value=\''.$de.'\'; window.close();"><img src="'.$mime_path.$de.'" border=0><br><font size=-2>'.$de.'</font></a></td>';
		}
		closedir($dp);
		echo '</tr></table>';
	}
	echo '</body></html>'; 
?>