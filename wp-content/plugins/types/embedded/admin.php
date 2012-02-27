<?php

require_once(WPCF_EMBEDDED_ABSPATH . '/common/visual-editor/editor-addon.class.php');

if (defined('DOING_AJAX')) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/ajax.php';
    add_action('wp_ajax_wpcf_ajax', 'wpcf_ajax_embedded');
}
/**
 * admin_init hook.
 */
function wpcf_embedded_admin_init_hook() {
    // Add callbacks for post edit pages
    add_action('load-post.php', 'wpcf_admin_post_page_load_hook');
    add_action('load-post-new.php', 'wpcf_admin_post_page_load_hook');
    
    // Add callback for 'media-upload.php'
    add_filter('get_media_item_args', 'wpcf_get_media_item_args_filter');

    // Add save_post callback
    add_action('save_post', 'wpcf_admin_save_post_hook', 10, 2);
    
    // Render messages
    wpcf_show_admin_messages();
    
    // Render JS settings
    add_action('admin_head', 'wpcf_admin_render_js_settings');
    
    // Media insert code
    if (isset($_GET['wpcf-fields-media-insert'])
            || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],
                    'wpcf-fields-media-insert=1'))) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/file.php';
        // Add types button
        add_filter('attachment_fields_to_edit',
                'wpcf_fields_file_attachment_fields_to_edit_filter', 10, 2);
        // Add JS
        add_action('admin_head', 'wpcf_fields_file_media_admin_head');
        // Filter media TABs
        add_filter('media_upload_tabs',
                'wpcf_fields_file_media_upload_tabs_filter');
    }
    
    register_post_type('wp-types-group',
            array(
        'public' => false,
        'label' => 'Types Groups',
        'can_export' => false,
            )
    );
    
    add_filter('icl_custom_fields_to_be_copied',
            'wpcf_custom_fields_to_be_copied', 10, 2);

    // WPML editor filters
    add_filter('icl_editor_cf_name', 'wpcf_icl_editor_cf_name_filter');
    add_filter('icl_editor_cf_description',
            'wpcf_icl_editor_cf_description_filter', 10, 2);
    add_filter('icl_editor_cf_style', 'wpcf_icl_editor_cf_style_filter', 10, 2);
}

/**
 * save_post hook.
 * 
 * @param type $post_ID
 * @param type $post 
 */
function wpcf_admin_save_post_hook($post_ID, $post) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
    wpcf_admin_post_save_post_hook($post_ID, $post);
}

/**
 * Triggers post procceses.
 */
function wpcf_admin_post_page_load_hook() {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';

    // Get post
    if (isset($_GET['post'])) {
        $post_id = (int) $_GET['post'];
    } else if (isset($_POST['post_ID'])) {
        $post_id = (int) $_POST['post_ID'];
    } else {
        $post_id = 0;
    }

    // Init processes
    if ($post_id) {
        $post = get_post($post_id);
        if (!empty($post)) {
            wpcf_admin_post_init($post);
        }
    } else {
        wpcf_admin_post_init();
    }
}

/**
 * Initiates/returns specific form.
 * 
 * @staticvar array $wpcf_forms
 * @param type $id
 * @param type $form
 * @return array 
 */
function wpcf_form($id, $form = array()) {
    static $wpcf_forms = array();
    if (isset($wpcf_forms[$id])) {
        return $wpcf_forms[$id];
    }
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/forms.php';
    $new_form = new Enlimbo_Forms_Wpcf();
    $new_form->autoHandle($id, $form);
    $wpcf_forms[$id] = $new_form;
    return $wpcf_forms[$id];
}

/**
 * Renders form elements.
 * 
 * @staticvar string $form
 * @param type $elements
 * @return type 
 */
function wpcf_form_simple($elements) {
    static $form = NULL;
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/forms.php';
    if (is_null($form)) {
        $form = new Enlimbo_Forms_Wpcf();
    }
    return $form->renderElements($elements);
}

/**
 * Validates form elements (simple).
 * 
 * @staticvar string $form
 * @param type $elements
 * @return type 
 */
function wpcf_form_simple_validate(&$elements) {
    static $form = NULL;
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/forms.php';
    if (is_null($form)) {
        $form = new Enlimbo_Forms_Wpcf();
    }
    $form->validate($elements);
    return $form;
}

/**
 * Stores JS validation rules.
 * 
 * @staticvar array $validation
 * @param type $element
 * @return array 
 */
function wpcf_form_add_js_validation($element) {
    static $validation = array();
    if ($element == 'get') {
        $temp = $validation;
        $validation = array();
        return $temp;
    }
    $validation[$element['#id']] = $element;
}

/**
 * Renders JS validation rules.
 * 
 * @return type 
 */
function wpcf_form_render_js_validation($form = '.wpcf-form-validate') {
    $elements = wpcf_form_add_js_validation('get');
    if (empty($elements)) {
        return '';
    }
    echo "\r\n" . '<script type="text/javascript">' . "\r\n" . '/* <![CDATA[ */'
    . "\r\n" . 'jQuery(document).ready(function(){' . "\r\n"
    . 'if (jQuery("' . $form . '").length > 0){' . "\r\n"
    . 'jQuery("' . $form . '").validate({
        errorClass: "wpcf-form-error",
        errorPlacement: function(error, element){
            error.insertBefore(element);
        },
        highlight: function(element, errorClass, validClass) {
            jQuery(element).parents(\'.collapsible\').slideDown();
            jQuery("input#publish").addClass("button-primary-disabled");
            jQuery("input#save-post").addClass("button-disabled");
            jQuery("#save-action .ajax-loading").css("visibility", "hidden");
            jQuery("#publishing-action #ajax-loading").css("visibility", "hidden");
//            jQuery.validator.defaults.highlight(element, errorClass, validClass); // Do not add class to element
		},
        unhighlight: function(element, errorClass, validClass) {
			jQuery("input#publish, input#save-post").removeClass("button-primary-disabled").removeClass("button-disabled");
//            jQuery.validator.defaults.unhighlight(element, errorClass, validClass);
		},
    });' . "\r\n";
    foreach ($elements as $id => $element) {
        if (in_array($element['#type'], array('radios'))) {
            echo 'jQuery(\'input:[name="' . $element['#name'] . '"]\').rules("add", {' . "\r\n";
        } else {
            echo 'jQuery("#' . $id . '").rules("add", {' . "\r\n";
        }
        $rules = array();
        $messages = array();
        foreach ($element['#validate'] as $method => $args) {
            if (!isset($args['value'])) {
                $args['value'] = 'true';
            }
            $rules[] = $method . ': ' . $args['value'];
            if (empty($args['message'])) {
                $args['message'] = wpcf_admin_validation_messages($method);
            }
            // TODO Why is this here?
//            if (!empty($args['message'])) {
//                $messages[] = $method . ': "' . wpcf_translate('field ' . $element['wpcf-id'] . ' validation message ' . $method, $args['message']) . '"';
//            }
        }
        echo implode(',' . "\r\n", $rules);
        if (!empty($messages)) {
            echo ',' . "\r\n" . 'messages: {' . "\r\n"
            . implode(',' . "\r\n", $messages) . "\r\n" . '}';
        }
        echo "\r\n" . '});' . "\r\n";
    }
    echo "\r\n" . '/* ]]> */' . "\r\n" . '}' . "\r\n" . '})' . "\r\n"
    . '</script>' . "\r\n";
}

/**
 * wpcf_custom_fields_to_be_copied
 *
 * Hook the copy custom fields from WPML and remove any of the fields
 * that wpcf will copy.
 */
function wpcf_custom_fields_to_be_copied($copied_fields, $original_post_id) {

    // see if this is one of our fields.
    $groups = wpcf_admin_post_get_post_groups_fields(get_post($original_post_id));

    foreach ($copied_fields as $id => $copied_field) {
        foreach ($groups as $group) {
            foreach ($group['fields'] as $field) {
                if ($copied_field == wpcf_types_get_meta_prefix($field) . $field['slug']) {
                    unset($copied_fields[$id]);
                }
            }
        }
    }
    return $copied_fields;
}

/**
 * Holds validation messages.
 * 
 * @param type $method
 * @return type 
 */
function wpcf_admin_validation_messages($method = false) {
    $messages = array(
        'required' => __('This Field is required', 'wpcf'),
        'email' => __('Please enter a valid email address', 'wpcf'),
        'url' => __('Please enter a valid URL address', 'wpcf'),
        'date' => __('Please enter a valid date', 'wpcf'),
        'digits' => __('Please enter numeric data', 'wpcf'),
        'number' => __('Please enter numeric data', 'wpcf'),
        'alphanumeric' => __('Letters, numbers, spaces or underscores only please',
                'wpcf'),
        'nospecialchars' => __('Letters, numbers, spaces, underscores and dashes only please',
                'wpcf'),
        'rewriteslug' => __('Letters, numbers, slashes, underscores and dashes only please',
                'wpcf')
    );
    if ($method) {
        return isset($messages[$method]) ? $messages[$method] : '';
    }
    return $messages;
}

/**
 * Adds admin notice.
 * 
 * @param type $message
 * @param type $class 
 */
function wpcf_admin_message($message, $class = 'updated') {
    add_action('admin_notices',
            create_function('$a=1, $class=\'' . $class . '\', $message=\''
                    . $message . '\'',
                    '$screen = get_current_screen(); if (!$screen->is_network) echo "<div class=\"message $class\"><p>$message</p></div>";'));
}

/**
 * Shows stored messages.
 */
function wpcf_show_admin_messages() {
    $messages = get_option('wpcf-messages', array());
    $messages_for_user = isset($messages[get_current_user_id()]) ? $messages[get_current_user_id()] : array();
    if (!empty($messages_for_user)) {
        foreach ($messages_for_user as $message) {
            wpcf_admin_message($message['message'], $message['class']);
        }
        unset($messages[get_current_user_id()]);
    }
    update_option('wpcf-messages', $messages);
}

/**
 * Stores admin notices if redirection is performed.
 * 
 * @param type $message
 * @param type $class
 * @return type 
 */
function wpcf_admin_message_store($message, $class = 'updated') {
    $messages = get_option('wpcf-messages', array());
    $messages[get_current_user_id()][md5($message)] = array(
        'message' => $message,
        'class' => $class
    );
    update_option('wpcf-messages', $messages);
}

/**
 * Saves cookie.
 * 
 * @param type $data 
 */
function wpcf_cookies_add($data) {
    if (isset($_COOKIE['wpcf'])) {
        $data = array_merge((array) $_COOKIE['wpcf'], $data);
    }
    setcookie('wpcf', $data, time() + $lifetime, COOKIEPATH, COOKIE_DOMAIN);
}

/**
 * WPML editor filter
 * 
 * @param type $cf_name
 * @return type 
 */
function wpcf_icl_editor_cf_name_filter($cf_name) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if (empty($fields)) {
        return $cf_name;
    }
    $cf_name = substr($cf_name, 6);
    if (strpos($cf_name, WPCF_META_PREFIX) == 0) {
        $cf_name = str_replace(WPCF_META_PREFIX, '', $cf_name);
    }
    if (isset($fields[$cf_name]['name'])) {
        $cf_name = wpcf_translate('field ' . $fields[$cf_name]['id'] . ' name',
                $fields[$cf_name]['name']);
    }
    return $cf_name;
}

/**
 * WPML editor filter
 * 
 * @param type $cf_name
 * @param type $description
 * @return type 
 */
function wpcf_icl_editor_cf_description_filter($description, $cf_name) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if (empty($fields)) {
        return $description;
    }
    $cf_name = substr($cf_name, 6);
    if (strpos($cf_name, WPCF_META_PREFIX) == 0) {
        $cf_name = str_replace(WPCF_META_PREFIX, '', $cf_name);
    }
    if (isset($fields[$cf_name]['description'])) {
        $description = wpcf_translate('field ' . $fields[$cf_name]['id'] . ' description',
                $fields[$cf_name]['description']);
    }

    return $description;
}

/**
 * WPML editor filter
 * 
 * @param type $cf_name
 * @param type $style
 * @return type 
 */
function wpcf_icl_editor_cf_style_filter($style, $cf_name) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if (empty($fields)) {
        return $style;
    }
    $cf_name = substr($cf_name, 6);
    if (strpos($cf_name, WPCF_META_PREFIX) == 0) {
        $cf_name = str_replace(WPCF_META_PREFIX, '', $cf_name);
    }
    if (isset($fields[$cf_name]['type']) && $fields[$cf_name]['type'] == 'textarea') {
        $style = 1;
    }
    if (isset($fields[$cf_name]['type']) && $fields[$cf_name]['type'] == 'wysiwyg') {
        $style = 2;
    }
    return $style;
}

/**
 * Renders page head.
 * 
 * @global type $pagenow
 * @param type $title
 */
function wpcf_admin_ajax_head($title) {
    global $pagenow;
    $hook_suffix = $pagenow;

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
        <head>
            <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
            <title><?php echo $title; ?></title>
            <?php
            if (wpcf_compare_wp_version('3.2.1', '<=')) {
                wp_admin_css('global');
            }
            wp_admin_css();
            wp_admin_css('colors');
            wp_admin_css('ie');
            do_action('admin_enqueue_scripts', $hook_suffix);
            do_action("admin_print_styles-$hook_suffix");
            do_action('admin_print_styles');
            do_action("admin_print_scripts-$hook_suffix");
            do_action('admin_print_scripts');
            // TODO Check if needed
//            do_action("admin_head-$hook_suffix");
//            do_action('admin_head');
            do_action('admin_head_wpcf_ajax');

            ?>
            <style type="text/css">
                html { height: auto; }
            </style>
        </head>
        <body style="padding: 20px;">
            <?php
        }

        /**
         * Renders page footer
         */
        function wpcf_admin_ajax_footer() {
            global $pagenow;
            do_action('admin_footer_wpcf_ajax');
//    do_action('admin_footer', '');
//    do_action('admin_print_footer_scripts');
//    do_action("admin_footer-" . $pagenow);

            ?>
        </body>
    </html>

    <?php
}

/**
 * Gets var from $_SERVER['HTTP_REFERER'].
 * 
 * @param type $var 
 */
function wpcf_admin_get_var_from_referer($var) {
    $value = false;
    if (isset($_SERVER['HTTP_REFERER'])) {
        $parts = explode('?', $_SERVER['HTTP_REFERER']);
        if (!empty($parts[1])) {
            parse_str($parts[1], $vars);
            if (isset($vars[$var])) {
                $value = $vars[$var];
            }
        }
    }
    return $value;
}

/**
 * Adds JS settings.
 * 
 * @staticvar array $settings
 * @param type $id
 * @param type $setting
 * @return string 
 */
function wpcf_admin_add_js_settings($id, $setting = '') {
    static $settings = array();
    $settings['wpcf_nonce_ajax_callback'] = '\'' . wp_create_nonce('execute') . '\'';
    $settings['wpcf_cookiedomain'] = '\'' . $_SERVER['SERVER_NAME'] . '\'';
    $settings['wpcf_cookiepath'] = '\'' . COOKIEPATH . '\'';
    if ($id == 'get') {
        $temp = $settings;
        $settings = array();
        return $temp;
    }
    $settings[$id] = $setting;
}

/**
 * Renders JS settings.
 * 
 * @return type 
 */
function wpcf_admin_render_js_settings() {
    $settings = wpcf_admin_add_js_settings('get');
    if (empty($settings)) {
        return '';
    }

    ?>
    <script type="text/javascript">
        //<![CDATA[
    <?php
    foreach ($settings as $id => $setting) {
        echo 'var ' . $id . ' = ' . $setting . ';' . "\r\n";
    }

    ?>
        //]]>
    </script>
    <?php
}

/**
 * wpcf_get_fields
 *
 * returns the fields handled by types
 *
 */

function wpcf_get_post_meta_field_names() {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    
    $field_names = array();
    foreach($fields as $field) {
        $field_names[] = wpcf_types_get_meta_prefix($field) . $field['slug'];
    }
    
    return $field_names;
}


/**
 * Forces 'Insert into post' link when called from our WYSIWYG.
 * 
 * @param array $args
 * @return boolean 
 */
function wpcf_get_media_item_args_filter($args) {
    if (strpos($_SERVER['SCRIPT_NAME'], '/media-upload.php') === false) {
        return $args;
    }
    if (!empty($_COOKIE['wpcfActiveEditor'])
            && strpos($_COOKIE['wpcfActiveEditor'], 'wpcf-wysiwyg-') !== false) {
        $args['send'] = true;
    }
    return $args;
}