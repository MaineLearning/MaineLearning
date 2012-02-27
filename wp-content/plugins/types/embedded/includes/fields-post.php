<?php
/*
 * Edit post page functions
 */

/**
 * Init functions for post edit pages.
 * 
 * @param type $upgrade 
 */
function wpcf_admin_post_init($post = false) {

    wpcf_admin_add_js_settings('wpcf_nonce_toggle_group',
            '\'' . wp_create_nonce('group_form_collapsed') . '\'');
    wpcf_admin_add_js_settings('wpcf_nonce_toggle_fieldset',
            '\'' . wp_create_nonce('form_fieldset_toggle') . '\'');

    // Get post_type
    if ($post) {
        $post_type = get_post_type($post);
    } else {
        if (!isset($_GET['post_type'])) {
            $post_type = 'post';
        } else if (in_array($_GET['post_type'],
                        get_post_types(array('show_ui' => true)))) {
            $post_type = $_GET['post_type'];
        } else {
            return false;
        }
    }

    // Add items to View dropdown
    if (in_array($post_type, array('view', 'view-template'))) {
        add_filter('editor_addon_menus_wpv-views',
                'wpcf_admin_post_editor_addon_menus_filter');
        add_action('admin_footer', 'wpcf_admin_post_js_validation');
    }

    // Never show on 'Views' and 'View Templates'
    if (in_array($post_type, array('view', 'view-template'))) {
        return false;
    }

    // Get groups
    $groups = wpcf_admin_post_get_post_groups_fields($post);
    $wpcf_active = false;
    foreach ($groups as $key => $group) {
        if (!empty($group['fields'])) {
            $wpcf_active = true;
            // Process fields
            $group['fields'] = wpcf_admin_post_process_fields($post,
                    $group['fields']);
        }
        // Add meta boxes
        add_meta_box($group['slug'],
                wpcf_translate('group ' . $group['id'] . ' name', $group['name']),
                'wpcf_admin_post_meta_box', $post_type,
                $group['meta_box_context'], 'high', $group);
    }

    // Activate scripts
    if ($wpcf_active) {
        wp_enqueue_script('wpcf-fields-post',
                WPCF_EMBEDDED_RES_RELPATH . '/js/fields-post.js',
                array('jquery'), WPCF_VERSION);
        wp_enqueue_script('wpcf-form-validation',
                WPCF_EMBEDDED_RES_RELPATH . '/js/'
                . 'jquery-form-validation/jquery.validate.min.js',
                array('jquery'), WPCF_VERSION);
        wp_enqueue_script('wpcf-form-validation-additional',
                WPCF_EMBEDDED_RES_RELPATH . '/js/'
                . 'jquery-form-validation/additional-methods.min.js',
                array('jquery'), WPCF_VERSION);
        wp_enqueue_style('wpcf-fields-basic',
                WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(),
                WPCF_VERSION);
        wp_enqueue_style('wpcf-fields-post',
                WPCF_EMBEDDED_RES_RELPATH . '/css/fields-post.css',
                array('wpcf-fields-basic'), WPCF_VERSION);
        add_action('admin_footer', 'wpcf_admin_post_js_validation');
    }
}

/**
 * Renders meta box content.
 * 
 * @param type $post
 * @param type $group 
 */
function wpcf_admin_post_meta_box($post, $group) {
    if (!empty($group['args']['fields'])) {
        // Display description
        if (!empty($group['args']['description'])) {
            echo '<div class="wpcf-meta-box-description">'
            . wpautop(wpcf_translate('group ' . $group['args']['id'] . ' description',
                            $group['args']['description'])) . '</div>';
        }
        foreach ($group['args']['fields'] as $field_slug => $field) {
            // Render form elements
            if (wpcf_compare_wp_version() && $field['#type'] == 'wysiwyg') {
                // Especially for WYSIWYG
                unset($field['#before'], $field['#after']);
                echo '<div class="wpcf-wysiwyg">';
                echo '<div id="wpcf-textarea-textarea-wrapper" class="form-item form-item-textarea wpcf-form-item wpcf-form-item-textarea">
<label class="wpcf-form-label wpcf-form-textarea-label">' . $field['#title'] . '</label>';
                echo '<div class="description wpcf-form-description wpcf-form-description-textarea description-textarea">
' . wpautop($field['#description']) . '</div>';
                wp_editor($field['#value'], $field['#id'],
                        $field['#editor_settings']);
                $field['slug'] = str_replace(WPCF_META_PREFIX . 'wysiwyg-', '',
                        $field_slug);
                $field['type'] = 'wysiwyg';
                echo '</div></div><br /><br />';
            } else {
                if ($field['#type'] == 'wysiwyg') {
                    $field['#type'] = 'textarea';
                }
                echo wpcf_form_simple(array($field['#id'] => $field));
            }
            do_action('wpcf_fields_' . $field_slug . '_meta_box_form', $field);
            if (isset($field['wpcf-type'])) { // May be ignored
                do_action('wpcf_fields_' . $field['wpcf-type'] . '_meta_box_form',
                        $field);
            }
        }
    }
}

/**
 * save_post hook.
 * 
 * @param type $post_ID
 * @param type $post 
 */
function wpcf_admin_post_save_post_hook($post_ID, $post) {
    // TODO Check if this prevents saving from outside of post form
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],
                    'update-' . $post->post_type . '_' . $post_ID)) {
        return false;
    }
    if (!in_array($post->post_type,
                    array('revision', 'attachment', 'wp-types-group', 'view',
                'view-template'))) {

        // Get groups
        $groups = wpcf_admin_post_get_post_groups_fields($post);
        if (empty($groups)) {
            return false;
        }
        $all_fields = array();
        foreach ($groups as $group) {
            // Process fields
            $fields = wpcf_admin_post_process_fields($post, $group['fields'],
                    true);
            // Validate fields
            $form = wpcf_form_simple_validate($fields);
            $all_fields = $all_fields + $fields;
            $error = $form->isError();
            // Trigger form error
            if ($error) {
                wpcf_admin_message_store(
                        __('Please check your input data', 'wpcf'), 'error');
            }
        }

        // Save invalid elements so user can be informed after redirect
        if (!empty($all_fields)) {
            update_post_meta($post_ID, 'wpcf-invalid-fields', $all_fields);
        }

        // Save meta fields
        if (!empty($_POST['wpcf'])) {
            foreach ($_POST['wpcf'] as $field_slug => $field_value) {

                // Don't save invalid
                if (isset($all_fields[wpcf_types_get_meta_prefix(wpcf_admin_fields_get_field($field_slug)) . $field_slug])
                        && isset($all_fields[wpcf_types_get_meta_prefix(wpcf_admin_fields_get_field($field_slug)) . $field_slug]['#error'])) {
                    continue;
                }

                // Get field by slug
                $field = wpcf_fields_get_field_by_slug($field_slug);
                if (!empty($field)) {

                    // Apply filters
                    $field_value = apply_filters('wpcf_fields_value_save',
                            $field_value, $field['type'], $field_slug);
                    $field_value = apply_filters('wpcf_fields_slug_' . $field_slug
                            . '_value_save', $field_value);
                    $field_value = apply_filters('wpcf_fields_type_' . $field['type']
                            . '_value_save', $field_value);

                    // Save field
                    update_post_meta($post_ID,
                            wpcf_types_get_meta_prefix($field) . $field_slug,
                            $field_value);

                    do_action('wpcf_fields_slug_' . $field_slug . '_save',
                            $field_value);
                    do_action('wpcf_fields_type_' . $field['type'] . '_save',
                            $field_value);
                }
            }
        }

        // Process checkboxes
        foreach ($all_fields as $field) {
            if ($field['#type'] == 'checkbox'
                    && !isset($_POST['wpcf'][$field['wpcf-slug']])) {
                delete_post_meta($post_ID,
                        wpcf_types_get_meta_prefix($field) . $field['wpcf-slug']);
            }
        }
    }
}

/**
 * Renders JS validation script.
 */
function wpcf_admin_post_js_validation() {
    wpcf_form_render_js_validation('#post');

    ?>
    <script type="text/javascript">
        //<![CDATA[
        function wpcfFieldsEditorCallback(field_id) {
            var url = "<?php echo admin_url('admin-ajax.php'); ?>?action=wpcf_ajax&wpcf_action=editor_callback&_wpnonce=<?php echo wp_create_nonce('editor_callback'); ?>&field_id="+field_id+"&keepThis=true&TB_iframe=true&height=400&width=400";
            tb_show("<?php
    _e('Insert field', 'wpcf');

    ?>", url);
        }
        //]]>
    </script>
    <?php
}

/**
 * Creates form elements.
 * 
 * @param type $post
 * @param type $fields
 * @return type 
 */
function wpcf_admin_post_process_fields($post = false, $fields = array()) {
    global $pagenow;

    // Get cached
    static $cache = array();
    $cache_key = $post ? $post->ID : false;
    if ($cache_key && isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $fields_processed = array();

    // Get invalid fields (if submitted)
    if ($post) {
        $invalid_fields = get_post_meta($post->ID, 'wpcf-invalid-fields', true);
        delete_post_meta($post->ID, 'wpcf-invalid-fields');
    }

    $original_cf = array();
    if (function_exists('wpml_get_copied_fields_for_post_edit')) {
        $original_cf = wpml_get_copied_fields_for_post_edit();
    }

    foreach ($fields as $field) {
        $field = wpcf_admin_fields_get_field($field['id']);
        if (!empty($field)) {

            $field_id = 'wpcf-' . $field['type'] . '-' . $field['slug'];
            $field_init_data = wpcf_fields_type_action($field['type']);

            // Get inherited field
            $inherited_field_data = false;
            if (isset($field_init_data['inherited_field_type'])) {
                $inherited_field_data = wpcf_fields_type_action($field_init_data['inherited_field_type']);
            }

            // Set value
            $field['value'] = '';
            if ($post) {
                $field['value'] = get_post_meta($post->ID,
                        wpcf_types_get_meta_prefix($field) . $field['slug'],
                        true);
            } else {
                // see if it's in the original custom fields to copy.
                if (!empty($original_cf['fields'])) {
                    foreach ($original_cf['fields'] as $cf_id) {
                        if (wpcf_types_get_meta_prefix($field) . $field['slug'] == $cf_id) {
                            $field['wpml_action'] = 'copy';
                            $field['value'] = get_post_meta($original_cf['original_post_id'],
                                    wpcf_types_get_meta_prefix($field) . $field['slug'],
                                    true);
                            break;
                        }
                    }
                }
            }

            // Mark any field that is going to be copied.
            if (!empty($original_cf['fields'])) {
                foreach ($original_cf['fields'] as $cf_id) {
                    if (wpcf_types_get_meta_prefix($field) . $field['slug'] == $cf_id) {
//                        $field['description_extra'] = $original_cf['copy_message'];
                        $field['readonly'] = true;
                        $field['wpml_action'] = 'copy';
                        break;
                    }
                }
            }

            // Apply filters
            $field['value'] = apply_filters('wpcf_fields_value_get',
                    $field['value'], $field, $field_init_data);
            $field['value'] = apply_filters('wpcf_fields_slug_' . $field['slug']
                    . '_value_get', $field['value'], $field, $field_init_data);
            $field['value'] = apply_filters('wpcf_fields_type_' . $field['type']
                    . '_value_get', $field['value'], $field, $field_init_data);

            // Process JS
            if (!empty($field_init_data['meta_box_js'])) {
                foreach ($field_init_data['meta_box_js'] as $handle => $data) {
                    if (isset($data['inline'])) {
                        add_action('admin_footer', $data['inline']);
                        continue;
                    }
                    $deps = !empty($data['deps']) ? $data['deps'] : array();
                    $in_footer = !empty($data['in_footer']) ? $data['in_footer'] : false;
                    wp_register_script($handle, $data['src'], $deps,
                            WPCF_VERSION, $in_footer);
                    wp_enqueue_script($handle);
                }
            }

            // Process CSS
            if (!empty($field_init_data['meta_box_css'])) {
                foreach ($field_init_data['meta_box_css'] as $handle => $data) {
                    if (isset($data['src'])) {
                        $deps = !empty($data['deps']) ? $data['deps'] : array();
                        wp_enqueue_style($handle, $data['src'], $deps,
                                WPCF_VERSION);
                    } else if (isset($data['inline'])) {
                        add_action('admin_head', $data['inline']);
                    }
                }
            }

            $element = array();

            // Set inherited values
            if ($inherited_field_data) {
                if (function_exists('wpcf_fields_'
                                . $field_init_data['inherited_field_type']
                                . '_meta_box_form')) {
                    $element = call_user_func('wpcf_fields_'
                            . $field_init_data['inherited_field_type']
                            . '_meta_box_form', $field);
                }
            }

            // Set generic values
            $element = array_merge(array(
                '#type' => isset($field_init_data['inherited_field_type']) ? $field_init_data['inherited_field_type'] : $field['type'],
                '#id' => $field_id,
                '#title' => wpcf_translate('field ' . $field['id'] . ' name',
                        $field['name']),
                '#description' => wpautop(wpcf_translate('field ' . $field['id'] . ' description',
                                $field['description'])),
                '#name' => 'wpcf[' . $field['slug'] . ']',
                '#value' => isset($field['value']) ? $field['value'] : '',
                'wpcf-id' => $field['id'],
                'wpcf-slug' => $field['slug'],
                'wpcf-type' => $field['type'],
                    ), $element);

            if (isset($field['description_extra'])) {
                $element['#description'] .= wpautop($field['description_extra']);
            }

            // Set atributes #1
            if (isset($field['disable'])) {
                $field['#disable'] = $field['disable'];
            }
            if (!empty($field['disable'])) {
                $field['#attributes']['disabled'] = 'disabled';
            }
            if (!empty($field['readonly'])) {
                $field['#attributes']['readonly'] = 'readonly';
            }

            // Set specific values
            if (defined('WPCF_INC_ABSPATH')
                    && file_exists(WPCF_INC_ABSPATH . '/fields/' . $field['type']
                            . '.php')) {
                require_once WPCF_INC_ABSPATH . '/fields/' . $field['type']
                        . '.php';
            }
            // Load field
            // TODO remove
//            wpcf_fields_type_action($field['type']);
//            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/' . $field['type']
//                    . '.php';
            if (function_exists('wpcf_fields_' . $field['type']
                            . '_meta_box_form')) {
                $element_specific = call_user_func('wpcf_fields_'
                        . $field['type'] . '_meta_box_form', $field);
                // Check if it's single
                if (isset($element_specific['#type'])) {
                    // Format description
                    if (!empty($element_specific['#description'])) {
                        $element_specific['#description'] = wpautop($element_specific['#description']);
                    }
                    $element = array_merge($element, $element_specific);
                } else { // More fields, loop all
                    foreach ($element_specific as $element_specific_fields_key => $element_specific_fields_value) {
                        // Format description
                        if (!empty($element_specific_fields_value['#description'])) {
                            $element_specific_fields_value['#description'] = wpautop($element_specific_fields_value['#description']);
                        }
                        // If no ID
                        if (!isset($element_specific_fields_value['#id'])) {
                            $element_specific_fields_value['#id'] = 'wpcf-'
                                    . $field['slug'] . '-' . mt_rand();
                        }
                        // If no name, name = #ignore or id = #ignore - IGNORE
                        if (!isset($element_specific_fields_value['#name'])
                                || $element_specific_fields_value['#name'] == '#ignore'
                                || $element_specific_fields_value['#id'] == '#ignore') {
                            $element_specific_fields_value['#id'] = 'wpcf-'
                                    . $field['slug'] . '-' . mt_rand();
                            $element_specific_fields_value['#name'] = 'wpcf[ignore][' . mt_rand() . ']';
                            $fields_processed[$element_specific_fields_value['#id']] = $element_specific_fields_value;
                            continue;
                        }
                        // This one is actually value and keep it (#name is required)
                        $element = array_merge($element,
                                $element_specific_fields_value);
                        // Add it here to keep order
                        $fields_processed[$element['#id']] = $element;
                    }
                }
            }

            // Set atributes #2 (override)
            if (isset($field['disable'])) {
                $element['#disable'] = $field['disable'];
            }
            if (!empty($field['disable'])) {
                $element['#attributes']['disabled'] = 'disabled';
            }

            if (!empty($field['readonly'])) {
                $element['#attributes']['readonly'] = 'readonly';
                if (!empty($element['#options'])) {
                    foreach ($element['#options'] as $key => $option) {
                        if (!is_array($option)) {
                            $element['#options'][$key] = array(
                                '#title' => $key,
                                '#value' => $option,
                            );
                        }
                        $element['#options'][$key]['#attributes']['readonly'] = 'readonly';
                        if ($element['#type'] == 'select') {
                            $element['#options'][$key]['#attributes']['disabled'] = 'disabled';
                        }
                    }
                }
                if ($element['#type'] == 'select') {
                    $element['#attributes']['disabled'] = 'disabled';
                }
            }

            // Set validation element
            if (isset($field['data']['validate'])) {
                $element['#validate'] = $field['data']['validate'];
            }

            // Check if it was invalid no submit and add error message
            if ($post && !empty($invalid_fields)) {
                if (isset($invalid_fields[$element['#id']]['#error'])) {
                    $element['#error'] = $invalid_fields[$element['#id']]['#error'];
                }
            }

            // Set WPML locked icon
            if (isset($field['wpml_action']) && $field['wpml_action'] == 'copy') {
                $element['#title'] .= '<img src="' . WPCF_EMBEDDED_RES_RELPATH . '/images/locked.png" alt="'
                        . __('This field is locked for editing because WPML will copy its value from the original language.',
                                'wpcf') . '" title="'
                        . __('This field is locked for editing because WPML will copy its value from the original language.',
                                'wpcf') . '" style="position:relative;left:2px;top:2px;" />';
            }

            // Add to editor
            wpcf_admin_post_add_to_editor($field);

            // Add shortcode info
//            $shortcode = '<div class="wpcf-shortcode">'
//                    . __('Shortcode:', 'wpcf') . ' '
//                    . '<span class="code">' . wpcf_fields_get_shortcode($field)
//                    . '</span></div>';
//            if (isset($element['#after']) && strpos($element['#after'],
//                            'class="wpcf-shortcode"') === FALSE) {
//                $element['#after'] .= $shortcode;
//            } else {
//                $element['#after'] = $shortcode;
//            }

            $fields_processed[$element['#id']] = $element;
        }
    }

    if ($cache_key && isset($cache[$cache_key])) {
        $cache[$cache_key] = $fields_processed;
    }

    return $fields_processed;
}

/**
 * Gets all groups and fields for post.
 * 
 * @param type $post_ID
 * @return type 
 */
function wpcf_admin_post_get_post_groups_fields($post = false) {

    // Get post_type
    if (!empty($post)) {
        $post_type = get_post_type($post);
    } else {
        if (!isset($_GET['post_type'])) {
            $post_type = 'post';
        } else if (in_array($_GET['post_type'],
                        get_post_types(array('show_ui' => true)))) {
            $post_type = $_GET['post_type'];
        } else {
            $post_type = 'post';
        }
    }

    // Get post terms
    $support_terms = false;
    if (!empty($post)) {
        $post->_wpcf_post_terms = array();
        $taxonomies = get_taxonomies('', 'objects');
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $tax_slug => $tax) {
                $temp_tax = get_taxonomy($tax_slug);
                if (!in_array($post_type, $temp_tax->object_type)) {
                    continue;
                }
                $support_terms = true;
                $terms = wp_get_post_terms($post->ID, $tax_slug,
                        array('fields' => 'ids'));
                foreach ($terms as $term_id) {
                    $post->_wpcf_post_terms[] = $term_id;
                }
            }
        }
    }

    // Get post template
    if (empty($post)) {
        $post->_wpcf_post_template = false;
        $post->_wpcf_post_views_template = false;
    } else {
        $post->_wpcf_post_template = get_post_meta($post->ID,
                '_wp_page_template', true);
        $post->_wpcf_post_views_template = get_post_meta($post->ID,
                '_views_template', true);
    }
    
    if (empty($post->_wpcf_post_terms)) {
        $post->_wpcf_post_terms = array();
    }

    $support_templates = !empty($post->_wpcf_post_template) || !empty($post->_wpcf_post_views_template);

    // Filter groups
    $groups = array();
    $groups_all = wpcf_admin_fields_get_groups();
    foreach ($groups_all as $temp_key => $temp_group) {
        if (empty($temp_group['is_active'])) {
            unset($groups_all[$temp_key]);
            continue;
        }
        // Get filters
        $groups_all[$temp_key]['_wp_types_group_post_types'] = explode(',',
                trim(get_post_meta($temp_group['id'],
                                '_wp_types_group_post_types', true), ','));
        $groups_all[$temp_key]['_wp_types_group_terms'] = explode(',',
                trim(get_post_meta($temp_group['id'], '_wp_types_group_terms',
                                true), ','));
        $groups_all[$temp_key]['_wp_types_group_templates'] = explode(',',
                trim(get_post_meta($temp_group['id'],
                                '_wp_types_group_templates', true), ','));

        $has_type = $has_term = $has_template = true;

        $post_type_filter = $groups_all[$temp_key]['_wp_types_group_post_types'][0] == 'all' ? -1 : 0;
        $taxonomy_filter = $groups_all[$temp_key]['_wp_types_group_terms'][0] == 'all' ? -1 : 0;
        $template_filter = $groups_all[$temp_key]['_wp_types_group_templates'][0] == 'all' ? -1 : 0;

        // See if post type matches
        if ($post_type_filter == 0 && in_array($post_type,
                        $groups_all[$temp_key]['_wp_types_group_post_types'])) {
            $post_type_filter = 1;
        }

        // See if terms match
        if ($taxonomy_filter == 0) {
            foreach ($post->_wpcf_post_terms as $temp_post_term) {
                if (in_array($temp_post_term,
                                $groups_all[$temp_key]['_wp_types_group_terms'])) {
                    $taxonomy_filter = 1;
                }
            }
        }

        // See if template match
        if ($template_filter == 0) {
            if ((!empty($post->_wpcf_post_template) && in_array($post->_wpcf_post_template,
                            $groups_all[$temp_key]['_wp_types_group_templates']))
                    || (!empty($post->_wpcf_post_views_template) && in_array($post->_wpcf_post_views_template,
                            $groups_all[$temp_key]['_wp_types_group_templates']))) {
                $template_filter = 1;
            }
        }
        // Filter by association
        if ($post_type_filter == -1 && $taxonomy_filter == -1 && $template_filter == -1) {
            $passed = 1;
        } else if ($groups_all[$temp_key]['filters_association'] == 'any') {
            $passed = $post_type_filter == 1 || $taxonomy_filter == 1 || $template_filter == 1;
        } else {
            $passed = $post_type_filter != 0 && $taxonomy_filter != 0 && $template_filter != 0;
        }
        if (!$passed) {
            unset($groups_all[$temp_key]);
        } else {
            $groups_all[$temp_key]['fields'] = wpcf_admin_fields_get_fields_by_group($temp_group['id'],
                                        'slug', true, false, true);
        }
    }
    $groups = apply_filters('wpcf_post_groups', $groups_all, $post);
    return $groups;

    // TODO Remove
    // Get by taxonomy
    $distinct_terms = array();
    if ($post) {
        $taxonomies = get_taxonomies('', 'objects');
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $tax_slug => $tax) {
                $terms = wp_get_post_terms($post->ID, $tax_slug,
                        array('fields' => 'ids'));
                foreach ($terms as $term_id) {
                    $groups_by_term = wpcf_admin_fields_get_groups_by_term($term_id,
                            true, $post_type);
                    if (!empty($groups_by_term)) {
                        foreach ($groups_by_term as $group) {
                            if (!isset($groups[$group['id']])) {
                                $groups[$group['id']] = $group;
                                $groups[$group['id']]['fields'] = wpcf_admin_fields_get_fields_by_group($group['id'],
                                        'slug', true, false, true);
                            }
                            $groups[$group['id']]['__wpcf_terms'] = 1;
                        }
                    }
                    $distinct_terms[] = $term_id;
                }
            }
            // Fetch displayed on all
            $groups_by_term = wpcf_admin_fields_get_groups_by_term(false, true,
                    $post_type);
            if (!empty($groups_by_term)) {
                foreach ($groups_by_term as $group) {
                    if (!isset($groups[$group['id']])) {
                        $groups[$group['id']] = $group;
                        $groups[$group['id']]['fields'] = wpcf_admin_fields_get_fields_by_group($group['id'],
                                'slug', true, false, true);
                    }
                    $groups[$group['id']]['__wpcf_terms'] = 1;
                }
            }
        }
    } else {
        $groups_by_term = wpcf_admin_fields_get_groups_by_term(false, true,
                $post_type);
        if (!empty($groups_by_term)) {
            foreach ($groups_by_term as $group) {
                if (!isset($groups[$group['id']])) {
                    $groups[$group['id']] = $group;
                    $groups[$group['id']]['fields'] = wpcf_admin_fields_get_fields_by_group($group['id'],
                            'slug', true, false, true);
                }
                $groups[$group['id']]['__wpcf_terms'] = 1;
            }
        }
    }

    // Get by post_type
    $groups_by_post_type = wpcf_admin_get_groups_by_post_type($post_type, true,
            $distinct_terms);
    if (!empty($groups_by_post_type)) {
        foreach ($groups_by_post_type as $key => $group) {
            if (!isset($groups[$group['id']])) {
                $groups[$group['id']] = $group;
                $groups[$group['id']]['fields'] = wpcf_admin_fields_get_fields_by_group($group['id'],
                        'slug', true, false, true);
            }
            $groups[$group['id']]['__wpcf_types'] = 1;
        }
    }

    // Get by template
    if (empty($post)) {
        $templates = array('_wp_page_template' => 'default');
    } else {
        $templates = array(
            '_wp_page_template' => get_post_meta($post->ID, '_wp_page_template',
                    true),
            '_views_template' => get_post_meta($post->ID, '_views_template',
                    true),
        );
    }
    $groups_by_template = wpcf_admin_get_groups_by_template($templates, false);
    if (!empty($groups_by_template)) {
        foreach ($groups_by_template as $key => $group) {
            if (!isset($groups[$group['id']])) {
                $groups[$group['id']] = $group;
                $groups[$group['id']]['fields'] = wpcf_admin_fields_get_fields_by_group($group['id'],
                        'slug', true, false, true);
            }
            $groups[$group['id']]['__wpcf_templates'] = 1;
        }
    }

    foreach ($groups as $group_id => $group) {
        if (!empty($group['filters_association'])) {
            if ($group['filters_association'] == 'all'
                    && (empty($group['__wpcf_templates'])
                    || empty($group['__wpcf_terms'])
                    || empty($group['__wpcf_types']))
            ) {
                unset($groups[$group_id]);
            }
        }
    }
    $groups = apply_filters('wpcf_post_groups', $groups, $post);
    return $groups;
}

/**
 * Stores fields for editor menu.
 * 
 * @staticvar array $fields
 * @param type $field
 * @return array 
 */
function wpcf_admin_post_add_to_editor($field) {
    static $fields = array();
    if ($field == 'get') {
        return $fields;
    }
    if (empty($fields)) {
        add_action('admin_enqueue_scripts', 'wpcf_admin_post_add_to_editor_js');
    }
    $fields[$field['id']] = $field;
}

/**
 * Renders JS for editor menu.
 * 
 * @return type 
 */
function wpcf_admin_post_add_to_editor_js() {
    global $post;
    $fields = wpcf_admin_post_add_to_editor('get');
    $groups = wpcf_admin_post_get_post_groups_fields($post);
    if (empty($fields) || empty($groups)) {
        return false;
    }
    $editor_addon = new Editor_addon('types',
                    __('Insert Types Shortcode', 'wpcf'),
                    WPCF_EMBEDDED_RES_RELPATH . '/js/types_editor_plugin.js',
                    WPCF_EMBEDDED_RES_RELPATH . '/images/bw-logo-16.png');

    foreach ($groups as $group) {
        if (empty($group['fields'])) {
            continue;
        }
        foreach ($group['fields'] as $group_field_id => $group_field) {
            if (!isset($fields[$group_field_id])) {
                continue;
            }
            $field = $fields[$group_field_id];
            $data = wpcf_fields_type_action($field['type']);
            $callback = '';
            if (isset($data['editor_callback'])) {
                $callback = sprintf($data['editor_callback'], $field['id']);
            } else {
                // Set callback if function exists
                $function = 'wpcf_fields_' . $field['type'] . '_editor_callback';
                $callback = function_exists($function) ? 'wpcfFieldsEditorCallback(\'' . $field['id'] . '\')' : '';
            }

            $editor_addon->add_insert_shortcode_menu(stripslashes($field['name']),
                    trim(wpcf_fields_get_shortcode($field), '[]'),
                    $group['name'], $callback);
        }
    }
}

/**
 * Adds items to view dropdown.
 * 
 * @param type $items
 * @return type 
 */
function wpcf_admin_post_editor_addon_menus_filter($items) {
    $groups = wpcf_admin_fields_get_groups();
    $add = array();
    if (!empty($groups)) {
        foreach ($groups as $group_id => $group) {
            $fields = wpcf_admin_fields_get_fields_by_group($group['id'],
                    'slug', true, false, true);
            if (!empty($fields)) {
                foreach ($fields as $field_id => $field) {
                    // Get field data
                    $data = wpcf_fields_type_action($field['type']);

                    // Get inherited field
                    if (isset($data['inherited_field_type'])) {
                        $inherited_field_data = wpcf_fields_type_action($data['inherited_field_type']);
                    }

                    $callback = '';
                    if (isset($data['editor_callback'])) {
                        $callback = sprintf($data['editor_callback'],
                                $field['id']);
                    } else {
                        // Set callback if function exists
                        $function = 'wpcf_fields_' . $field['type'] . '_editor_callback';
                        $callback = function_exists($function) ? 'wpcfFieldsEditorCallback(\'' . $field['id'] . '\')' : '';
                    }
                    $add[$group['name']][stripslashes($field['name'])] = array(stripslashes($field['name']), trim(wpcf_fields_get_shortcode($field),
                                '[]'), $group['name'], $callback);

                    // Process JS
                    if (!empty($data['meta_box_js'])) {
                        foreach ($data['meta_box_js'] as $handle => $data_script) {
                            if (isset($data_script['inline'])) {
                                add_action('admin_footer',
                                        $data_script['inline']);
                                continue;
                            }
                            $deps = !empty($data_script['deps']) ? $data_script['deps'] : array();
                            wp_enqueue_script($handle, $data_script['src'],
                                    $deps, WPCF_VERSION);
                        }
                    }

                    // Process CSS
                    if (!empty($data['meta_box_css'])) {
                        foreach ($data['meta_box_css'] as $handle => $data_script) {
                            $deps = !empty($data_script['deps']) ? $data_script['deps'] : array();
                            if (isset($data_script['inline'])) {
                                add_action('admin_header',
                                        $data_script['inline']);
                                continue;
                            }
                            wp_enqueue_style($handle, $data_script['src'],
                                    $deps, WPCF_VERSION);
                        }
                    }
                }
            }
        }
    }

    $search_key = '';

    foreach ($items as $key => $item) {
        if ($key == __('Basic', 'wpv-views')) {
            $search_key = 'found';
            continue;
        }
        if ($search_key == 'found') {
            $search_key = $key;
        }
        if ($key == __('Field', 'wpv-views') && isset($item[trim(wpcf_types_get_meta_prefix(),
                                '-')])) {
            unset($items[$key][trim(wpcf_types_get_meta_prefix(), '-')]);
        }
    }
    if (empty($search_key) || $search_key == 'found') {
        $search_key = count($items);
    }

    $insert_position = array_search($search_key, array_keys($items));
    $part_one = array_slice($items, 0, $insert_position);
    $part_two = array_slice($items, $insert_position);
    $items = $part_one + $add + $part_two;
    return $items;
}
