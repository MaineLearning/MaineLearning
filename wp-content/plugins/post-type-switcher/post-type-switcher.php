<?php

/**
 * Post Type Switcher
 *
 * Allow switching of a post type while editing a post (in post publish section)
 *
 * @package PostTypeSwitcher
 * @subpackage Main
 */

/**
 * Plugin Name: Post Type Switcher
 * Plugin URI:  http://wordpress.org/extend/post-type-switcher/
 * Description: Allow switching of a post type while editing a post (in post publish section)
 * Version:     1.1
 * Author:      johnjamesjacoby
 * Author URI:  http://johnjamesjacoby.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * The main post type switcher class
 *
 * @package PostTypeSwitcher
 */
final class Post_Type_Switcher {

	/**
	 * Setup the actions needed to execute class methods where needed
	 *
	 * @since PostTypeSwitcher (1.1)
	 */
	public function __construct() {

		if ( ! $this->is_allowed_page() )
			return;

		add_action( 'post_submitbox_misc_actions', array( $this, 'metabox'    )        );
		add_action( 'save_post',                   array( $this, 'save_post'  ), 10, 2 );
		add_action( 'admin_head',                  array( $this, 'admin_head' )        );
	}

	/**
	 * pts_metabox()
	 *
	 * Adds post_publish metabox to allow changing post_type
	 *
	 * @since PostTypeSwitcher (0.3)
	 */
	public function metabox() {

		// Allow types to be filtered, just incase you really need to switch
		// between crazy types of posts.
		$args = (array) apply_filters( 'pts_post_type_filter', array(
			'public'  => true,
			'show_ui' => true
		) );
		$post_types  = get_post_types( $args, 'objects' );
		$cpt_object  = get_post_type_object( get_post_type() );

		// Bail if object is dirty
		if ( empty( $cpt_object ) || is_wp_error( $cpt_object ) )
			return; ?>

		<div class="misc-pub-section misc-pub-section-last post-type-switcher">
			<label for="pts_post_type"><?php _e( 'Post Type:' ); ?></label>
			<span id="post-type-display"><?php echo $cpt_object->labels->singular_name; ?></span>

			<?php if ( current_user_can( $cpt_object->cap->publish_posts ) ) : ?>

				<a href="#" id="edit-post-type-switcher" class="hide-if-no-js"><?php _e( 'Edit' ); ?></a>

				<?php wp_nonce_field( 'post-type-selector', 'pts-nonce-select' ); ?>

				<div id="post-type-select">
					<select name="pts_post_type" id="pts_post_type">

					<?php foreach ( $post_types as $post_type => $pt ) :
						if ( ! current_user_can( $pt->cap->publish_posts ) )
							continue;

						echo '<option value="' . esc_attr( $pt->name ) . '"' . selected( get_post_type(), $post_type, false ) . '>' . $pt->labels->singular_name . "</option>\n";
					endforeach; ?>

					</select>
					<a href="#" id="save-post-type-switcher" class="hide-if-no-js button"><?php _e( 'OK' ); ?></a>
					<a href="#" id="cancel-post-type-switcher" class="hide-if-no-js"><?php _e( 'Cancel' ); ?></a>
				</div>

			<?php endif; ?>

		</div>

	<?php
	}
	
	/**
	 * Set the post type on save_post but only when editing
	 *
	 * @since PostTypeSwitcher (0.3)
	 * @param int $post_id
	 * @param object $post
	 * @return If any number of condtions are met
	 */
	function save_post( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! isset( $_POST['pts-nonce-select'] ) )
			return;

		if ( ! wp_verify_nonce( $_POST['pts-nonce-select'], 'post-type-selector' ) )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		if ( empty( $_POST['pts_post_type'] ) )
			return;

		if ( $_POST['pts_post_type'] == $post->post_type )
			return;

		if ( ! $new_post_type_object = get_post_type_object( $_POST['pts_post_type'] ) )
			return;		

		if ( ! current_user_can( $new_post_type_object->cap->publish_posts ) )
			return;

		if ( 'revision' == $post->post_type )
			return;

		set_post_type( $post_id, $new_post_type_object->name );
	}

	/**
	 * Adds needed JS and CSS to admin header
	 *
	 * @since PostTypeSwitcher (0.3)
	 * @return If on post-new.php
	 */
	function admin_head() {
	?>

		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
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

	/**
	 * Whether or not the current file requires the post type switcher
	 *
	 * @since PostTypeSwitcher (1.1)
	 * @return bool True if it should load, false if not
	 */
	private static function is_allowed_page() {
		global $pagenow;

		$pages = apply_filters( 'pts_allowed_pages', array(
			'post.php'
		) );

		// Only show switcher when editing
		return (bool) in_array( $pagenow, $pages );
	}
}
new Post_Type_Switcher();

?>
