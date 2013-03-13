<?php

/**
 * Views-Shortcode: wpv-if
 *
 * Description: Conditional shortcode to be used to display a specific area
 * based on a custom field condition. \n
 * Supported actions and symbols:\n
 * Integer and floating-point numbers \n
 * Math operators: +, -, *, / \n
 * Comparison operators: &lt;, &gt;, =, &lt;=, &gt;=, != \n
 * Boolean operators: AND, OR, NOT \n
 * Nested expressions - several levels of parentheses \n
 * Variables defined as shortcode parameters starting with a dollar sign \n
 * empty() function that checks for empty or non-existing fields
 *
 * Parameters:
 * 'condition' => Define expected result from evaluate - either true or false
 * 'evaluate' => Evaluate expression with fields involved, sample use: "($field1 > $field2) AND !empty($field3)"
 * 'debug' => Enable debug to display error messages in the shortcode 
 * 'fieldX' => Define fields to be taken into account during evaluation 
 *
 * Example usage:
 * [wpv-if evaluate="boolean condition"]
 *    Execute code for true
 * [/wpv-if]
 * Sing a variable and comparing its value to a constant
 * [wpv-if f1="wpcf-condnum1" evaluate="$f1 = 1" debug="true"]Number1=1[/wpv-if]
 * Two numeric variables in a mathematical expression with boolean operators
 * [wpv-if f1="wpcf-condnum1" f2="wpcf-condnum2" evaluate="(2 < 3 AND (((3+$f2)/2) > 3 OR NOT($f1 > 3)))" debug="true"]Visible block[/wpv-if]
 * Compare custom field with a value
 * [wpv-if f1="wpcf-condstr1" evaluate="$f1 = 'My text'" debug="true"]Text1='My text' [/wpv-if]
 * Display condition if evaluates to false (use instead of else-if)
 * [wpv-if condition="false" evaluate="2 > 3"] 2 > 3 [/wpv-if]
 *
 * Link:
 * <a href="http://wp-types.com/documentation/user-guides/conditional-html-output-in-views/">Conditional HTML output in Views</a>
 *
 * Note:
 *
 */

function wpv_shortcode_wpv_if($args, $content) {
    $result = wpv_condition($args);
    
    extract(
        shortcode_atts( array('evaluate' => FALSE, 'debug' => FALSE, 'condition' => TRUE), $args)
    );
    $condition = ($condition == 'true' || $condition === TRUE) ? true : false;
    
 	// show the view area if condition corresponds to the evaluate returned result 1=1 or 0=0
    if(($result === true && $condition) || ($result === false && !$condition)) {
    	return wpv_do_shortcode($content);
    }
    else { 
    	// output empty string or the error message if debug is true
    	// empty for different condition and evaluate result
    	if(($result === false && $condition) || ($result === true && !$condition) ) {
    		return '';
    	}
    	else {
    		if($debug) {
    			return $result;
    		}
    	}
    }
}

add_shortcode('wpv-if', 'wpv_shortcode_wpv_if');

//////////////////////////////
//////////////////////////////
/**
 * Handle wpv-if inside wpv-if
 *
 */
//////////////////////////////
//////////////////////////////

function wpv_resolve_wpv_if_shortcodes($content) {
	$content = wpv_parse_wpv_if_shortcodes($content);
	
	return $content;
}

// adding filter with priority before do_shortcode and other WP standard filters
add_filter('the_content', 'wpv_resolve_wpv_if_shortcodes', 9);

/**
 * Search for the inner [wpv-if] [/wpv-if] pairs and process the inner ones first
 * TODO: see if we can have wpv-if inside wpv-for-each working
 */

function wpv_parse_wpv_if_shortcodes($content) {
	global $shortcode_tags;

	// Back up current registered shortcodes and clear them all out
	$orig_shortcode_tags = $shortcode_tags;
	remove_all_shortcodes();

	// only do wpv-if				
	add_shortcode('wpv-if', 'wpv_shortcode_wpv_if');

	$expression = '/\\[wpv-if((?!\\[wpv-if).)*\\[\\/wpv-if\\]/isU';
	$counts = preg_match_all($expression, $content, $matches);
	
	while ($counts) {
		foreach($matches[0] as $match) {

			// this will only processes the [wpv-if] shortcode
			$shortcode = do_shortcode($match);
			$content = str_replace($match, $shortcode, $content);
			
		}
		
		$counts = preg_match_all($expression, $content, $matches);
	}

	// Put the original shortcodes back
	$shortcode_tags = $orig_shortcode_tags;
	
	return $content;
}

// register filter for the wpv_do_shortcode Views rendering
add_filter('wpv-pre-do-shortcode', 'wpv_parse_wpv_if_shortcodes');


// Special handling to get shortcodes rendered in widgets.
function wpv_resolve_wpv_if_shortcodes_for_widgets($content) {
	$content = wpv_parse_wpv_if_shortcodes($content);
	
	return do_shortcode($content);
}

add_filter('widget_text', 'wpv_resolve_wpv_if_shortcodes_for_widgets');



