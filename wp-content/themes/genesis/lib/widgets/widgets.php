<?php
/**
 * Handles including the widget class files, and registering the widgets in
 * WordPress.
 *
 * @category Genesis
 * @package  Widgets
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/** Include widget class files */
require_once( GENESIS_WIDGETS_DIR . '/user-profile-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/enews-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/featured-post-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/featured-page-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/latest-tweets-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/menu-pages-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/menu-categories-widget.php' );

add_action( 'widgets_init', 'genesis_load_widgets' );
/**
 * Register widgets for use in the Genesis theme.
 *
 * @category Genesis
 * @package Widgets
 *
 * @since 1.7.0
 */
function genesis_load_widgets() {

	register_widget( 'Genesis_eNews_Updates' );
	register_widget( 'Genesis_Featured_Page' );
	register_widget( 'Genesis_Featured_Post' );
	register_widget( 'Genesis_Latest_Tweets_Widget' );
	register_widget( 'Genesis_Widget_Menu_Categories' );
	register_widget( 'Genesis_Menu_Pages_Widget' );
	register_widget( 'Genesis_User_Profile_Widget' );

}

add_action( 'load-themes.php', 'genesis_remove_default_widgets_from_header_right' );
/**
 * Temporary function to work around the default widgets that get added to
 * Header Right when switching themes.
 *
 * The $defaults array contains a list of the IDs of the widgets that are added
 * to the first sidebar in a new default install. If this exactly matches the
 * widgets in Header Right after switching themes, then they are removed.
 *
 * This works around a perceived WP problem for new installs.
 *
 * If a user amends the list of widgets in the first sidebar before switching to
 * a Genesis child theme, then this function won't do anything.
 *
 * @since 1.8.0
 *
 * @return null Return early if not just switched to a new theme.
 */
function genesis_remove_default_widgets_from_header_right() {

	/** Some tomfoolery for a faux activation hook */
	if ( ! isset( $_REQUEST['activated'] ) || 'true' != $_REQUEST['activated'] )
		return;

	$widgets  = get_option( 'sidebars_widgets' );
	$defaults = array( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2', );

	if ( $defaults == $widgets['header-right'] ) {
		$widgets['header-right'] = array();
		update_option( 'sidebars_widgets', $widgets );
	}

}