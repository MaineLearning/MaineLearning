<?php
/**
 * Admin Controller for WP-Table Reloaded with functions for the backend
 *
 * @package WP-Table Reloaded
 * @subpackage Admin Controller
 * @author Tobias B&auml;thge
 * @since 1.6
 */

/**
 * Include file with the Base Controller Class
 */
require_once ( WP_TABLE_RELOADED_ABSPATH . 'controllers/controller-base.php' );

/**
 * Define the WP-Table Reloaded Text Domain, used to separate between plugin and core localization
 */
define( 'WP_TABLE_RELOADED_TEXTDOMAIN', 'wp-table-reloaded' );

/**
 * Admin Controller class, extends Base Controller Class
 */
class WP_Table_Reloaded_Controller_Admin extends WP_Table_Reloaded_Controller_Base {

    /**
     * Nonce for security of links/forms, to prevent "CSRF"
     * @var string
     */
    var $nonce_base = 'wp-table-reloaded-nonce';

    /**
     * List of allowed actions that a user can perform with tables or the plugin
     * @var array
     */
    var $allowed_actions = array( 'list', 'add', 'edit', 'bulk_edit', 'copy', 'delete', 'import', 'export', 'options', 'uninstall', 'about', 'hide_donate_nag', 'hide_welcome_message' );
                            // 'ajax_list', 'ajax_preview' also exist, but are handled separately

    /**
     * current action that is performed in this page load, populated in load_manage_page()
     * @var string
     */
    var $action = 'list';

    /**
     * List of available translations of WP-Table Reloaded, init in __construct, because of the translations
     * @var array
     */
    var $available_plugin_languages = array();

    /**
     * Default plugin options and their default values, fresh installs use those, updated installs update their options accordingly
     * @var array
     */
    var $default_options = array(
        'installed_version' => '0',
        'plugin_language' => 'auto',
        'uninstall_upon_deactivation' => false,
        'show_exit_warning' => true,
        'growing_textareas' => true,
        'use_datatables_on_table_list' => true,
        'add_target_blank_to_links' => false,
        'enable_tablesorter' => true,
        'tablesorter_script' => 'datatables', // others are 'datatables-tabletools', 'tablesorter', and 'tablesorter_extended'
        'use_default_css' => true,
        'use_custom_css' => true,
        'custom_css' => '',
        'enable_search' => true,
        'admin_menu_parent_page' => 'tools.php',
        'user_access_plugin' => 'author', // others are 'contributor', 'editor', and 'admin'
        'user_access_plugin_options' => 'author', // others are 'editor', and 'admin'
        'frontend_edit_table_link' => true,
        'install_time' => 0,
        'show_donate_nag' => true,
        'show_welcome_message' => 0, // 0 = no message, 1 = install message, 2 = update message
        'update_message' => array(),
        'last_id' => 0
    );
    
    /**
     * Default list of tables (empty, because there are no tables right after installation)
     * @var array
     */
    var $default_tables = array();

    /**
     * Instance of the WP_Table_Reloaded_Helper class, which has additional functions for frontend and backend, stored in separate file for better overview and maintenance
     * @var object
     */
    var $helper;

    /**
     * Instance of the WP_Table_Reloaded_Export class
     * @var object
     */
    var $export_instance;
    
    /**
     * Instance of the WP_Table_Reloaded_Import class
     * @var object
     */
    var $import_instance;

    /**
     * Hook (i.e. name) WordPress uses for the WP-Table Reloaded page, needed for certain plugin actions and filters, populated in add_manage_page()
     * @var string
     */
    var $hook = '';

    /**
     * Name of the file, WP-Table Reloaded is accessible under, dependant of whether admin has moved the WP-Table Reloaded menu entry
     * @var string
     */
    var $page_url = '';

    /**
     * PHP4 class constructor, calls the PHP5 class constructor __construct()
     */
    function WP_Table_Reloaded_Controller_Admin() {
        $this->__construct();
    }

    /**
     * PHP5 class constructor
     *
     * Initiate Backend functionality, by checking for AJAX calls, eventually answering those or setting up the admin page
     */
    function __construct() {
        register_activation_hook( WP_TABLE_RELOADED__FILE__, array( &$this, 'plugin_activation_hook' ) );
        register_deactivation_hook( WP_TABLE_RELOADED__FILE__, array( &$this, 'plugin_deactivation_hook' ) );

        $this->helper = $this->create_class_instance( 'WP_Table_Reloaded_Helper', 'helper.class.php' );

        // load plugin options and existing tables
        $this->init_plugin();

        // WordPress 3.1 requires new update check
        if ( version_compare( $this->options['installed_version'], WP_TABLE_RELOADED_PLUGIN_VERSION, '<' ) )
            add_action( 'init', array( &$this, 'plugin_update' ) );

        // init variables to check whether we do valid AJAX
        $doing_ajax = defined( 'DOING_AJAX' ) ? DOING_AJAX : false;
        $valid_ajax_call = ( isset( $_GET['page'] ) && $this->page_slug == $_GET['page'] ) ? true : false;

        // have to check for possible "export all" request this early,
        // because otherwise http-headers will be sent by WP before we can send download headers
        if ( !$doing_ajax && $valid_ajax_call && isset( $_POST['export_all'] ) ) {
            // can be done in plugins_loaded, as no language support is needed
            add_action( 'plugins_loaded', array( &$this, 'do_action_export_all' ) );
            $doing_ajax = true;
        }
        // have to check for possible export file download request this early,
        // because otherwise http-headers will be sent by WP before we can send download headers
        if ( !$doing_ajax && $valid_ajax_call && isset( $_POST['download_export_file'] ) && 'true' == $_POST['download_export_file'] ) {
            // can be done in plugins_loaded, as no language support is needed
            add_action( 'plugins_loaded', array( &$this, 'do_action_export' ) );
            $doing_ajax = true;
        }
        // have to check for possible call by editor button to show list of tables
        // and possible call to show a table preview in a thickbox on "List Tables" screen
        if ( !$doing_ajax && $valid_ajax_call && isset( $_GET['action'] ) && ( 'ajax_list' == $_GET['action'] || 'ajax_preview' == $_GET['action'] ) ) {
            // can not be done earlier, because we need language support
            add_action( 'init', array( &$this, 'do_action_' . $_GET['action'] ) );
            $doing_ajax = true;
        }

        // we are not doing AJAX, so we call the main plugin handler
        if ( !$doing_ajax ) {
            add_action( 'admin_menu', array( &$this, 'add_manage_page' ) );

            // add JS to add button to editor on admin pages that might have an editor
            $pages_with_editor_button = array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' );
            foreach ( $pages_with_editor_button as $page )
                add_action( 'load-' . $page, array( &$this, 'add_editor_button' ) );
        }

        // add message to list of plugins, if an update is available / add additional links on Plugins page, for both regular visits and AJAX calls
        add_action( 'in_plugin_update_message-' . WP_TABLE_RELOADED_BASENAME, array( &$this, 'add_plugin_update_message' ), 10, 2 );
        add_filter( 'plugin_row_meta', array( &$this, 'add_plugin_row_meta' ), 10, 2);
    }

    /**
     * Add admin page to the correct place in the admin menu, and set handler for when page is loaded or shown
     */
    function add_manage_page() {
        // user needs at least this capability to view WP-Table Reloaded config page
        // capabilities from http://codex.wordpress.org/Roles_and_Capabilities
        $user_group = $this->options['user_access_plugin'];
        $capabilities = array(
            'admin' => 'manage_options',
            'editor' => 'publish_pages',
            'author' => 'publish_posts',
            'contributor' => 'edit_posts'
        );
        $min_capability = isset( $capabilities[ $user_group ] ) ? $capabilities[ $user_group ] : 'manage_options';
        $min_capability = apply_filters( 'wp_table_reloaded_min_needed_capability', $min_capability ); // plugins may filter/change this though
        
        $display_name = 'WP-Table Reloaded'; // the name that is displayed in the admin menu on the left
        $display_name = apply_filters( 'wp_table_reloaded_plugin_display_name', $display_name ); // can be filtered to something shorter maybe

        $admin_menu_page = apply_filters( 'wp_table_reloaded_admin_menu_parent_page', $this->options['admin_menu_parent_page'] );
        // backward-compatibility for the filter
        if ( 'top-level' == $admin_menu_page )
            $admin_menu_page = 'admin.php';
        // 'edit-pages.php' was renamed to 'edit.php?post_type=page' in WP 3.0
        if ( 'edit-pages.php' == $admin_menu_page )
            $admin_menu_page = 'edit.php?post_type=page';
        if ( !in_array( $admin_menu_page, $this->possible_admin_menu_parent_pages ) )
            $admin_menu_page = 'tools.php';

        // Top-Level menu is created in different function, all others are created with the filename as a parameter
        if ( 'admin.php' == $admin_menu_page )
            $this->hook = add_menu_page( 'WP-Table Reloaded', $display_name, $min_capability, $this->page_slug, array( &$this, 'show_manage_page' ), plugins_url( 'admin/plugin-icon-small.png', WP_TABLE_RELOADED__FILE__ ) );
        else
            $this->hook = add_submenu_page( $admin_menu_page, 'WP-Table Reloaded', $display_name, $min_capability, $this->page_slug, array( &$this, 'show_manage_page' ) );
        $this->page_url = $admin_menu_page;

        add_action( 'load-' . $this->hook, array( &$this, 'load_manage_page' ) );
    }

    /**
     * Function is loaded by WordPress, if WP-Table Reloaded's admin menu entry is called,
     * Load the scripts, stylesheets and language, all of this will be done before the page is shown by show_manage_page()
     */
    function load_manage_page() {
        // show admin footer message (only on pages of WP-Table Reloaded)
		add_filter( 'admin_footer_text', array( &$this->helper, 'add_admin_footer_text' ) );

        $this->init_language_support();

        // get and check action parameter from passed variables
        $default_action = 'list';
        $default_action = apply_filters( 'wp_table_reloaded_default_action', $default_action );
        $this->allowed_actions = apply_filters( 'wp_table_reloaded_allowed_actions', $this->allowed_actions );
        $action = ( !empty( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : $default_action;
        // check if action is in allowed actions and if method is callable, if yes, call it
        if ( in_array( $action, $this->allowed_actions ) )
            $this->action = $action;

        add_thickbox();
        // load js and css for admin, needs to stand below thickbox script
        $this->add_manage_page_js(); // will add script to footer
        $this->add_manage_page_css(); // needs to be added to the header

        // done after determining the action, because needs action parameter to load correct help string
        add_contextual_help( $this->hook, $this->helper->get_contextual_help_string() );
    }

    /**
     * Function is loaded by WordPress, if WP-Table Reloaded's admin menu entry is called,
     * responsible for calling the appropriate action handler, output of WP admin menu and header is already done here
     */
    function show_manage_page() {
        $this->available_plugin_languages = array(
            'ar'    => __( 'Arabic', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'be_BY' => __( 'Belarusian', WP_TABLE_RELOADED_TEXTDOMAIN ),
			'bg_BG' => __( 'Bulgarian', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'cs_CZ' => __( 'Czech', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'de_DE' => __( 'German', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'en_US' => __( 'English', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'es_ES' => __( 'Spanish', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'fi'    => __( 'Finnish', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'fr_FR' => __( 'French', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'he_IL' => __( 'Hebrew', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'hi_IN' => __( 'Hindi', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'id_ID' => __( 'Indonesian', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'it_IT' => __( 'Italian', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'ja'    => __( 'Japanese', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'nl_NL' => __( 'Dutch', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'pl_PL' => __( 'Polish', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'pt_BR' => __( 'Brazilian Portuguese', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'pt_PT' => __( 'Portuguese (Portugal)', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'ru_RU' => __( 'Russian', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'sk_SK' => __( 'Slovak', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'sv_SE' => __( 'Swedish', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'ua_UA' => __( 'Ukrainian', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'zh_CN' => __( 'Chinese (Simplified)', WP_TABLE_RELOADED_TEXTDOMAIN ),
            // the following are inactive because they are not up-to-date
            // 'ga_IR' => __( 'Irish', WP_TABLE_RELOADED_TEXTDOMAIN ),
            // 'sq_AL' => __( 'Albanian', WP_TABLE_RELOADED_TEXTDOMAIN ),
            // 'tr_TR' => __( 'Turkish', WP_TABLE_RELOADED_TEXTDOMAIN ),
        );
        asort( $this->available_plugin_languages );

        // do WP plugin action (before action is fired) -> can stop further plugin execution by returning true
        $overwrite = apply_filters( 'wp_table_reloaded_action_pre_' . $this->action, false );
        if ( $overwrite )
            return;
    
        // call appropriate action, $this->action is populated in load_manage_page
        if ( is_callable( array( &$this, 'do_action_' . $this->action ) ) )
            call_user_func( array( &$this, 'do_action_' . $this->action ) );
    }
    
    // ###################################################################################################################
    // ##########################################                   ######################################################
    // ##########################################      ACTIONS      ######################################################
    // ##########################################                   ######################################################
    // ###################################################################################################################

    /**
     * "List Tables" action handler
     */
    function do_action_list() {
        $messages = array(
            0 => false,
            1 => sprintf( __( 'Welcome to WP-Table Reloaded %s. If you encounter any questions or problems, please refer to the <a href="%s">FAQ</a>, the <a href="%s">documentation</a>, and the <a href="%s">support</a> section.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->options['installed_version'], 'http://tobias.baethge.com/go/wp-table-reloaded/faq/', 'http://tobias.baethge.com/go/wp-table-reloaded/documentation/', 'http://tobias.baethge.com/go/wp-table-reloaded/support/' ),
            2 => sprintf( __( 'Thank you for upgrading to WP-Table Reloaded %s.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->options['installed_version'] ) . ' ' . __( 'This version includes several bugfixes and a few enhancements.', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . sprintf( __( 'Please read the <a href="%s">release announcement</a> for more information.', WP_TABLE_RELOADED_TEXTDOMAIN ), "http://tobias.baethge.com/go/wp-table-reloaded/release-announcement/{$this->options['installed_version']}/" ) . '<br/>' . sprintf( __( 'If you like the new features and enhancements, I would appreciate a small <a href="%s">donation</a>. Thank you.', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/donate/' )
        );
        $message = ( isset( $messages[ $this->options['show_welcome_message'] ] ) ) ? $messages[ $this->options['show_welcome_message'] ] : false;
        if ( $message ) {
            $hide_welcome_message_url = $this->get_action_url( array( 'action' => 'hide_welcome_message' ), true );
            $this->helper->print_header_message( $message . '<br/><br/>' . sprintf( '<a href="%s" style="font-weight:normal;">%s</a>', $hide_welcome_message_url, __( 'Hide this message', WP_TABLE_RELOADED_TEXTDOMAIN ) ) );
        }

        if ( $this->may_print_donate_nag() ) {
            $donate_url = 'http://tobias.baethge.com/go/wp-table-reloaded/donate/message/';
            $donated_true_url = $this->get_action_url( array( 'action' => 'hide_donate_nag', 'user_donated' => true ), true );
            $donated_false_url = $this->get_action_url( array( 'action' => 'hide_donate_nag', 'user_donated' => false ), true );
            $this->helper->print_header_message(
                __( 'Thanks for using this plugin! You\'ve installed WP-Table Reloaded over a month ago.', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . sprintf( _n( 'If it works and you are satisfied with the results of managing your %s table, isn\'t it worth at least one dollar or euro?', 'If it works and you are satisfied with the results of managing your %s tables, isn\'t it worth at least one dollar or euro?', count( $this->tables ), WP_TABLE_RELOADED_TEXTDOMAIN ), count( $this->tables ) ) . '<br/><br/>' .
                sprintf( __( '<a href="%s">Donations</a> help me to continue support and development of this <i>free</i> software - things for which I spend countless hours of my free time! Thank you!', WP_TABLE_RELOADED_TEXTDOMAIN ), $donate_url ) . '<br/><br/>' .
                sprintf( '<a href="%s" target="_blank">%s</a>', $donate_url, __( 'Sure, no problem!', WP_TABLE_RELOADED_TEXTDOMAIN ) ) . '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' .
                sprintf( '<a href="%s" style="font-weight:normal;">%s</a>', $donated_true_url, __( 'I already donated.', WP_TABLE_RELOADED_TEXTDOMAIN ) ) . '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' .
                sprintf( '<a href="%s" style="font-weight:normal;">%s</a>', $donated_false_url, __( 'No, thanks. Don\'t ask again.', WP_TABLE_RELOADED_TEXTDOMAIN ) )
            );
        }

        $this->load_view( 'list' );
    }
    
    /**
     * "Add new Table" action handler
     */
    function do_action_add() {
        if ( isset( $_POST['submit'] ) && isset( $_POST['table'] ) ) {
            check_admin_referer( $this->get_nonce( 'add' ) );

            $rows = ( 0 < $_POST['table']['rows'] ) ? $_POST['table']['rows'] : 1;
            $cols = ( 0 < $_POST['table']['cols'] ) ? $_POST['table']['cols'] : 1;

            $table = $this->default_table;

            $table['id'] = $this->get_new_table_id();
            $table['data'] = $this->helper->create_empty_table( $rows, $cols );
            $table['visibility']['rows'] = array_fill( 0, $rows, false );
            $table['visibility']['columns'] = array_fill( 0, $cols, false );
            $table['name'] = $_POST['table']['name'];
            $table['description'] = $_POST['table']['description'];

            $this->save_table( $table );

            $this->helper->print_header_message( sprintf( __( 'Table &quot;%s&quot; added successfully.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table['name'] ) ) );
            $table_id = $table['id'];
            $this->load_view( 'edit', compact( 'table_id' ) );
        } else {
            $this->load_view( 'add' );
        }
    }

    /**
     * "Edit Table" action handler
     */
    function do_action_edit() {
        if ( isset( $_POST['submit'] ) && isset( $_POST['table'] ) ) {
            check_admin_referer( $this->get_nonce( 'edit' ) );
            
            $subactions = array_keys( $_POST['submit'] );
            $subaction = $subactions[0];
            
            switch( $subaction ) {
            case 'update':
            case 'save_back':
                $table = $_POST['table'];   // careful here to not miss any stuff!!! (options, etc.)
                // do we want to change the ID?
                $new_table_id = ( isset( $_POST['table_id'] ) ) ? $_POST['table_id'] : $table['id'] ;
                if ( $new_table_id != $table['id'] && is_numeric( $new_table_id ) && ( 0 < $new_table_id ) ) {
                    if ( !$this->table_exists( $new_table_id ) ) {
                        // delete table with old ID
                        $old_table_id = $table['id'];
                        $this->delete_table( $old_table_id );
                        // set new table ID
                        $table['id'] = $new_table_id;
                        $message = sprintf( __( "Table edited successfully. This Table now has the ID %s. You'll need to adjust existing shortcodes accordingly.", WP_TABLE_RELOADED_TEXTDOMAIN ), $new_table_id );
                    } else {
                        $message = sprintf( __( 'The ID could not be changed from %s to %s, because there already is a Table with that ID.', WP_TABLE_RELOADED_TEXTDOMAIN ), $table['id'], $new_table_id );
                    }
                } else {
                    $message = __( 'Table edited successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                }
                // save table options (checkboxes!), only checked checkboxes are submitted (then as true)
                $table['options']['alternating_row_colors'] = isset( $_POST['table']['options']['alternating_row_colors'] );
                $table['options']['row_hover'] = isset( $_POST['table']['options']['row_hover'] );
                $table['options']['first_row_th'] = isset( $_POST['table']['options']['first_row_th'] );
                $table['options']['table_footer'] = isset( $_POST['table']['options']['table_footer'] );
                $table['options']['print_name'] = isset( $_POST['table']['options']['print_name'] );
                $table['options']['print_description'] = isset( $_POST['table']['options']['print_description'] );
                $table['options']['cache_table_output'] = isset( $_POST['table']['options']['cache_table_output'] );
                $table['options']['custom_css_class'] = trim( $table['options']['custom_css_class'] ); // more complex sanitize_* functions would change spaces to hyphens...
                $table['options']['use_tablesorter'] = isset( $_POST['table']['options']['use_tablesorter'] );
                $table['options']['datatables_sort'] = isset( $_POST['table']['options']['datatables_sort'] );
                $table['options']['datatables_paginate'] = isset( $_POST['table']['options']['datatables_paginate'] );
                $table['options']['datatables_lengthchange'] = isset( $_POST['table']['options']['datatables_lengthchange'] );
                $table['options']['datatables_filter'] = isset( $_POST['table']['options']['datatables_filter'] );
                $table['options']['datatables_info'] = isset( $_POST['table']['options']['datatables_info'] );
                $table['options']['datatables_tabletools'] = isset( $_POST['table']['options']['datatables_tabletools'] );
                $table['options']['datatables_paginate_entries'] = ( is_numeric( $table['options']['datatables_paginate_entries'] ) ) ? absint( $table['options']['datatables_paginate_entries'] ) : $this->default_table['options']['datatables_paginate_entries'];
                // $table['options']['datatables_customcommands'] is an input type=text field that is always submitted
                // $table['options']['print_name|description_position'] are select fields that are always submitted

                // save visibility settings (checkboxes!)
                foreach ( $table['data'] as $row_idx => $row )
                    $table['visibility']['rows'][$row_idx] = ( isset( $_POST['table']['visibility']['rows'][$row_idx] ) && ( 'true' == $_POST['table']['visibility']['rows'][$row_idx] ) );
                foreach ( $table['data'][0] as $col_idx => $col )
                    $table['visibility']['columns'][$col_idx] = ( isset( $_POST['table']['visibility']['columns'][$col_idx] ) && ( 'true' == $_POST['table']['visibility']['columns'][$col_idx] ) );

                if ( !empty( $table['custom_fields'] ) )
                    uksort( $table['custom_fields'], 'strnatcasecmp' ); // sort the keys naturally

                $this->save_table( $table );
                break;
            case 'swap_rows':
                $table_id = $_POST['table']['id'];
                $row_id1 = ( isset( $_POST['swap']['row'][1] ) ) ? $_POST['swap']['row'][1] : -1;
                $row_id2 = ( isset( $_POST['swap']['row'][2] ) ) ? $_POST['swap']['row'][2] : -1;
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                // swap rows $row_id1 and $row_id2
                if ( ( 1 < $rows ) && ( -1 < $row_id1 ) && ( -1 < $row_id2 ) && ( $row_id1 != $row_id2 ) ) {
                    $temp_row = $table['data'][$row_id1];
                    $table['data'][$row_id1] = $table['data'][$row_id2];
                    $table['data'][$row_id2] = $temp_row;
                    $temp_visibility = $table['visibility']['rows'][$row_id1];
                    $table['visibility']['rows'][$row_id1] = $table['visibility']['rows'][$row_id2];
                    $table['visibility']['rows'][$row_id2] = $temp_visibility;
                }
                $this->save_table( $table );
                $message = __( 'Rows swapped successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'swap_cols':
                $table_id = $_POST['table']['id'];
                $col_id1 = ( isset( $_POST['swap']['col'][1] ) ) ? $_POST['swap']['col'][1] : -1;
                $col_id2 = ( isset( $_POST['swap']['col'][2] ) ) ? $_POST['swap']['col'][2] : -1;
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                // swap rows $col_id1 and $col_id2
                if ( ( 1 < $cols ) && ( -1 < $col_id1 ) && ( -1 < $col_id2 ) && ( $col_id1 != $col_id2 ) ) {
                    foreach ( $table['data'] as $row_idx => $row ) {
                        $temp_col = $table['data'][$row_idx][$col_id1];
                        $table['data'][$row_idx][$col_id1] = $table['data'][$row_idx][$col_id2];
                        $table['data'][$row_idx][$col_id2] = $temp_col;
                    }
                    $temp_visibility = $table['visibility']['columns'][$col_id1];
                    $table['visibility']['columns'][$col_id1] = $table['visibility']['columns'][$col_id2];
                    $table['visibility']['columns'][$col_id2] = $temp_visibility;
                }
                $this->save_table( $table );
                $message = __( 'Columns swapped successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'sort':
                $table_id = $_POST['table']['id'];
                $column = ( isset( $_POST['sort']['col'] ) ) ? $_POST['sort']['col'] : -1;
                $sort_order = ( isset( $_POST['sort']['order'] ) ) ? $_POST['sort']['order'] : 'ASC';
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                // sort array for $column in $sort_order
                if ( ( 1 < $rows ) && ( -1 < $column ) ) {
                    // for sorting: temporarily store row visibility in data, so that it gets sorted, too
                    foreach ( $table['data'] as $row_idx => $row )
                        array_splice( $table['data'][$row_idx], $cols, 0, $table['visibility']['rows'][$row_idx] );

                    $array_to_sort = $table['data'];
                    if ( isset( $table['options']['first_row_th'] ) && $table['options']['first_row_th'] )
                        $first_row = array_shift( $array_to_sort );
                    if ( isset( $table['options']['table_footer'] ) && $table['options']['table_footer'] )
                        $last_row = array_pop( $array_to_sort );

                    $sortarray = $this->create_class_instance( 'arraysort', 'arraysort.class.php' );
                    $sortarray->input_array = $array_to_sort;
                    $sortarray->column = $column;
                    $sortarray->order = $sort_order;
                    $sortarray->sort();
                    $sorted_array = $sortarray->sorted_array;

                    if ( isset( $table['options']['first_row_th'] ) && $table['options']['first_row_th'] )
                        array_unshift( $sorted_array, $first_row );
                    if ( isset( $table['options']['table_footer'] ) && $table['options']['table_footer'] )
                        array_push( $sorted_array, $last_row );
                    $table['data'] = $sorted_array;

                    // then restore row visibility from sorted data and remove temporary column
                    foreach ( $table['data'] as $row_idx => $row ) {
                        $table['visibility']['rows'][$row_idx] = $table['data'][$row_idx][$cols];
                        array_splice( $table['data'][$row_idx], $cols, 1 );
                    }
                }
                $this->save_table( $table );
                $message = __( 'Table sorted successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'move_row':
                $table_id = $_POST['table']['id'];
                $row_id1 = ( isset( $_POST['move']['row'][1] ) ) ? $_POST['move']['row'][1] : -1;
                $row_id2 = ( isset( $_POST['move']['row'][2] ) ) ? $_POST['move']['row'][2] : -1;
                $move_where = ( isset( $_POST['move']['row']['where'] ) ) ? $_POST['move']['row']['where'] : 'before';
                if ( 'after' == $move_where )
                    $row_id2 = $row_id2 + 1; // move after is the same as move before the next row
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                // move row $row_id1 before/after $row_id2
                if ( ( 1 < $rows ) && ( -1 < $row_id1 ) && ( -1 < $row_id2 ) && ( $row_id1 != $row_id2 ) ) {
                    if ( $row_id2 > $row_id1 )
                        $row_id2 = $row_id2 - 1; // if target higher than source, source element is removed, so target index smaller by one
                    $temp_row = array( $table['data'][$row_id1] );
                    unset( $table['data'][$row_id1] );
                    array_splice( $table['data'], $row_id2, 0, $temp_row );
                    $temp_visibility = $table['visibility']['rows'][$row_id1];
                    unset( $table['visibility']['rows'][$row_id1] );
                    array_splice( $table['visibility']['rows'], $row_id2, 0, $temp_visibility );
                }
                $this->save_table( $table );
                $message = __( 'Row moved successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'move_col':
                $table_id = $_POST['table']['id'];
                $col_id1 = ( isset( $_POST['move']['col'][1] ) ) ? $_POST['move']['col'][1] : -1;
                $col_id2 = ( isset( $_POST['move']['col'][2] ) ) ? $_POST['move']['col'][2] : -1;
                $move_where = ( isset( $_POST['move']['col']['where'] ) ) ? $_POST['move']['col']['where'] : 'before';
                if ( 'after' == $move_where )
                    $col_id2 = $col_id2 + 1; // move after is the same as move before the next row
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                // move col $col_id1 before/after $col_id2
                if ( ( 1 < $cols ) && ( -1 < $col_id1 ) && ( -1 < $col_id2 ) && ( $col_id1 != $col_id2 ) ) {
                    if ( $col_id2 > $col_id1 )
                        $col_id2 = $col_id2 - 1; // if target higher than source, source element is removed, so target index smaller by one
                    foreach ( $table['data'] as $row_idx => $row ) {
                        $temp_col = $table['data'][$row_idx][$col_id1];
                        unset( $table['data'][$row_idx][$col_id1] );
                        array_splice( $table['data'][$row_idx], $col_id2, 0, $temp_col );

                    }
                    $temp_visibility = $table['visibility']['columns'][$col_id1];
                    unset( $table['visibility']['columns'][$col_id1] );
                    array_splice( $table['visibility']['columns'], $col_id2, 0, $temp_visibility );
                }
                $this->save_table( $table );
                $message = __( 'Column moved successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'delete_rows':
                $table_id = $_POST['table']['id'];
                $delete_rows = ( isset( $_POST['table_select']['rows'] ) ) ? $_POST['table_select']['rows'] : array();
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                $message = _n( 'Row could not be deleted.', 'Rows could not be deleted.', count( $delete_rows ), WP_TABLE_RELOADED_TEXTDOMAIN ); // only used if deletion fails below
                if ( ( 1 < $rows ) && ( 0 < count( $delete_rows ) ) && ( count( $delete_rows ) < $rows ) ) {
                    // remove rows and re-index
                    foreach ( $delete_rows as $row_idx => $value) {
                        unset( $table['data'][$row_idx] );
                        unset( $table['visibility']['rows'][$row_idx] );
                    }
                    $table['data'] = array_merge( $table['data'] );
                    $table['visibility']['rows'] = array_merge( $table['visibility']['rows'] );
                    $message = _n( 'Row deleted successfully.', 'Rows deleted successfully.', count( $delete_rows ), WP_TABLE_RELOADED_TEXTDOMAIN );
                }
                $this->save_table( $table );
                break;
            case 'delete_cols':
                $table_id = $_POST['table']['id'];
                $delete_columns = ( isset( $_POST['table_select']['columns'] ) ) ? $_POST['table_select']['columns'] : array();
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                $message = _n( 'Column could not be deleted.', 'Columns could not be deleted.', count( $delete_columns ), WP_TABLE_RELOADED_TEXTDOMAIN ); // only used if deletion fails below
                if ( ( 1 < $cols ) && ( 0 < count( $delete_columns ) ) && ( count( $delete_columns ) < $cols ) ) {
                    foreach ( $table['data'] as $row_idx => $row ) {
                        // remove columns and re-index
                        foreach ( $delete_columns as $col_idx => $value) {
                            unset( $table['data'][$row_idx][$col_idx] );
                        }
                        $table['data'][$row_idx] = array_merge( $table['data'][$row_idx] );
                    }
                    foreach ( $delete_columns as $col_idx => $value) {
                        unset( $table['visibility']['columns'][$col_idx] );
                    }
                    $table['visibility']['columns'] = array_merge( $table['visibility']['columns'] );
                    $message = _n( 'Column deleted successfully.', 'Columns deleted successfully.', count( $delete_columns ), WP_TABLE_RELOADED_TEXTDOMAIN );
                }
                $this->save_table( $table );
                break;
            case 'insert_rows': // insert row before each selected row
                $table_id = $_POST['table']['id'];
                $insert_rows = ( isset( $_POST['table_select']['rows'] ) ) ? $_POST['table_select']['rows'] : array();
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;

                // insert rows and re-index
                $row_change = 0; // row_change is growing parameter, needed because indices change
                $new_row = array( array_fill( 0, $cols, '' ) );
                foreach ( $insert_rows as $row_idx => $value) {
                    $row_id = $row_idx + $row_change;
                    // init new empty row (with all columns) and insert it before row with key $row_id
                    array_splice( $table['data'], $row_id, 0, $new_row );
                    array_splice( $table['visibility']['rows'], $row_id, 0, false );
                    $row_change++;
                }
                
                $this->save_table( $table );
                $message = _n( 'Row inserted successfully.', 'Rows inserted successfully.', count( $insert_rows ), WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'insert_cols': // insert column before each selected column
                $table_id = $_POST['table']['id'];
                $insert_columns = ( isset( $_POST['table_select']['columns'] ) ) ? $_POST['table_select']['columns'] : array();
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;

                // insert cols and re-index
                $new_col = '';
                foreach ( $table['data'] as $row_idx => $row ) {
                    $col_change = 0; // col_change is growing parameter, needed because indices change
                    foreach ( $insert_columns as $col_idx => $value) {
                        $col_id = $col_idx + $col_change;
                        array_splice( $table['data'][$row_idx], $col_id, 0, $new_col );
                        $col_change++;
                    }
                }
                $col_change = 0; // col_change is growing parameter, needed because indices change
                foreach ( $insert_columns as $col_idx => $value) {
                    $col_id = $col_idx + $col_change;
                    array_splice( $table['visibility']['columns'], $col_id, 0, false );
                    $col_change++;
                }

                $this->save_table( $table );
                $message = _n( 'Column inserted successfully.', 'Columns inserted successfully.', count( $insert_columns ), WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'append_rows':
                $table_id = $_POST['table']['id'];
                $number = ( isset( $_POST['insert']['row']['number'] ) && ( 0 < $_POST['insert']['row']['number'] ) ) ? $_POST['insert']['row']['number'] : 1;
                $row_id = $_POST['insert']['row']['id'];
                $table = $this->load_table( $table_id );
                $rows = count( $table['data'] );
                $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                // init new empty row (with all columns) and insert it before row with key $row_id
                $new_rows = $this->helper->create_empty_table( $number, $cols, '' );
                $new_rows_visibility = array_fill( 0, $number, false );
                array_splice( $table['data'], $row_id, 0, $new_rows );
                array_splice( $table['visibility']['rows'], $row_id, 0, $new_rows_visibility );
                $this->save_table( $table );
                $message = _n( 'Row added successfully.', 'Rows added successfully.', $number, WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'append_cols':
                $table_id = $_POST['table']['id'];
                $number = ( isset( $_POST['insert']['col']['number'] ) && ( 0 < $_POST['insert']['col']['number'] ) ) ? $_POST['insert']['col']['number'] : 1;
                $col_id = $_POST['insert']['col']['id'];
                $table = $this->load_table( $table_id );
                // init new empty row (with all columns) and insert it before row with key $col_id
                $new_cols = array_fill( 0, $number, '' );
                $new_cols_visibility = array_fill( 0, $number, false );
                foreach ( $table['data'] as $row_idx => $row )
                    array_splice( $table['data'][$row_idx], $col_id, 0, $new_cols );
                array_splice( $table['visibility']['columns'], $col_id, 0, $new_cols_visibility );
                $this->save_table( $table );
                $message = _n( 'Column added successfully.', 'Columns added successfully.', $number, WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            case 'insert_cf':
                $table_id = $_POST['table']['id'];
                $table = $this->load_table( $table_id );
                $name = ( isset( $_POST['insert']['custom_field'] ) ) ? $_POST['insert']['custom_field'] : '';
                if ( empty( $name ) ) {
                    $message = __( 'Could not add Custom Data Field, because you did not enter a name.', WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                }
                $reserved_names = array( 'name', 'description', 'last_modified', 'last_editor' );
                if ( in_array( $name, $reserved_names ) ) {
                    $message = __( 'Could not add Custom Data Field, because the name you entered is reserved for other table data.', WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                }
                // Name can only contain lowercase letters, numbers, _ and - (like permalink slugs)
                $clean_name = sanitize_title_with_dashes( $name );
                if ( $name != $clean_name ) {
                    $message = __( 'Could not add Custom Data Field, because the name contained illegal characters.', WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                }
                if ( isset( $table['custom_fields'][$name] ) ) {
                    $message = __( 'Could not add Custom Data Field, because a Field with that name already exists.', WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                }
                $table['custom_fields'][$name] = '';
                uksort( $table['custom_fields'], 'strnatcasecmp' ); // sort the keys naturally
                $this->save_table( $table );
                $message = __( 'Custom Data Field added successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                break;
            default:
                $this->do_action_list();
                return;
            }

            $this->helper->print_header_message( $message );
            if ( 'save_back' == $subaction ) {
                $this->do_action_list();
            } else {
                $table_id = $table['id'];
                $this->load_view( 'edit', compact( 'table_id' ) );
            }
        } elseif ( isset( $_GET['table_id'] ) && $this->table_exists( $_GET['table_id'] ) ) {
            $table_id = $_GET['table_id'];
            $this->load_view( 'edit', compact( 'table_id' ) );
        } else {
            $this->do_action_list();
        }
    }

    /**
     * "Bulk Edit" action handler
     */
    function do_action_bulk_edit() {
        if ( isset( $_POST['submit'] ) ) {
            check_admin_referer( $this->get_nonce( 'bulk_edit' ) );

            if ( isset( $_POST['tables'] ) ) {

                $subactions = array_keys( $_POST['submit'] );
                $subaction = $subactions[0];

                switch( $subaction ) {
                case 'copy': // see do_action_copy for explanations
                    foreach ( $_POST['tables'] as $table_id ) {
                        $table_to_copy = $this->load_table( $table_id );
                        $new_table = $table_to_copy;
                        $new_table['id'] = $this->get_new_table_id();
                        $new_table['name'] = __( 'Copy of', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . $table_to_copy['name'];
                        unset( $table_to_copy );
                        $this->save_table( $new_table );
                    }
                    $message = _n( 'Table copied successfully.', 'Tables copied successfully.', count( $_POST['tables'] ), WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                case 'delete': // see do_action_delete for explanations
                    foreach ( $_POST['tables'] as $table_id ) {
                        $this->delete_table( $table_id );
                    }
                    $message = _n( 'Table deleted successfully.', 'Tables deleted successfully.', count( $_POST['tables'] ), WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                case 'wp_table_import': // see do_action_import for explanations
                    $this->import_instance = $this->create_class_instance( 'WP_Table_Reloaded_Import', 'import.class.php' );
                    $this->import_instance->import_format = 'wp_table';
                    foreach ( $_POST['tables'] as $table_id ) {
                        $this->import_instance->wp_table_id = $table_id;
                        $this->import_instance->import_table();
                        $imported_table = $this->import_instance->imported_table;
                        $table = array_merge( $this->default_table, $imported_table );
                        
                        $rows = count( $table['data'] );
                        $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
                        $rows = ( 0 < $rows ) ? $rows : 1;
                        $cols = ( 0 < $cols ) ? $cols : 1;
                        $table['visibility']['rows'] = array_fill( 0, $rows, false );
                        $table['visibility']['columns'] = array_fill( 0, $cols, false );
                        
                        $table['id'] = $this->get_new_table_id();
                        $this->save_table( $table );
                    }
                    $message = _n( 'Table imported successfully.', 'Tables imported successfully.', count( $_POST['tables'] ), WP_TABLE_RELOADED_TEXTDOMAIN );
                    break;
                default:
                    break;
                }

            } else {
                $message = __( 'You did not select any tables!', WP_TABLE_RELOADED_TEXTDOMAIN );
            }
            $this->helper->print_header_message( $message );
        }
        $this->do_action_list();
    }

    /**
     * "Copy" action handler
     */
    function do_action_copy() {
        if ( isset( $_GET['table_id'] ) ) {
            check_admin_referer( $this->get_nonce( 'copy' ) );

            $table_to_copy = $this->load_table( $_GET['table_id'] );

            $new_table = $table_to_copy;
            $new_table['id'] = $this->get_new_table_id();
            $new_table['name'] = __( 'Copy of', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . $table_to_copy['name'];
            unset( $table_to_copy );

            $this->save_table( $new_table );

            $this->helper->print_header_message( sprintf( __( 'Table &quot;%s&quot; copied successfully.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $new_table['name'] ) ) );
        }
        $this->do_action_list();
    }

    /**
     * "Delete" action handler, for tables and custom fields
     */
    function do_action_delete() {
        if ( isset( $_GET['table_id'] ) && isset( $_GET['item'] ) ) {
            check_admin_referer( $this->get_nonce( 'delete', $_GET['item'] ) );

            $table_id = $_GET['table_id'];
            $table = $this->load_table( $table_id );

            switch( $_GET['item'] ) {
            case 'table':
                $this->delete_table( $table_id );
                $this->helper->print_header_message( sprintf( __( 'Table &quot;%s&quot; deleted successfully.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table['name'] ) ) );
                $this->do_action_list();
                break;
            case 'custom_field':
                $name = ( isset( $_GET['element_id'] ) ) ? $_GET['element_id'] : '';
                if ( !empty( $name ) && isset( $table['custom_fields'][$name] ) ) {
                    unset( $table['custom_fields'][$name] );
                    $this->save_table( $table );
                    $message = __( 'Custom Data Field deleted successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
                } else {
                    $message = __( 'Custom Data Field could not be deleted.', WP_TABLE_RELOADED_TEXTDOMAIN );
                }
                $this->helper->print_header_message( $message );
                $this->load_view( 'edit', compact( 'table_id' ) );
                break;
            default:
                $this->helper->print_header_message( __( 'Delete failed.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->do_action_list();
            }
        } else {
            $this->do_action_list();
        }
    }

    /**
     * "Import" action handler, for single tables and a Dump File
     */
    function do_action_import() {
        $this->import_instance = $this->create_class_instance( 'WP_Table_Reloaded_Import', 'import.class.php' );
        if ( isset( $_POST['submit'] ) && isset( $_POST['import_from'] ) ) {
            check_admin_referer( $this->get_nonce( 'import' ) );

            $import_error = false;
            switch( $_POST['import_from'] ) {
            case 'file-upload':
                if ( !empty( $_FILES['import_file']['tmp_name'] ) ) {
                    $this->import_instance->tempname = $_FILES['import_file']['tmp_name'];
                    $this->import_instance->filename = $_FILES['import_file']['name'];
                    $this->import_instance->mimetype = $_FILES['import_file']['type'];
                    $this->import_instance->import_from = 'file-upload';
                    $unlink_file = true;
                } else {
                    $import_error = true;
                }
                break;
            case 'server':
                if ( !empty( $_POST['import_server'] ) ) {
                    $this->import_instance->tempname = $_POST['import_server'];
                    $this->import_instance->filename = __( 'Imported Table', WP_TABLE_RELOADED_TEXTDOMAIN );
                    $this->import_instance->mimetype = sprintf( __( 'from %s', WP_TABLE_RELOADED_TEXTDOMAIN ), $_POST['import_server'] );
                    $this->import_instance->import_from = 'server';
                } else {
                    $import_error = true;
                }
                break;
            case 'form-field':
                if ( !empty( $_POST['import_data'] ) ) {
                    $this->import_instance->tempname = '';
                    $this->import_instance->filename = __( 'Imported Table', WP_TABLE_RELOADED_TEXTDOMAIN );
                    $this->import_instance->mimetype = __( 'via form', WP_TABLE_RELOADED_TEXTDOMAIN );
                    $this->import_instance->import_from = 'form-field';
                    $this->import_instance->import_data = stripslashes( $_POST['import_data'] );
                } else {
                    $import_error = true;
                }
                break;
            case 'url':
                if ( !empty( $_POST['import_url'] ) ) {
                    $this->import_instance->tempname = '';
                    $this->import_instance->filename = __( 'Imported Table', WP_TABLE_RELOADED_TEXTDOMAIN );
                    $this->import_instance->mimetype = sprintf( __( 'from %s', WP_TABLE_RELOADED_TEXTDOMAIN ), $_POST['import_url'] );
                    $this->import_instance->import_from = 'url';
                    $url = esc_url( $_POST['import_url'] );
                    $temp_data = wp_remote_fopen( $url );
                    $this->import_instance->import_data = ( false !== $temp_data ) ? $temp_data : '';
                } else {
                    $import_error = true;
                }
                break;
            default:
                // no valid import source
                $import_error = true;
            }

            if ( $import_error ) {
                // no valid data submitted
                $this->helper->print_header_message( __( 'Table could not be imported.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->load_view( 'import' );
                return;
            }
            
            // do import with the config set above
            $this->import_instance->import_format = $_POST['import_format'];
            $this->import_instance->import_table();
            $error = $this->import_instance->error;
            $imported_table = $this->import_instance->imported_table;

            if ( isset( $unlink_file) && $unlink_file )
                $this->import_instance->unlink_uploaded_file();

            if ( isset( $_POST['import_addreplace'] ) && isset( $_POST['import_addreplace_table'] ) && ( 'replace' == $_POST['import_addreplace'] ) && $this->table_exists( $_POST['import_addreplace_table'] ) ) {
                $table = $this->load_table( $_POST['import_addreplace_table'] );
                $table['data'] = $imported_table['data'];
                $success_message = sprintf( __( 'Table %s (%s) replaced successfully.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table['name'] ), $this->helper->safe_output( $table['id'] ) );
            } else {
                $table = array_merge( $this->default_table, $imported_table );
                $table['id'] = $this->get_new_table_id();
                $success_message = _n( 'Table imported successfully.', 'Tables imported successfully.', 1, WP_TABLE_RELOADED_TEXTDOMAIN );
            }

            unset( $imported_table );

            foreach ( $table['data'] as $row_idx => $row )
                $table['visibility']['rows'][$row_idx] = isset( $table['visibility']['rows'][$row_idx] ) ? $table['visibility']['rows'][$row_idx] : false;
            foreach ( $table['data'][0] as $col_idx => $col )
                $table['visibility']['columns'][$col_idx] = isset( $table['visibility']['columns'][$col_idx] ) ? $table['visibility']['columns'][$col_idx] : false;

            if ( !$error ) {
                $this->save_table( $table );
                $this->helper->print_header_message( $success_message );
                $table_id = $table['id'];
                $this->load_view( 'edit', compact( 'table_id' ) );
            } else {
                $this->helper->print_header_message( __( 'Table could not be imported.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->load_view( 'import' );
            }
        } elseif ( isset( $_GET['import_format'] ) && 'wp_table' == $_GET['import_format'] && isset( $_GET['wp_table_id'] ) ) {
            check_admin_referer( $this->get_nonce( 'import' ) );

            $this->import_instance->import_format = 'wp_table';
            $this->import_instance->wp_table_id = $_GET['wp_table_id'];
            $this->import_instance->import_table();
            $imported_table = $this->import_instance->imported_table;

            $table = array_merge( $this->default_table, $imported_table );

            $rows = count( $table['data'] );
            $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
            $table['visibility']['rows'] = array_fill( 0, $rows, false );
            $table['visibility']['columns'] = array_fill( 0, $cols, false );

            $table['id'] = $this->get_new_table_id();

            $this->save_table( $table );

            $this->helper->print_header_message( _n( 'Table imported successfully.', 'Tables imported successfully.', 1, WP_TABLE_RELOADED_TEXTDOMAIN ) );
            $table_id = $table['id'];
            $this->load_view( 'edit', compact( 'table_id' ) );
        } elseif ( isset( $_POST['import_wp_table_reloaded_dump_file'] ) ) {
            check_admin_referer( $this->get_nonce( 'import_dump' ), $this->get_nonce( 'import_dump' ) );
            
            // check if user is admin
            if ( !current_user_can( 'manage_options' ) ) {
                $this->helper->print_header_message( __( 'You do not have sufficient rights to perform this action.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->load_view( 'options' );
                return;
            }
            
            // check if file was uploaded
            if ( empty( $_FILES['dump_file']['tmp_name'] ) ) {
                $this->helper->print_header_message( __( 'You did not upload a WP-Table Reloaded dump file.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->load_view( 'options' );
                return;
            }
            // read data from file and rewrite string to array
            $import_data = file_get_contents( $_FILES['dump_file']['tmp_name'] );
            $import = unserialize( $import_data );
            // check if import dump is not empty
            if ( empty( $import ) ) {
                $this->helper->print_header_message( __( 'The uploaded dump file is empty. Please upload a valid dump file.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->load_view( 'options' );
                return;
            }

            // NEED TO ADD SOME MORE CHECKS HERE, IF IMPORT IS VALID AND COMPLETE!

            // remove all existing data
            foreach ( $this->tables as $id => $tableoptionname )
                delete_option( $tableoptionname );
            delete_option( $this->optionname['tables'] );
            delete_option( $this->optionname['options'] );

            // import and save options
            $this->options = $import['options'];
            $this->update_options();
            // import and save table overview
            $this->tables = $import['table_info'];
            $this->update_tables();
            // import each table
            foreach ( $this->tables as $table_id => $tableoptionname ) {
                $dump_table = $import['tables'][ $table_id ];
                update_option( $tableoptionname, $dump_table );
            }
            // check if plugin update is necessary, compared to imported data
            if ( version_compare( $this->options['installed_version'], WP_TABLE_RELOADED_PLUGIN_VERSION, '<' ) )
                $this->plugin_update();

            $this->helper->print_header_message( __( 'All Tables, Settings and Options were successfully imported.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
            $this->do_action_list();
        } else {
            $this->load_view( 'import' );
        }
    }

    /**
     * "Export" action handler, for single tables
     */
    function do_action_export() {
        $this->export_instance = $this->create_class_instance( 'WP_Table_Reloaded_Export', 'export.class.php' );
        if ( isset( $_POST['submit'] ) && isset( $_POST['table_id'] ) && isset( $_POST['export_format'] ) ) {
            check_admin_referer( $this->get_nonce( 'export' ) );

            $table_to_export = $this->load_table( $_POST['table_id'] );
            
            $this->export_instance->table_to_export = $table_to_export;
            $this->export_instance->export_format = $_POST['export_format'];
            $this->export_instance->delimiter = $_POST['delimiter'];
            $this->export_instance->export_table();
            $exported_table = $this->export_instance->exported_table;

            if ( isset( $_POST['download_export_file'] ) && 'true' == $_POST['download_export_file'] ) {
                $filename = $table_to_export['id'] . '-' . $table_to_export['name'] . '-' . date( 'Y-m-d' ) . '.' . $_POST['export_format'];
                $this->helper->prepare_download( $filename, strlen( $exported_table ), 'text/' . $_POST['export_format'] );
                echo $exported_table;
                exit;
            } else {
                $this->helper->print_header_message( sprintf( __( 'Table &quot;%s&quot; exported successfully.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table_to_export['name'] ) ) );
                $table_id = $_POST['table_id'];
                $output = $exported_table;
                $this->load_view( 'export', compact( 'table_id', 'output' ) );
            }
        } else {
            $table_id = isset( $_REQUEST['table_id'] ) ? $_REQUEST['table_id'] : 0;
            $this->load_view( 'export', compact( 'table_id' ) );
        }
    }
    
    /**
     * "Export" action handler, for Dump Files, stores all plugin data, like tables, options, etc. in a single array,
     * serializes it and offers the resulting string for download in a Dump File
     */
    function do_action_export_all() {
        if ( isset( $_POST['export_all'] ) ) {
            check_admin_referer( $this->get_nonce( 'export_all' ), $this->get_nonce( 'export_all' ) );

            $export = array();
            $export['table_info'] = $this->tables;
            foreach ( $this->tables as $table_id => $tableoptionname ) {
                $dump_table = $this->load_table( $table_id );
                $export['tables'][ $table_id ] = $dump_table;
            }
            $export['options'] = $this->options;
            
            $export_dump = serialize( $export );

            $filename = 'wp-table-reloaded-export-' . date( 'Y-m-d' ) . '.dump';
            $this->helper->prepare_download( $filename, strlen( $export_dump ), 'text/data' );
            echo $export_dump;
            exit;
        }
    }

    /**
     * "Plugin Options" action handler
     */
    function do_action_options() {
        if ( isset( $_POST['submit'] ) && isset( $_POST['options'] ) ) {
            check_admin_referer( $this->get_nonce( 'options' ), $this->get_nonce( 'options' ) );

            // check if user can access Plugin Options
            if ( !$this->user_has_access( 'plugin-options' ) ) {
                $this->helper->print_header_message( __( 'You do not have sufficient rights to access the Plugin Options.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
                $this->load_view( 'options' );
                return;
            }

            $new_options = $_POST['options'];
            
            // checkboxes: option value is defined by whether option isset (e.g. was checked) or not
            $this->options['show_exit_warning'] = isset( $new_options['show_exit_warning'] );
            $this->options['growing_textareas'] = isset( $new_options['growing_textareas'] );
            $this->options['use_datatables_on_table_list'] = isset( $new_options['use_datatables_on_table_list'] );
            $this->options['enable_tablesorter'] = isset( $new_options['enable_tablesorter'] );
            $this->options['use_default_css'] = isset( $new_options['use_default_css'] );
            $this->options['use_custom_css'] = isset( $new_options['use_custom_css'] );
            $this->options['add_target_blank_to_links'] = isset( $new_options['add_target_blank_to_links'] );
            // drop down: only set when not disabled (by JavaScript)
            if ( isset( $new_options['tablesorter_script'] ) )
            	$this->options['tablesorter_script'] = $new_options['tablesorter_script'];

            // only save these settings, if user is administrator, as they are admin options
            if ( current_user_can( 'manage_options' ) ) {
                $this->options['uninstall_upon_deactivation'] = isset( $new_options['uninstall_upon_deactivation'] );
                $this->options['enable_search'] = isset( $new_options['enable_search'] );
                $this->options['frontend_edit_table_link'] = isset( $new_options['frontend_edit_table_link'] );
                // plugin language
                if ( isset( $this->available_plugin_languages[ $new_options['plugin_language'] ] ) )
                    $this->options['plugin_language'] = $new_options['plugin_language'];
                else
                    $this->options['plugin_language'] = 'auto';
                // admin menu parent page
                $admin_menu_parent_page_changed = ( $this->options['admin_menu_parent_page'] != $new_options['admin_menu_parent_page'] );
                if ( in_array( $new_options['admin_menu_parent_page'], $this->possible_admin_menu_parent_pages ) )
                    $this->options['admin_menu_parent_page'] = $new_options['admin_menu_parent_page'];
                else
                    $this->options['admin_menu_parent_page'] = 'tools.php';
                // update $this->page_url, so that next page load will work
                $this->page_url = $this->options['admin_menu_parent_page'] ;
                // user access to plugin
                if ( in_array( $new_options['user_access_plugin'], array( 'admin', 'editor', 'author', 'contributor' ) ) )
                    $this->options['user_access_plugin'] = $new_options['user_access_plugin'];
                else
                    $this->options['user_access_plugin'] = 'admin'; // better set it high, if something is wrong
                // user access to plugin options
                if ( in_array( $new_options['user_access_plugin_options'], array( 'admin', 'editor', 'author' ) ) )
                    $this->options['user_access_plugin_options'] = $new_options['user_access_plugin_options'];
                else
                    $this->options['user_access_plugin_options'] = 'admin'; // better set it high, if something is wrong
            }

            // clean up CSS style input (if user enclosed it into <style...></style>
            if ( isset( $new_options['custom_css'] ) ) {
                    if ( 1 == preg_match( '/<style.*?>(.*?)<\/style>/is', stripslashes( $new_options['custom_css'] ), $matches ) )
                        $new_options['custom_css'] = $matches[1]; // if found, take match as style to save
                    $this->options['custom_css'] = $new_options['custom_css'];
            }

            $this->update_options();

            $message = __( 'Options saved successfully.', WP_TABLE_RELOADED_TEXTDOMAIN );
            if ( $admin_menu_parent_page_changed ) {
                $url = $this->get_action_url( array( 'action' => 'options' ), false );
                $message .= ' ' . sprintf( __(  '<a href="%s">Click here to Proceed.</a>', WP_TABLE_RELOADED_TEXTDOMAIN ), $url );
            }

            $this->helper->print_header_message( $message );
        }
        $this->load_view( 'options' );
    }
    
    /**
     * "Plugin Uninstall" action handler, checks if an admin is performing it, sets uninstall to true and deactivates the plugin
     * (which executes the plugin_deactivation_hook() which then deletes all options from the DB
     */
    function do_action_uninstall() {
        check_admin_referer( $this->get_nonce( 'uninstall' ) );

        if ( !current_user_can( 'manage_options' ) ) {
            $this->helper->print_header_message( __( 'You do not have sufficient rights to perform this action.', WP_TABLE_RELOADED_TEXTDOMAIN ) );
            $this->load_view( 'options' );
            return;
        }

        // everything shall be deleted (manual uninstall)
        $this->options['uninstall_upon_deactivation'] = true;
        $this->update_options();

        $plugin = WP_TABLE_RELOADED_BASENAME;
        deactivate_plugins( $plugin );
        if ( false !== get_option( 'recently_activated', false ) )
            update_option( 'recently_activated', array( $plugin => time() ) + (array)get_option( 'recently_activated' ) );

        $this->load_view( 'uninstall', array(), false );
    }
    
    /**
     * "About" action handler, only calls the view
     */
    function do_action_about() {
        $this->load_view( 'about' );
    }

    /**
     * "AJAX List of Tables" action handler
     */
    function do_action_ajax_list() {
        check_admin_referer( $this->get_nonce( 'ajax_list' ) );

        $this->init_language_support();
        $this->load_view( 'ajax_list', array(), false );

        exit; // necessary to stop page building here!
    }
    
    /**
     * "AJAX Table Preview" action handler
     */
    function do_action_ajax_preview() {
        check_admin_referer( $this->get_nonce( 'ajax_preview' ) );

        $this->init_language_support();

        $table_id = ( isset( $_GET['table_id'] ) && 0 < (int)$_GET['table_id'] ) ? (int)$_GET['table_id'] : 0;

        if ( $this->table_exists( $table_id ) ) {
            $this->load_view( 'ajax_preview', compact( 'table_id' ), false );
        } else {
            ?>
            <div style="clear:both;"><p style="width:97%;"><?php _e( 'There is no table with this ID!', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></p></div>
            <?php
        }

        exit; // necessary to stop page building here!
    }

    /**
     * "Hide welcome message" action handler (which either is a plugin update or a plugin install message)
     */
    function do_action_hide_welcome_message() {
        check_admin_referer( $this->get_nonce( 'hide_welcome_message' ) );
        $this->options['show_welcome_message'] = 0;
        $this->update_options();
        $this->do_action_list();
    }
    
    /**
     * "Hide Donate Message" action handler
     */
     function do_action_hide_donate_nag() {
        check_admin_referer( $this->get_nonce( 'hide_donate_nag' ) );

        $this->options['show_donate_nag'] = false;
        $this->update_options();

        if ( isset( $_GET['user_donated'] ) && $_GET['user_donated'] ) {
            $this->helper->print_header_message( __( 'Thank you very much! Your donation is highly appreciated. You just contributed to the further development of WP-Table Reloaded!', WP_TABLE_RELOADED_TEXTDOMAIN ) );
        } else {
            $this->helper->print_header_message( sprintf( __( 'No problem! I still hope you enjoy the benefits that WP-Table Reloaded brings to you. If you should want to change your mind, you\'ll always find the &quot;%s&quot; button on the <a href="%s">WP-Table Reloaded website</a>.', WP_TABLE_RELOADED_TEXTDOMAIN ), __( 'Donate', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/website/' ) );
        }
        
        $this->do_action_list();
    }
    
    // ###################################################################################################################
    // ##########################################                     ####################################################
    // ##########################################   Page Generation   ####################################################
    // ##########################################                     ####################################################
    // ###################################################################################################################

    /**
     * Load a view from another file, which contains HTML for the $name view to render and output
     *
     * @param string $name Name of the view to load
     * @param array $params (optional) Parameters/PHP variables that shall be available to the view, extracted to single variables at the beginning
     * @param bool $print_submenu_navigation (optional) Whether to print the submenu navigation under the page headline
     */
    function load_view( $name, $params = array(), $print_submenu_navigation = true ) {
        extract( $params );

        $headlines = array(
            'list' => __( 'List of Tables', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' &lsaquo; ' . __( 'WP-Table Reloaded', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'add' => __( 'Add new Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'import' => __( 'Import a Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'export' => __( 'Export a Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'options' => __( 'Plugin Options', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' &lsaquo; ' . __( 'WP-Table Reloaded', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'about' => __( 'About WP-Table Reloaded', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'uninstall' => __( 'WP-Table Reloaded', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'ajax_list' => __( 'List of Tables', WP_TABLE_RELOADED_TEXTDOMAIN )
        );

        // these views also need the complete table, besides the parameters
        if ( in_array( $name, array( 'edit', 'ajax_preview' ) ) ) {
            $table = $this->load_table( $table_id );
            $headlines['edit'] = sprintf( __( 'Edit Table &quot;%s&quot; (ID %s)', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table['name'] ), $this->helper->safe_output( $table['id'] ) );
            $headlines['ajax_preview'] = sprintf( __( 'Preview of Table &quot;%s&quot; (ID %s)', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table['name'] ), $this->helper->safe_output( $table['id'] ) );
        }

        $headline = isset( $headlines[ $name ] ) ? $headlines[ $name ] : '';

        $this->helper->print_page_header( $headline );
        if ( $print_submenu_navigation )
            $this->print_submenu_navigation( $name );

        include ( WP_TABLE_RELOADED_ABSPATH . "views/view-{$name}.php" );

        $this->helper->print_page_footer();
    }

    /**
     * Render and output the submenu navigation with links to the possible actions, highlighting the current one,
     * separated into table actions (List, Add, Import, Export) and plugin actions (Options, About)
     *
     * @param string $the_action Action that is being processed in this page load
     */
    function print_submenu_navigation( $the_action ) {
        $table_actions = array(
            'list' =>  __( 'List Tables', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'add' =>  __( 'Add new Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'import' => __( 'Import a Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'export' => __( 'Export a Table', WP_TABLE_RELOADED_TEXTDOMAIN )
        );
        $table_actions = apply_filters( 'wp_table_reloaded_backend_table_actions', $table_actions );
        $_table_actions = array_keys( $table_actions );
        $last_table_action = array_pop( $_table_actions );

        $plugin_actions = array(
            'options' => __( 'Plugin Options', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'about' => __( 'About the plugin', WP_TABLE_RELOADED_TEXTDOMAIN )
        );
        $plugin_actions = apply_filters( 'wp_table_reloaded_backend_plugin_actions', $plugin_actions );
        $_plugin_actions = array_keys( $plugin_actions );
        $last_plugin_action = array_pop( $_plugin_actions );

        ?>
        <ul class="subsubsub">
            <?php
            foreach ( $table_actions as $action => $name ) {
                $action_url = $this->get_action_url( array( 'action' => $action ), false );
                $class = ( $action == $the_action ) ? 'class="current" ' : '';
                $bar = ( $last_table_action != $action ) ? ' | ' : '';
                echo "<li><a {$class}href=\"{$action_url}\">{$name}</a>{$bar}</li>";
            }
            echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>';
            foreach ( $plugin_actions as $action => $name ) {
                $action_url = $this->get_action_url( array( 'action' => $action ), false );
                $class = ( $action == $the_action ) ? 'class="current" ' : '';
                $bar = ( $last_plugin_action != $action ) ? ' | ' : '';
                echo "<li><a {$class}href=\"{$action_url}\">{$name}</a>{$bar}</li>";
            }
            ?>
        </ul>
        <br class="clear" />
        <?php
    }
    
    /**
     * Decide whether a donate message can be shown on the "List Tables" screen, depending on passed days since installation and whether it was shown before
     *
     * @return bool Whether the donate message can be shown on the "List Tables" screen
     */
    function may_print_donate_nag() {
        if ( !$this->options['show_donate_nag'] )
            return false;

        // how long has the plugin been installed?
        $secs = time() - $this->options['install_time'];
        $days = floor( $secs / (60*60*24) );
        return ( $days >= 30 ) ? true : false;
    }

    // ###################################################################################################################
    // #########################################                      ####################################################
    // #########################################   Options Functions  ####################################################
    // #########################################                      ####################################################
    // ###################################################################################################################

    /**
     * Determine an ID that is not yet used and can be used for a new table, by checking what the last ID was
     *
     * @return int New and unused table ID
     */
    function get_new_table_id() {
        // need to check new ID candidate, because a higher one might be in use, if a table ID was manually changed
        do {
            $this->options['last_id'] = $this->options['last_id'] + 1;
        } while ( $this->table_exists( $this->options['last_id'] ) );
        $this->update_options();
        return $this->options['last_id'];
    }

    /**
     * Save current set of Plugin Options to the DB (as an option in the WP options DB table),
     */
    function update_options() {
        // possibility to overwrite option updating (i.e. to update them in own DB table)
        $options_updated = apply_filters( 'wp_table_reloaded_update_options', false, $this->options );
        if ( $options_updated )
            return;
    
        update_option( $this->optionname['options'], $this->options );
    }

    /**
     * Save current List of Tables to the DB (as an option in the WP options DB table),
     */
    function update_tables() {
        ksort( $this->tables, SORT_NUMERIC ); // sort for table IDs, as one with a small ID might have been appended
        
        // possibility to overwrite tables updating (i.e. to update them in own DB table)
        $tables_updated = apply_filters( 'wp_table_reloaded_update_tables', false, $this->tables );
        if ( $tables_updated )
            return;
            
        update_option( $this->optionname['tables'], $this->tables );
    }

    /**
     * Save the given $table to its option in the DB and also update the List of Tables
     *
     * @param array $table Table to store, including data, options, custom fields, visibility settings, ...
     */
    function save_table( $table ) {
        if ( 0 < $table['id'] ) {
            // update last changes data
            $table['last_modified'] = current_time( 'mysql' );
            $user = wp_get_current_user();
            $table['last_editor_id'] = $user->ID;

            // possibility to overwrite table saving (i.e. to store it in own DB table)
            $table_saved = apply_filters( 'wp_table_reloaded_save_table', false, $table );
            if ( $table_saved )
                return;

            $table = apply_filters( 'wp_table_reloaded_pre_save_table', $table );
            $table = apply_filters( 'wp_table_reloaded_pre_save_table_id-' . $table['id'], $table );

            // delete the transient that caches the table output
            $cache_name = "wp_table_reloaded_table_output_{$table['id']}";
            delete_transient( $cache_name );
            
            $this->tables[ $table['id'] ] = ( isset( $this->tables[ $table['id'] ] ) ) ? $this->tables[ $table['id'] ] : $this->optionname['table'] . '_' . $table['id'];
            update_option( $this->tables[ $table['id'] ], $table );
            $this->update_tables();
        }
    }

    /**
     * Delete the table with the given $table_id from the DB and also remove it from the List of Tables
     *
     * @param int $table_id ID of the table to delete
     */
    function delete_table( $table_id ) {
        // possibility to overwrite table deleting (i.e. to delete it in own DB table)
        $table_deleted = apply_filters( 'wp_table_reloaded_delete_table', false, $table_id );
        if ( !$table_deleted ) {
            $this->tables[ $table_id ] = ( isset( $this->tables[ $table_id ] ) ) ? $this->tables[ $table_id ] : $this->optionname['table'] . '_' . $table_id;
            delete_option( $this->tables[ $table_id ] );
        }
        unset( $this->tables[ $table_id ] );
        $this->update_tables();
    }

    // ###################################################################################################################
    // #########################################                      ####################################################
    // #########################################      URL Support     ####################################################
    // #########################################                      ####################################################
    // ###################################################################################################################
    
    /**
     * Generate the complete nonce string, from the nonce base, the action and an item, e.g. wp-table-reloaded-nonce_delete_table
     *
     * @param string $action Action for which the nonce is needed
     * @param string $item (optional) Item for which the action will be performed, like "table" or "custom_field"
     * @return string The complete nonce string
     */
    function get_nonce( $action, $item = false ) {
        return ( false !== $item ) ? $this->nonce_base . '_' . $action . '_' . $item : $this->nonce_base . '_' . $action;
    }

    /**
     * Generate the action URL, to be used as a link within the plugin (e.g. in the submenu navigation or List of Tables)
     *
     * @param array $params (optional) Parameters to form the Query String of the URL
     * @param bool $add_nonce (optional) Whether the URL shall be nonced by WordPress
     * @return string The action URL
     */
    function get_action_url( $params = array(), $add_nonce = false ) {
        $default_params = array(
                'page' => $this->page_slug,
                'action' => false,
                'item' => false
        );
        $url_params = array_merge( $default_params, $params );

        $action_url = add_query_arg( $url_params, admin_url( $this->page_url ) );
        if ( $add_nonce )
            $action_url = wp_nonce_url( $action_url, $this->get_nonce( $url_params['action'], $url_params['item'] ) );
        $action_url = esc_url( $action_url );
        return $action_url;
    }
    
    // ###################################################################################################################
    // #######################################                         ###################################################
    // #######################################    Plugin Management    ###################################################
    // #######################################                         ###################################################
    // ###################################################################################################################

    /**
     * Load plugin options and list of tables from DB options, if not available: install plugin first
     */
    function init_plugin() {
		$this->options = $this->load_options();
		$this->tables = $this->load_tables();
        if ( false === $this->options || false === $this->tables )
            $this->plugin_install();
    }

    /**
     * Commands to be executed, when plugin is activated by WordPress, performs check, if plugin is freshly installed or updated
     */
    function plugin_activation_hook() {
        $this->options = $this->load_options();
        if ( false !== $this->options && isset( $this->options['installed_version'] ) ) {
            // check if update needed, or just re-activated the latest version of it
            if ( version_compare( $this->options['installed_version'], WP_TABLE_RELOADED_PLUGIN_VERSION, '<' ) ) {
                $this->plugin_update();
            } else {
                // just reactivating, but latest version of plugin installed
            }
        } else {
            // plugin has never been installed before
            $this->plugin_install();
        }
    }

    /**
     * Commands to be executed, when plugin is deactivated by WordPress, removes all options and tables, if corresponding admin option is set
     */
    function plugin_deactivation_hook() {
        $this->options = $this->load_options();
   		$this->tables = $this->load_tables();
        if ( false !== $this->options && isset( $this->options['uninstall_upon_deactivation'] ) && $this->options['uninstall_upon_deactivation'] ) {
            // delete all options and tables
            foreach ( $this->tables as $id => $tableoptionname )
                delete_option( $tableoptionname );
            delete_option( $this->optionname['tables'] );
            delete_option( $this->optionname['options'] );
        }
    }

    /**
     * Install the plugin by setting up the options and tables
     */
    function plugin_install() {
        $this->options = $this->default_options;
        $this->options['installed_version'] = WP_TABLE_RELOADED_PLUGIN_VERSION;
        $this->options['install_time'] = time();
        $this->options['custom_css'] = ''; // we could add initial CSS here, for demonstration
        $this->options['show_welcome_message'] = 1;  // 1 = install message
        $this->update_options();
        $this->tables = $this->default_tables;
        $this->update_tables();
    }

    /**
     * Update the plugin, add new values to the plugin and table options and remove deprecated ones
     */
    function plugin_update() {
        // update general plugin options
        // 1. step: by adding/overwriting existing options
		$this->options = $this->load_options();

        // do nothing, if installed version is up-to-date
        if ( ! version_compare( $this->options['installed_version'], WP_TABLE_RELOADED_PLUGIN_VERSION, '<' ) )
            return;

		$new_options = array();

        // 1b. step: update new default options before possibly adding them
        $this->default_options['install_time'] = time();

        // 2a. step: add/delete new/deprecated options by overwriting new ones with existing ones, if there are any
		foreach ( $this->default_options as $key => $value )
            $new_options[ $key ] = ( isset( $this->options[ $key ] ) ) ? $this->options[ $key ] : $this->default_options[ $key ] ;

        // 2b., take care of CSS
        $new_options['use_custom_css'] = ( !isset( $this->options['use_custom_css'] ) && isset( $this->options['use_global_css'] ) ) ? $this->options['use_global_css'] : $this->options['use_custom_css'];

        // 2c., take care of Tablesorter script, comparison to 1.4.9 equaly means smaller than anything like 1.5
        if ( version_compare( $this->options['installed_version'] , '1.4.9', '<' ) )
            $new_options['tablesorter_script'] = ( isset( $this->options['use_tablesorter_extended'] ) && $this->options['use_tablesorter_extended'] ) ? 'tablesorter_extended' : 'tablesorter';

        // 2d., 'edit-pages.php' was renamed to 'edit.php?post_type=page' in WP 3.0
        if ( 'edit-pages.php' == $this->options['admin_menu_parent_page'] )
            $new_options['admin_menu_parent_page'] = 'edit.php?post_type=page';

        // 2e., 'top-level' was renamed to 'admin.php' (internally)
        if ( 'top-level' == $this->options['admin_menu_parent_page'] )
            $new_options['admin_menu_parent_page'] = 'admin.php';

        // 3. step: update installed version number, empty update message cache, set welcome message
        $new_options['installed_version'] = WP_TABLE_RELOADED_PLUGIN_VERSION;
        $new_options['update_message'] = array();
        $new_options['show_welcome_message'] = 2; // 2 = update message
        
        // 4. step: save the new options
        $this->options = $new_options;
        $this->update_options();

        // update individual tables and their options
		$this->tables = $this->load_tables();
        foreach ( $this->tables as $id => $tableoptionname ) {
            $table = $this->load_table( $id );
            
            $temp_table = $this->default_table;
            
            // if table doesn't have visibility information, add them
            $rows = count( $table['data'] );
            $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
            $temp_table['visibility']['rows'] = array_fill( 0, $rows, false );
            $temp_table['visibility']['columns'] = array_fill( 0, $cols, false );
            
            foreach ( $temp_table as $key => $value )
                $new_table[ $key ] = ( isset( $table[ $key ] ) ) ? $table[ $key ] : $temp_table[ $key ] ;

            foreach ( $temp_table['options'] as $key => $value )
                $new_table['options'][ $key ] = ( isset( $table['options'][ $key ] ) ) ? $table['options'][ $key ] : $temp_table['options'][ $key ] ;

            $this->save_table( $new_table );
        }
    }

    /**
     * Find out whether the current user has enough rights to access $screen, currently only used for Plugin Options ($screen = 'plugin-options')
     *
     * @param string $screen Screen/View that shall be checked for access, currently only used in the filter
     * @return bool Whether the user has access to $screen
     */
    function user_has_access( $screen ) {
        // capabilities from http://codex.wordpress.org/Roles_and_Capabilities
        $user_group = $this->options['user_access_plugin_options'];
        $capabilities = array(
            'admin' => 'manage_options',
            'editor' => 'publish_pages',
            'author' => 'publish_posts'
        );
        $needed_cap = isset( $capabilities[ $user_group ] ) ? $capabilities[ $user_group ] : 'manage_options';
        $has_access = current_user_can( $needed_cap );
        $has_access = apply_filters( 'wp_table_reloaded_user_access_' . $screen, $has_access, $this->options['user_access_plugin_options'] );
        return $has_access;
    }
    
    /**
     * Get the plugin update message from the remote server, if there is an update available
     *
     * @param array $current Information about the currently installed version (provided by WP)
     * @param object $new Information about the available plugin version (provided by WP)
     * @return string Plugin Update Message
     */
    function get_plugin_update_message( $current, $new ) {
        if ( empty( $this->options['update_message'][ $new->new_version ] ) ) {
            $message = $this->helper->retrieve_plugin_update_message( $current['Version'], $new->new_version );
            $this->options['update_message'][ $new->new_version ] = $message;
            $this->update_options();
        }
        return $this->options['update_message'][ $new->new_version ];
    }

    /**
     * Print the plugin update message in the Plugins list (right in the row), if there's an update available,
     * wrapper for get_plugin_update_message()
     *
     * @param array $current Information about the currently installed version (provided by WP)
     * @param object $new Information about the available plugin version (provided by WP)
     */
    function add_plugin_update_message( $current, $new ) {
        $message = $this->get_plugin_update_message( $current, $new );
        if ( !empty( $message ) )
            echo '<br />' . $this->helper->safe_output( $message );
    }

    /**
     * Add more links to plugin's entry on Plugins page
     *
     * @param array $links List of links to print on the Plugins page
     * @param string $file Name of the plugin
     * @return array Extended list of links to print on the Plugins page
     */
	function add_plugin_row_meta( $links, $file ) {
		if ( WP_TABLE_RELOADED_BASENAME != $file )
            return $links;
				
		$links[] = '<a href="' . $this->get_action_url() . '" title="' . __( 'WP-Table Reloaded Plugin Page', WP_TABLE_RELOADED_TEXTDOMAIN ) . '">' . __( 'Plugin Page', WP_TABLE_RELOADED_TEXTDOMAIN ) . '</a>';
		$links[] = '<a href="http://tobias.baethge.com/go/wp-table-reloaded/faq/" title="' . __( 'Frequently Asked Questions', WP_TABLE_RELOADED_TEXTDOMAIN ) . '">' . __( 'FAQ', WP_TABLE_RELOADED_TEXTDOMAIN ) . '</a>';
		$links[] = '<a href="http://tobias.baethge.com/go/wp-table-reloaded/support/" title="' . __( 'Support', WP_TABLE_RELOADED_TEXTDOMAIN ) . '">' . __( 'Support', WP_TABLE_RELOADED_TEXTDOMAIN ) . '</a>';
		$links[] = '<a href="http://tobias.baethge.com/go/wp-table-reloaded/documentation/" title="' . __( 'Plugin Documentation', WP_TABLE_RELOADED_TEXTDOMAIN ) . '">' . __( 'Documentation', WP_TABLE_RELOADED_TEXTDOMAIN ) . '</a>';
		$links[] = '<a href="http://tobias.baethge.com/go/wp-table-reloaded/donate/" title="' . __( 'Support WP-Table Reloaded with your donation!', WP_TABLE_RELOADED_TEXTDOMAIN ) . '"><strong>' . __( 'Donate', WP_TABLE_RELOADED_TEXTDOMAIN ) . '</strong></a>';

		return $links;
	}

    /**
     * Initialize i18n support, load plugin's textdomain, to retrieve correct translations
     */
    function init_language_support() {
    	add_filter( 'locale', array( &$this, 'get_plugin_locale' ) ); // allow changing the plugin language
        $language_directory = basename( dirname( WP_TABLE_RELOADED__FILE__ ) ) . '/languages';
        load_plugin_textdomain( WP_TABLE_RELOADED_TEXTDOMAIN, false, $language_directory );
        remove_filter( 'locale', array( &$this, 'get_plugin_locale' ) );
    }
    
    /**
     * Retrieve the locale the plugin shall be shown in, applied as a filter in get_locale()
     */
    function get_plugin_locale( $locale ) {
        if ( isset( $_POST['options']['plugin_language'] ) ) {
            if ( 'auto' != $_POST['options']['plugin_language'] )
                return $_POST['options']['plugin_language'];
            else
                return $locale;
        }
        
        $locale = ( !empty( $this->options['plugin_language'] ) && 'auto' != $this->options['plugin_language'] ) ? $this->options['plugin_language'] : $locale;
        return $locale;
    }

    /**
     * Enqueue plugin's JavaScript functions for the backend, by registering, translating (text strings and options) and printing the JS file
     */
    function add_manage_page_js() {
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.dev' : '';
        $jsfile = "admin/admin-script{$suffix}.js";
        $js_url = plugins_url( $jsfile, WP_TABLE_RELOADED__FILE__ );
        wp_enqueue_script( 'wp-table-reloaded-admin-js', $js_url, array( 'jquery', 'thickbox' ), $this->options['installed_version'], true );
        wp_localize_script( 'wp-table-reloaded-admin-js', 'WP_Table_Reloaded_Admin', array(
	  	    'str_UninstallCheckboxActivation' => __( 'Do you really want to activate this? You should only do that right before uninstallation!', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DataManipulationLinkInsertURL' => __( 'URL of link to insert', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DataManipulationLinkInsertText' => __( 'Text of link', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DataManipulationLinkInsertExplain' => __( 'To insert the following HTML code for a link into a cell, just click the cell after closing this dialog.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DataManipulationImageInsertThickbox' => __( 'To insert an image, click &quot;OK&quot; and then click into the cell into which you want to insert the image.', WP_TABLE_RELOADED_TEXTDOMAIN ) . "\n" . __( 'The Media Library will open, from which you can select the desired image or insert the image URL.', WP_TABLE_RELOADED_TEXTDOMAIN ) . "\n" . sprintf( __( 'Click the &quot;%s&quot; button to insert the image.', WP_TABLE_RELOADED_TEXTDOMAIN ), esc_js( __( 'Insert into Post', 'default' ) ) ),
	  	    'str_DataManipulationAddColspan' => __( 'To combine cells within a row, click into the cell to the right of the cell that has the content the combined cells shall have.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DataManipulationAddRowspan' => __( 'To combine cells within a column, click into the cell below the cell that has the content the combined cells shall have.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_BulkCopyTablesLink' => __( 'Do you want to copy the selected tables?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_BulkDeleteTablesLink' => __( 'The selected tables and all content will be erased. Do you really want to delete them?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_BulkImportwpTableTablesLink' => __( 'Do you really want to import the selected tables from the wp-Table plugin?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_CopyTableLink' => __( 'Do you want to copy this table?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DeleteTableLink' => __( 'The complete table and all content will be erased. Do you really want to delete it?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DeleteRowsConfirm' => __( 'Do you really want to delete the selected rows?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DeleteColsConfirm' => __( 'Do you really want to delete the selected columns?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DeleteRowsFailedNoSelection' => __( 'You have not selected any rows.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DeleteColsFailedNoSelection' => __( 'You have not selected any columns.', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'str_DeleteRowsFailedNotAll' => __( 'You can not delete all rows of the table at once!', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_DeleteColsFailedNotAll' => __( 'You can not delete all columns of the table at once!', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_UnHideRowsNoSelection' => __( 'You have not selected any rows.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_UnHideColsNoSelection' => __( 'You have not selected any columns.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_InsertRowsNoSelection' => __( 'You have not selected any rows.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_InsertColsNoSelection' => __( 'You have not selected any columns.', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_ImportwpTableLink' => __( 'Do you really want to import this table from the wp-Table plugin?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_UninstallPluginLink_1' => __( 'Do you really want to uninstall the plugin and delete ALL data?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_UninstallPluginLink_2' => __( 'Are you really sure?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_ChangeTableID' => __( 'Do you really want to change the ID of the table?', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_CFShortcodeMessage' => __( 'To show this Custom Data Field, use this shortcode:', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_TableShortcodeMessage' => __( 'To show this table, use this shortcode:', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'str_ImportDumpFile' => __( 'Warning: You will lose all current Tables and Settings! You should create a backup first. Be warned!', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'str_saveAlert' => __( 'You have made changes to the content of this table and not yet saved them.', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . sprintf( __( 'You should first click &quot;%s&quot; or they will be lost if you navigate away from this page.', WP_TABLE_RELOADED_TEXTDOMAIN ), __( 'Update Changes', WP_TABLE_RELOADED_TEXTDOMAIN ) ),
            'option_show_exit_warning' => $this->options['show_exit_warning'],
            'option_growing_textareas' => $this->options['growing_textareas'],
            'option_add_target_blank_to_links' => $this->options['add_target_blank_to_links'],
            'option_tablesorter_enabled' => $this->options['enable_tablesorter'],
            'option_datatables_active' => $this->options['enable_tablesorter'] && ( 'datatables' == $this->options['tablesorter_script'] || 'datatables-tabletools' == $this->options['tablesorter_script'] ),
            'option_tabletools_active' => $this->options['enable_tablesorter'] && ( 'datatables-tabletools' == $this->options['tablesorter_script'] ),
            'l10n_print_after' => 'try{convertEntities(WP_Table_Reloaded_Admin);}catch(e){};'
        ) );
    }

    /**
     * Enqueue plugin's CSS Stylesheet for the backend
     */
    function add_manage_page_css() {
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.dev' : '';
        $cssfile = "admin/admin-style{$suffix}.css";
        wp_enqueue_style( 'wp-table-reloaded-admin-css', plugins_url( $cssfile, WP_TABLE_RELOADED__FILE__ ), array(), $this->options['installed_version'] );

        // RTL languages support
        if ( is_rtl() ) {
            $cssfile = "admin/admin-style.rtl.css";
            wp_enqueue_style( 'wp-table-reloaded-admin-rtl-css', plugins_url( $cssfile, WP_TABLE_RELOADED__FILE__ ), array(), $this->options['installed_version'] );
        }
    }

    /**
     * Add button "Table" to HTML editor on "Edit Post" and "Edit Pages" pages of WordPress, but only if there is at least one table available
     */
    function add_editor_button() {
        if ( 0 == count( $this->tables ) )
            return;
            
        $this->init_language_support();
        add_thickbox(); // we need thickbox to show the list

        $params = array(
            'page' => $this->page_slug,
            'action' => 'ajax_list'
        );
        $ajax_url = add_query_arg( $params, admin_url( $this->page_url ) );
        $ajax_url = wp_nonce_url( $ajax_url, $this->get_nonce( $params['action'], false ) );
        $ajax_url = esc_url( $ajax_url );
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.dev' : '';
        $jsfile = "admin/admin-editor-buttons-script{$suffix}.js";

        // HTML editor integration
        wp_register_script( 'wp-table-reloaded-editor-button-js', plugins_url( $jsfile, WP_TABLE_RELOADED__FILE__ ), array( 'jquery', 'thickbox', 'media-upload', 'quicktags' ), $this->options['installed_version'], true );
        wp_localize_script( 'wp-table-reloaded-editor-button-js', 'WP_Table_Reloaded_Editor_Button', array(
	  	    'str_EditorButtonCaption' => __( 'Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_EditorButtonTitle' => __( 'Insert a Table', WP_TABLE_RELOADED_TEXTDOMAIN ),
	  	    'str_EditorButtonAjaxURL' => $ajax_url,
            'l10n_print_after' => 'try{convertEntities(WP_Table_Reloaded_Editor_Button);}catch(e){};'
        ) );

        // TinyMCE integration
        if ( user_can_richedit() ) {
        	add_filter( 'mce_external_plugins', array( &$this, 'add_tinymce_plugin' ) );
        	add_filter( 'mce_buttons', array( &$this, 'add_tinymce_button' ) );
        }
        
        add_action( 'admin_print_footer_scripts', array( &$this, '_print_editor_button' ), 100 );
    }

    function _print_editor_button() {
        wp_print_scripts( 'wp-table-reloaded-editor-button-js' );
    }

    /**
     * Add "Table" button and separator to the TinyMCE toolbar
     *
     * @param array $buttons Current set of buttons in the TinyMCE toolbar
     * @return array Current set of buttons in the TinyMCE toolbar, including "Table" button
     */
    function add_tinymce_button( $buttons ) {
    	$buttons[] = '|';
        $buttons[] = 'table';
    	return $buttons;
    }

    /**
     * Register "Table" button plugin to TinyMCE
     *
     * @param array $plugins Current set of registered TinyMCE plugins
     * @return array Current set of registered TinyMCE plugins, including "Table" button plugin
     */
    function add_tinymce_plugin( $plugins ) {
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.dev' : '';
        $jsfile = "admin/admin-tinymce-buttons-script{$suffix}.js";
    	$plugins['table'] = plugins_url( $jsfile, WP_TABLE_RELOADED__FILE__ );
    	return $plugins;
    }

    /**
     * Load DataTables library and print JavaScript commands for sorting and AJAX functions on the "List Tables" screen
     */
    function output_tablesorter_js() {
        $datatables = '';
        // filter to false, to prevent using DataTables in the List of Tables (seems to cause problems with IE 7)
        $use_datatables = $this->options['use_datatables_on_table_list'];
        $use_datatables = apply_filters( 'wp_table_reloaded_admin_use_datatables', $use_datatables );
        // sorting doesn't make sense, if there is only one table in the list
        if ( $use_datatables && 1 < count( $this->tables ) ) {
            $datatables_url = plugins_url( 'js/jquery.datatables.min.js', WP_TABLE_RELOADED__FILE__ );
            wp_register_script( 'wp-table-reloaded-datatables-js', $datatables_url, array( 'wp-table-reloaded-admin-js' ), $this->options['installed_version'] );
            wp_print_scripts( 'wp-table-reloaded-datatables-js' );

            $sProcessing = __( 'Please wait...', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sLengthMenu = __( 'Show _MENU_ Tables', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sLengthMenu_All = __( 'All', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sZeroRecords = __( 'No tables were found.', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sInfo = __( '_START_ to _END_ of _TOTAL_ Tables', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sInfoFiltered = __( '(filtered from _MAX_ Tables)', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sSearch = __( 'Filter:', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sFirst = __( 'First', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sPrevious = __( 'Back', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sNext = __( 'Next', WP_TABLE_RELOADED_TEXTDOMAIN );
            $sLast = __( 'Last', WP_TABLE_RELOADED_TEXTDOMAIN );

            $pagination = '';
            if ( 11 > count( $this->tables ) )
                $pagination = '"bPaginate": false, "bLengthChange": false,';

            $datatables = <<<DATATABLES
\nvar tablelist = $('#wp-table-reloaded-list').dataTable({
    "bSortClasses": false,
    {$pagination}
    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{$sLengthMenu_All}"]],
    "aaSorting": [],
    "bProcessing": true,
    "sPaginationType": "full_numbers",
    "asStripClasses": ['alternate',''],
    "aoColumns": [
        { "sWidth": "24px", "bSortable": false, "bSearchable": false },
        { "sType": "numeric" },
        { "bVisible": false, "bSearchable": true, "sType": "string" },
        { "bSearchable": false, "iDataSort": 2 },
        { "sType": "string" },
        { "bSortable": false }
	],
    "oLanguage": {
	   "sProcessing": "{$sProcessing}",
	   "sLengthMenu": "{$sLengthMenu}",
	   "sZeroRecords": "{$sZeroRecords}",
	   "sInfo": "{$sInfo}",
	   "sInfoFiltered": "{$sInfoFiltered}",
	   "sSearch": "{$sSearch}",
	   "oPaginate": {
            "sFirst": "{$sFirst}",
            "sPrevious": "{$sPrevious}",
            "sNext": "{$sNext}",
            "sLast": "{$sLast}"
        }
    }
})
.find('.sorting').append('&nbsp;<span>&nbsp;&nbsp;&nbsp;</span>');\n
DATATABLES;
        }
		$datatables = apply_filters( 'wp_table_reloaded_admin_datatables_js', $datatables );
        echo <<<JSSCRIPT
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
{$datatables}
});
/* ]]> */
</script>
JSSCRIPT;
    }

} // class WP_Table_Reloaded_Controller_Admin

?>