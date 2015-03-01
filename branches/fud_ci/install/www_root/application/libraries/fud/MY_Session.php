<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Session extends CI_Session 
{
  private $namespace = "vcaptcha";

  public function clear() 
  {
    $_SESSION[ $this->namespace ] = Array();
  }

  public function get( $key ) 
  {
    if ( !isset( $_SESSION[ $this->namespace ] ) ) 
    {
      $this->clear();
    }
    if ( isset( $_SESSION[ $this->namespace ][ $key ] ) ) 
    {
      return $_SESSION[ $this->namespace ][ $key ];
    }
    return null;
  }

  public function set( $key, $value ) 
  {
    if ( !isset( $_SESSION[ $this->namespace ] ) ) 
    {
      $this->clear();
    }
    $_SESSION[ $this->namespace ][ $key ] = $value;
  }
}