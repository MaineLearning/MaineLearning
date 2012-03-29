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

        case 'pr_add_child_post':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if (isset($_GET['post_id']) && isset($_GET['post_type_child']) && isset($_GET['post_type_parent'])) {
                $relationships = get_option('wpcf_post_relationship', array());
                $post = get_post($_GET['post_id']);
                $post_type = $_GET['post_type_child'];
                $parent_post_type = $_GET['post_type_parent'];
                $data = $relationships[$parent_post_type][$post_type];
                $output = wpcf_pr_admin_post_meta_box_has_row($post, $post_type,
                        $data, $parent_post_type, false);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        case 'pr_save_child_post':
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = array();
            if (isset($_GET['post_id']) && isset($_GET['post_type_child'])) {
                $post = get_post($_GET['post_id']);
                $post_type = $_GET['post_type_child'];
//                $output = wpcf_pr_admin_save_child_item($post, $post_type,
//                        $_POST);
                $output = wpcf_pr_admin_save_post_hook($_GET['post_id']);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        case 'pr_delete_child_post':
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if (isset($_GET['post_id'])) {
                $output = wpcf_pr_admin_delete_child_item($_GET['post_id']);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        case 'pr-update-belongs':
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if (isset($_POST['post_id']) && isset($_POST['wpcf_pr_belongs'])) {
                $output = wpcf_pr_admin_update_belongs($_POST['post_id'],
                        $_POST['wpcf_pr_belongs']);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        case 'pr_pagination':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if (isset($_GET['post_id']) && isset($_GET['post_type'])) {
                $post = get_post($_GET['post_id']);
                $post_type = $_GET['post_type'];
                $has = wpcf_pr_admin_get_has($post->post_type);
                $output = wpcf_pr_admin_post_meta_box_has_form($post,
                        $post_type, $has[$post_type], $post->post_type);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        case 'pr_sort':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if (isset($_GET['field']) && isset($_GET['sort']) && isset($_GET['post_id']) && isset($_GET['post_type'])) {
                $post = get_post($_GET['post_id']);
                $post_type = $_GET['post_type'];
                $has = wpcf_pr_admin_get_has($post->post_type);
                $output = wpcf_pr_admin_post_meta_box_has_form($post,
                        $post_type, $has[$post_type], $post->post_type);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;
            
        case 'pr_sort_parent':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if (isset($_GET['field']) && isset($_GET['sort']) && isset($_GET['post_id']) && isset($_GET['post_type'])) {
                $post = get_post($_GET['post_id']);
                $post_type = $_GET['post_type'];
                $has = wpcf_pr_admin_get_has($post->post_type);
                $output = wpcf_pr_admin_post_meta_box_has_form($post,
                        $post_type, $has[$post_type], $post->post_type);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;
            
        case 'pr_save_all':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = array();
            if (isset($_POST['post_id']) && isset($_POST['wpcf_post_relationship'])) {
                $output = wpcf_pr_admin_save_post_hook($_POST['post_id']);
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        default:
            break;
    }
    if (function_exists('wpcf_ajax')) {
        wpcf_ajax();
    }
    die();
}