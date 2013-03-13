<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_textarea() {
    return array(
        'id' => 'wpcf-textarea',
        'title' => __('Multiple lines', 'wpcf'),
        'description' => __('Textarea', 'wpcf'),
        'validate' => array('required'),
    );
}

/**
 * Meta box form.
 * 
 * @param type $field
 * @return string 
 */
function wpcf_fields_textarea_meta_box_form($field) {
    $form = array();
    $form['name'] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[' . $field['slug'] . ']',
    );
    return $form;
}

/**
 * Formats display data.
 */
function wpcf_fields_textarea_view($params) {

    $value = $params['field_value'];

    // see if it's already wrapped in <p> ... </p>
    $wrapped_in_p = false;
    if (!empty($value) && strpos($value, '<p>') === 0 && strrpos($value,
                    "</p>\n") == strlen($value) - 5) {
        $wrapped_in_p = true;
    }

    // use wpautop for converting line feeds to <br />, etc
    $value = wpautop($value);

    if (!$wrapped_in_p) {
        // If it wasn't wrapped then remove the wrapping wpautop has added.
        if (!empty($value) && strpos($value, '<p>') === 0 && strrpos($value,
                        "</p>\n") == strlen($value) - 5) {
            // unwrapp the <p> ..... </p>
            $value = substr($value, 3, -5);
        }
    }

    return $value;
}