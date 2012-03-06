<?php
/**
 * Filter the_content tag 
 * Added support for internal shortcode execution
 * This handles Types shortcodes within other shortcodes
 * eg.  [app [types field="my_field"]]
 */

function wpv_resolve_internal_shortcodes($content) {
	$content = wpv_parse_content_shortcodes($content);
	
	return $content;
}

// adding filter with priority before do_shortcode and other WP standard filters
add_filter('the_content', 'wpv_resolve_internal_shortcodes', 9);

/**
 * Parse shortcodes in the page content
 * @param string page content to be evaluated for internal shortcodes
 */
function wpv_parse_content_shortcodes($content) {
	$outer_expression = "/\\[.*?\\]/";
	// $inner_expression = "/(\\[types.*?\\])|(\\[wpv-post-field.*?\\])/i";
	$inner_expression = "/\\[(wpv-post-field|types).*?\\]/i";
	
	// search for shortcodes
	$counts = preg_match_all($outer_expression, $content, $matches);
	
	// iterate 0-level shortcode elements
	if($counts > 0) {
		foreach($matches[0] as $match) {
			// extract the shortcode content without the brackets
			$match = substr($match, 1, strlen($match) - 1);
			$inner_counts = preg_match_all($inner_expression, $match, $inner_matches);
			
			// replace all 1-level inner shortcode matches
			if($inner_counts > 0) {
				foreach($inner_matches[0] as &$inner_match) {
					// execute shortcode content and replace
					$replacement = do_shortcode($inner_match);
					$resolved_match = $replacement;
					$content = str_replace($inner_match, $resolved_match, $content);
				}
			}
		}
	}
	
	return $content;
}

// register filter for the wpv_do_shortcode Views rendering
add_filter('wpv-pre-do-shortcode', 'wpv_parse_content_shortcodes');

