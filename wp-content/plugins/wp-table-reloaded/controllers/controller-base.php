<?php
/**
 * Base Controller for WP-Table Reloaded with functions needed both in backend and frontend
 *
 * @package WP-Table Reloaded
 * @subpackage Base Controller
 * @author Tobias B&auml;thge
 * @since 1.6
 */

/**
 * Base Controller class
 */
class WP_Table_Reloaded_Controller_Base {

    /**
     * Plugin Options, key is each option's name, value is bool, string or int value
     * @var array
     */
    var $options = array();

    /**
     * List of all tables, key is table ID, value is name of WP option
     * @var array
     */
    var $tables = array();

    /**
     * Names/prefixes for the options which are stored in the WP database
     * @var array
     */
    var $optionname = array(
        'tables' => 'wp_table_reloaded_tables',
        'options' => 'wp_table_reloaded_options',
        'table' => 'wp_table_reloaded_data'
    );

    /**
     * Default structure of a new table (one cell, default table options)
     * @var array
     */
    var $default_table = array(
        'id' => 0,
        'data' => array( 0 => array( 0 => '' ) ),
        'name' => '',
        'description' => '',
        'last_modified' => '0000-00-00 00:00:00',
        'last_editor_id' => '',
        'visibility' => array(
            'rows' => array(),
            'columns' => array()
        ),
        'options' => array(
            'alternating_row_colors' => true,
            'row_hover' => false,
            'first_row_th' => true,
            'table_footer' => false,
            'print_name' => false,
            'print_name_position' => 'above',
            'print_description' => false,
            'print_description_position' => 'below',
            'custom_css_class' => '',
            'cache_table_output' => true,
            'use_tablesorter' => true,
            'datatables_sort' => true,
            'datatables_paginate' => true,
            'datatables_paginate_entries' => 10,
            'datatables_lengthchange' => true,
            'datatables_filter' => true,
            'datatables_info' => true,
            'datatables_tabletools' => false,
            'datatables_customcommands' => ''
        ),
        'custom_fields' => array()
    );

    /**
     * Slug that will be appended to the URL of the plugin by WordPress, e.g. http://example.com/wp-admin/tools.php?page=wp-table-reloaded
     * @var string
     */
    var $page_slug = 'wp-table-reloaded';

    /**
     * List of allowed places for the menu item of WP-Table Reloaded in the WP admin menu
     * @var array
     */
    var $possible_admin_menu_parent_pages = array( 'tools.php', 'admin.php', 'edit.php', 'edit.php?post_type=page', 'edit-pages.php', 'plugins.php', 'index.php', 'options-general.php' );

    /**
     * PHP4 class constructor, calls the PHP5 class constructor __construct()
     */
    function WP_Table_Reloaded_Controller_Base() {
        $this->__construct();
    }

    /**
     * PHP5 class constructor
     */
    function __construct() {
        // intentionally left blank
    }

    /**
     * Check, if there is a table for the given table ID
     *
     * @param int $table_id ID of the table that is checked
     * @return bool True if table exists, False if not
     */
    function table_exists( $table_id ) {
        return isset( $this->tables[ $table_id ] );
    }

    /**
     * Load the table with the given ID from the WP options table
     *
     * @param int $table_id ID of the table to load
     * @return array Table as stored in DB, or overwritten by plugin filter
     */
    function load_table( $table_id ) {
        // possibility to overwrite table loading (i.e. to get it from own DB table)
        $table_loaded = apply_filters( 'wp_table_reloaded_load_table', false, $table_id );
        if ( $table_loaded )
            return $table_loaded;

        $table_option = ( $this->table_exists( $table_id ) ) ? $this->tables[ $table_id ] : $this->optionname['table'] . '_' . $table_id;
        $table = get_option( $table_option, $this->default_table);

        $table = apply_filters( 'wp_table_reloaded_post_load_table', $table, $table_id );
        $table = apply_filters( 'wp_table_reloaded_post_load_table_id-' . $table_id, $table );
        return $table;
    }

    /**
     * Load the list of tables from the WP options table
     *
     * @return array|bool List of tables as stored in DB|False if no option in DB exists
     */
    function load_tables() {
        // possibility to overwrite tables loading (i.e. to get list from own DB table)
        $tables_loaded = apply_filters( 'wp_table_reloaded_load_tables_list', false );
        if ( $tables_loaded )
            return $tables_loaded;

        return get_option( $this->optionname['tables'], false );
    }

    /**
     * Load the plugin options from the WP options table
     *
     * @return array|bool Plugin options as stored in DB|False if no option in DB exists
     */
    function load_options() {
        // possibility to overwrite options loading (i.e. to get list from own DB table)
        $options_loaded = apply_filters( 'wp_table_reloaded_load_options', false );
        if ( $options_loaded )
            return $options_loaded;
        return get_option( $this->optionname['options'], false );
    }

    /**
     * Create a new instance of the $class, which is stored in the $file in $folder
     * of the WP-Table Reloaded plugin directory
     *
     * @param string $class Name of the class
     * @param string $file Name of the PHP file with the class
     * @param string $folder (optional) Name of the folder with the class's file
     * @return object Instance of the class
     */
    function create_class_instance( $class, $file, $folder = 'classes' ) {
        if ( !class_exists( $class ) )
            include_once ( WP_TABLE_RELOADED_ABSPATH . $folder . '/' . $file );
        return new $class;
    }

    /**
     * Get a nice looking date and time string from the mySQL format of datetime strings for output
     *
     * @param string $datetime DateTime string in mySQL format
     * @return string Nice looking string with the date and time
     */
    function format_datetime( $datetime ) {
        return mysql2date( get_option('date_format'), $datetime ) . ' ' . mysql2date( get_option('time_format'), $datetime );
    }

    /**
     * Get the name from a WP user ID (used to store information on last editor of a table)
     *
     * @param int $user_id WP user ID
     * @return string Nickname of the WP user with the $user_id
     */
    function get_last_editor( $user_id ) {
        $user = get_userdata( $user_id );
        $nickname = ( isset( $user->nickname ) ) ? $user->nickname : '';
        return $nickname;
    }

} // class WP_Table_Reloaded_Controller_Base

?>