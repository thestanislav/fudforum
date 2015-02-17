<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  FUDForum display integration class
 */
class Welcome extends CI_Controller
{
  public function index()
  {
    redirect( site_url('fud/main') );
  }
}