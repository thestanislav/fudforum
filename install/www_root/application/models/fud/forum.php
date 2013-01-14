<?php

class Forum extends CI_Model
{
	private messages;
	
	public function __construct( $fid = null )
    {
    	parent::__construct();
		
		$this->messages = array();
	}
}

?>
