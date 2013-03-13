<?php
/*
  Plugin Name: Types - Complete Solution for Custom Fields and Types
  Plugin URI: http://wordpress.org/extend/plugins/types/
  Description: Define custom post types, custom taxonomy and custom fields.
  Author: ICanLocalize
  Author URI: http://wp-types.com
  Version: 1.2
 */
// Added check because of activation hook and theme embedded code
if ( !defined( 'WPCF_VERSION' ) ) {
    define( 'WPCF_VERSION', '1.2' );
}

define( 'WPCF_REPOSITORY', 'http://api.wp-types.com/' );

define( 'WPCF_ABSPATH', dirname( __FILE__ ) );
define( 'WPCF_RELPATH', plugins_url() . '/' . basename( WPCF_ABSPATH ) );
define( 'WPCF_INC_ABSPATH', WPCF_ABSPATH . '/includes' );
define( 'WPCF_INC_RELPATH', WPCF_RELPATH . '/includes' );
define( 'WPCF_RES_ABSPATH', WPCF_ABSPATH . '/resources' );
define( 'WPCF_RES_RELPATH', WPCF_RELPATH . '/resources' );

require_once WPCF_INC_ABSPATH . '/constants.php';

if ( !defined( 'EDITOR_ADDON_RELPATH' ) ) {
    define( 'EDITOR_ADDON_RELPATH',
            WPCF_RELPATH . '/embedded/common/visual-editor' );
}


add_action( 'plugins_loaded', 'wpcf_init' );
// init hook for module manager
add_action( 'init', 'wpcf_wp_init' );
add_action( 'after_setup_theme', 'wpcf_init_embedded_code', 999 );
register_activation_hook( __FILE__, 'wpcf_upgrade_init' );
register_deactivation_hook( __FILE__, 'wpcf_deactivation_hook' );

add_filter( 'plugin_action_links', 'wpcf_types_plugin_action_links', 10, 2 );

/**
 * Deactivation hook.
 * 
 * Reset some of data.
 */
function wpcf_deactivation_hook() {
    // Reset redirection
    delete_option( 'wpcf_types_plugin_do_activation_redirect', true );

    // Delete messages
    delete_option( 'wpcf-messages' );
}

/**
 * Main init hook.
 */
function wpcf_init() {
    if ( is_admin() ) {
        require_once WPCF_ABSPATH . '/admin.php';
    }
}

/**
 * WP Main init hook.
 */
function wpcf_wp_init() {
    if ( is_admin() ) {
        require_once WPCF_ABSPATH . '/admin.php';
    }
}

/**
 * Include embedded code if not used in theme.
 * 
 * We are actually calling this hook on after_setup_theme which is called
 * immediatelly before 'init'. However WP issues warnings because for some
 * action it strictly required 'init' hook to be used.
 * 
 * @todo Revise this!
 */
function wpcf_init_embedded_code() {
    if ( !defined( 'WPCF_EMBEDDED_ABSPATH' ) ) {
        require_once WPCF_ABSPATH . '/embedded/types.php';
    } else {
        require_once WPCF_EMBEDDED_ABSPATH . '/types.php';
    }

    // TODO Better bootstrapping is ready to be added
    // Make this check for now.
    if ( did_action( 'init' ) > 0 ) {
        wpcf_embedded_init();
    } else {
        add_action( 'init', 'wpcf_embedded_init' );
    }
}

/**
 * Upgrade hook.
 */
function wpcf_upgrade_init() {
    require_once WPCF_ABSPATH . '/upgrade.php';
    wpcf_upgrade();
    wpcf_types_plugin_activate();
}

// Local debug
if ( ($_SERVER['SERVER_NAME'] == '192.168.1.2' || $_SERVER['SERVER_NAME'] == 'localhost') && !function_exists( 'debug' ) ) {

    function debug( $data, $die = true ) {
        echo '<pre>';
        print_r( $data );
        echo '</pre>';
        if ( $die )
            die();
    }

}

function wpcf_types_plugin_activate() {
    add_option( 'wpcf_types_plugin_do_activation_redirect', true );
}

function wpcf_types_plugin_redirect() {
    if ( get_option( 'wpcf_types_plugin_do_activation_redirect', false ) ) {
        delete_option( 'wpcf_types_plugin_do_activation_redirect' );
        wp_redirect( admin_url() . 'admin.php?page=wpcf-help' );
        exit;
    }
}

function wpcf_types_plugin_action_links( $links, $file ) {
    $this_plugin = basename( WPCF_ABSPATH ) . '/wpcf.php';
    if ( $file == $this_plugin ) {
        $links[] = '<a href="admin.php?page=wpcf-help">' . __( 'Getting started',
                        'wpcf' ) . '</a>';
    }
    return $links;
}

/**
 * Checks if name is reserved.
 * 
 * @param type $name
 * @return type 
 */
function wpcf_is_reserved_name( $name, $check_pages = true ) {
    $name = strval( $name );
    /*
     * 
     * If name is empty string skip page cause there might be some pages without name
     */
    if ( $check_pages && !empty( $name ) ) {
        global $wpdb;
        $page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'",
                        sanitize_title( $name ) ) );
        if ( !empty( $page ) ) {
            return new WP_Error( 'wpcf_reserved_name', __( 'You cannot use this slug because there is already a page by that name. Please choose a different slug.',
                                    'wpcf' ) );
        }
    }
    $reserved = wpcf_reserved_names();
    $name = str_replace( '-', '_', sanitize_title( $name ) );
    return in_array( $name, $reserved ) ? new WP_Error( 'wpcf_reserved_name', __( 'You cannot use this slug because it is a reserved word, used by WordPress. Please choose a different slug.',
                            'wpcf' ) ) : false;
}

/**
 * Reserved names.
 * 
 * @return type 
 */
function wpcf_reserved_names() {
    $reserved = array(
        'attachment',
        'attachment_id',
        'author',
        'author_name',
        'calendar',
        'cat',
        'category',
        'category__and',
        'category__in',
        'category__not_in',
        'category_name',
        'comments_per_page',
        'comments_popup',
        'cpage',
        'day',
        'debug',
        'error',
        'exact',
        'feed',
        'hour',
        'link_category',
        'm',
        'minute',
        'monthnum',
        'more',
        'name',
        'nav_menu',
        'nopaging',
        'offset',
        'order',
        'orderby',
        'p',
        'page',
        'page_id',
        'paged',
        'pagename',
        'pb',
        'perm',
        'post',
        'post__in',
        'post__not_in',
        'post_format',
        'post_mime_type',
        'post_status',
        'post_tag',
        'post_type',
        'posts',
        'posts_per_archive_page',
        'posts_per_page',
        'preview',
        'robots',
        's',
        'search',
        'second',
        'sentence',
        'showposts',
        'static',
        'subpost',
        'subpost_id',
        'tag',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_id',
        'tag_slug__and',
        'tag_slug__in',
        'taxonomy',
        'tb',
        'term',
        'type',
        'w',
        'withcomments',
        'withoutcomments',
        'year',
    );

    return apply_filters( 'wpcf_reserved_names', $reserved );
}

add_action( 'icl_pro_translation_saved',
        'wpcf_fix_translated_post_relationships' );

function wpcf_fix_translated_post_relationships( $post_id ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    wpcf_post_relationship_set_translated_parent( $post_id );
    wpcf_post_relationship_set_translated_children( $post_id );
}