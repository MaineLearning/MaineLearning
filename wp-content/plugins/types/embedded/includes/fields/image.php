<?php
add_filter('wpcf_fields_type_image_value_get', 'wpcf_fields_image_value_filter');
add_filter('wpcf_fields_type_image_value_save', 'wpcf_fields_image_value_filter');
add_filter('upload_dir', 'wpcf_fields_image_uploads_realpath');

/**
 * Register data (called automatically).
 * @return type 
 */
function wpcf_fields_image() {
    return array(
        'id' => 'wpcf-image',
        'title' => __('Image', 'wpcf'),
        'description' => __('Image', 'wpcf'),
        'validate' => array('required'),
        'meta_box_js' => array(
            'wpcf-jquery-fields-file' => array(
                'inline' => 'wpcf_fields_file_meta_box_js_inline',
            ),
            'wpcf-jquery-fields-image' => array(
                'inline' => 'wpcf_fields_image_meta_box_js_inline',
            ),
        ),
        'inherited_field_type' => 'file',
    );
}

/**
 * Renders inline JS.
 */
function wpcf_fields_image_meta_box_js_inline() {
    global $post;

    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function(){
            wpcf_formfield = false;
            jQuery('.wpcf-fields-image-upload-link').live('click', function() {
                wpcf_formfield = '#'+jQuery(this).attr('id')+'-holder';
                tb_show('<?php
    echo esc_js(__('Upload image', 'wpcf'));

    ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&wpcf-fields-media-insert=1&TB_iframe=true');
                return false;
            }); 
        });
        //]]>
    </script>
    <?php
}

/**
 * Editor callback form.
 */
function wpcf_fields_image_editor_callback() {
    wp_enqueue_style('wpcf-fields-image',
            WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION);
    wp_enqueue_script('jquery');

    // Get field
    $field = wpcf_admin_fields_get_field($_GET['field_id']);
    if (empty($field)) {
        _e('Wrong field specified', 'wpcf');
        die();
    }

    // Get post_ID
    $post_ID = false;
    if (isset($_POST['post_id'])) {
        $post_ID = intval($_POST['post_id']);
    } else {
        $http_referer = explode('?', $_SERVER['HTTP_REFERER']);
        if (isset($http_referer[1])) {
            parse_str($http_referer[1], $http_referer);
            if (isset($http_referer['post'])) {
                $post_ID = $http_referer['post'];
            }
        }
    }

    // Get attachment
    $image = false;
    $attachment_id = false;
    if ($post_ID) {
        $image = get_post_meta($post_ID,
                wpcf_types_get_meta_prefix($field) . $field['slug'], true);
        if (!empty($image)) {
            // Get attachment by guid
            global $wpdb;
            $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts}
    WHERE post_type = 'attachment' AND guid=%s",
                            $image));
        }
    }

    // Get post type
    $post_type = '';
    if ($post_ID) {
        $post_type = get_post_type($post_ID);
    } else {
        $http_referer = explode('?', $_SERVER['HTTP_REFERER']);
        parse_str($http_referer[1], $http_referer);
        if (isset($http_referer['post_type'])) {
            $post_type = $http_referer['post_type'];
        }
    }

    $image_data = wpcf_fields_image_get_data($image);

    if (!in_array($post_type, array('view', 'view-template'))) {
        // We must ignore errors here and treat image as outsider
        if (!empty($image_data['error'])) {
            $image_data['is_outsider'] = 1;
            $image_data['is_attachment'] = 0;
        }
    } else {
        if (!empty($image_data['error'])) {
            $image_data['is_outsider'] = 0;
            $image_data['is_attachment'] = 0;
        }
    }

    $last_settings = wpcf_admin_fields_get_field_last_settings($_GET['field_id']);

    $form = array();
    $form['#form']['callback'] = 'wpcf_fields_image_editor_submit';
    if ($attachment_id) {
        $form['preview'] = array(
            '#type' => 'markup',
            '#markup' => '<div style="position:absolute; margin-left:300px;">'
            . wp_get_attachment_image($attachment_id, 'thumbnail') . '</div>',
        );
    }
    $alt = '';
    $title = '';
    if ($attachment_id) {
        $alt = trim(strip_tags(get_post_meta($attachment_id,
                                '_wp_attachment_image_alt', true)));
        $attachment_post = get_post($attachment_id);
        if (!empty($attachment_post)) {
            $title = trim(strip_tags($attachment_post->post_title));
        } else if (!empty($alt)) {
            $title = $alt;
        }
        if (empty($alt)) {
            $alt = $title;
        }
    }
    $form['title'] = array(
        '#type' => 'textfield',
        '#title' => __('Image title', 'wpcf'),
        '#description' => __('Title text for the image, e.g. &#8220;The Mona Lisa&#8221;',
                'wpcf'),
        '#name' => 'title',
        '#value' => isset($last_settings['title']) ? $last_settings['title'] : $title,
    );
    $form['alt'] = array(
        '#type' => 'textfield',
        '#title' => __('Alternate Text', 'wpcf'),
        '#description' => __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;',
                'wpcf'),
        '#name' => 'alt',
        '#value' => isset($last_settings['alt']) ? $last_settings['alt'] : $alt,
    );
    $form['alignment'] = array(
        '#type' => 'radios',
        '#title' => __('Alignment', 'wpcf'),
        '#name' => 'alignment',
        '#default_value' => isset($last_settings['alignment']) ? $last_settings['alignment'] : 'none',
        '#options' => array(
            __('None', 'wpcf') => 'none',
            __('Left', 'wpcf') => 'left',
            __('Center', 'wpcf') => 'center',
            __('Right', 'wpcf') => 'right',
        ),
    );
    $form['class'] = array(
        '#type' => 'textfield',
        '#title' => __('Class', 'wpcf'),
        '#name' => 'class',
        '#value' => isset($last_settings['class']) ? $last_settings['class'] : '',
    );
    $form['style'] = array(
        '#type' => 'textfield',
        '#title' => __('Style', 'wpcf'),
        '#name' => 'style',
        '#value' => isset($last_settings['style']) ? $last_settings['style'] : '',
    );

    $attributes_outsider = array();
    $attributes_attachment = array();
    $fetch_remote = (bool) wpcf_get_settings('images_remote');
    if ($image_data['is_outsider'] && !$fetch_remote) {
        $form['notice'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="message error" style="margin:0 0 20px 0;"><p>'
            . sprintf(__('Remote image resize is currently disabled, so Types will only resize images that you upload. To change, go to the %sTypes settings page%s.',
                            'wpcf'),
                    '<a href="' . admin_url('admin.php?page=wpcf-custom-settings#types-image-settings') . '" target="_blank">',
                    '</a>')
            . '</p></div>',
        );
        $attributes_outsider = array('disabled' => 'disabled');
        $attributes_attachment = array('disabled' => 'disabled');
    }

    if ($image_data['is_attachment']) {
        $default_value = isset($last_settings['image-size']) ? $last_settings['image-size'] : 'thumbnail';
    } else if (!$image_data['is_outsider']) {
        $default_value = 'wpcf-custom';
    } else {
        $default_value = 'thumbnail';
    }
    $form['size'] = array(
        '#type' => 'radios',
        '#title' => __('Pre-defined sizes', 'wpcf'),
        '#name' => 'image-size',
        '#default_value' => $default_value,
        '#options' => array(
            'thumbnail' => array('#title' => __('Thumbnail', 'wpcf'), '#value' => 'thumbnail', '#attributes' => $attributes_attachment),
            'medium' => array('#title' => __('Medium', 'wpcf'), '#value' => 'medium', '#attributes' => $attributes_attachment),
            'large' => array('#title' => __('Large', 'wpcf'), '#value' => 'large', '#attributes' => $attributes_attachment),
            'full' => array('#title' => __('Full Size', 'wpcf'), '#value' => 'full', '#attributes' => $attributes_attachment),
            'wpcf-custom' => array('#title' => __('Custom size', 'wpcf'), '#value' => 'wpcf-custom', '#attributes' => $attributes_outsider),
        ),
    );
    $form['toggle-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-toggle" style="display:none;">',
    );
    $form['width'] = array(
        '#type' => 'textfield',
        '#title' => __('Width', 'wpcf'),
        '#description' => __('Specify custom width', 'wpcf'),
        '#name' => 'width',
        '#value' => isset($last_settings['width']) ? $last_settings['width'] : '',
        '#suffix' => '&nbsp;px',
        '#attributes' => $attributes_outsider,
    );
    $form['height'] = array(
        '#type' => 'textfield',
        '#title' => __('Height', 'wpcf'),
        '#description' => __('Specify custom height', 'wpcf'),
        '#name' => 'height',
        '#value' => isset($last_settings['height']) ? $last_settings['height'] : '',
        '#suffix' => '&nbsp;px',
        '#attributes' => $attributes_outsider,
    );
    $form['proportional'] = array(
        '#type' => 'checkbox',
        '#title' => __('Keep proportional', 'wpcf'),
        '#name' => 'proportional',
        '#default_value' => 1,
        '#attributes' => $attributes_outsider,
    );
    $form['toggle-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
        '#attributes' => $attributes_outsider,
    );
    if ($post_ID) {
        $form['post_id'] = array(
            '#type' => 'hidden',
            '#name' => 'post_id',
            '#value' => $post_ID,
        );
    }
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => __('Insert shortcode', 'wpcf'),
        '#attributes' => array('class' => 'button-primary'),
    );
    $f = wpcf_form('wpcf-form', $form);
    wpcf_admin_ajax_head('Insert email', 'wpcf');
    echo '<form method="post" action="">';
    echo $f->renderForm();
    echo '</form>';

    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function(){
            jQuery('input[name="image-size"]').change(function(){
                if (jQuery(this).val() == 'wpcf-custom') {
                    jQuery('#wpcf-toggle').slideDown();
                } else {
                    jQuery('#wpcf-toggle').slideUp();
                }
            });
            if (jQuery('input[name="image-size"]:checked').val() == 'wpcf-custom') {
                jQuery('#wpcf-toggle').show();
            }
        });
        //]]>
    </script>
    <?php
    wpcf_admin_ajax_footer();
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_image_editor_submit() {
    $add = '';
    if (!empty($_POST['alt'])) {
        $add .= ' alt="' . strval($_POST['alt']) . '"';
    }
    if (!empty($_POST['title'])) {
        $add .= ' title="' . strval($_POST['title']) . '"';
    }
    $size = !empty($_POST['image-size']) ? $_POST['image-size'] : false;
    if ($size == 'wpcf-custom') {
        if (!empty($_POST['width'])) {
            $add .= ' width="' . intval($_POST['width']) . '"';
        }
        if (!empty($_POST['height'])) {
            $add .= ' height="' . intval($_POST['height']) . '"';
        }
        if (!empty($_POST['proportional'])) {
            $add .= ' proportional="true"';
        }
    } else if (!empty($size)) {
        $add .= ' size="' . $size . '"';
    }
    if (!empty($_POST['alignment'])) {
        $add .= ' align="' . $_POST['alignment'] . '"';
    }
    if (!empty($_POST['class'])) {
        $add .= ' class="' . $_POST['class'] . '"';
    }
    if (!empty($_POST['style'])) {
        $add .= ' style="' . $_POST['style'] . '"';
    }
    $field = wpcf_admin_fields_get_field($_GET['field_id']);
    if (!empty($field)) {
        $shortcode = wpcf_fields_get_shortcode($field, $add);
        wpcf_admin_fields_save_field_last_settings($_GET['field_id'], $_POST);
        echo editor_admin_popup_insert_shortcode_js($shortcode);
        die();
    }
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_image_view($params) {
    $output = '';
    $alt = false;
    $title = false;
    $class = array();
    $style = array();

    // Get image data
    $image_data = wpcf_fields_image_get_data($params['field_value']);

    //print_r($image_data);
    //print_r($params);
    // Display error to admin only
    if (!empty($image_data['error'])) {
        if (current_user_can('administrator')) {
            return '<div style="padding:10px;background-color:Red;color:#FFFFFF;">'
                    . 'Types: ' . $image_data['error'] . '</div>';
        }
        return $params['field_value'];
    }

    // Set alt
    if (isset($params['alt'])) {
        $alt = $params['alt'];
    }

    // Set title
    if (isset($params['title'])) {
        $title = $params['title'];
    }

    // Set attachment class
    if (!empty($params['size'])) {
        $class[] = 'attachment-' . $params['size'];
    }

    // Set align class
    if (!empty($params['align']) && $params['align'] != 'none') {
        $class[] = 'align' . $params['align'];
    }

    if (!empty($params['class'])) {
        $class[] = $params['class'];
    }
    if (!empty($params['style'])) {
        $style[] = $params['style'];
    }

    // Pre-configured size (use WP function)
    if ($image_data['is_attachment'] && !empty($params['size'])) {
        //print_r('is_attachment');
        if (isset($params['url']) && $params['url'] == 'true') {
            //print_r('is_url');
            $image_url = wp_get_attachment_image_src($image_data['is_attachment'],
                    $params['size']);
            if (!empty($image_url[0])) {
                $output = $image_url[0];
            } else {
                $output = $params['field_value'];
            }
        } else {
            //print_r('is_not_url');
            $output = wp_get_attachment_image($image_data['is_attachment'],
                    $params['size'], false,
                    array(
                'class' => implode(' ', $class),
                'style' => implode(' ', $style),
                'alt' => $alt,
                'title' => $title
                    )
            );
        }
    } else { // Custom size
        //print_r('custom_size');
        $width = !empty($params['width']) ? intval($params['width']) : null;
        $height = !empty($params['height']) ? intval($params['height']) : null;
        $crop = (!empty($params['proportional']) && $params['proportional'] == 'true') ? false : true;


        //////////////////////////
        // If width and height are not set then check the size parameter.
        // This handles the case when the image is not an attachment.
        if (!$width && !$height && !empty($params['size'])) {
            //print_r('no_width_no_height_and_size');
            switch ($params['size']) {
                case 'thumbnail':
                    $width = get_option('thumbnail_size_w');
                    $height = get_option('thumbnail_size_h');
                    if (empty($params['proportional'])) {
                        $crop = get_option('thumbnail_crop');
                    }
                    break;

                case 'medium':
                    $width = get_option('medium_size_w');
                    $height = get_option('medium_size_h');
                    break;

                case 'large':
                    $width = get_option('large_size_w');
                    $height = get_option('large_size_h');
                    break;
            }
        }


        // Check if image is outsider
        if (!$image_data['is_outsider']) {
            //print_r('Not is_outsider');
            $resized_image = wpcf_fields_image_resize_image(
                    $params['field_value'], $width, $height, 'relpath', false,
                    $crop
            );
            if (!$resized_image) {
                //print_r('Not resized image');
                $resized_image = $params['field_value'];
            } else {
                //print_r('resized image add to lib');
                // Add to library
                $image_abspath = wpcf_fields_image_resize_image(
                        $params['field_value'], $width, $height, 'abspath',
                        false, $crop
                );
                $add_to_library = wpcf_get_settings('add_resized_images_to_library');
                if ($add_to_library) {
                    //print_r('add to lib');
                    global $wpdb;
                    $attachment_exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts}
    WHERE post_type = 'attachment' AND guid=%s",
                                    $resized_image));
                    if (empty($attachment_exists)) {
                        // Add as attachment
                        $wp_filetype = wp_check_filetype(basename($image_abspath),
                                null);
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => preg_replace('/\.[^.]+$/', '',
                                    basename($image_abspath)),
                            'post_content' => '',
                            'post_status' => 'inherit',
                            'guid' => $resized_image,
                        );
                        global $post;
                        $attach_id = wp_insert_attachment($attachment,
                                $image_abspath, $post->ID);
                        // you must first include the image.php file
                        // for the function wp_generate_attachment_metadata() to work
                        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata($attach_id,
                                $image_abspath);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                    }
                }
            }
        } else {
            //print_r('is_outsider');
            $resized_image = $params['field_value'];
        }
        if (isset($params['url']) && $params['url'] == 'true') {
            //print_r('return');
            return $resized_image;
        }
        //print_r('output');
        $output = '<img alt="';
        $output .= $alt !== false ? $alt : $resized_image;
        $output .= '" title="';
        $output .= $title !== false ? $title : $resized_image;
        $output .= '"';
        $output .=!empty($params['onload']) ? ' onload="' . $params['onload'] . '"' : '';
        $output .=!empty($class) ? ' class="' . implode(' ', $class) . '"' : '';
        $output .=!empty($style) ? ' style="' . implode(' ', $style) . '"' : '';
        $output .= ' src="' . $resized_image . '" />';
    }

    return $output;
}

/**
 * Resizes image using WP image_resize() function.
 *
 * Caches return data if called more than one time in one pass.
 *
 * @staticvar array $cached Caches calls in one pass
 * @param <type> $url_path Full URL path (works only with images on same domain)
 * @param <type> $width
 * @param <type> $height
 * @param <type> $refresh Set to true if you want image re-created or not cached
 * @param <type> $crop Set to true if you want apspect ratio to be preserved
 * @param string $suffix Optional (default 'wpcf_$widthxheight)
 * @param <type> $dest_path Optional (defaults to original image)
 * @param <type> $quality
 * @return array
 */
function wpcf_fields_image_resize_image($url_path, $width = 300, $height = 200,
        $return = 'relpath', $refresh = FALSE, $crop = TRUE, $suffix = '',
        $dest_path = NULL, $quality = 75) {

    if (empty($url_path)) {
        //print_r('return url path');
        return $url_path;
    }

    // Get image data
    $image_data = wpcf_fields_image_get_data($url_path);

    if (empty($image_data['fullabspath']) || !empty($image_data['error'])) {
        //print_r('return url path no full or error');
        return $url_path;
    }

    // Set cache
    static $cached = array();
    $cache_key = md5($url_path . $width . $height . intval($crop) . $suffix . $dest_path);

    // Check if cached in this call
    if (!$refresh && isset($cached[$cache_key][$return])) {
        //print_r('return cached');
        return $cached[$cache_key][$return];
    }

    $width = intval($width);
    $height = intval($height);

    // Get size of new file
    $size = @getimagesize($image_data['fullabspath']);
    if (!$size) {
        //print_r('not size');
        return $url_path;
    }
    list($orig_w, $orig_h, $orig_type) = $size;
    $dims = image_resize_dimensions($orig_w, $orig_h, $width, $height, $crop);
    if (!$dims) {
        //print_r('not dims');
        return $url_path;
    }
    list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

    // Set suffix
    if (empty($suffix)) {
        $suffix = 'wpcf_' . $dst_w . 'x' . $dst_h;
    } else {
        $suffix .= '_wpcf_' . $dst_w . 'x' . $dst_h;
    }

    $image_data['extension'] = in_array(strtolower($image_data['extension']),
                    array('gif', 'png', 'jpeg')) ? $image_data['extension'] : 'jpg';

    $image_relpath = $image_data['relpath'] . '/' . $image_data['image_name'] . '-'
            . $suffix . '.' . $image_data['extension'];
    $image_abspath = $image_data['abspath'] . DIRECTORY_SEPARATOR
            . $image_data['image_name'] . '-' . $suffix . '.'
            . $image_data['extension'];

    // Check if already resized
    if (!$refresh && file_exists($image_abspath)) {
        //print_r('file exists');
        // Cache it
        $cached[$cache_key]['relpath'] = $image_relpath;
        $cached[$cache_key]['abspath'] = $image_abspath;
        return $return == 'relpath' ? $image_relpath : $image_abspath;
    }

    // If original file don't exists
    if (!file_exists($image_data['fullabspath'])) {
        //print_r('not file exists');
        return $url_path;
    }

    // Resize image
    $resized_image = @wpcf_image_resize(
                    $image_data['fullabspath'], $width, $height, $crop, $suffix,
                    $dest_path, $quality
    );

    // Check if error
    if (is_wp_error($resized_image)) {
        //print_r('resized wp error');
        //print_r($resized_image);
        return $url_path;
    }

    $image_abspath = $resized_image;

    // Cache it
    $cached[$cache_key]['relpath'] = $image_relpath;
    $cached[$cache_key]['abspath'] = $image_abspath;

    return $return == 'relpath' ? $image_relpath : $image_abspath;
}

/**
 * Gets all necessary data for processed image.
 * 
 * @global type $wpdb
 * @param type $image
 * @return type 
 */
function wpcf_fields_image_get_data($image) {

    global $current_user;

    // Check if already cached
    static $cache = array();
    if (isset($cache[md5($image)])) {
        return $cache[md5($image)];
    }

    // Defaults
    $data = array(
        'image' => basename($image),
        'image_name' => '',
        'extension' => '',
        'abspath' => '',
        'relpath' => dirname($image),
        'fullabspath' => '',
        'fullrelpath' => $image,
        'is_outsider' => 1,
        'is_in_upload_path' => 0,
        'is_attachment' => 0,
        'error' => '',
    );

    // Strip GET vars
    $image = strtok($image, '?');

    // Basic URL check
    if (strpos($image, 'http') != 0) {
        return array('error' => sprintf(__('Image %s not valid', 'wpcf'), $image));
    }
    // Extension check
    $data['extension'] = pathinfo($image, PATHINFO_EXTENSION);
    if (!in_array(strtolower($data['extension']),
                    array('jpg', 'jpeg', 'gif', 'png'))) {
        return array('error' => sprintf(__('Image %s not valid', 'wpcf'), $image));
    }
    // Parse URL
    $parsed = parse_url($image);
    $parsed_wp = parse_url(get_site_url());
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i',
                    $parsed['host'], $regs)) {
        $parsed['domain'] = $regs['domain'];
    } else {
        $parsed['domain'] = $parsed['host'];
    }
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i',
                    $parsed_wp['host'], $regs)) {
        $parsed_wp['domain'] = $regs['domain'];
    } else {
        $parsed_wp['domain'] = $parsed_wp['host'];
    }

    // Check if it's on same domain
    $data['is_outsider'] = $parsed['domain'] == $parsed_wp['domain'] ? 0 : 1;

    if (!$data['is_outsider']) {
        // Check if it's in upload path
        $upload_dir = wp_upload_dir();
        $upload_dir_parsed = parse_url($upload_dir['baseurl']);

        // Determine if in upload path and calculate abspath
        // 
        // This works for regular installation and main blog on multisite
        if ((!is_multisite() || is_main_site())
                && strpos($parsed['path'], $upload_dir_parsed['path']) === 0) {
            $data['is_in_upload_path'] = 1;
            if (!empty($parsed_wp['path'])) {
                $data['abspath'] = dirname(str_replace($parsed_wp['path'] . '/',
                                ABSPATH, $parsed['path']));
            } else {
                $data['abspath'] = ABSPATH . dirname($parsed['path']);
            }
            $data['fullabspath'] = $data['abspath'] . DIRECTORY_SEPARATOR . basename($image);

            //
            // Check Multisite
        } else if (is_multisite() && !is_main_site()) {
            if (strpos($parsed['path'], $upload_dir_parsed['path']) === 0) {
                $data['is_in_upload_path'] = 1;
            }
            $multisite_parsed = explode('/files/', $parsed['path']);
            if (isset($multisite_parsed[1])) {
                $data['is_in_upload_path'] = 1;
                $data['abspath'] = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . dirname($multisite_parsed[1]);
                $data['fullabspath'] = $data['abspath'] . DIRECTORY_SEPARATOR . basename($image);
            }
        }

        // Manual upload
        if (empty($data['abspath'])) {
            if (!empty($parsed_wp['path'])) {
                $data['abspath'] = dirname(str_replace($parsed_wp['path'] . '/',
                                ABSPATH, $parsed['path']));
            } else {
                $data['abspath'] = ABSPATH . dirname($parsed['path']);
            }
            $data['fullabspath'] = $data['abspath'] . DIRECTORY_SEPARATOR . basename($image);
        }

        // Check if it's attachment
        global $wpdb;
        $data['is_attachment'] = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts}
    WHERE post_type = 'attachment' AND guid=%s",
                        $image));
    }

    // Set remote if enabled
    if ($data['is_outsider'] && wpcf_get_settings('images_remote')) {
        $remote = wpcf_fields_image_get_remote($image);
        if (!is_wp_error($remote)) {
            $data['is_outsider'] = 0;
            $data['is_in_upload_path'] = 1;
            $data['abspath'] = dirname($remote['abspath']);
            $data['fullabspath'] = $remote['abspath'];
            $data['image'] = $remote['relpath'];
            $data['relpath'] = dirname($remote['relpath']);
            $data['fullrelpath'] = $remote['relpath'];
        }
    }

    // Set rest of data
    $data['image_name'] = basename($data['image'], '.' . $data['extension']);
    $abspath_realpath = realpath($data['abspath']);
    $data['abspath'] = $abspath_realpath ? $abspath_realpath : $data['abspath'];
    $fullabspath_realpath = realpath($data['fullabspath']);
    $data['fullabspath'] = $fullabspath_realpath ? $fullabspath_realpath : $data['fullabspath'];
    //$user = wp_get_current_user();
    if (isset($current_user->user_email) && $current_user->user_email == 'jocics@gmail.comrr') {
        echo '<pre>';
        print_r($parsed);
        print_r($parsed_wp);
        print_r($upload_dir);
        print_r($upload_dir_parsed);
        print_r($data);
        die();
    }
    // Cache it
    $cache[md5($data['image'])] = $data;

    return $data;
}

/**
 * Strips GET vars from value.
 * 
 * @param type $value
 * @return type 
 */
function wpcf_fields_image_value_filter($value) {
    return strtok($value, '?');
}

/**
 * Gets cache directory.
 * 
 * @return \WP_Error 
 */
function wpcf_fields_image_get_cache_directory($suppress_filters = false) {
    $wp_upload_dir = wp_upload_dir();
    if (!empty($wp_upload_dir['error'])) {
        return new WP_Error('wpcf_image_cache_dir', $wp_upload_dir['error']);
    } else {
        $cache_dir = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'types_image_cache';
    }
    if (!$suppress_filters) {
        $cache_dir = apply_filters('types_image_cache_dir', $cache_dir);
        if (!wp_mkdir_p($cache_dir)) {
            return new WP_Error('wpcf_image_cache_dir', sprintf(__('Image cache directory %s could not be created',
                                            'wpcf'),
                                    '<strong>' . $cache_dir . '</strong>'));
        }
    }
    return $cache_dir;
}

function wpcf_image_http_request_timeout($timeout) {
    return 20;
}

/**
 * Fetches remote images.
 * 
 * @param type $url
 * @return \WP_Error 
 */
function wpcf_fields_image_get_remote($url) {

    $refresh = false;

    // Set directory
    $cache_dir = wpcf_fields_image_get_cache_directory();
    if (is_wp_error($cache_dir)) {
        return $cache_dir;
    }

    // Validate image
    $extension = pathinfo($url, PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), array('jpg', 'jpeg', 'gif', 'png'))) {
        return new WP_Error('wpcf_image_cache_not_valid', sprintf(__('Image %s not valid',
                                        'wpcf'), $url));
    }

    $image = $cache_dir . DIRECTORY_SEPARATOR . md5($url) . '.' . $extension;

    // Refresh if necessary
    $refresh_time = intval(wpcf_get_settings('images_remote_cache_time'));
    if ($refresh_time != 0 && file_exists($image)) {
        $time_modified = filemtime($image);
        if (time() - $time_modified > $refresh_time * 60 * 60) {
            $refresh = true;
            $files = glob($cache_dir . DIRECTORY_SEPARATOR . md5($url) . "-*");
            if ($files) {
                foreach ($files as $filename) {
                    @unlink($filename);
                }
            }
        }
    }

    // Check if image is fetched
    if ($refresh || !file_exists($image)) {

        // fetch the remote url and write it to the placeholder file
        add_filter('http_request_timeout', 'wpcf_image_http_request_timeout',
                10, 1);
        $resp = wp_remote_get($url);
        remove_filter('http_request_timeout', 'wpcf_image_http_request_timeout',
                10, 1);
        // make sure the fetch was successful
        if ($resp['response']['code'] != '200') {
            return new WP_Error('wpcf_image_cache_file_error', sprintf(__('Remote server returned error response %1$d %2$s',
                                            'wpcf'),
                                    esc_html($resp['response']),
                                    get_status_header_desc($resp['response'])));
        }
        if (strlen($resp['body']) != $resp['headers']['content-length']) {
            return new WP_Error('wpcf_image_cache_file_error', __('Remote file is incorrect size',
                                    'wpcf'));
        }

        $out_fp = fopen($image, 'w');
        if (!$out_fp) {
            return new WP_Error('wpcf_image_cache_file_error', __('Could not create cache file',
                                    'wpcf'));
        }

        fwrite($out_fp, $resp['body']);
        fclose($out_fp);

        $max_size = (int) apply_filters('import_attachment_size_limit', 0);
        $filesize = filesize($image);
        if (!empty($max_size) && $filesize > $max_size) {
            @unlink($image);
            return new WP_Error('wpcf_image_cache_file_error', sprintf(__('Remote file is too large, limit is %s',
                                            'wpcf'), size_format($max_size)));
        }
    }

    return array(
        'abspath' => $image,
        'relpath' => icl_get_file_relpath($image) . '/' . basename($image)
    );
}

/**
 * Clears remote image cache.
 * 
 * @param type $action 
 */
function wpcf_fields_image_clear_cache($cache_dir = null, $action = 'outdated') {
    if (is_null($cache_dir)) {
        $cache_dir = wpcf_fields_image_get_cache_directory();
    }
    $refresh_time = intval(wpcf_get_settings('images_remote_cache_time'));
    if ($refresh_time == 0 && $action != 'all') {
        return true;
    }
    foreach (glob($cache_dir . DIRECTORY_SEPARATOR . "*") as $filename) {
        if ($action == 'all') {
            @unlink($filename);
        } else {
            $time_modified = filemtime($filename);
            if (time() - $time_modified > $refresh_time * 60 * 60) {
                @unlink($filename);
                // Clear resized images
                $path = pathinfo($filename);
                foreach (glob($path['dirname'] . DIRECTORY_SEPARATOR . $path['filename'] . "-*") as $resized) {
                    @unlink($resized);
                }
            }
        }
    }
}

/**
 * Filters upload paths (to fix Windows issues).
 * 
 * @param type $args
 * @return type
 */
function wpcf_fields_image_uploads_realpath($args) {
    $fixes = array('path', 'subdir', 'basedir');
    foreach ($fixes as $fix) {
        if (isset($args[$fix])) {
            $args[$fix] = realpath($args[$fix]);
        }
    }
    return $args;
}

/**
 * i18n friendly version of basename(), copy from wp-includes/formatting.php to solve bug with windows
 *
 * @since 3.1.0
 *
 * @param string $path A path.
 * @param string $suffix If the filename ends in suffix this will also be cut off.
 * @return string
 */
function wpcf_basename( $path, $suffix = '' ) {
    return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) ); 
}

/**
 * Copy from wp-includes/media.php
 * Scale down an image to fit a particular size and save a new copy of the image.
 *
 * The PNG transparency will be preserved using the function, as well as the
 * image type. If the file going in is PNG, then the resized image is going to
 * be PNG. The only supported image types are PNG, GIF, and JPEG.
 *
 * Some functionality requires API to exist, so some PHP version may lose out
 * support. This is not the fault of WordPress (where functionality is
 * downgraded, not actual defects), but of your PHP version.
 *
 * @since 2.5.0
 *
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed WP_Error on failure. String with new destination path.
 */
function wpcf_image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

	$image = wp_load_image( $file );
	if ( !is_resource( $image ) )
		return new WP_Error( 'error_loading_image', $image, $file );

	$size = @getimagesize( $file );
	if ( !$size )
		return new WP_Error('invalid_image', __('Could not read image size'), $file);
	list($orig_w, $orig_h, $orig_type) = $size;

	$dims = image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);
	if ( !$dims )
		return new WP_Error( 'error_getting_dimensions', __('Could not calculate resized image dimensions') );
	list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

	$newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );

	imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

	// convert from full colors to index colors, like original PNG.
	if ( IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor( $image ) )
		imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

	// we don't need the original in memory anymore
	imagedestroy( $image );

	// $suffix will be appended to the destination filename, just before the extension
	if ( !$suffix )
		$suffix = "{$dst_w}x{$dst_h}";

	$info = pathinfo($file);
	$dir = $info['dirname'];
	$ext = $info['extension'];
	$name = wpcf_basename($file, ".$ext"); // use fix here for windows

	if ( !is_null($dest_path) and $_dest_path = realpath($dest_path) )
		$dir = $_dest_path;
	$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

	if ( IMAGETYPE_GIF == $orig_type ) {
		if ( !imagegif( $newimage, $destfilename ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} elseif ( IMAGETYPE_PNG == $orig_type ) {
		if ( !imagepng( $newimage, $destfilename ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} else {
		// all other formats are converted to jpg
		if ( 'jpg' != $ext && 'jpeg' != $ext )
			$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
		if ( !imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	}

	imagedestroy( $newimage );

	// Set correct file permissions
	$stat = stat( dirname( $destfilename ));
	$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
	@ chmod( $destfilename, $perms );

	return $destfilename;
}
