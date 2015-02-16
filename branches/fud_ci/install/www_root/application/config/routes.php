<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "fud/fud";
$route['404_override'] = '';

/* FUD routes */
$route['login'] = "fud/fud/login";
$route['logout'] = "fud/fud/logout";
$route['fora'] = "fud/fud/index";
$route['category/(:num)'] = "fud/fud/category/$1";
$route['forum/(:num)/(:num)'] = "fud/fud/forum/$1/$2";
$route['forum/(:num)/(:num)/(:num)'] = "fud/fud/forum/$1/$2/$3";
$route['forum/(:num)/(:num)/(:num)/(:num)'] = "fud/fud/forum/$1/$2/$3/$4";
$route['topic/(:num)/(:num)/(:num)'] = "fud/fud/topic/$1/$2/$3";
$route['topic/(:num)/(:num)/(:num)/(:num)'] = "fud/fud/topic/$1/$2/$3/$4";
$route['topic/(:num)/(:num)/(:num)/(:num)/(:num)'] = "fud/fud/topic/$1/$2/$3/$4/$5";
$route['reply/(:num)'] = "fud/fud/reply/$1";
$route['reply/(:num)/(:num)'] = "fud/fud/reply/$1/$2";
$route['reply/(:num)/(:num)/(:num)'] = "fud/fud/reply/$1/$2/$3";
$route['newtopic/(:num)'] = "fud/fud/newtopic/$1";

/* End of file routes.php */
/* Location: ./application/config/routes.php */
