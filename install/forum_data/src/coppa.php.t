<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: coppa.php.t,v 1.3 2003/04/02 01:46:35 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	{PRE_HTML_PHP}
	$TITLE_EXTRA = ': {TEMPLATE: coppa_conf}';
	{POST_HTML_PHP}
	$coppa = __request_timestamp__-409968000;
	{POST_PAGE_PHP_CODE}
?>
{TEMPLATE: COPPA_PAGE}