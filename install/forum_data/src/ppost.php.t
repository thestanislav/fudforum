<?php
/***************************************************************************
*   copyright            : (C) 2001,2002 Advanced Internet Designs Inc.
*   email                : forum@prohost.org
*
*   $Id: ppost.php.t,v 1.18 2003/04/17 12:30:34 hackie Exp $
****************************************************************************
          
****************************************************************************
*
*	This program is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
***************************************************************************/

/*{PRE_HTML_PHP}*/
	
	if (!_uid) {
		error_dialog('{TEMPLATE: permission_denied_title}', '{TEMPLATE: permission_denied_msg}');
	}
	if ($PM_ENABLED == 'N') {
		error_dialog('{TEMPLATE: pm_err_nopm_title}', '{TEMPLATE: pm_err_nopm_msg}');
	}
	if ($usr->pm_messages == 'N') {
		error_dialog('{TEMPLATE: pm_err_disabled_title}', '{TEMPLATE: pm_err_disabled_msg}');
	}
	if (($fldr_size = q_singleval('SELECT SUM(length) FROM {SQL_TABLE_PREFIX}pmsg WHERE duser_id='._uid)) > $MAX_PMSG_FLDR_SIZE) {
		error_dialog('{TEMPLATE: pm_no_space_title}', '{TEMPLATE: pm_no_space_msg}');
	}
	is_allowed_user($usr);

	$attach_control_error=NULL;

	/* deal with users passed via GET */
	if (!isset($_POST['prev_loaded']) && isset($_GET['toi']) && ($toi = (int)$_GET['toi'])) {
		$msg_to_list = q_singleval('SELECT alias FROM {SQL_TABLE_PREFIX}users WHERE id='.$toi);
	}
	
	$attach_count = 0; $file_array = '';
	
	if (!isset($_POST['prev_loaded'])) {
		/* setup some default values */
		$msg_subject = $msg_body = $msg_icon = '';
		$msg_track = NULL;
		$msg_show_sig = $usr->append_sig == 'Y' ? 1 : NULL;
		$msg_smiley_disabled = $PRIVATE_MSG_SMILEY == 'Y' ? 1 : NULL;
		$reply = $forward = 0;
		
		/* deal with users passed via GET */
		if (isset($_GET['toi']) && ($toi = (int)$_GET['toi'])) {
			$msg_to_list = q_singleval('SELECT alias FROM {SQL_TABLE_PREFIX}users WHERE id='.$toi);
		} else {
			$msg_to_list = '';
		}
	
		if (isset($_GET['msg_id']) && ($msg_id = (int)$_GET['msg_id'])) { /* editing a message */
			if (($msg_r = db_sab('SELECT * FROM {SQL_TABLE_PREFIX}pmsg WHERE id='.$msg_id.' AND (ouser_id='._uid.' OR duser_id='._uid.')'))) {
				export_vars('msg_', $msg_r);
				reverse_FMT($msg_subject);
				$msg_subject = apply_reverse_replace($msg_subject);
				$msg_body = post_to_smiley($msg_body);
				switch ($PRIVATE_TAGS) {
					case 'ML':
						$msg_body = html_to_tags($msg_body);
				 		break;
					case 'HTML':
						break;
					default:
						reverse_FMT($msg_body);
						reverse_nl2br($msg_body);
				}
				$msg_body = apply_reverse_replace($msg_body);
			}
		} else if (isset($_GET['quote']) || isset($_GET['forward'])) { /* quote or forward message */
			if (($msg_r = db_sab('SELECT * FROM {SQL_TABLE_PREFIX}pmsg WHERE id='.(int)(isset($_GET['quote']) ? $_GET['quote'] : $_GET['forward']).' AND (ouser_id='._uid.' OR duser_id='._uid.')'))) {
				$reply = $quote = isset($_GET['quote']) ? (int)$_GET['quote'] : 0;
				$forward = isset($_GET['forward']) ? (int)$_GET['forward'] : 0;
			
				export_vars('msg_', $msg_r);
				$msg_id = $msg_to_list = $msg_duser_id = '';
				$msg_body = post_to_smiley($msg_body);
				
				switch ($PRIVATE_TAGS) {
					case 'ML':
						$msg_body = html_to_tags($msg_body);
					 	$msg_body = '{TEMPLATE: fud_quote}';
					 	break;
					case 'HTML':
						$msg_body = '{TEMPLATE: html_quote}';
						break;
					default:
						reverse_FMT($msg_body);
						reverse_nl2br($msg_body);
						$msg_body = str_replace('<br>', "\n", '{TEMPLATE: plain_quote}');
				}
			 	$msg_body = apply_reverse_replace($msg_body)."\n";	
		 	
			 	reverse_FMT($msg_subject);
				$msg_subject = apply_reverse_replace($msg_subject);
			
				if ($quote && !preg_match('!^Re: !', $msg_subject)) {
					$msg_subject = 'Re: ' . $msg_subject;
					$msg_ref_msg_id = 'R'.$reply;
				} else if ($forward && !preg_match('!^Fwd: !', $msg_subject)) {
					$msg_subject = 'Fwd: ' . $msg_subject;
					$msg_ref_msg_id = 'F'.$forward;
				}

				if ($quote) {
					$msg_to_list = q_singleval('SELECT alias FROM {SQL_TABLE_PREFIX}users WHERE id='.$msg_r->ouser_id);
					unset($msg_r);
				}
			}
		} else if (isset($_GET['reply']) && ($reply = (int)$_GET['reply'])) {
			if (($msg_r = db_saq('SELECT p.subject, u.alias FROM {SQL_TABLE_PREFIX}pmsg WHERE id='.(int)(isset($_GET['quote']) ? $_GET['quote'] : $_GET['forward']).' AND (ouser_id='._uid.' OR duser_id='._uid.')'))) {
				$msg_subject = $msg_r[0];
				$msg_to_list = $msg_r[1];
				reverse_FMT($msg_subject);
				$msg_subject = apply_reverse_replace($msg_subject);
			
				if (!preg_match('!^Re:!', $msg_subject)) {
					$msg_subject = 'Re: ' . $msg_subject;
				}
				unset($msg_r);
				$msg_ref_msg_id = 'R'.$reply;
			}
		}
		
		/* restore file attachments */
		if (!empty($msg_r->attach_cnt) && $PRIVATE_ATTACHMENTS > 0) {
			$c = uq('SELECT id FROM {SQL_TABLE_PREFIX}attach WHERE message_id='.$msg_r->id.' AND private=\'Y\'');
	 		while ($r = db_rowarr($c)) {
	 			$attach_list[$r[0]] = $r[0];
	 		}
	 		qf($c);
	 		
		}
	} else {
		if (!isset($_POST['preview']) && !isset($_POST['spell']) && isset($_POST['btn_action'])) {
			if ($_POST['btn_action'] == 'draft') {
				$_POST['btn_draft'] = 1;
			} else {
				$_POST['btn_submit'] = 1;
			}
		}
		$msg_to_list = htmlspecialchars($msg_to_list);

		$reply = isset($_POST['quote']) ? (int)$_POST['quote'] : 0;
		$forward = isset($_POST['forward']) ? (int)$_POST['forward'] : 0;

		/* restore file attachments */
		if (!empty($_POST['file_array']) && $PRIVATE_ATTACHMENTS > 0) {
			$attach_list = base64_decode(@unserialize($_POST['file_array']));
		}			
	}

	if (isset($attach_list)) {
		$attach_count = count($attach_list);
		$enc = base64_encode(@serialize($attach_list));
		foreach ($attach_list as $v) {
			if (!$v) {
				$attach_count--;
			}
		}
		/* remove file attachment */
		if (isset($_POST['file_del_opt']) && isset($attach_list[$_POST['file_del_opt']])) {
			if ($attach_list[$_POST['file_del_opt']]) {
				$attach_list[$_POST['file_del_opt']] = 0;
				$attach_count--;
			}
		}
	} else {
		$attach_count = 0;
		$file_array = '';
	}

	/* deal with newly uploaded files */
	if ($PRIVATE_ATTACHMENTS > 0 && isset($_FILES['attach_control'])) {
		if ($_FILES['attach_control']['size'] > $PRIVATE_ATTACH_SIZE) {
			$attach_control_error = '{TEMPLATE: post_err_attach_size}';	
		} else {
			if (filter_ext($_FILES['attach_control']['name'])) {
				$attach_control_error = '{TEMPLATE: post_err_attach_ext}';
			} else {
				if (($attach_count+1) <= $PRIVATE_ATTACHMENTS) {
					$val = attach_add($_FILES['attach_control'], _uid, 'Y');
					$attach_list[$val] = $val;
					$attach_count++;
				} else {
					$attach_control_error = '{TEMPLATE: post_err_attach_filelimit}';
				}	
			}
		}
	}

	if ((isset($_POST['btn_submit']) && !check_ppost_form()) || isset($_POST['btn_draft'])) {
		$msg_p = new fud_pmsg;
		$msg_p->smiley_disabled = isset($_POST['msg_smiley_disabled']) ? 'Y' : 'N';
		$msg_p->show_sig = isset($_POST['msg_show_sig']) ? 'Y' : 'N';
		$msg_p->track = isset($_POST['msg_track']) ? 'Y' : 'N';
		$msg_p->attach_cnt = $attach_count;
		$msg_p->icon = isset($_POST['msg_icon']) ? $_POST['msg_icon'] : NULL;
		$msg_p->body = $_POST['msg_body'];
		$msg_p->subject = $_POST['msg_subject'];
		$msg_p->folder_id = isset($_POST['btn_submit']) ? 'SENT' : 'DRAFT';
		$msg_p->to_list = $_POST['msg_to_list'];
		
		$msg_p->body = apply_custom_replace($msg_p->body);
		switch ($PRIVATE_TAGS) {
			case 'ML':
				$msg_p->body = tags_to_html($msg_p->body, $PRIVATE_IMAGES);
				break;
			case 'HTML':
				break;
			default:
				$msg_p->body = nl2br(htmlspecialchars($msg_p->body));	
		}
		
		if ($msg_p->smiley_disabled != 'Y') {
			$msg_p->body = smiley_to_post($msg_p->body);
		}
		fud_wordwrap($msg_p->body);
		
		$msg_p->ouser_id = _uid;
		
		$msg_p->subject = apply_custom_replace($msg_p->subject);
		$msg_p->subject = htmlspecialchars($msg_p->subject);
	
		if (empty($_POST['msg_id'])) {
			if ($_POST['reply']) {
				$msg_p->ref_msg_id = 'R'.$_POST['reply'];
			} else if ($_POST['forward']) {
				$msg_p->ref_msg_id = 'F'.$_POST['forward'];
			} else {
				$msg_p->ref_msg_id = NULL;
			}

			$msg_p->add();
		} else {
			$msg_p->sync();
		}
				
		if (!isset($_POST['btn_draft'])	&& $msg_p->ref_msg_id) {
			set_nrf(substr($msg_p->ref_msg_id, 0, 1), substr($msg_p->ref_msg_id, 1));
		}
				
		if (isset($attach_list)) {
			attach_finalize($attach_list, $msg_p->id, 'Y');
				
			/* we need to add attachments to all copies of the message */
			if (!isset($_POST['btn_draft'])) {
				$c = uq('SELECT id, original_name, mime_type, fsize FROM {SQL_TABLE_PREFIX}attach WHERE message_id='.$msg_p->id.' AND private=\'Y\'');
				while ($r = db_rowarr($c)) {
					$atl[$r[0]] = "'".addslashes($r[1])."', ".$r[2].", ".$r[3];
				}
				qf($c);
				if (isset($atl)) {
					foreach ($GLOBALS['send_to_array'] as $mid) {
						foreach ($atl as $k => $v) {
							$aid = db_qid('INSERT INTO {SQL_TABLE_PREFIX}attach (owner, private, message_id, original_name, mime_type, fsize) VALUES(' . $mid[0] . ', \'Y\',' . $mid[1] . $v .')');
							$aidl[] = $aid;
							copy($FILE_STORE . $k . '.atch', $FILE_STORE . $aid . '.atch');
							@chmod($FILE_STORE . $aid . '.atch', ($FILE_LOCK == 'Y' ? 0600 : 0666));
						}
					}
					q('UPDATE {SQL_TABLE_PREFIX}attach SET location='.__FUD_SQL_CONCAT__.'(\''.$FILE_STORE.'\', id, \'.atch\') WHERE id IN('.implode(',', $aidl).')');
				}
			}
		}	
		header('Location: {ROOT}?t=pmsg&'._rsidl.'&folder_id=INBOX');
		exit;
	}

	$no_spell_subject = ($reply && $old_subject == $msg_subject) ? 1 : 0;

	if (isset($_POST['btn_spell'])) {
		$text = apply_custom_replace($_POST['msg_body']);
		$text_s = apply_custom_replace($_POST['msg_subject']);
		
		switch ($PRIVATE_TAGS) {
			case 'ML':
				$text = tags_to_html($text, $PRIVATE_IMAGES);
				break;
			case 'HTML':
				break;
			default:
				$text = htmlspecialchars($text);
		}

		if ($PRIVATE_MSG_SMILEY == 'Y' && !isset($msg_smiley_disabled)) {
			$text = smiley_to_post($text);
		}

	 	if ($text) {	
			$text = spell_replace(tokenize_string($text), 'body');
			
			if ($PRIVATE_MSG_SMILEY == 'Y' && !isset($msg_smiley_disabled)) {
				$msg_body = post_to_smiley($text);
			}
			
			switch ($PRIVATE_TAGS) {
				case 'ML':
					$msg_body = html_to_tags($msg_body);
					break;
				case 'HTML':
					break;
				default:
					reverse_FMT($msg_body);		
			}
			
			$msg_body = apply_reverse_replace($msg_body);
		}	
			
		if ($text_s && !$no_spell_subject) {
			$text_s = htmlspecialchars($text_s);
			$text_s = spell_replace(tokenize_string($text_s), 'subject');
			reverse_FMT($text_s);
			$msg_subject = apply_reverse_replace($text_s);
		}
	}

	ses_update_status($usr->sid, '{TEMPLATE: pm_update}');
	
/*{POST_HTML_PHP}*/
	
	$cur_ppage = tmpl_cur_ppage('', $folders);

	if (isset($_POST['preview']) || isset($_POST['spell'])) {
		$text = apply_custom_replace($_POST['msg_body']);
		$text_s = apply_custom_replace($_POST['msg_subject']);

		switch ($PRIVATE_TAGS) {
			case 'ML':
				$text = tags_to_html($text, $PRIVATE_IMAGES);
				break;
			case 'HTML':
				break;
			default:
				$text = nl2br(htmlspecialchars($text));
		}
		if ($PRIVATE_MSG_SMILEY == 'Y' && !isset($msg_smiley_disabled)) {
			$text = smiley_to_post($text);
		}
		$text_s = htmlspecialchars($text_s);
	
		$spell = (isset($_POST['spell']) && unction_exists('pspell_config_create') && $usr->pspell_lang) ? 1 : 0;
	
		if ($spell && strlen($text)) {
			$text = check_data_spell($text, 'body');
		}
		fud_wordwrap($text);

		$subj = ($spell && !$no_spell_subject && $text_s) ? check_data_spell($text_s, 'subject') : $text_s;

		$signature = ($ALLOW_SIGS == 'Y' && $usr->sig && isset($msg_show_sig)) ? '{TEMPLATE: signature}' : '';
		$apply_spell_changes = $spell ? '{TEMPLATE: apply_spell_changes}' : '';
		$preview_message = '{TEMPLATE: preview_message}';
	} else {
		$preview_message = '';
	}

	$post_error = is_post_error() ? '{TEMPLATE: post_error}' : '';

	$to_err = get_err('msg_to_list');
	$msg_subect_err = get_err('msg_subject');
	$message_err = get_err('msg_body',1);

	$post_smilies = $PRIVATE_MSG_SMILEY == 'Y' ? draw_post_smiley_cntrl() : '';
	$post_icons = draw_post_icons($msg_icon);
	$fud_code_icons = $PRIVATE_TAGS == 'ML' ? '{TEMPLATE: fud_code_icons}' : '';

	$post_options = tmpl_post_options('private');

	if ($PRIVATE_ATTACHMENTS > 0) {	
		$file_attachments = draw_post_attachments((isset($attach_list) ? $attach_list : ''), round($PRIVATE_ATTACH_SIZE / 1024), $PRIVATE_ATTACHMENTS, $attach_control_error);
	} else {
		$file_attachments = '';	
	}

	$msg_track_check = isset($msg_track) ? ' checked' : '';
	$msg_show_sig_check = (isset($msg_show_sig) || $usr->append_sig == 'Y')  ? ' checked' : '';

	if ($PRIVATE_MSG_SMILEY == 'Y') {
		$msg_smiley_disabled_check = isset($msg_smiley_disabled) ? ' checked' : '';
		$disable_smileys = '{TEMPLATE: disable_smileys}';
	} else {
		$disable_smileys = '';
	}

	if ($SPELL_CHECK_ENABLED == 'Y' && function_exists('pspell_config_create') && $usr->pspell_lang) {
		$spell_check_button = '{TEMPLATE: spell_check_button}';
	} else {
		$spell_check_button = '';
	}

	if ($reply && ($mm = db_sab('SELECT p.*, u.alias, u.invisible_mode, u.posted_msg_count, u.join_date, u.last_visit FROM {SQL_TABLE_PREFIX}pmsg p INNER JOIN {SQL_TABLE_PREFIX}users u ON p.ouser_id=u.id WHERE p.duser_id='._uid.' AND p.id='.$reply))) {
		fud_use('drawpmsg.inc');	
		$reference_msg = tmpl_drawpmsg($mm, TRUE);
		$reference_msg = '{TEMPLATE: reference_msg}';
	} else {
		$reference_msg = '';
	}

/*{POST_PAGE_PHP_CODE}*/
?>
{TEMPLATE: PPOST_PAGE}