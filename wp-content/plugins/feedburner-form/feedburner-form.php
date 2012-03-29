<?php
/*
Plugin Name: FeedBurner Form
Text Domain: fbf
Domain Path: /lang
Version: 1.3
Plugin URI: http://dianakcury.com/dev/feedburner-form
Description: Add Google Feedburner&reg; email subscription forms with optional subscribers counter. Access Plugins &rarr; <a href="plugins.php?page=fbtools">FeedBurner Form</a>.
Author: Diana K. Cury
Author URI: http://arquivo.tk/
*/

    function fb_setup(){
    load_plugin_textdomain('fbf', null, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
    }
    add_action( 'init', 'fb_setup' );


		if ( !defined('WP_CONTENT_URL') )
		    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
		if ( !defined('WP_CONTENT_DIR') )
		    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

		if (!defined('PLUGIN_URL'))
		    define('PLUGIN_URL', WP_CONTENT_URL . '/plugins');
		if (!defined('PLUGIN_PATH'))
		    define('PLUGIN_PATH', WP_CONTENT_DIR . '/plugins');

		define('FB_FILE_PATH', dirname(__FILE__));
		define('FB_DIR_NAME', basename(FB_FILE_PATH));


    // Hook for adding admin menus
    add_action('admin_menu', 'fb_add_pages');

    // action function for above hook
    function fb_add_pages() {
        add_plugins_page( 'FeedBurner Form', 'FeedBurner Form', 'manage_options', 'fbtools', 'fb_tools_page'); }


    // fb_tools_page() displays the page content for the Test Tools submenu
    function fb_tools_page() { include('control/info.php');  }

    //Widgets
    require PLUGIN_PATH .'/'.FB_DIR_NAME . '/control/widgets.php';

    //admin style
    add_action('admin_head', 'fb_plugin_header');
    function fb_plugin_header() {
  	global $post_type, $page;
  	?>
   <style>

   span.fb-admin{background:#fff;font-family:courier;padding:2px; font-weight: bold }
   .fb-admin ul li{list-style:disc;list-style-position:inside;margin-left:20px;}

   .special {font-weight:bold;background:#fff;padding:4px ;border:1px dashed #ccc;}
   </style>
   <?php }

    // outupt style
    function fbstyle($result) {
     wp_enqueue_style('fb_data_style', PLUGIN_URL ."/".FB_DIR_NAME."/control/fbstyle.css");
    }
    add_filter('get_header','fbstyle');
?>