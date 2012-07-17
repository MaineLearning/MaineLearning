<?php

add_filter('wpv_view_settings', 'wpv_order_by_default_settings', 10, 2);
function wpv_order_by_default_settings($view_settings) {

    if (!isset($view_settings['orderby'])) {
        $view_settings['orderby'] = 'post_date';
    }
    if (!isset($view_settings['order'])) {
        $view_settings['order'] = 'DESC';
    }
    
    return $view_settings;
}


add_filter('wpv_filter_query', 'wpv_filter_get_order_arg', 10, 2);
function wpv_filter_get_order_arg($query, $view_settings) {
    $orderby = $view_settings['orderby'];
    if (isset($_GET['wpv_column_sort_id']) && $_GET['wpv_column_sort_id'] != 'undefined') {
        $orderby = $_GET['wpv_column_sort_id'];
    }
    
    $orderby_set = false;
    
    if (strpos($orderby, 'field-') === 0) {
        // we need to order by meta data.
        $query['meta_key'] = substr($orderby, 6);
        $orderby = 'meta_value';

        $orderby_set = true;
        
        // Fix for numeric custom field , need to user meta_value_num
        if (_wpv_is_numeric_field($view_settings['orderby'])) {
            $orderby= 'meta_value_num';
        }        
    }
    $query['orderby'] = $orderby;
    
    if (isset($_GET['wpv_order'])) {
        $query['order']= $_GET['wpv_order'][0];
    }
    
    // check for column sorting GET parameters.
    
    if (!$orderby_set && isset($_GET['wpv_column_sort_id']) && $_GET['wpv_column_sort_id'] != 'undefined') {
        $field = $_GET['wpv_column_sort_id'];
        if (strpos($field, 'post-field') === 0) {
            $query['meta_key'] = substr($field, 11);
            $query['orderby'] = 'meta_value';
        } elseif (strpos($field, 'types-field') === 0) {
            $query['meta_key'] = substr($field, 12);
            if (function_exists('wpcf_types_get_meta_prefix')) {
                $query['meta_key'] = wpcf_types_get_meta_prefix() . $query['meta_key'];
            }
            if (_wpv_is_numeric_field('field-' . $query['meta_key'])) {
                $query['orderby'] = 'meta_value_num';
            } else {
                $query['orderby'] = 'meta_value';
            }
        } else {
            $query['orderby'] = str_replace('-', '_', $field);
        }
    }
    
    if (isset($_GET['wpv_column_sort_dir']) && $_GET['wpv_column_sort_dir'] != 'undefined') {
        $query['order'] = strtoupper($_GET['wpv_column_sort_dir']);
    }    

    if ($query['orderby'] == 'post_link') {
        $query['orderby'] = 'post_title';
    }
    if ($query['orderby'] == 'post_body') {
        $query['orderby'] = 'post_content';
    }

    if (strpos($query['orderby'], 'post_') === 0) {
        $query['orderby'] = substr($query['orderby'], 5);
    }
    
    return $query;
}

function _wpv_is_numeric_field($field_name) {
    $opt = get_option('wpcf-fields');
    if($opt && mb_ereg('^field-wpcf-',$field_name)) {
        $field_name = substr($field_name,11);
        if (isset($opt[$field_name]['type'])) {
            $field_type = strtolower($opt[$field_name]['type']);
            if ( $field_type == 'numeric' || $field_type == 'date') {
                return true;
            }
        }
        
    }
    
    return false;
}