<?php
/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_url() {
    return array(
        'id' => 'wpcf-email',
        'title' => 'URL',
        'description' => 'URL',
        'validate' => array('required', 'url'),
        'inherited_field_type' => 'textfield',
    );
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_url_view($params) {
    if ($params['style'] == 'raw') {
        return '';
    }
    $title = '';
    $add = '';
    if (!empty($params['title'])) {
        $add .= ' title="' . $params['title'] . '"';
        $title .= $params['title'];
    } else {
        $add .= ' title="' . $params['field_value'] . '"';
        $title .= $params['field_value'];
    }
    if (!empty($params['class'])) {
        $add .= ' class="' . $params['class'] . '"';
    }
    $output = '<a href="' . $params['field_value'] . '"' . $add . '>'
            . $title . '</a>';
    $output = wpcf_frontend_wrap_field_value($params['field'], $output, $params);
    return wpcf_frontend_wrap_field($params['field'], $output, $params);
}

/**
 * Editor callback form.
 */
function wpcf_fields_url_editor_callback() {
    $form = array();
    $form['#form']['callback'] = 'wpcf_fields_url_editor_submit';
    $form['title'] = array(
        '#type' => 'textfield',
        '#title' => __('Title', 'wpcf'),
        '#description' => __('If set, this text will be displayed instead of raw data'),
        '#name' => 'title',
    );
    $form['submit'] = array(
        '#type' => 'markup',
        '#markup' => get_submit_button(),
    );
    $f = wpcf_form('wpcf-form', $form);
    wpcf_admin_ajax_head('Insert URL', 'wpcf');
    echo '<form method="post" action="">';
    echo $f->renderForm();
    echo '</form>';
    wpcf_admin_ajax_footer();
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_url_editor_submit() {
    $add = '';
    if (!empty($_POST['title'])) {
        $add .= ' title="' . strval($_POST['title']) . '"';
    }
    $add .= ' class=""';
    $field = wpcf_admin_fields_get_field($_GET['field_id']);
    if (!empty($field)) {
        $shortcode = wpcf_fields_get_shortcode($field, $add);
        echo wpcf_admin_fields_popup_insert_shortcode_js($shortcode);
        die();
    }
}