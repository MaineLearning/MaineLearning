<?php
/**
 * Admin Controller for TablePress with the functionality for the non-AJAX backend
 *
 * @package TablePress
 * @subpackage Controllers
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Admin Controller class, extends Base Controller Class
 * @package TablePress
 * @subpackage Controllers
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class TablePress_Admin_Controller extends TablePress_Controller {

	/**
	 * Page hooks (i.e. names) WordPress uses for the TablePress admin screens,
	 * populated in add_admin_menu_entry()
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $page_hooks = array();

	/**
	 * Actions that have a view and admin menu or nav tab menu entry
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $view_actions = array();

	/**
	 * Boolean to record whether language support has been loaded (to prevent to do it twice)
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $i18n_support_loaded = false;

	/**
	 * Initialize the Admin Controller, determine location the admin menu, set up actions
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		// handler for changing the number of shown tables in the list of tables (via WP List Table class)
		add_filter( 'set-screen-option', array( $this, 'save_list_tables_screen_option' ), 10, 3 );

		add_action( 'admin_menu', array( $this, 'add_admin_menu_entry' ) );
		add_action( 'admin_init', array( $this, 'add_admin_actions' ) );
	}

	/**
	 * Handler for changing the number of shown tables in the list of tables (via WP List Table class)
	 *
	 * @since 1.0.0
	 *
	 * @param bool $false Current value of the filter (probably bool false)
	 * @param string $option Option in which the setting is stored
	 * @param int $value Current value of the setting
	 * @return bool|int False to not save the changed setting, or the int value to be saved
	 */
	public function save_list_tables_screen_option( $false, $option, $value ) {
		if ( 'tablepress_list_per_page' == $option )
			return $value;
		else
			return $false;
	}

	/**
	 * Add admin screens to the correct place in the admin menu
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu_entry() {
		// for all menu entries:
		$callback = array( $this, 'show_admin_page' );
		$admin_menu_entry_name = apply_filters( 'tablepress_admin_menu_entry_name', 'TablePress' );

		if ( $this->is_top_level_page ) {
			$this->init_i18n_support(); // done here as translated strings for admin menu are needed already
			$this->init_view_actions(); // after init_i18n_support(), as it requires translation
			$min_access_cap = $this->view_actions['list']['required_cap'];

			$icon_url = plugins_url( 'admin/tablepress-icon-small.png', TABLEPRESS__FILE__ );
			switch ( $this->parent_page ) {
				case 'top':
					$position = 3; // position of Dashboard + 1
					break;
				case 'middle':
					$position = ( ++$GLOBALS['_wp_last_object_menu'] );
					break;
				case 'bottom':
					$position = ( ++$GLOBALS['_wp_last_utility_menu'] );
					break;
			}
			add_menu_page( 'TablePress', $admin_menu_entry_name, $min_access_cap, 'tablepress', $callback, $icon_url, $position );
			foreach ( $this->view_actions as $action => $entry ) {
				if ( ! $entry['show_entry'] )
					continue;
				$slug = 'tablepress';
				if ( 'list' != $action )
					$slug .= '_' . $action;
				$this->page_hooks[] = add_submenu_page( 'tablepress', sprintf( __( '%1$s &lsaquo; %2$s', 'tablepress' ), $entry['page_title'], 'TablePress' ), $entry['admin_menu_title'], $entry['required_cap'], $slug, $callback );
			}
		} else {
			$this->init_view_actions(); // no translation necessary here
			$min_access_cap = $this->view_actions['list']['required_cap'];
			$this->page_hooks[] = add_submenu_page( $this->parent_page, 'TablePress', $admin_menu_entry_name, $min_access_cap, 'tablepress', $callback );
		}
	}

	/**
	 * Set up handlers for user actions in the backend that exceed plain viewing
	 *
	 * @since 1.0.0
	 */
	public function add_admin_actions() {
		// register the callbacks for processing action requests
		$post_actions = array( 'list', 'add', 'edit', 'options', 'export', 'import' );
		$get_actions = array( 'hide_message', 'delete_table', 'copy_table', 'preview_table', 'editor_button_thickbox' );
		foreach ( $post_actions as $action ) {
			add_action( "admin_post_tablepress_{$action}", array( $this, "handle_post_action_{$action}" ) );
		}
		foreach ( $get_actions as $action ) {
			add_action( "admin_post_tablepress_{$action}", array( $this, "handle_get_action_{$action}" ) );
		}

		// register callbacks to trigger load behavior for admin pages
		foreach ( $this->page_hooks as $page_hook ) {
			add_action( "load-{$page_hook}", array( $this, 'load_admin_page' ) );
		}

		$pages_with_editor_button = array( 'post.php', 'post-new.php' );
		foreach ( $pages_with_editor_button as $editor_page ) {
			add_action( "load-{$editor_page}", array( $this, 'add_editor_buttons' ) );
		}

		if ( ! is_network_admin() && ! is_user_admin() )
			add_action( 'admin_bar_menu', array( $this, 'add_wp_admin_bar_new_content_menu_entry' ), 71 );

		add_action( 'admin_print_styles', array( $this, 'add_tablepress_hidpi_css' ), 21 );

		add_action( 'load-plugins.php', array( $this, 'plugins_page' ) );
	}

	/**
	 * Register actions to add "Table" button to "HTML editor" and "Visual editor" toolbars
	 *
	 * @since 1.0.0
	 */
	public function add_editor_buttons() {
		$this->init_i18n_support();
		add_thickbox(); // usually already loaded by media upload functions
		$admin_page = TablePress::load_class( 'TablePress_Admin_Page', 'class-admin-page-helper.php', 'classes' );
		$admin_page->enqueue_script( 'quicktags-button', array( 'quicktags', 'media-upload' ), array(
			'editor_button' => array(
				'caption' => __( 'Table', 'tablepress' ),
				'title' => __( 'Insert a Table from TablePress', 'tablepress' ),
				'thickbox_title' => __( 'Insert a Table from TablePress', 'tablepress' ),
				'thickbox_url' => TablePress::url( array( 'action' => 'editor_button_thickbox' ), true, 'admin-post.php' )
			)
		) );

		// TinyMCE integration
		if ( user_can_richedit() ) {
			add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
			add_filter( 'mce_buttons', array( $this, 'add_tinymce_button' ) );
		}
	}

	/**
	 * Add "Table" button and separator to the TinyMCE toolbar
	 *
	 * @since 1.0.0
	 *
	 * @param array $buttons Current set of buttons in the TinyMCE toolbar
	 * @return array Current set of buttons in the TinyMCE toolbar, including "Table" button
	 */
	public function add_tinymce_button( $buttons ) {
		$buttons[] = 'tablepress_insert_table';
		return $buttons;
	}

	/**
	 * Register "Table" button plugin to TinyMCE
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugins Current set of registered TinyMCE plugins
	 * @return array Current set of registered TinyMCE plugins, including "Table" button plugin
	 */
	public function add_tinymce_plugin( $plugins ) {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$js_file = "admin/tinymce-button{$suffix}.js";
		$plugins['tablepress_tinymce'] = plugins_url( $js_file, TABLEPRESS__FILE__ );
		return $plugins;
	}

	/**
	 * Print TablePress HiDPI CSS to the <head>, for Admin Menu icon, and maybe for TinyMCE button
	 *
	 * @since 1.0.0
	 */
	public function add_tablepress_hidpi_css() {
		echo '<style type="text/css">@media print,(-o-min-device-pixel-ratio:5/4),(-webkit-min-device-pixel-ratio:1.25),(min-resolution:120dpi){';
		if ( ! empty( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'post.php', 'post-new.php' ), true ) && user_can_richedit() ) {
			echo '#content_tablepress_insert_table span{background:url(' . plugins_url( 'admin/tablepress-editor-button-2x.png', TABLEPRESS__FILE__ ) . ') no-repeat 0 0;background-size:20px 20px}';
			echo '#content_tablepress_insert_table img,'; // display:none of next selector is re-used, by combining selectors
		}
		echo '#toplevel_page_tablepress .wp-menu-image img{display:none}';
		echo '#toplevel_page_tablepress .wp-menu-image{background:url(' . plugins_url( 'admin/tablepress-icon-small-2x.png', TABLEPRESS__FILE__ ) . ') no-repeat 7px 7px;background-size:16px 16px}';
		echo '}</style>' . "\n";
	}

	/**
	 * Add "TablePress Table" entry to "New" dropdown menu in the WP Admin Bar
	 *
	 * @since 1.0.0
	 *
	 * @param object $wp_admin_bar The current WP Admin Bar object
	 */
	public function add_wp_admin_bar_new_content_menu_entry( $wp_admin_bar ) {
		if ( ! current_user_can( 'tablepress_add_tables' ) )
			return;
		// @TODO: Translation might not work, as textdomain might not yet be loaded here (for submenu entries)
		// Might need $this->init_i18n_support(); here
		$wp_admin_bar->add_menu( array(
			'parent' => 'new-content',
			'id' => 'new-tablepress-table',
			'title' => __( 'TablePress Table', 'tablepress' ),
			'href' => TablePress::url( array( 'action' => 'add' ) )
		) );
	}

	/**
	 * Handle actions for loading of Plugins page
	 *
	 * @since 1.0.0
	 */
	public function plugins_page() {
		$this->init_i18n_support();
		// add additional links on Plugins page
		add_filter( 'plugin_action_links_' . TABLEPRESS_BASENAME, array( $this, 'add_plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Add links to the TablePress entry in the "Plugin" column on the Plugins page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links List of links to print in the "Plugin" column on the Plugins page
	 * @return array Extended list of links to print in the "Plugin" column on the Plugins page
	 */
	public function add_plugin_action_links( $links ) {
		if ( current_user_can( 'tablepress_list_tables' ) )
			$links[] = '<a href="' . TablePress::url() . '" title="' . __( 'TablePress Plugin page', 'tablepress' ) . '">' . __( 'Plugin page', 'tablepress' ) . '</a>';
		return $links;
	}
	/**
	 * Add links to the TablePress entry in the "Description" column on the Plugins page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links List of links to print in the "Description" column on the Plugins page
	 * @param string $file Name of the plugin
	 * @return array Extended list of links to print in the "Description" column on the Plugins page
	 */
	public function add_plugin_row_meta( $links, $file ) {
		if ( TABLEPRESS_BASENAME == $file ) {
			$links[] = '<a href="http://tablepress.org/faq/" title="' . __( 'Frequently Asked Questions', 'tablepress' ) . '">' . __( 'FAQ', 'tablepress' ) . '</a>';
			$links[] = '<a href="http://tablepress.org/documentation/" title="' . __( 'Plugin Documentation', 'tablepress' ) . '">' . __( 'Documentation', 'tablepress' ) . '</a>';
			$links[] = '<a href="http://tablepress.org/support/" title="' . __( 'Support', 'tablepress' ) . '">' . __( 'Support', 'tablepress' ) . '</a>';
			$links[] = '<a href="http://tablepress.org/donate/" title="' . __( 'Support TablePress with your donation!', 'tablepress' ) . '"><strong>' . __( 'Donate', 'tablepress' ) . '</strong></a>';
		}
		return $links;
	}

	/**
	 * Prepare the rendering of an admin screen, by determining the current action, loading necessary data and initializing the view
	 *
	 * @since 1.0.0
	 */
	 public function load_admin_page() {
		// determine the action from either the GET parameter (for sub-menu entries, and the main admin menu entry)
		$action = ( ! empty( $_GET['action'] ) ) ? $_GET['action'] : 'list'; // default action is list
		if ( $this->is_top_level_page ) {
			// or for sub-menu entry of an admin menu "TablePress" entry, get it from the "page" GET parameter
			if ( 'tablepress' !== $_GET['page'] )
				// actions that are top-level entries, but don't have an action GET parameter (action is after last _ in string)
				$action = substr( $_GET['page'], 11 ); // $_GET['page'] has the format 'tablepress_{$action}'
		} else {
			// do this here in the else-part, instead of adding another if ( ! $this->is_top_level_page ) check
			$this->init_i18n_support(); // done here, as for sub menu admin pages this is the first time translated strings are needed
			$this->init_view_actions(); // for top-level menu entries, this has been done above, just like init_i18n_support()
		}

		// check if action is a supported action, and whether the user is allowed to access this screen
		if ( ! isset( $this->view_actions[ $action ] ) || ! current_user_can( $this->view_actions[ $action ]['required_cap'] ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		// changes current screen ID and pagenow variable in JS, to enable automatic meta box JS handling
		set_current_screen( "tablepress_{$action}" );

		// pre-define some table data
		$data = array(
			'view_actions' => $this->view_actions,
			'message' => ( ! empty( $_GET['message'] ) ) ? $_GET['message'] : false
		);

		// depending on action, load more necessary data for the corresponding view
		switch ( $action ) {
			case 'list':
				$data['tables'] = $this->model_table->load_all(); // does not contain table data
				$data['messages']['first_visit'] = $this->model_options->get( 'message_first_visit' );
				if ( current_user_can( 'tablepress_import_tables_wptr' ) )
					$data['messages']['wp_table_reloaded_warning'] = is_plugin_active( 'wp-table-reloaded/wp-table-reloaded.php' ); // check if WP-Table Reloaded is activated
				else
					$data['messages']['wp_table_reloaded_warning'] = false;
				$data['messages']['show_plugin_update'] = $this->model_options->get( 'message_plugin_update' );
				$data['messages']['plugin_update_message'] = $this->model_options->get( 'message_plugin_update_content' );
				$data['messages']['donation_message'] = $this->maybe_show_donation_message();
				$data['table_count'] = count( $data['tables'] );
				break;
			case 'about':
				$data['plugin_languages'] = $this->get_plugin_languages();
				$data['first_activation'] = $this->model_options->get( 'first_activation' );
				$exporter = TablePress::load_class( 'TablePress_Export', 'class-export.php', 'classes' );
				$data['zip_support_available'] = $exporter->zip_support_available;
				break;
			case 'options':
				// Maybe try saving "Custom CSS" to a file:
				// (called here, as the credentials form posts to this handler again, due to how request_filesystem_credentials() works)
				if ( isset( $_GET['item'] ) && 'save_custom_css' == $_GET['item'] ) {
					TablePress::check_nonce( 'options', $_GET['item'] ); // nonce check here, as we don't have an explicit handler, and even viewing the screen needs to be checked
					$action = 'options_custom_css'; // to load a different view
					// try saving "Custom CSS" to a file, otherwise this gets the HTML for the credentials form
					$result = $this->model_options->save_custom_css_to_file();
					$data['credentials_form'] = $result;
					break;
				}
				$data['frontend_options']['use_custom_css'] = $this->model_options->get( 'use_custom_css' );
				$data['frontend_options']['use_custom_css_file'] = $this->model_options->get( 'use_custom_css_file' );
				$data['frontend_options']['custom_css'] = $this->model_options->load_custom_css_from_file( 'normal' );
				$data['frontend_options']['custom_css_file_exists'] = ( false !== $data['frontend_options']['custom_css'] );
				if ( $data['frontend_options']['use_custom_css_file'] ) {
					// fall back to "Custom CSS" in options, if it could not be retrieved from file
					if ( ! $data['frontend_options']['custom_css_file_exists'] )
						$data['frontend_options']['custom_css'] = $this->model_options->get( 'custom_css' );
				} else {
					// get "Custom CSS" from options
					$data['frontend_options']['custom_css'] = $this->model_options->get( 'custom_css' );
				}
				$data['user_options']['parent_page'] = $this->parent_page;
				$data['user_options']['plugin_language'] = $this->model_options->get( 'plugin_language' );
				$data['user_options']['plugin_languages'] = $this->get_plugin_languages();
				break;
			case 'edit':
				if ( ! empty( $_GET['table_id'] ) ) {
					$data['table'] = $this->model_table->load( $_GET['table_id'] );
					if ( false === $data['table'] )
						TablePress::redirect( array( 'action' => 'list', 'message' => 'error_load_table' ) );
					if ( ! current_user_can( 'tablepress_edit_table', $_GET['table_id'] ) )
						wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
				} else {
					TablePress::redirect( array( 'action' => 'list', 'message' => 'error_no_table' ) );
				}
				break;
			case 'export':
				$data['tables'] = $this->model_table->load_all(); // does not contain table data
				$data['tables_count'] = $this->model_table->count_tables();
				if ( ! empty( $_GET['table_id'] ) )
					$data['export_ids'] = explode( ',', $_GET['table_id'] );
				else
					$data['export_ids'] = array(); // just show empty export form
				$exporter = TablePress::load_class( 'TablePress_Export', 'class-export.php', 'classes' );
				$data['zip_support_available'] = $exporter->zip_support_available;
				$data['export_formats'] = $exporter->export_formats;
				$data['csv_delimiters'] = $exporter->csv_delimiters;
				$data['export_format'] = ( ! empty( $_GET['export_format'] ) ) ? $_GET['export_format'] : false;
				$data['csv_delimiter'] = ( ! empty( $_GET['csv_delimiter'] ) ) ? $_GET['csv_delimiter'] : _x( ',', 'Default CSV delimiter in the translated language (";", ",", or "tab")', 'tablepress' );
				break;
			case 'import':
				$data['tables'] = $this->model_table->load_all(); // does not contain table data
				$data['tables_count'] = $this->model_table->count_tables();
				$importer = TablePress::load_class( 'TablePress_Import', 'class-import.php', 'classes' );
				$data['zip_support_available'] = $importer->zip_support_available;
				$data['html_import_support_available'] = $importer->html_import_support_available;
				$data['import_formats'] = $importer->import_formats;
				$data['import_format'] = ( ! empty( $_GET['import_format'] ) ) ? $_GET['import_format'] : false;
				$data['import_add_replace'] = ( ! empty( $_GET['import_add_replace'] ) ) ? $_GET['import_add_replace'] : 'add';
				$data['import_replace_table'] = ( ! empty( $_GET['import_replace_table'] ) ) ? $_GET['import_replace_table'] : false;
				$data['import_source'] = ( ! empty( $_GET['import_source'] ) ) ? $_GET['import_source'] : 'file-upload';
				$data['import_url'] = ( ! empty( $_GET['import_url'] ) ) ? $_GET['import_url'] : 'http://';
				$data['import_server'] = ( ! empty( $_GET['import_server'] ) ) ? $_GET['import_server'] : ABSPATH;
				$data['import_form_field'] = ( ! empty( $_GET['import_form_field'] ) ) ? $_GET['import_form_field'] : '';
				$data['wp_table_reloaded_installed'] = ( false !== get_option( 'wp_table_reloaded_options', false ) && false !== get_option( 'wp_table_reloaded_tables', false ) );
				$data['import_wp_table_reloaded_source'] = ( ! empty( $_GET['import_wp_table_reloaded_source'] ) ) ? $_GET['import_wp_table_reloaded_source'] : ( $data['wp_table_reloaded_installed'] ? 'db' : 'dump-file' );
				break;
		}

		$data = apply_filters( 'tablepress_view_data', $data, $action );

		// prepare and initialize the view
		$this->view = TablePress::load_view( $action, $data );
	}

	/**
	 * Render the view that has been initialized in load_admin_page() (called by WordPress when the actual page content is needed)
	 *
	 * @since 1.0.0
	 */
	public function show_admin_page() {
		$this->view->render();
	}

	/**
	 * Initialize i18n support, load plugin's textdomain, to retrieve correct translations
	 *
	 * @since 1.0.0
	 */
	protected function init_i18n_support() {
		if ( $this->i18n_support_loaded )
			return;
		add_filter( 'locale', array( $this, 'change_plugin_locale' ) ); // allow changing the plugin language
		$language_directory = basename( dirname( TABLEPRESS__FILE__ ) ) . '/i18n';
		load_plugin_textdomain( 'tablepress', false, $language_directory );
		remove_filter( 'locale', array( $this, 'change_plugin_locale' ) );
		$this->i18n_support_loaded = true;
	}

	/**
	 * Get a list of available plugin languages and information on the translator
	 *
	 * @since 1.0.0
	 *
	 * @return array List of languages
	 */
	protected function get_plugin_languages() {
		$languages = array(
			'de_DE' => array(
				'name' => __( 'German', 'tablepress' ),
				'translator_name' => 'Tobias Bäthge',
				'translator_url' => 'http://tobias.baethge.com/'
			),
			'en_US' => array(
				'name' => __( 'English', 'tablepress' ),
				'translator_name' => 'Tobias Bäthge',
				'translator_url' => 'http://tobias.baethge.com/'
			),
			'es_ES' => array(
				'name' => __( 'Spanish', 'tablepress' ),
				'translator_name' => 'Darío Hereñú',
				'translator_url' => ''
			),
			'fr_FR' => array(
				'name' => __( 'French', 'tablepress' ),
				'translator_name' => 'Loïc Herry',
				'translator_url' => 'http://www.lherry.fr/'
			),
			'sk_SK' => array(
				'name' => __( 'Slovak', 'tablepress' ),
				'translator_name' => 'sle',
				'translator_url' => 'http://fooddrink.sk/'
			),
			'zh_CN' => array(
				'name' => __( 'Chinese (Simplified)', 'tablepress' ),
				'translator_name' => 'Haoxian Zeng',
				'translator_url' => 'http://cnzhx.net/'
			)
		);
		uasort( $languages, array( $this, '_get_plugin_languages_sort_cb' ) ); // to sort after the translation is done
		return $languages;
	}

	/**
	 * Callback for sorting the language array in @see get_plugin_languages()
	 *
	 * @see get_plugin_languages()
	 * @since 1.0.0
	 *
	 * @param array $a First language to sort
	 * @param array $b Second language to sort
	 * @return array -1, 0, 1, depending on sort
	 */
	protected function _get_plugin_languages_sort_cb( $a, $b ) {
		return strnatcasecmp( $a['name'], $b['name'] );
	}

	/**
	 * Decide whether a donate message shall be shown on the "All Tables" screen, depending on passed days since installation and whether it was shown before
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the donate message shall be shown on the "All Tables" screen
	 */
	protected function maybe_show_donation_message() {
		// Only show the message to plugin admins
		if ( ! current_user_can( 'tablepress_edit_options' ) )
			return false;

		if ( ! $this->model_options->get( 'message_donation_nag' ) )
			return false;

		// How long has the plugin been installed?
		$seconds_installed = time() - $this->model_options->get( 'first_activation' );
		return ( $seconds_installed > 30*DAY_IN_SECONDS );
	}

	/**
	 * Init list of actions that have a view with their titles/names/caps
	 *
	 * @since 1.0.0
	 */
	protected function init_view_actions() {
		$this->view_actions = array(
			'list' => array(
				'show_entry' => true,
				'page_title' => __( 'All Tables', 'tablepress' ),
				'admin_menu_title' => __( 'All Tables', 'tablepress' ),
				'nav_tab_title' => __( 'All Tables', 'tablepress' ),
				'required_cap' => 'tablepress_list_tables'
			),
			'add' => array(
				'show_entry' => true,
				'page_title' => __( 'Add New Table', 'tablepress' ),
				'admin_menu_title' => __( 'Add New Table', 'tablepress' ),
				'nav_tab_title' => __( 'Add New', 'tablepress' ),
				'required_cap' => 'tablepress_add_tables'
			),
			'edit' => array(
				'show_entry' => false,
				'page_title' => __( 'Edit Table', 'tablepress' ),
				'admin_menu_title' => '',
				'nav_tab_title' => '',
				'required_cap' => 'tablepress_edit_tables'
			),
			'import' => array(
				'show_entry' => true,
				'page_title' => __( 'Import a Table', 'tablepress' ),
				'admin_menu_title' => __( 'Import a Table', 'tablepress' ),
				'nav_tab_title' => _x( 'Import', 'navigation bar', 'tablepress' ),
				'required_cap' => 'tablepress_import_tables'
			),
			'export' => array(
				'show_entry' => true,
				'page_title' => __( 'Export a Table', 'tablepress' ),
				'admin_menu_title' => __( 'Export a Table', 'tablepress' ),
				'nav_tab_title' => _x( 'Export', 'navigation bar', 'tablepress' ),
				'required_cap' => 'tablepress_export_tables'
			),
			'options' => array(
				'show_entry' => true,
				'page_title' => __( 'Plugin Options', 'tablepress' ),
				'admin_menu_title' => __( 'Plugin Options', 'tablepress' ),
				'nav_tab_title' => __( 'Plugin Options', 'tablepress' ),
				'required_cap' => 'tablepress_access_options_screen'
			),
			'about' => array(
				'show_entry' => true,
				'page_title' => __( 'About', 'tablepress' ),
				'admin_menu_title' => __( 'About TablePress', 'tablepress' ),
				'nav_tab_title' => __( 'About', 'tablepress' ),
				'required_cap' => 'tablepress_access_about_screen'
			)
		);

		$this->view_actions = apply_filters( 'tablepress_admin_view_actions', $this->view_actions );
	}

	/**
	 * Change the WordPress locale to the desired plugin locale, applied as a filter in get_locale(), while loading the plugin textdomain
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale Current WordPress locale
	 * @return string TablePress locale
	 */
	public function change_plugin_locale( $locale ) {
		$new_locale = $this->model_options->get( 'plugin_language' );
		$locale = ( ! empty( $new_locale ) && 'auto' != $new_locale ) ? $new_locale : $locale;
		return $locale;
	}

	/**
	 * HTTP POST actions
	 */

	/**
	 * Handle Bulk Actions (Copy, Export, Delete) on "All Tables" list screen
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_list() {
		TablePress::check_nonce( 'list' );

		if ( isset( $_POST['bulk-action-top'] ) && '-1' != $_POST['bulk-action-top'] )
			$bulk_action = $_POST['bulk-action-top'];
		elseif ( isset( $_POST['bulk-action-bottom'] ) && '-1' != $_POST['bulk-action-bottom'] )
			$bulk_action = $_POST['bulk-action-bottom'];
		else
			$bulk_action = false;

		if ( ! in_array( $bulk_action, array( 'copy', 'export', 'delete' ), true ) )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'error_bulk_action_invalid' ) );

		if ( empty( $_POST['table'] ) || ! is_array( $_POST['table'] ) )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'error_no_selection' ) );
		else
			$tables = stripslashes_deep( $_POST['table'] );

		$no_success = array(); // to store table IDs that failed

		switch ( $bulk_action ) {
			case 'copy':
				foreach ( $tables as $table_id ) {
					if ( current_user_can( 'tablepress_copy_table', $table_id ) )
						$copy_table_id = $this->model_table->copy( $table_id );
					else
						$copy_table_id = false;
					if ( false === $copy_table_id )
						$no_success[] = $table_id;
				}
				break;
			case 'export':
				// Cap check is done on redirect target page
				// to export, redirect to "Export" screen, with selected table IDs
				$table_ids = implode( ',', $tables );
				TablePress::redirect( array( 'action' => 'export', 'table_id' => $table_ids ) );
				break;
			case 'delete':
				foreach ( $tables as $table_id ) {
					if ( current_user_can( 'tablepress_delete_table', $table_id ) )
						$deleted = $this->model_table->delete( $table_id );
					else
						$deleted = false;
					if ( false === $deleted )
						$no_success[] = $table_id;
				}
				break;
		}

		if ( count( $no_success ) != 0 ) { // maybe pass this information to the view?
			$message = "error_{$bulk_action}_not_all_tables";
		} else {
			$plural = ( count( $tables ) > 1 ) ? '_plural' : '';
			$message = "success_{$bulk_action}{$plural}";
		}

		// slightly more complex redirect method, to account for sort, search, and pagination in the WP_List_Table on the List View
		// but only if this action succeeds, to have everything fresh in the event of an error
		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = TablePress::url( array( 'action' => 'list', 'message' => $message ) );
		} else {
			$sendback = remove_query_arg( array( 'action', 'message', 'table_id' ), $sendback );
			$sendback = add_query_arg( array( 'action' => 'list', 'message' => $message ), $sendback );
		}
		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Save a table after the "Edit" screen was submitted
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_edit() {
		if ( empty( $_POST['table'] ) || empty( $_POST['table']['id'] ) )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'error_save' ) );
		else
			$edit_table = stripslashes_deep( $_POST['table'] );

		TablePress::check_nonce( 'edit', $edit_table['id'], 'nonce-edit-table' );

		if ( ! current_user_can( 'tablepress_edit_table', $edit_table['id'] ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		// Options array must exist, so that checkboxes can be evaluated
		if ( empty( $edit_table['options'] ) )
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $edit_table['id'], 'message' => 'error_save' ) );

		// Evaluate options that have a checkbox (only necessary in Admin Controller, where they might not be set (if unchecked))
		$checkbox_options = array(
			'table_head', 'table_foot', 'alternating_row_colors', 'row_hover', 'print_name', 'print_description', // Table Options
			'use_datatables', 'datatables_sort', 'datatables_filter', 'datatables_paginate', 'datatables_lengthchange', 'datatables_info', 'datatables_scrollx' // DataTables JS Features
		);
		foreach ( $checkbox_options as $option ) {
			$edit_table['options'][$option] = ( isset( $edit_table['options'][$option] ) && 'true' === $edit_table['options'][$option] );
		}

		// Load existing table from DB
		$existing_table = $this->model_table->load( $edit_table['id'] );
		if ( false === $existing_table ) // @TODO: Maybe somehow load a new table here? ($this->model_table->get_table_template())?
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $edit_table['id'], 'message' => 'error_save' ) );

		// Check consistency of new table, and then merge with existing table
		$table = $this->model_table->prepare_table( $existing_table, $edit_table );
		if ( false === $table )
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $edit_table['id'], 'message' => 'error_save' ) );

		// DataTables Custom Commands can only be edit by trusted users
		if ( ! current_user_can( 'unfiltered_html' ) )
			$table['options']['datatables_custom_commands'] = $existing_table['options']['datatables_custom_commands'];

		// Save updated table
		$saved = $this->model_table->save( $table );
		if ( false === $saved )
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'error_save' ) );

		// Check if ID change is desired
		if ( $table['id'] === $table['new_id'] ) // if not, we are done
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'success_save' ) );

		// Change table ID
		if ( current_user_can( 'tablepress_edit_table_id', $table['id'] ) )
			$id_changed = $this->model_table->change_table_id( $table['id'], $table['new_id'] );
		else
			$id_changed = false;
		if ( $id_changed )
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $table['new_id'], 'message' => 'success_save_success_id_change' ) );
		else
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $table['id'], 'message' => 'success_save_error_id_change' ) );
	}

	/**
	 * Add a table, according to the parameters on the "Add new Table" screen
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_add() {
		TablePress::check_nonce( 'add' );

		if ( ! current_user_can( 'tablepress_add_tables' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		if ( empty( $_POST['table'] ) || ! is_array( $_POST['table'] ) )
			TablePress::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );
		else
			$add_table = stripslashes_deep( $_POST['table'] );

		// Perform sanity checks of posted data
		$name = ( isset( $add_table['name'] ) ) ? $add_table['name'] : '';
		$description = ( isset( $add_table['description'] ) ) ? $add_table['description'] : '';
		if ( ! isset( $add_table['rows'] ) || ! isset( $add_table['columns'] ) )
			TablePress::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );

		$num_rows = absint( $add_table['rows'] );
		$num_columns = absint( $add_table['columns'] );
		if ( 0 == $num_rows || 0 == $num_columns )
			TablePress::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );

		// Create a new table array with information from the posted data
		$new_table = array(
			'name' => $name,
			'description' => $description,
			'data' => array_fill( 0, $num_rows, array_fill( 0, $num_columns, '' ) ),
			'visibility' => array(
				'rows' => array_fill( 0, $num_rows, 1 ),
				'columns' => array_fill( 0, $num_columns, 1 )
			)
		);
		// Merge this data into an empty table template
		$table = $this->model_table->prepare_table( $this->model_table->get_table_template(), $new_table, false );
		if ( false === $table )
			TablePress::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );

		// Add the new table (and get its first ID)
		$table_id = $this->model_table->add( $table );
		if ( false === $table_id )
			TablePress::redirect( array( 'action' => 'add', 'message' => 'error_add' ) );

		TablePress::redirect( array( 'action' => 'edit', 'table_id' => $table_id, 'message' => 'success_add' ) );
	}

	/**
	 * Save changed "Plugin Options"
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_options() {
		TablePress::check_nonce( 'options' );

		if ( ! current_user_can( 'tablepress_access_options_screen' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		if ( empty( $_POST['options'] ) || ! is_array( $_POST['options'] ) )
			TablePress::redirect( array( 'action' => 'options', 'message' => 'error_save' ) );
		else
			$posted_options = stripslashes_deep( $_POST['options'] );

		// Valid new options that will be merged into existing ones
		$new_options = array();

		// Check each posted option value, and (maybe) add it to the new options
		if ( ! empty( $posted_options['admin_menu_parent_page'] ) && '-' != $posted_options['admin_menu_parent_page'] ) {
			$new_options['admin_menu_parent_page'] = $posted_options['admin_menu_parent_page'];
			// re-init parent information, as TablePress::redirect() URL might be wrong otherwise
			$this->parent_page = apply_filters( 'tablepress_admin_menu_parent_page', $posted_options['admin_menu_parent_page'] );
			$this->is_top_level_page = in_array( $this->parent_page, array( 'top', 'middle', 'bottom' ), true );
		}
		if ( ! empty( $posted_options['plugin_language'] ) && '-' != $posted_options['plugin_language'] ) {
			// only allow "auto" language and all values that have a translation
			if ( 'auto' == $posted_options['plugin_language'] || array_key_exists( $posted_options['plugin_language'], $this->get_plugin_languages() ) )
				$new_options['plugin_language'] = $posted_options['plugin_language'];
		}

		// Custom CSS can only be saved if the user is allowed to do so
		$update_custom_css_file = false;
		if ( current_user_can( 'tablepress_edit_options' ) ) {
			// Checkboxes
			foreach ( array( 'use_custom_css', 'use_custom_css_file' ) as $checkbox ) {
				$new_options[ $checkbox ] = ( isset( $posted_options[ $checkbox ] ) && 'true' === $posted_options[ $checkbox ] );
			}
			if ( isset( $posted_options['custom_css'] ) ) {
				$custom_css = $posted_options['custom_css'];

				$csstidy = TablePress::load_class( 'csstidy', 'class.csstidy.php', 'libraries/csstidy' );

				// Sanitization and not just tidying for users without enough privileges
				if ( ! current_user_can( 'unfiltered_html' ) ) {
					$csstidy->optimise = new csstidy_custom_sanitize( $csstidy );

					$custom_css = str_replace( '<=', '&lt;=', $custom_css ); // Let "arrows" survive, otherwise this might be recognized as the beginning of an HTML tag and removed with other stuff behind it
					$custom_css = wp_kses( $custom_css, 'strip' ); // remove all HTML tags
					$custom_css = str_replace( '&gt;', '>', $custom_css ); // KSES replaces single ">" with "&gt;", but ">" is valid in CSS selectors
					$custom_css = strip_tags( $custom_css ); // strip_tags again, because of the just added ">" (KSES for a second time would again bring the ">" problem)
				}

				$csstidy->set_cfg( 'remove_bslash', false );
				$csstidy->set_cfg( 'compress_colors', false );
				$csstidy->set_cfg( 'compress_font-weight', false );
				$csstidy->set_cfg( 'lowercase_s', false );
				$csstidy->set_cfg( 'optimise_shorthands', false );
				$csstidy->set_cfg( 'remove_last_;', false );
				$csstidy->set_cfg( 'case_properties', false);
				$csstidy->set_cfg( 'sort_properties', false );
				$csstidy->set_cfg( 'sort_selectors', false );
				$csstidy->set_cfg( 'discard_invalid_selectors', false );
				$csstidy->set_cfg( 'discard_invalid_properties', true );
				$csstidy->set_cfg( 'merge_selectors', false );
				$csstidy->set_cfg( 'css_level', 'CSS3.0' );
				$csstidy->set_cfg( 'preserve_css', true );
				$csstidy->set_cfg( 'timestamp', false );
				$csstidy->set_cfg( 'template', dirname( TABLEPRESS__FILE__ ) . '/libraries/csstidy/tablepress-standard.tpl' );

				$csstidy->parse( $custom_css );
				$custom_css = $csstidy->print->plain();
				// Save "Custom CSS" to option
				$new_options['custom_css'] = $custom_css;

				// Minify CSS
				$minify_csstidy = new csstidy();
				$minify_csstidy->optimise = new csstidy_custom_sanitize( $minify_csstidy );
				$minify_csstidy->set_cfg( 'remove_bslash', false );
				$minify_csstidy->set_cfg( 'compress_colors', true );
				$minify_csstidy->set_cfg( 'compress_font-weight', true );
				$minify_csstidy->set_cfg( 'lowercase_s', false );
				$minify_csstidy->set_cfg( 'optimise_shorthands', 1 );
				$minify_csstidy->set_cfg( 'remove_last_;', true );
				$minify_csstidy->set_cfg( 'case_properties', false);
				$minify_csstidy->set_cfg( 'sort_properties', false );
				$minify_csstidy->set_cfg( 'sort_selectors', false );
				$minify_csstidy->set_cfg( 'discard_invalid_selectors', false );
				$minify_csstidy->set_cfg( 'discard_invalid_properties', true );
				$minify_csstidy->set_cfg( 'merge_selectors', false );
				$minify_csstidy->set_cfg( 'css_level', 'CSS3.0' );
				$minify_csstidy->set_cfg( 'preserve_css', false );
				$minify_csstidy->set_cfg( 'timestamp', false );
				$minify_csstidy->set_cfg( 'template', 'highest' );

				$minify_csstidy->parse( $custom_css );
				$minified_custom_css = $minify_csstidy->print->plain();
				// Save minified "Custom CSS" to option
				$new_options['custom_css_minified'] = $minified_custom_css;

				// Maybe update CSS files as well
				if ( $new_options['use_custom_css_file']
				&& $new_options['custom_css'] !== $this->model_options->load_custom_css_from_file( 'normal' ) ) { // only write to file, if CSS really changed
					$update_custom_css_file = true;
					// Set to false again. As it was set here, it will be set true again, if file saving succeeds
					$new_options['use_custom_css_file'] = false;
				}
			}
		}

		// save gathered new options (will be merged into existing ones), and flush caches of caching plugins, to make sure that the new Custom CSS is used
		if ( ! empty( $new_options ) ) {
			$this->model_options->update( $new_options );
			$this->model_table->_flush_caching_plugins_caches();
		}

		if ( $update_custom_css_file ) // capability check is performed above
			TablePress::redirect( array( 'action' => 'options', 'item' => 'save_custom_css' ), true );

		TablePress::redirect( array( 'action' => 'options', 'message' => 'success_save' ) );
	}

	/**
	 * Export selected tables
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_export() {
		TablePress::check_nonce( 'export' );

		if ( ! current_user_can( 'tablepress_export_tables' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		if ( empty( $_POST['export'] ) || ! is_array( $_POST['export'] ) )
			TablePress::redirect( array( 'action' => 'export', 'message' => 'error_export' ) );
		else
			$export = stripslashes_deep( $_POST['export'] );

		$exporter = TablePress::load_class( 'TablePress_Export', 'class-export.php', 'classes' );

		if ( empty( $export['tables'] ) )
			TablePress::redirect( array( 'action' => 'export', 'message' => 'error_export' ) );
		if ( empty( $export['format'] ) || ! isset( $exporter->export_formats[ $export['format'] ] ) )
			TablePress::redirect( array( 'action' => 'export', 'message' => 'error_export' ) );
		if ( empty( $export['csv_delimiter'] ) )
			$export['csv_delimiter'] = ''; // set a value, so that the variable exists
		if ( 'csv' == $export['format'] && ! isset( $exporter->csv_delimiters[ $export['csv_delimiter'] ] ) )
			TablePress::redirect( array( 'action' => 'export', 'message' => 'error_export' ) );

		// use list of tables from concatenated field if available (as that's hopefully not truncated by Suhosin, which is possible for $export['tables'])
		$tables = ( ! empty( $export['tables_list'] ) ) ? explode( ',', $export['tables_list'] ) : $export['tables'];

		if ( $exporter->zip_support_available // determine if ZIP file support is available
		&& ( ( isset( $export['zip_file'] ) && 'true' == $export['zip_file'] ) || count( $tables ) > 1 ) ) // only if ZIP desired or more than one table selected (mandatory)
			$export_to_zip = true;
		else
			$export_to_zip = false;

		if ( ! $export_to_zip ) {
			// this is only possible for one table, so take the first
			if ( ! current_user_can( 'tablepress_export_table', $tables[0] ) )
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
			$table = $this->model_table->load( $tables[0] );
			if ( false === $table )
				TablePress::redirect( array( 'action' => 'export', 'message' => 'error_load_table', 'export_format' => $export['format'], 'csv_delimiter' => $export['csv_delimiter'] ) );
			$download_filename = sprintf( '%1$s-%2$s-%3$s.%4$s', $table['id'], $table['name'], date( 'Y-m-d' ), $export['format'] );
			// export table
			$export_data = $exporter->export_table( $table, $export['format'], $export['csv_delimiter'] );
			$download_data = $export_data;
		} else {
			// Zipping can use a lot of memory and execution time, but not this much hopefully
			@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
			@set_time_limit( 300 );

			$zip_file = new ZipArchive();
			$download_filename = sprintf( 'tablepress-export-%1$s-%2$s.zip', date_i18n( 'Y-m-d-H-i-s' ), $export['format'] );
			$full_filename = wp_tempnam( $download_filename );
			if ( true !== $zip_file->open( $full_filename, ZIPARCHIVE::OVERWRITE ) ) {
				@unlink( $full_filename );
				TablePress::redirect( array( 'action' => 'export', 'message' => 'error_create_zip_file', 'export_format' => $export['format'], 'csv_delimiter' => $export['csv_delimiter'] ) );
			}

			foreach ( $tables as $table_id ) {
				// don't export tables for which the user doesn't have the necessary export rights
				if ( current_user_can( 'tablepress_export_table', $table_id ) )
					$table = $this->model_table->load( $table_id );
				else
					$table = false;
				if ( false === $table )
					continue; // no export if table could not be loaded
				$export_data = $exporter->export_table( $table, $export['format'], $export['csv_delimiter'] );
				$export_filename = sprintf( '%1$s-%2$s-%3$s.%4$s', $table['id'], $table['name'], date( 'Y-m-d' ), $export['format'] );
				$zip_file->addFromString( $export_filename, $export_data );
			}

			// if something went wrong, or no files were added to the ZIP file, bail out
			if ( ! ZIPARCHIVE::ER_OK == $zip_file->status || 0 == $zip_file->numFiles ) {
				$zip_file->close();
				@unlink( $full_filename );
				TablePress::redirect( array( 'action' => 'export', 'message' => 'error_create_zip_file', 'export_format' => $export['format'], 'csv_delimiter' => $export['csv_delimiter'] ) );
			}
			$zip_file->close();

			// load contents of the ZIP file, to send it as a download
			$download_data = file_get_contents( $full_filename );
			@unlink( $full_filename );
		}

		// Send download headers for export file
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename=\"{$download_filename}\"" );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . strlen( $download_data ) );
		// $filetype = text/csv, text/html, application/json
		// header( 'Content-Type: ' . $filetype. '; charset=' . get_option( 'blog_charset' ) );
		@ob_end_clean();
		flush();
		echo $download_data;
		exit;
	}

	/**
	 * Import data from either an existing source or WP-Table Reloaded
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_import() {
		TablePress::check_nonce( 'import' );

		if ( ! current_user_can( 'tablepress_import_tables' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		if ( empty( $_POST['import'] ) || ! is_array( $_POST['import'] ) )
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import' ) );
		else
			$import = stripslashes_deep( $_POST['import'] );

		// Determine if this is a regular import or an import from WP-Table Reloaded
		if ( isset( $_POST['submit_wp_table_reloaded_import'] ) && isset( $import['wp_table_reloaded'] ) && isset( $import['wp_table_reloaded']['source'] ) ) {
			if ( ! current_user_can( 'tablepress_import_tables_wptr' ) )
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

			// Handle checkbox selections
			$import_tables = ( isset( $import['wp_table_reloaded']['tables'] ) && 'true' === $import['wp_table_reloaded']['tables'] );
			$import_css = ( isset( $import['wp_table_reloaded']['css'] ) && 'true' === $import['wp_table_reloaded']['css'] );
			if ( ! $import_tables && ! $import_css )
				TablePress::redirect( array( 'action' => 'import', 'message' => 'error_wp_table_reloaded_nothing_selected' ) );

			if ( 'db' == $import['wp_table_reloaded']['source'] )
				$this->_import_from_wp_table_reloaded_db( $import_tables, $import_css );
			else
				$this->_import_from_wp_table_reloaded_dump_file( $import_tables, $import_css );
		} else {
			$this->_import_tablepress_regular( $import );
		}
	}

	/**
	 * Import data from existing source (Upload, URL, Server, Direct input)
	 *
	 * @since 1.0.0
	 *
	 * @param array $import Submitted form data
	 */
	protected function _import_tablepress_regular( $import ) {
		if ( ! isset( $import['add_replace'] ) )
			$import['add_replace'] = 'add';
		if ( ! isset( $import['replace_table'] ) )
			$import['replace_table'] = '';
		if ( ! isset( $import['source'] ) )
			$import['source'] = '';

		// Check if a table to replace was selected
		if ( 'replace' == $import['add_replace'] && empty( $import['replace_table'] ) )
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_no_replace_id', 'import_format' => $import['format'], 'import_add_replace' => 'replace', 'import_source' => $import['source'] ) );

		$import_error = true;
		$unlink_file = false;
		$import_data = array();
		switch ( $import['source'] ) {
			case 'file-upload':
				if ( ! empty( $_FILES['import_file_upload'] ) && UPLOAD_ERR_OK == $_FILES['import_file_upload']['error'] ) {
					$import_data['file_location'] = $_FILES['import_file_upload']['tmp_name'];
					$import_data['file_name'] = $_FILES['import_file_upload']['name'];
					// $_FILES['import_file_upload']['type'];
					// $_FILES['import_file_upload']['size']
					$import_error = false;
					$unlink_file = true;
				}
				break;
			case 'url':
				if ( ! empty( $import['url'] ) && 'http://' != $import['url'] ) {
					$import_data['file_location'] = download_url( $import['url'] ); // download URL to local file
					$import_data['file_name'] = $import['url'];
					if ( ! is_wp_error( $import_data['file_location'] ) )
						$import_error = false;
					$unlink_file = true;
				}
				break;
			case 'server':
				if ( ! empty( $import['server'] ) && ABSPATH != $import['server'] ) {
					$import_data['file_location'] = $import['server'];
					$import_data['file_name'] = pathinfo( $import['server'], PATHINFO_BASENAME );
					if ( is_readable( $import['server'] ) )
						$import_error = false;
				}
				break;
			case 'form-field':
				if ( ! empty( $import['form_field'] ) ) {
					$import_data['file_location'] = '';
					$import_data['file_name'] = __( 'Imported from Manual Input', 'tablepress' ); // Description of the table
					$import_data['data'] = $import['form_field'];
					$import_error = false;
				}
				break;
		}

		if ( $import_error ) {
			if ( $unlink_file )
				@unlink( $import_data['file_location'] );
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_source_invalid', 'import_format' => $import['format'], 'import_add_replace' => $import['add_replace'], 'import_replace_table' => $import['replace_table'], 'import_source' => $import['source'] ) );
		}

		$this->importer = TablePress::load_class( 'TablePress_Import', 'class-import.php', 'classes' );

		if ( 'zip' == pathinfo( $import_data['file_name'], PATHINFO_EXTENSION ) ) {
			if ( ! $this->importer->zip_support_available ) { // determine if ZIP file support is available
				if ( $unlink_file )
					@unlink( $import_data['file_location'] );
				TablePress::redirect( array( 'action' => 'import', 'message' => 'error_no_zip_import', 'import_format' => $import['format'], 'import_add_replace' => $import['add_replace'], 'import_replace_table' => $import['replace_table'], 'import_source' => $import['source'] ) );
			}
			$import_zip = true;
		} else {
			$import_zip = false;
		}

		if ( ! $import_zip ) {
			if ( ! isset( $import_data['data'] ) )
				$import_data['data'] = file_get_contents( $import_data['file_location'] );
			if ( false === $import_data['data'] )
				TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import' ) );

			$name = $import_data['file_name'];
			$description = $import_data['file_name'];
			$replace_id = ( 'replace' == $import['add_replace'] && ! empty( $import['replace_table'] ) ) ? $import['replace_table'] : false;
			$table_id = $this->_import_tablepress_table( $import['format'], $import_data['data'], $name, $description, $replace_id );

			if ( $unlink_file )
				@unlink( $import_data['file_location'] );

			if ( false === $table_id )
				TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_data' ) );
			else
				TablePress::redirect( array( 'action' => 'edit', 'table_id' => $table_id, 'message' => 'success_import' ) );
		} else {
			// Zipping can use a lot of memory and execution time, but not this much hopefully
			@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
			@set_time_limit( 300 );

			$zip = new ZipArchive();
			if ( true !== $zip->open( $import_data['file_location'], ZIPARCHIVE::CHECKCONS ) ) {
				if ( $unlink_file )
					@unlink( $import_data['file_location'] );
				TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_zip_open' ) );
			}

			$imported_files = array();
			for ( $file_idx = 0; $file_idx < $zip->numFiles; $file_idx++ ) {
				$file_name = $zip->getNameIndex( $file_idx );
				if ( '/' == substr( $file_name, -1 ) ) // directory
					continue;
				if ( '__MACOSX/' == substr( $file_name, 0, 9 ) ) // Skip the __MACOSX directory that Mac OSX adds to archives
					continue;
				$data = $zip->getFromIndex( $file_idx );
				if ( false === $data )
					continue;

				$name = $file_name;
				$description = $file_name;
				$replace_id = ( 'replace' == $import['add_replace'] ) ? false : false; // @TODO: Find a way to extract the replace ID from the filename, maybe?
				$table_id = $this->_import_tablepress_table( $import['format'], $data, $name, $description, $replace_id );
				if ( false === $table_id )
					continue;
				else
					$imported_files[] = $table_id;
			};
			$zip->close();

			if ( $unlink_file )
				@unlink( $import_data['file_location'] );

			if ( count( $imported_files ) > 1 )
				TablePress::redirect( array( 'action' => 'list', 'message' => 'success_import' ) );
			elseif ( 1 == count( $imported_files ) )
				TablePress::redirect( array( 'action' => 'edit', 'table_id' => $imported_files[0], 'message' => 'success_import' ) );
			else
				TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_zip_content' ) );
		}

	}

	/**
	 * Import a table by either replacing an existing table or adding it as a new table
	 *
	 * @since 1.0.0
	 *
	 * @param string $format Import format
	 * @param array $data Data to import
	 * @param string $name Name of the table
	 * @param string $description Description of the table
	 * @param bool|string $replace_id False if table shall be added new, ID of the table to be replaced otherwise
	 * @return bool|string False on error, table ID on success
	 */
	protected function _import_tablepress_table( $format, $data, $name, $description, $replace_id ) {
		$imported_table = $this->importer->import_table( $format, $data );
		if ( false === $imported_table )
			return false;

		// to be able to replace a table, editing that table must be allowed
		if ( false !== $replace_id && ! current_user_can( 'tablepress_edit_table', $replace_id ) )
			return false;

		// Full JSON format table can contain a table ID, try to keep that
		$table_id_in_import = false;

		if ( false !== $replace_id ) {
			// Load existing table from DB
			$existing_table = $this->model_table->load( $replace_id );
			if ( false === $existing_table )
				return false;
			// don't change name and description when a table is replaced
			$imported_table['name'] = $existing_table['name'];
			$imported_table['description'] = $existing_table['description'];
		} else {
			$existing_table = $this->model_table->get_table_template();
			// if name and description are imported from a new table, use those
			if ( isset( $imported_table['id'] ) )
				$table_id_in_import = $imported_table['id'];
			if ( ! isset( $imported_table['name'] ) )
				$imported_table['name'] = $name;
			if ( ! isset( $imported_table['description'] ) )
				$imported_table['description'] = $description;
		}

		// Merge new or existing table with information from the imported table
		$imported_table['id'] = $existing_table['id']; // will be false for new table or the existing table ID
		// cut visibility array (if the imported table is smaller), and pad correctly if imported table is bigger than existing table (or new template)
		$num_rows = count( $imported_table['data'] );
		$num_columns = count( $imported_table['data'][0] );
		$imported_table['visibility'] = array(
			'rows' => array_pad( array_slice( $existing_table['visibility']['rows'], 0, $num_rows ), $num_rows, 1 ),
			'columns' => array_pad( array_slice( $existing_table['visibility']['columns'], 0, $num_columns ), $num_columns, 1 )
		);

		// Check if new data is ok
		$table = $this->model_table->prepare_table( $existing_table, $imported_table, false );
		if ( false === $table )
			return false;

		// DataTables Custom Commands can only be edit by trusted users
		if ( ! current_user_can( 'unfiltered_html' ) )
			$table['options']['datatables_custom_commands'] = $existing_table['options']['datatables_custom_commands'];

		// Replace existing table or add new table
		if ( false !== $replace_id )
			$table_id = $this->model_table->save( $table ); // Replace existing table with imported table
		else
			$table_id = $this->model_table->add( $table ); // Add the imported table (and get its first ID)

		if ( false === $table_id )
			return false;

		// Try to use ID from imported file (e.g. in full JSON format table)
		if ( false !== $table_id_in_import && $table_id != $table_id_in_import && current_user_can( 'tablepress_edit_table_id', $table_id ) ) {
			$id_changed = $this->model_table->change_table_id( $table_id, $table_id_in_import );
			if ( $id_changed )
				$table_id = $table_id_in_import;
		}

		return $table_id;
	}

	/**
	 * Import data from WP-Table Reloaded from the WordPress database
	 *
	 * @since 1.0.0
	 *
	 * @param bool $import_tables Whether tables shall be imported
	 * @param bool $import_css Whether Plugin Options (only CSS related right now) shall be imported
	 */
	protected function _import_from_wp_table_reloaded_db( $import_tables, $import_css ) {
		if ( false === get_option( 'wp_table_reloaded_options', false ) || false === get_option( 'wp_table_reloaded_tables', false ) )
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_wp_table_reloaded_not_installed' ) );

		// Import WP-Table Reloaded tables
		$not_imported_tables = $imported_tables = $imported_other_id_tables = array();
		if ( $import_tables ) {
			$wp_table_reloaded_tables_list = get_option( 'wp_table_reloaded_tables', array() );
			foreach ( $wp_table_reloaded_tables_list as $wptr_table_id => $table_option_name ) {
				$wptr_table = get_option( $table_option_name, false );
				$import_status = $this->_import_wp_table_reloaded_table( $wptr_table );
				switch ( $import_status ) {
					case 0:
						$not_imported_tables[] = $wptr_table_id;
						break;
					case 1:
						$imported_tables[] = $wptr_table_id;
						break;
					case 2:
						$imported_other_id_tables[] = $wptr_table_id;
						break;
				}
			}
		}

		// Import WP-Table Reloaded Plugin Options (currently only CSS related options)
		$imported_css = false;
		if ( $import_css ) {
			$wp_table_reloaded_options = get_option( 'wp_table_reloaded_options', false );
			if ( ! empty( $wp_table_reloaded_options ) )
				$imported_css = $this->_import_wp_table_reloaded_plugin_options( $wp_table_reloaded_options );
		}

		// @TODO: Better handling of the different cases of imported/imported-without-ID-change/not-imported tables
		if ( count( $imported_tables ) > 1 )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'success_import_wp_table_reloaded' ) );
		elseif ( 1 == count( $imported_tables ) )
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $imported_tables[0], 'message' => 'success_import_wp_table_reloaded' ) );
		if ( count( $imported_other_id_tables ) > 0 )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'success_import_wp_table_reloaded' ) );
		elseif ( $imported_css )
			TablePress::redirect( array( 'action' => 'options', 'message' => 'success_import_wp_table_reloaded' ) );
		else
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_wp_table_reloaded' ) );
	}

	/**
	 * Import data from WP-Table Reloaded from a WP-Table Reloaded Dump File
	 *
	 * @since 1.0.0
	 *
	 * @param bool $import_tables Whether tables shall be imported
	 * @param bool $import_css Whether Plugin Options (only CSS related right now) shall be imported
	 */
	protected function _import_from_wp_table_reloaded_dump_file( $import_tables, $import_css ) {
		if ( empty( $_FILES['import_wp_table_reloaded_file_upload'] ) || empty( $_FILES['import_wp_table_reloaded_file_upload']['tmp_name'] ) || UPLOAD_ERR_OK !== $_FILES['import_wp_table_reloaded_file_upload']['error'] )
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_wp_table_reloaded_dump_file' ) );

		$dump_file = file_get_contents( $_FILES['import_wp_table_reloaded_file_upload']['tmp_name'] );
		$dump_file = unserialize( $dump_file );
		if ( empty( $dump_file ) ) {
			@unlink( $_FILES['import_wp_table_reloaded_file_upload']['tmp_name'] );
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_wp_table_reloaded_dump_file' ) );
		}

		// Import WP-Table Reloaded tables
		$not_imported_tables = $imported_tables = $imported_other_id_tables = array();
		if ( $import_tables && ! empty( $dump_file['tables'] ) ) {
			foreach ( $dump_file['tables'] as $wptr_table_id => $wptr_table ) {
				$import_status = $this->_import_wp_table_reloaded_table( $wptr_table );
				switch ( $import_status ) {
					case 0:
						$not_imported_tables[] = $wptr_table_id;
						break;
					case 1:
						$imported_tables[] = $wptr_table_id;
						break;
					case 2:
						$imported_other_id_tables[] = $wptr_table_id;
						break;
				}
			}
		}

		// Import WP-Table Reloaded Plugin Options (currently only CSS related options)
		$imported_css = false;
		if ( $import_css && ! empty( $dump_file['options'] ) )
			$imported_css = $this->_import_wp_table_reloaded_plugin_options( $dump_file['options'] );

		@unlink( $_FILES['import_wp_table_reloaded_file_upload']['tmp_name'] );
		// @TODO: Better handling of the different cases of imported/imported-without-ID-change/not-imported tables
		if ( count( $imported_tables ) > 1 )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'success_import_wp_table_reloaded' ) );
		elseif ( 1 == count( $imported_tables ) )
			TablePress::redirect( array( 'action' => 'edit', 'table_id' => $imported_tables[0], 'message' => 'success_import_wp_table_reloaded' ) );
		if ( count( $imported_other_id_tables ) > 0 )
			TablePress::redirect( array( 'action' => 'list', 'message' => 'success_import_wp_table_reloaded' ) );
		elseif ( $imported_css )
			TablePress::redirect( array( 'action' => 'options', 'message' => 'success_import_wp_table_reloaded' ) );
		else
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_import_wp_table_reloaded' ) );
	}

	/**
	 * Import a WP-Table Reloaded table
	 *
	 * @since 1.0.0
	 *
	 * @param array $wptr_table WP-Table Reloaded table
	 * @return int Import status: 0=Import failed; 1=Imported with ID change; 2=Imported without ID change
	 */
	protected function _import_wp_table_reloaded_table( $wptr_table ) {
		if ( empty( $wptr_table ) )
			return 0; // Import failed

		// Perform sanity checks of imported table
		if ( ! isset( $wptr_table['name'] )
			|| ! isset( $wptr_table['description'] )
			|| empty( $wptr_table['data'] )
			|| empty( $wptr_table['options'] ) )
			return 0; // Import failed

		$wptr_table = stripslashes_deep( $wptr_table ); // slashed in WP-Table Reloaded

		// Table was loaded, import the data, table options, and visibility
		// Create a new table array with information from the imported table
		$new_table = array(
			'name' => $wptr_table['name'],
			'description' => $wptr_table['description'],
			'data' => $wptr_table['data'],
			'options' => array(),
			'visibility' => array(
				'rows' => array_fill( 0, count( $wptr_table['data'] ), 1 ),
				'columns' => array_fill( 0, count( $wptr_table['data'][0] ), 1 )
			)
		);
		if ( isset( $wptr_table['last_modified'] ) )
			$new_table['last_modified'] = $wptr_table['last_modified'];
		if ( isset( $wptr_table['last_editor_id'] ) )
			$new_table['author'] = $wptr_table['last_editor_id'];
		if ( isset( $wptr_table['options']['last_editor_id'] ) )
			$new_table['options']['last_editor'] = $wptr_table['last_editor_id'];
		if ( isset( $wptr_table['options']['first_row_th'] ) )
			$new_table['options']['table_head'] = $wptr_table['options']['first_row_th'];
		if ( isset( $wptr_table['options']['table_footer'] ) )
			$new_table['options']['table_foot'] = $wptr_table['options']['table_footer'];
		if ( isset( $wptr_table['options']['custom_css_class'] ) )
			$new_table['options']['extra_css_classes'] = $wptr_table['options']['custom_css_class'];
		// array key is the same in both plugins for the following options
		foreach ( array( 'alternating_row_colors', 'row_hover',
			'print_name', 'print_name_position', 'print_description', 'print_description_position',
			'use_datatables', 'datatables_sort',  'datatables_filter', 'datatables_paginate',
			'datatables_lengthchange', 'datatables_paginate_entries', 'datatables_info'
			) as $_option ) {
			if ( isset( $wptr_table['options'][ $_option ] ) )
				$new_table['options'][ $_option ] = $wptr_table['options'][ $_option ];
		}
		if ( isset( $wptr_table['options']['datatables_customcommands'] ) && current_user_can( 'unfiltered_html' ) )
			$new_table['options']['datatables_custom_commands'] = $wptr_table['options']['datatables_customcommands'];
		// not imported: $wptr_table['options']['cache_table_output']
		// not imported: $wptr_table['custom_fields']

		// Fix visibility: WP-Table Reloaded uses 0 and 1 the other way around
		foreach ( array_keys( $wptr_table['visibility']['rows'], true ) as $row_idx ) {
			$new_table['visibility']['rows'][ $row_idx ] = 0;
		}
		foreach ( array_keys( $wptr_table['visibility']['columns'], true ) as $column_idx ) {
			$new_table['visibility']['columns'][ $column_idx ] = 0;
		}

		// Merge this data into an empty table template
		$table = $this->model_table->prepare_table( $this->model_table->get_table_template(), $new_table, false );
		if ( false === $table )
			return 0; // Import failed

		// Add the new table (and get its first ID)
		$tp_table_id = $this->model_table->add( $table );
		if ( false === $tp_table_id )
			return 0; // Import failed

		// Change table ID to the ID the table had in WP-Table Reloaded (except if that ID is already taken)
		$id_changed = $this->model_table->change_table_id( $tp_table_id, $wptr_table['id'] );
		if ( ! $id_changed )
			return 2; // Imported without ID change

		return 1; // Imported with ID change
	}

	/**
	 * Import WP-Table Reloaded Plugin Options (currently just CSS related options)
	 *
	 * @since 1.0.0
	 *
	 * @param array $wp_table_reloaded_options Plugin Options of WP-Table Reloaded that shall be imported
	 * @return bool Whether the import was successful or not (on at least on option)
	 */
	protected function _import_wp_table_reloaded_plugin_options( $wp_table_reloaded_options ) {
		if ( ! is_array( $wp_table_reloaded_options ) )
			return false;

		if ( ! current_user_can( 'tablepress_edit_options' ) )
			return false;

		$imported_options = array();
		if ( isset( $wp_table_reloaded_options['use_custom_css'] ) )
			$imported_options['use_custom_css'] = (bool)$wp_table_reloaded_options['use_custom_css'];
		if ( isset( $wp_table_reloaded_options['custom_css'] ) ) {
			$imported_options['custom_css'] = stripslashes( $wp_table_reloaded_options['custom_css'] );
			$imported_options['custom_css'] = str_replace( '#wp-table-reloaded-id-', '#tablepress-', $imported_options['custom_css'] );
			$imported_options['custom_css'] = str_replace( '-no-1', '', $imported_options['custom_css'] );
			$imported_options['custom_css'] = str_replace( '.wp-table-reloaded-id-', '.tablepress-id-', $imported_options['custom_css'] );
			$imported_options['custom_css'] = str_replace( '.wp-table-reloaded', '.tablepress', $imported_options['custom_css'] );
		}

		/*
			// @TODO:
			// Maybe save it to file as well
			$update_custom_css_file = false;
			if ( $this->model_options->get( 'use_custom_css_file' )
			&& $imported_options['custom_css'] !== $this->model_options->load_custom_css_from_file( 'normal' ) ) { // only write to file, if CSS really changed
				$update_custom_css_file = true;
				// Set to false again. As it was set here, it will be set true again, if file saving succeeds
				$imported_options['use_custom_css_file'] = false;
			}
		*/

		// Save gathered imported options
		if ( empty( $imported_options ) )
			return false;

		$this->model_options->update( $imported_options );

		// @TODO: Necessary if saving to file above is used
		// if ( $update_custom_css_file )
		//	TablePress::redirect( array( 'action' => 'options', 'item' => 'save_custom_css' ), true );

		// Plugin Options import successful
		return true;
	}

	/**
	 * Save GET actions
	 */

	/**
	 * Hide a header message on an admin screen
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_hide_message() {
		$message_item = ! empty( $_GET['item'] ) ? $_GET['item'] : '';
		TablePress::check_nonce( 'hide_message', $message_item );

		if ( ! current_user_can( 'tablepress_list_tables' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		$updated_options = array( "message_{$message_item}" => false );
		if ( 'plugin_update' == $message_item )
			$updated_options['message_plugin_update_content'] = '';
		$this->model_options->update( $updated_options );

		$return = ! empty( $_GET['return'] ) ? $_GET['return'] : 'list';
		TablePress::redirect( array( 'action' => $return ) );
	}

	/**
	 * Delete a table
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_delete_table() {
		$table_id = ( ! empty( $_GET['item'] ) ) ? $_GET['item'] : false;
		TablePress::check_nonce( 'delete_table', $table_id );

		$return = ! empty( $_GET['return'] ) ? $_GET['return'] : 'list';
		$return_item = ! empty( $_GET['return_item'] ) ? $_GET['return_item'] : false;

		if ( false === $table_id ) // nonce check should actually catch this already
			TablePress::redirect( array( 'action' => $return, 'message' => 'error_delete', 'table_id' => $return_item ) );

		if ( ! current_user_can( 'tablepress_delete_table', $table_id ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		$deleted = $this->model_table->delete( $table_id );
		if ( false === $deleted )
			TablePress::redirect( array( 'action' => $return, 'message' => 'error_delete', 'table_id' => $return_item ) );

		// slightly more complex redirect method, to account for sort, search, and pagination in the WP_List_Table on the List View
		// but only if this action succeeds, to have everything fresh in the event of an error
		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = TablePress::url( array( 'action' => 'list', 'message' => 'success_delete', 'table_id' => $return_item ) );
		} else {
			$sendback = remove_query_arg( array( 'action', 'message', 'table_id' ), $sendback );
			$sendback = add_query_arg( array( 'action' => 'list', 'message' => 'success_delete', 'table_id' => $return_item ), $sendback );
		}
		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Copy a table
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_copy_table() {
		$table_id = ( ! empty( $_GET['item'] ) ) ? $_GET['item'] : false;
		TablePress::check_nonce( 'copy_table', $table_id );

		$return = ! empty( $_GET['return'] ) ? $_GET['return'] : 'list';
		$return_item = ! empty( $_GET['return_item'] ) ? $_GET['return_item'] : false;

		if ( false === $table_id ) // nonce check should actually catch this already
			TablePress::redirect( array( 'action' => $return, 'message' => 'error_copy', 'table_id' => $return_item ) );

		if ( ! current_user_can( 'tablepress_copy_table', $table_id ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		$copy_table_id = $this->model_table->copy( $table_id );
		if ( false === $copy_table_id )
			TablePress::redirect( array( 'action' => $return, 'message' => 'error_copy', 'table_id' => $return_item ) );

		// slightly more complex redirect method, to account for sort, search, and pagination in the WP_List_Table on the List View
		// but only if this action succeeds, to have everything fresh in the event of an error
		$sendback = wp_get_referer();
		if ( ! $sendback ) {
			$sendback = TablePress::url( array( 'action' => 'list', 'message' => 'success_copy', 'table_id' => $return_item ) );
		} else {
			$sendback = remove_query_arg( array( 'action', 'message', 'table_id' ), $sendback );
			$sendback = add_query_arg( array( 'action' => 'list', 'message' => 'success_copy', 'table_id' => $return_item ), $sendback );
		}
		wp_redirect( $sendback );
		exit;
	}

	/**
	 * Preview a table
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_preview_table() {
		$table_id = ( ! empty( $_GET['item'] ) ) ? $_GET['item'] : false;
		TablePress::check_nonce( 'preview_table', $table_id );

		$this->init_i18n_support();

		if ( false === $table_id ) // nonce check should actually catch this already
			wp_die( __( 'The preview could not be loaded.', 'tablepress' ), __( 'Preview', 'tablepress' ) );

		if ( ! current_user_can( 'tablepress_preview_table', $table_id ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );

		// Load existing table from DB
		$table = $this->model_table->load( $table_id );
		if ( false === $table )
			wp_die( __( 'The table could not be loaded.', 'tablepress' ), __( 'Preview', 'tablepress' ) );

		// Create a render class instance
		$_render = TablePress::load_class( 'TablePress_Render', 'class-render.php', 'classes' );
		// Merge desired options with default render options (as not all of them are stored in the table options, but are just Shortcode parameters)
		$render_options = shortcode_atts( $_render->get_default_render_options(), $table['options'] );
		$_render->set_input( $table, $render_options );
		$view_data = array(
			'table_id' => $table_id,
			'head_html' => $_render->get_preview_css(),
			'body_html' => $_render->get_output()
		);

		if ( $this->model_options->get( 'use_custom_css_file' ) ) {
			$custom_css = $this->model_options->load_custom_css_from_file( 'normal' );
			// fall back to "Custom CSS" in options, if it could not be retrieved from file
			if ( false === $custom_css )
				$custom_css = $this->model_options->get( 'custom_css' );
		} else {
			// get "Custom CSS" from options
			$custom_css = $this->model_options->get( 'custom_css' );
		}
		if ( ! empty( $custom_css ) )
			$view_data['head_html'] .= "<style type=\"text/css\">\n{$custom_css}\n</style>\n";

		// Prepare, initialize, and render the view
		$this->view = TablePress::load_view( 'preview_table', $view_data );
		$this->view->render();
	}

	/**
	 * Show a list of tables in the Editor toolbar Thickbox (opened by TinyMCE or Quicktags button)
	 *
	 * @since 1.0.0
	 */
	public function handle_get_action_editor_button_thickbox() {
		TablePress::check_nonce( 'editor_button_thickbox' );

		$this->init_i18n_support();

		$view_data = array(
			'tables' => $this->model_table->load_all() // does not contain table data
		);

		set_current_screen( 'tablepress_editor_button_thickbox' );

		// Prepare, initialize, and render the view
		$this->view = TablePress::load_view( 'editor_button_thickbox', $view_data );
		$this->view->render();
	}

} // class TablePress_Admin_Controller