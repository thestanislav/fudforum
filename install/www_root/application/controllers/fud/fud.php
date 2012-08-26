<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *	FUDForum display integration class
 */
class Fud extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library( 'fud/fud_library', null, 'FUD' );

        $this->load->model('fud/user','user');

        $this->load->helper( 'fud' );
        $this->load->helper( 'br2nl' );
    }

    public function index()
    {
		$uid = $this->user->getUid() ? $this->user->getUid() : 0;
        $visibleForums = array();
        $cats = $this->FUD->fetch_categories( null, TRUE );
        foreach( $cats as $cat )
        {
            $catForums[$cat->id] = $this->FUD->fetch_forums_by_category( $cat->id, TRUE );
            for( $frmIdx=0; $frmIdx<count($catForums[$cat->id]); $frmIdx++ )
            {
                if( !is_array($catForums[$cat->id]) )
                    $catForums[$cat->id] = array($catForums[$cat->id]);
                $forum = $catForums[$cat->id][$frmIdx];
                if( is_object( $forum ) )
                {
                    if( $this->FUD->forum_is_visible( $forum->id, $uid ) )
                    {
                        $pid = $forum->last_post_id;
                        $last_msg  = $this->FUD->fetch_message( $pid );
                        $forum->last_post = $last_msg;
                        $visibleForums[$cat->id][] = $forum;
                    }
                }
                else
                {
                    if( $this->FUD->forum_is_visible( $forum, $uid ) )
                            $visibleForums[$cat->id][] = $forum;
                }
            }
        }
        $data = array( 'cats' => $cats, 'catForums' => $visibleForums );
        $this->load->view('fud/index.php', $data);
    }

    public function category( $cid )
    {
		$uid = $this->user->getUid() ? $this->user->getUid() : 0;
		$cat = $this->FUD->fetch_categories( $cid );
		$navigation = "<div id=\"fud_navigation\"><a href=\"/fora\">Home</a>".
		              "<a href=\"/category/{$cid}\">{$cat->name}</a></div>";

        $fora = $this->FUD->fetch_forums_by_category( $cid, TRUE );
        foreach( $fora as $forum )
        {
            echo $forum->name."<br/>";
        }
    }

    public function forum( $cid, $fid, $per_page = 20, $start = 0 )
    {
		$uid = $this->user->getUid() ? $this->user->getUid() : 0;
		$cat = $this->FUD->fetch_categories( $cid );
		$forum = $this->FUD->fetch_forums( $fid );
		$topics = $this->FUD->fetch_topics_by_forum( $fid, true, array( $start, $per_page ) );
        if( !is_array( $topics ) )
			$topics = array( $topics );

		$navigation = "<div id=\"fud_navigation\"><a href=\"/fora\">Home</a> >> ".
		              "<a href=\"/category/{$cid}\">{$cat->name}</a> >> ".
		              "<a href=\"/forum/{$cid}/{$fid}\">{$forum->name}</a></div>";

        foreach( $topics as $topic )
        {
			$topic->last_message = $this->FUD->fetch_message( $topic->last_post_id );
            $topic->root_message = $this->FUD->fetch_message( $topic->root_msg_id );
        }

        $this->load->library('pagination');
        $config['uri_segment'] = 6;
        $config['num_links'] = 10;
        $config['base_url'] = "/forum/{$cid}/{$fid}/{$per_page}/";
        $config['per_page'] = $per_page;
		$config['total_rows'] = $forum->thread_count;
		$config['last_link'] = '>>';
		$config['first_link'] = '<<';
		//~ $config['full_tag_close'] = '<div id="fud_forum_pagination" class="fud_pagination">';
		//~ $config['full_tag_close'] = '</div>';
		$this->pagination->initialize($config);
		$pages  = ceil( $forum->thread_count / $per_page );
		$pagination = $this->pagination->create_links();
		$pagination = empty( $pagination ) ? $pagination : "<div id=\"fud_forum_pagination\" class=\"fud_pagination\">Pages ({$pages}): [{$pagination}]</div>";

        $data = array( 'forum' => $forum, 'topics' => $topics,
		               'pagination' => $pagination, 'fid' => $fid,
		               'navigation' => $navigation, 'cid' => $cid );
        $this->load->view('fud/forum.php', $data);
    }

    public function topic( $cid, $fid, $tid, $per_page = 40, $start = 0 )
    {
		$uid = $this->user->getUid() ? $this->user->getUid() : 0;
		$cat = $this->FUD->fetch_categories( $cid );
		$forum = $this->FUD->fetch_forums( $fid );
		$topic = $this->FUD->fetch_full_topic( $tid );
        if( !is_array($topic) )
            $topic = array($topic);

        $navigation = "<div id=\"fud_navigation\"><a href=\"/fora\">Home</a> >> ".
		              "<a href=\"/category/{$cid}\">{$cat->name}</a> >> ".
		              "<a href=\"/forum/{$cid}/{$fid}\">{$forum->name}</a> >> ".
		              "<a href=\"/topic/{$cid}/{$fid}/{$tid}\">{$topic[0]->subject}</a></div>";

        $permissions = $this->FUD->check_permissions( $fid, $uid );

        $total = count($topic);

        $this->load->library('pagination');
        $config['uri_segment'] = 6;
        $config['num_links'] = 2;
        $config['base_url'] = "/topic/{$cid}/{$tid}/{$per_page}/";
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
        $this->load->view('fud/topic.php', $data);
    }

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

	private function _reply_new( $tid, $mid = null, $do_quote = FALSE )
	{
		$topic = $this->FUD->fetch_full_topic( $tid );
		$reply_to_id = null == $mid ? $topic->root_msg_id : $mid;
		$quote = FALSE == $do_quote ? "" : $this->FUD->fetch_message( $mid )->body;
		$quote = br2nl( $quote );
		$quote = "[quote]{$quote}[/quote]\n&nbsp;";

		$data = array( 'tid' => $tid, 'mid' => $mid, 'do_quote' => $do_quote,
		               'quote' => $quote, 'reply_to_id' => $reply_to_id );
        $this->load->view('fud/reply.php', $data);
	}

	private function _reply_preview( $tid, $mid = null, $do_quote = FALSE )
	{

	}

	private function _reply_post( $tid, $mid = null, $do_quote = FALSE )
	{
	}
}
