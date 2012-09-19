<?php
/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

/** Child theme (do not remove) */
define( 'CHILD_THEME_NAME', 'Sample Child Theme' );
define( 'CHILD_THEME_URL', 'http://www.studiopress.com/themes/genesis' );

/** Add support for custom background */
add_custom_background();

/** Add support for custom header */
add_theme_support( 'genesis-custom-header', array( 'width' => 960, 'height' => 90 ) );

/** Add support for 3-column footer widgets */
add_theme_support( 'genesis-footer-widgets', 3 );

//load extra-editor-styles.css in tinymce
add_editor_style('css/extra-editor-styles.css');
add_filter('tiny_mce_before_init', 'myCustomTinyMCE' );
/* Custom CSS styles on TinyMCE Editor */
if ( ! function_exists( 'myCustomTinyMCE' ) ) {
	function myCustomTinyMCE($init) {
		$init['theme_advanced_styles'] = 'Byline=byline; Summary=summary; Pullquote 40pc=.pullquote-40pc';
	return $init;
	}
}

/** MLN: Add mln-site-id to body class */
/** http://www.studiopress.com/support/showthread.php?p=472123 */
add_action( 'body_class', 'wpmu_body_class' );
function wpmu_body_class( $class ) {
    global $current_blog;
    $class[] = 'mln-site-' . $current_blog-> blog_id;
    return $class;
}

/** MLN: Change comments invite copy */
/** http://www.studiopress.com/tutorials/comments 
*/

/** Modify the comment link text */
add_filter( 'genesis_post_meta', 'post_meta_filter' );
function post_meta_filter( $post_meta ) {
    return '[post_comments zero="Share your recommendation" one="1 Comment" more="% Comments"]';
}

/** MLN: Add home featured widgitized area */

genesis_register_sidebar( array(
'id'		=> 'home-featured',
'name'		=> __( 'Home Featured Area' ),
'description'	=> __( 'This is the Home Featured Area.' ),
) );


/**
 * Customize text inside of search box
 *
 * @author Rick R. Duncan
 * @link http://www.buildbrandbelieve.com
 */
add_filter('genesis_search_text', 'b3_custom_search_text');
function b3_custom_search_text($text) {
    return esc_attr('Search all text');
}

/**
 * Customize search button text
 *
 * @author Rick R. Duncan
 * @link http://www.buildbrandbelieve.com
 */
add_filter('genesis_search_button_text', 'b3_custom_search_button_text');
function b3_custom_search_button_text($text) {
    return esc_attr('Go');
}

/**
 * Lower priority of automatic p tags
 *
 * http://wordpress.org/extend/plugins/column-matic/faq/
 */


add_filter( 'the_content', 'wpautop',20 );



/* Includes externally-stored functions */

include_once( "functions/chosen-taxonomy-metabox.php" );




/**
 * Change upload directory for all forms
 * http://code.hyperspatial.com/1171/gravity-forms-upload-path/
  */

add_filter("gform_upload_path", "change_upload_path", 20, 2);
function change_upload_path($path_info, $form_id){
     $path_info["path"] = "/assets/";
     $path_info["url"] = "http://mainelearning.net/assets/";
     return $path_info;
}
