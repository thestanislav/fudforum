<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: logaction.inc.t,v 1.3 2002/09/18 22:04:43 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	
function logaction($user_id, $res, $res_id=0, $action=NULL)
{
	q("INSERT INTO {SQL_TABLE_PREFIX}action_log (logtime, logaction, user_id, a_res, a_res_id)
		VALUES(".__request_timestamp__.", '$action', $user_id, '$res', $res_id)");
}

function clear_action_log()
{
	q("DELETE FROM {SQL_TABLE_PREFIX}action_log");
}
?>