<?php
/**
 * Controls the Genesis admin menu.
 *
 * @category   Genesis
 * @package    Admin
 * @subpackage Menu
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'after_setup_theme', 'genesis_add_admin_menu' );
/**
 * Adds Genesis top-level item in admin menu.
 *
 * Calls the genesis_admin_menu hook at the end - all submenu items should be
 * attached to that hook to ensure correct ordering.
 *
 * @since 0.2.0
 *
 * @return null Returns null if Genesis menu is disabled, or disabled for current user
 */
function genesis_add_admin_menu() {

	/** Do nothing, if not viewing the admin */
	if ( ! is_admin() )
		return;

	global $_genesis_admin_settings;

	/** Don't add menu item if programatically disabled */
	if ( ! current_theme_supports( 'genesis-admin-menu' ) )
		return;

	/** Don't add menu item if disabled for current user */
	$user = wp_get_current_user();
	if ( ! get_the_author_meta( 'genesis_admin_menu', $user->ID ) )
		return;

	$_genesis_admin_settings = new Genesis_Admin_Settings;

	/** set the old global pagehook var for backward compatibility */
	global $_genesis_theme_settings_pagehook;
	$_genesis_theme_settings_pagehook = $_genesis_admin_settings->pagehook;

	/** Hook here to create submenus */
	do_action( 'genesis_admin_menu' );

}

add_action( 'genesis_admin_menu', 'genesis_add_admin_submenus' );
/**
 * Adds submenu items under Genesis item in admin menu.
 *
 * @since 0.2.0
 *
 * @see Genesis_Admin_SEO_Settings SEO Settings class
 * @see Genesis_Admin_Import_export Import / Export class
 * @see Genesis_Admin_Readme Readme class
 *
 * @global string $_genesis_admin_seo_settings
 * @global string $_genesis_admin_import_export
 * @global string $_genesis_admin_readme
 * @return null Returns null if Genesis menu is disabled
 */
function genesis_add_admin_submenus() {

	/** Do nothing, if not viewing the admin */
	if ( ! is_admin() )
		return;

	global $_genesis_admin_seo_settings, $_genesis_admin_import_export, $_genesis_admin_readme;

	/** Don't add submenu items if Genesis menu is disabled */
	if( ! current_theme_supports( 'genesis-admin-menu' ) )
		return;

	$user = wp_get_current_user();

	/** Add "SEO Settings" submenu item */
	if ( current_theme_supports( 'genesis-seo-settings-menu' ) && get_the_author_meta( 'genesis_seo_settings_menu', $user->ID ) ) {
		$_genesis_admin_seo_settings = new Genesis_Admin_SEO_Settings;

		/** set the old global pagehook var for backward compatibility */
		global $_genesis_seo_settings_pagehook;
		$_genesis_seo_settings_pagehook = $_genesis_admin_seo_settings->pagehook;
	}

	/** Add "Import/Export" submenu item */
	if ( current_theme_supports( 'genesis-import-export-menu' ) && get_the_author_meta( 'genesis_import_export_menu', $user->ID ) )
		$_genesis_admin_import_export = new Genesis_Admin_Import_Export;

	/** Add README file submenu item, if it exists */
	if ( current_theme_supports( 'genesis-readme-menu' ) && file_exists( CHILD_DIR . '/README.txt' ) )
		$_genesis_admin_readme = new Genesis_Admin_Readme;

}