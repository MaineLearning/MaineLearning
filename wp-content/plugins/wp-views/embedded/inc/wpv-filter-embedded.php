<?php


/* Handle the short codes for creating a user query form
  
  [wpv-filter-start]
  [wpv-filter-end]
  [wpv-filter-submit]
  
*/

/**
 * Views-Shortcode: wpv-filter-start
 *
 * Description: The [wpv-filter-start] shortcode specifies the start point
 * for any controls that the views filter generates. Example controls are
 * pagination controls and search boxes. This shortcode is usually added
 * automatically to the Views Meta HTML.
 *
 * Parameters:
 * This takes no parameters.
 *
 */
  
add_shortcode('wpv-filter-start', 'wpv_filter_shortcode_start');
function wpv_filter_shortcode_start($atts){
    global $WP_Views;
    $post_query = $WP_Views->get_query();

    extract(
        shortcode_atts( array(), $atts )
    );
    
    $hide = '';
    if (isset($atts['hide']) && $atts['hide'] == 'true') {
        $hide = ' style="display:none"';
    }
    
    $out = '<form style="margin:0; padding:0;" id="wpv-filter-' . $WP_Views->get_view_count() . '" action="' . get_permalink() . '" method="GET"' . $hide . ">\n";
    
    // add hidden inputs for column sorting id and direction.
    // these are empty to start off with but will be populated
    // when a column title is clicked.
    
    $view_layout_settings = $WP_Views->get_view_layout_settings();
    if (!isset($view_layout_settings['style'])) {
        return '';
    }

    $view_settings = $WP_Views->get_view_settings();
    
    if ($view_layout_settings['style'] == 'table_of_fields') {
        
        $sort_id = $view_settings['orderby'];
        if (isset($_GET['wpv_column_sort_id'])) {
            $sort_id = $_GET['wpv_column_sort_id'];
        }
        $sort_dir = strtolower($view_settings['order']);
        if (isset($_GET['wpv_column_sort_dir'])) {
            $sort_dir = $_GET['wpv_column_sort_dir'];
        }
        
        $out .= '<input id="wpv_column_sort_id" type="hidden" name="wpv_column_sort_id" value="' . $sort_id . '">' . "\n";
        $out .= '<input id="wpv_column_sort_dir" type="hidden" name="wpv_column_sort_dir" value="' . $sort_dir . '">' . "\n";
        
        $out .= "
        <script type=\"text/javascript\">
            function wpv_column_head_click(name, direction) {
                jQuery('#wpv_column_sort_id').val(name);
                jQuery('#wpv_column_sort_dir').val(direction);
                jQuery('#wpv-filter-" . $WP_Views->get_view_count() . "').submit();
                return false;
            }
        </script>
        ";
    }
    
    // add a hidden input for the current page.
    $page = '1';
    if (isset($_GET['wpv_paged'])) {
        $page = $_GET['wpv_paged'];
    }
    $out .= '<input id="wpv_paged-' . $WP_Views->get_view_count() . '" type="hidden" name="wpv_paged" value="' . $page . '">' . "\n";
    $out .= '<input id="wpv_paged_max-' . $WP_Views->get_view_count() . '" type="hidden" name="wpv_paged_max" value="' . intval($post_query->max_num_pages) . '">' . "\n";

    // Output the current page ID. This is used for ajax call back in pagination.
    $current_post = $WP_Views->get_top_current_page();
    if ($current_post) {
        $out .= '<input id="wpv_post_id-' . $WP_Views->get_view_count() . '" type="hidden" name="wpv_post_id" value="' . $current_post->ID . '">' . "\n";
    }
    add_action('wp_footer', 'wpv_pagination_js');
    
    // Rollover
    if (isset($view_settings['pagination']['mode']) && $view_settings['pagination']['mode'] == 'rollover') {
        wpv_pagination_rollover_shortcode();
    }
    return $out;
}

/**
 * Views-Shortcode: wpv-filter-end
 *
 * Description: The [wpv-filter-end] shortcode is the end point
 * for any controls that the views filter generates.
 *
 * Parameters:
 * This takes no parameters.
 *
 */
  
add_shortcode('wpv-filter-end', 'wpv_filter_shortcode_end');
function wpv_filter_shortcode_end($atts){
    extract(
        shortcode_atts( array(), $atts )
    );
    
    return '</form>';
}
    
/**
 * Views-Shortcode: wpv-filter-submit
 *
 * Description: The [wpv-filter-submit] shortcode adds a submit button to
 * the form that the views filter generates. An example is the "Submit" button
 * for a search box
 *
 * Parameters:
 * 'hide' => 'true'|'false'
 * 'name' => The text to be used on the button.
 *
 */
  
add_shortcode('wpv-filter-submit', 'wpv_filter_shortcode_submit');
function wpv_filter_shortcode_submit($atts){

    extract(
        shortcode_atts( array(), $atts )
    );
    
    $hide = '';
    if (isset($atts['hide']) && $atts['hide'] == 'true') {
        $hide = ' style="display:none"';
    }
    
    $out = '';
    $out .= '<input type="submit" value="' . $atts['name'] . '" name="wpv_filter_submit"' . $hide . '>';
    return $out;
}

/**
 * Views-Shortcode: wpv-post-count
 *
 * Description: The [wpv-post-count] shortcode will display the number of
 * posts that will be displayed on the page
 *
 * Parameters:
 * This takes no parameters.
 *
 */
  

add_shortcode('wpv-post-count', 'wpv_post_count');
function wpv_post_count($atts){
    extract(
        shortcode_atts( array(), $atts )
    );

    global $WP_Views;
    
    $query = $WP_Views->get_query();
    
    return $query->post_count;
}
    
/**
 * Views-Shortcode: wpv-found-count
 *
 * Description: The [wpv-found-count] shortcode will display the total number of
 * posts that have been found by the Views query
 *
 * Parameters:
 * This takes no parameters.
 *
 */
  
add_shortcode('wpv-found-count', 'wpv_found_count');
function wpv_found_count($atts){
    extract(
        shortcode_atts( array(), $atts )
    );

    global $WP_Views;
    
    $query = $WP_Views->get_query();
    
    return $query->found_posts;
}

/**
 * Views-Shortcode: wpv-posts-found
 *
 * Description: The [wpv-posts-found] shortcode will display the text inside
 * the shortcode if there are posts found by the Views query.
 * eg. [wpv-posts-found]<strong>Some posts were found</strong>[/wpv-posts-found]
 *
 * Parameters:
 * This takes no parameters.
 *
 */
  
add_shortcode('wpv-posts-found', 'wpv_posts_found');
function wpv_posts_found($atts, $value){
    extract(
        shortcode_atts( array(), $atts )
    );

    global $WP_Views;
    
    $query = $WP_Views->get_query();

    if ($query->found_posts != 0 || $query->post_count != 0) {
        // display the message when posts are found.
        return wpv_do_shortcode($value);
    } else {
        return '';
    }
    
}
    
/**
 * Views-Shortcode: wpv-no-posts-found
 *
 * Description: The [wpv-no-posts-found] shortcode will display the text inside
 * the shortcode if there are no posts found by the Views query.
 * eg. [wpv-no-posts-found]<strong>No posts found</strong>[/wpv-no-posts-found]
 *
 * Parameters:
 * This takes no parameters.
 *
 */
  
add_shortcode('wpv-no-posts-found', 'wpv_no_posts_found');
function wpv_no_posts_found($atts, $value){
    extract(
        shortcode_atts( array(), $atts )
    );

    global $WP_Views;
    
    $query = $WP_Views->get_query();

    if ($query->found_posts == 0 && $query->post_count == 0) {
        // display the message when no posts are found.
        return wpv_do_shortcode($value);
    } else {
        return '';
    }
    
}
    
/*
         
    This shows the user interface to the end user on page
    that contains the view.
    
*/

function wpv_filter_show_user_interface($name, $values, $selected, $style) {
    $out = '';
    $out .= "<div>\n";
    
    if ($style == 'drop_down') {
        $out .= '<select name="'. $name . '[]">' . "\n";
    }
    
    foreach($values as $v) {
        switch ($style) {
            case "checkboxes":
                if (is_array($selected)) {
                    $checked = @in_array($v, $selected) ? ' checked="checked"' : '';
                } else {
                    $checked = $v == $selected ? ' checked="checked"' : '';
                }
                $out .= '<label><input type="checkbox" name="' . $name. '[]" value="' . $v . '" ' . $checked . '>&nbsp;' . $v . "</label>\n";
                break;

            case "radios":
                if (is_array($selected)) {
                    $checked = @in_array($v, $selected) ? ' checked="checked"' : '';
                } else {
                    $checked = $v == $selected ? ' checked="checked"' : '';
                }
                $out .= '<label><input type="radio" name="' . $name. '[]" value="' . $v . '" ' . $checked . '>&nbsp;' . $v . "</label>\n";
                break;

            case "drop_down":
                if (is_array($selected)) {
                    $is_selected = @in_array($v, $selected) ? ' selected="selected"' : '';
                } else {
                    $is_selected = $v == $selected ? ' selected="selected"' : '';
                }
                $out .= '<option value="' . $v . '" ' . $is_selected . '>' . $v . "</option>\n";
                break;
        }
    }

    if ($style == 'drop_down') {
        $out .= "</select>\n";
    }
    
    $out .= "</div>\n";
    
    return $out;
}

