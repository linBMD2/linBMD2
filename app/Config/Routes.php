<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // HBW - reccommended to be false by Codeigniter 4 user manual

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('home/signout', 'Home::signout');
$routes->get('home', 'Home::index');
$routes->get('home/close', 'Home::close');
$routes->get('home/help', 'Home::help');

$routes->get('identity/signin_step1/(:segment)', 'Identity::signin_step1/$1');
$routes->post('identity/signin_step2', 'Identity::signin_step2');
$routes->get('identity/create_identity_step1/(:segment)', 'Identity::create_identity_step1/$1');
$routes->post('identity/create_identity_step2', 'Identity::create_identity_step2');
$routes->get('identity/change_password_step1/(:segment)', 'Identity::change_password_step1/$1');
$routes->post('identity/change_password_step2', 'Identity::change_password_step2');
$routes->get('identity/retrieve_password_step1/(:segment)', 'Identity::retrieve_password_step1/$1');
$routes->post('identity/retrieve_password_step2', 'Identity::retrieve_password_step2');

$routes->get('transcribe', 'Transcribe::index');
$routes->get('transcribe/transcribe_step1/(:segment)', 'Transcribe::transcribe_step1/$1');
$routes->post('transcribe/next_action', 'Transcribe::transcribe_next_action');
$routes->get('transcribe/create_BMD_file/(:segment)', 'Transcribe::create_BMD_file/$1');
$routes->get('transcribe/upload_BMD_file/(:segment)', 'Transcribe::upload_BMD_file/$1');
$routes->get('transcribe/submit_details/(:segment)', 'Transcribe::submit_details/$1');
$routes->get('transcribe/close_header_step1/(:segment)', 'Transcribe::close_header_step1/$1');
$routes->post('transcribe/close_header_step2', 'Transcribe::close_header_step2');
$routes->get('transcribe/verify_BMD_file_step1/(:segment)', 'Transcribe::verify_BMD_file_step1/$1');
$routes->get('transcribe/verify_BMD_trans_step1/(:segment)', 'Transcribe::verify_BMD_trans_step1/$1');
$routes->get('transcribe/search_synonyms', 'Transcribe::search_synonyms');
$routes->get('transcribe/search_districts', 'Transcribe::search_districts');
$routes->get('transcribe/search_volumes', 'Transcribe::search_volumes');
$routes->get('transcribe/search_firstnames', 'Transcribe::search_firstnames');
$routes->get('transcribe/search_surnames', 'Transcribe::search_surnames');
$routes->get('transcribe/update_firstnames/(:segment)', 'Transcribe::update_firstnames/$1');
$routes->get('transcribe/update_surnames/(:segment)', 'Transcribe::update_surnames/$1');
$routes->get('transcribe/image_parameters_step1/(:segment)', 'Transcribe::image_parameters_step1/$1');
$routes->post('transcribe/image_parameters_step2/(:segment)', 'Transcribe::image_parameters_step2/$1');
$routes->get('transcribe/delete_line_step1/(:segment)', 'Transcribe::delete_line_step1/$1');
$routes->post('transcribe/delete_line_step2', 'Transcribe::delete_line_step2');
$routes->get('transcribe/send_email/(:segment)', 'Transcribe::send_email/$1');

$routes->get('header/create_BMD_step1/(:segment)', 'Header::create_BMD_step1/$1');
$routes->post('header/create_bmd_step2', 'Header::create_BMD_step2');
$routes->get('header/reopen_BMD_step1/(:segment)', 'Header::reopen_BMD_step1/$1');
$routes->post('header/reopen_BMD_step2', 'Header::reopen_BMD_step2');

$routes->get('allocation/create_allocation_step1/(:segment)', 'Allocation::create_allocation_step1/$1');
$routes->post('allocation/create_allocation_step2', 'Allocation::create_allocation_step2');
$routes->get('allocation/manage_allocations/(:segment)', 'Allocation::manage_allocations/$1');
$routes->post('allocation/next_action', 'Allocation::next_action');

$routes->get('births/transcribe_births_step1/(:segment)', 'Births::transcribe_births_step1/$1');
$routes->post('births/transcribe_births_step2', 'Births::transcribe_births_step2');
$routes->get('births/select_line/(:segment)', 'Births::select_line/$1');
$routes->get('births/delete_line_step1/(:segment)', 'Births::delete_line_step1/$1');
$routes->post('births/delete_line_step2', 'Births::delete_line_step2');
$routes->get('births/comment_step1/(:segment)', 'Births::comment_step1/$1');
$routes->post('births/comment_step2', 'Births::comment_step2');
$routes->get('births/select_comment/(:segment)', 'Births::select_comment/$1');
$routes->get('births/remove_comments/(:segment)/(:segment)', 'Births::remove_comments/$1/$2');

$routes->get('marriages/transcribe_marriages_step1/(:segment)', 'Marriages::transcribe_marriages_step1/$1');
$routes->post('marriages/transcribe_marriages_step2', 'Marriages::transcribe_marriages_step2');
$routes->get('marriages/select_line/(:segment)', 'Marriages::select_line/$1');
$routes->get('marriages/delete_line_step1/(:segment)', 'Marriages::delete_line_step1/$1');
$routes->post('marriages/delete_line_step2', 'Marriages::delete_line_step2');
$routes->get('marriages/comment_step1/(:segment)', 'Marriages::comment_step1/$1');
$routes->post('marriages/comment_step2', 'Marriages::comment_step2');
$routes->get('marriages/select_comment/(:segment)', 'Marriages::select_comment/$1');
$routes->get('marriages/remove_comments/(:segment)/(:segment)', 'Marriages::remove_comments/$1/$2');

$routes->get('deaths/transcribe_deaths_step1/(:segment)', 'Deaths::transcribe_deaths_step1/$1');
$routes->post('deaths/transcribe_deaths_step2', 'Deaths::transcribe_deaths_step2');
$routes->post('deaths/transcribe_deaths_step2', 'Deaths::transcribe_deaths_step2');
$routes->get('deaths/select_line/(:segment)', 'Deaths::select_line/$1');
$routes->get('deaths/comment_step1/(:segment)', 'Deaths::comment_step1/$1');
$routes->post('deaths/comment_step2', 'Deaths::comment_step2');
$routes->get('deaths/select_comment/(:segment)', 'Deaths::select_comment/$1');
$routes->get('deaths/remove_comments/(:segment)/(:segment)', 'Deaths::remove_comments/$1/$2');

$routes->get('housekeeping/index/(:segment)', 'Housekeeping::index/$1');
$routes->get('housekeeping/districts_staleness', 'Housekeeping::districts_staleness');
$routes->get('housekeeping/districts_refresh', 'Housekeeping::districts_refresh');
$routes->get('housekeeping/database_backup', 'Housekeeping::database_backup');
$routes->get('housekeeping/firstnames', 'Housekeeping::firstnames');
$routes->get('housekeeping/surnames', 'Housekeeping::surnames');
$routes->get('housekeeping/phpinfo', 'Housekeeping::phpinfo');

$routes->get('syndicate/refresh_syndicates', 'Syndicate::refresh_syndicates');

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
