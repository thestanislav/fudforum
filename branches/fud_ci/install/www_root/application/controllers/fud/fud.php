<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *	FUDForum display integration class
 */
class Fud extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('parser');
        $this->load->library( 'fud/fud_library', null, 'FUD' );

        $this->load->model('fud/fud_user','user');

        $this->load->helper( 'fud' );
        $this->load->helper( 'br2nl' );
    }

	private function _get_navigation( $cid = null, $fid = null, $tid = null )
	{
		$home_url = site_url( "fora" );
		$cat = null; $cat_url = null;
		$forum = null; $forum_url = null;
		$topic = null; $topic_url = null;

		$navigation = "<div id=\"fud_navigation\"><a href=\"{$home_url}\">Home</a>";

		if( null != $cid )
		{
			$cat = $this->FUD->fetch_categories( $cid );
			$cat_url = site_url( "category/{$cid}" );
			$navigation .= " >> <a href=\"{$cat_url}\">{$cat->name}</a>";

			if( null != $fid )
			{
				$forum = $this->FUD->fetch_forums( $fid );
				$forum_url = site_url( "forum/{$cid}/{$fid}" );
				$navigation .= " >> <a href=\"{$forum_url}\">{$forum->name}</a>";

				if( null != $tid )
				{
					$topic = $this->FUD->fetch_full_topic( $tid );
					if( !is_array($topic) )
						$topic = array( $topic );
					$topic_url = site_url( "topic/{$cid}/{$fid}/{$tid}" );
					$navigation .= " >> <a href=\"{$topic_url}\">{$topic[0]->subject}</a>";
				}
			}
		}
        $navigation .= "</div>";

		$result = new StdClass;
		$result->navigation = $navigation;
		$result->category = $cat;
		$result->forum = $forum;
		$result->topic = $topic;
		return $result;
	}

	/**
	* Index page.
	*
	* Index page for fudForum. Shows the default selection of categories
	* and forums.
	*
	* @author  Massimo Fierro <massimo.fierro@gmail.com>
	*
	*/
  public function index()
  {
  	$uid = $this->user->getUid() ? $this->user->getUid() : 0;

  	$nav = $this->_get_navigation();
  	$navigation = $nav->navigation;

  	$cats = $this->FUD->fetch_categories( null, TRUE );
    $visibleCats = array();
    foreach( $cats as $cat )
    {
      $visibleForums = array();

      $cat->url = site_url( "category/{$cat->id}" );
      $catForums[$cat->id] = $this->FUD->fetch_forums_by_category( $cat->id, TRUE );
      $forumsInCat = count($catForums[$cat->id]);

      for( $frmIdx=0; $frmIdx<$forumsInCat; $frmIdx++ )
      {
        if( !is_array($catForums[$cat->id]) )
            $catForums[$cat->id] = array($catForums[$cat->id]);

        $forum = $catForums[$cat->id][$frmIdx];
        if( is_object( $forum ) )
        {
          if( $this->FUD->forum_is_visible( $forum->id, $uid ) )
          {
            $forum->url = site_url( "forum/{$cat->id}/{$forum->id}" );
            $pid = $forum->last_post_id;
            $last_msg  = $this->FUD->fetch_message( $pid );
            $forum->last_post = $last_msg;
            if( $forum->post_count ) {
              // TODO: format date properly
              $forum->last_date = date( "D, j F Y", $forum->last_post->post_stamp );
              $forum->last_author = "by ".$forum->last_post->login;
            }

            $f['f_url'] = $forum->url;
            $f['f_name'] = $forum->name;
            $f['f_description'] = $forum->descr;
            $f['f_post_count'] = $forum->post_count;
            $f['f_thread_count'] = $forum->thread_count;
            $f['f_last_date'] = $forum->last_date;
            $f['f_last_author'] = $forum->last_author;
            $visibleForums[] = $f;
          }
        }
        else
        {
          if( $this->FUD->forum_is_visible( $forum, $uid ) )
            $visibleForums[] = $forum;
        }
      }

      if( count($visibleForums) ){
        $c['c_name'] = $cat->name;
        $c['c_url'] = $cat->url;
        $c['c_description']  = $cat->description;
        $c['forums']  = $visibleForums;
        $visibleCats[] = $c;
        //$cat->forums = $visibleForums;
      	//$visibleCats[] = $cat;
      }
    }
    $data = array( 'categories' => $visibleCats, 'navigation' => $navigation );

    $html_head = $this->load->view('fud/html_head.php', null, true);
  	$html_body = $this->parser->parse('fud/index.php', $data, true);
  	$html_parts = array( 'html_body' => $html_body, 'html_head' => $html_head);

  	$this->load->view( 'fud/html_page.php', $html_parts );
  }

	/**
	* Shows the forums in a category.
	*
	* Shows the forums in a category.
	*
	* @author  Massimo Fierro <massimo.fierro@gmail.com>
	*
	* @param integer $cid Numerical category id as in the DB.
	*
	*/
  public function category( $cid )
  {
    $uid = $this->user->getUid() ? $this->user->getUid() : 0;

    $nav = $this->_get_navigation( $cid );
    $navigation = $nav->navigation;
    $cat = $nav->category;

    $forums = $this->FUD->fetch_forums_by_category( $cid, TRUE );

    if( !is_array($forums) )
    	$forums = array( $forums );

    $count = count($forums);

    for( $frmIdx=0; $frmIdx<$count; $frmIdx++ )
    {
      $forum = $forums[$frmIdx];
      if( is_object( $forum ) )
      {
        if( $this->FUD->forum_is_visible( $forum->id, $uid ) )
        {
          $forum->url = site_url( "forum/{$cat->id}/{$forum->id}" );
          $pid = $forum->last_post_id;
          $last_msg  = $this->FUD->fetch_message( $pid );
          $forum->last_post = $last_msg;
          if( $forum->post_count ) {
            // TODO: format date properly
            $forum->last_date = date( "D, j F Y", $forum->last_post->post_stamp );
            $forum->last_author = "by ".$forum->last_post->login;
          }

          $f['f_url'] = $forum->url;
          $f['f_name'] = $forum->name;
          $f['f_description'] = $forum->descr;
          $f['f_post_count'] = $forum->post_count;
          $f['f_thread_count'] = $forum->thread_count;
          $f['f_last_date'] = $forum->last_date;
          $f['f_last_author'] = $forum->last_author;
          $visibleForums[] = $f;
        }
      }
      else
      {
        if( $this->FUD->forum_is_visible( $forum, $uid ) )
          $visibleForums[] = $forum;
      }
    }


    $data = array( 'forums' => $visibleForums,
                   'navigation'=> $navigation );

    $html_head = $this->load->view('fud/html_head.php', null, true);
    $html_body = $this->parser->parse('fud/category.php', $data, true);
    $html_parts = array( 'html_body' => $html_body, 'html_head' => $html_head);
    $this->load->view( 'fud/html_page.php', $html_parts );
  }

    /**
	* Shows the topics in a forum.
	*
	* Shows the topics in a forum.
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

		$nav = $this->_get_navigation( $cid, $fid );
		$navigation = $nav->navigation;
		$cat = $nav->category;
		$forum = $nav->forum;

		$data = $this->FUD->fetch_topics_by_forum( $fid, true, array( $start, $per_page ) );
        if( !is_array( $data ) )
		$data = array( $data );

    $topics = array();
		foreach( $data as $topic )
    {
      //die("<pre>".print_r($topic,true)."</pre>");
      $t = array();
      $t['t_id'] = $topic->topic_id;
      $t['t_url'] = site_url( "topic/{$cid}/{$fid}/{$topic->topic_id}" );
      $t['t_description'] = $topic->tdescr;
      $t['t_subject'] = $topic->subject;
      $t['t_replies'] = $topic->replies;
      $t['t_views'] = $topic->views;
      $root_message = $this->FUD->fetch_message( $topic->root_msg_id );
      $t['t_date'] = date( "D, j F Y", $root_message->post_stamp );
      $t['t_author'] = $root_message->login;
      $last_message = $this->FUD->fetch_message( $topic->last_post_id );
      $t['t_last_author'] = $last_message->login;
      $t['t_last_date'] = date( "D, j F Y", $last_message->post_stamp );
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
		$pagination = empty( $pagination ) ? $pagination : "<div id=\"fud_forum_pagination\" class=\"fud_pagination\">Pages ({$pages}): [{$pagination}]</div>";

    $data = array( 'topics' => $topics,
	                 'pagination' => $pagination,
		               'navigation' => $navigation );

		$html_head = $this->load->view('fud/html_head.php', null, true);
		$html_body = $this->parser->parse('fud/forum.php', $data, true);
		$html_parts = array( 'html_body' => $html_body, 'html_head' => $html_head);
		$this->load->view( 'fud/html_page.php', $html_parts );
    }

  /**
	* Shows the messages in a topic.
	*
	* Shows the messages in a topic.
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

		$nav = $this->_get_navigation( $cid, $fid, $tid );
		$navigation = $nav->navigation;
		$cat = $nav->category;
		$forum = $nav->forum;
		$topic = $nav->topic;

		if( !is_array($topic) )
            $topic = array($topic);

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
		$pagination = empty( $pagination ) ? $pagination : "<div id=\"fud_topic_pagination\" class=\"fud_pagination\">Pages ({$pages}): [{$pagination}]</div>";
		//die( print_r( $pagination, true ) );

        $topic = array_slice( $topic, $start, $per_page );
        $data = array( 'topic' => $topic, 'pagination' => $pagination,
                       'cid' => $cid, 'navigation' => $navigation,
                       'permissions' => $permissions, 'fid' => $fid );

		$html_head = $this->load->view('fud/html_head.php', null, true);
		$html_body = $this->load->view('fud/topic.php', $data, true);
		$html_body = fix_relative_urls( $html_body );
		$html_parts = array( 'html_body' => $html_body, 'html_head' => $html_head);
		$this->load->view( 'fud/html_page.php', $html_parts );
    }

	/**
	* XXXXXXXXXXXXXXXXXXXXXXXXXX
	*
	* XXXXXXXXXXXXXXXXXXXXXXXXXX
	*
	* @author  Massimo Fierro <massimo.fierro@gmail.com>
	*
	* @param integer $tid Numerical topic id used to reply.
	* @param integer $mid OPTIONAL. Numerical message id used to reply.
	* @param boolean $do_quote OPTIONAL. True to quote $mid's message's body*
	*
	*/
    public function reply( $tid, $mid = null, $do_quote = FALSE )
    {
		if( !strcasecmp( $_SERVER['method'], 'post' ) )
		{
			if( array_key_exists( 'preview', $_POST ) )
				$this->_reply_preview( $tid, $mid = null, $do_quote = FALSE, $preview = TRUE );
			else if( array_key_exists( 'submit', $_POST ) )
				$this->_reply_post( $tid, $mid = null, $do_quote = FALSE );
		}
		else
		{
			$this->_reply_new( $tid, $mid = null, $do_quote = FALSE );
		}
	}

	/**
	* XXXXXXXXXXXXXXXXXXXXXXXXXX
	*
	* XXXXXXXXXXXXXXXXXXXXXXXXXX
	*
	* @author  Massimo Fierro <massimo.fierro@gmail.com>
	*
	* @param integer $tid Numerical topic id used to reply.
	* @param integer $mid OPTIONAL. Numerical message id used to reply.
	* @param boolean $do_quote OPTIONAL. True to quote $mid's message's body
	*
	*/
    private function _reply_new( $tid, $mid = null, $do_quote = FALSE )
	{
		$topic = $this->FUD->fetch_full_topic( $tid );
		$reply_to_id = null == $mid ? $topic->root_msg_id : $mid;
		$quote = FALSE == $do_quote ? "" : $this->FUD->fetch_message( $mid )->body;
		$quote = br2nl( $quote );
		$quote = "[quote]{$quote}[/quote]\n&nbsp;";

		$data = array( 'tid' => $tid, 'mid' => $mid, 'do_quote' => $do_quote,
		               'quote' => $quote, 'reply_to_id' => $reply_to_id );

		$html_head = $this->load->view('fud/html_head.php', null, true);
		$html_body = $this->load->view('fud/reply.php', $data, true);
		$html_parts = array( 'html_body' => $html_body, 'html_head' => $html_head);
		$this->load->view( 'fud/html_page.php', $html_parts );
	}

	/**
	* XXXXXXXXXXXXXXXXXXXXXXXXXX
	*
	* XXXXXXXXXXXXXXXXXXXXXXXXXX
	*
	* @author  Massimo Fierro <massimo.fierro@gmail.com>
	*
	* @param integer $tid Numerical topic id used to reply.
	* @param integer $mid OPTIONAL. Numerical message id used to reply.
	* @param boolean $do_quote OPTIONAL. True to quote $mid's message's body
	*
	*/
    private function _reply_preview( $tid, $mid = null, $do_quote = FALSE )
	{

	}

	private function _reply_post( $tid, $mid = null, $do_quote = FALSE )
	{

	}
}
