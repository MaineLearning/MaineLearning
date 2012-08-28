<?php
/**
 * Controls display of theme files within Theme Editor.
 *
 * @category   Genesis
 * @package    Admin
 * @subpackage Editor
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

#add_action( 'admin_notices', 'genesis_theme_files_to_edit' );
/**
 * Remove the Genesis theme files from the Theme Editor, except when Genesis is
 * the current theme.
 *
 * @category Genesis
 * @package Admin
 * @subpackage Editor
 * @since 1.4.0
 *
 * @global array $themes Array of available themes
 * @global string $theme Name of current theme
 * @global string $current_screen Reference to current screen
 */
function genesis_theme_files_to_edit() {

	global $themes, $theme, $current_screen;

	/** Check to see if we are on the editor page */
	if ( 'theme-editor' == $current_screen->id ) {
		/** Do not change anything if we are in the Genesis theme */
		if ( $theme != 'Genesis' ) {
			/** Remove Genesis from the theme drop down list */
			unset($themes['Genesis']);

			/** Remove the Genesis files from the files lists */
			$themes[$theme]['Template Files']   = preg_grep( '|/genesis/|', $themes[$theme]['Template Files'],   PREG_GREP_INVERT );
			$themes[$theme]['Stylesheet Files'] = preg_grep( '|/genesis/|', $themes[$theme]['Stylesheet Files'], PREG_GREP_INVERT );
		}
	}

}