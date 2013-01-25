<?php
/*
  Plugin Name: Types - Complete Solution for Custom Fields and Types
  Plugin URI: http://wordpress.org/extend/plugins/types/
  Description: Define custom post types, custom taxonomy and custom fields.
  Author: ICanLocalize
  Author URI: http://wp-types.com
  Version: 1.1.3.4
 */
// Added check because of activation hook and theme embedded code
if (!defined('WPCF_VERSION')) {
    define('WPCF_VERSION', '1.1.3.4');
}

define('WPCF_REPOSITORY', 'http://api.wp-types.com/');

define('WPCF_ABSPATH', dirname(__FILE__));
define('WPCF_RELPATH', plugins_url() . '/' . basename(WPCF_ABSPATH));
define('WPCF_INC_ABSPATH', WPCF_ABSPATH . '/includes');
define('WPCF_INC_RELPATH', WPCF_RELPATH . '/includes');
define('WPCF_RES_ABSPATH', WPCF_ABSPATH . '/resources');
define('WPCF_RES_RELPATH', WPCF_RELPATH . '/resources');
require_once WPCF_INC_ABSPATH . '/constants.php';

if (!defined('EDITOR_ADDON_RELPATH')) {
    define('EDITOR_ADDON_RELPATH',
            WPCF_RELPATH . '/embedded/common/visual-editor');
}


add_action('plugins_loaded', 'wpcf_init');
add_action('after_setup_theme', 'wpcf_init_embedded_code', 999);
register_activation_hook(__FILE__, 'wpcf_upgrade_init');
register_deactivation_hook(__FILE__, 'wpcf_deactivate_init');

add_filter('plugin_action_links', 'wpcf_types_plugin_action_links', 10, 2);

/**
 * Main init hook.
 */
function wpcf_init() {
    if (is_admin()) {
        require_once WPCF_ABSPATH . '/admin.php';
    }
}

/**
 * Include embedded code if not used in theme.
 * 
 * add_action('after_setup_theme', 'wpcf_init_embedded_code', 999);
 * 'after_setup_theme' hook is performed just before 'init' hook.
 */
function wpcf_init_embedded_code() {
    if (!defined('WPCF_RUNNING_EMBEDDED')) {
        require_once WPCF_ABSPATH . '/embedded/types.php';

        // TODO Monitor this
        // Reviewed
        // Not sure why earlier we did not always forced 'init' hook
        // Maybe there was some reason but may not be obstacle anymore
        // Check if it runs OK with embedded code
        //
        // PROPOSAL
        //add_action('init', 'wpcf_embedded_init');
        wpcf_embedded_init();
    } else {// Added because if plugin is active - theme embedded code won't fire
        require_once WPCF_EMBEDDED_ABSPATH . '/types.php';
        //
        // PROPOSAL
        //add_action('init', 'wpcf_embedded_init');
        wpcf_embedded_init();
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
if (($_SERVER['SERVER_NAME'] == '192.168.1.2' || $_SERVER['SERVER_NAME'] == 'localhost') && !function_exists('debug')) {

    function debug($data, $die = true) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die)
            die();
    }

}

function wpcf_types_plugin_activate() {
    add_option('wpcf_types_plugin_do_activation_redirect', true);
}

function wpcf_deactivate_init() {
    delete_option('wpcf_types_plugin_do_activation_redirect', true);
}

function wpcf_types_plugin_redirect() {
    if (get_option('wpcf_types_plugin_do_activation_redirect', false)) {
        delete_option('wpcf_types_plugin_do_activation_redirect');
        wp_redirect(admin_url() . 'admin.php?page=wpcf-help');
        exit;
    }
}

function wpcf_types_plugin_action_links($links, $file) {
    $this_plugin = basename(WPCF_ABSPATH) . '/wpcf.php';
    if ($file == $this_plugin) {
        $links[] = '<a href="admin.php?page=wpcf-help">' . __('Getting started',
                        'wpcf') . '</a>';
    }
    return $links;
}

/**
 * Checks if name is reserved.
 * 
 * @param type $name
 * @return type 
 */
function wpcf_is_reserved_name($name, $check_pages = true) {
    if ($check_pages) {
        global $wpdb;
        $page = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'",
                        sanitize_title($name)));
        if ($page) {
            return true;
        }
    }
    $reserved = wpcf_reserved_names();
    $name = str_replace('-', '_', sanitize_title($name));
    return in_array($name, $reserved);
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

    return apply_filters('wpcf_reserved_names', $reserved);
}

add_action('icl_pro_translation_saved', 'wpcf_fix_translated_post_relationships');

function wpcf_fix_translated_post_relationships($post_id) {
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    wpcf_post_relationship_set_translated_parent($post_id);
    wpcf_post_relationship_set_translated_children($post_id);
}

