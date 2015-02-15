<?php

class FUD_Message extends FUD_Model
{
	protected $id;
	protected $threadId;
	protected $posterId;
	protected $replyTo;
	protected $ipAddress;
	protected $hostname;
	protected $postStamp;
	protected $updateStamp;
	protected $updatedBy;
	protected $icon;
	protected $subject;
	protected $attachmentCount;
	protected $pollId;
	protected $fileOffset;
	protected $length;
	protected $fileId;
	protected $offsetPreview;
	protected $lengthPreview;
	protected $fileIdPreview;
	protected $attachmentCache;
	protected $pollCache;
	protected $mailingListMessageId;
	protected $messageOptions;
	protected $apr; // ???
	protected $flagCC; // ???
	protected $flagCountry;
	
	protected dbToObjectMap = array( 'id' => 'id', 'thread_id' => 'threadId',
		'poster_id' => 'posterId', 'reply_to' => 'replyTo', 
		'ip_addr' => 'ipAddress', 'host_name' => 'hostname', 
		'post_stamp' => 'postStamp', 'update_stamp' => 'updateStamp',
		'updated_by' => 'updatedBy', 'icon' => 'icon', 'subject' => 'subject',
		'attach_cnt' => 'attachmentCount', 'poll_id' => 'pollId',
		'foff' => 'fileOffset', 'length' => 'length', 'file_id' => 'fileId',
		'offset_preview' => 'offsetPreview', 'length_preview' => 'lengthPreview',
		'file_id_preview' => 'fileIdPreview', 'attach_cache' => 'attachmentCache',
		'poll_cache' => 'pollCache', 'mlist_msg_id' => 'mailingListMessageId',
		'msg_opt' => 'messageOptions', 'apr' => 'apr', 'flag_cc' => 'flagCC',
		'flag_country' => 'flagCountry' );
	

	public function __construct( $mid = null )
    {
    	parent::__construct();
	}
}

