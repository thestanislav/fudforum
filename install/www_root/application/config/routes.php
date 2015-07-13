<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| --------------------------------------------  -----------------------------
|
| There are three reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|   $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|       my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = "welcome";
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


/* FUD routes */
$route['login'] = "fud/main/login";
$route['logout'] = "fud/main/logout";
$route['register'] = "fud/main/register";
$route['registration_ok'] = "fud/main/registration_ok";
$route['fora'] = "fud/main/index";
$route['category/(:num)'] = "fud/main/category/$1";
$route['forum/(:num)/(:num)'] = "fud/main/forum/$1/$2";
$route['forum/(:num)/(:num)/(:num)'] = "fud/main/forum/$1/$2/$3";
$route['forum/(:num)/(:num)/(:num)/(:num)'] = "fud/main/forum/$1/$2/$3/$4";
$route['topic/(:num)/(:num)/(:num)'] = "fud/main/topic/$1/$2/$3";
$route['topic/(:num)/(:num)/(:num)/(:num)'] = "fud/main/topic/$1/$2/$3/$4";
$route['topic/(:num)/(:num)/(:num)/(:num)/(:num)'] = "fud/main/topic/$1/$2/$3/$4/$5";

$route['message/get/(:num)'] = "fud/message/get/$1";
$route['message/new/(:num)'] = "fud/message/new/$1";
$route['message/new/(:num)/(:num)'] = "fud/messaeg/new/$1/$2";
$route['message/new/(:num)/(:num)/(:num)'] = "fud/message/new/$1/$2/$3";\
$route['message/edit/(:num)'] = "fud/message/edit/$1";
$route['message/delete/(:num)'] = "fud/message/delete/$1/";



$route['reply/(:num)'] = "fud/main/reply/$1";
$route['reply/(:num)/(:num)'] = "fud/main/reply/$1/$2";
$route['reply/(:num)/(:num)/(:num)'] = "fud/main/reply/$1/$2/$3";
$route['newtopic/(:num)'] = "fud/main/newtopic/$1";
$route['vcaptcha/(:num)'] = "fud/captcha/$1";
$route['vcaptcha/image/(:num)'] = "fud/captchaimage/$1";
$route['vcaptcha/audio/(:num)'] = "fud/captchaaudio/$1";

/* End of file routes.php */
/* Location: ./application/config/routes.php */
