<?php

add_filter('wpv_filter_query', 'wpv_filter_get_post_types_arg', 10, 2);
function wpv_filter_get_post_types_arg($query, $view_settings) {
    
    $post_type = $query['post_type'];
    // See if the post_type is exposed as a url arg.
    if (isset($view_settings['post_type_expose_arg']) && $view_settings['post_type_expose_arg']) {
        if ($_GET['wpv_post_type']) {
            $post_type = $_GET['wpv_post_type'];
        }
    }
    $query['post_type'] = $post_type;
    return $query;

}

