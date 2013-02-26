<?php
/*
 * Menu functions.
 */

add_action('wpcf_menu_plus', 'wpcf_access_admin_menu_hook', 11);

/**
 * Menu hook. 
 */
function wpcf_access_admin_menu_hook() {
    $hook = add_submenu_page('wpcf', __('Access Control and User Roles', 'wpcf'), __('Access Control and User Roles', 'wpcf'),
            'manage_options', 'wpcf-access', 'wpcf_access_admin_menu_page');
    wpcf_admin_plugin_help($hook, 'wpcf-access');
    add_action('load-' . $hook, 'wpcf_access_admin_menu_load');
}

/**
 * Menu page load hook. 
 */
function wpcf_access_admin_menu_load() {
    wp_enqueue_style('wpcf-access-wpcf',
            WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION);
    wp_enqueue_style('wpcf-access', WPCF_ACCESS_RELPATH . '/css/basic.css',
            array(), WPCF_VERSION);
    wp_enqueue_script('wpcf-access', WPCF_ACCESS_RELPATH . '/js/basic.js',
            array('jquery'));
    wp_enqueue_script('types-suggest', WPCF_ACCESS_RELPATH . '/js/suggest.js',
            array(), WPCF_ACCESS_VERSION);
    wp_enqueue_style('types-suggest', WPCF_ACCESS_RELPATH . '/css/suggest.css',
            array(), WPCF_ACCESS_VERSION);
    add_thickbox();
}

/**
 * Menu page render hook. 
 */
function wpcf_access_admin_menu_page() {
    echo wpcf_add_admin_header(__('Access', 'wpcf'), 'icon-wpcf-access');
    require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
    wpcf_access_admin_edit_access();
    echo wpcf_add_admin_footer();
}
