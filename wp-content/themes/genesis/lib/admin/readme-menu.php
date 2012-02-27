<?php
/**
 * Displays the contents of the README file.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the Readme page.
 *
 * Although this class was added in 1.8.0, some of the methods were originally
 * standalone functions added in previous versions of Genesis.
 *
 * @category Genesis
 * @package Admin
 *
 * @since 1.8.0
 */
class Genesis_Admin_Readme extends Genesis_Admin_Basic {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @uses Genesis_Admin::create() Register the admin page
	 *
	 * @since 1.8.0
	 */
	function __construct() {

		$page_id = 'genesis-readme';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'genesis',
				'page_title'  => __( 'README', 'genesis' ),
				'menu_title'  => __( 'README', 'genesis' )
			)
		);

		$this->create( $page_id, $menu_ops );

	}

	/**
	 * Callback for displaying the Genesis Readme admin page.
	 *
	 * Checks if the file contents are readable, and echoes out HTML.
	 *
	 * @since 1.3.0
	 *
	 * @uses CHILD_DIR
	 */
	public function admin() {

		/** Assume we cannot find the file */
		$file = false;

		/** Get the file contents */
		$file = @file_get_contents( CHILD_DIR . '/README.txt' );

		/** If we can't find file contents, show a message */
		if ( ! $file || empty( $file ) )
			$file = '<div class="error"><p>' . sprintf( __( 'The %s file was not found in the child theme, or it was empty.', 'genesis' ), '<code>README.txt</code>' ) . '</p></div>';
		?>
		<div id="genesis-readme-file" class="wrap">
			<?php screen_icon( 'edit-pages' ); ?>
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php echo wpautop( $file ); ?>
		</div>
		<?php

	}

}