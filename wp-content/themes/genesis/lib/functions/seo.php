<?php
/**
 * Checks for the existence of 3rd party SEO plugins, and disables the Genesis
 * SEO features if they are present.
 *
 * @category   Genesis
 * @package    SEO
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

/**
 * Disables the Genesis SEO features.
 *
 * @since 1.6.0
 *
 * @uses GENESIS_SEO_SETTINGS_FIELD
 *
 * @see genesis_default_title()
 * @see genesis_doc_head_control()
 * @see genesis_seo_meta_description()
 * @see genesis_seo_meta_keywords()
 * @see genesis_robots_meta()
 * @see genesis_canonical()
 * @see genesis_add_inpost_seo_box()
 * @see genesis_add_inpost_seo_save()
 * @see genesis_add_taxonomy_seo_options()
 * @see genesis_user_seo_fields()
 */
function genesis_disable_seo() {

	remove_filter( 'wp_title', 'genesis_default_title', 10, 3 );
	remove_action( 'get_header', 'genesis_doc_head_control' );
	remove_action( 'genesis_meta','genesis_seo_meta_description' );
	remove_action( 'genesis_meta','genesis_seo_meta_keywords' );
	remove_action( 'genesis_meta','genesis_robots_meta' );
	remove_action( 'wp_head','genesis_canonical', 5 );

	remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );
	remove_action( 'save_post', 'genesis_inpost_seo_save', 1, 2 );

	remove_action( 'admin_init', 'genesis_add_taxonomy_seo_options' );

	remove_action( 'show_user_profile', 'genesis_user_seo_fields' );
	remove_action( 'edit_user_profile', 'genesis_user_seo_fields' );

	remove_theme_support( 'genesis-seo-settings-menu' );
	add_filter( 'pre_option_' . GENESIS_SEO_SETTINGS_FIELD, '__return_empty_array' );
	
	define( 'GENESIS_SEO_DISABLED', true );

}

/**
 * Detects whether or not Genesis SEO has been disabled.
 *
 * @since 1.8.0
 *
 * @uses GENESIS_SEO_DISABLED
 *
 * @return bool
 */
function genesis_seo_disabled() {
	
	if ( defined( 'GENESIS_SEO_DISABLED' ) && GENESIS_SEO_DISABLED )
		return true;
		
	return false;
	
}

add_action( 'after_setup_theme', 'genesis_seo_compatibility_check', 5 );
/**
 * Checks for the existence of popular SEO plugins and disables
 * the Genesis SEO features if one or more of the plugins is active.
 *
 * Runs before the menu is built, so we can disable SEO Settings menu, if necessary.
 *
 * @since 1.2.0
 *
 * @uses genesis_detect_seo_plugins() Detect certain SEO plugins
 * @uses genesis_disable_seo() Disable all aspects of Genesis SEO features
 *
 * @see genesis_default_title()
 */
function genesis_seo_compatibility_check() {

	if ( genesis_detect_seo_plugins() )
		genesis_disable_seo();

	/** Disable Genesis <title> generation if SEO Title Tag is active */
	if ( function_exists( 'seo_title_tag' ) ) {
		remove_filter( 'wp_title', 'genesis_default_title', 10, 3 );
		remove_action( 'genesis_title', 'wp_title' );
		add_action( 'genesis_title', 'seo_title_tag' );
	}

}

add_action( 'admin_notices', 'genesis_scribe_nag' );
/**
 * Display admin notice for Scribe SEO Copywriting tool.
 *
 * @since 1.4.0
 *
 * @link http://scribeseo.com/
  *
 * @return null Returns early if not on the SEO Settings page, Scribe is
 * installed, or it has already been dismissed
 */
function genesis_scribe_nag() {

	if ( ! genesis_is_menu_page( 'seo-settings' ) )
		return;

	if ( class_exists( 'Ecordia' ) || get_option( 'genesis-scribe-nag-disabled' ) )
		return;

	$copy = sprintf( __( 'Have you tried our Scribe SEO software? Do keyword research, content optimization, and link building without leaving WordPress. <b>Genesis owners save over 50&#37; using the promo code FIRST when you sign up</b>. <a href="%s" target="_blank">Click here for more info</a>.', 'genesis' ), 'http://scribeseo.com/genesis-owners-only' );

	printf( '<div class="updated" style="overflow: hidden;"><p class="alignleft">%s</p> <p class="alignright"><a href="%s">%s</a></p></div>', $copy, add_query_arg( 'dismiss-scribe', 'true', menu_page_url( 'seo-settings', false ) ), __( 'Dismiss', 'genesis' ) );

}

add_action( 'admin_init', 'genesis_disable_scribe_nag' );
/**
 * Potentially disables Scribe admin notice.
 *
 * Detects a query flag, and disables the Scribe nag, then redirects the user
 * back to the SEO settings page.
 *
 * @since 1.4.0
 *
 * @uses genesis_admin_redirect() Redirect to SEO Settings page after dismissing.
 *
 * @return null Returns early if not on the SEO Settings page, or dismiss-scribe
 * querystring argument not present and set to true
 */
function genesis_disable_scribe_nag() {

	if ( ! genesis_is_menu_page( 'seo-settings' ) )
		return;

	if ( ! isset( $_REQUEST['dismiss-scribe'] ) || 'true' !== $_REQUEST['dismiss-scribe'] )
		return;

	update_option( 'genesis-scribe-nag-disabled', 1 );

	genesis_admin_redirect( 'seo-settings' );
	exit;

}

/**
 * Detect some SEO Plugin that add constants, classes or functions.
 *
 * Uses genesis_detect_seo_plugin filter to allow third party manpulation of SEO
 * plugin list.
 *
 * @since 1.6.0
 *
 * @uses genesis_detect_plugin()
 *
 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
 */
function genesis_detect_seo_plugins() {

	return genesis_detect_plugin(
		// Use this filter to adjust plugin tests.
		apply_filters(
			'genesis_detect_seo_plugins',
			/** Add to this array to add new plugin checks. */
			array(

				// Classes to detect.
				'classes' => array(
					'wpSEO',
					'All_in_One_SEO_Pack',
					'HeadSpace_Plugin',
					'Platinum_SEO_Pack',
				),

				// Functions to detect.
				'functions' => array(),

				// Constants to detect.
				'constants' => array( 'WPSEO_VERSION', ),
			)
		)
	);

}
