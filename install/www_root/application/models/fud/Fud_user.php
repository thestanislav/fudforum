<?php

class FUD_user extends CI_model
{
  private $FUDsid = null;
  private $FUDuid = null;
  private $username = null;

  public function __construct()
  {
    parent::__construct();

    $this->load->library( 'fud/fud_library', null, 'FUD' );
    $this->load->helper( 'fud' );
    $this->load->database( 'fud' );

    if( $this->isLoggedIn() )
    {
      $this->FUDsid = $this->session->userdata('FUDsid');
      $this->FUDuid = $this->session->userdata('FUDuid');
      //NOTE(nexus): is it really necessary to store the username in a cookie?
      $this->username = $this->session->userdata('FUDusername');
    }
  }

  /**
  * Returns a copy of the user's data
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  */
  public function getData( $key = '' )
  {
    if( $this->isLoggedIn() )
    {
      if( $key != '' && $key != null )
        return $this->data[$key];
      else
        return $this->data;
    }
    else
      return FALSE;
  }

  /**
  * Performs user login
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param string Username
  * @param string Password
  * @return array Return code and human readable message
  */
  public function login( $username = null, $password = null )
  {
    if( ($username != null)  )
    {
      //TODO(nexus): understand why the previous call to the library did not work
      //TODO(nexus): fix installation so that it properly generates DNS strings for sqlite DBs (?)
      $this->db->where('login',$username);
      $this->db->from('users');
      $q = $this->db->get();
      $row = $q->num_rows() ? $q->row() : NULL;
      if( $row && (empty($row->salt) && $row->passwd == md5($password) || 
          $row->passwd == sha1($row->salt . sha1($password))) )
      {
        $this->FUDuid = $row->id;
      }
      else return;
    }

    if( null != $this->FUDuid )
    {
      $this->FUDsid = $this->FUD->login( $this->FUDuid );

      $this->username = $username;

      $this->session->set_userdata('loggedin',TRUE);
      $this->session->set_userdata('FUDusername',$username);
      $this->session->set_userdata('FUDuid',$this->FUDuid);
      $this->session->set_userdata('FUDsid',$this->FUDsid);
      return array( 'retcode' => 'LOGIN_SUCCESS', 'message' => '' );
    }
    else
    {
      //TODO(nexus): Localize error messages
      return array( 'retcode' => 'LOGIN_ERROR_USERNAME_OR_PASSWORD',
      'message' => 'Wrong username or password' );
    }
  }

  /**
  * Performs user logout
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  */
  public function logout()
  {
    $this->FUD->logout( $this->FUDuid );

    $this->username = NULL;
    $this->FUDuid = NULL;
    $this->FUDsid = NULL;

    $this->session->sess_destroy();
  }

  /**
  *
  */
  public function isLoggedIn()
  {
    return $this->session->userdata('loggedin');
  }

  /**
  * Returns the session id if the user is logged in, FALSE otherwise
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @return mixed
  */
  public function getSid()
  {
    if( $this->isLoggedIn() )
    {
      return $this->FUDsid;
    }

    return FALSE;
  }

  /**
  * Returns the user id if the user is logged in, FALSE otherwise
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @return mixed
  */
  public function getUid()
  {
    if( $this->isLoggedIn() )
    {
      return $this->FUDuid;
    }

    return FALSE;
  }

  /**
  * Returns the username if the user is logged in, FALSE otherwise
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @return mixed
  */
  public function getUsername()
  {
    if( $this->isLoggedIn() )
      return $this->username;

    return FALSE;
  }

  /**
  * Returns TRUE if user is the admin user or part of the default admin group
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @return boolean
  */
  public function isAdmin()
  {
    if( $this->isLoggedIn() )
    {
      $qStr = " SELECT `id` FROM `fud30_users` AS u 
                WHERE ( `u`.`users_opt` & 1048576 ) 
                  AND `u`.`id` = '{$this->FUDuid}'";
      $q = $this->db->query( $qStr );
      if( $q->num_rows() == 1)
      {
        return TRUE;
      }
    }
    
    return FALSE;
  }
  
  /**
  * Returns TRUE if user is moderator for any forum or a given one
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @return boolean
  */
  public function isModerator( $fid = NULL )
  {
    if( $fid == NULL ) 
    {
      if( $this->isLoggedIn() )
      {
        $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
        $qStr = "SELECT `id` FROM `{$prefix}_users` AS u 
                WHERE ( `u`.`users_opt` & 524288 ) 
                  AND `u`.`id` = '{$this->FUDuid}'";
        $q = $this->db->query( $qStr );
        if( $q->num_rows() == 1)
        {
          return TRUE;
        }
      }
      return FALSE;
    }
    else 
    {
      if( $this->isModerator() )
      {
        $prefix = $GLOBALS['DBHOST_TBL_PREFIX'];
        $qStr = "SELECT `moderators`
                FROM `{$prefix}_forum` 
                WHERE `id`='{$fid}'";
        $q = $this->db->query( $qStr );
        if( $q->num_rows() == 1)
        {
          $u = $this->username;
          $l = $u;
          if( strstr( $q['moderators'], "s:{$l}:\"{$u}\"" ) )
          {
            return TRUE;
          }
        }
      }
      return FALSE;
    }
  }
  
  
}

?>
