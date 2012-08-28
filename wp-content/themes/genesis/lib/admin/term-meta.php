<?php
/**
 * Handles the creation and saving of term settings.
 *
 * @category   Genesis
 * @package    Admin
 * @subpackage Term-Meta
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'admin_init', 'genesis_add_taxonomy_archive_options' );
/**
 * Loop through the custom taxonomies and add the archive options to each
 * custom taxonomy edit screen.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.6.0
 *
 * @see genesis_taxonomy_archive_options() Add Disyplay title / description checkboxes
 */
function genesis_add_taxonomy_archive_options() {

	foreach ( get_taxonomies( array( 'show_ui' => true ) ) as $tax_name )
		add_action( $tax_name . '_edit_form', 'genesis_taxonomy_archive_options', 10, 2 );

}

/**
 * Add new fields for display on archives.
 *
 * Displays on the category / tag edit forms.
 * The variables $tag and $taxonomy are passed via the hook so that we can use
 * them.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.6.0
 *
 * @see genesis_add_taxonomy_archive_options() Callback caller
 *
 * @param string $tag Name of the term
 * @param string $taxonomy Name of the taxnomy
 */
function genesis_taxonomy_archive_options( $tag, $taxonomy ) {

	$tax = get_taxonomy( $taxonomy );
	?>
	<h3><?php echo esc_html( $tax->labels->singular_name ) . ' ' . __( 'Archive Settings', 'genesis' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top"><label for="meta[headline]"><?php _e( 'Archive Headline', 'genesis' ); ?></label></th>
				<td>
					<input id="meta[headline]" name="meta[headline]" type="text" value="<?php echo esc_attr( $tag->meta['headline'] ); ?>" size="40" />
					<p class="description"><?php _e( 'Leave empty if you do not want to display a headline.', 'genesis' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="meta[intro_text]"><?php _e( 'Archive Intro Text', 'genesis' ); ?></label></th>
				<td>
					<textarea id="meta[intro_text]" class="widefat" name="meta[intro_text]" rows="5" cols="30"><?php echo esc_textarea( $tag->meta['intro_text'] ); ?></textarea>
					<p class="description"><?php _e( 'Leave empty if you do not want to display any intro text.', 'genesis' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php

}

add_action( 'admin_init', 'genesis_add_taxonomy_seo_options' );
/**
 * Loop through the custom taxonomies and add the SEO options to each custom
 * taxonomy edit screen.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.3.0
 *
 * @see genesis_taxonomy_seo_options() Adds SEO fields
 */
function genesis_add_taxonomy_seo_options() {

	foreach ( get_taxonomies( array( 'show_ui' => true ) ) as $tax_name )
		add_action( $tax_name . '_edit_form', 'genesis_taxonomy_seo_options', 10, 2 );

}

/**
 * Display title, description, keywords and robots meta SEO fields.
 *
 * The variables $tag and $taxonomy are passed via the hook so that we can use
 * them.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.2.0
 *
 * @see genesis_add-taxonomy_seo_options() Callback caller
 *
 * @param string $tag Name of the term
 * @param string $taxonomy Name of the taxonomy
 */
function genesis_taxonomy_seo_options( $tag, $taxonomy ) {

	$tax = get_taxonomy( $taxonomy );
	?>
	<h3><?php _e( 'Theme SEO Settings', 'genesis' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="meta[doctitle]"><?php printf( __( 'Custom Document %s', 'genesis' ), '<code>&lt;title&gt;</code>' ); ?></label></th>
				<td>
					<input name="meta[doctitle]" id="meta[doctitle]" type="text" value="<?php echo esc_attr( $tag->meta['doctitle'] ); ?>" size="40" />
				</td>
			</tr>

			<tr class="form-field">
				<th scope="row" valign="top"><label for="meta[description]"><?php printf( __( '%s Description', 'genesis' ), '<code>META</code>' ); ?></label></th>
				<td>
					<textarea name="meta[description]" id="meta[description]" rows="3" cols="50"><?php echo esc_html( $tag->meta['description'] ); ?></textarea>
				</td>
			</tr>

			<tr class="form-field">
				<th scope="row" valign="top"><label for="meta[keywords]"><?php printf( __( '%s Keywords', 'genesis' ), '<code>META</code>' ); ?></label></th>
				<td>
					<input name="meta[keywords]" id="meta[keywords]" type="text" value="<?php echo esc_attr( $tag->meta['keywords'] ); ?>" size="40" />
					<p class="description"><?php _e( 'Comma separated list', 'genesis' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="top"><?php _e( 'Robots Meta', 'genesis' ); ?></th>
				<td>
					<input name="meta[noindex]" id="meta[noindex]" type="checkbox" value="1" <?php checked( $tag->meta['noindex'] ); ?> />
					<label for="meta[noindex]"><?php printf( __( 'Apply %s to this archive?', 'genesis' ), '<code>noindex</code>' ); ?></label><br />
					<input name="meta[nofollow]" id="meta[nofollow]" type="checkbox" value="1" <?php checked( $tag->meta['nofollow'] ); ?> />
					<label for="meta[nofollow]"><?php printf( __( 'Apply %s to this archive?', 'genesis' ), '<code>nofollow</code>' ); ?></label><br />
					<input name="meta[noarchive]" id="meta[noarchive]" type="checkbox" value="1" <?php checked( $tag->meta['noarchive'] ); ?> />
					<label for="meta[noarchive]"><?php printf( __( 'Apply %s to this archive?', 'genesis' ), '<code>noarchive</code>' ); ?></label>
				</td>
			</tr>
		</tbody>
	</table>
	<?php

}

add_action( 'admin_init', 'genesis_add_taxonomy_layout_options' );
/**
 * Loop through the custom taxonomies and add the layout options to each custom
 * taxonomy edit screen.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.4.0
 *
 * @see genesis_taxonomy_layout_options() Add layout picker
 */
function genesis_add_taxonomy_layout_options() {

	foreach ( get_taxonomies( array( 'show_ui' => true ) ) as $tax_name )
		add_action( $tax_name . '_edit_form', 'genesis_taxonomy_layout_options', 10, 2 );

}

/**
 * Display layout picker.
 *
 * The variables $tag and $taxonomy are passed via the hook so that we can use
 * them.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.4.0
 *
 * @see genesis_add_taxonomy_layout_options() Callback caller
 *
 * @param string $tag
 * @param string $taxonomy
 */
function genesis_taxonomy_layout_options( $tag, $taxonomy ) {

	$tax = get_taxonomy( $taxonomy );
	?>
	<h3><?php _e( 'Layout Settings', 'genesis' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top"><?php _e( 'Choose Layout', 'genesis' ); ?></th>
				<td>
					<div class="genesis-layout-selector">
						<p>
							<input type="radio" name="meta[layout]" id="default-layout" value="" <?php checked( $tag->meta['layout'], '' ); ?> />
							<label for="default-layout" class="default"><?php printf( __( 'Default Layout set in <a href="%s">Theme Settings</a>', 'genesis' ), menu_page_url( 'genesis', 0 ) ); ?></label>
						</p>

						<p><?php genesis_layout_selector( array( 'name' => 'meta[layout]', 'selected' => $tag->meta['layout'], 'type' => 'site' ) ); ?></p>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<?php

}

add_action( 'edit_term', 'genesis_term_meta_save', 10, 2 );
/**
 * Save term meta data.
 *
 * Fires when a user edits and saves a term.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.2.0
 *
 * @param integer $term_id
 * @param integer $tt_id
 */
function genesis_term_meta_save( $term_id, $tt_id ) {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;

	$term_meta = (array) get_option( 'genesis-term-meta' );

	$term_meta[$term_id] = isset( $_POST['meta'] ) ? (array) $_POST['meta'] : array();
	if ( ! current_user_can( 'unfiltered_html' ) && isset( $term_meta[$term_id]['archive_description'] ) )
		$term_meta[$term_id]['archive_description'] = wp_kses( $term_meta[$term_id]['archive_description'], genesis_formatting_allowedtags() );

	update_option( 'genesis-term-meta', $term_meta );

}

add_action( 'delete_term', 'genesis_term_meta_delete', 10, 2 );
/**
 * Delete term meta data.
 *
 * Fires when a user deletes a term.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Term-Meta
 *
 * @since 1.2.0
 *
 * @param integer $term_id
 * @param integer $tt_id
 */
function genesis_term_meta_delete( $term_id, $tt_id ) {

	$term_meta = (array) get_option( 'genesis-term-meta' );

	unset( $term_meta[$term_id] );

	update_option( 'genesis-term-meta', (array) $term_meta );

}