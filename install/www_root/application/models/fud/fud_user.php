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
      $this->username = $this->session->userdata('FUDusername');
    }
  }

  /**
  *
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
  * @param string Username
  * @param string Password
  * @return array Return code and human readable message
  */
  public function login( $username = null, $password = null )
  {
    $u = mysql_escape_string( $username );
    $p = mysql_escape_string( $password );
    $this->FUDuid = $this->FUD->get_uid_by_auth( $u, $p );

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
      return array( 'retcode' => 'LOGIN_ERROR_USERNAME_OR_PASSWORD',
      'message' => 'Wrong username or password' );
    }
  }

  /**
  * Performs user logout
  */
  public function logout()
  {
    if( $this->isLoggedIn() )
    {
      $this->FUD->logout( $this->FUDuid );

      $this->username = null;

      $this->session->unset_userdata('loggedin');
      $this->session->unset_userdata('FUDsid');
      $this->session->unset_userdata('FUDuid');
      $this->session->unset_userdata('FUDusername');
      $this->input->set_cookie($GLOBALS['COOKIE_NAME'], '' );
    }
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
