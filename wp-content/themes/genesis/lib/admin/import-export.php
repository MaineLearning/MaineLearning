<?php
/**
 * Controls the Import / Export functions of the Genesis Framework.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the Import / Export page.
 *
 * Although this class was added in 1.8.0, some of the methods were originally
 * standalone functions added in previous versions of Genesis.
 *
 * @category Genesis
 * @package Admin
 *
 * @since 1.8.0
 */
class Genesis_Admin_Import_Export extends Genesis_Admin_Basic {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * Also hooks in the handling of file imports and exports.
	 *
	 * @since 1.8.0
	 *
	 * @uses Genesis_Admin::create() Register the admin page
	 *
	 * @see Genesis_Admin_Import_Export::export() Handle settings file exports
	 * @see Genesis_Admin_Import_Export::import() Handle settings file imports
	 */
	public function __construct() {

		$page_id = 'genesis-import-export';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'genesis',
				'page_title'  => __( 'Genesis - Import/Export', 'genesis' ),
				'menu_title'  => __( 'Import/Export', 'genesis' )
			)
		);

		$this->create( $page_id, $menu_ops );

		add_action( 'admin_init', array( $this, 'export' ) );
		add_action( 'admin_init', array( $this, 'import' ) );

	}

	/**
	 * Callback for displaying the Genesis Import / Export admin page.
	 *
	 * Echoes out HTML.
	 *
	 * Calls the genesis_import_export_form action after the last default table
	 * row.
	 *
	 * @since 1.4.0
	 *
	 * @uses Genesis_Admin_Import_Export::export_checkboxes() Echo export checkboxes
	 * @uses Genesis_Admin_Import_Export::get_export_options() Get array of export options
	 */
	public function admin() {

		?>
		<div class="wrap">
			<?php screen_icon( 'tools' ); ?>
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

			<table class="form-table">
				<tbody>

					<tr>
						<th scope="row"><b><?php _e( 'Import Genesis Settings File', 'genesis' ); ?></p></th>
						<td>
							<p><?php _e( 'Upload the data file (<code>.json</code>) from your computer and we\'ll import your settings.', 'genesis' ); ?></p>
							<p><?php _e( 'Choose the file from your computer and click "Upload file and Import"', 'genesis' ); ?></p>
							<p>
								<form enctype="multipart/form-data" method="post" action="<?php echo menu_page_url( 'genesis-import-export', 0 ); ?>">
									<?php wp_nonce_field( 'genesis-import' ); ?>
									<input type="hidden" name="genesis-import" value="1" />
									<label for="genesis-import-upload"><?php sprintf( __( 'Upload File: (Maximum Size: %s)', 'genesis' ), ini_get( 'post_max_size' ) ); ?></label>
									<input type="file" id="genesis-import-upload" name="genesis-import-upload" size="25" />
									<?php
									submit_button( __( 'Upload File and Import', 'genesis' ), 'primary', 'upload', false );
									?>
								</form>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><b><?php _e( 'Export Genesis Settings File', 'genesis' ); ?></b></th>
						<td>
							<p><?php _e( 'When you click the button below, Genesis will generate a data file (<code>.json</code>) for you to save to your computer.', 'genesis' ); ?></p>
							<p><?php _e( 'Once you have saved the download file, you can use the import function on another site to import this data.', 'genesis' ); ?></p>
							<p>
								<form method="post" action="<?php echo menu_page_url( 'genesis-import-export', 0 ); ?>">
									<?php
									wp_nonce_field( 'genesis-export' );
									$this->export_checkboxes();
									if ( $this->get_export_options() )
										submit_button( __( 'Download Export File', 'genesis' ), 'primary', 'download' );
									?>
								</form>
							</p>
						</td>
					</tr>

					<?php do_action( 'genesis_import_export_form' ); ?>

				</tbody>
			</table>

		</div>
		<?php

	}

	/**
	 * Add custom notices that display when you successfully import or export the settings.
	 *
	 * @since 1.4.0
	 *
	 * @uses genesis_is_menu_page() Check if we're on a Genesis page
	 *
	 * @return null Returns null if not on the correct admin page.
	 */
	public function notices() {

		if ( ! genesis_is_menu_page( 'genesis-import-export' ) )
			return;

		if ( isset( $_REQUEST['imported'] ) && 'true' == $_REQUEST['imported'] )
			echo '<div id="message" class="updated"><p><strong>' . __( 'Settings successfully imported.', 'genesis' ) . '</strong></p></div>';
		elseif ( isset( $_REQUEST['error'] ) && 'true' == $_REQUEST['error'] )
			echo '<div id="message" class="error"><p><strong>' . __( 'There was a problem importing your settings. Please try again.', 'genesis' ) . '</strong></p></div>';

	}

	/**
	 * Return array of export options and their arguments.
	 *
	 * Plugins and themes can hook into the genesis_export_options filter to add
	 * their own settings to the exporter.
	 *
	 * @since 1.6.0
	 *
	 * @return array Export options
	 */
	protected function get_export_options() {

		$options = array(
			'theme' => array(
				'label'          => __( 'Theme Settings', 'genesis' ),
				'settings-field' => GENESIS_SETTINGS_FIELD,
			),
			'seo' => array(
				'label' => __( 'SEO Settings', 'genesis' ),
				'settings-field' => GENESIS_SEO_SETTINGS_FIELD,
			)
		);

		return (array) apply_filters( 'genesis_export_options', $options );

	}

	/**
	 * Echo out the checkboxes for the export options.
	 *
	 * @since 1.6.0
	 *
	 * @uses Genesis_Admin_Import_Export::get_export_options() Get array of export options
	 *
	 * @return null Returns null if there are no options to export
	 */
	protected function export_checkboxes() {

		if ( ! $options = $this->get_export_options() ) {
			/** Not even the Genesis theme / seo export options were returned from the filter */
			printf( '<p><em>%s</em></p>', __( 'No export options available.', 'genesis' ) );
			return;
		}

		foreach ( $options as $name => $args ) {
			/** Ensure option item has an array key, and that label and settings-field appear populated */
			if ( is_int( $name ) || ! isset( $args['label'] ) || ! isset( $args['settings-field'] ) || '' === $args['label'] || '' === $args['settings-field'] )
				return;

			echo '<p><input id="genesis-export-' . esc_attr( $name ) . '" name="genesis-export[' . esc_attr( $name ) . ']" type="checkbox" value="1" /> ';
			echo '<label for="genesis-export-' . esc_attr( $name ) . '">' . esc_html( $args['label'] ) . '</label></p>' . "\n";
		}

	}

	/**
	 * Generate the export file, if requested, in JSON format.
	 *
	 * After checking we're on the right page, and trying to export, loop through
	 * the list of requested options to export, grabbing the settings from the
	 * database, and building up a file name that represents that collection of
	 * settings.
	 *
	 * A .json file is then sent to the browser, named with "genesis" at the start
	 * and ending with the current date-time.
	 *
	 * The genesis_export action is fired after checking we can proceed, but
	 * before the array of export options are retrieved.
	 *
	 * @since 1.4.0
	 *
	 * @uses genesis_is_admin_page() Check if we're on a Genesis page
	 * @uses Genesis_Admin_Import_Export::get_export_options() Get array of export options
	 *
	 * @return null Returns null if not correct page, or we're not exporting
	 */
	public function export() {

		/** Check we're on the Import / Export page */
		if ( ! genesis_is_menu_page( 'genesis-import-export' ) )
			return;

		/** Check we're trying to export */
		if ( empty( $_REQUEST['genesis-export'] ) )
			return;

		/** Verify nonce */
		check_admin_referer( 'genesis-export' );

		/** Hookable */
		do_action( 'genesis_export', $_REQUEST['genesis-export'] );

		/** Get array of available options that can be exported */
		$options = $this->get_export_options();

		$settings = array();

		/** Exported file name always starts with "genesis" */
		$prefix = array( 'genesis' );

		/** Loop through set(s) of options */
		foreach ( (array) $_REQUEST['genesis-export'] as $export => $value ) {
			/** Grab settings field name (key) */
			$settings_field = $options[$export]['settings-field'];

			/** Grab all of the settings from the database under that key */
			$settings[$settings_field] = get_option( $settings_field );

			/* Add name of option set to build up export file name */
			$prefix[] = $export;
		}

		/** Check there's something to export */
		if ( ! $settings )
			return;

		/** Complete the export file name by joining parts together */
		$prefix = join( '-', $prefix );

	    $output = json_encode( (array) $settings );

		/** Prepare and send the export file to the browser */
	    header( 'Content-Description: File Transfer' );
	    header( 'Cache-Control: public, must-revalidate' );
	    header( 'Pragma: hack' );
	    header( 'Content-Type: text/plain' );
	    header( 'Content-Disposition: attachment; filename="' . $prefix . '-' . date( 'Ymd-His' ) . '.json"' );
	    header( 'Content-Length: ' . strlen( $output ) );
	    echo $output;
	    exit;

	}

	/**
	 * Handles the imported file.
	 *
	 * Upon upload, the file contents are JSON-decoded. If there were errors, or no
	 * options to import, then reload the page to show an error message.
	 *
	 * Otherwise, loop through the array of option sets, and update the data under
	 * those keys in the database. Afterwards, reload the page with a success message.
	 *
	 * Calls genesis_import action is fired after checking we can proceed, but
	 * before attempting to extract the contents from the uploaded file.
	 *
	 * @since 1.4.0
	 *
	 * @uses genesis_is_admin_page() Check if we're on a Genesis page
	 * @uses genesis_admin_redirect() Redirect user to an admin page
	 *
	 * @return null Returns null if not correct admin page, we're not importing
	 */
	public function import() {

		/** Check we're on the Import / Export page */
		if ( ! genesis_is_menu_page( 'genesis-import-export' ) )
			return;

		/** Check we're trying to import */
		if ( empty( $_REQUEST['genesis-import'] ) )
			return;

		/** Verify nonce */
		check_admin_referer( 'genesis-import' );

		/** Hookable */
		do_action( 'genesis_import', $_REQUEST['genesis-import'], $_FILES['genesis-import-upload'] );

		/** Extract file contents */
		$upload = file_get_contents( $_FILES['genesis-import-upload']['tmp_name'] );

		/** Decode the JSON */
		$options = json_decode( $upload, true );

		/** Check for errors */
		if ( ! $options || $_FILES['genesis-import-upload']['error'] ) {
			genesis_admin_redirect( 'genesis-import-export', array( 'error' => 'true' ) );
			exit;
		}

		/** Cycle through data, import settings */
		foreach ( (array) $options as $key => $settings )
			update_option( $key, $settings );

		/** Redirect, add success flag to the URI */
		genesis_admin_redirect( 'genesis-import-export', array( 'imported' => 'true' ) );
		exit;

	}

}