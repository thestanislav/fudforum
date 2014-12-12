<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fud_Library
{
    //private static $instance;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper( 'fud' );
        require_once 'GLOBALS.php';
    	require_once $GLOBALS['DATA_DIR'].'/scripts/fudapi.inc.php';
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

    function add_user($vals, &$err)
    {
    	return fud_add_user($vals, $err);
    }

    function update_user($vals, &$err)
    {
    	return fud_update_user($vals, $err);
    }

    function check_permissions( $rid, $uid = 0 )
    {
		$id2 = $uid ? 2147483647 : 0;

        $joins = "";
        if( $uid )
            $joins = " LEFT JOIN fud30_mod AS mo \n" .
                     "   ON mo.user_id='{$uid}' AND mo.forum_id='{$rid}' \n".
                     " LEFT JOIN fud30_group_cache AS g2 \n".
                     "   ON g2.user_id='{$uid}' AND g2.resource_id='{$rid}' \n".
                     " LEFT JOIN fud30_users AS usr \n ".
                     "   ON usr.id='{$uid}' ";

        $selects = " g1.group_cache_opt ";
        if( $uid )
            $selects = " usr.users_opt, mo.id AS is_mod, COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt ";

        $qStr = " SELECT {$selects} \n".
                " FROM fud30_group_cache AS g1 \n".
                " {$joins} \n" .
                " WHERE ( g1.user_id  = '{$id2}' AND g1.resource_id = '{$rid}' ) \n";
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
					$permissions[$perm] = TRUE;
				else
					$permissions[$perm] = FALSE;
			}
			return $permissions;
		}

        return FALSE;
	}

    function check_permission( $rid, $uid = 0, $permission )
    {
		if( !is_numeric( $permission ) )
			return FALSE;

		$id2 = $uid ? 2147483647 : 0;

        $joins = "";
        if( $uid )
            $joins = " LEFT JOIN fud30_mod AS mo \n" .
                     "   ON mo.user_id='{$uid}' AND mo.forum_id='{$rid}' \n".
                     " LEFT JOIN fud30_group_cache AS g2 \n".
                     "   ON g2.user_id='{$uid}' AND g2.resource_id='{$rid}' \n".
                     " LEFT JOIN fud30_users AS usr \n ".
                     "   ON usr.id='{$uid}' ";

        $selects = " g1.group_cache_opt ";
        if( $uid )
            $selects = " usr.users_opt, mo.id AS is_mod, COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt ";

        $qStr = " SELECT {$selects} \n".
                " FROM fud30_group_cache AS g1 \n".
                " {$joins} \n" .
                " WHERE ( g1.user_id  = '{$id2}' AND g1.resource_id = '{$rid}' ) \n";
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
        $id2 = $uid ? 2147483647 : 0;

        $joins = "";
        if( $uid )
            $joins = " LEFT JOIN fud30_mod AS mo \n" .
                     "   ON mo.user_id='{$uid}' AND mo.forum_id='{$rid}' \n".
                     " LEFT JOIN fud30_group_cache AS g2 \n".
                     "   ON g2.user_id='{$uid}' AND g2.resource_id='{$rid}' \n".
                     " LEFT JOIN fud30_users AS usr \n ".
                     "   ON usr.id='{$uid}' ";

        $selects = " g1.group_cache_opt ";
        if( $uid )
            $selects = " usr.users_opt, mo.id AS is_mod, COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt ";

        $qStr = " SELECT {$selects} \n".
                " FROM fud30_group_cache AS g1 \n".
                " {$joins} \n" .
                " WHERE ( g1.user_id  = '{$id2}' AND g1.resource_id = '{$rid}' ) \n";
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
