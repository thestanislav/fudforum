<?php
/***************************************************************************
* copyright            : (C) 2001-2003 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: users.inc.t,v 1.98 2003/11/26 19:33:41 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
***************************************************************************/

function init_user()
{
	$o1 =& $GLOBALS['FUD_OPT_1'];
	$o2 =& $GLOBALS['FUD_OPT_2'];

	/* we need to parse S & rid right away since they are used during user init */
	if ($o2 & 32768 && !empty($_SERVER['PATH_INFO'])) {
		$p = explode('/', substr($_SERVER['PATH_INFO'], 1, -1));
		if ($o1 & 128) {
			$_GET['S'] = array_pop($p);
		}
		if ($o2 & 8192) {
			$_GET['rid'] = array_pop($p);
		}
		$_SERVER['QUERY_STRING'] = $_SERVER['PATH_INFO'] . '?' . $_SERVER['QUERY_STRING'];
	}

	header("Expires: Mon, 21 Jan 1980 06:01:01 GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
	header("Pragma: no-cache");

	$sq = 0;
	/* fetch an object with the user's session, profile & theme info */
	if (!($u = ses_get())) {
		/* new anon user */
		$u = ses_anon_make();
	} else if ($u->id != 1 && (!count($_POST) || sq_check(1, $u->sq, $u->id, $u->ses_id))) { /* store the last visit date for registered user */
		q('UPDATE {SQL_TABLE_PREFIX}users SET last_visit='.__request_timestamp__.' WHERE id='.$u->id);
		if ($GLOBALS['FUD_OPT_3'] & 1) {
			setcookie($GLOBALS['COOKIE_NAME'], $u->ses_id, 0, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
		}
		if (__request_timestamp__ - $u->last_visit > 300) {
			$sq = regen_sq($u->id);
		} else {
			$sq =& $u->sq;
		}
	}
	if ($u->data) {
		$u->data = @unserialize($u->data);
	}
	$u->users_opt = (int) $u->users_opt;

	/* this should allow path_info & normal themes to work properly within 1 forum */
	if ($o2 & 32768 && !($u->theme_opt & 4)) {
		$o2 ^= 32768;
	}

	/* set timezone */
	@putenv('TZ=' . $u->time_zone);
	/* set locale */
	setlocale(LC_ALL, $u->locale);

	/* view format for threads & messages */
	define('d_thread_view', $u->users_opt & 256 ? 'msg' : 'tree');
	define('t_thread_view', $u->users_opt & 128 ? 'thread' : 'threadt');

	/* theme path */
	@define('fud_theme', 'theme/' . $u->theme_name . '/');

	/* define _uid, which, will tell us if this is a 'real' user or not */
	define('__fud_real_user__', ($u->id != 1 ? $u->id : 0));
	define('_uid', __fud_real_user__ && ($u->users_opt & 131072) && !($u->users_opt & 2097152) ? $u->id : 0);

	/* define constants used to track URL sessions & referrals */
	if ($o1 & 128) {
		define('s', $u->ses_id); define('_hs', '<input type="hidden" name="S" value="'.s.'"><input type="hidden" name="SQ" value="'.$sq.'">');
		if ($o2 & 8192) {
			if ($o2 & 32768) {
				define('_rsid', __fud_real_user__ . '/' . s . ($sq ? '/?SQ='.$sq : ''));
			} else {
				define('_rsid', 'rid='.__fud_real_user__.'&amp;S='.s.($sq ? '&amp;SQ='.$sq : ''));
			}
		} else {
			if ($o2 & 32768) {
				define('_rsid', s . ($sq ? '/?SQ='.$sq : ''));
			} else {
				define('_rsid',  'S='.s.($sq ? '&amp;SQ='.$sq : ''));
			}
		}
	} else {
		define('s', ''); define('_hs', '<input type="hidden" name="SQ" value="'.$sq.'">');
		if ($o2 & 8192) {
			if ($o2 & 32768) {
				define('_rsid', __fud_real_user__ . ($sq ? '/?SQ='.$sq : ''));
			} else {
				define('_rsid',  'rid='.__fud_real_user__. ($sq ? '&amp;SQ='.$sq : ''));
			}
		} else {
			if ($o2 & 32768) {
				define('_rsid', ($sq ? '?SQ='.$sq : ''));
			} else {
				define('_rsid', ($sq ? 'SQ='.$sq : ''));
			}
		}
	}
	define('_rsidl', ($o2 & 32768 ? _rsid : str_replace('&amp;', '&', _rsid)));

	/* continuation of path info parsing */
	if (isset($p)) {
		define('pinfo_page', $p[0]);

			switch ($p[0]) {
				case 'm': /* goto specific message */
					$_GET['t'] = d_thread_view;
					$_GET['goto'] = $p[1];
					if (isset($p[2])) {
						$_GET['th'] = $p[2];
						if (isset($p[3])) {
							$_GET['start'] = $p[3];
							if ($p[3]) {
								$_GET['t'] = 'msg';
								unset($_GET['goto']);
							}

							if (isset($p[4])) {
								if ($p[4] === 'prevloaded') {
									$_GET['prevloaded'] = 1;
									$i = 5;
								} else {
									$i = 4;
								}

								if (isset($p[$i])) {
									$_GET['rev'] = $p[$i];
									if (isset($p[$i+1])) {
										$_GET['reveal'] = $p[$i+1];
									}
								}
							}
						}
					}
					break;

				case 't': /* view thread */
					$_GET['t'] = d_thread_view;
					$_GET['th'] = $p[1];
					if (isset($p[2])) {
						$_GET['start'] = $p[2];
						if (!empty($p[3])) {
							$_GET[$p[3]] = 1;
						}
					}
					break;

				case 'f': /* view forum */
					$_GET['t'] = t_thread_view;
					$_GET['frm_id'] = $p[1];
					if (isset($p[2])) {
						$_GET['start'] = $p[2];
						if (isset($p[3])) {
							if ($p[3] === '0') {
								$_GET['sub'] = 1;
							} else {
								$_GET['unsub'] = 1;
							}
						}
					}
					break;

				case 'r':
					$_GET['t'] = 'post';
					$_GET[$p[1]] = $p[2];
					if (isset($p[3])) {
						$_GET['reply_to'] = $p[3];
						if (isset($p[4])) {
							if ($p[4]) {
								$_GET['quote'] = 'true';
							}
							if (isset($p[5])) {
								$_GET['start'] = $p[5];
							}
						}
					}
					break;

				case 'u': /* view user's info */
					$_GET['t'] = 'usrinfo';
					$_GET['id'] = $p[1];
					break;

				case 'i':
					$_GET['t'] = 'index';
					if (isset($p[1])) {
						$_GET['c'] = $p[1];
					}
					break;

				case 'fa':
					$_GET['t'] = 'getfile';
					$_GET['id'] = $p[1];
					if (!empty($p[2])) {
						$_GET['private'] = 1;
					}
					break;

				case 'sp': /* show posts */
					$_GET['t'] = 'showposts';
					$_GET['id'] = $p[1];
					if (isset($p[2])) {
						$_GET['so'] = $p[2];
						if (isset($p[3])) {
							$_GET['start'] = $p[3];
						}
					}
					break;

				case 'l': /* login/logout */
					$_GET['t'] = 'login';
					if (isset($p[1])) {
						$_GET['logout'] = 1;
					}
					break;

				case 'e':
					$_GET['t'] = 'error';
					break;

				case 'st':
					$_GET['t'] = $p[1];
					$_GET['th'] = $p[2];
					$_GET['notify'] = $p[3];
					$_GET['opt'] = $p[4] ? 'on' : 'off';
					$_GET['start'] = $p[5];
					break;

				case 'sf':
					$_GET['t'] = $p[1];
					$_GET['frm_id'] = $p[2];
					$_GET[$p[3]] = 1;
					$_GET['start'] = $p[4];
					break;

				case 'sl':
					$_GET['t'] = 'subscribed';
					if ($p[1] == 'start') {
						$_GET['start'] = $p[2];
					} else {
						if (isset($p[2])) {
							$_GET['th'] = $p[2];
						} else if (isset($p[1])) {
							$_GET['frm_id'] = $p[1];
						}
					}
					break;

				case 'pmm':
					$_GET['t'] = 'ppost';
					if (isset($p[1])) {
						$_GET[$p[1]] = $p[2];
					}
					break;

				case 'pmv':
					$_GET['t'] = 'pmsg_view';
					$_GET['id'] = $p[1];
					if (isset($p[2])) {
						$_GET['dr'] = 1;
					}
					break;

				case 'pdm':
					$_GET['t'] = 'pmsg';
					if (isset($p[1])) {
						if ($p[1] !== 'btn_delete') {
							$_GET['folder_id'] = $p[1];
							if (isset($p[2]) && (int) $p[2]) {
								$_GET['all'] = 1;
							}
						} else {
							$_GET['btn_delete'] = 1;
							$_GET['sel'] = $p[2];
						}
						if (isset($p[3])) {
							$_GET['start'] = $p[3];
						}
					}
					break;

				case 'pl': /* poll list */
					$_GET['t'] = 'polllist';
					if (isset($p[1])) {
						$_GET['uid'] = $p[1];
						if (isset($p[2])) {
							$_GET['start'] = $p[2];
							if (isset($p[3])) {
								$_GET['oby'] = $p[3];
							}
						}
					}
					break;

				case 'ml': /* member list */
					$_GET['t'] = 'finduser';
					if (isset($p[1])) {
						if ($p[1] == '1') { /* order by reg date */
							$_GET['pc'] = 1;
						} else if ($p[1] == '2') { /* order by login */
							$_GET['us'] = 1;
						} /* else order by date */
						if (isset($p[2])) {
							$_GET['start'] = $p[2];
							if (isset($p[3])) {
								$_GET['usr_login'] = urldecode($p[3]);
								if (isset($p[4])) {
									$_GET['usr_email'] = urldecode($p[4]);
									if (isset($p[5])) {
										$_GET['js_redr'] = $p[5];
									}
								}
							}
						}
					}
					break;

				case 'h': /* help */
					$_GET['t'] = 'help_index';
					if (isset($p[1])) {
						$_GET['section'] = $p[1];
					}
					break;

				case 'cv': /* change thread view mode */
					$_GET['t'] = $p[1];
					$_GET['frm_id'] = $p[2];
					break;

				case 'mv': /* change message view mode */
					$_GET['t'] = $p[1];
					$_GET['th'] = $p[2];
					if (isset($p[3])) {
						if ($p[3] !== '0') {
							$_GET['goto'] = $p[3];
						} else {
							$_GET['prevloaded'] = 1;
							$_GET['start'] = $p[4];
							if (isset($p[5])) {
								$_GET['rev'] = $p[5];
								if (isset($p[6])) {
									$_GET['reveal'] = $p[6];
								}
							}
						}
					}
					break;

				case 'pv':
					$_GET['t'] = d_thread_view;
					$_GET['goto'] = $p[1];
					$_GET['pl_view'] = $p[2];
					break;

				case 'rm': /* report message */
					$_GET['t'] = 'report';
					$_GET['msg_id'] = $p[1];
					break;

				case 'rl': /* list of reported messages */
					$_GET['t'] = 'reported';
					if (isset($p[1])) {
						$_GET['del'] = $p[1];
					}
					break;

				case 'd': /* delete thread/message */
					$_GET['t'] = 'mmod';
					$_GET['del'] = $p[1];
					if (isset($p[2])) {
						$_GET['th'] = $p[2];
					}
					break;

				case 'em': /* email forum member */
					$_GET['t'] = 'email';
					$_GET['toi'] = $p[1];
					break;

				case 'mar': /* mark all/forum read */
					$_GET['t'] = 'markread';
					if (isset($p[1])) {
						$_GET['id'] = $p[1];
					}
					break;

				case 'bl': /* buddy list */
					$_GET['t'] = 'buddy_list';
					if (isset($p[1])) {
						if (!empty($p[2])) {
							$_GET['add'] = $p[1];
						} else {
							$_GET['del'] = $p[1];
						}
						if (isset($p[3])) {
							$_GET['redr'] = 1;
						}
					}
					break;

				case 'il': /* ignore list */
					$_GET['t'] = 'ignore_list';
					if (isset($p[1])) {
						if (!empty($p[2])) {
							$_GET['add'] = $p[1];
						} else {
							$_GET['del'] = $p[1];
						}
						if (isset($p[3])) {
							$_GET['redr'] = 1;
						}
					}
					break;

				case 'lk': /* lock/unlock thread */
					$_GET['t'] = 'mmod';
					$_GET['th'] = $p[1];
					$_GET[$p[2]] = 1;
					break;

				case 'stt': /* split thread */
					$_GET['t'] = 'split_th';
					$_GET['th'] = $p[1];
					break;

				case 'ef': /* email to friend */
					$_GET['t'] = 'remail';
					$_GET['th'] = $p[1];
					break;

				case 'lr': /* list referers */
					$_GET['t'] = 'list_referers';
					if (isset($p[1])) {
						$_GET['start'] = $p[1];
					}
					break;

				case 'a':
					$_GET['t'] = 'actions';
					break;

				case 's':
					$_GET['t'] = 'search';
					if (isset($p[1])) {
						$_GET['srch'] = urldecode($p[1]);
						$_GET['field'] = $p[2];
						$_GET['search_logic'] = $p[3];
						$_GET['sort_order'] = $p[4];
						$_GET['forum_limiter'] = $p[5];
						$_GET['start'] = $p[6];
					}
					break;

				case 'p':
					if (!is_numeric($p[1])) {
						$_GET[$p[1]] = $p[2];
					} else {
						$_GET['frm'] = $p[1];
						$_GET['page'] = $p[2];
					}
					break;

				case 'ot':
					$_GET['t'] = 'online_today';
					break;

				case 're':
					$_GET['t'] = 'register';
					if (isset($p[1])) {
						$_GET['reg_coppa'] = $p[1];
					}
					break;

				case 'tt':
					$_GET['t'] = $p[1];
					$_GET['frm_id'] = $p[2];
					break;

				case 'mh':
					$_GET['t'] = 'mvthread';
					$_GET['th'] = $p[1];
					if (isset($p[2], $p[3])) {
						$_GET[$p[2]] = $p[3];
					}
					break;

				case 'mn':
					$_GET['t'] = $p[1];
					$_GET['th'] = $p[2];
					$_GET['notify'] = $p[3];
					$_GET['opt'] = $p[4];
					if ($p[1] == 'msg') {
						$_GET['start'] = $p[5];
					} else {
						$_GET['mid'] = $p[5];
					}
					break;

				case 'tr':
					$_GET['t'] = 'ratethread';
					break;

				case 'gm':
					$_GET['t'] = 'groupmgr';
					if (isset($p[1], $p[2], $p[3])) {
						$_GET[$p[1]] = $p[2];
						$_GET['group_id'] = $p[3];
					}
					break;

				case 'te':
					$_GET['t'] = 'thr_exch';
					if (isset($p[1], $p[2])) {
						$_GET[$p[1]] = $p[2];
					}
					break;

				case 'mq':
					$_GET['t'] = 'modque';
					if (isset($p[1], $p[2])) {
						$_GET[$p[1]] = $p[2];
					}
					break;

				case 'pr':
					$_GET['t'] = 'pre_reg';
					$_GET['coppa'] = $p[1];
					break;

				case 'qb':
					$_GET['t'] = 'qbud';
					if (isset($p[1])) {
						$_GET['all'] = 1;
					}
					break;

				case 'po':
					$_GET['t'] = 'poll';
					$_GET['frm_id'] = $p[1];
					if (isset($p[2])) {
						$_GET['pl_id'] = $p[2];
						if (isset($p[3], $p[4])) {
							$_GET[$p[3]] = $p[4];
						}
					}
					break;

				case 'sm':
					$_GET['t'] = 'smladd';
					break;

				case 'mk':
					$_GET['t'] = 'mklist';
					$_GET['tp'] = $p[1];
					break;

				case 'rp':
					$_GET['t'] = 'rpasswd';
					break;

				case 'as':
					$_GET['t'] = 'avatarsel';
					break;

				case 'sel':
					$_GET['t'] = 'selmsg';
					$c = (count($p) - 1) / 2;
					$j = 0;
					for ($i = 0; $i < $c; $i++) {
						@$_GET[$p[++$j]] = @$p[++$j];
					}
					break;

				case 'pml':
					$_GET['t'] = 'pmuserloc';
					$_GET['js_redr'] = $p[1];
					if (isset($p[2])) {
						$_GET['overwrite'] = 1;
					}
					break;

				case 'rst':
					$_GET['t'] = 'reset';
					if (isset($p[1])) {
						$_GET['email'] = urldecode($p[1]);
					}
					break;

				case 'cpf':
					$_GET['t'] = 'coppa_fax';
					break;

				case 'cp':
					$_GET['t'] = 'coppa';
					break;

				case 'rc':
					$_GET['t'] = 'reg_conf';
					break;

				case 'ma':
					$_GET['t'] = 'mnav';
					$_GET['u'] = isset($p[2]) ? $p[2] : 0;
					$_GET['rng'] = isset($p[1]) ? $p[1] : 0;
					$_GET['start'] = isset($p[2]) ? $p[3] : 0;
					break;

				case 'met':
					$_GET['t'] = 'merge_th';
					if (isset($p[1])) {
						$_GET['frm'] = $p[1];
					}
					break;

				default:
					$_GET['t'] = 'index';
					break;
			}
	}
	return $u;
}

function user_alias_by_id($id)
{
	return q_singleval('SELECT alias FROM {SQL_TABLE_PREFIX}users WHERE id='.$id);
}

function user_register_forum_view($frm_id)
{
	q('UPDATE {SQL_TABLE_PREFIX}forum_read SET last_view='.__request_timestamp__.' WHERE forum_id='.$frm_id.' AND user_id='._uid);
	if (!db_affected()) {
		db_li('INSERT INTO {SQL_TABLE_PREFIX}forum_read (forum_id, user_id, last_view) VALUES ('.$frm_id.', '._uid.', '.__request_timestamp__.')', $ef);
	}
}

function user_register_thread_view($thread_id, $tm=__request_timestamp__, $msg_id=0)
{
	if (!db_li('INSERT INTO {SQL_TABLE_PREFIX}read (last_view, msg_id, thread_id, user_id) VALUES('.$tm.', '.$msg_id.', '.$thread_id.', '._uid.')', $ef)) {
		q('UPDATE {SQL_TABLE_PREFIX}read SET last_view='.$tm.', msg_id='.$msg_id.' WHERE thread_id='.$thread_id.' AND user_id='._uid);
	}
}

function user_set_post_count($uid)
{
	$pd = db_saq("SELECT MAX(id),count(*) FROM {SQL_TABLE_PREFIX}msg WHERE poster_id=".$uid." AND apr=1");
	$level_id = (int) q_singleval('SELECT id FROM {SQL_TABLE_PREFIX}level WHERE post_count <= '.$pd[1].' ORDER BY post_count DESC LIMIT 1');
	q('UPDATE {SQL_TABLE_PREFIX}users SET u_last_post_id='.(int)$pd[0].', posted_msg_count='.(int)$pd[1].', level_id='.$level_id.' WHERE id='.$uid);
}

function user_mark_all_read($id)
{
	q('UPDATE {SQL_TABLE_PREFIX}users SET last_read='.__request_timestamp__.' WHERE id='.$id);
	q('DELETE FROM {SQL_TABLE_PREFIX}read WHERE user_id='.$id);
	q('DELETE FROM {SQL_TABLE_PREFIX}forum_read WHERE user_id='.$id);
}

function user_mark_forum_read($id, $fid, $last_view)
{
	if (__dbtype__ == 'mysql') {
		q('REPLACE INTO {SQL_TABLE_PREFIX}read (user_id, thread_id, msg_id, last_view) SELECT '.$id.', id, last_post_id, '.__request_timestamp__.' FROM {SQL_TABLE_PREFIX}thread WHERE forum_id='.$fid);
	} else {
		if (!db_li('INSERT INTO {SQL_TABLE_PREFIX}read (user_id, thread_id, msg_id, last_view) SELECT '.$id.', id, last_post_id, '.__request_timestamp__.' FROM {SQL_TABLE_PREFIX}thread WHERE forum_id='.$fid)) {
			q("UPDATE {SQL_TABLE_PREFIX}read SET user_id=".$id.", thread_id=id, msg_id=last_post_id, last_view=".__request_timestamp__." WHERE user_id=".$id." SELECT id, last_post_id FROM {SQL_TABLE_PREFIX}thread WHERE forum_id=".$fid);
		}
	}
}

function sq_check($post, $sq, $uid=__fud_real_user__, $ses=s)
{
	/* no sequence # check for anonymous users or when multi-host login is enabled */
	if (!$uid || $GLOBALS['FUD_OPT_2'] & 256) {
		return 1;
	}

	if ($post && isset($_POST['SQ'])) {
		$s = $_POST['SQ'];
	} else if (!$post && isset($_GET['SQ'])) {
		$s = $_GET['SQ'];
	} else {
		$s = 0;
	}

	if ($sq !== $s) {
		header('Location: {ROOT}?S='.$ses);
		exit;
	}

	return 1;
}

function regen_sq($uid=__fud_real_user__)
{
	$sq = md5(get_random_value(128));
	q("UPDATE {SQL_TABLE_PREFIX}users SET sq='".$sq."' WHERE id=".$uid);
	return $sq;
}

if (!defined('forum_debug')) {
	$GLOBALS['usr'] =& init_user();
}
?>