<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  FUDForum display integration class
 */
class Fud extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    
    // TODO(nexus): fix once the options have been moved to a new location
    require_once 'GLOBALS.php';
    date_default_timezone_set($GLOBALS['SERVER_TZ']);
    
    $this->load->library('parser');
    $this->load->library( 'fud/fud_library', NULL, 'FUD' );

    $this->load->model('fud/fud_user','user');

    $this->load->helper( 'fud' );
    $this->load->helper( 'br2nl' );

  }

  /**
  * Returns the site navigation menu
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  */
  private function _get_site_navigation()
  {
    $loginLogoutLink = "";
    $cpOrRegisterLink = "";
    $administrationLink = "";

    if( $this->user->isLoggedIn() )
    {
      $address = site_url("logout");
      $loginLogoutLink = "<a href=\"{$address}\">Logout</a>";
      $address = site_url("controlpanel");
      $cpOrRegisterLink = "<a href=\"{$address}\">Control Panel</a>";
      
      if( $this->user->isAdmin() )
      {
        // TODO(nexus): fix temporary link to old admin panel
        //$address = site_url("administration");
        $address = base_url("/adm/admloginuser.php");
        $administrationLink = "<a href=\"{$address}\">Administration</a>";
      }
    }
    else
    {
      $address = site_url("login");
      $loginLogoutLink = "<a href=\"{$address}\">Login</a>";
      $address = site_url("register");
      $cpOrRegisterLink = "<a href=\"{$address}\">Register</a>";
      $administrationLink = "";
    }

    $data = array( "login_logout_link" => $loginLogoutLink,
                   "cp_or_register_link" => $cpOrRegisterLink,
                   "administration_link" => $administrationLink,
                   "home_url" => site_url() );

    return $this->parser->parse('fud/site_navigation.php', $data, true );
  }

  /**
  * Returns the path navigation menu
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  * @param integer $fid Numerical forum id as in the DB.
  * @param integer $tid Numerical topic id as in the DB.
  *
  */
  private function _get_path_navigation( $cid = NULL, $fid = NULL, $tid = NULL )
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
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  */
  private function _get_header()
  {
    // TODO(nexus): un-escape title and description
    $data = array( 'title' => $GLOBALS['FORUM_TITLE'],
                   'description' => $GLOBALS['FORUM_DESCR'],
                   'base_url' => base_url() );
    $header = $this->parser->parse( "fud/header", $data, TRUE );
    return $header;
  }

  /**
  * Index page.
  *
  * Index page for fudForum. Shows the default selection of categories
  * and fora.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  */
  public function index()
  {
    $uid = $this->user->getUid() ? $this->user->getUid() : 0;

    $nav = $this->_get_path_navigation();
    $path_navigation = $nav->navigation;

    $cats = $this->FUD->fetch_categories( NULL, TRUE );
    $visibleCats = array();
    foreach( $cats as $cat )
    {
      $visibleFora = array();

      $cat->url = site_url( "category/{$cat->id}" );
      $catFora[$cat->id] = $this->FUD->fetch_forums_by_category( $cat->id, TRUE );
      $foraInCat = count($catFora[$cat->id]);

      for( $frmIdx=0; $frmIdx<$foraInCat; $frmIdx++ )
      {
        if( !is_array($catFora[$cat->id]) )
        {
          $catFora[$cat->id] = array($catFora[$cat->id]);
        }

        $forum = $catFora[$cat->id][$frmIdx];
        if( is_object( $forum ) )
        {
          if( $this->FUD->forum_is_visible( $forum->id, $uid ) )
          {
            $forum->url = site_url( "forum/{$cat->id}/{$forum->id}" );
            $pid = $forum->last_post_id;
            $last_msg  = $this->FUD->fetch_message( $pid );
            $forum->last_post = $last_msg;
            if( $forum->post_count )
            {
              // TODO(nexus): add option for date formatting
              $forum->last_date = date( "j F Y", $forum->last_post->post_stamp );
              $forum->last_author = "by ".$forum->last_post->login;
            }
            else
            {
              $forum->last_date = date( "" );
              $forum->last_author = "";
            }

            $f['f_url'] = $forum->url;
            $f['f_name'] = $forum->name;
            $f['f_description'] = $forum->descr;
            $f['f_post_count'] = $forum->post_count;
            $f['f_thread_count'] = $forum->thread_count;
            $f['f_last_date'] = $forum->last_date;
            $f['f_last_author'] = $forum->last_author;
            $visibleFora[] = $f;
          }
        }
        else
        {
          if( $this->FUD->forum_is_visible( $forum, $uid ) )
            $visibleFora[] = $forum;
        }
      }

      if( count($visibleFora) )
      {
        $c['c_id'] = $cat->id;
        $c['c_name'] = $cat->name;
        $c['c_url'] = $cat->url;
        $c['c_description']  = $cat->description;
        $c['fora']  = $visibleFora;
        $visibleCats[] = $c;
      }
    }

    $data = array( 'categories' => $visibleCats,
                   'path_navigation' => $path_navigation,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url('/') );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/index.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Login page.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  */
  public function login()
  {
    $errorMessage = "";

    // Process login
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
      if( isset( $_POST['login'] ) )
      {
        $username = $_POST['login'];
      }

      if( isset( $_POST['password'] ) )
      {
        $password = $_POST['password'];
      }

      if( isset($username) && isset($password) )
      {
        $result = $this->user->login( $username, $password );
        if( $result['retcode'] == 'LOGIN_SUCCESS' )
        {
          // TODO(nexus): decide where to redirect after login
          redirect('/');
        }
        else
        {
          $errorMessage = $result['message'];
        }
      }
      else
      {
        $errorMessage = "Please input both username and password.";
      }
    }

    if( !empty($errorMessage) )
    {
      //TODO(nexus): Add proper error generating function
      //TODO(nexus): Localization
      $errorMesage = "<div class='error'>{$errorMessage}</div>";
    }

    $data = array( 'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'login_url' => site_url("/login"),
                   'error_message' => $errorMessage,
                   'base_url' => base_url('/') );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/login.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Logout page.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  */
  public function logout()
  {
    //TODO(nexus): decide where to redirect after logout
    $result = $this->user->logout();
    redirect('/');
  }

  /**
  * Shows the fora in a given category.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  *
  */
  public function category( $cid )
  {
    $uid = $this->user->getUid() ? $this->user->getUid() : 0;

    $nav = $this->_get_path_navigation( $cid );
    $path_navigation = $nav->navigation;
    $cat = $nav->category;

    $fora = $this->FUD->fetch_forums_by_category( $cid, TRUE );

    if( !is_array($fora) )
    {
      $fora = array( $fora );
    }

    $count = count($fora);

    for( $frmIdx=0; $frmIdx<$count; $frmIdx++ )
    {
      $forum = $fora[$frmIdx];
      if( is_object( $forum ) )
      {
        if( $this->FUD->forum_is_visible( $forum->id, $uid ) )
        {
          $forum->url = site_url( "forum/{$cat->id}/{$forum->id}" );
          $pid = $forum->last_post_id;
          $last_msg  = $this->FUD->fetch_message( $pid );
          $forum->last_post = $last_msg;
          if( $forum->post_count )
          {
            // TODO(nexus): add option for date formatting
            $forum->last_date = date( "j F Y", $forum->last_post->post_stamp );
            $forum->last_author = "by ".$forum->last_post->login;
          }
          else 
          {
            $forum->last_date = "";
            $forum->last_author = "";
          }

          $f['f_url'] = $forum->url;
          $f['f_name'] = $forum->name;
          $f['f_description'] = $forum->descr;
          $f['f_post_count'] = $forum->post_count;
          $f['f_thread_count'] = $forum->thread_count;
          $f['f_last_date'] = $forum->last_date;
          $f['f_last_author'] = $forum->last_author;
          $visibleFora[] = $f;
        }
      }
      else
      {
        if( $this->FUD->forum_is_visible( $forum, $uid ) )
          $visibleFora[] = $forum;
      }
    }

    $data = array( 'fora' => $visibleFora,
                   'path_navigation'=> $path_navigation,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url('/') );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/category.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Shows the topics in a given forum.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  * @param integer $fid Numerical forum id as in the DB.
  * @param integer $per_page Number of topics to show per page.
  * @param integer $start Which page to start on.
  *
  */
  public function forum( $cid, $fid, $per_page = 20, $start = 0 )
  {
    $uid = $this->user->getUid() ? $this->user->getUid() : 0;

    $nav = $this->_get_path_navigation( $cid, $fid );
    $path_navigation = $nav->navigation;
    $cat = $nav->category;
    $forum = $nav->forum;

    $rows = $this->FUD->fetch_topics_by_forum( $fid, true, array( $start, $per_page ) );
    if( !is_array( $rows ) )
    {
      $rows = array( $rows );
    }

    $topics = array();
    foreach( $rows as $topic )
    {
      $t = array();
      $t['t_id'] = $topic->topic_id;
      $t['t_url'] = site_url( "topic/{$cid}/{$fid}/{$topic->topic_id}" );
      $t['t_description'] = $topic->tdescr;
      $t['t_subject'] = $topic->subject;
      $t['t_replies'] = $topic->replies;
      $t['t_views'] = $topic->views;
      $root_message = $this->FUD->fetch_message( $topic->root_msg_id );
      // TODO(nexus): add option for date formatting
      $t['t_date'] = date( "j F Y", $root_message->post_stamp );
      $t['t_author'] = $root_message->login;
      $last_message = $this->FUD->fetch_message( $topic->last_post_id );
      $t['t_last_author'] = $last_message->login;
      // TODO(nexus): add option for date formatting
      $t['t_last_date'] = date( "j F Y", $last_message->post_stamp );
      $topics[] = $t;
    }

    $this->load->library('pagination');
    $config['uri_segment'] = 5;
    $config['num_links'] = 10;
    $config['base_url'] = site_url( "forum/{$cid}/{$fid}/{$per_page}/" );
    $config['per_page'] = $per_page;
    $config['total_rows'] = $forum->thread_count;
    $config['last_link'] = '>>';
    $config['first_link'] = '<<';
    $this->pagination->initialize($config);

    $pages  = ceil( $forum->thread_count / $per_page );
    $pagination = $this->pagination->create_links();
    $pagination = empty( $pagination ) ? $pagination :
      "<div id=\"fud_forum_pagination\" class=\"fud_pagination\">Pages ({$pages}): [{$pagination}]</div>";

    $data = array( 'topics' => $topics,
                   'pagination' => $pagination,
                   'path_navigation' => $path_navigation,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url('/') );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/forum.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Shows the messages in a given topic.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  * @param integer $fid Numerical forum id as in the DB.
  * @param integer $tid Numerical topic id as in the DB.
  * @param integer $per_page Number of topics to show per page.
  * @param integer $start Which page to start on.
  *
  */
  public function topic( $cid, $fid, $tid, $per_page = 40, $start = 0 )
  {
    $uid = $this->user->getUid() ? $this->user->getUid() : 0;

    $nav = $this->_get_path_navigation( $cid, $fid, $tid );
    $path_navigation = $nav->navigation;
    $cat = $nav->category;
    $forum = $nav->forum;
    $topic = $nav->topic;

    if( !is_array($topic) )
    {
      $topic = array($topic);
    }

    $permissions = $this->FUD->check_permissions( $fid, $uid );

    $total = count($topic);

    $this->load->library('pagination');
    $config['uri_segment'] = 5;
    $config['num_links'] = 2;
    $config['base_url'] = site_url( "topic/{$cid}/{$tid}/{$per_page}/" );
    $config['per_page'] = $per_page;
    $config['total_rows'] = $total;
    $config['last_link'] = '>>';
    $config['first_link'] = '<<';
    $this->pagination->initialize($config);
    $pagination = $this->pagination->create_links();
    $pages  = ceil( $total / $per_page );
    $pagination = empty( $pagination ) ? $pagination :
      "<div id=\"fud_topic_pagination\" class=\"fud_pagination\">Pages ({$pages}): [{$pagination}]</div>";

    if( !$permissions['READ'] )
    {
      // TODO(nexus): Load proper error view
      $messages = "You don't have read permissions to this forum.";
    }

    $topic = array_slice( $topic, $start, $per_page );

    $messages = array();
    foreach( $topic as $message )
    {
      $m = array();
      $m['m_id'] = $message->id;
      $m['m_subject'] = $message->subject;
      // TODO(nexus): add option for date formatting
      $date = date( "D, j F Y H:m", $message->post_stamp );
      $m['m_date'] = $date;
      $avt_height = 60; // TODO(nexus): Get from config
      $height = 64; // 2px padding top and bottom
      $avatar = $message->avatar_loc;
      $avatar = preg_replace("/width=\".*\"/", "width=\"{$avt_height}\"", $avatar );
      $avatar = preg_replace("/height=\".*\"/", "height=\"{$avt_height}\"", $avatar );
      $m['m_avatar'] =  str_replace( "<img", "<img class=\"fud_post_author_avatar\"", $avatar);
      $m['m_login'] =  $message->login;
      $m['m_body'] =  $message->body;
      // TODO(nexus): localization and load proper view
      $reply_url = site_url("reply/{$message->thread_id}/{$message->id}");
      $quote_url = site_url("reply/{$message->thread_id}/{$message->id}/1");
      $m['m_reply_buttons'] = $permissions['REPLY'] ?
        "<span class=\"float_right\"><a href=\"{$reply_url}\">Reply</a></span>"
        ."<span class=\"float_right\"><a href=\"{$quote_url}\">Quote</a></span>" :
        "";
      $messages[] = $m;
    }

    $data = array( 'messages' => $messages,
                   'pagination' => $pagination,
                   'path_navigation' => $path_navigation,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url('/') );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = fix_relative_urls( $this->parser->parse('fud/topic.php', $data, true) );
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Function to manage replies.
  *
  * Displays a new message form, message preview or message edit form
  * as required by the situation.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $tid Numerical topic id used to reply.
  * @param integer $mid OPTIONAL. Numerical message id used to reply.
  * @param boolean $do_quote OPTIONAL. True to quote $mid's message's body.
  */
  public function reply( $tid, $mid = NULL, $do_quote = FALSE )
  {
    if( isset($_POST) AND !empty( $_POST ) )
    {
      if( array_key_exists( 'preview', $_POST ) )
      {
        $this->_reply( $tid, $mid, FALSE, TRUE );
      }
      else if( array_key_exists( 'submit', $_POST ) )
      {
        $this->_reply_post( $tid, $mid );
      }
    }
    else
    {
        $this->_reply( $tid, $mid, $do_quote );
    }
  }

  /**
  * Displays a new reply form.
  *
  * Displays a new reply form. Parameters will determine the topic and
  * possibly message message to, as well as whether to quote or not.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $tid Numerical topic id used to reply.
  * @param integer $mid OPTIONAL. Numerical message id used to reply.
  * @param boolean $do_quote OPTIONAL. True to quote $mid's message's body
  */
  private function _reply( $tid, $mid = NULL, $do_quote = FALSE, $preview = FALSE )
  {
    $topic = $this->FUD->fetch_full_topic( $tid );
    $reply_to_id = $mid == NULL ? $topic->root_msg_id : $mid;
    $message = $this->FUD->fetch_message( $reply_to_id );
    $forum = $this->FUD->fetch_forums( $message->forum_id );
    
    $quote = "";
    if( $do_quote )
    {
      $quote = $message->body;
      $quote = br2nl( $quote );
      $quote = "[quote]{$quote}[/quote]\n&nbsp;";
    } 
    else if( $preview )
    {
      $quote = $_POST['reply_contents'];
    }
    
    $subject = "RE: ".$message->subject;

    $data = array( 'tid' => $tid, 
                   'mid' => $mid, 
                   'quote' => $quote, 
                   'subject' => $subject,
                   'forum' => $forum->name,
                   'reply_to_id' => $reply_to_id,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url(),
                   'site_url' => site_url() );


    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = fix_relative_urls( $this->parser->parse('fud/reply.php', $data, true) );
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Posts a reply to the forum
  *
  * Posts a reply to the forum. Parameters will usually be passed as per the
  * initial _reply_new() call.
  *
  * @author  Massimo Fierro <massimo.fierro@gmail.com>
  *
  * @param integer $tid Numerical topic id used to reply.
  * @param integer $mid OPTIONAL. Numerical message id used to reply.
  * @param boolean $do_quote OPTIONAL. True to quote $mid's message's body
  *
  */
  private function _reply_post( $tid, $mid = NULL )
  {
    $topic = $this->FUD->fetch_full_topic( $tid );
    $subject = $_POST['subject'];
    $pos = strpos( $subject, 'RE: ');
    if( ($pos == FALSE) OR ($pos != 0) )
    {
      $subject = "RE: {$subject}";
    }
    $reply_to_id = $mid == NULL ? $topic->root_msg_id : $mid;
    $this->FUD->new_reply( $subject, $_POST['reply_contents'], 0, 
                           $this->user->getUid(), $reply_to_id );
    
    // TODO(nexus): get the right behaviour from trunk
    redirect( site_url() );
  }
}
