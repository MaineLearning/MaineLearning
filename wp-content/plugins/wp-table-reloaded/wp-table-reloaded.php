<?php
/**
 * WordPress plugin "WP-Table Reloaded" main file, responsible for initiating the plugin
 *
 * @package WP-Table Reloaded
 * @author Tobias Bäthge
 * @version 1.9.3
 */

/*
Plugin Name: WP-Table Reloaded
Plugin URI: http://tobias.baethge.com/wordpress-plugins/wp-table-reloaded-english/
Description: This plugin allows you to create and easily manage tables in the admin-area of WordPress. A comfortable backend allows an easy manipulation of table data. You can then include the tables into your posts, on your pages or in text widgets by using a shortcode or a template tag function. Tables can be imported and exported from/to CSV, XML and HTML.
Version: 1.9.3
Author: Tobias Bäthge
Author URI: http://tobias.baethge.com/
Author eMail: wordpress@tobias.baethge.com
Text Domain: wp-table-reloaded
Domain Path: /languages
License: GPL 2
Donate URI: http://tobias.baethge.com/donate/
*/

/*  Copyright 2009-2012 Tobias Bäthge

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

/**
 * Define certain plugin variables as constants
 */
if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) )
    define( 'WP_TABLE_RELOADED_ABSPATH', plugin_dir_path( __FILE__ ) );
if ( !defined( 'WP_TABLE_RELOADED_BASENAME' ) )
    define( 'WP_TABLE_RELOADED_BASENAME', plugin_basename( __FILE__ ) );
if ( !defined( 'WP_TABLE_RELOADED__FILE__' ) )
    define( 'WP_TABLE_RELOADED__FILE__', __FILE__ );
if ( !defined( 'WP_TABLE_RELOADED_PLUGIN_VERSION' ) )
    define( 'WP_TABLE_RELOADED_PLUGIN_VERSION', '1.9.3' );

/**
 * Decide whether admin controller or frontend controller is loaded
 */
if ( is_admin() ) {
    include_once( WP_TABLE_RELOADED_ABSPATH . 'controllers/controller-admin.php' );
    $WP_Table_Reloaded_Admin = new WP_Table_Reloaded_Controller_Admin();
} else {
    include_once ( WP_TABLE_RELOADED_ABSPATH . 'controllers/controller-frontend.php' );
    add_action( 'init', '_wp_table_reloaded_controller_frontend_constructor' );
    
    /**
     * Call constructor on init hook
     */
    function _wp_table_reloaded_controller_frontend_constructor() {
        global $WP_Table_Reloaded_Frontend;
        $WP_Table_Reloaded_Frontend = new WP_Table_Reloaded_Controller_Frontend();
    }

    /**
     * Add template tag function for "table" shortcode to be used anywhere in the template
     *
     * This function provides a possibility to show a table anywhere in a WordPress template,
     * which is needed for any region of a theme that can not use Shortcodes.
     * Thus, the function is only available in the frontend part of WordPress.
     *
     * @see wp_table_reloaded_get_table
     * @param string|array $table_query Query string like list or array of parameters for Shortcode "table" rendering
     */
    function wp_table_reloaded_print_table( $table_query ) {
        echo wp_table_reloaded_get_table( $table_query );
    }

    /**
     * Add function to retrieve the table HTML, needed for template tag function wp_table_reloaded_print_table
     *
     * @uses $WP_Table_Reloaded_Frontend
     * @param string|array $table_query Query string like list or array of parameters for Shortcode "table" rendering
     */
    function wp_table_reloaded_get_table( $table_query ) {
        global $WP_Table_Reloaded_Frontend;
        if ( is_array( $table_query ) )
            $atts = $table_query;
        else
            parse_str( $table_query, $atts );
        return $WP_Table_Reloaded_Frontend->handle_content_shortcode_table( $atts );
    }

    /**
     * Add template tag function for "table-info" shortcode to be used anywhere in the template
     *
     * This function provides a possibility to show table info data anywhere in a WordPress template,
     * which is needed for any region of a theme that can not use Shortcodes.
     * Thus, the function is only available in the frontend part of WordPress.
     *
     * @see wp_table_reloaded_get_table_info
     * @param string|array $table_query Query string like list or array of parameters for Shortcode "table-info" rendering
     */
    function wp_table_reloaded_print_table_info( $table_query ) {
        echo wp_table_reloaded_get_table_info( $table_query );
    }

    /**
     * Add function to retrieve the table-info HTML, needed for template tag function wp_table_reloaded_print_table_info
     *
     * @uses $WP_Table_Reloaded_Frontend
     * @param string|array $table_query Query string like list or array of parameters for Shortcode "table-info" rendering
     */
    function wp_table_reloaded_get_table_info( $table_query ) {
        global $WP_Table_Reloaded_Frontend;
        if ( is_array( $table_query ) )
            $atts = $table_query;
        else
            parse_str( $table_query, $atts );
        return $WP_Table_Reloaded_Frontend->handle_content_shortcode_table_info( $atts );
    }

}

?>