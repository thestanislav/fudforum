<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: objutil.inc.t,v 1.3 2003/04/17 09:37:33 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

function fetch_vars($pref, &$obj, $arr)
{
	foreach($obj as $k => $v) {
		if (isset($arr[$pref.$k])) {
			$obj->{$k} = $arr[$pref.$k];
		}
	}
}
?>