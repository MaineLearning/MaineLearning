<?php

/**
 * Modify the query to include filtering by category.
 *
 */

add_filter('wpv_filter_query', 'wpv_filter_post_category', 10, 2);
function wpv_filter_post_category($query, $view_settings) {
	
	if (!isset($view_settings['taxonomy_relationship'])) {
		$view_settings['taxonomy_relationship'] = 'OR';
	}

	$taxonomies = get_taxonomies('', 'objects');
	foreach ($taxonomies as $category_slug => $category) {
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		
		if (isset($view_settings[$relationship_name])) {
			
			$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;			
			
			if (!isset($query['tax_query'])) {
				$query['tax_query'] = array('relation' => $view_settings['taxonomy_relationship']);
			}
			
			if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM PAGE") {
				// we need to get the terms from the current page.
				global $WP_Views;
				$current_page = $WP_Views->get_current_page();
				if ($current_page) {
					$terms = wp_get_post_terms($current_page->ID, $category->name, array("fields" => "ids"));
					$query['tax_query'][] = array('taxonomy' => $category->name,
											  'field' => 'id',
											  'terms' => $terms,
											  'operator' => "IN");
				}
			} else if (isset($view_settings[$save_name])) {
			
				$query['tax_query'][] = array('taxonomy' => $category->name,
										  'field' => 'id',
										  'terms' => $view_settings[$save_name],
										  'operator' => $view_settings['tax_' . $category->name . '_relationship']);
			}
		}
    }
    
    return $query;
}
