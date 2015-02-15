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
  
  function add_user($vals, &$err)
  {
    return fud_add_user($vals, $err);
  }

  function update_user($vals, &$err)
  {
    return fud_update_user($vals, $err);
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
	if( ( isset($r->users_opt) AND ($r->users_opt & FUD_users_opt::IS_ADMIN) ) OR
	  ( isset($r->is_mod) AND null != $r->is_mod) OR
	  ($r->group_cache_opt & $constants[$perm]) )
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
