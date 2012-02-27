<?php

/**
 * All AJAX calls go here.
 */
function wpcf_ajax_embedded() {
    if (!current_user_can('manage_options')
            || (!isset($_REQUEST['_wpnonce'])
            || !wp_verify_nonce($_REQUEST['_wpnonce'], $_REQUEST['wpcf_action']))) {
        die('Verification failed');
    }
    switch ($_REQUEST['wpcf_action']) {
        
        case 'editor_insert_date':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/date.php';
            wpcf_fields_date_editor_form();
            break;

        case 'insert_skype_button':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/skype.php';
            wpcf_fields_skype_meta_box_ajax();
            break;

        case 'editor_callback':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            $field = wpcf_admin_fields_get_field($_GET['field_id']);
            if (!empty($field)) {
                // TODO Remove
//                $file = WPCF_EMBEDDED_INC_ABSPATH . '/fields/' . $field['type'] . '.php';
//                if (file_exists($file)) {
//                    require_once $file;
                    $function = 'wpcf_fields_' . $field['type'] . '_editor_callback';
                    if (function_exists($function)) {
                        call_user_func($function);
                    }
//                }
            }
            break;
            
        case 'dismiss_message':
            if (isset($_GET['id'])) {
                $messages = get_option('wpcf_dismissed_messages', array());
                $messages[] = $_GET['id'];
                update_option('wpcf_dismissed_messages', $messages);
            }
            break;

        default:
            break;
    }
    if (function_exists('wpcf_ajax')) {
        wpcf_ajax();
    }
    die();
}