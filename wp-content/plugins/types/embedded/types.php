<?php
if (!defined('WPCF_VERSION')) {
    define('WPCF_RUNNING_EMBEDDED', true);
    add_action('init', 'wpcf_embedded_init');
}

define('WPCF_EMBEDDED_ABSPATH', dirname(__FILE__));
define('WPCF_EMBEDDED_INC_ABSPATH', WPCF_EMBEDDED_ABSPATH . '/includes');
define('WPCF_EMBEDDED_RES_ABSPATH', WPCF_EMBEDDED_ABSPATH . '/resources');

if (!defined('ICL_COMMON_FUNCTIONS')) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/functions.php';
}

wpcf_embedded_after_setup_theme_hook();
/**
 * after_setup_theme hook.
 */
function wpcf_embedded_after_setup_theme_hook() {
    $custom_types = get_option('wpcf-custom-types', array());
    if (!empty($custom_types)) {
        foreach ($custom_types as $post_type => $data) {
            if (!empty($data['supports']['thumbnail'])) {
                if (!current_theme_supports('post-thumbnails')) {
                    add_theme_support('post-thumbnails');
                    remove_post_type_support('post', 'thumbnail');
                    remove_post_type_support('page', 'thumbnail');
                } else {
                    add_post_type_support($post_type, 'thumbnail');
                }
            }
        }
    }
}

/**
 * Main init hook.
 */
function wpcf_embedded_init() {

    load_plugin_textdomain('wpcf', false, WPCF_EMBEDDED_ABSPATH . '/locale');
    if (!defined('WPV_VERSION')) {
        load_plugin_textdomain('wpv-views', false,
                WPCF_EMBEDDED_ABSPATH . '/locale/locale-views');
    }

    // Define necessary constants if plugin is not present
    if (!defined('WPCF_VERSION')) {
        define('WPCF_VERSION', '0.9.4.2');
        define('WPCF_META_PREFIX', 'wpcf-');
        define('WPCF_EMBEDDED_RELPATH', icl_get_file_relpath(__FILE__));
    } else {
        define('WPCF_EMBEDDED_RELPATH', WPCF_RELPATH . '/embedded');
    }

    define('WPCF_EMBEDDED_INC_RELPATH', WPCF_EMBEDDED_RELPATH . '/includes');
    define('WPCF_EMBEDDED_RES_RELPATH', WPCF_EMBEDDED_RELPATH . '/resources');

    if (is_admin()) {
        require_once WPCF_EMBEDDED_ABSPATH . '/admin.php';
        wpcf_embedded_admin_init_hook();
    } else {
        require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
    }
    wpcf_init_custom_types_taxonomies();
    if (defined('DOING_AJAX')) {
        require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
    }
    wpcf_embedded_check_import();
}

/**
 * Inits custom types and taxonomies.
 */
function wpcf_init_custom_types_taxonomies() {
    $custom_types = get_option('wpcf-custom-types', array());
    if (!empty($custom_types)) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
        wpcf_custom_types_init();
    }
    $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
    if (!empty($custom_taxonomies)) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';
        wpcf_custom_taxonomies_init();
    }
}

/**
 * WPML translate call.
 * 
 * @param type $name
 * @param type $string
 * @return type 
 */
function wpcf_translate($name, $string) {
    if (!function_exists('icl_t')) {
        return $string;
    }
    return icl_t('plugin Types', $name, $string);
}

/**
 * Returns meta_key type for specific field type.
 * 
 * @param type $type
 * @return type 
 */
function types_get_field_type($type) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $data = wpcf_fields_type_action($type);
    if (!empty($data['meta_key_type'])) {
        return $data['meta_key_type'];
    }
    return 'CHAR';
}

/**
 * Imports settings.
 */
function wpcf_embedded_check_import() {
    if (file_exists(WPCF_EMBEDDED_ABSPATH . '/settings.php')) {
        require_once WPCF_EMBEDDED_ABSPATH . '/admin.php';
        require_once WPCF_EMBEDDED_ABSPATH . '/settings.php';
        $dismissed = get_option('wpcf_dismissed_messages', array());
        if (in_array($timestamp, $dismissed)) {
            return false;
        }
        if ($timestamp > get_option('wpcf-types-embedded-import', 0)) {
            if (!$auto_import) {
                wp_enqueue_script('wpcf-fields-edit',
                        WPCF_EMBEDDED_RES_RELPATH . '/js/basic.js',
                        array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable'),
                        WPCF_VERSION);
                $link = "<a href=\"" . admin_url('?types-embedded-import=1&amp;_wpnonce=' . wp_create_nonce('embedded-import')) . "\">";
                $text = sprintf(__('You have Types import pending. %sClick here to import.%s %sDismiss message.%s',
                                'wpcf'), $link, '</a>',
                        "<a onclick=\"jQuery(this).parent().parent().fadeOut();\" class=\"wpcf-ajax-link\" href=\""
                        . admin_url('admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=dismiss_message&amp;id='
                                . $timestamp . '&amp;_wpnonce=' . wp_create_nonce('dismiss_message')) . "\">",
                        '</a>');
                wpcf_admin_message($text);
            }
            if ($auto_import || (isset($_GET['types-embedded-import']) && isset($_GET['_wpnonce'])
                    && wp_verify_nonce($_GET['_wpnonce'], 'embedded-import'))) {
                if (file_exists(WPCF_EMBEDDED_ABSPATH . '/settings.xml')) {
                    $_POST['overwrite-groups'] = 1;
                    $_POST['overwrite-fields'] = 1;
                    $_POST['overwrite-types'] = 1;
                    $_POST['overwrite-tax'] = 1;
                    $_POST['delete-groups'] = 1;
                    $_POST['delete-fields'] = 1;
                    $_POST['delete-types'] = 1;
                    $_POST['delete-tax'] = 1;
                    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
                    require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
                    $data = @file_get_contents(WPCF_EMBEDDED_ABSPATH . '/settings.xml');
                    wpcf_admin_import_data($data, false);
                    update_option('wpcf-types-embedded-import', $timestamp);
                    wp_redirect(admin_url());
                } else {
                    $code = __('settings.xml file missing', 'wpcf');
                    wpcf_admin_message($code, 'error');
                }
            }
        }
    }
}

/**
 * Display information about upgrading to the plugin version of types.
 *
 */
function wpcf_promote_types_admin() {
    $custom_types = get_option('wpcf-custom-types', array());

    ?>

    <?php
    if (sizeof($custom_types) > 0) {
        echo '<p>' . __('Types creates Custom Post Types. These are user-defined WordPress content types. On  your theme the following Types are defined:',
                'wpcf') . "</p>\n";
        echo "<ul style='margin-left:20px;'>\n";
        foreach ($custom_types as $type) {
            echo "<li>" . $type['labels']['name'] . "</li>\n";
        }
        echo "</ul>\n";
    }

    ?>
    <p><?php
    echo sprintf(__('If you want to edit these or create your own you can download the full version of <strong>Types</strong> from <a href="%s">%s</a>',
                    'wpcf'), 'http://wordpress.org/extend/plugins/types/',
            'http://wordpress.org/extend/plugins/types/');

    ?></p>

    <?php
}

/**
 * Actions for outside fields control.
 * 
 * @param type $action 
 */
function wpcf_types_cf_under_control($action = 'add', $args = array()) {
    global $wpcf_types_under_control;
    $wpcf_types_under_control['errors'] = array();
    switch ($action) {
        case 'add':
            $fields = wpcf_admin_fields_get_fields();
            foreach ($args['fields'] as $field_id) {
                $field_type = !empty($args['type']) ? $args['type'] : 'textfield';
                if (strpos($field_id, md5('wpcf_not_controlled')) !== false) {
                    $field_id_add = str_replace('_' . md5('wpcf_not_controlled'),
                            '', $field_id);
                    // Activating field that previously existed in Types
                    if (array_key_exists($field_id_add, $fields)) {
                        $fields[$field_id_add]['data']['disabled'] = 0;
                    } else { // Adding from outside
                        $fields[$field_id_add]['id'] = $field_id_add;
                        $fields[$field_id_add]['type'] = $field_type;
                        $fields[$field_id_add]['name'] = $field_id_add;
                        $fields[$field_id_add]['slug'] = $field_id_add;
                        $fields[$field_id_add]['description'] = '';
                        $fields[$field_id_add]['data'] = array();
                        // @TODO WATCH THIS! MUST NOT BE DROPPED IN ANY CASE
                        $fields[$field_id_add]['data']['controlled'] = 1;
                    }
                    $unset_key = array_search($field_id, $args['fields']);
                    if ($unset_key !== false) {
                        unset($args['fields'][$unset_key]);
                        $args['fields'][$unset_key] = $field_id_add;
                    }
                }
            }
            wpcf_admin_fields_save_fields($fields);
            return $args['fields'];
            break;

        case 'check_exists':
            $fields = wpcf_admin_fields_get_fields();
            $field = $args;
            if (array_key_exists($field, $fields) && empty($fields[$field]['data']['disabled'])) {
                return true;
            }
            return false;
            break;

        case 'check_outsider':
            $fields = wpcf_admin_fields_get_fields();
            $field = $args;
            if (array_key_exists($field, $fields) && !empty($fields[$field]['data']['controlled'])) {
                return true;
            }
            return false;
            break;

        default:
            break;
    }
}

/**
 * Controlls meta prefix.
 * 
 * @param array $field
 */
function wpcf_types_get_meta_prefix($field = array()) {
    if (empty($field)) {
        return WPCF_META_PREFIX;
    }
    if (!empty($field['data']['controlled'])) {
        return '';
    }
    return WPCF_META_PREFIX;
}

/**
 * Compares WP versions
 * @global type $wp_version
 * @param type $version
 * @param type $operator
 * @return type 
 */
function wpcf_compare_wp_version($version = '3.2.1', $operator = '>') {
    global $wp_version;
    return version_compare($wp_version, $version, $operator);
}