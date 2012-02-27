<?php
/*
 * Custom types functions.
 * @todo Investigate supports post-formats
 */

/**
 * Returns default custom type structure.
 *
 * @return array
 */
function wpcf_custom_types_default() {
    return array(
        'labels' => array(
            'name' => '',
            'singular_name' => '',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New %s',
//          'edit' => 'Edit',
            'edit_item' => 'Edit %s',
            'new_item' => 'New %s',
//          'view' => 'View',
            'view_item' => 'View %s',
            'search_items' => 'Search %s',
            'not_found' => 'No %s found',
            'not_found_in_trash' => 'No %s found in Trash',
            'parent_item_colon' => 'Parent %s',
            'menu_name' => '%s',
            'all_items' => '%s',
        ),
        'slug' => '',
        'description' => '',
        'public' => true,
        'capabilities' => false,
        'menu_position' => null,
        'menu_icon' => '',
        'taxonomies' => array(
            'category' => false,
            'post_tag' => false,
        ),
        'supports' => array(
            'title' => true,
            'editor' => true,
            'trackbacks' => false,
            'comments' => false,
            'revisions' => false,
            'author' => false,
            'excerpt' => false,
            'thumbnail' => false,
            'custom-fields' => false,
            'page-attributes' => false,
            'post-formats' => false,
        ),
        'rewrite' => array(
            'enabled' => true,
            'slug' => '',
            'with_front' => true,
            'feeds' => true,
            'pages' => true,
        ),
        'has_archive' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'hierarchical' => false,
        'query_var_enabled' => true,
        'query_var' => '',
        'can_export' => true,
        'show_in_nav_menus' => true,
        'register_meta_box_cb' => '',
        'permalink_epmask' => 'EP_PERMALINK'
    );
}

/**
 * Returns HTML formatted AJAX activation link.
 * 
 * @param type $post_type
 * @return type 
 */
function wpcf_admin_custom_types_get_ajax_activation_link($post_type) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=activate_post_type&amp;wpcf-post-type='
                    . $post_type . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $post_type) . '&amp;_wpnonce=' . wp_create_nonce('activate_post_type')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $post_type . '">'
            . __('Activate', 'wpcf') . '</a>';
}

/**
 * Returns HTML formatted AJAX deactivation link.
 * @param type $group_id
 * @return type 
 */
function wpcf_admin_custom_types_get_ajax_deactivation_link($post_type) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=deactivate_post_type&amp;wpcf-post-type='
                    . $post_type . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $post_type) . '&amp;_wpnonce=' . wp_create_nonce('deactivate_post_type')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $post_type . '">'
            . __('Deactivate', 'wpcf') . '</a>';
}