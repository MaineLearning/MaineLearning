<?php
/**
 * Controls the ability to choose a post template.
 *
 * This code adapted from the Single Post Template
 * plugin by Nathan Rice, http://www.nathanrice.net/plugins
 *
 * @category   Genesis
 * @package    Tools
 * @subpackage SinglePostTemplate
 * @author     Nathan Rice
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

if ( ! function_exists( 'get_post_templates' ) ) {
/**
 * Scans the template files of the active theme, and returns an
 * array of [Template Name => {file}.php]
 *
 * @since 0.2.0
 *
 * @return array
 */
function get_post_templates() {

	$themes = get_themes();
	$theme = get_current_theme();
	$templates = $themes[$theme]['Template Files'];
	$post_templates = array();

	$base = array( trailingslashit( get_template_directory() ), trailingslashit( get_stylesheet_directory() ) );

	foreach ( (array) $templates as $template ) {
		$template = WP_CONTENT_DIR . str_replace( WP_CONTENT_DIR, '', $template );
		$basename = str_replace( $base, '', $template );

		/** Don't allow template files in subdirectories */
		if ( false !== strpos( $basename, '/' ) )
			continue;

		$template_data = implode( '', file( $template ) );

		$name = '';
		if ( preg_match( '|Single Post Template:(.*)$|mi', $template_data, $name ) )
			$name = _cleanup_header_comment( $name[1] );

		if ( ! empty( $name ) ) {
			if( basename( $template ) != basename( __FILE__ ) )
				$post_templates[trim( $name )] = $basename;
		}
	}

	return $post_templates;

}
}

if ( ! function_exists( 'post_templates_dropdown' ) ) {
/**
 * Build the dropdown items for the post screen metabox.
 *
 * @since 0.2.0
 *
 * @global stdClass $post Post object
 */
function post_templates_dropdown() {

	global $post;

	$post_templates = get_post_templates();

	/** Loop through templates, make them options */
	foreach ( $post_templates as $template_name => $template_file ) {
		$selected = ( $template_file == get_post_meta( $post->ID, '_wp_post_template', true ) ) ? ' selected="selected"' : '';
		$opt = '<option value="' . esc_attr( $template_file ) . '"' . $selected . '>' . esc_html( $template_name ) . '</option>';
		echo $opt;
	}

}
}

add_filter( 'single_template', 'get_post_template' );
if ( ! function_exists( 'get_post_template' ) ) {
/**
 * Filter the single template value, and replace it with the template chosen by
 * the user, if they chose one.
 *
 * @since 0.2.0
 *
 * @global stdClass $post Post object
 * @param string $template Name of the template
 * @return string Possibly amended template name
 */
function get_post_template( $template ) {

	global $post;

	$custom_field = get_post_meta( $post->ID, '_wp_post_template', true );

	if( ! $custom_field )
		return $template;

	/** Prevent directory traversal */
	$custom_field = str_replace( '..', '', $custom_field );

	if( file_exists( STYLESHEETPATH . "/{$custom_field}" ) )
		$template = STYLESHEETPATH . "/{$custom_field}";
	elseif( file_exists( TEMPLATEPATH . "/{$custom_field}" ) )
		$template = TEMPLATEPATH . "/{$custom_field}";

	return $template;

}
}

add_action( 'admin_menu', 'pt_add_custom_box' );
if ( ! function_exists( 'pt_add_custom_box' ) ) {
/**
 * Register a metabox for the Post edit screen.
 *
 * @since 0.2.0
 *
 * @uses get_post_template()
 *
 * @see pt_inner_custom_box()
 */
function pt_add_custom_box() {

	if ( get_post_templates() )
		add_meta_box( 'pt_post_templates', __( 'Single Post Template', 'genesis' ), 'pt_inner_custom_box', 'post', 'normal', 'high' );

}
}

if ( ! function_exists( 'pt_inner_custom_box' ) ) {
/**
 * Echoes single post template metabox contents.
 *
 * Uses nonce for verification.
 *
 * @since 0.2.0
 *
 * @uses post_templates_dropdown() Create dropdown options
 *
 * @see pt_add_custom_box()
 *
 * @global stdClass $post Post object
 */
function pt_inner_custom_box() {

	global $post;
	?>
	<input type="hidden" name="pt_noncename" id="pt_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

	<label class="hidden" for="post_template"><?php  _e( 'Post Template', 'genesis' ); ?></label><br />
	<select name="_wp_post_template" id="post_template" class="dropdown">
		<option value=""><?php _e( 'Default', 'genesis' ); ?></option>
		<?php post_templates_dropdown(); ?>
	</select><br /><br />
	<p><?php _e( 'Some themes have custom templates you can use for single posts that might have additional features or custom layouts. If so, you will see them above.', 'genesis' ); ?></p>
	<?php

}
}

add_action( 'save_post', 'pt_save_postdata', 1, 2 );
if ( ! function_exists( 'pt_save_postdata' ) ) {
/**
 * When the post is saved, saves the selected single post template.
 *
 * @since 0.2.0
 *
 * @param integer $post_id Post ID
 * @param stdClass $post Post object
 * @return integer Post ID
 */
function pt_save_postdata( $post_id, $post ) {

	/*
	 * Verify this came from the our screen and with proper authorization,
	 * because save_post can be triggered at other times
	 */
	if ( ! wp_verify_nonce( $_POST['pt_noncename'], plugin_basename( __FILE__ ) ) )
		return $post->ID;

	/** Is the user allowed to edit the post or page? */
	if ( 'page' == $_POST['post_type'] )
		if ( ! current_user_can( 'edit_page', $post->ID ) )
			return $post->ID;
	else
		if ( ! current_user_can( 'edit_post', $post->ID ) )
			return $post->ID;

	/** OK, we're authenticated: we need to find and save the data */

	/** Put the data into an array to make it easier to loop though and save */
	$mydata['_wp_post_template'] = $_POST['_wp_post_template'];

	/** Add values of $mydata as custom fields */
	foreach ( $mydata as $key => $value ) {
		/** Don't store custom data twice */
		if( 'revision' == $post->post_type )
			return;

		/** If $value is an array, make it a CSV (unlikely) */
		$value = implode( ',', (array) $value );

		/** Update the data if it exists, or add it if it doesn't */
		if( get_post_meta( $post->ID, $key, false ) )
			update_post_meta( $post->ID, $key, $value );
		else
			add_post_meta( $post->ID, $key, $value );

		/** Delete if blank */
		if( ! $value )
			delete_post_meta( $post->ID, $key );
	}

}
}