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


/* Includes externally-stored functions */

/*include_once "functions/chosen-taxonomy-metabox.php"; // 


/**
 * WordPress Chosen Taxonomy Metabox
 * Author: Helen Hou-Sandi
 *
 * Use Chosen for a replacement taxonomy metabox in WordPress
 * Useful for taxonomies that aren't changed much on the fly,
 * as Chosen is for selection only.
 * You can always use the taxonomy admin screen to add/edit taxonomy terms.
 *
 * Example screenshot: http://cl.ly/2T2D232x172G353i2V2C
 *
 * Chosen: http://harvesthq.github.com/chosen/
 */

add_action( 'admin_init', 'hhs_add_meta_boxes', 1 );
function hhs_add_meta_boxes() {
	add_meta_box( 'chosen-tax', 'Choose Terms', 'hhs_chosen_tax_meta_box_display', 'post', 'side', 'default' );
}

function hhs_chosen_tax_meta_box_display() {
	global $post;
	
	wp_nonce_field( 'hhs_chosen_tax_meta_box_nonce', 'hhs_chosen_tax_meta_box_nonce' );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$( '.chzn-select' ).chosen();
	});
	</script>
	<?php
	// which taxonomies should be used - can be multiple
	$taxes = array(
		'post_tag' => 'Tags',
	);
	
	foreach ( $taxes as $tax => $label ) {
		// add more args if you want (e.g. orderby)
		$terms = get_terms( $tax, array( 'hide_empty' => 0 ) );
		$current_terms = wp_get_post_terms ( $post->ID, $tax, array('fields' => 'ids') );
		?>
		<p><label for="<?php echo $tax; ?>"><?php echo $label; ?></label>:</p>
		<p><select name="<?php echo $tax; ?>[]" class="chzn-select widefat" data-placeholder="Select one or more" multiple="multiple">
		<?php foreach ( $terms as $term ) { ?>
			<option value="<?php echo $term->slug; ?>"<?php selected( in_array( $term->term_id, $current_terms ) ); ?>><?php echo $term->name; ?></option>
		<?php } ?>
		</select>
		</p>
		<?php
	}
}
add_action( 'save_post', 'hhs_chosen_tax_meta_box_display_save' );
function hhs_chosen_tax_meta_box_display_save( $post_id ) {

	// verify nonce
	if ( ! isset( $_POST['hhs_chosen_tax_meta_box_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['hhs_chosen_tax_meta_box_nonce'], 'hhs_chosen_tax_meta_box_nonce' ) )
		return;
	
	// check autosave - maybe overkill?
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	
	// check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return;
	
	// change to match above, or be a good coder and don't dupe this
	$taxes = array (
		'post_tag',
	);
	
	foreach ( $taxes as $tax ) {
		if ( isset( $_POST[$tax] ) && is_array( $_POST[$tax] ) )
			wp_set_post_terms( $post_id, $_POST[$tax], $tax, false );
	}
}
 
// Chosen JS and CSS enqueue - assumes you are doing this in a theme
// with the JS, CSS, and sprite files in themefolder/js/chosen/
// You'd want to use plugins_url() instead if using this in a plugin
add_action( 'admin_enqueue_scripts', 'hhs_add_admin_scripts', 10, 1 );
function hhs_add_admin_scripts( $hook ) {
	global $post;
	
	// There's probably a better way to check the screen...
	if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		if ( 'post' === $post->post_type ) {     
			wp_enqueue_script(  'chosen', get_stylesheet_directory_uri().'/js/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.0' );
			wp_enqueue_style( 'chosen', get_stylesheet_directory_uri().'/js/chosen/chosen.css' );
		}
	}
}

// Hide unused taxonomy metabox(es)
add_action( 'do_meta_boxes', 'hhs_remove_tax_meta_boxes', 10, 3 );
function hhs_remove_tax_meta_boxes( $post_type, $priority, $post ) {
	remove_meta_box( 'tagsdiv-post_tag', 'post', 'side' );
}

