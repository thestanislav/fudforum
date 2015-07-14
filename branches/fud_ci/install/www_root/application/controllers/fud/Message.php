<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  FUDforum main controller class
 */
class Message extends MY_FudBaseController
{
  public function __construct()
  {
    parent::__construct();
  }

  /**
  * Displays a single message.
  *
  * NOTE(nexus): Any actual use? Reserve for AJAX?
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $mid Numerical message id to display/retrieve.
  */
  public function get( $mid )
  {
  
  }
  
  /**
  * Function to manage new messages/replies.
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
  public function new( $tid, $mid = NULL, $doQuote = FALSE )
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
  * Shows the message edit form.
  *
  * Shows the message edit form if the user has the right permissions.
  *
  * @author  Massimo Fierro (theonlynexus) <massimo.fierro@gmail.com>
  *
  * @param integer $cid Numerical topic id used to reply.
  * @param integer $tid Numerical topic id used to reply.
  * @param integer $mid Numerical message id used to reply.
  *
  */
  public function edit( $mid, $newBody = NULL )
  {
    $msg = $this->fud->fetch_message( $mid );
    $tid = $msg->thread_id;
    
    $topic = $this->FUD->fetch_full_topic( $tid );
    $forum = $this->FUD->fetch_forums( $message->forum_id );
    
    $subject = trim($message->subject);
    
    if( !$newBody )
    {
      $newBody = $message->body;
      $newBody = br2nl( $newBody );
    } 
    
    $data = array( 'mid' => $mid, 
                   'newBody' => $newBody, 
                   'subject' => $subject,
                   'forum' => $forum->name,                   
                   'reply_to_id' => $message->reply_to,
                   'site_navigation' => $this->_get_site_navigation(),
                   'header' => $this->_get_header(),
                   'base_url' => base_url(),
                   'site_url' => site_url() );
                   
    $data['html_head'] = $this->parser->parse('fud/html_head.php', $data, true);
    $data['html_body'] = $this->parser->parse('fud/message_edit.php', $data, true);
    $this->parser->parse( 'fud/html_page.php', $data );
  }
  
  
  public function delete( $mid )
  {
  
  }
  
  public function delete( $tid, $mid )
  {
  
  }
  
  
  
}