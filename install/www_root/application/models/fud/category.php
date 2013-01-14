<?php

class Category extends CI_Model
{
	private forums;
	
	public function __construct( $cid )
    {
	   	parent::__construct();
	    
		$this->load->helper( 'fud' );
        $this->load->helper( 'br2nl' );
		
		$this->id = $cid;
		
		$this->forums = array();
		
		$cat = $this->FUD->fetch_categories( $cid );		
		foreach( array_keys( $cat ) as $key )
		{
			$this->$$key = $cat[$key];
		}
		
		$this->_fetchForums();
		
	}
	
	private function _fetchForums()
	{
		$tmp = $this->FUD->fetch_forums_by_category( $this->id, TRUE );
		
		for( $frmIdx=0; $frmIdx < count($tmp[$cat->id]); $frmIdx++ )
		{
			if( !is_array($tmp[$cat->id]) )
				$tmp[$cat->id] = array($tmp[$cat->id]);
				
			$forum = $tmp[$cat->id][$frmIdx];
			if( is_object( $forum ) )
			{
				if( $this->FUD->forum_is_visible( $forum->id, $uid ) )
				{
					$pid = $forum->last_post_id;
					$last_msg  = $this->FUD->fetch_message( $pid );
					$forum->last_post = $last_msg;
					$this->forums[$cat->id][] = $forum;
				}
			}
			else
			{
				if( $this->FUD->forum_is_visible( $forum, $uid ) )
						$this->forums[$cat->id][] = $forum;
			}
		}
	}
}

?>
