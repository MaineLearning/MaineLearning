<?php
/**
 * Controls the adding of scripts to the front-end and admin.
 *
 * @category Genesis
 * @package  Scripts-Styles
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

add_action( 'wp_enqueue_scripts', 'genesis_load_scripts' );
/**
 * Enqueue the scripts used on the front-end of the site.
 *
 * Includes comment-reply, superfish and the superfish arguments.
 *
 * @since 0.2.0
 */
function genesis_load_scripts() {

	/** If a single post or page, threaded comments are enabled, and comments are open */
	if ( is_singular() && get_option( 'thread_comments' ) && comments_open() )
		wp_enqueue_script( 'comment-reply' );

	/** If a superfish option is enabled or showing a menu of any type, load superfish and the arguments for it in the footer */
	if ( genesis_get_option( 'nav_superfish' ) || genesis_get_option( 'subnav_superfish' ) ||
		is_active_widget( 0, 0, 'menu-categories' ) || is_active_widget( 0, 0, 'menu-pages' ) || is_active_widget( 0, 0, 'nav_menu' ) ) {
			wp_enqueue_script( 'superfish', GENESIS_JS_URL . '/menu/superfish.js', array( 'jquery' ), '1.4.8', true );
			wp_enqueue_script( 'superfish-args', GENESIS_JS_URL . '/menu/superfish.args.js', array( 'superfish' ), PARENT_THEME_VERSION, true );
	}

}

add_action( 'admin_enqueue_scripts', 'genesis_load_admin_scripts' );
/**
 * Conditionally enqueues the scripts used in the admin.
 *
 * Includes Thickbox, theme preview and a Genesis script (actually enqueued
 * in genesis_load_admin_js()).
 *
 * @since 0.2.3
 *
 * @uses genesis_load_admin_js()
 * @uses genesis_is_menu_page()
 * @uses genesis_update_check()
 * @uses genesis_seo_disabled()
 *
 * @global stdClass $post Post object
 *
 * @param string $hook_suffix Admin page identifier.
 */
function genesis_load_admin_scripts( $hook_suffix ) {

	/** Only add thickbox/preview if there is an update to Genesis available */
	if ( genesis_update_check() ) {
		add_thickbox();
		wp_enqueue_script( 'theme-preview' );
		genesis_load_admin_js();
	}

	/** If we're on a Genesis admin screen */
	if ( genesis_is_menu_page( 'genesis' ) || genesis_is_menu_page( 'seo-settings' ) || genesis_is_menu_page( 'design-settings' ) )
		genesis_load_admin_js();

	global $post;

	/** If we're viewing an edit post page, make sure we need Genesis SEO JS */
	if ( 'post-new.php' == $hook_suffix || 'post.php' == $hook_suffix ) {
		if ( ! genesis_seo_disabled() && post_type_supports( $post->post_type, 'genesis-seo' ) )
			genesis_load_admin_js();
	}

}

/**
 * Enqueues the custom script used in the admin, and localizes several strings or
 * values used in the scripts.
 *
 * @since 1.8.0
 */
function genesis_load_admin_js() {

	wp_enqueue_script( 'genesis_admin_js', GENESIS_JS_URL . '/admin.js', array( 'jquery' ), PARENT_THEME_VERSION, true );
	$strings = array(
		'category_checklist_toggle' => __( 'Select / Deselect All', 'genesis' )
	);
	wp_localize_script( 'genesis_admin_js', 'genesisL10n', $strings );

	$toggles = array(
		'update'                    => array( '#genesis-settings\\[update\\]', '#genesis_update_notification_setting', null ),
		'nav'                       => array( '#genesis-settings\\[nav\\]', '#genesis_nav_settings', null ),
		'subnav'                    => array( '#genesis-settings\\[subnav\\]', '#genesis_subnav_settings', null ),
		'content_archive_thumbnail' => array( '#genesis-settings\\[content_archive_thumbnail\\]', '#genesis_image_size', null ),
		'nav_extras_enable'         => array( '#genesis-settings\\[nav_extras_enable\\]', '#genesis_nav_extras_settings', null ),
		// Select toggles
		'nav_extras'                => array( '#genesis-settings\\[nav_extras\\]', '#genesis_nav_extras_twitter', 'twitter' ),
		'content_archive'           => array( '#genesis-settings\\[content_archive\\]', '#genesis_content_limit_setting', 'full' ),
	);
	wp_localize_script( 'genesis_admin_js', 'genesis_toggles', apply_filters( 'genesis_toggles', $toggles ) );

}