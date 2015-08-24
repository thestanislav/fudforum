<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
  * Wrapper for the scripts/fudapi.php library with some bonuses
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  */

class Fud_Library
{
    //private static $instance;

    public function __construct()
    {
      $this->CI = &get_instance();
      $this->CI->load->helper( 'fud' );
      require_once 'GLOBALS.php';
      require_once $GLOBALS['DATA_DIR'].'/scripts/fudapi.inc.php';
      require_once $GLOBALS['DATA_DIR'].'/scripts/forum_login.php';
    }
    
    function get_uid_from_login($login)
    {
      return external_get_user_from_login($login);
    }

    function get_uid_by_auth($login, $passwd)
    {
    	return external_get_user_by_auth( $login, $passwd );
    }

  function login( $uid )
  {
    return external_fud_login( $uid );
  }

  function logout( $uid )
  {
    return external_fud_logout( $uid );
  }

  function fetch_message( $mid )
  {
    return fud_fetch_msg( $mid );
  }

  function fetch_topic_info( $tid )
  {
    return fud_fetch_topic( $tid );
  }

  function fetch_full_topic( $tid )
  {
    return fud_fetch_full_topic( $tid );
  }

  function fetch_topics_by_forum( $fid, $sort = FALSE, $limit = null )
  {
    return fud_fetch_forum_topics( $fid, $sort, $limit );
  }

  function fetch_categories( $catIds = null, $sort = FALSE )
  {
    return fud_fetch_cat( $catIds, $sort );
  }

  function fetch_forums_by_category( $categories = null, $sort = FALSE )
  {
    return fud_fetch_cat_forums( $categories, $sort );
  }

  function fetch_forums( $frmId = null, $sort = FALSE )
  {
    return fud_fetch_forum( $frmId, $sort );
  }

  function new_reply( $subject, $body, $mode, $author, $rep_id, $icon=null, $attach=null, $poll=null, $time=null )
  {
    return fud_new_reply($subject, $body, $mode, $author, $rep_id, $icon, $attach, $poll, $time);
  }
  
  function new_topic( $subject, $desc=null, $body, $mode, $author, $fid, $icon=null, $attach=null, $poll=null, $time=null )
  {
    fud_new_topic($subject, $body, $mode, $author, $fid, $icon, $attach, $poll, $time, $desc);
  }
  
  function add_user($vals)
  {
    // Check for required fields.
    foreach (array('login', 'passwd', 'email', 'name') as $v) {
        if (empty($vals[$v])) {
            $err = 'missing value for a required field '. $v;
            return $err;
        }
    }

    $passwd = $vals['passwd'];

    // Generate unique salt to distrupt rainow tables
    if( !array_key_exists('salt', $vals) || empty( $vals['salt'] ) ) {
        $vals['salt'] = substr(md5(uniqid(mt_rand(), true)), 0, 9);
    }

    $salt = $vals['salt'];

    // Password may already be encrypted (prefixed with 'MD5' or 'SHA1').
    if (!strncmp($passwd, 'SHA1:', 5)) {
        $vals['passwd'] = substr($passwd, 5);
    } else if (!strncmp($vals['passwd'], 'MD5:', 4)) {
        $vals['passwd'] = substr($passwd, 4);
        $vals['salt']   = '';
    } else {
        // Probably a plain text password.
        $vals['passwd'] = sha1($salt . sha1($passwd));
    }

    if (empty($vals['alias'])) {
        if (strlen($vals['login']) > $GLOBALS['MAX_LOGIN_SHOW']) {
            $vals['alias'] = substr($vals['login'], 0, $GLOBALS['MAX_LOGIN_SHOW']);
        } else {
            $vals['alias'] = $vals['login'];
        }
        $vals['alias'] = htmlspecialchars($vals['alias']);
    }

    // Some fields must be unique, check them.
    foreach (array('login', 'email', 'alias') as $v) {
        if (q_singleval('SELECT id FROM '. $GLOBALS['DBHOST_TBL_PREFIX'] .'users WHERE '. $v .'='. _esc($vals[$v]))) {
            $err = 'value for '. $v .' must be unique, specified value of '. $vals[$v] .' already exists.';
            return $err;
        }
    }

    $o2 =& $GLOBALS['FUD_OPT_2'];
    $users_opt = 4|16|32|128|256|512|2048|4096|8192|16384|131072|4194304;
    $theme = q_singleval(q_limit('SELECT id FROM '. $GLOBALS['DBHOST_TBL_PREFIX'] .'themes WHERE theme_opt>=2 AND '. q_bitand('theme_opt', 2) .' > 0', 1));
    $time_zone =& $GLOBALS['SERVER_TZ'];
    $posts_ppg =& $GLOBALS['POSTS_PER_PAGE'];
    if (!($o2 & 4)) {
        $users_opt ^= 128;
    }
    if (!($o2 & 8)) {
        $users_opt ^= 256;
    }
    if ($o2 & 1) {
        $o2 ^= 1;
    }
    $reg_ip = '127.0.0.1';
    $last_visit = $last_read = $join_date = __request_timestamp__;

    // Make sure all fields are set.
    foreach( array('login','alias','passwd','name','email','icq','aim','yahoo','msnm','jabber','google','skype','twitter',
        'affero','posts_ppg','time_zone','birthday','last_visit','conf_key','user_image',
        'join_date','location','theme','occupation','interests','referer_id','last_read',
        'sig','home_page','bio','users_opt','reg_ip') as $v) {
        if (empty($vals[$v])) {
            $vals[$v] = isset($$v) ? $$v : '';
        }
    }
    $qStr = 'INSERT INTO
            '.$GLOBALS['DBHOST_TBL_PREFIX'].'users (
                login,
                alias,
                passwd,
                salt,
                name,
                email,
                icq,
                aim,
                yahoo,
                msnm,
                jabber,
                google,
                skype,
                twitter,
                affero,
                posts_ppg,
                time_zone,
                birthday,
                last_visit,
                conf_key,
                user_image,
                join_date,
                location,
                theme,
                occupation,
                interests,
                referer_id,
                last_read,
                sig,
                home_page,
                bio,
                users_opt
            ) VALUES (
                '. _esc($vals['login']) .',
                '. _esc($vals['alias']) .',
                \''. $vals['passwd'] .'\',
                \''. $vals['salt'] .'\',
                '. _esc($vals['name']) .',
                '. _esc($vals['email']) .',
                '. (int)$vals['icq'] .',
                '. ssn(urlencode($vals['aim'])) .',
                '. ssn(urlencode($vals['yahoo'])) .',
                '. ssn(urlencode($vals['msnm'])) .',
                '. ssn(htmlspecialchars($vals['jabber'])) .',
                '. ssn(htmlspecialchars($vals['google'])) .',
                '. ssn(htmlspecialchars($vals['skype'])) .',
                '. ssn(htmlspecialchars($vals['twitter'])) .',
                '. ssn(urlencode($vals['affero'])) .',
                '. (int)$vals['posts_ppg'] .',
                '. _esc($vals['time_zone']) .',
                '. ssn($vals['birthday']) .',
                '. (int)$vals['last_visit'] .',
                \''. $vals['conf_key'] .'\',
                '. ssn(htmlspecialchars($vals['user_image'])) .',
                '. $vals['join_date'] .',
                '. ssn($vals['location']) .',
                '. (int)$vals['theme'] .',
                '. ssn($vals['occupation']) .',
                '. ssn($vals['interests']) .',
                '. (int)$vals['referer_id'] .',
                '. (int)$vals['last_read'] .',
                '. ssn($vals['sig']) .',
                '. ssn(htmlspecialchars($vals['home_page'])) .',
                '. ssn($vals['bio']) .',
                '. (int)$vals['users_opt'] .'
            )
        ';
    
    $q = $this->CI->db->query($qStr);
        
    return $q;
  }

  function update_user($vals, &$err)
  {
    return fud_update_user($vals, $err);
  }
  
  function update_message( $subject, $body, $mode, $author, $mid, $icon=null, $attach=null,
                           $poll=null )
  {
    fud_update_message( $subject, $body, $mode, $author, $mid, $icon, $attach, $poll);
  }
  
  function get_last_forum_visit( $fid, $uid )
  {
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    $time = time();
    
    $qStr = "SELECT last_view FROM {$prefix}forum_read
             WHERE `forum_id`='{$fid}' AND `user_id`='{$uid}' ";
    $q = $this->CI->db->query( $qStr );
    
    if( $q->num_rows() == 1 )
    {
      return $q->row()->last_view;
    }
    
    return NULL;
  }
  
  function get_last_topic_visit( $tid, $uid )
  {
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    $time = time();
    
    $qStr = "SELECT last_view FROM {$prefix}read
             WHERE `thread_id`='{$tid}' AND `user_id`='{$uid}' ";
    $q = $this->CI->db->query( $qStr );
    
    if( $q->num_rows() == 1 )
    {
      return $q->row()->last_view;
    }
    
    return NULL;
  }
  
  function update_last_forum_visit( $fid, $uid )
  {
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    $time = time();
    /* 
      // MySQL
      INSERT INTO ...
      ON DUPLICATE KEY UPDATE ..
    */
    /* 
      // SQLite
      // One way is:
      INSERT OR IGNORE INTO ....;
      UPDATE ...;
      
      // Another
      INSERT... ON CONFLICT REPALCE ...;
      
      Also -> http://stackoverflow.com/questions/418898/sqlite-upsert-not-insert-or-replace/4330694#4330694
    */
    /*
      // PostgreSQL
      // http://stackoverflow.com/questions/1109061/insert-on-duplicate-update-in-postgresql
      UPDATE table SET field='C', field2='Z' WHERE id=3;
        INSERT INTO table (id, field, field2)
      SELECT 3, 'C', 'Z'
      WHERE NOT EXISTS (SELECT 1 FROM table WHERE id=3);
    */
    /*
      // Standard SQL>=2003
      // http://en.wikipedia.org/wiki/Merge_(SQL)
      MERGE INTO tablename USING table_reference ON (condition)
        WHEN MATCHED THEN
       UPDATE SET column1 = value1 [, column2 = value2 ...]
        WHEN NOT MATCHED THEN
       INSERT (column1 [, column2 ...]) VALUES (value1 [, value2 ...
    */
    $qStr = "SELECT * FROM {$prefix}forum_read
             WHERE `forum_id`='{$fid}' AND `user_id`='{$uid}' ";
    $q = $this->CI->db->query( $qStr );
    
    if( $q->num_rows() == 1 )
    {
      $qStr = "UPDATE {$prefix}forum_read
              SET `last_view`='{$time}' 
              WHERE `forum_id`='{$fid}' AND `user_id`='{$uid}' ";
    }
    else
    {
      $qStr = "INSERT INTO {$prefix}forum_read (user_id, forum_id, last_view)
               VALUES ( '{$uid}' , '{$fid}', '{$time}' ) ";
    }
    $q = $this->CI->db->query( $qStr );
              
  }
  
  function update_last_topic_visit( $fid, $uid )
  {
    /*
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    $time = time();
    
    $qStr = "SELECT * FROM {$prefix}forum_read
             WHERE `forum_id`='{$fid}' AND `user_id`='{$uid}' ";
    $q = $this->CI->db->query( $qStr );
    
    if( $q->num_rows() == 1 )
    {
      $qStr = "UPDATE {$prefix}forum_read
              SET `last_view`='{$time}' 
              WHERE `forum_id`='{$fid}' AND `user_id`='{$uid}' ";
    }
    else
    {
      $qStr = "INSERT INTO {$prefix}forum_read (user_id, forum_id, last_view)
               VALUES ( '{$uid}' , '{$fid}', '{$time}' ) ";
    }
    $q = $this->CI->db->query( $qStr );
    */              
  }
  
  function check_permissions( $rid, $uid = 0 )
  {
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    $id2 = $uid ? 2147483647 : 0;

    $joins = "";
    if( $uid )
    {
      $joins = " LEFT JOIN {$prefix}mod AS mo \n" .
                "   ON mo.user_id='{$uid}' AND mo.forum_id='{$rid}' \n".
                " LEFT JOIN {$prefix}group_cache AS g2 \n".
                "   ON g2.user_id='{$uid}' AND g2.resource_id='{$rid}' \n".
                " LEFT JOIN {$prefix}users AS usr \n ".
                "   ON usr.id='{$uid}' ";
    }
                  
    $selects = " g1.group_cache_opt ";
    if( $uid )
    {
      $selects = "usr.users_opt, mo.id AS is_mod, 
                  COALESCE(g2.group_cache_opt, g1.group_cache_opt) 
                  AS group_cache_opt ";
    }
    
    $qStr = " SELECT {$selects}
              FROM {$prefix}group_cache AS g1
              {$joins}
              WHERE ( g1.user_id='{$id2}' AND g1.resource_id='{$rid}' ) ";
    $q = $this->CI->db->query( $qStr );

    $t = new FUD_groups_opt();
    $r = new ReflectionObject($t);
    $constants = $r->getConstants();

    $permissions = array();
    if( $q->num_rows() == 1 )
    {
      $r = $q->first_row();
      $perms = array_keys( $constants );

      foreach( $perms as $perm )
      {
				if( ( isset($r->users_opt) AND ($r->users_opt & FUD_users_opt::IS_ADMIN) ) 
				  OR ( isset($r->is_mod) AND (null != $r->is_mod) ) 
				  OR ($r->group_cache_opt & $constants[$perm]) )
				{
					$permissions[$perm] = TRUE;
				}
				else
				{
					$permissions[$perm] = FALSE;
				}
      }
      return $permissions;
    }

    return FALSE;
  }

  function check_permission( $rid, $uid = 0, $permission )
  {
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    if( !is_numeric( $permission ) )
    {
      return FALSE;
    }

    $id2 = $uid ? 2147483647 : 0;

    $joins = "";
    if( $uid )
    {
      $joins = "LEFT JOIN {$prefix}mod AS mo 
                  ON mo.user_id='{$uid}' AND mo.forum_id='{$rid}' 
                LEFT JOIN {$prefix}group_cache AS g2
                  ON g2.user_id='{$uid}' AND g2.resource_id='{$rid}'
                LEFT JOIN {$prefix}users AS usr
                  ON usr.id='{$uid}' ";
    }
    
    $selects = " g1.group_cache_opt ";
    if( $uid )
    {
      $selects = " usr.users_opt, mo.id AS is_mod, 
                   COALESCE(g2.group_cache_opt, g1.group_cache_opt) 
                     AS group_cache_opt ";
    }

    $qStr = " SELECT {$selects}
              FROM {$prefix}group_cache AS g1
              {$joins}
              WHERE ( g1.user_id  = '{$id2}' AND g1.resource_id = '{$rid}' ) ";
    $q = $this->CI->db->query( $qStr );

    if( count( $q->result_array() ) == 1 )
    {
      $r = $q->row();
      if( ( isset($r->users_opt) AND ($r->users_opt & FUD_users_opt::IS_ADMIN) ) OR
          ( isset($r->is_mod) AND null != $r->is_mod) OR
          ($r->group_cache_opt & $permission) )
      return TRUE;
    }

    return FALSE;
  }

  function forum_is_visible( $rid, $uid = 0 )
  {
    $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
    $id2 = $uid ? 2147483647 : 0;

    $joins = "";
    if( $uid )
    {
      $joins = "LEFT JOIN {$prefix}mod AS mo
                  ON mo.user_id='{$uid}' AND mo.forum_id='{$rid}'
                LEFT JOIN {$prefix}group_cache AS g2
                  ON g2.user_id='{$uid}' AND g2.resource_id='{$rid}'
                LEFT JOIN {$prefix}users AS usr
                  ON usr.id='{$uid}' ";
    }

    $selects = " g1.group_cache_opt ";
    if( $uid )
    {
      $selects = "usr.users_opt, mo.id AS is_mod, 
                  COALESCE(g2.group_cache_opt, g1.group_cache_opt) 
                  AS group_cache_opt ";
    }

    $qStr = " SELECT {$selects}
              FROM {$prefix}group_cache AS g1
              {$joins}
              WHERE ( g1.user_id='{$id2}' AND g1.resource_id='{$rid}' ) ";
    $q = $this->CI->db->query( $qStr );

    if( $q->num_rows() == 1 )
    {
      $r = $q->first_row();
      if( ( isset($r->users_opt) AND ($r->users_opt & FUD_users_opt::IS_ADMIN) ) OR
          ( isset($r->is_mod) AND null != ($r->is_mod) ) OR
          ( $r->group_cache_opt & FUD_groups_opt::VISIBLE) )
      return TRUE;
    }

    return FALSE;
  }
}

/* End of file fud_library.php */
/* Location: ./application/libraries/fud/fud_library.php */
