<?php
/*
Plugin Name: Custom CSS Manager
Plugin URI: http://MyWebsiteAdvisor.com
Description: Create, manage and edit custom.css theme file
Version: 1.3
Author: MyWebsiteAdvisor
Author URI: http://MyWebsiteAdvisor.com
*/

register_activation_hook(__FILE__, 'custom_css_manager');

// display error message to users
if ($_GET['action'] == 'error_scrape') {                                                                                                   
    die("Sorry,  Plugin requires PHP 5.0 or higher. Please deactivate Plugin.");                                 
}

function custom_css_manager() {
	if ( version_compare( phpversion(), '5.0', '<' ) ) {
		trigger_error('', E_USER_ERROR);
	}
}

// require  Plugin if PHP 5 installed
if ( version_compare( phpversion(), '5.0', '>=') ) {
	define('CCM_LOADER', __FILE__);

	require_once(dirname(__FILE__) . '/custom-css-manager.php');
	require_once(dirname(__FILE__) . '/plugin-admin.php');

}
?>