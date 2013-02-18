<?php
/**
 * TablePress Base Controller with members and methods for all controllers
 *
 * @package TablePress
 * @subpackage Controllers
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Base Controller class
 * @package TablePress
 * @subpackage Controllers
 * @author Tobias Bäthge
 * @since 1.0.0
 */
abstract class TablePress_Controller {

	/**
	 * Instance of the Options Model
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $model_options;

	/**
	 * Instance of the Table Model
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $model_table;

	/**
	 * File name of the admin screens's parent page in the admin menu
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $parent_page = 'middle';

	/**
	 * Whether TablePress admin screens are a top-level menu item in the admin menu
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $is_top_level_page = false;

	/**
	 * Initialize all controllers, by loading Plugin and User Options, and performing an update check
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->model_options = TablePress::load_model( 'options' );
		$this->model_table = TablePress::load_model( 'table' );

		// update check, in all controllers (frontend and admin), to make sure we always have up-to-date options
		$this->plugin_update_check(); // should be done very early

		// Admin Page Menu entry, needed for construction of plugin URLs
		$this->parent_page = apply_filters( 'tablepress_admin_menu_parent_page', $this->model_options->get( 'admin_menu_parent_page' ) );
		$this->is_top_level_page = in_array( $this->parent_page, array( 'top', 'middle', 'bottom' ), true );
	}

	/**
	 * Check if the plugin was updated and perform necessary actions, like updating the options
	 *
	 * @since 1.0.0
	 */
	protected function plugin_update_check() {
		// First activation or plugin update
		$current_plugin_options_db_version = $this->model_options->get( 'plugin_options_db_version' );
		if ( $current_plugin_options_db_version < TablePress::db_version ) {
			// Allow more PHP execution time for update process
			@set_time_limit( 300 );

			// Add TablePress capabilities to the WP_Roles objects, for new installations and all versions below 12
			if ( $current_plugin_options_db_version < 12 )
				$this->model_options->add_access_capabilities();

			if ( 0 == $this->model_options->get( 'first_activation' ) ) {
				// Save initial set of plugin options, and time of first activation of the plugin, on first activation
				$this->model_options->update( array(
					'first_activation' => current_time( 'timestamp' ),
					'plugin_options_db_version' => TablePress::db_version
				) );
			} else {
				// Update Plugin Options Options, if necessary
				$this->model_options->merge_plugin_options_defaults();
				$this->model_options->update( array(
					'plugin_options_db_version' => TablePress::db_version,
					'prev_tablepress_version' => $this->model_options->get( 'tablepress_version' ),
					'tablepress_version' => TablePress::version,
					'message_plugin_update' => true
				) );

				// Clear table caches
				if ( $current_plugin_options_db_version < 16 )
					$this->model_table->invalidate_table_output_caches_tp09(); // for pre-0.9-RC, where the arrays are serialized and not JSON encoded
				else
					$this->model_table->invalidate_table_output_caches(); // for 0.9-RC and onwards
			}

			$this->model_options->update( array(
				'message_plugin_update_content' => $this->model_options->plugin_update_message( $this->model_options->get( 'prev_tablepress_version' ), TablePress::version, get_locale() )
			) );
		}

		// Maybe update the table scheme in each existing table, independently from updating the plugin options
		if ( $this->model_options->get( 'table_scheme_db_version' ) < TablePress::table_scheme_version ) {
			// Convert parameter "datatables_scrollX" to "datatables_scrollx", has to be done before merge_table_options_defaults() is called!
			if ( $this->model_options->get( 'table_scheme_db_version' ) < 3 )
				$this->model_table->merge_table_options_tp08();

			$this->model_table->merge_table_options_defaults();

			// Merge print_name/print_description changes made for 0.6-beta
			if ( $this->model_options->get( 'table_scheme_db_version' ) < 2 )
				$this->model_table->merge_table_options_tp06();

			$this->model_options->update( array(
				'table_scheme_db_version' => TablePress::table_scheme_version
			) );
		}

		// Update User Options, if necessary
		// User Options are not saved in DB until first change occurs
		if ( is_user_logged_in() && ( $this->model_options->get( 'user_options_db_version' ) < TablePress::db_version ) ) {
			$this->model_options->merge_user_options_defaults();
			$this->model_options->update( array(
				'user_options_db_version' => TablePress::db_version
			) );
		}
	}

} // class TablePress_Controller