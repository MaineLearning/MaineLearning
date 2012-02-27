<?php

/**
 * Inits custom types.
 */
function wpcf_custom_types_init() {
    $custom_types = get_option('wpcf-custom-types', array());
    if (!empty($custom_types)) {
        foreach ($custom_types as $post_type => $data) {
            wpcf_custom_types_register($post_type, $data);
        }
    }
}

/**
 * Registers custom post type.
 * 
 * @param type $post_type
 * @param type $data 
 */
function wpcf_custom_types_register($post_type, $data) {
    if (!empty($data['disabled'])) {
        return false;
    }
    // Set labels
    if (!empty($data['labels'])) {
        if (!isset($data['labels']['name'])) {
            $data['labels']['name'] = $post_type;
        }
        if (!isset($data['labels']['singular_name'])) {
            $data['labels']['singular_name'] = $data['labels']['name'];
        }
        foreach ($data['labels'] as $label_key => $label) {
            switch ($label_key) {
                case 'add_new_item':
                case 'edit_item':
                case 'new_item':
                case 'view_item':
                case 'parent_item_colon':
                    $data['labels'][$label_key] = sprintf($label,
                            $data['labels']['singular_name']);
                    break;

                case 'search_items':
                case 'all_items':
                case 'not_found':
                case 'not_found_in_trash':
                case 'menu_name':
                    $data['labels'][$label_key] = sprintf($label,
                            $data['labels']['name']);
                    break;
            }
        }
    }
    $data['description'] = !empty($data['description']) ? htmlspecialchars(stripslashes($data['description']),
                    ENT_QUOTES) : '';
    $data['public'] = (empty($data['public']) || strval($data['public']) == 'hidden') ? false : true;
    $data['publicly_queryable'] = !empty($data['publicly_queryable']);
    $data['exclude_from_search'] = !empty($data['exclude_from_search']);
    $data['show_ui'] = (empty($data['show_ui']) || !$data['public']) ? false : true;
    if (empty($data['menu_position'])) {
        unset($data['menu_position']);
    } else {
        $data['menu_position'] = intval($data['menu_position']);
    }
    $data['hierarchical'] = !empty($data['hierarchical']);
    $data['supports'] = !empty($data['supports']) && is_array($data['supports']) ? array_keys($data['supports']) : array();
    $data['taxonomies'] = !empty($data['taxonomies']) && is_array($data['taxonomies']) ? array_keys($data['taxonomies']) : array();
    $data['has_archive'] = !empty($data['has_archive']);
    $data['can_export'] = !empty($data['can_export']);
    $data['show_in_nav_menus'] = !empty($data['show_in_nav_menus']);
    $data['show_in_menu'] = !empty($data['show_in_menu']);
    if (empty($data['query_var_enabled'])) {
        $data['query_var'] = false;
    } else if (empty($data['query_var'])) {
        $data['query_var'] = true;
    }
    if (!empty($data['show_in_menu_page'])) {
        $data['show_in_menu'] = $data['show_in_menu_page'];
    }
    if (empty($data['menu_icon'])) {
        unset($data['menu_icon']);
    } else {
        $data['menu_icon'] = stripslashes($data['menu_icon']);
        if (strpos($data['menu_icon'], '[theme]') !== false) {
            $data['menu_icon'] = str_replace('[theme]', get_stylesheet_directory_uri(), $data['menu_icon']);
        }
    }
    if (!empty($data['rewrite']['enabled'])) {
        $data['rewrite']['with_front'] = !empty($data['rewrite']['with_front']);
        $data['rewrite']['feeds'] = !empty($data['rewrite']['feeds']);
        $data['rewrite']['pages'] = !empty($data['rewrite']['pages']);
        if (!empty($data['rewrite']['custom']) && $data['rewrite']['custom'] != 'custom') {
            unset($data['rewrite']['slug']);
        }
        unset($data['rewrite']['custom']);
    } else {
        $data['rewrite'] = false;
    }
    register_post_type($post_type, $data);
    
    // Add the standard tags and categoires if the're set.
    $body = '';
    if (in_array('post_tag', $data['taxonomies'])) {
        $body = 'register_taxonomy_for_object_type("post_tag", "'.$post_type.'");';
    }
    if (in_array('category', $data['taxonomies'])) {
        $body .= 'register_taxonomy_for_object_type("category", "'.$post_type.'");';
    }
    
    // make sure the function name is OK
    $post_type = str_replace('-', '_', $post_type);
    if ($body != '' && ! function_exists($post_type.'_add_default_taxes')) {
        eval('function '.$post_type.'_add_default_taxes() { '.$body.' }');
        add_action('init', $post_type.'_add_default_taxes');    
    }
}