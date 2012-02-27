<?php

/**
 * Post Type Switcher
 *
 * Allow switching of a post type while editing a post in post publish area
 *
 * @package PostTypeSwitcher
 * @subpackage Main
 */

/**
 * Plugin Name: Post Type Switcher
 * Plugin URI:  http://wordpress.org/extend/post-type-switcher/
 * Description: Allow switching of a post type while editing a post in post publish area
 * Version:     1.0
 * Author:      johnjamesjacoby
 * Author URI:  http://johnjamesjacoby.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * pts_metabox()
 *
 * Adds post_publish metabox to allow changing post_type
 *
 * @since PostTypeSwitcher (0.3)
 * @global object $post Current post
 */
function pts_metabox() {
	global $post, $pagenow;

	// Only show switcher when editing
	if ( ! in_array( $pagenow, pts_allowed_pages() ) )
		return;

	// Disallows things like attachments, revisions, etc...
	$safe_filter          = array( 'public' => true, 'show_ui' => true );

	// Allow to be filtered, just incase you really need to switch between
	// those crazy types of posts
	$args                 = apply_filters( 'pts_metabox', $safe_filter );

	// Get the post types based on the above arguments
	$post_types           = get_post_types( (array) $args, 'objects' );

	// Populate necessary post_type values
	$cur_post_type        = $post->post_type;
	$cur_post_type_object =	get_post_type_object( $cur_post_type );

	// Make sure the currently logged in user has the power
	$can_publish          = current_user_can( $cur_post_type_object->cap->publish_posts );
?>

<div class="misc-pub-section misc-pub-section-last post-type-switcher">
	<label for="pts_post_type"><?php _e( 'Post Type:' ); ?></label>
	<span id="post-type-display"><?php echo $cur_post_type_object->labels->singular_name; ?></span>

<?php if ( !empty( $can_publish ) ) : ?>

	<a href="#" id="edit-post-type-switcher" class="hide-if-no-js"><?php _e( 'Edit' ); ?></a>

	<?php wp_nonce_field( 'post-type-selector', 'pts-nonce-select' ); ?>

	<div id="post-type-select">
		<select name="pts_post_type" id="pts_post_type">

<?php
		foreach ( $post_types as $post_type => $pt ) {
			if ( ! current_user_can( $pt->cap->publish_posts ) )
				continue;

			echo '<option value="' . esc_attr( $pt->name ) . '"' . selected( $cur_post_type, $post_type, false ) . '>' . $pt->labels->singular_name . "</option>\n";
		}
?>

		</select>
		<a href="#" id="save-post-type-switcher" class="hide-if-no-js button"><?php _e( 'OK' ); ?></a>
		<a href="#" id="cancel-post-type-switcher" class="hide-if-no-js"><?php _e( 'Cancel' ); ?></a>
	</div>
</div>

<?php
	endif;
}
add_action( 'post_submitbox_misc_actions', 'pts_metabox' );

/**
 * Set the post type on save_post but only when editing
 *
 * @since PostTypeSwitcher (0.3)
 * @global string $pagenow
 * @param int $post_id
 * @param object $post
 * @return If any number of condtions are met
 */
function pts_save_post( $post_id, $post ) {
	global $pagenow;

	// Only show switcher when editing
	if ( ! in_array( $pagenow, pts_allowed_pages() ) )
		return;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ! isset( $_POST['pts-nonce-select'] ) )
		return;

	if ( ! wp_verify_nonce( $_POST['pts-nonce-select'], 'post-type-selector' ) )
		return;

	if ( ! current_user_can( 'edit_post', $post_id ) )
		return;

	if ( $_POST['pts_post_type'] == $post->post_type )
		return;

	if ( ! $new_post_type_object = get_post_type_object( $_POST['pts_post_type'] ) )
		return;		
	
	if ( ! current_user_can( $new_post_type_object->cap->publish_posts ) )
		return;

	set_post_type( $post_id, $new_post_type_object->name );
}
add_action( 'save_post', 'pts_save_post', 10, 2 );

/**
 * Adds needed JS and CSS to admin header
 *
 * @since PostTypeSwitcher (0.3)
 * @global string $pagenow
 * @return If on post-new.php
 */
function pts_head() {
	global $pagenow;

	// Only show switcher when editing
	if ( ! in_array( $pagenow, pts_allowed_pages() ) )
		return; ?>

	<script type="text/javascript">
		jQuery( document ).ready( function($) {
			jQuery( '.misc-pub-section.curtime.misc-pub-section-last' ).removeClass( 'misc-pub-section-last' );
			jQuery( '#edit-post-type-switcher' ).click( function(e) {
				jQuery( this ).hide();
				jQuery( '#post-type-select' ).slideDown();
				e.preventDefault();
			});

			jQuery( '#save-post-type-switcher' ).click( function(e) {
				jQuery( '#post-type-select' ).slideUp();
				jQuery( '#edit-post-type-switcher' ).show();
				jQuery( '#post-type-display' ).text( jQuery( '#pts_post_type :selected' ).text() );
				e.preventDefault();
			});

			jQuery( '#cancel-post-type-switcher' ).click( function(e) {
				jQuery( '#post-type-select' ).slideUp();
				jQuery( '#edit-post-type-switcher' ).show();
				e.preventDefault();
			});
		});
	</script>
	<style type="text/css">
		#post-type-select {
			line-height: 2.5em;
			margin-top: 3px;
			display: none;
		}
		#post-type-display {
			font-weight: bold;
		}
	</style>
<?php
}
add_action( 'admin_head', 'pts_head' );

/**
 * Return the allowed pages that $pagenow global can be
 *
 * @since PostTypeSwitcher (1.0)
 */
function pts_allowed_pages() {
	$pages = array(
		'post.php'
	);

	return $pages;
}

?>
