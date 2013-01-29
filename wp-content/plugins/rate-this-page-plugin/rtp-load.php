<?php
/*
Plugin Name: Rate This Page Plugin
Plugin URI: http://www.agentsofvalue.com/?p=6351
Description: Wikipedia Style Rate This Page Plugin - a plugin which allows registered user and visitor to rate an article posts or pages.
Version: 2.1
Author: Agents Of Value

== Copyright ==

Copyright 2011 Agents Of Value  (email : support@agentsofvalue.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require dirname( __FILE__ ) . "/install/feedbacks.php";
require dirname( __FILE__ ) . "/rtp-query.php";
//require dirname( __FILE__ ) . "/rtp-admin.php";
require dirname( __FILE__ ) . "/rtp-main.php";
require dirname( __FILE__ ) . "/rtp-config.php";
require dirname( __FILE__ ) . "/rtp-hooks.php";

require dirname( __FILE__ ) . "/cls-top-rated-posts.php";

// Call to initialize cookie variable.
add_action( 'init', 'aft_init_cookie' );

// Call to activate the plugin.
add_action( 'admin_menu', 'aft_plgn_load' );

// Call to initialize custom jQuery plugins
add_action( 'wp_print_scripts', 'aft_plgn_jquery_init' );

// Call to initialize custom jQuery plugins
add_action( 'wp_print_styles', 'aft_plgn_css_init' );

// Call to initialize jQuery and Stylesheet Scripts for Admin
add_action( 'admin_enqueue_scripts', 'rtp_scripts_admin_init' );

// if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
add_action( 'wp_ajax_nopriv_submit-feedback', 'rtp_process_save' );
add_action( 'wp_ajax_submit-feedback', 'rtp_process_save' );

// Call to instantiate the position of plugin.
add_action( 'the_content', 'rtp_display_to_content' );

// Register plugin function. For options function 
register_activation_hook( __FILE__, 'aft_plgn_options' );

// Unregister plugin function. For options function
// Uncomment if options being added really need to be removed if plugin is deactivated.
//register_deactivation_hook( __FILE__, 'aft_plgn_remove_options' );

// Call the function to create the table.
register_activation_hook( __FILE__, 'create_table' );

global $db_version;

if ( $db_version != get_option( 'aft_db_version' ) ) {
	register_activation_hook( __FILE__, 'update_table' );
}

add_action( 'widgets_init', create_function( '', 'register_widget("RTP_TopRatedWidget");' ) );
?>