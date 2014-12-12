<?php

class FUD_Forum extends FUD_Model
{
	protected $id;
	protected $categoryId;
	protected $name;
	protected $description;
	protected $parent;
	protected $urlRedirect;
	protected $postPassword;
	protected $icon;
	protected $dateCreated;
	protected $topicCount;
	protected $postCount;
	protected $lastPostId;
	protected $viewOrder;
	protected $maxAttachmentSize;
	protected $maxAttachmentNumber;
	protected $moderators;
	protected $messageThreshold;
	protected $forumOptions;
	protected $topics;

	public function __construct( $fid = null )
  {
    parent::__construct();

		$this->topics = $this->FUD->fetch_topics_by_forum( $fid, true,
			array( $start, $per_page ) );
	}
}

?>
