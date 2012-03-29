<?php
/*
  Plugin Name: Types - Complete Solution for Custom Fields and Types 
  Plugin URI: http://wordpress.org/extend/plugins/types/
  Description: Define custom post types, custom taxonomy and custom fields.
  Author: ICanLocalize
  Author URI: http://wp-types.com
  Version: 0.9.5.1
 */
// Added check because of activation hook and theme embedded code
if (!defined('WPCF_VERSION')) {
    define('WPCF_VERSION', '0.9.5.1');
}
define('WPCF_ABSPATH', dirname(__FILE__));
define('WPCF_RELPATH', plugins_url() . '/' . basename(WPCF_ABSPATH));
define('WPCF_INC_ABSPATH', WPCF_ABSPATH . '/includes');
define('WPCF_INC_RELPATH', WPCF_RELPATH . '/includes');
define('WPCF_RES_ABSPATH', WPCF_ABSPATH . '/resources');
define('WPCF_RES_RELPATH', WPCF_RELPATH . '/resources');
require_once WPCF_INC_ABSPATH . '/constants.inc';

if (!defined('EDITOR_ADDON_RELPATH')) {
    define('EDITOR_ADDON_RELPATH', WPCF_RELPATH . '/embedded/common/visual-editor');
}


add_action('plugins_loaded', 'wpcf_init');
add_action('after_setup_theme', 'wpcf_init_embedded_code', 999);
register_activation_hook(__FILE__, 'wpcf_upgrade_init');

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
 */
function wpcf_init_embedded_code() {
    if (!defined('WPCF_EMBEDDED_ABSPATH')) {
        require_once WPCF_ABSPATH . '/embedded/types.php';
        wpcf_embedded_init();
    } else {// Added because if plugin is active - theme embedded code won't fire
        require_once WPCF_EMBEDDED_ABSPATH . '/types.php';
        wpcf_embedded_init();
    }
}

/**
 * Upgrade hook.
 */
function wpcf_upgrade_init() {
    require_once WPCF_ABSPATH . '/upgrade.php';
    wpcf_upgrade();
}

// Local debug
if (($_SERVER['SERVER_NAME'] == '192.168.1.2' || $_SERVER['SERVER_NAME'] == 'localhost') && !function_exists('debug')) {

    function debug($data, $die = true) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) die();
    }

}