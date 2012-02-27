<?php
/*
 * Common functions.
 */
define('ICL_COMMON_FUNCTIONS', true);
/**
 * Calculates relative path for given file.
 * 
 * @param type $file Absolute path to file
 * @return string Relative path
 */
function icl_get_file_relpath($file) {
    $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
    $http_protocol = $is_https ? 'https' : 'http';
    $base_root = $http_protocol . '://' . $_SERVER['HTTP_HOST'];
    $base_url = $base_root;
    $dir = rtrim(dirname($file), '\/');
    if ($dir) {
        $base_path = $dir;
        $base_url .= $base_path;
        $base_path .= '/';
    } else {
        $base_path = '/';
    }
    $relpath = $base_root
            . str_replace(
                    str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))
                    , '', str_replace('\\', '/', dirname($file))
    );
    return $relpath;
}

/**
 * Fix WP's multiarray parsing.
 * 
 * @param type $arg
 * @param type $defaults
 * @return type 
 */
function wpv_parse_args_recursive($arg, $defaults) {
    $temp = false;
    if (isset($arg[0])) {
        $temp = $arg[0];
    } else if (isset($defaults[0])) {
        $temp = $defaults[0];
    }
    $arg = wp_parse_args($arg, $defaults);
    if ($temp) {
        $arg[0] = $temp;
    }
    foreach ($defaults as $default_setting_parent => $default_setting) {
        if (!is_array($default_setting)) {
            if (!isset($arg[$default_setting_parent])) {
                $arg[$default_setting_parent] = $default_setting;
            }
            continue;
        }
        if (!isset($arg[$default_setting_parent])) {
            $arg[$default_setting_parent] = $defaults[$default_setting_parent];
        }
        $arg[$default_setting_parent] = wpv_parse_args_recursive($arg[$default_setting_parent], $defaults[$default_setting_parent]);
    }
    
    return $arg;
}

/**
 * Condition function to evaluate and display given block based on expressions
 * 'args' => arguments for evaluation fields
 * 
 * Supported actions and symbols:
 * 
 * Integer and floating-point numbers
 * Math operators: +, -, *, /
 * Comparison operators: &lt;, &gt;, =, &lt;=, &gt;=, !=
 * Boolean operators: AND, OR, NOT
 * Nested expressions - several levels of brackets
 * Variables defined as shortcode parameters starting with a dollar sign
 * empty() function that checks for blank or non-existing fields
 * 
 * 
 */
function wpv_condition($atts) {
	extract(
        shortcode_atts( array('evaluate' => FALSE), $atts )
    );
    
    global $post;
    
    // if in admin, get the post from the URL
    if(is_admin()) {
        // Get post
        if (isset($_GET['post'])) {
            $post_id = (int) $_GET['post'];
        } else if (isset($_POST['post_ID'])) {
            $post_id = (int) $_POST['post_ID'];
        } else {
            $post_id = 0;
        }
        if ($post_id) {
            $post = get_post($post_id);
        }
    }
    
    global $wplogger;

    $logging_string = "Original expression: ". $evaluate;
    
    // evaluate empty() statements for variables
    $empties = preg_match_all("/empty\(\s*\\$(\w+)\s*\)/", $evaluate, $matches);
    
    if($empties && $empties > 0) {
    	for($i = 0; $i < $empties; $i++) {
   		 	$match_var = get_post_meta($post->ID, $atts[$matches[1][$i]], true);
   		 	$is_empty = '1=0';
   		 	
   		 	// mark as empty only nulls and ""  
   		 	if(is_null($match_var) || strlen($match_var) == 0) {
   		 		$is_empty = '1=1';
   		 	}
   		 	
			$evaluate = str_replace($matches[0][$i], $is_empty, $evaluate);
   		 }
    }
    
    // find string variables and evaluate
	$strings_count = preg_match_all('/((\$\w+)|(\'[^\']*\'))\s*([\!<>\=]+)\s*((\$\w+)|(\'[^\']*\'))/', $evaluate, $matches);

	// get all string comparisons - with variables and/or literals
	if($strings_count && $strings_count > 0) {
	    for($i = 0; $i < $strings_count; $i++) {
			
	    	// get both sides and sign
	    	$first_string = $matches[1][$i];
	    	$second_string = $matches[5][$i];
	    	$math_sign =  $matches[4][$i];
	    	
	    	// replace variables with text representation
	    	if(strpos($first_string, '$') === 0) {
	    		$variable_name = substr($first_string, 1); // omit dollar sign
	    		$first_string = get_post_meta($post->ID, $atts[$variable_name], true);
	    	}
	    	if(strpos($second_string, '$') === 0) {
	    		$variable_name = substr($second_string, 1);
	    		$second_string = get_post_meta($post->ID, $atts[$variable_name], true);
	    	}
	    	
	    	// remove single quotes from string literals to get value only
	    	$first_string = (strpos($first_string, '\'') === 0) ? substr($first_string, 1, strlen($first_string) - 2) : $first_string;
	    	$second_string = (strpos($second_string, '\'') === 0) ? substr($second_string, 1, strlen($second_string) - 2) : $second_string; 
	    	
	    	// don't do string comparison if variables are numbers 
	    	if(!(is_numeric($first_string) && is_numeric($second_string))) {
	    		// compare string and return true or false
	    		$compared_str_result = wpv_compare_strings($first_string, $second_string, $math_sign);
	    	
		    	if($compared_str_result) {
					$evaluate = str_replace($matches[0][$i], '1=1', $evaluate);
		    	} else {
		    		$evaluate = str_replace($matches[0][$i], '1=0', $evaluate);
		    	}
	    	}
		}
    }
    
    // find all variable placeholders in expression
    $count = preg_match_all('/\$(\w+)/', $evaluate, $matches);
    
    $logging_string .= "; Variable placeholders: ". var_export($matches[1], true); 
    
    // replace all variables with their values listed as shortcode parameters
    if($count && $count > 0) {
    	// sort array by length desc, fix str_replace incorrect replacement
    	wpv_sort_matches_by_length(&$matches[1]);
    	
	    foreach($matches[1] as $match) {
            $meta = get_post_meta($post->ID, $atts[$match], true);
            if (empty($meta)) {
                $meta = "0";
            }
	    	$evaluate = str_replace('$'.$match, $meta, $evaluate);
	    }
    }
    
    $logging_string .= "; End evaluated expression: ". $evaluate;
    
    $wplogger->log($logging_string, WPLOG_DEBUG);
    // evaluate the prepared expression using the custom eval script
    $result = wpv_evaluate_expression($evaluate);
    
    // return true, false or error string to the conditional caller
    return $result;
}

function wpv_eval_check_syntax($code) {
    return @eval('return true;' . $code);
}

/**
 * 
 * Sort matches array by length so evaluate longest variable names first
 * 
 * Otherwise the str_replace would break a field named $f11 if there is another field named $f1
 * 
 * @param array $matches all variable names
 */
function wpv_sort_matches_by_length($matches) {
	$length = count($matches);
	for($i = 0; $i < $length; $i++) {
		$max = strlen($matches[$i]);
		$max_index = $i;
		
		// find the longest variable
		for($j = $i+1; $j < $length; $j++) {
			if(strlen($matches[$j]) > $max ) {
				$max = $matches[$j];
				$max_index = $j;
			}
		}
		
		// swap
		$temp = $matches[$i];
		$matches[$i] = $matches[$max_index];
		$matches[$max_index] = $temp;
	}
	
}


/**
 * Boolean function for string comparison
 *
 * @param string $first first string to be compared
 * @param string $second second string for comparison
 * 
 * 
 */
function wpv_compare_strings($first, $second, $sign) {
	// get comparison results
	$comparison = strcmp($first, $second);
	
	// verify cases 'less than' and 'less than or equal': <, <=
	if($comparison < 0 && ($sign == '<' || $sign == '<=')) {
		return true;	
	}
	
	// verify cases 'greater than' and 'greater than or equal': >, >=
	if($comparison > 0 && ($sign == '>' || $sign == '>=')) {
		return true;	
	}
	
	// verify equal cases: =, <=, >=
	if($comparison == 0 && ($sign == '=' || $sign == '<=' || $sign == '>=') ) {
		return true;
	}
	
	// verify != case
	if($comparison != 0 && $sign == '!=' ) {
		return true;
	}
	
	// or result is incorrect
	return false;
}

/**
 * 
 * Function that prepares the expression and calls eval()
 * Validates the input for a list of whitechars and handles internal errors if any
 * 
 * @param string $expression the expression to be evaluated 
 */
function wpv_evaluate_expression($expression){
    //Replace AND, OR, ==
    $expression = strtoupper($expression);
    $expression = str_replace("AND", "&&", $expression);
    $expression = str_replace("OR", "||", $expression);
    $expression = str_replace("NOT", "!", $expression);
    $expression = str_replace("=", "==", $expression);
    $expression = str_replace("!==", "!=", $expression); // due to the line above
    
    // validate against allowed input characters
	$count = preg_match('/[0-9+-\=\*\/<>&\!\|\s\(\)]+/', $expression, $matches);
	
	// find out if there is full match for the entire expression	
	if($count > 0) {
		if(strlen($matches[0]) == strlen($expression)) {
			 	$valid_eval = wpv_eval_check_syntax("return $expression;");
			 	if($valid_eval) {
			 		return eval("return $expression;");
			 	}
			 	else {
			 		return __("Error while parsing the evaluate expression", 'wpv-views');
			 	}
		}
		else {
			return __("Conditional expression includes illegal characters", 'wpv-views');
		}
	}
	else {
		return __("Correct conditional expression has not been found", 'wpv-views');
	}
	
}