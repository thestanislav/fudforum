<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: scripts_common.inc,v 1.3 2003/05/07 01:05:31 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

function match_user_to_post($from_email, $from_name, $create_users, &$user_id)
{
	/* Try to identify user by email */
	$user_id = q_singleval("SELECT id FROM ".sql_p."users WHERE email='".addslashes($from_email)."'");

	/* If user was not found via email, try to look the user up by login */
	if (empty($user_id) && !empty($from_name)) {
		$user_id = q_singleval("SELECT id FROM ".sql_p."users WHERE login='".addslashes($from_name)."'");
	}	

	if (empty($user_id)) {
		$user_id = $create_users == 'Y' ? create_new_user($from_name, $from_email) : 0;
	} 

	return $user_id;	
}

function create_new_user($from_name, $from_email)
{
	/* Since we assume every user created from a mailing list is already 'confirmed' 
	 * this disables sending of the email confirmation
	 */
	$GLOBALS['EMAIL_CONFIRMATION'] == 'Y';
	
	db_lock(sql_p.'users WRITE, '.sql_p.'themes WRITE');

	$login = empty($from_name) ? $from_email : $from_name;

	/* 
	 * This code ensures that creation of user does not fail in the event another user on the forum 
	 * is already signed up under the same login name and/or alias
	 */	

	$i = 1;
	
	$user = new fud_user_reg;
	$user->login = $login;

	while (q_singleval("SELECT id FROM ".sql_p."users WHERE login='".addslashes($user->login)."'")) {
		$user->login = $login.'['.$i++.']';
	}

	$alias = $user->alias = htmlspecialchars($user->login);
	
	if ($GLOBALS['USE_ALIASES'] == 'Y') {
		while (q_singleval("SELECT id FROM ".sql_p."users WHERE alias='".addslashes($user->alias)."'")) {
			$user->alias = $alias.'['.$i++.']';
		}
	}
	
	$GLOBALS['EMAIL_CONFIRMATION'] = 'N';
		
	$user->email = $from_email;
	$user->plaintext_passwd = substr(md5(get_random_value()), 0, 8);
	$user->default_view = $GLOBALS['DEFAULT_THREAD_VIEW'];
	$user->notify_method = 'EMAIL';
	$user->gender = 'UNSPECIFIED';
	$user->email_messages = 'Y';
	$user->pm_messages = 'Y';
	$user->show_avatars = 'Y';
	$user->show_sigs = 'Y';
	$user->name = $from_name;
	$user->time_zone = $GLOBALS['SERVER_TZ'];
	$user->posts_ppg = $GLOBALS['POSTS_PER_PAGE'];
	$user->theme = q_singleval("SELECT id FROM ".sql_p."themes WHERE t_default='Y' LIMIT 1");
		
	$id = $user->add_user();
	q("UPDATE ".sql_p."users SET email_conf='Y' WHERE id=".$id);
		
	db_unlock();
		
	return $id;
}

function get_fud_reply_id($complex, $forum_id, $subject, $data)
{
	if (!empty($data)) {
		if (is_string($data)) {
			$data = array($data);	
		}

		foreach ($data as $reply_id) {
			if (($r = db_saq("SELECT id, thread_id FROM ".sql_p."msg WHERE mlist_msg_id='".addslashes($reply_id)."'"))) {
				break;
			}
		}
	}
	if (empty($r) && $complex == 'Y') {
		// This is slow, but only way to match 'rouge' replies in the event no reference fields are avaliable
		if (preg_match('!(Re|Wa)\s*:(.*)$!i', $subject, $matches)) {
			$r = db_saq('SELECT m.id, m.thread_id FROM '.sql_p.'msg m INNER JOIN '.sql_p.'thread t ON m.thread_id=t.id WHERE t.forum_id='.$forum_id.' AND m.subject='.strnull(addslashes(trim($matches[2]))));
		}
	}

	return !empty($r) ? array((int)$r[0], (int)$r[1]) : array(0, 0);
}

function parse_ip($str)
{
	if (preg_match('!([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})!', $str, $m)) {
		return $m[1];
	} else {
		return;	
	}
}

function decode_string($str, $encoding)
{
	switch ($encoding) {
		case 'quoted-printable':
			// Remove soft line breaks & decode
		        return quoted_printable_decode(preg_replace("!=\r?\n!", '', $str));
			break;
		case 'base64':
			return base64_decode($str);
			break;
		default:
			return $str;	
			break;
	}
}

function decode_header_value($val)
{
	// check if string needs to be decoded
	if (strpos($val, '?') === FALSE) {
		return trim($val);	
	}

	// Decode String
	if (preg_match_all('!(.*?)(=\?([^?]+)\?(Q|B)\?([^?]*)\?=)[[:space:]]*(.*)!i', $val, $m)) {
		$newval = '';
			
		$c = count($m[4]);
		for ($i = 0; $i < $c; $i++) {
			$ec_type = strtolower($m[4][$i]);
			
			if ($ec_type == 'q') {
				$newval .= decode_string(str_replace('_', ' ', $m[5][$i]), 'quoted-printable');	
			} else if ($ec_type == 'b') {
				$newval .= decode_string($m[5][$i], 'base64');
			}

			if (!empty($m[5][$i])) {
				$newval .= ' '.$m[6][$i];
			}
			if (!empty($m[1][$i])) {
				$newval = $m[1][$i].$newval;
			}
		}
		$val = trim($newval);
	}
	return trim($val);
}
?>