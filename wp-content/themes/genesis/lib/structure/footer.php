<?php
/**
 * Adds footer structure.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Footer
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );
/**
 * Echos the markup necessary to facilitate the footer widget areas.
 *
 * Checks for a numerical parameter given when adding theme support - if none is
 * found, then the function returns early.
 *
 * The child theme must style the widget areas.
 *
 * @since 1.6.0
 *
 * @return null Returns early if number of widget areas could not be determined,
 * or nothing is added to the first widget area
 */
function genesis_footer_widget_areas() {

	$footer_widgets = get_theme_support( 'genesis-footer-widgets' );

	if ( ! $footer_widgets || ! isset( $footer_widgets[0] ) || ! is_numeric( $footer_widgets[0] ) )
		return;

	$footer_widgets = (int) $footer_widgets[0];

	/**
	 * Check to see if first widget area has widgets. If not,
	 * do nothing. No need to check all footer widget areas.
	 */
	if ( ! is_active_sidebar( 'footer-1' ) )
		return;

	$output  = '';
	$counter = 1;

	while ( $counter <= $footer_widgets ) {
		/** Darn you, WordPress! Gotta output buffer. */
		ob_start();
		dynamic_sidebar( 'footer-' . $counter );
		$widgets = ob_get_clean();

		$output .= sprintf( '<div class="footer-widgets-%d widget-area">%s</div>', $counter, $widgets );

		$counter++;
	}

	echo apply_filters( 'genesis_footer_widget_areas', sprintf( '<div id="footer-widgets" class="footer-widgets">%2$s%1$s%3$s</div>', $output, genesis_structural_wrap( 'footer-widgets', 'open', 0 ), genesis_structural_wrap( 'footer-widgets', 'close', 0 ) ) );

}

add_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
/**
 * Echo the opening div tag for the footer.
 *
 * @since 1.2.0
 *
 * @uses genesis_structural_wrap() Maybe add opening .wrap div tag
 */
function genesis_footer_markup_open() {

	echo '<div id="footer" class="footer">';
	genesis_structural_wrap( 'footer', 'open' );

}

add_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );
/**
 * Echo the closing div tag for the footer.
 *
 * @since 1.2.0
 *
 * @uses genesis_structural_wrap() Maybe add closing .wrap div tag
 */
function genesis_footer_markup_close() {

	genesis_structural_wrap( 'footer', 'close' );
	echo '</div><!-- end #footer -->' . "\n";

}

add_filter( 'genesis_footer_output', 'do_shortcode', 20 );
add_action( 'genesis_footer', 'genesis_do_footer' );
/**
 * Echo the contents of the footer.
 *
 * Execute any shortcodes that might be present.
 *
 * Several filters are applied here, which can be used for customising the output:
 *   genesis_footer_backtotop_text,
 *   genesis_footer_creds_text,
 *   genesis_footer_output.
 *
 * @since 1.0.1
 *
 * @uses g_ent() Pass entities through a filter
 */
function genesis_do_footer() {

	/** Build the text strings. Includes shortcodes */
	$backtotop_text = '[footer_backtotop]';
	$creds_text     = sprintf( '[footer_copyright before="%s "] &middot; [footer_childtheme_link before="" after=" %s"] [footer_genesis_link url="http://www.studiopress.com/" before=""] &middot; [footer_wordpress_link] &middot; [footer_loginout]', __( 'Copyright', 'genesis' ), __( 'on', 'genesis' ) );

	/** Filter the text strings */
	$backtotop_text = apply_filters( 'genesis_footer_backtotop_text', $backtotop_text );
	$creds_text     = apply_filters( 'genesis_footer_creds_text', $creds_text );

	$backtotop = $backtotop_text ? sprintf( '<div class="gototop"><p>%s</p></div>', $backtotop_text ) : '';
	$creds     = $creds_text ? sprintf( '<div class="creds"><p>%s</p></div>', g_ent( $creds_text ) ) : '';

	$output = $backtotop . $creds;

	echo apply_filters( 'genesis_footer_output', $output, $backtotop_text, $creds_text );

}

add_filter( 'genesis_footer_scripts', 'do_shortcode' );
add_action( 'wp_footer', 'genesis_footer_scripts' );
/**
 * Echo the footer scripts, defined in Theme Settings.
 *
 * Applies the 'genesis_footer_scripts filter to the value returns from the
 * footer_scripts option.
 *
 * @since 1.1.0
 *
 * @uses genesis_option()
 */
function genesis_footer_scripts() {

	echo apply_filters( 'genesis_footer_scripts', genesis_option( 'footer_scripts' ) );

}