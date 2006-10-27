<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: tabs.inc.t,v 1.6 2003/01/17 09:45:28 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

if( isset($usr) ) {
	$tablist = array(
'{TEMPLATE: tabs_register}'=>'register', 
'{TEMPLATE: tabs_subscriptions}'=>'subscribed',
'{TEMPLATE: tabs_referrals}'=>'referals',
'{TEMPLATE: tabs_buddy_list}'=>'buddy_list',
'{TEMPLATE: tabs_ignore_list}'=>'ignore_list'
);
	if (isset($GLOBALS['HTTP_POST_VARS']['mod_id'])) {
		$mod_id_chk = $GLOBALS['HTTP_POST_VARS']['mod_id'];
	} else if (isset($GLOBALS['HTTP_GET_VARS']['mod_id'])) {
		$mod_id_chk = $GLOBALS['HTTP_GET_VARS']['mod_id'];
	} else {
		$mod_id_chk = NULL;	
	}

	if (!is_numeric($mod_id_chk) && $pg == 'register') {
		if( $GLOBALS['PM_ENABLED']=='Y' ) $tablist['{TEMPLATE: tabs_private_messaging}'] = 'pmsg';
	
		$tabs='';
		$pg = $t;
		if( $pg == 'pmsg_view' || $pg == 'ppost' ) $pg = 'pmsg';
	
		foreach($tablist as $tab_name => $tab) { 
			$tab_url = '{ROOT}?t='.$tab.'&amp;'._rsid;
			if( $tab == 'referals' ) $tab_url .= '&amp;id='._uid;	
			$tabs .= ($pg == $tab) ? '{TEMPLATE: active_tab}' : '{TEMPLATE: inactive_tab}';
		}
	
		$tabs = '{TEMPLATE: tablist}';
	}
}	
?>