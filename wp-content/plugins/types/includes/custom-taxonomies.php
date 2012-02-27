<?php
/*
 * Custom taxonomies functions.
 */

/**
 * Returna default custom taxonomy structure.
 *
 * @return array
 */
function wpcf_custom_taxonomies_default() {
    return array(
        'slug' => '',
        'description' => '',
        'supports' => array(),
        'public' => true,
        'show_in_nav_menus' => true,
        'hierarchical' => false,
        'show_ui' => true,
        'show_tagcloud' => true,
        'update_count_callback' => '',
        'query_var_enabled' => true,
        'query_var' => '',
        'rewrite' => array(
            'enabled' => true,
            'slug' => '',
            'with_front' => true,
            'hierarchical' => true
        ),
        'capabilities' => false,
        'labels' => array(
            'name' => '',
            'singular_name' => '',
            'search_items' => 'Search %s',
            'popular_items' => 'Popular %s',
            'all_items' => 'All %s',
            'parent_item' => 'Parent %s',
            'parent_item_colon' => 'Parent %s:',
            'edit_item' => 'Edit %s',
            'update_item' => 'Update %s',
            'add_new_item' => 'Add New %s',
            'new_item_name' => 'New %s Name',
            'separate_items_with_commas' => 'Separate %s with commas',
            'add_or_remove_items' => 'Add or remove %s',
            'choose_from_most_used' => 'Choose from the most used %s',
            'menu_name' => '%s',
        ),
    );
}

/**
 * Returns HTML formatted AJAX activation link.
 * 
 * @param type $taxonomy
 * @return type 
 */
function wpcf_admin_custom_taxonomies_get_ajax_activation_link($taxonomy) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax'
                    . '&amp;wpcf_action=activate_taxonomy&amp;wpcf-tax='
                    . $taxonomy . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $taxonomy) . '&amp;_wpnonce=' . wp_create_nonce('activate_taxonomy')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $taxonomy . '">'
            . __('Activate', 'wpcf') . '</a>';
}

/**
 * Returns HTML formatted AJAX deactivation link.
 * 
 * @param type $taxonomy
 * @return type 
 */
function wpcf_admin_custom_taxonomies_get_ajax_deactivation_link($taxonomy) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=deactivate_taxonomy&amp;wpcf-tax='
                    . $taxonomy . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $taxonomy) . '&amp;_wpnonce=' . wp_create_nonce('deactivate_taxonomy')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $taxonomy . '">'
            . __('Deactivate', 'wpcf') . '</a>';
}