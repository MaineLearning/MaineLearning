<?php

/**
 * All AJAX calls go here.
 */
function wpcf_ajax() {
    if (!current_user_can('manage_options')
            || (!isset($_REQUEST['_wpnonce'])
            || !wp_verify_nonce($_REQUEST['_wpnonce'], $_REQUEST['wpcf_action']))) {
        die();
    }
    switch ($_REQUEST['wpcf_action']) {
        case 'fields_insert':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
            wpcf_fields_insert_ajax();
            wpcf_form_render_js_validation();
            break;

        case 'fields_insert_existing':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
            wpcf_fields_insert_existing_ajax();
            wpcf_form_render_js_validation();
            break;

        case 'remove_field_from_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            if (isset($_GET['group_id']) && isset($_GET['field_id'])) {
            wpcf_admin_fields_remove_field_from_group($_GET['group_id'],
                    $_GET['field_id']);
            }
            break;

        case 'deactivate_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $success = wpcf_admin_fields_deactivate_group(intval($_GET['group_id']));
            if ($success) {
                echo json_encode(array(
                    'output' => __('Group deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . intval($_GET['group_id']) . '").replaceWith(\''
                    . wpcf_admin_fields_get_ajax_activation_link(intval($_GET['group_id']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . intval($_GET['group_id']) . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'activate_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $success = wpcf_admin_fields_activate_group(intval($_GET['group_id']));
            if ($success) {
                echo json_encode(array(
                    'output' => __('Group activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . intval($_GET['group_id']) . '").replaceWith(\''
                    . wpcf_admin_fields_get_ajax_deactivation_link(intval($_GET['group_id']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . intval($_GET['group_id']) . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'delete_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            wpcf_admin_fields_delete_group(intval($_GET['group_id']));
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . intval($_GET['group_id'])
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'deactivate_post_type':
            if (!isset($_GET['wpcf-post-type'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-types.php';
            $custom_types = get_option('wpcf-custom-types', array());
            if (isset($custom_types[$_GET['wpcf-post-type']])) {
                $custom_types[$_GET['wpcf-post-type']]['disabled'] = 1;
                update_option('wpcf-custom-types', $custom_types);
                echo json_encode(array(
                    'output' => __('Post type deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . $_GET['wpcf-post-type'] . '").replaceWith(\''
                    . wpcf_admin_custom_types_get_ajax_activation_link(esc_attr(
$_GET['wpcf-post-type']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . $_GET['wpcf-post-type'] . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'activate_post_type':
            if (!isset($_GET['wpcf-post-type'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-types.php';
            $custom_types = get_option('wpcf-custom-types', array());
            if (isset($custom_types[$_GET['wpcf-post-type']])) {
                $custom_types[$_GET['wpcf-post-type']]['disabled'] = 0;
                update_option('wpcf-custom-types', $custom_types);
                echo json_encode(array(
                    'output' => __('Post type activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . $_GET['wpcf-post-type'] . '").replaceWith(\''
                    . wpcf_admin_custom_types_get_ajax_deactivation_link($_GET['wpcf-post-type'])
                    . '\');jQuery(".wpcf-table-column-active-'
                    . $_GET['wpcf-post-type'] . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'delete_post_type':
            if (!isset($_GET['wpcf-post-type'])) {
                die();
            }
            $custom_types = get_option('wpcf-custom-types', array());
            unset($custom_types[$_GET['wpcf-post-type']]);
            update_option('wpcf-custom-types', $custom_types);
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . $_GET['wpcf-post-type']
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'deactivate_taxonomy':
            if (!isset($_GET['wpcf-tax'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
            $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
            if (isset($custom_taxonomies[$_GET['wpcf-tax']])) {
                $custom_taxonomies[$_GET['wpcf-tax']]['disabled'] = 1;
                update_option('wpcf-custom-taxonomies', $custom_taxonomies);
                echo json_encode(array(
                    'output' => __('Taxonomy deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . $_GET['wpcf-tax'] . '").replaceWith(\''
                    . wpcf_admin_custom_taxonomies_get_ajax_activation_link($_GET['wpcf-tax'])
                    . '\');jQuery(".wpcf-table-column-active-'
                    . $_GET['wpcf-tax'] . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'activate_taxonomy':
            if (!isset($_GET['wpcf-tax'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
            $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
            if (isset($custom_taxonomies[$_GET['wpcf-tax']])) {
                $custom_taxonomies[$_GET['wpcf-tax']]['disabled'] = 0;
                update_option('wpcf-custom-taxonomies', $custom_taxonomies);
                echo json_encode(array(
                    'output' => __('Taxonomy activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . $_GET['wpcf-tax'] . '").replaceWith(\''
                    . wpcf_admin_custom_taxonomies_get_ajax_deactivation_link($_GET['wpcf-tax'])
                    . '\');jQuery(".wpcf-table-column-active-'
                    . $_GET['wpcf-tax'] . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'delete_taxonomy':
            if (!isset($_GET['wpcf-tax'])) {
                die();
            }
            $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
            unset($custom_taxonomies[$_GET['wpcf-tax']]);
            update_option('wpcf-custom-taxonomies', $custom_taxonomies);
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . $_GET['wpcf-tax']
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'add_radio_option':
            require_once WPCF_INC_ABSPATH . '/fields/radio.php';
            $element = wpcf_fields_radio_get_option(
                    urldecode($_GET['parent_name']));
            $id = array_shift($element);
            $element_txt = wpcf_fields_radio_get_option_alt_text($id,
                    urldecode($_GET['parent_name']));
            echo json_encode(array(
                'output' => wpcf_form_simple($element),
                'execute' => 'jQuery("#wpcf-form-groups-radio-ajax-response-'
                . urldecode($_GET['wpcf_ajax_update_add']) . '").append(\''
                . trim(str_replace("\r\n", '', wpcf_form_simple($element_txt))) . '\');',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'add_select_option':
            require_once WPCF_INC_ABSPATH . '/fields/select.php';
            $element = wpcf_fields_select_get_option(
                    urldecode($_GET['parent_name']));
            echo json_encode(array(
                'output' => wpcf_form_simple($element)
            ));
            break;

        case 'group_form_collapsed':
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
            $group_id = $_GET['group_id'];
            $action = $_GET['toggle'];
            $fieldset = $_GET['id'];
            wpcf_admin_fields_form_save_open_fieldset($action, $fieldset,
                    $group_id);
            break;

        case 'form_fieldset_toggle':
            $action = $_GET['toggle'];
            $fieldset = $_GET['id'];
            wpcf_admin_form_fieldset_save_toggle($action, $fieldset);
            break;

        case 'group_update_post_types':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $post_types = empty($_GET['wpcf']['group']['supports']) ? array() : $_GET['wpcf']['group']['supports'];
            if (!empty($_GET['group_id'])) {
                wpcf_admin_fields_save_group_post_types($_GET['group_id'],
                        $post_types);
                $output = array();
                foreach ($post_types as $post_type) {
                    $post_type = get_post_type_object($post_type);
                    if (!empty($post_type->label)) {
                        $output[] = $post_type->label;
                    }
                }
                if (empty($post_types)) {
                    $output[] = __('No post types associated', 'wpcf');
                }
                $output = implode(', ', $output);
            } else {
                $output = __('No post types associated', 'wpcf');
            }
            echo json_encode(array(
                'output' => $output
            ));
            break;

        case 'group_update_taxonomies':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $taxonomies_post = empty($_GET['wpcf']['group']['taxonomies']) ? array() : $_GET['wpcf']['group']['taxonomies'];
            $terms = array();
            foreach ($taxonomies_post as $taxonomy) {
                foreach ($taxonomy as $tax => $term) {
                    $terms[] = $term;
                }
            }
            if (!empty($_GET['group_id'])) {
                wpcf_admin_fields_save_group_terms($_GET['group_id'], $terms);
                $output = array();
                foreach ($taxonomies_post as $taxonomy => $terms) {
                    $taxonomy = get_taxonomy($taxonomy);
                    if (!empty($taxonomy)) {
                        $title = $taxonomy->label . ': ';
                        foreach ($terms as $term_id) {
                            $term = get_term($term_id, $taxonomy->name);
                            $output[] = $title . $term->name;
                            $title = '';
                        }
                    }
                }
                if (empty($output)) {
                    $output[] = __('No taxonomies associated', 'wpcf');
                }
                $output = implode(', ', $output);
            } else {
                $output = __('No taxonomies associated', 'wpcf');
            }
            echo json_encode(array(
                'output' => $output
            ));
            break;
            
        case 'custom_fields_control_bulk':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-control.php';
            wpcf_admin_custom_fields_control_bulk_ajax();
            break;
        
        case 'fields_delete':
        case 'delete_field':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            if (isset($_GET['field_id'])) {
                wpcf_admin_fields_delete_field($_GET['field_id']);
            }
            if (isset($_GET['field'])) {
                wpcf_admin_fields_delete_field($_GET['field']);
            }
            echo json_encode(array(
                'output' => ''
            ));
            break;
            
        case 'remove_from_history':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $fields = wpcf_admin_fields_get_fields();
            if (isset($_GET['field_id']) && isset($fields[$_GET['field_id']])) {
                $fields[$_GET['field_id']]['data']['removed_from_history'] = 1;
                wpcf_admin_fields_save_fields($fields, true);
            }
            echo json_encode(array(
                'output' => ''
            ));
            break;

        default:
            break;
    }
    die();
}