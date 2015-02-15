<?php

class FUD_Model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();

    $this->load->helper( 'fud' );
    $this->load->helper( 'br2nl' );
  }
}