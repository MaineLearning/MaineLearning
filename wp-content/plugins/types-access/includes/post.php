<?php
/*
 * Post functions.
 */

add_action('add_meta_boxes', 'wpcf_access_post_add_meta_boxes');
add_action('save_post', 'wpcf_access_post_save');
add_action('load-post.php', 'wpcf_access_post_init');
add_action('load-post-new.php', 'wpcf_access_post_init');
add_action('load-post.php', 'wpcf_access_admin_post_page_load_hook');
add_action('load-post-new.php', 'wpcf_access_admin_post_page_load_hook');

/**
 * Init function. 
 */
function wpcf_access_post_init() {
    wp_enqueue_script('wpcf-access', WPCF_ACCESS_RELPATH . '/js/basic.js',
            array('jquery'));
    wp_enqueue_style('wpcf-access-wpcf',
            WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION);
    wp_enqueue_style('wpcf-access', WPCF_ACCESS_RELPATH . '/css/basic.css',
            array(), WPCF_VERSION);
    wp_enqueue_script('types-suggest', WPCF_ACCESS_RELPATH . '/js/suggest.js',
            array(), WPCF_ACCESS_VERSION);
    wp_enqueue_style('types-suggest', WPCF_ACCESS_RELPATH . '/css/suggest.css',
            array(), WPCF_ACCESS_VERSION);
    add_thickbox();
}

/**
 * Registers meta boxes.
 * 
 * @global type $post 
 */
function wpcf_access_post_add_meta_boxes() {
    global $post;
    $areas = array();
    $areas = apply_filters('types-access-show-ui-area', $areas);
    foreach ($areas as $area) {
        require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
        add_action('admin_footer', 'wpcf_access_suggest_js');
        // Add meta boxes
        add_meta_box('wpcf-access-' . $area['id'], $area['name'],
                'wpcf_access_post_meta_box', $post->post_type, 'advanced',
                'high', $area);
    }
}

/**
 * Renders meta boxes.
 * 
 * @param type $post
 * @param type $args 
 */
function wpcf_access_post_meta_box($post, $args) {
    $meta = get_post_meta($post->ID, '_types_access', true);
    $roles = wpcf_get_editable_roles();
    $area = $args['args'];
    $output = '';
    $groups = array();
    $groups = apply_filters('types-access-show-ui-group', $groups, $area['id']);
    foreach ($groups as $group) {
        $output .= '<div class="wpcf-access-type-item">';
        $output .= '<div class="wpcf-access-mode">';
        $caps = array();
        $caps = apply_filters('types-access-show-ui-cap', $caps, $area['id'],
                $group['id']);
        $saved_data = array();
        foreach ($caps as $cap_slug => $cap) {
            if (isset($cap['default_role'])) {
                $caps[$cap_slug]['role'] = $cap['role'] = $cap['default_role'];
            }
            $saved_data[$cap['cap_id']] =
                        is_array($meta) && isset($meta[$area['id']][$group['id']]['permissions'][$cap['cap_id']]) ?
                        $meta[$area['id']][$group['id']]['permissions'][$cap['cap_id']] : array('role' => $cap['role']);
        }
        if (isset($cap['style']) && $cap['style'] == 'dropdown') {
            
        } else {

            $output .= wpcf_access_permissions_table($roles, $saved_data, $caps,
                    $area['id'], $group['id']);
        }
        $output .= '</div>';
        $output .= '</div>';
    }
    echo $output;
}

/**
 * Save post hook.
 * 
 * @param type $post_id 
 */
function wpcf_access_post_save($post_id) {
    $areas = array();
    $areas = apply_filters('types-access-show-ui-area', $areas);
    foreach ($areas as $area) {
        $groups = array();
        $groups = apply_filters('types-access-show-ui-group', $groups,
                $area['id']);
        foreach ($groups as $group) {
            $caps = array();
            $caps = apply_filters('types-access-cap', $caps, $area['id'],
                    $group['id']);
            foreach ($caps as $cap) {
                do_action('types-access-process-ui-result', $area['id'],
                        $group['id'], $cap['cap_id']);
            }
        }
    }
    if (!empty($_POST['types_access'])) {
        update_post_meta($post_id, '_types_access', $_POST['types_access']);
    } else {
        delete_post_meta($post_id, '_types_access');
    }
}

/**
 * Post edit page hook. 
 */
function wpcf_access_admin_post_page_load_hook() {
    if (!current_user_can('edit_posts')) {
        add_action('admin_footer', 'wpcf_access_admin_edit_post_js');
    }
}

/**
 * Post edit page JS. 
 */
function wpcf_access_admin_edit_post_js() {
    $preview_txt = addslashes(__("Preview might not work. Try right clicking on button and select 'Open in new tab'.",
                    'wpcf_access'));

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('#post-preview').after('<div style="color:Red;clear:both;"><?php echo $preview_txt; ?></div>'); 
        });
    </script>
    <?php
}

/**
 * Post edit page JS. 
 */
function wpcf_access_post_no_publish_js() {

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('#publish').attr('disabled', 'disabled').attr('readonly', 'readonly'); 
        });
    </script>
    <?php
}