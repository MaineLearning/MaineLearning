<?php
/**
 * Frontend Controller for WP-Table Reloaded with functions for the frontend
 *
 * @package WP-Table Reloaded
 * @subpackage Frontend Controller
 * @author Tobias B&auml;thge
 * @since 1.6
 */

/**
 * Include file with the Base Controller Class
 */
require_once ( WP_TABLE_RELOADED_ABSPATH . 'controllers/controller-base.php' );

/**
 * Frontend Controller class, extends Base Controller Class
 */
class WP_Table_Reloaded_Controller_Frontend extends WP_Table_Reloaded_Controller_Base {

    /**
     * Shortcode to insert a table into a post, page, or text-widget, used like [table id=<ID> /]
     * @var string
     */
    var $shortcode_table = 'table';

    /**
     * Shortcode to insert table meta data into a post, page, or text-widget, used like [table-info id=<ID> field=<Name> /]
     * @var string
     */
    var $shortcode_table_info = 'table-info';

    /**
     * List of all tables that are actually shown on a page, a new entry is made each time, a Shortcode or template tag
     * function is executed, used to determine unique HTML IDs for each table
     * @var array
     */
    var $shown_tables = array();

    /**
     * List of all tables that have a JavaScript library call attached, a new entry is made, if a table has usage of a JS
     * library enabled when its Shortcode/template tag function is executed, used to generate JS calls in the page footer
     * @var array
     */
    var $tablesorter_tables = array();

    /**
     * PHP4 class constructor, calls the PHP5 class constructor __construct()
     */
    function WP_Table_Reloaded_Controller_Frontend() {
        $this->__construct();
    }

    /**
     * PHP5 class constructor
     *
     * Initiate frontend functionality, by loading plugin options and tables, registering Shortcodes,
     * possibly enabling WP Search, and loading plugin's CSS and JS files in header or footer
     */
    function __construct() {
        // load options and tables from WP database; if not available: abort without further action
        $this->options = $this->load_options();
        $this->tables = $this->load_tables();
        if ( false === $this->options || false === $this->tables )
            return;

        // make Shortcode names filterable
        $this->shortcode_table_info = apply_filters( 'wp_table_reloaded_shortcode_table_info', $this->shortcode_table_info );
        $this->shortcode_table = apply_filters( 'wp_table_reloaded_shortcode_table', $this->shortcode_table );

        // shortcode "table-info" needs to be declared before "table"! Otherwise it will not be recognized!
        add_shortcode( $this->shortcode_table_info, array( &$this, 'handle_content_shortcode_table_info' ) );
        add_shortcode( $this->shortcode_table, array( &$this, 'handle_content_shortcode_table' ) );

        add_filter( 'widget_text', array( &$this, 'handle_widget_text_filter' ) );

        // extend WordPress Search to also find posts/pages that have a table with the one of the search terms in them
        if ( $this->options['enable_search'] )
            add_filter( 'posts_search', array( &$this, 'handle_posts_search_filter' ) );

        // if a JavaScript library is (globally) enabled, include respective files
        if ( $this->options['enable_tablesorter'] ) {
            wp_enqueue_script( 'jquery' ); // jQuery needed in any case (it's too late to do this, when Shortcode is executed)
            $priority = apply_filters( 'wp_table_reloaded_frontend_js_priority', 10 );
            add_action( 'wp_footer', array( &$this, 'add_frontend_js' ), $priority );
        }

        // if default CSS or Custom CSS shall be included, include respective files in the header
        if ( $this->options['use_default_css'] || $this->options['use_custom_css'] )
            add_action( 'wp_head', array( &$this, 'add_frontend_css' ) );
    }

    /**
     * Handle Shortcode [table-info id=<ID> field=<name> /] in the_content()
     *
     * @param array $atts list of attributes that where included in the Shortcode
     * @return string Text that replaces the Shortcode (error message or asked-for information)
     */
    function handle_content_shortcode_table_info( $atts ) {
        // parse Shortcode attributes, only allow those that are specified
        $default_atts = array(
                'id' => 0,
                'field' => '',
                'format' => ''
        );
        $default_atts = apply_filters( 'wp_table_reloaded_shortcode_table_info_default_atts', $default_atts );
        $atts = shortcode_atts( $default_atts, $atts );

        // allow a filter to determine behavior of this function, by overwriting its behavior, just need to return something other than false
        $overwrite = apply_filters( 'wp_table_reloaded_shortcode_table_info_overwrite', false, $atts );
        if ( $overwrite )
            return $overwrite;

        // check, if a table with the given ID exists
        $table_id = $atts['id'];
        if ( !is_numeric( $table_id ) || 1 > $table_id || !$this->table_exists( $table_id ) ) {
            $message = "[table \"{$table_id}\" not found /]<br />\n";
            $message = apply_filters( 'wp_table_reloaded_table_not_found_message', $message, $table_id );
            return $message;
        }

        $field = $atts['field'];
        $format = $atts['format'];
        
        $table = $this->load_table( $table_id );

        // generate output, depending on what information (field) was asked for
        switch ( $field ) {
            case 'name':
            case 'description':
                $output = $table[ $field ];
                break;
            case 'last_modified':
                $output = ( 'raw' == $format ) ?  $table['last_modified'] : $this->format_datetime( $table['last_modified'] );
                break;
            case 'last_editor':
                $output = $this->get_last_editor( $table['last_editor_id'] );
                break;
            default:
                if ( isset( $table['custom_fields'][ $field ] ) ) {
                    $output = $table['custom_fields'][ $field ];
                } else {
                    $output = "[table-info field &quot;{$field}&quot; not found in table {$table_id} /]<br />\n";
                    $output = apply_filters( 'wp_table_reloaded_table_info_not_found_message', $output, $table_id, $field );
                }
        }

		$output = apply_filters( 'wp_table_reloaded_shortcode_table_info_output', $output, $atts );

        return $output;
    }

    /**
     * Handle Shortcode [table id=<ID> /] in the_content()
     *
     * @param array $atts list of attributes that where included in the Shortcode
     * @return string HTML code for the table with the ID <ID>
     */
    function handle_content_shortcode_table( $atts ) {
        // parse Shortcode attributs, only allow those that are specified
        $default_atts = array(
            'id' => 0,
            'column_widths' => '',
            'alternating_row_colors' => -1,
            'row_hover' => -1,
            'first_row_th' => -1,
            'first_column_th' => false,
            'table_footer' => -1,
            'print_name' => -1,
            'print_name_position' => -1,
            'print_description' => -1,
            'print_description_position' => -1,
            'cache_table_output' => -1,
            'custom_css_class' => -1,
            'use_tablesorter' => -1,
            'datatables_sort' => -1,
            'datatables_paginate' => -1,
            'datatables_paginate_entries' => -1,
            'datatables_lengthchange' => -1,
            'datatables_filter' => -1,
            'datatables_info' => -1,
            'datatables_tabletools' => -1,
            'datatables_customcommands' => -1,
            'row_offset' => 1, // ATTENTION: MIGHT BE DROPPED IN FUTURE VERSIONS!
            'row_count' => null, // ATTENTION: MIGHT BE DROPPED IN FUTURE VERSIONS!
            'show_rows' => '',
            'show_columns' => '',
            'hide_rows' => '',
            'hide_columns' => '',
            'cellspacing' => false,
            'cellpadding' => false,
            'border' => false
        );
        $default_atts = apply_filters( 'wp_table_reloaded_shortcode_table_default_atts', $default_atts );
        $atts = shortcode_atts( $default_atts, $atts );

        // allow a filter to determine behavior of this function, by overwriting its behavior, just need to return something other than false
        $overwrite = apply_filters( 'wp_table_reloaded_shortcode_table_overwrite', false, $atts );
        if ( $overwrite )
            return $overwrite;

        // check, if a table with the given ID exists
        $table_id = $atts['id'];
        if ( !is_numeric( $table_id ) || 1 > $table_id || !$this->table_exists( $table_id ) ) {
            $message = "[table \"{$table_id}\" not found /]<br />\n";
            $message = apply_filters( 'wp_table_reloaded_table_not_found_message', $message, $table_id );
            return $message;
        }

        $table = $this->load_table( $table_id );

        // check for table data
        if ( empty( $table['data'] ) ) {
            $message = "[table &quot;{$table_id}&quot; seems to be empty /]<br />\n";
            $message = apply_filters( 'wp_table_reloaded_table_empty_message', $message, $table_id );
            return $message;
        }

        $rows = count( $table['data'] );
        $columns = count( $table['data'][0] );
        
        // explode from string to array
        $atts['column_widths'] = ( !empty( $atts['column_widths'] ) ) ? explode( '|', $atts['column_widths'] ) : array();

        // add all rows/columns to array if "all" value set for one of the four parameters
        // rows/columns are indexed from 0 internally, but from 1 externally, thus substract 1 from each value
        $actions = array( 'show', 'hide' );
        $elements = array( 'rows', 'columns' );
        foreach ( $actions as $action ) {
            foreach ( $elements as $element ) {
                if ( !empty( $atts["{$action}_{$element}"] ) ) {
                    if ( 'all' == $atts["{$action}_{$element}"] )
                        $atts["{$action}_{$element}"] = range( 1, ${$element} + 1 ); // because second comment above
                    else
                        $atts["{$action}_{$element}"] = explode( ',', $atts["{$action}_{$element}"] );
                    foreach ( $atts["{$action}_{$element}"] as $key => $value )
                        $atts["{$action}_{$element}"][ $key ] = (string) ( $value - 1 );
                } else {
                        $atts["{$action}_{$element}"] = array();
                }
            }
        }

        // determine options to use (if set in Shortcode, use those, otherwise use options from DB, i.e. "Edit Table" screen)
        $output_options = array();
        foreach ( $atts as $key => $value ) {
            // have to check this, because strings 'true' or 'false' are not recognized as boolean!
            if ( is_array( $value ) )
                $output_options[ $key ] = $value;
            elseif ( 'true' == strtolower( $value ) )
                $output_options[ $key ] = true;
            elseif ( 'false' == strtolower( $value ) )
                $output_options[ $key ] = false;
            else
                $output_options[ $key ] = ( -1 !== $value ) ? $value : $table['options'][ $key ] ;
        }
        
        // generate unique HTML ID, depending on how often this table has already been shown on this page
        $count = ( isset( $this->shown_tables[ $table_id ] ) ) ? $this->shown_tables[ $table_id ] : 0;
        $count = $count + 1;
        $this->shown_tables[ $table_id ] = $count;
        $output_options['html_id'] = "wp-table-reloaded-id-{$table_id}-no-{$count}";
        $output_options['html_id'] = apply_filters( 'wp_table_reloaded_html_id', $output_options['html_id'], $table_id );

        // get options for the JavaScript library from the table's options
        $js_options = array (
            'alternating_row_colors' => $output_options['alternating_row_colors'],
            'datatables_sort' => $output_options['datatables_sort'],
            'datatables_paginate' => $output_options['datatables_paginate'],
            'datatables_paginate_entries' => $output_options['datatables_paginate_entries'],
            'datatables_lengthchange' => $output_options['datatables_lengthchange'],
            'datatables_filter' => $output_options['datatables_filter'],
            'datatables_info' => $output_options['datatables_info'],
            'datatables_tabletools' => $output_options['datatables_tabletools'],
            'datatables_customcommands' => $output_options['datatables_customcommands']
        );
        $js_options = apply_filters( 'wp_table_reloaded_table_js_options', $js_options, $table_id, $output_options );

        // eventually add this table to list of tables which have a JS library enabled and thus are to be included in the script's call in the footer
        if ( $output_options['use_tablesorter'] && $output_options['first_row_th'] && 1 < $rows )
            $this->tablesorter_tables[] = array (
                'table_id' => $table_id,
                'html_id' => $output_options['html_id'],
                'js_options' => $js_options
            );

        // generate "Edit Table" link
        $edit_url = '';
        if ( is_user_logged_in() && $this->options['frontend_edit_table_link'] ) {
            $user_group = $this->options['user_access_plugin'];
            $capabilities = array(
                'admin' => 'manage_options',
                'editor' => 'publish_pages',
                'author' => 'publish_posts',
                'contributor' => 'edit_posts'
            );
            $min_capability = isset( $capabilities[ $user_group ] ) ? $capabilities[ $user_group ] : 'manage_options';
            $min_capability = apply_filters( 'wp_table_reloaded_min_needed_capability', $min_capability );

            if ( current_user_can( $min_capability ) ) {
                $admin_menu_page = $this->options['admin_menu_parent_page'];
                $admin_menu_page = apply_filters( 'wp_table_reloaded_admin_menu_parent_page', $admin_menu_page );
                // backward-compatibility for the filter
                if ( 'top-level' == $admin_menu_page )
                    $admin_menu_page = 'admin.php';
                // 'edit-pages.php' was renamed to 'edit.php?post_type=page' in WP 3.0
                if ( 'edit-pages.php' == $admin_menu_page )
                    $admin_menu_page = 'edit.php?post_type=page';
                if ( !in_array( $admin_menu_page, $this->possible_admin_menu_parent_pages ) )
                    $admin_menu_page = 'tools.php';
                $url_params = array(
                        'page' => $this->page_slug,
                        'action' => 'edit',
                        'table_id' => $table['id']
                );
                $edit_url = add_query_arg( $url_params, admin_url( $admin_menu_page ) );
                $edit_url = esc_url( $edit_url );
            }
        }
        $output_options['edit_table_url'] = $edit_url;

        // check if table output shall and can be loaded from the transient cache, otherwise generate the output
        $cache_name = "wp_table_reloaded_table_output_{$table_id}";
        if ( !$output_options['cache_table_output'] || is_user_logged_in() || ( false === ( $output = get_transient( $cache_name ) ) ) ) {
            // render/generate the table HTML
            $render = $this->create_class_instance( 'WP_Table_Reloaded_Render', 'render.class.php' );
            $render->output_options = apply_filters( 'wp_table_reloaded_frontend_output_options', $output_options, $table['id'], $table );
            $render->table = $table;
            $output = $render->render_table();

            if ( $output_options['cache_table_output'] && !is_user_logged_in() )
                set_transient( $cache_name, $output, 60*60*24 ); // store $output in a transient, set cache timeout to 24 hours
        }

        return $output;
    }

    /**
     * Handle Shortcodes in text-widgets, by temporarily removing all Shortcodes, registering only the plugin's two,
     * running WP's Shortcode routines, and then restoring old behavior/Shortcodes
     *
     * @param string $content Text content of the text-widget, will be searched for one of the plugin's Shortcodes
     * @uses $shortcode_tags
     * @return string Text of the text-widget, with eventually found Shortcodes having been replaced by corresponding output
     */
    function handle_widget_text_filter( $content ) {
        global $shortcode_tags;
        // backup the currently registered Shortcodes and clear the global array
        $orig_shortcode_tags = $shortcode_tags;
        $shortcode_tags = array();
        // register plugin's Shortcodes (which are then the only one's registered)
        add_shortcode( $this->shortcode_table_info, array( &$this, 'handle_content_shortcode_table_info' ) );
        add_shortcode( $this->shortcode_table, array( &$this, 'handle_content_shortcode_table' ) );
        // do the WP Shortcode routines on the widget-text (i.e. search for plugin's Shortcodes)
        $content = do_shortcode( $content );
        // restore the original Shortcodes (which includes plugin's Shortcodes, for use in posts and pages)
        $shortcode_tags = $orig_shortcode_tags;
        return $content;
    }

    /**
     * Expand WP Search to also find posts and pages that have a search term in a table that is shown in them
     *
     * This is done by looping through all search terms and WP-Table Reloaded tables and searching there for the search term,
     * saving all tables's IDs that have a search term and then expanding the WP query to search for posts or pages that have the
     * Shortcode for one of these tables in their content.
     *
     * @param string $search Current part of the "WHERE" clause of the SQL statement used to get posts/pages from the WP database that is related to searching
     * @uses $wpdb
     * @return string Eventually extended SQL "WHERE" clause, to also find posts/pages with Shortcodes in them
     */
    function handle_posts_search_filter( $search_sql ) {
        if ( !is_search() )
            return $search_sql;

        global $wpdb;

        // get variable that contains all search terms, parsed from $_GET['s'] by WP
        $search_terms = get_query_var( 'search_terms' );
        $query_array = ( !empty( $search_terms ) && is_array( $search_terms ) ) ? $search_terms : array();

        // load all tables, and remove hidden cells, as those will not be searched
        // do this here once, so that we don't have to do it in each loop for each search term again
        $search_tables = array();
        foreach ( $this->tables as $table_id => $tableoptionname ) {
            $table = $this->load_table( $table_id );
            $hidden_rows = isset( $table['visibility']['rows'] ) ? array_keys( $table['visibility']['rows'], true ) : array();
            $hidden_columns = isset( $table['visibility']['columns'] ) ? array_keys( $table['visibility']['columns'], true ) : array();
            foreach ( $hidden_rows as $row_idx ) {
                unset( $table['data'][ $row_idx ] );
            }
            $table['data'] = ( !isset( $table['data'] ) ) ? array() : $table['data']; // make sure $table['data'] exists for the next steps
            $table['data'] = array_merge( $table['data'] );
            foreach ( $table['data'] as $row_idx => $row ) {
                foreach ( $hidden_columns as $col_idx ) {
                    unset( $row[ $col_idx ] );
                }
                $table['data'][ $row_idx ] = array_merge( $row );
            }
            // add name and description to searched items, if they are displayed with the table
            $table_name = ( isset( $table['options']['print_name'] ) && $table['options']['print_name'] ) ? $table['name'] : '';
            $table_description = ( isset( $table['options']['print_description'] ) && $table['options']['print_description'] ) ? $table['description'] : '';
            
            $search_tables[ $table_id ] = array(
                'data' => $table['data'],
                'name' => $table_name,
                'description' => $table_description
            );
        }

        // for all search terms loop through all tables's cells (those cells are all visible, because we filtered before!)
        $query_result = array(); // array of all search words that were found, and the table IDs where they were found
        foreach ( $query_array as $search_term ) {
            $search_term = addslashes_gpc( $search_term ); // escapes with esc_sql
            foreach ( $search_tables as $table_id => $table ) {
                if ( false !== stripos( $table['name'], $search_term ) || false !== stripos( $table['description'], $search_term ) ){
                            // we found the $search_term in the name or description (and they are shown)
                            $query_result[ $search_term ][] = $table_id; // add table ID to result list
                            continue; // don't need to search through this table any further, continue with next table
                }
                foreach ( $table['data'] as $table_row ) {
                    foreach ( $table_row as $table_cell ) {
                        if ( false !== stripos( $table_cell, $search_term ) ){
                            // we found the $search_term in the cell
                            $query_result[ $search_term ][] = $table_id; // add table ID to result list
                            break 2; // don't need to search through this table any further, "2" means that we leave both foreach loops
                        }
                    }
                }
            }
        }

        // for all found table IDs for each search term, add additional OR statement to the SQL "WHERE" clause
        $exact = get_query_var( 'exact' ); // if $_GET['exact'] is set, WordPress doesn't use % in SQL LIKE clauses
        $n = ( !empty( $exact ) ) ? '' : '%';
        foreach ( $query_result as $search_term => $tables ) {
            $old_or = "OR ({$wpdb->posts}.post_content LIKE '{$n}{$search_term}{$n}')";
            $table_ids = implode( '|', $tables );
            $regexp = '\\\\[table id=(["\\\']?)(' . $table_ids . ')(["\\\' ])'; // ' needs to be single escaped, [ double escaped (with \\) in mySQL
            $new_or = $old_or . " OR ({$wpdb->posts}.post_content REGEXP '{$regexp}')";
            $search_sql = str_replace( $old_or, $new_or, $search_sql );
        }

        return $search_sql;
    }

    /**
     * Print CSS styles in the header (only called if enabled as a wp_head action), by creating "@import" commands
     * for the default files (depending on JS library used), and outputting any Custom CSS
     */
    function add_frontend_css() {
        $default_css = array();
        if ( $this->options['use_default_css'] ) {
            $plugin_path = plugin_dir_url( WP_TABLE_RELOADED__FILE__ );
            $plugin_path = apply_filters( 'wp_table_reloaded_plugin_path', $plugin_path );

            $url_css_plugin = $plugin_path . 'css/plugin.css' . '?ver=' . $this->options['installed_version'];
            $url_css_plugin = apply_filters( 'wp_table_reloaded_url_css_plugin', $url_css_plugin );
            if ( !empty( $url_css_plugin ) )
                $default_css['plugin.css'] = "@import url(\"{$url_css_plugin}\");";

            // RTL languages support
            if ( is_rtl() ) {
                $url_css_rtl_plugin = $plugin_path . 'css/plugin.rtl.css' . '?ver=' . $this->options['installed_version'];
                $url_css_rtl_plugin = apply_filters( 'wp_table_reloaded_url_css_rtl_plugin', $url_css_rtl_plugin );
                if ( !empty( $url_css_rtl_plugin ) )
                    $default_css['plugin.rtl.css'] = "@import url(\"{$url_css_rtl_plugin}\");";
            }

            if ( $this->options['enable_tablesorter'] ) {
                switch ( $this->options['tablesorter_script'] ) {
                    case 'datatables-tabletools':
                        $url_css_tabletools = $plugin_path . 'js/tabletools/tabletools.css' . '?ver=' . $this->options['installed_version'];
                        $url_css_tabletools = apply_filters( 'wp_table_reloaded_url_css_tabletools', $url_css_tabletools );
                        if ( !empty( $url_css_tabletools ) )
                            $default_css['tabletools.css'] = "@import url(\"{$url_css_tabletools}\");";
                    case 'datatables': // this also applies to the above, because there is no "break;" above
                        $url_css_datatables = $plugin_path . 'css/datatables.css' . '?ver=' . $this->options['installed_version'];
                        $url_css_datatables = apply_filters( 'wp_table_reloaded_url_css_datatables', $url_css_datatables );
                        if ( !empty( $url_css_datatables ) )
                            $default_css['datatables.css'] = "@import url(\"{$url_css_datatables}\");";
                        break;
                    case 'tablesorter':
                    case 'tablesorter_extended':
                        $url_css_tablesorter = $plugin_path . 'css/tablesorter.css' . '?ver=' . $this->options['installed_version'];
                        $url_css_tablesorter = apply_filters( 'wp_table_reloaded_url_css_tablesorter', $url_css_tablesorter );
                        if ( !empty( $url_css_tablesorter ) )
                            $default_css['tablesorter.css'] = "@import url(\"{$url_css_tablesorter}\");";
                        break;
                    default:
                }
            }
        }
        $default_css = apply_filters( 'wp_table_reloaded_default_css', $default_css, $this->options['use_default_css'], $this->options['tablesorter_script'], $this->options['enable_tablesorter'] );
        $default_css = implode( "\n", $default_css );

        $custom_css = '';
        if ( $this->options['use_custom_css'] ) {
            $custom_css = ( isset( $this->options['custom_css'] ) ) ? $this->options['custom_css'] : '';
            $custom_css = stripslashes( $custom_css );
        }
        $custom_css = apply_filters( 'wp_table_reloaded_custom_css', $custom_css, $this->options['use_custom_css'] );

        if ( !empty( $default_css ) || !empty( $custom_css ) ) {
            $divider = ( !empty( $default_css ) && !empty( $custom_css ) ) ? "\n" : '';
            // $default_css needs to stand above $custom_css, so that $custom_css commands can overwrite $default_css commands
            $css = <<<CSSSTYLE
<style type="text/css" media="all">
/* <![CDATA[ */
{$default_css}{$divider}{$custom_css}
/* ]]> */
</style>
CSSSTYLE;
        $css = apply_filters( 'wp_table_reloaded_frontend_css', $css );
        echo $css;
        }
    }

    /**
     * Print JS script tags and corresponding calls to the library's constructor, depending on the list of tables that use one
     */
    function add_frontend_js() {
        if ( 0 == count( $this->tablesorter_tables ) )
            return; // no tables with script enabled shown
            
        switch ( $this->options['tablesorter_script'] ) {
            case 'datatables':
                $jsfile =  'jquery.datatables.min.js';
                $js_command = 'dataTable';
                break;
            case 'datatables-tabletools':
                $include_tabletools = true;
                $jsfile =  'jquery.datatables.min.js';
                $js_command = 'dataTable';
                break;
            case 'tablesorter':
                $jsfile =  'jquery.tablesorter.min.js';
                $js_command = 'tablesorter';
                break;
            case 'tablesorter_extended':
                $jsfile =  'jquery.tablesorter.extended.js';
                $js_command = 'tablesorter';
                break;
            default:
                $jsfile =  'jquery.tablesorter.min.js';
                $js_command = 'tablesorter';
        }

        $js_script_url = plugins_url( 'js/' . $jsfile, WP_TABLE_RELOADED__FILE__ );
        $js_script_url = apply_filters( 'wp_table_reloaded_url_js_script', $js_script_url, $jsfile );
        wp_register_script( 'wp-table-reloaded-frontend-js', $js_script_url, array( 'jquery' ), $this->options['installed_version'] );
        wp_print_scripts( 'wp-table-reloaded-frontend-js' );

        if ( isset( $include_tabletools ) && $include_tabletools ) {
            $js_zeroclipboard_url = plugins_url( 'js/tabletools/zeroclipboard.js', WP_TABLE_RELOADED__FILE__ );
            $js_zeroclipboard_url = apply_filters( 'wp_table_reloaded_url_js_zeroclipboard', $js_zeroclipboard_url );
            // no need to explicitely check for dependencies ( 'wp-table-reloaded-frontend-js' and 'jquery' ) again
            wp_register_script( 'wp-table-reloaded-zeroclipboard-js', $js_zeroclipboard_url, array(), $this->options['installed_version'] );
            wp_print_scripts( 'wp-table-reloaded-zeroclipboard-js' );

            $js_tabletools_url = plugins_url( 'js/tabletools/tabletools.min.js', WP_TABLE_RELOADED__FILE__ );
            $js_tabletools_url = apply_filters( 'wp_table_reloaded_url_js_tabletools', $js_tabletools_url );
            wp_register_script( 'wp-table-reloaded-tabletools-js', $js_tabletools_url, array(), $this->options['installed_version'] );
            $swf_zeroclipboard_url = plugins_url( 'js/tabletools/zeroclipboard.swf', WP_TABLE_RELOADED__FILE__ );
            $swf_zeroclipboard_url = apply_filters( 'wp_table_reloaded_url_swf_zeroclipboard', $swf_zeroclipboard_url );
            wp_localize_script( 'wp-table-reloaded-tabletools-js', 'WP_Table_Reloaded_TableTools', array(
                'swf_path' => $swf_zeroclipboard_url,
                'l10n_print_after' => 'try{convertEntities(WP_Table_Reloaded_TableTools);}catch(e){};'
            ) );
            wp_print_scripts( 'wp-table-reloaded-tabletools-js' );
        }

        // generate the specific commands for the JS library, depending on chosen features on the "Edit Table" screen
        $commands = array();
        foreach ( $this->tablesorter_tables as $tablesorter_table ) {
            $table_id = $tablesorter_table['table_id'];
            $html_id = $tablesorter_table['html_id'];
            $js_options = $tablesorter_table['js_options'];

            $parameters = array();
            switch ( $this->options['tablesorter_script'] ) {
                case 'datatables-tabletools':
                    if ( $js_options['datatables_tabletools'] )
                        $parameters['sDom'] = "\"sDom\": 'T<\"clear\">lfrtip'";
                case 'datatables':
                    $datatables_locale = get_locale();
                    $datatables_locale = apply_filters( 'wp_table_reloaded_datatables_locale', $datatables_locale );
                    $language_file = "languages/datatables/lang-{$datatables_locale}.txt";
                    $language_file = ( file_exists( WP_TABLE_RELOADED_ABSPATH . $language_file ) ) ? '/' . $language_file : '/languages/datatables/lang-default.txt';
                    $language_file_url = plugins_url( $language_file, WP_TABLE_RELOADED__FILE__ );
                    $language_file_url = apply_filters( 'wp_table_reloaded_url_datatables_language_file', $language_file_url );
                    if ( !empty( $language_file_url ) )
                        $parameters['oLanguage'] = "\"oLanguage\":{\"sUrl\": \"{$language_file_url}\"}"; // URL with language file
                    // these parameters need to be added for performance gain or to overwrite unwanted default behavior
                    // $parameters['bAutoWidth'] = '"bAutoWidth": false'; // might need to add this by default
                    $parameters['aaSorting'] = '"aaSorting": []'; // no initial sort
                    $parameters['bSortClasses'] = '"bSortClasses": false'; // don't add additional classes, to speed up sorting
                    $parameters['asStripClasses'] = ( $js_options['alternating_row_colors'] ) ? "\"asStripClasses\":['even','odd']" : '"asStripClasses":[]'; // alternating row colors is default, so remove them if not wanted with []
                    // the following options are activated by default, so we only need to "false" them if we don't want them, but don't need to "true" them if we do
                    if ( !$js_options['datatables_sort'] )
                        $parameters['bSort'] = '"bSort": false';
                    if ( !$js_options['datatables_paginate'] )
                        $parameters['bPaginate'] = '"bPaginate": false';
                    if ( $js_options['datatables_paginate'] && !empty( $js_options['datatables_paginate_entries'] ) && 10 <> $js_options['datatables_paginate_entries'] )
                        $parameters['iDisplayLength'] = '"iDisplayLength": '. $js_options['datatables_paginate_entries'];
                    if ( !$js_options['datatables_lengthchange'] )
                        $parameters['bLengthChange'] = '"bLengthChange": false';
                    if ( !$js_options['datatables_filter'] )
                        $parameters['bFilter'] = '"bFilter": false';
                    if ( !$js_options['datatables_info'] )
                        $parameters['bInfo'] = '"bInfo": false';
                    if ( !empty( $js_options['datatables_customcommands'] ) ) // custom commands added, if not empty
                        $parameters['custom_commands'] = stripslashes( $js_options['datatables_customcommands'] ); // stripslashes is necessary!
                    break;
                case 'tablesorter':
                case 'tablesorter_extended':
                    if ( $js_options['alternating_row_colors'] )
                        $parameters['widgets'] = "widgets: ['zebra']";
                    break;
                default:
            }
            $parameters = apply_filters( 'wp_table_reloaded_js_frontend_parameters', $parameters, $table_id, $html_id, $this->options['tablesorter_script'], $js_command, $js_options );
            $parameters = implode( ", ", $parameters );
            $parameters = ( !empty( $parameters ) ) ? "{{$parameters}}" : '';

            $command = "$(\"#{$html_id}\").{$js_command}({$parameters});";

            $command = apply_filters( 'wp_table_reloaded_js_frontend_command', $command, $table_id, $html_id, $this->options['tablesorter_script'], $js_command, $parameters, $js_options );
            if ( !empty( $command ) )
                $commands[] = "\t{$command}";
        }

        $commands = implode( "\n", $commands );
        $commands = apply_filters( 'wp_table_reloaded_js_frontend_all_commands', $commands );

        if ( !empty( $commands ) ) {
            echo <<<JSSCRIPT
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
{$commands}
});
/* ]]> */
</script>
JSSCRIPT;
        }
    }

} // class WP_Table_Reloaded_Controller_Frontend

?>