<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require(__DIR__.'/FudBaseController.php');

/**
 *  FUDforum main controller class
 */
class Main extends FudBaseController
{
  public function __construct()
  {
    parent::__construct();
  }
 

  /**
  * Prepares the necessary data (dates, URL, etc) for the output of a forum
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  private function _prepare_forum_for_output( $cat, $forum ) 
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
    
    // TODO(nexus): appropriately retrieve the icon according to theme
    $last_view = NULL;
    $iconUrl = base_url("theme/default/images/no_new_messages.png");
    if( $this->user->isLoggedIn() )
    {
      $last_view = $this->FUD->get_last_forum_visit( $forum->id, 
                                                     $this->user->getUid() );
      $last_post = $forum->post_count ? $forum->last_post->post_stamp : 0;
      if( $last_post > $last_view  )
      {
        $iconUrl = base_url("theme/default/images/new_messages.png");
      }
    }
    $new_messages_icon = "<img class=\"fud_new_messages_icon\" alt=\"New messages icon\" src=\"{$iconUrl}\" />";
   

    $f['c_id'] = $cat->id;
    $f['f_url'] = $forum->url;
    $f['f_name'] = $forum->name;
    $f['f_description'] = $forum->descr;
    $f['f_post_count'] = $forum->post_count;
    $f['f_thread_count'] = $forum->thread_count;
    $f['f_last_date'] = $forum->last_date;
    $f['f_last_author'] = $forum->last_author;
    $iconUrl = base_url("images/forum_icons/{$forum->forum_icon}");
    $f['f_icon'] = empty($forum->forum_icon) ? "" :
      "<img class=\"fud_forum_icon\" alt=\"Forum icon\" src=\"{$iconUrl}\" />";
    $f['f_new_messages_icon'] = $new_messages_icon;
    $f['f_redirect_url'] = $forum->url_redirect;
    
    return $f;
  }
  
  /**
  * Index page.
  *
  * Index page for FUDforum. Shows the default selection of categories
  * and fora.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
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
            $f = $this->_prepare_forum_for_output( $cat, $forum );
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
  * Callback for username validation upon register.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  public function validate_username() 
  {  
    
  }
  
  /**
  * Callback for captcha validation, must be public.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */  
  public function validate_captcha( $user_input ) 
  {
    // First, delete old captchas
    // TODO(nexus): get captcha expiration from config
    $expiration = time() - 360; // Ten minutes limit
    $this->db->where('captcha_time < ', $expiration)->delete('captcha');

    $ip = $this->input->ip_address();
    $exp = $expiration;
    
    // Then see if a captcha exists:
    $where = array('word' => $user_input, 'ip_address' => $ip, 'captcha_time >' => $exp);
    $query = $this->db->get_where('captcha', $where );

    if( $query == NULL or $query == FALSE)
    {
      $this->db->display_error();
    }
    if( $query->num_rows() == 0 )
    {
      return FALSE;
    }
    
    return TRUE;
  }
  
  /**
  * Private function to handle registration.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  private function _validate_registration_form() 
  {  
    $this->load->library('form_validation');
  
    $this->form_validation->set_rules('username', 'Username', 'trim|required|alpha_dash|is_unique[users.login]');
    $this->form_validation->set_rules('fullname', 'Full name', 'trim|required');
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
    $this->form_validation->set_rules('password', 'Password', 'required');
    $this->form_validation->set_rules('password2', 'Password Confirmation', 'required|matches[password]');
    $this->form_validation->set_rules('captcha', 'Captcha', 'required|alpha_numeric|callback_validate_captcha');
    
    return $this->form_validation->run();
  }
  
  /**
  * Private function to create a CI captcha.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  private function _create_captcha() 
  {
    $this->load->helper('captcha');
    $vals = array(
        //'word'          => 'Random word',
        'img_path'      => './captcha/',
        'img_url'       => base_url('captcha/'),
        'font_path'     => './fonts/AnkaCoder-C75-r.ttf',
        'img_width'     => 200,
        'img_height'    => 50,
        'expiration'    => 7200,
        'word_length'   => 6,
        'font_size'     => 18,
        'img_id'        => 'FUDcaptcha',
        'pool'          => '0123456789abcdefghijkmnpqrstuvxyz',
        // White background and border, black text and red grid
        'colors'        => array(
          'background' => array(255, 255, 255),
          'border' => array(255, 255, 255),
          'text' => array(200, 0, 0),
          'grid' => array(255, 0, 0)
        )
    );
    
    $captcha = create_captcha($vals);    
    $captcha_data = array(
        'captcha_time'  => $captcha['time'],
        'ip_address'    => $this->input->ip_address(),
        'word'          => $captcha['word']
    );

    $q_str = $this->db->insert_string('captcha', $captcha_data);
    $this->db->query($q_str);
    
    return $captcha;
  }
  
  /**
  * Register page.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  public function register()
  {
    $errorMessage = "";

    // Process login
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
      if($this->_validate_registration_form() )
      {
        $values = array( 'login' => $_POST['username'],
                         'email' => $_POST['email'],
                         'name' => $_POST['fullname'],
                         'passwd' => $_POST['password'] );
        if( $this->FUD->add_user($values) == TRUE )
        {
          redirect(site_url(registration_ok));
        }
        else 
        {
          // TODO(nexus): properly display error
          $this->db->display_error( $this->db->error()['message'] );
        }
      }
      else 
      {
        $errorMessage = validation_errors();
      }
    }

    if( !empty($errorMessage) )
    {
      //TODO(nexus): Add proper error generating function
      //TODO(nexus): Localization
      $errorMesage = "<div class='error'>{$errorMessage}</div>";
    }
    
    $captcha = $this->_create_captcha();

    $data = array( 'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'register_url' => site_url("/register"),
                   'captcha_image' => $captcha['image'],
                   'error_message' => $errorMessage,
                   'base_url' => base_url('/') );
    
    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/register.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }
  
  /**
  * Register page.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  */
  public function registration_ok()
  {
    $data = array( 'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url('/'),
                   'login_url' => site_url('login') );
    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/registration_ok.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }
  
  /**
  * Login page.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
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
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
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
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
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
          $f = $this->_prepare_forum_for_output( $cat, $forum );
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
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  * @param integer $fid Numerical forum id as in the DB.
  * @param integer $per_page Number of topics to show per page.
  * @param integer $start Which page to start on.
  */
  public function forum( $cid, $fid, $per_page = 20, $start = 0 )
  {
    $uid = $this->user->getUid() ? $this->user->getUid() : 0;

    $nav = $this->_get_path_navigation( $cid, $fid );
    $path_navigation = $nav->navigation;
    $cat = $nav->category;
    $forum = $nav->forum;
    
    if( $uid != 0 )
    {
      $this->FUD->update_last_forum_visit( $fid, $uid );
    }
    $permissions = $this->FUD->check_permissions( $fid, $uid );
    
    // TODO(nexus): add theming function to wrap data in tags
    $newTopicUrl = "";
    if( $permissions['POST'] )
    {
      $newTopicUrl = site_url("newtopic/{$fid}");
    }

    $rows = $this->FUD->fetch_topics_by_forum( $fid, true, array( $start, $per_page ) );
    if( !is_array( $rows ) )
    {
      $rows = array( $rows );
    }

    $topics = array();
    foreach( $rows as $topic )
    {
      // TODO(nexus): appropriately retrieve the icon according to theme
      $last_view = NULL;
      $iconUrl = base_url("theme/default/images/no_new_messages.png");
      if( $this->user->isLoggedIn() )
      {
        $last_view = $this->FUD->get_last_topic_visit( $topic->id, 
                                                       $this->user->getUid() );
        $last_post = $topic->post_stamp;
        if( $last_post > $last_view  )
        {
          $iconUrl = base_url("theme/default/images/new_messages.png");
        }
      }
      $new_messages_icon = "<img class=\"fud_new_messages_icon\" alt=\"New messages icon\" src=\"{$iconUrl}\" />";
    
      $t = array();
      $t['t_id'] = $topic->topic_id;
      $t['t_url'] = site_url( "topic/{$cid}/{$fid}/{$topic->topic_id}" );
      $t['t_description'] = $topic->tdescr;
      $t['t_subject'] = $topic->subject;
      // TODO(nexus): fix the logic
      // This is quirky, the replies field actually containes the number
      // of messages, no the number of replies
      $t['t_replies'] = $topic->replies-1;
      $t['t_views'] = $topic->views;
      $root_message = $this->FUD->fetch_message( $topic->root_msg_id );
      // TODO(nexus): add option for date formatting
      $t['t_date'] = date( "j F Y", $root_message->post_stamp );
      $t['t_author'] = $root_message->login;
      $last_message = $this->FUD->fetch_message( $topic->last_post_id );
      $t['t_last_author'] = $last_message->login;
      // TODO(nexus): add option for date formatting
      $t['t_last_date'] = date( "j F Y", $last_message->post_stamp );
      // TODO(nexus): retrieve icon from first message
      $iconUrl = base_url("images/message_icons/{$root_message->icon}");
      $t['t_icon'] = empty($root_message->icon) ? "" :
        "<img class=\"fud_forum_icon\" alt=\"Forum icon\" src=\"{$iconUrl}\" />";
      $t['t_new_messages_icon'] = $new_messages_icon;
    
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
                   'new_topic_url' => $newTopicUrl,
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
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical category id as in the DB.
  * @param integer $fid Numerical forum id as in the DB.
  * @param integer $tid Numerical topic id as in the DB.
  * @param integer $per_page Number of topics to show per page.
  * @param integer $start Which page to start on.
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
      // NOTE(nexus): this is a hack to get around the current API/DB
      // TODO(nexus): let the user have a default avatar if not present 
      // NOTE(nexus): select random avatar at registration?
      $src_start = strpos( $message->avatar_loc, "src=\"");
      $m['m_avatar_url'] =  "";
      if( $src_start ){
        $src_start += 5;
        $src_end = strpos( $message->avatar_loc, "\"", $src_start);
        $image_location = substr( $message->avatar_loc, $src_start, $src_end-$src_start );
        $m['m_avatar_url'] =  base_url($image_location);
      }      
      $m['m_login'] =  $message->login;
      $m['m_body'] =  $message->body;
      // TODO(nexus): localization and load proper view
      $reply_url = site_url("message/reply/{$message->thread_id}/{$message->id}");
      $quote_url = site_url("message/reply/{$message->thread_id}/{$message->id}/1");
      $delete_url = site_url("message/delete/{$message->id}");
      $edit_url = site_url("message/edit/{$message->id}");
      
      $m['m_reply_url'] = '';
      $m['m_reply_text'] = '';
      $m['m_quote_url'] = '';
      $m['m_quote_text'] = '';
      if( $permissions['REPLY'] )
      {
        $m['m_reply_url'] = $reply_url;
        $m['m_reply_text'] = 'Reply'; //TODO(nexus): localization
        $m['m_quote_url'] = $quote_url;
        $m['m_quote_text'] = 'Quote'; //TODO(nexus): localization
      }
      //TODO(nexus): Check permisisons!!!
      $m['m_delete_url'] = $delete_url;
      $m['m_delete_text'] = 'Delete'; //TODO(nexus): localization
      $m['m_edit_url'] = $edit_url;
      $m['m_edit_text'] = 'Edit'; //TODO(nexus): localization
      $messages[] = $m;
    }

    $data = array( 'messages' => $messages,
                   'pagination' => $pagination,
                   'can_reply' => $permissions['REPLY'],
                   'path_navigation' => $path_navigation,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url('/') );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/topic.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Function to manage new postings (topics).
  *
  * Displays a new message form, message preview or message edit form
  * as required by the situation.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $fid Numerical forum id in which to create new post.
  */
  public function newtopic( $fid )
  {
    if( isset($_POST) AND !empty( $_POST ) )
    {
      if( array_key_exists( 'preview', $_POST ) )
      {
        $this->_newtopic( $fid, TRUE );
      }
      else if( array_key_exists( 'submit', $_POST ) )
      {
        $this->_newtopic_add( $fid );
      }
    }
    else
    {
        $this->_newtopic( $fid );
    }
  }
  
  /**
  * Displays a new message form with preview (if necessary)
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $fid Numerical forum id in which to create new post.
  * @param boolean $preview TRUE if user has requested a preview
  */  
  private function _newtopic( $fid, $preview = FALSE )
  {
    $forum = $this->FUD->fetch_forums( $fid );
    
    $username = "";
    if( $this->user->isLoggedIn() )
    {
      $username = $this->user->getUsername();
    }
    else
    {
      //TODO(nexus): redirect to session expired page
      redirect( site_url() );
    }
    
    $subject = "";
    if( $preview ) $subject = $_POST['subject'];
    
    $description = "";
    if( $preview ) $description = $_POST['description'];
    
    $quote = "";
    if( $preview ) $quote = $_POST['message_contents'];
    
    $data = array( 'fid' => $fid,
                   'username' => $username,
                   'forum' => $forum->name,
                   'subject' => $subject,
                   'description' => $description,
                   'quote' => $quote, // Used for preview
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url(),
                   'site_url' => site_url() );

    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    //$data['html_body'] = fix_relative_urls( $this->parser->parse('fud/post.php', $data, true) );
    $data['html_body'] = $this->parser->parse('fud/post.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }
  
  /**
  * Adds a new topic to a forum
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $fid Numerical forum id in which to create new post.
  */
  private function _newtopic_add( $fid )
  {
    $forum = $this->FUD->fetch_forums( $fid );
    
    $username = "";
    if( $this->user->isLoggedIn() )
    {
      $username = $this->user->getUsername();
    }
    else
    {
      //TODO(nexus): redirect to session expired page
      redirect( site_url() );
    }
    
    $subject = $_POST['subject'];
    $this->FUD->new_topic( $_POST['subject'], 
                           $_POST['description'],
                           $_POST['message_contents'], 
                           0, // TODO(nexus): get proper flags  from form
                           $this->user->getUid(), 
                           $fid );
    
    // TODO(nexus): get the right behaviour from trunk
    redirect( site_url() );
  }
  
  /**
  * Function to manage replies.
  *
  * Displays a new message form, message preview or message edit form
  * as required by the situation.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
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
        $this->_reply_add( $tid, $mid );
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
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
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
      $quote = $_POST['message_contents'];
    }
    
    // Only add RE: once
    $subject = trim($message->subject);
    $pos = strpos( $subject, 'RE:');
    if( ($pos === FALSE) OR ($pos > 0) )
    {
      $subject = "RE: {$subject}";
    }

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
    //$data['html_body'] = fix_relative_urls( $this->parser->parse('fud/reply.php', $data, true) );
    $data['html_body'] = $this->parser->parse('fud/reply.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }

  /**
  * Adds a reply to the forum
  *
  * Adds a reply to the forum. Parameters will usually be passed as per the
  * initial _reply_new() call.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $tid Numerical topic id used to reply.
  * @param integer $mid OPTIONAL. Numerical message id used to reply.
  * @param boolean $do_quote OPTIONAL. True to quote $mid's message's body
  *
  */
  private function _reply_add( $tid, $mid = NULL )
  {
    $topic = $this->FUD->fetch_full_topic( $tid );
    $subject = $_POST['subject'];
    $reply_to_id = $mid == NULL ? $topic->root_msg_id : $mid;
    $this->FUD->new_reply( $subject, $_POST['message_contents'], 0, 
                           $this->user->getUid(), $reply_to_id );
    
    // TODO(nexus): get the right behaviour from trunk
    redirect( site_url() );
  }
  
  /**
  * VisualCaptcha - Generates a captcha query with the specified number of options.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $num Number of options to generate.
  */
  function vCaptcha( $num )
  {
    $this->load->helper( 'visualcaptcha' );
    $captcha = new \visualCaptcha\Captcha( $this->session );
    $captcha->generate( $howMany );
    // TODO(nexus): Add page loading
  }
  
  /**
  * VisualCaptcha - Streams the captcha image at the given index.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $idx Index.
  */
  function vCaptchaImage( $idx )
  {
    $this->load->helper( 'visualcaptcha' );
    $captcha = new \visualCaptcha\Captcha( $this->session );
    $captcha->streamImage($app->response, $index, FALSE);
    // TODO(nexus): Add page loading
  }
  
  /**
  * VisualCaptcha - Streams the requested captcha audio type.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $type Is it int????? 
  */
  function vCaptchaAudio( $type )
  {
    $this->load->helper( 'visualcaptcha' );
    $captcha = new \visualCaptcha\Captcha( $this->session );
    $captcha->streamAudio( $app->response, $type );
    // TODO(nexus): Add page loading
  }
  
  
}

