<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: return.inc.t,v 1.3 2002/08/05 00:47:55 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

function create_return()
{
	return '<input type="hidden" name="returnto" value="'.htmlspecialchars(urldecode($GLOBALS['returnto'])).'">';
}
        
function check_return()
{
        if ( empty($GLOBALS['returnto']) ) 
        	$GLOBALS['returnto']='{ROOT}?'._rsid;
        else if ( !strstr($GLOBALS['returnto'], 'S=') ) {
        	if ( !strstr($GLOBALS['returnto'], '?') ) 
	         	$GLOBALS['returnto'] .= '?'._rsid;
	        else
	        	$GLOBALS['returnto'] .= '&'._rsid;
        }
        header("Location: ".$GLOBALS['returnto']);
	exit();
}
?>