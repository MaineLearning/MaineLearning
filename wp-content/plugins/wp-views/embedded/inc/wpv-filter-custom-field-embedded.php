<?php

/*
  Modify the query to include filtering by custom_field.
  
*/

add_filter('wpv_filter_query', 'wpv_filter_post_custom_field', 11, 2);  // 11 to make sure it occurs after the orderby filter.
function wpv_filter_post_custom_field($query, $view_settings) {

	global $WP_Views;
	$meta_keys = $WP_Views->get_meta_keys();

	foreach (array_keys($view_settings) as $key) {
		if (strpos($key, 'custom-field-') === 0 && strpos($key, '_compare') === strlen($key) - strlen('_compare')) {
			$name = substr($key, 0, strlen($key) - strlen('_compare'));
			$name = substr($name, strlen('custom-field-'));
			
			$meta_name = $name;
			if (!in_array($meta_name, $meta_keys)) {
				$meta_name = str_replace('_', ' ', $meta_name);
			}

			if (!isset($query['meta_query']) && isset($view_settings['custom_fields_relationship'])) {
				$query['meta_query'] = array('relation' => $view_settings['custom_fields_relationship']);
			}

			$query['meta_query'][] = array('key' => $meta_name,
										  'value' => $view_settings['custom-field-' . $name . '_value'],
										  'type' => $view_settings['custom-field-' . $name . '_type'],
										  'compare' => $view_settings['custom-field-' . $name . '_compare']);
			
			
		}
	}

	if (isset($query['meta_query'])) {
		$orderby = $view_settings['orderby'];
		if (strpos($orderby, 'field-') === 0) {
			// we need to remove the orderby set by the orderby filter
			unset($query['meta_key']);
			unset($query['orderby']);
			// we then post process the query posts to fix the order
			add_filter('wpv_filter_query_post_process', 'wpv_filter_post_process_custom_field', 10, 2);
			
		}
		
	}
	
    return $query;
}

function wpv_filter_post_process_custom_field($post_query, $view_settings) {
	global $wpdb;
	
    $orderby = $view_settings['orderby'];
    if (strpos($orderby, 'field-') === 0) {
        // we need to order by meta data.
        $orderby = substr($orderby, 6);
		
		$posts = $post_query->posts;
		
		$post_ids = array();
		$posts_temp = array();
		foreach($posts as $index => $post) {
			$post_ids[] = $post->ID;
			$posts_temp[$post->ID] = $post;
		}
		
		if (sizeof($post_ids) > 0) {
			$post_ids_found = implode(',', $post_ids);
			$post_ids_order = $wpdb->get_col( "
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '{$orderby}'
				AND
				post_id IN ({$post_ids_found})
				ORDER BY meta_value {$view_settings['order']}
				");
			
			$new_order = array();
			foreach ($post_ids_order as $post_id) {
				$new_order[] = $posts_temp[$post_id];
				unset($posts_temp[$post_id]);
			}
			
			// add the remaining posts
			foreach ($posts_temp as $post) {
				$new_order[] = $post;
			}
			
			$post_query->posts = $new_order;
			
		}	
	}	
	
	return $post_query;
}

