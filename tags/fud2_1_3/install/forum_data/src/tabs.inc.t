<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: tabs.inc.t,v 1.3 2002/06/26 19:35:55 hackie Exp $
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
	
	if( $GLOBALS['PM_ENABLED']=='Y' ) $tablist['{TEMPLATE: tabs_private_messaging}'] = 'pmsg';
	
	$tabs='';
	$pg = $t;
	if( $pg == 'pmsg_view' || $pg == 'ppost' ) $pg = 'pmsg';
	
	reset($tablist);
	while( list($tab_name, $tab) = each($tablist) ) {
		$tab_url = '{ROOT}?t='.$tab.'&'._rsid;
		if( $tab == 'referals' ) $tab_url .= '&id='._uid;	
		$tabs .= ($pg == $tab) ? '{TEMPLATE: active_tab}' : '{TEMPLATE: inactive_tab}';
	}
	
	$tabs = '{TEMPLATE: tablist}';
}	
?>