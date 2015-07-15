<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  FUDforum main controller class
 */
class FudBaseController extends CI_Controller
{
  protected $captcha = NULL;

  public function __construct()
  {
    parent::__construct();
    
    // TODO(nexus): fix once the options have been moved to a new location
    require_once 'GLOBALS.php';
    date_default_timezone_set($GLOBALS['SERVER_TZ']);
    
    $this->load->library('session');
    
    $this->load->library('parser');
    $this->load->library( 'fud/fud_library', NULL, 'FUD' );

    $this->load->model('fud/fud_user','user');

    $this->load->helper( 'fud' );
    $this->load->helper( 'br2nl' );
  }

  /**
  * Returns the site navigation menu
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  protected function _get_site_navigation()
  {
    $loginLogoutUrl = "";
    $loginLogoutText = "";
    $cpOrRegisterUrl = "";
    $cpOrRegisterText = "";
    $administrationUrl = "";    
    $administrationText = "";    

    if( $this->user->isLoggedIn() )
    {
      $address = site_url("logout");
      $loginLogoutUrl = $address;      
      $loginLogoutText = "Logout";
      $address = site_url("controlpanel");
      $cpOrRegisterUrl = $address;
      $cpOrRegisterText = "Control panel";
      
      if( $this->user->isAdmin() )
      {
        // TODO(nexus): fix temporary link to old admin panel
        $address = base_url("/adm/admloginuser.php");
        $administrationUrl = $address;
        $administrationText = "Administrative panel";    
      }
    }
    else
    {
      $address = site_url("login");
      $loginLogoutUrl = $address;
      $loginLogoutText = "Login";
      $address = site_url("register");
      $cpOrRegisterUrl = $address;
      $cpOrRegisterText = "Register";
      $administrationUrl = "";
    }

    $data = array( "login_logout_url" => $loginLogoutUrl,
                   "login_logout_text" => $loginLogoutText,
                   "cp_or_register_url" => $cpOrRegisterUrl,
                   "cp_or_register_text" => $cpOrRegisterText,
                   "administration_url" => $administrationUrl,
                   "administration_text" => $administrationText,
                   "home_url" => site_url() );

    return $this->parser->parse('fud/site_navigation.php', $data, true );
  }

  /**
  * Returns the path navigation menu
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  * @param integer $fid Numerical forum id as in the DB.
  * @param integer $tid Numerical topic id as in the DB.
  */
  protected function _get_path_navigation( $cid = NULL, $fid = NULL, $tid = NULL )
  {
    $home_url = site_url( "fora" );
    $category = NULL; $category_url = NULL;
    $forum = NULL; $forum_url = NULL;
    $topic = NULL; $topic_url = NULL;

    $navigation = "<nav id=\"fud_path_navigation\"><a href=\"{$home_url}\">Home</a>";

    $home_html = "<a href=\"{$home_url}\">Home</a>";
    $category_html = "";
    $forum_html = "";
    $topic_html = "";

    if( $cid != NULL  )
    {
      $category = $this->FUD->fetch_categories( $cid );
      $category_url = site_url( "category/{$cid}" );
      $category_html = " >> <a href=\"{$category_url}\">{$category->name}</a>";

      if( $fid != NULL  )
      {
        $forum = $this->FUD->fetch_forums( $fid );
        $forum_url = site_url( "forum/{$cid}/{$fid}" );
        $forum_html .= " >> <a href=\"{$forum_url}\">{$forum->name}</a>";

        if( $tid != NULL  )
        {
          $topic = $this->FUD->fetch_full_topic( $tid );
          if( !is_array($topic) )
          {
            $topic = array( $topic );
          }
          $topic_url = site_url( "topic/{$cid}/{$fid}/{$tid}" );
          $topic_html .= " >> <a href=\"{$topic_url}\">{$topic[0]->subject}</a>";
        }
      }
    }

    $data = array( "home" => $home_html,
                   "category" => $category_html,
                   "forum" => $forum_html,
                   "topic" => $topic_html );

    $nav = new stdClass();
    $nav->navigation = $this->parser->parse( "fud/path_navigation", $data, true );
    $nav->category = $category;
    $nav->forum = $forum;
    $nav->topic = $topic;

    return $nav;
  }
  
  /**
  * Returns the site header
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  protected function _get_header()
  {
    // TODO(nexus): un-escape title and description
    $data = array( 'title' => html_entity_decode($GLOBALS['FORUM_TITLE']),
                   'description' => html_entity_decode($GLOBALS['FORUM_DESCR']),
                   'base_url' => base_url() );
    $header = $this->parser->parse( "fud/header", $data, TRUE );
    return $header;
  }
}

