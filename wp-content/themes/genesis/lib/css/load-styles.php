<?php
/**
 * Controls the adding of style sheets.
 *
 * @category Genesis
 * @package  Scripts-Styles
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

add_action( 'admin_print_styles', 'genesis_load_admin_styles' );
/**
 * Enqueue Genesis admin styles.
 *
 * @category Genesis
 * @package Scripts-Styles
 *
 * @since 0.2.3
 */
function genesis_load_admin_styles() {

	wp_enqueue_style( 'genesis_admin_css', GENESIS_CSS_URL . '/admin.css', array(), PARENT_THEME_VERSION );

}