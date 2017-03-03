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
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


////////////////////////Role routes////////////////////////
$route['roles']                 = 'RoleController/roles';  //Get -> show roles  & post -> create role

////////////////////////Permission routes////////////////////////
$route['permissions']           = 'PermissionController/permissions'; //Get -> show permissions  & post -> create permission

////////////////////////User routes////////////////////////
//Users
$route['users']                 = 'UserController/users'; //Get -> show users  & post -> create users
$route['users/(:num)']          = 'UserController/getUser/id/$1';
$route['users/update/(:num)']   = 'UserController/updateUser/id/$1';
$route['users/remove/(:num)']   = 'UserController/removeUser/id/$1';
$route['users/roles/(:num)']    = 'UserController/updateRoles/id/$1';

//Recover or change password
$route['recoverPassword']       = 'UserController/recoverPassword';
$route['changePassword']        = 'UserController/changePassword';

////////////////////////Bank routes////////////////////////
$route['banks']                 = 'BankController/banks';  //Get -> show banks  & post -> create bank
$route['banks/update/(:num)']   = 'BankController/updateBank/id/$1';
$route['banks/remove/(:num)']   = 'BankController/removeBank/id/$1';
$route['banks/(:num)']          = 'BankController/getBank/id/$1';

////////////////////////Login routes////////////////////////
$route['login']["post"]         = 'LoginController/login';
