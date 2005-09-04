<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: groupmgr.php.t,v 1.1.1.1 2002/06/17 23:00:09 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

	include_once "GLOBALS.php";
	{PRE_HTML_PHP}
	
	if ( empty($usr) || (!empty($group_id) && !BQ("SELECT id FROM {SQL_TABLE_PREFIX}group_members WHERE group_id=".$group_id." AND user_id=".$usr->id." AND group_leader='Y'") && $usr->is_mod!='A') ) {
		std_error('access');
		exit;	
	}
	
	if( $usr->is_mod != 'A' ) { 
		$r = Q("SELECT group_id, name FROM {SQL_TABLE_PREFIX}group_members INNER JOIN {SQL_TABLE_PREFIX}groups ON {SQL_TABLE_PREFIX}group_members.group_id={SQL_TABLE_PREFIX}groups.id WHERE user_id=".$usr->id." AND group_leader='Y'");
		if( !($group_count = DB_COUNT($r)) ) {
			QF($r);
			std_error('access');
			exit;	
		}
	}
	else {
		$r = Q("SELECT id AS group_id, name FROM {SQL_TABLE_PREFIX}groups WHERE id>2");
		$group_count = DB_COUNT($r);
	}	
	
	if( empty($group_id) && $group_count ) {
		list($group_id,) = DB_ROWARR($r);
		DB_SEEK($r,0);
	}
	
	if( !empty($group_count) && $group_count>1 ) {
		$vl = $kl = '';
		while( list($gid,$gname) = DB_ROWARR($r) ) {
			$vl .= $gid."\n";
			$kl .= $gname."\n";
		}
		$vl = substr($vl, 0, -1);
		$kl = substr($kl, 0, -1);
		
		$group_selection = tmpl_draw_select_opt($vl, $kl, $group_id, '', '');
		$group_selection = '{TEMPLATE: group_selection}';
	}
	QF($r);
	
	{POST_HTML_PHP}

function draw_tmpl_perm_table($perm_arr)
{
	reset($perm_arr);
	$str = '';
	while ( list($k, $v) = each($perm_arr) ) {
		if ( substr($k, 0, 3) != 'up_' ) continue;
		if ( $v == 'Y' ) 
			$str .= '{TEMPLATE: perm_yes}';
		else
			$str .= '{TEMPLATE: perm_no}';
	}
	return $str;
}

	if( $group_id ) {
		$grp = new fud_group;
		$grp->get($group_id);
		$indicator = '{TEMPLATE: indicator}';
		$pret = $grp->resolve_perms();
		$maxperms = $pret['perms'];	
	}
	
	if ( $btn_cancel ) {
		header("Location: {ROOT}?t=groupmgr&"._rsid."&group_id=$grp->id&rnd=".get_random_value());
		exit();
	}
	
	if ( $usr->is_mod!='A' && !$grp->is_leader($usr->id) ) {
		std_error('access');
		exit();
	}
	
	if ( $btn_submit ) {
		$mbr = new fud_user_reg;
		$perms_arr = mk_perms_arr('', $perms, 'u');
		if ( empty($edit) ) {
			$mbr->get_user_by_login($gr_member);
			$gr_member = stripslashes($gr_member);
			if( empty($mbr->id) ) 
				$login_error = '{TEMPLATE: groupmgr_no_user}';
			else if( BQ("SELECT id FROM {SQL_TABLE_PREFIX}group_members WHERE group_id=".$grp->id." AND user_id=".$mbr->id) )
				$login_error = '{TEMPLATE: groupmgr_already_exists}';
			else {
				$grp->add_member($mbr->id, $perms_arr);
			}
		}
		else {
			$usr_id = Q_SINGLEVAL("SELECT user_id FROM {SQL_TABLE_PREFIX}group_members WHERE group_id=$group_id AND id=$edit");
			$grp->update_member($usr_id, $perms_arr);
		}

		if( empty($login_error) ) {
			$grp->rebuild_cache($mbr->id);
			header("Location: {ROOT}?t=groupmgr&"._rsid."&group_id=$grp->id&rnd=".get_random_value());
			exit();
		}	
	}
	
	if ( !empty($del) ) {
		$grp->delete_member($del);
		header("Location: {ROOT}?t=groupmgr&"._rsid."&group_id=$grp->id&rnd=".get_random_value());
		exit();
	}
	
	if ( !empty($edit) ) {
		$mbr = $grp->get_member_by_ent_id($edit);
		
		if ( $mbr->user_id == 0 ) 
			$gr_member = '{TEMPLATE: group_mgr_anon}';
		else if ( $mbr->user_id == '4294967295' ) 
			$gr_member = '{TEMPLATE: group_mgr_reged}';
		else 
			$gr_member = htmlspecialchars($mbr->login);		
		
		$perms = perm_obj_to_arr($mbr, 'up_');
		reset($perms);
		while ( list($k, $v) = each($perms) ) {
			$perms_new[substr($k, 1)] = $v;
		}
		$perms = $perms_new;
	}
	else if( $grp->id>2 && ($luser_id = Q_SINGLEVAL("SELECT MAX(id) FROM {SQL_TABLE_PREFIX}group_members WHERE group_id=".$grp->id)) ) {
		$mbr = $grp->get_member_by_ent_id($luser_id);
		$perms = perm_obj_to_arr($mbr, 'up_');
		reset($perms);
		while ( list($k, $v) = each($perms) ) {
			$perms_new[substr($k, 1)] = $v;
		}
		$perms = $perms_new;
	}
	
	if( $group_id ) {
		if( $mbr->user_id == 0 ) $maxperms['p_VOTE'] = $maxperms['p_RATE'] = 'N';

		$perm_select = draw_permissions('', $perms, $maxperms);
		if ( empty($edit) ) {
			$member_input = '{TEMPLATE: member_add}';
			$submit_button = '{TEMPLATE: submit_button}';
		}
		else {
			$submit_button = '{TEMPLATE: update_buttons}';
			$member_input = '{TEMPLATE: member_edit}';
		}
	
		$r = Q("SELECT 
				{SQL_TABLE_PREFIX}group_members.id AS MMID, 
				{SQL_TABLE_PREFIX}group_members.*, 
				{SQL_TABLE_PREFIX}groups.*, 
				{SQL_TABLE_PREFIX}users.login 
			FROM 
				{SQL_TABLE_PREFIX}group_members 
				LEFT JOIN {SQL_TABLE_PREFIX}users 
					ON {SQL_TABLE_PREFIX}group_members.user_id={SQL_TABLE_PREFIX}users.id  INNER JOIN {SQL_TABLE_PREFIX}groups ON {SQL_TABLE_PREFIX}group_members.group_id={SQL_TABLE_PREFIX}groups.id WHERE group_id=$grp->id AND {SQL_TABLE_PREFIX}group_members.group_leader='N' ORDER BY {SQL_TABLE_PREFIX}group_members.id");
	
		$group_members_list = '';
		while ( $obj = DB_ROWOBJ($r) ) {
			$perm_table = draw_tmpl_perm_table($obj);
			$rand = get_random_value();
			$delete_allowed = 0;
			if ( $obj->user_id == 0 )
				$member_name = '{TEMPLATE: group_mgr_anon}';
			else if ( $obj->user_id == '4294967295' ) 
				$member_name = '{TEMPLATE: group_mgr_reged}';
			else { $member_name = htmlspecialchars($obj->login); $delete_allowed = 1; }
		
			if ( $delete_allowed ) 
				$group_members_list .= '{TEMPLATE: group_member_entry}';
			else
				$group_members_list .= '{TEMPLATE: group_const_entry}';
		}
		QF($r);
		
		$group_control_panel = '{TEMPLATE: group_control_panel}';
	}
	else
		$group_control_panel = '{TEMPLATE: no_group_control_panel}';

	{POST_PAGE_PHP_CODE}
?>
{TEMPLATE: GROUP_MANAGER}