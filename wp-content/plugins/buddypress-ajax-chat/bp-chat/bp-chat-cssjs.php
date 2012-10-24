<?php

/**
 * NOTE: You should always use the wp_enqueue_script() and wp_enqueue_style() functions to include
 * javascript and css files.
 */

/**
 * bp_chat_add_js()
 *
 * This function will enqueue the components javascript file, so that you can make
 * use of any javascript you bundle with your component within your interface screens.
 */
function bp_chat_add_js() {
	global $bp;

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'bp-chat-js2', WP_PLUGIN_URL . '/buddypress-ajax-chat/bp-chat/js/jqDnR.js' );
	wp_enqueue_script( 'bp-chat-js3', WP_PLUGIN_URL . '/buddypress-ajax-chat/bp-chat/js/dimensions.js' );
	wp_enqueue_script( 'bp-chat-js4', WP_PLUGIN_URL . '/buddypress-ajax-chat/bp-chat/js/jqModal.js' );
	wp_enqueue_script( 'bp-chat-nifty', WP_PLUGIN_URL . '/buddypress-ajax-chat/bp-chat/js/nifty.js' );
	wp_enqueue_script( 'bp-chat-js5', WP_PLUGIN_URL . '/buddypress-ajax-chat/bp-chat/js/move_chat.js' );
}
add_action( 'template_redirect', 'bp_chat_add_js', 1 );
add_action( 'admin_menu', 'bp_chat_add_js', 101 );
add_action( 'network_admin_menu', 'bp_chat_add_js', 101 );

/**
 * bp_chat_add_structure_css()
 *
 * This function will enqueue structural CSS so that your component will retain interface
 * structure regardless of the theme currently in use. See the notes in the CSS file for more info.
 */
function bp_chat_add_structure_css() {
	/* Enqueue the structure CSS file to give basic positional formatting for your component reglardless of the theme. */
	wp_enqueue_style( 'bp-chat-shoutbox-css', WP_PLUGIN_URL . "/buddypress-ajax-chat/bp-chat/css/shoutbox.css" );	
	wp_enqueue_style( 'bp-chat-structure', WP_PLUGIN_URL . "/buddypress-ajax-chat/bp-chat/css/structure.css" );	
    wp_enqueue_style( 'bp-chat-nifty-css', WP_PLUGIN_URL . "/buddypress-ajax-chat/bp-chat/css/nifty.css" );	
    $custom_buddypress_ajax_chat_css = get_bloginfo('stylesheet_directory') . "/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css";
    $custom_buddypress_ajax_chat_css_file = STYLESHEETPATH . "/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css";
    if (file_exists($custom_buddypress_ajax_chat_css_file))
        wp_enqueue_style( 'bp-chat-custom-css', $custom_buddypress_ajax_chat_css );	
}
#add_action( 'template_redirect', 'bp_chat_add_structure_css', 1 );
#add_action( 'admin_menu', 'bp_chat_add_structure_css' );
add_action( 'wp_print_styles', 'bp_chat_add_structure_css' );
add_action( 'admin_menu', 'bp_chat_add_structure_css', 101 );
add_action( 'network_admin_menu', 'bp_chat_add_structure_css', 101 );
?>
