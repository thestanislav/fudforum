<?php
	chdir(dirname(__FILE__));
	require "./mcrypt.inc";

	$data = file_get_contents("php://stdin");

	$p = strpos($data, "\n\n");
	$p2 = strpos($data, "\r\n\r\n");
	if ($p2 !== false && ($p > $p2 || $p === false)) {
		$p = p2;
	}

	$data = trim(substr($data, $p));
	$data = str_replace("\r\n", "\n", $data);
	$values = explode("\n", $data);
	$type = array_shift($values);

	$decoded = decode_strings($values);
	$ok = "Failed";

	define('shell_script', 1);
	define('forum_debug', 1);

	require("/home/qrca/forum/include/GLOBALS.php");
	fud_use('err.inc');
	fud_use('db.inc');

	switch ($type) {
		case 'UPDATE':
			$qrca_id = (int) $decoded[0];
			$alias = htmlspecialchars($decoded[1]);
			if (($uid = q_singleval("SELECT id FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."users WHERE qrca_id=".$qrca_id))) {
				q("UPDATE ".$GLOBALS['DBHOST_TBL_PREFIX']."users SET 
					login='".addslashes($decoded[1])."',
					alias='".addslashes($alias)."',
					email='".addslashes($decoded[3])."',
					name='".addslashes($decoded[2])."'
				WHERE id=".$uid);
			} else {
				if (($uid = q_singleval("SELECT id FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."users WHERE login='".addslashes($decoded[1])."'"))) {
					q("UPDATE ".$GLOBALS['DBHOST_TBL_PREFIX']."users SET 
						alias='".addslashes($alias)."',
						email='".addslashes($decoded[3])."',
						name='".addslashes($decoded[2])."',
						qrca_id=".$qrca_id."
					WHERE id=".$uid);
				} else {
					$opt = 4|16|32|128|256|512|2048|4096|8192|16384|131072|4194304;
					$o2 =& $GLOBALS['FUD_OPT_2'];
					if (!($o2 & 4)) {
						$opt ^= 128;
					}
					if (!($o2 & 8)) {
						$opt ^= 256;
					}
				
					q("INSERT INTO ".$GLOBALS['DBHOST_TBL_PREFIX']."users (login, alias, email, qrca_id, name, theme, users_opt, join_date) VALUES(
						'".addslashes($decoded[1])."',
						'".addslashes($alias)."',
						'".addslashes($decoded[3])."',
						".$qrca_id.",
						'".addslashes($decoded[2])."',
						".q_singleval("SELECT id FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."themes WHERE theme_opt=3 LIMIT 1").",
						".$opt.",
						".time()."
					)");
				}
			}
			$ok = "Succeeded";
			break;

		case 'DELETE':
			$del_id = (int) $decoded[0];
			if ($del_id) {
				$uid = q_singleval("SELECT id FROM ".$GLOBALS['DBHOST_TBL_PREFIX']."users WHERE qrca_id=".$del_id);
				if (!$uid) {
					break;
				}

				$GLOBALS['usr']->users_opt = 1048576;
				$_POST = array(1);

				fud_use('private.inc');
				fud_use('adm.inc', true);
				fud_use('users_adm.inc', true);

				usr_delete($uid);
				$ok = "Succeeded";
			}
			break;

		default: /* error, should not happen */
			break;
	}

	mail("forumreceive@qrca.org", "Re: QRCA Forum Access Update", $ok."\n".$data, "From: qrca@s2.prohost.org\r\nReply-To: webmaster@qrca.org");
?>