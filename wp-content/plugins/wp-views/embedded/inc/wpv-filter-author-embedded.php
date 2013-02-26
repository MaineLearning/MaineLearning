<?php

/**
 * Add a filter to add the query by author to the $query
 */

add_filter('wpv_filter_query', 'wpv_filter_post_author', 10, 2);
function wpv_filter_post_author($query, $view_settings) {

	global $WP_Views;

	if (isset($view_settings['author_mode'][0])) {
		global $wpdb;
		$author_parameter = '';
		$author_url_type = '';
		$show_author_array = array();
		$author_shortcode = '';
        
		if ($view_settings['author_mode'][0] == 'current_user') {
			global $current_user;
			if (is_user_logged_in()) {
				get_currentuserinfo();
				$show_author_array[] = $current_user->ID; // set the array to only the current user ID if is logged in
			}
		}

		if ($view_settings['author_mode'][0] == 'this_user') {
			if (isset($view_settings['author_id']) && $view_settings['author_id'] > 0) {
				$show_author_array[] = $view_settings['author_id']; // set the array to only the selected user ID
			}
		}
        
		if ($view_settings['author_mode'][0] == 'by_url') {
			if (isset($view_settings['author_url']) && '' != $view_settings['author_url']) {
				$author_parameter = $view_settings['author_url'];
			}
			if (isset($view_settings['author_url_type']) && '' != $view_settings['author_url_type']) {
				$author_url_type = $view_settings['author_url_type'];
			}
            
			if ('' != $author_parameter && '' != $author_url_type) {
				if (isset($_GET[$author_parameter])) {  // if the URL parameter is present
					$authors_to_load = $_GET[$author_parameter]; // get the array of possible authors from the URL parameter
					if (is_string($authors_to_load)) $authors_to_load = explode(',',$authors_to_load);
					if (1 == count($authors_to_load)) $authors_to_load = explode(',',$authors_to_load[0]); // fix on the pagination for the author filter
					if (0 == count($authors_to_load) || '' == $authors_to_load[0]) { // if the URL parameter is empty
						$show_author_array = null;
					} else { // if the user parameter is not empty
						switch($author_url_type) { // switch depending on what we are expecting
							case 'id':
								foreach ($authors_to_load as $id_author_to_load) {
									if (is_numeric($id_author_to_load)) { // if ID expected and not a number, skip it
										$show_author_array[] = $id_author_to_load; // if ID expected and is a number, add it to the array
									}
								}
								break;
							case 'username':
								foreach ($authors_to_load as $username_author_to_load) {
									$username_author_to_load = strip_tags($username_author_to_load);
									$author_username_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login='{$username_author_to_load}'");
									if ($author_username_id) {
										$show_author_array[] = $author_username_id; // if user exists, add it to the array
									}
								}
								break;
						}
					}
				} else {
					$show_author_array = null; // if the URL parameter is missing
				}
			}
		}
		
		if ($view_settings['author_mode'][0] == 'shortcode') {
			if (isset($view_settings['author_shortcode']) && '' != $view_settings['author_shortcode']) {
				$author_shortcode = $view_settings['author_shortcode'];
			}
			if (isset($view_settings['author_shortcode_type']) && '' != $view_settings['author_shortcode_type']) {
				$author_shortcode_type = $view_settings['author_shortcode_type'];
			}
			if ('' != $author_shortcode && ''!= $author_shortcode_type) {
				$view_attrs = $WP_Views->get_view_shortcodes_attributes();
				if (isset($view_attrs[$author_shortcode])) { // if the defined shortcode attribute is present
					$author_candidates = explode(',', $view_attrs[$author_shortcode]); // allow for multiple authors
					switch($author_shortcode_type) {
						case 'id':
							foreach ($author_candidates as $id_candid) {
								if (is_numeric($id_candid)) { // if ID expected and not a number, skip it
									$show_author_array[] = $id_candid; // if ID expected and is a number, add it to the array
								}
							}
						
						break;
						case 'username':
							foreach ($author_candidates as $username_candid) {
								$username_candid = trim(strip_tags($username_candid));
								$username_candid_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login='{$username_candid}'");
								if ($username_candid_id) {
									$show_author_array[] = $username_candid_id; // if user exists, add it to the array
								}
							}						
						break;					
					}
				} else {
					$show_author_array = null; // if the View shortcode attribute is missing
				}
			
			}
		}
		
		if (isset($show_author_array)) { // only modify the query if the URL parameter is present and not empty
			if (count($show_author_array) > 0) {
				// $query['author'] must be a string like 'id1,id2,id3'
				// because we're using &get_posts() to run the query
				// and it doesn't accept an array as author parameter
				$show_author_list = implode(",", $show_author_array);
				if (isset($query['author'])) {
					$query['author'] = implode(",", array_merge((array)$query['author'], $show_author_array));
				} else {
					$query['author'] = implode(",", $show_author_array);
				}
			} else {
				// this only happens when:
				// - auth_mode = current_user and user is not logged in
				// - auth_mode = by_url and no numeric id or valid nicename is given
				// we need to return an empty query
				$query['post__in'] = array('0');
			}
		}
        }
    
	return $query;
}