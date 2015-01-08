<?php

class FUD_user extends CI_Model
{
  private $FUDsid = null;
  private $FUDuid = null;
  private $username = null;

  public function __construct()
  {
    parent::__construct();

    $this->load->library( 'fud/fud_library', null, 'FUD' );
    $this->load->helper( 'fud' );
    $this->load->database('fud');

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
    $u = $this->db->escape( $username );
    $p = $this->db->escape( $password );

    if( ($username != null)  )
    {
      //TODO(nexus): understand why the previous call to the library did not work
      //TODO(nexus): fix installation so that it properly generates DNS strings for sqlite DBs (?)
      $qStr = "SELECT id, passwd, salt FROM {$GLOBALS['DBHOST_TBL_PREFIX']}users WHERE login={$u}";
      $q = $this->db->query( $qStr );
      $row = $q->num_rows ? $q->row() : NULL;
      if ( $row && (empty($row->salt) && $row->passwd == md5($password) || $row->passwd == sha1($row->salt . sha1($password))))
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
    //if( $this->isLoggedIn() )
    //{
      $this->FUD->logout( $this->FUDuid );

      $this->username = NULL;
      $this->FUDuid = NULL;
      $this->FUDsid = NULL;

      /*
      $this->session->unset_userdata('loggedin');
      $this->session->unset_userdata('FUDsid');
      $this->session->unset_userdata('FUDuid');
      $this->session->unset_userdata('FUDusername');
      $this->input->set_cookie($GLOBALS['COOKIE_NAME'], '' );
      */
      $this->session->sess_destroy();
    //}
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
      return $this->FUDsid;

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
      return $this->FUDuid;

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
  * Returns TRUE if user is part of the default admin group
  *
  * @author Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @return boolean
  */
  public function isAdmin()
  {
    if( $this->isLoggedIn() )
    {
      $qStr = " SELECT `id` FROM `fud30_users` AS u " .
        " WHERE ( `u`.`users_opt` & 1048576 ) AND `u`.`id` = '{$this->FUDuid}'";
      $q = $this->db->query( $qStr );
      if( $q->num_rows() == 1)
      {
        return TRUE;
      }
    }
    return FALSE;
  }
}

?>
