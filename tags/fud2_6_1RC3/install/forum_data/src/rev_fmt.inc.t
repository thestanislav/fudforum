<?php
/***************************************************************************
* copyright            : (C) 2001-2004 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: rev_fmt.inc.t,v 1.10 2004/02/14 00:06:29 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
***************************************************************************/

function reverse_fmt(&$data)
{
	$data = str_replace(array('&quot;', '&lt;', '&gt;', '&amp;'), array('"', '<', '>', '&'), $data);
}
?>