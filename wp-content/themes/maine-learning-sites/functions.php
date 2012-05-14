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
/** http://wpsmith.net/2011/genesis/how-to-customize-the-genesis-comment-form/ */

add_filter('genesis_comment_form_args', 'custom_comment_form_args');
function custom_comment_form_args($args) {
  $args['title_reply'] = 'Share your knowledge';// $args['title_reply'] = ''; for total removal
  return $args;
}

/** MLN: Add home featured widgitized area */

genesis_register_sidebar( array(
'id'		=> 'home-featured',
'name'		=> __( 'Home Featured Area' ),
'description'	=> __( 'This is the Home Featured Area.' ),
) );




