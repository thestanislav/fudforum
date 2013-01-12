<?php

if( !class_exists('FUD_groups_opt') ) {
    class FUD_groups_opt 
    {
        const VISIBLE = 1;
        const READ  = 2;
        const POST = 4;
        const REPLY = 8;
        const EDIT = 16;
        const DEL = 32;
        const STICKY = 64;
        const POLL = 128;
        const FILE = 256;
        const VOTE = 512;
        const RATE = 1024;
        const SPLIT = 2048;
        const LOCK = 4096;
        const MOVE = 8192;
        const SML = 16384;
        const IMG = 32768;
        const SEARCH = 262144;
    }
}

if( !class_exists('FUD_users_opt') ) {
    class FUD_users_opt 
    {
        const ANONYMOUS = 0;
        const REGISTERED = 2147483647;
        const DISPLAY_EMAIL = 1; // (ON/OFF)
        const NOTIFY = 2; // (ON/OFF)
        const NOTIFY_METHOD = 4; // (Mail/ICQ)
        const IGNORE_ADMIN = 8; // (ON/OFF)
        const EMAIL_MESSAGES = 16; // (ON/OFF)
        const PM_MESSAGES = 32; // (ON/OFF)
        const PM_NOTIFY = 64; // (ON/OFF)
        const DEFAULT_TOPIC_VIEW = 128; // (MSG/TREE)
        const DEFAULT_MESSAGE_VIEW = 256; // (MSG/TREE)
        const GENDER_UNSPECIFIED = 512; // (UNSPECIFIED)
        const GENDER = 1024; // (MALE/FEMALE)
        const APPEND_SIG = 2048; // (ON/OFF)
        const SHOW_SIGS = 4096; // (ON/OFF)
        const SHOW_AVATARS = 8192; // (ON/OFF)
        const SHOW_IM = 16384; // (ON/OFF)
        const INVISIBLE = 32768; // (ON/OFF)
        const BLOCKED = 65536; // (ON/OFF)
        const EMAIL_CONF = 131072; // (ON/OFF)
        const COPPA = 262144; // (ON/OFF)
        const IS_MOD = 524288; // (ON/OFF)
        const IS_ADMIN = 1048576; // (ON/OFF)
        const ACC_STATUS = 2097152; // (PENDING/APPROVED)
        const AVATAR_ABSENT = 4194304; // (No Avatar)
        const AVATAR_APPROVED = 8388608;
        const AVATAR_NOT_APPROVED = 16777216;
        const ADMIN_DISABLED_PM = 33554432; // (ON/OFF)
        const ADMIN_DISABLED_SIGNATURE = 67108864; // (ON/OFF)
        const EMAIL_NOTIFY_DISABLED = 134217728; // (ON/OFF)
        # 268435456 account moderator (ON/OFF)
        # 536870912 always moderate user's posts (ON/OFF)
        const IS_SPIDER = 1073741824;
    }
}

/**
  * TRANSITIONAL. Fixes relative URLs using the CI's site_url() function.
  *
  * This function is TRANSITIONAL, it should be removed once the core of 
  * fudForum has been migrated to CI. The function takes as input some 
  * HTML code and replaces relative links (which do not work) with absolute 
  * links generated using CI's site_url() function.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param string $html_body  The body of the HTML page which URLs need to
  * be fixed.
  * 
  * @return string Contents of $html_body with corrected URLs
  */
function fix_relative_urls( $html_body )
{
	$pattern = array( '#(src|href)(\s*)=(\s*)"(.*)"#' );
	return preg_replace_callback( $pattern, "preg_callback", $html_body );
}

function preg_callback( $matches )
{
	$pos = strpos( $matches[4], 'http' );
	if( FALSE === $pos )
		return $matches[1].' = "'.base_url( $matches[4] ).'"';
	else
		return $matches[0];
}