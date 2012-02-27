<?php
/*
 * Frontend functions.
 */
add_shortcode('types', 'wpcf_shortcode');

function wpcf_shortcode($atts, $content = null, $code = '') {
    $atts = array_merge(array(
        'field' => false,
        'style' => 'default',
        'show_name' => false,
        'raw' => false,
            ), $atts
    );
    if ($atts['field']) {
        return types_render_field($atts['field'], $atts, $content, $code);
    }
    return '';
}

/**
 * Calls view function for specific field type.
 * 
 * @param type $field
 * @param type $atts
 * @return type 
 */
function types_render_field($field, $params, $content = null, $code = '') {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';

    // Count fields (if there are duplicates)
    static $count = array();

    // Get field
    $field = wpcf_fields_get_field_by_slug($field);
    if (empty($field)) {
        return '';
    }

    // Count it
    if (!isset($count[$field['slug']])) {
        $count[$field['slug']] = 1;
    } else {
        $count[$field['slug']] += 1;
    }

    // Get post field value
    global $post;
    $value = get_post_meta($post->ID, wpcf_types_get_meta_prefix($field) . $field['slug'], true);
    if ($value == '' && $field['type'] != 'checkbox') {
        return '';
    }

    // Load type
    $type = wpcf_fields_type_action($field['type']);

    // Apply filters to field value
    $value = apply_filters('wpcf_fields_value_display', $value);
    $value = apply_filters('wpcf_fields_slug_' . $field['slug'] . '_value_display',
            $value);
    $value = apply_filters('wpcf_fields_type_' . $field['type'] . '_value_display',
            $value);
     // To make sure
    if (is_string($value)) {
        $value = addslashes(stripslashes($value));
    }

    // Set values
    $field['name'] = wpcf_translate('field ' . $field['id'] . ' name',
            $field['name']);
    $params['field'] = $field;
    $params['post'] = $post;
    $params['field_value'] = $value;

    // Get output
    $params['#content'] = htmlspecialchars($content);
    $params['#code'] = $code;
    $output = wpcf_fields_type_action($field['type'], 'view', $params);

    // Convert to string
    if (!empty($output)) {
        $output = strval($output);
    }

    // @todo Reconsider if ever changing this (works fine now)
    // If no output or 'raw' return default
    if (($params['raw'] == 'true' || empty($output)) && !empty($value)) {
        $field_name = '';
        if ($params['show_name'] == 'true') {
            $field_name = wpcf_frontend_wrap_field_name($field, $field['name'],
                    $params);
        }
        $field_value = wpcf_frontend_wrap_field_value($field, $value, $params);
        $output = wpcf_frontend_wrap_field($field, $field_name . $field_value);
    }

    // Apply filters
    $output = strval(apply_filters('types_view', $output, $value,
                    $field['type'], $field['slug'], $field['name'], $params));

    // Add count
    if (isset($count[$field['slug']]) && intval($count[$field['slug']]) > 1) {
        $add = '-' . intval($count[$field['slug']]);
        $output = str_replace('id="wpcf-field-' . $field['slug'] . '"',
                'id="wpcf-field-' . $field['slug'] . $add . '"', $output);
    }

    return htmlspecialchars_decode(stripslashes($output));
}

/**
 * Wraps field content.
 * 
 * @param type $field
 * @param type $content
 * @return type 
 */
function wpcf_frontend_wrap_field($field, $content, $params = array()) {
    if (isset($params['output']) && $params['output'] == 'html') {
        // Add name if needed
        if (isset($params['show_name']) && $params['show_name'] == 'true'
                && strpos($content,
                        'class="wpcf-field-' . $field['type']
                        . '-name ') === false) {
            $content = wpcf_frontend_wrap_field_name($field, $field['name'],
                            $params) . $content;
        }
        return '<div id="wpcf-field-' . $field['slug'] . '"'
                . ' class="wpcf-field-' . $field['type'] . ' wpcf-field-'
                . $field['slug'] . '"' . '>' . $content . '</div>';
    } else {
        if (isset($params['show_name']) && $params['show_name'] == 'true'
                && strpos($content, $field['name']) === false) {
            $content = wpcf_frontend_wrap_field_name($field,
                            $params['field']['name'], $params) . $content;
        }
        return $content;
    }
}

/**
 * Wraps field name.
 * 
 * @param type $field
 * @param type $content
 * @return type 
 */
function wpcf_frontend_wrap_field_name($field, $content, $params = array()) {
    if (isset($params['output']) && $params['output'] == 'html') {
        return '<span class="wpcf-field-name wpcf-field-' . $field['type'] . ' wpcf-field-'
                . $field['slug'] . '-name">' . stripslashes($content)
                . ':</span> ';
    } else {
        return stripslashes($content) . ': ';
    }
}

/**
 * Wraps field value.
 * 
 * @param type $field
 * @param type $content
 * @return type 
 */
function wpcf_frontend_wrap_field_value($field, $content, $params = array()) {
    if (isset($params['output']) && $params['output'] == 'html') {
        return '<span class="wpcf-field-value wpcf-field-' . $field['type'] . '-value wpcf-field-'
                . $field['slug'] . '-value">' . stripslashes($content) . '</span>';
    } else {
        return stripslashes($content);
    }
}