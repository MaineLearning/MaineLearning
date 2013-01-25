<?php
/*
 * Types Access teaser.
 */

add_action('plugins_loaded', 'wpcf_access_teaser_init', 15);

/**
 * Teaser init. 
 */
function wpcf_access_teaser_init() {
    if (!defined('WPCF_ACCESS_VERSION')) {
        define('WPCF_ACCESS_ABSPATH', dirname(__FILE__) . '/types-access');
        define('WPCF_ACCESS_RELPATH',
                plugins_url() . '/' . basename(WPCF_ABSPATH) . '/plus/types-access');
        define('WPCF_ACCESS_INC', WPCF_ACCESS_ABSPATH . '/includes');
        add_action('wpcf_menu_plus', 'wpcf_access_teaser_admin_menu');
        $locale = get_locale();
        load_textdomain('wpcf_access',
                WPCF_ACCESS_ABSPATH . '/locale/types-access-' . $locale . '.mo');
    }
}

/**
 * Teaser menu hook. 
 */
function wpcf_access_teaser_admin_menu() {
    $hook = add_submenu_page('wpcf', __('Access Control and User Roles', 'wpcf'),
            __('Access Control and User Roles', 'wpcf'), 'manage_options', 'wpcf-access',
            'wpcf_access_teaser_admin_menu_page');
    add_action('load-' . $hook, 'wpcf_access_teaser_admin_menu_load');
}

/**
 * Teaser menu load. 
 */
function wpcf_access_teaser_admin_menu_load() {
    require_once WPCF_ACCESS_ABSPATH . '/embedded.php';
    wp_enqueue_style('wpcf-access-wpcf', WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css',
            array(), WPCF_VERSION);
    wp_enqueue_style('wpcf-access', WPCF_ACCESS_RELPATH . '/css/basic.css',
            array(), WPCF_VERSION);
    wp_enqueue_style('wpcf-access-suggest', WPCF_ACCESS_RELPATH . '/css/suggest.css',
            array(), WPCF_VERSION);
    wp_enqueue_script('wpcf-access', WPCF_ACCESS_RELPATH . '/js/basic.js',
            array('jquery'));
}

/**
 * Teaser admin screen. 
 */
function wpcf_access_teaser_admin_menu_page() {
    echo wpcf_add_admin_header(__('Access', 'wpcf'), 'icon-wpcf-access');
    echo '<p>' . __('To enjoy access management for your site, you need to have the Access plugin.',
            'wpcf')
    . '<br /><br /><a href="http://wp-types.com/home/types-access/?utm_source=typesplugin&utm_medium=accessadmin&utm_term=Meet&utm_campaign=typesplugin" class="button-primary" target="_blank">'
    . __('Meet Access') . '</a>&nbsp;<a href="http://wp-types.com/buy/?utm_source=typesplugin&utm_medium=accessadmin&utm_term=Pricing&utm_campaign=typesplugin" class="button-primary" target="_blank">'
    . __('Pricing') . '</a>' . '<br /><br /></p>';
    require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
    wpcf_access_admin_edit_access(false);
    echo wpcf_add_admin_footer();
}
