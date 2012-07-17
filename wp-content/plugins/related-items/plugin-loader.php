<?php
/*
Plugin Name: Related Items
Plugin URI: http://MyWebsiteAdvisor.com/
Description: Related Items plugin lets you relate a page, post or custom post type to other pages, posts and custom post types.
Version: 1.1
Author: MyWebsiteAdvisor
Author URI: http://MyWebsiteAdvisor.com/


Copyright 2010-2011  MyWebsiteAdvisor.com  (email: MyWebsiteAdvisor@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook(__FILE__, 'related_items_activate');

// display error message to users
if ($_GET['action'] == 'error_scrape') {                                                                                                   
    die("Sorry,  Plugin requires PHP 5.0 or higher. Please deactivate Plugin.");                                 
}

function related_items_activate() {
	if ( version_compare( phpversion(), '5.0', '<' ) ) {
		trigger_error('', E_USER_ERROR);
	}
}

// require  Plugin if PHP 5 installed
if ( version_compare( phpversion(), '5.0', '>=') ) {
	define('RI_LOADER', __FILE__);

	require_once(dirname(__FILE__) . '/related-items.php');
	require_once(dirname(__FILE__) . '/plugin-admin.php');

        // Start the plugin
	global $related_items;
	$related_items = new Related_Items_Admin();
}
?>