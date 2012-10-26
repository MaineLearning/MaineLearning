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
 * Version:     1.2
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

		// @todo Remove this; since it's janky to need to do this.
		add_action( 'manage_posts_columns',        array( $this, 'add_column'       )         );
		add_action( 'manage_pages_columns',        array( $this, 'add_column'       )         );
		add_action( 'manage_posts_custom_column',  array( $this, 'manage_column'    ), 10,  2 );
		add_action( 'manage_pages_custom_column',  array( $this, 'manage_column'    ), 10,  2 );

		add_action( 'post_submitbox_misc_actions', array( $this, 'metabox'          )         );
		add_action( 'quick_edit_custom_box',       array( $this, 'quickedit'        ), 10,  2 );
		add_action( 'bulk_edit_custom_box',        array( $this, 'quickedit'        ), 10,  2 );
		add_action(	'admin_enqueue_scripts',       array( $this, 'quickedit_script' ), 10,  1 );
		add_action( 'save_post',                   array( $this, 'save_post'        ), 999, 2 ); // Late priority for plugin friendliness
		add_action( 'admin_head',                  array( $this, 'admin_head'       )         );
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
		$post_types = get_post_types( $args, 'objects' );
		$cpt_object = get_post_type_object( get_post_type() );

		// Bail if object does not exist or produces an error
		if ( empty( $cpt_object ) || is_wp_error( $cpt_object ) )
			return; ?>

		<div class="misc-pub-section misc-pub-section-last post-type-switcher">
			<label for="pts_post_type"><?php _e( 'Post Type:' ); ?></label>
			<span id="post-type-display"><?php echo esc_html( $cpt_object->labels->singular_name ); ?></span>

			<?php if ( current_user_can( $cpt_object->cap->publish_posts ) ) : ?>

				<a href="#" id="edit-post-type-switcher" class="hide-if-no-js"><?php _e( 'Edit' ); ?></a>

				<?php wp_nonce_field( 'post-type-selector', 'pts-nonce-select' ); ?>

				<div id="post-type-select">
					<select name="pts_post_type" id="pts_post_type">

						<?php foreach ( $post_types as $post_type => $pt ) : ?>

							<?php if ( ! current_user_can( $pt->cap->publish_posts ) ) continue; ?>

							<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( get_post_type(), $post_type ); ?>><?php echo esc_html( $pt->labels->singular_name ); ?></option>

						<?php endforeach; ?>

					</select>
					<a href="#" id="save-post-type-switcher" class="hide-if-no-js button"><?php _e( 'OK' ); ?></a>
					<a href="#" id="cancel-post-type-switcher" class="hide-if-no-js"><?php _e( 'Cancel' ); ?></a>
				</div>

			<?php endif; ?>

		</div>

	<?php
	}

	/**
	 * Adds the post type column
	 *
	 * @since PostTypeSwitcher (1.2)
	 */
	public function add_column( $columns ) {
		return array_merge( $columns,  array( 'post_type' => __( 'Type' ) ) );
	}

	/**
	 * Manages the post type column
	 *
	 * @since PostTypeSwitcher (1.1.1)
	 */
	public function manage_column( $column, $post_id ) {
		switch( $column ) {
			case 'post_type' :
				$post_type = get_post_type_object( get_post_type( $post_id ) ); ?>

				<span data-post-type="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->labels->singular_name ); ?></span>

				<?php
				break;
		}
	}

	/**
	 * Adds quickedit button for bulk-editing post types
	 *
	 * @since PostTypeSwitcher (1.2)
	 */
	public function quickedit( $column_name, $post_type ) {
	?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label class="alignleft">
					<span class="title"><?php _e( 'Post Type' ); ?></span>
					<?php wp_nonce_field( 'post-type-selector', 'pts-nonce-select' ); ?>
					<?php $this->select_box(); ?>
				</label>
			</div>
		</fieldset>
	<?php
	}

	/**
	 * Adds quickedit script for getting values into quickedit box
	 *
	 * @since PostTypeSwitcher (1.2)
	 */
	public function quickedit_script( $hook = '' ) {
		if ( 'edit.php' != $hook )
			return;

		wp_enqueue_script( 'pts_quickedit', plugins_url( 'js/quickedit.js', __FILE__ ), array( 'jquery' ), '', true );
	}

	/**
	 * Output a post-type dropdown
	 *
	 * @since PostTypeSwitcher (1.2)
	 */
	public function select_box() {
		$args = (array) apply_filters( 'pts_post_type_filter', array(
			'public'  => true,
			'show_ui' => true
		) );
		$post_types = get_post_types( $args, 'objects' ); ?>

		<select name="pts_post_type" id="pts_post_type">

			<?php foreach ( $post_types as $post_type => $pt ) : ?>

				<?php if ( ! current_user_can( $pt->cap->publish_posts ) ) continue; ?>

				<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( get_post_type(), $post_type ); ?>><?php echo esc_html( $pt->labels->singular_name ); ?></option>

			<?php endforeach; ?>

		</select>

	<?php
	}

	/**
	 * Set the post type on save_post but only when editing
	 *
	 * We do a bunch of sanity checks here, to make sure we're only changing the
	 * post type when the user explicitly intends to.
	 *
	 * - Not during autosave
	 * - Check nonce
	 * - Check user capabilities
	 * - Check $_POST input name
	 * - Check if revision or current post-type
	 * - Check new post-type exists
	 * - Check that user can publish posts of new type
	 *
	 * @since PostTypeSwitcher (0.3)
	 * @param int $post_id
	 * @param object $post
	 * @return If any number of condtions are met
	 */
	function save_post( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			die( 'autosave' );
			return;
		}

		if ( ! isset( $_REQUEST['pts-nonce-select'] ) )
			return;

		if ( ! wp_verify_nonce( $_REQUEST['pts-nonce-select'], 'post-type-selector' ) )
			return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		if ( empty( $_REQUEST['pts_post_type'] ) )
			return;

		if ( in_array( $post->post_type, array( $_REQUEST['pts_post_type'], 'revision' ) ) )
			return;

		if ( ! $new_post_type_object = get_post_type_object( $_REQUEST['pts_post_type'] ) )
			return;

		if ( ! current_user_can( $new_post_type_object->cap->publish_posts ) )
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

		// Only for admin area
		if ( ! is_admin() )
			return false;

		// Allowed admin pages
		$pages = apply_filters( 'pts_allowed_pages', array(
			'post.php', 'edit.php', 'admin-ajax.php'
		) );

		// Only show switcher when editing
		return (bool) in_array( $pagenow, $pages );
	}
}
new Post_Type_Switcher();
