<?php
/**
 * Controls output elements in menus.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Menus
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'after_setup_theme', 'genesis_register_nav_menus' );
/**
 * Registers the custom menu locations, if theme has support for them.
 *
 * @since 1.8.0
 *
 * @return null Returns early if no Genesis menus are supported.
 */
function genesis_register_nav_menus() {

	if ( ! current_theme_supports( 'genesis-menus' ) )
		return;

	$menus = get_theme_support( 'genesis-menus' );

	/** Register supported menus */
	foreach ( (array) $menus[0] as $id => $name ) {
		register_nav_menu( $id , $name );
	}

	do_action( 'genesis_register_nav_menus' );

}

add_action( 'genesis_after_header', 'genesis_do_nav' );
/**
 * Echoes the "Primary Navigation" menu.
 *
 * The preferred option for creating menus is the Custom Menus feature in
 * WordPress. There is also a fallback to using the Genesis wrapper functions
 * for creating a menu of Pages, or a menu of Categories (maintained only for
 * backwards compatibility).
 *
 * Either output can be filtered via 'genesis_do_nav'.
 *
 * @since 1.0.0
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses genesis_nav() Use old-style Genesis Pages or Categories menu
 * @uses genesis_structural_wrap() Adds optional internal wrap divs
 */
function genesis_do_nav() {

	/** Do nothing if menu not supported */
	if ( ! genesis_nav_menu_supported( 'primary' ) )
		return;

	/** If menu is assigned to theme location, output */
	if ( has_nav_menu( 'primary' ) ) {

		$args = array(
			'theme_location' => 'primary',
			'container'      => '',
			'menu_class'     => genesis_get_option( 'nav_superfish' ) ? 'menu genesis-nav-menu menu-primary superfish' : 'menu genesis-nav-menu menu-primary',
			'echo'           => 0,
		);

		$nav = wp_nav_menu( $args );

		$pattern = genesis_markup( '<nav class="primary">%2$s%1$s%3$s</nav>', '<div id="nav">%2$s%1$s%3$s</div>', 0 );

		$nav_output = sprintf( $pattern, $nav, genesis_structural_wrap( 'nav', 'open', 0 ), genesis_structural_wrap( 'nav', 'close', 0 ) );

		echo apply_filters( 'genesis_do_nav', $nav_output, $nav, $args );

	}

}

add_action( 'genesis_after_header', 'genesis_do_subnav' );
/**
 * Echoes the "Secondary Navigation" menu.
 *
 * The preferred option for creating menus is the Custom Menus feature in
 * WordPress. There is also a fallback to using the Genesis wrapper functions
 * for creating a menu of Pages, or a menu of Categories (maintained only for
 * backwards compatibility).
 *
 * Either output can be filtered via 'genesis_do_subnav'.
 *
 * @since 1.0.0
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses genesis_nav() Use old-style Genesis Pages or Categories menu
 * @uses genesis_structural_wrap() Adds optional internal wrap divs
 */
function genesis_do_subnav() {

	/** Do nothing if menu not supported */
	if ( ! genesis_nav_menu_supported( 'secondary' ) )
		return;

	/** If menu is assigned to theme location, output */
	if ( has_nav_menu( 'secondary' ) ) {

		$args = array(
			'theme_location' => 'secondary',
			'container'      => '',
			'menu_class'     => genesis_get_option( 'subnav_superfish' ) ? 'menu genesis-nav-menu menu-secondary superfish' : 'menu genesis-nav-menu menu-secondary',
			'echo'           => 0,
		);

		$subnav = wp_nav_menu( $args );

		$pattern = genesis_markup( '<nav class="secondary">%2$s%1$s%3$s</nav>', '<div id="subnav">%2$s%1$s%3$s</div>', 0 );

		$subnav_output = sprintf( $pattern, $subnav, genesis_structural_wrap( 'subnav', 'open', 0 ), genesis_structural_wrap( 'subnav', 'close', 0 ) );

		echo apply_filters( 'genesis_do_subnav', $subnav_output, $subnav, $args );

	}

}

add_filter( 'wp_nav_menu_items', 'genesis_nav_right', 10, 2 );
/**
 * Filters the Primary Navigation menu items, appending either RSS links,
 * search form, twitter link, or today's date.
 *
 * @since 1.0.0
 *
 * @uses genesis_get_option() Get navigation extras settings
 *
 * @param string $menu HTML string of list items
 * @param array $args Menu arguments
 * @return string Amended HTML string of list items
 */
function genesis_nav_right( $menu, $args ) {

	$args = (array) $args;

	if ( ! genesis_get_option( 'nav_extras_enable' ) || 'primary' != $args['theme_location'] )
		return $menu;

	switch ( genesis_get_option( 'nav_extras' ) ) {
		case 'rss':
			$rss   = '<a rel="nofollow" href="' . get_bloginfo( 'rss2_url' ) . '">' . __( 'Posts', 'genesis' ) . '</a>';
			$rss  .= '<a rel="nofollow" href="' . get_bloginfo( 'comments_rss2_url' ) . '">' . __( 'Comments', 'genesis' ) . '</a>';
			$menu .= '<li class="right rss">' . $rss . '</li>';
			break;
		case 'search':
			// I hate output buffering, but I have no choice
			ob_start();
			get_search_form();
			$search = ob_get_clean();
			$menu  .= '<li class="right search">' . $search . '</li>';
			break;
		case 'twitter':
			$menu .= sprintf( '<li class="right twitter"><a href="%s">%s</a></li>', esc_url( 'http://twitter.com/' . genesis_get_option( 'nav_extras_twitter_id' ) ), esc_html( genesis_get_option( 'nav_extras_twitter_text' ) ) );
			break;
		case 'date':
			$menu .= '<li class="right date">' . date_i18n( get_option( 'date_format' ) ) . '</li>';
			break;
	}

	return $menu;

}