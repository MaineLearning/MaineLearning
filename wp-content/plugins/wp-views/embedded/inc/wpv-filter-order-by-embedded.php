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
    if (strpos($orderby, 'field-') === 0) {
        // we need to order by meta data.
        $query['meta_key'] = substr($orderby, 6);
        $orderby = 'meta_value';
    }
    $query['orderby'] = $orderby;
    
    if (isset($_GET['wpv_order'])) {
        $query['order']= $_GET['wpv_order'][0];
    }
    
    // check for column sorting GET parameters.
    
    if (isset($_GET['wpv_column_sort_id'])) {
        $field = $_GET['wpv_column_sort_id'];
        if (strpos($field, 'post-field') === 0) {
            $query['meta_key'] = substr($field, 11);
            $query['orderby'] = 'meta_value';
        } else {
            $query['orderby'] = str_replace('-', '_', $field);
        }
    }
    
    if (isset($_GET['wpv_column_sort_dir'])) {
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

