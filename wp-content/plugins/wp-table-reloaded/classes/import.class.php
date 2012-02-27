<?php
/**
 * Import Class for WP-Table Reloaded, used to import single tables on the "Import Table" screen
 *
 * @package WP-Table Reloaded
 * @subpackage Classes
 * @author Tobias B&auml;thge
 * @since 0.9.2
 */

// should be included by WP_Table_Reloaded_Controller_Admin!
class WP_Table_Reloaded_Import {

    // ###################################################################################################################
    // possible import formats
    var $import_formats = array();

    // used if file uploaded
    var $filename = '';
    var $tempname = '';
    var $mimetype = '';

    // filled before import
    var $import_format = '';
    var $import_from = '';
    var $wp_table_id = '';
    
    // return values
    var $error = false;
    var $imported_table = array();

    // ###################################################################################################################
    // constructor class
    function WP_Table_Reloaded_Import() {
        $this->import_formats = array(
            'csv' => __( 'CSV - Character-Separated Values', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'html' => __( 'HTML - Hypertext Markup Language', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'xml' => __( 'XML - eXtended Markup Language', WP_TABLE_RELOADED_TEXTDOMAIN )
            // don't have this show up in list, as handled in separate table
            // 'wp_table' => 'wp-Table plugin database'
        );
    }

    // ###################################################################################################################
    function import_table() {
        switch( $this->import_format ) {
            case 'csv':
                $this->import_csv();
                break;
            case 'html':
                $this->import_html();
                break;
            case 'xml':
                $this->import_xml();
                break;
            case 'wp_table':
                $this->import_wp_table();
                break;
            default:
                $this->imported_table = array();
        }

        // only check this, if needed functions are available (needs PHP library "mbstring")
        if ( function_exists('mb_detect_encoding') && function_exists('mb_check_encoding') && function_exists('utf8_encode') )
            $this->fix_table_encoding();
        
    }

    // ###################################################################################################################
    function import_csv() {
        $table = $this->get_table_meta();

        $parseCSV = $this->create_class_instance( 'parseCSV', 'parsecsv.class.php' );
        
        switch ( $this->import_from ) {
            case 'form-field':
            case 'url':
                $temp_data = $this->import_data;
                break;
            case 'file-upload':
            case 'server':
                $temp_data = file_get_contents( $this->tempname );
                break;
            default:
                // this should never happen
                $this->imported_table = array();
                $this->error = true;
                return;
        }

        if ( empty( $temp_data ) ) {
            $this->imported_table = array();
            $this->error = true;
            return;
        }

        $parseCSV->heading = false; // means: treat first row like all others
        
        // different things have worked, but don't always
        // none of the following: 1 of 3
        //$parseCSV->encoding( 'ISO-8859-1', 'ISO-8859-1//IGNORE' ); // might need to play with this a little or offer an option // 1 of 3
        //$parseCSV->encoding( 'ISO-8859-1', 'UTF-8//IGNORE' ); // might need to play with this a little or offer an option // 0 of 3
        //$parseCSV->encoding( 'ISO-8859-1', 'UTF-8' ); // might need to play with this a little or offer an option // 0 of 3
        //$parseCSV->encoding( 'Windows-1252', 'UTF-8//IGNORE' ); // might need to play with this a little or offer an option // 1 of 3
        $parseCSV->load_data( $temp_data );
        $parseCSV->auto(); // let parsecsv do its magic (determine delimiter and parse the data)

        $temp_table = $parseCSV->data;
            
        $table['data'] = $this->pad_array_to_max_cols( $temp_table );
        $this->imported_table = $table;
    }

    // ###################################################################################################################
    function import_html() {
        $table = $this->get_table_meta();

        $simpleXML = $this->create_class_instance( 'simplexml', 'simplexml.class.php' );

        switch ( $this->import_from ) {
            case 'form-field':
            case 'url':
                $temp_data = $this->import_data;
                break;
            case 'file-upload':
            case 'server':
                $temp_data = file_get_contents( $this->tempname );
                break;
            default:
                // this should never happen
                $this->imported_table = array();
                $this->error = true;
                return;
        }

        if ( empty( $temp_data ) ) {
            $this->imported_table = array();
            $this->error = true;
            return;
        }

        // extract table from html, pattern: <table> (with eventually class, id, ...
        // . means any charactery (except newline),
        // * means in any number
        // ? means non-gready (shortest possible)
        // is at the end: i: case-insensitive, s: include newline (in .)
        if ( 1 == preg_match( '/<table.*?>.*?<\/table>/is', $temp_data, $matches ) )
            $temp_data = $matches[0]; // if found, take match as table to import
        else {
            $this->imported_table = array();
            $this->error = true;
            return;
        }
            
        // most inner items have to be escaped, so we can get their contents as a string not as array elements
        $temp_data = preg_replace( '#<td(.*?)>#', '<td><![CDATA[' , $temp_data ); //eventually later <td$1>
        $temp_data = preg_replace( '#</td>#', ']]></td>' , $temp_data );
        $temp_data = preg_replace( '#<thead(.*?)>#', '<_thead>' , $temp_data ); // temporaray, otherwise <thead> will be affected by replacement of <th
        $temp_data = preg_replace( '#<th(.*?)>#', '<th><![CDATA[' , $temp_data ); //eventually later <th$1>
        $temp_data = preg_replace( '#<_thead(.*?)>#', '<thead>' , $temp_data ); // revert from 2 lines above
        $temp_data = preg_replace( '#</th>#', ']]></th>' , $temp_data );
        $temp_data = $simpleXML->xml_load_string( $temp_data, 'array' );

        if ( false == is_array( $temp_data ) ) {
            $this->imported_table = array();
            $this->error = true;
            return;
        }

        $data = array();
        
        $rows = array();
        $rows = ( isset( $temp_data['thead'][0]['tr'] ) ) ? array_merge( $rows, $temp_data['thead'][0]['tr'] ) : $rows ;
        $rows = ( isset( $temp_data['tbody'][0]['tr'] ) ) ? array_merge( $rows, $temp_data['tbody'][0]['tr'] ) : $rows ;
        $rows = ( isset( $temp_data['tfoot'][0]['tr'] ) ) ? array_merge( $rows, $temp_data['tfoot'][0]['tr'] ) : $rows ;
        $rows = ( isset( $temp_data['tr'] ) ) ? array_merge( $rows, $temp_data['tr'] ) : $rows ;
        foreach ($rows as $row ) {
            $th_cols = ( isset( $row['th'] ) ) ? $row['th'] : array() ;
            $td_cols = ( isset( $row['td'] ) ) ? $row['td'] : array() ;
            $data[] = array_merge( $th_cols, $td_cols );
        }

        $table['data'] = $this->pad_array_to_max_cols( $data );
        $this->imported_table = $table;
    }

    // ###################################################################################################################
    function import_xml() {
        $table = $this->get_table_meta();

        $simpleXML = $this->create_class_instance( 'simplexml', 'simplexml.class.php' );

        switch ( $this->import_from ) {
            case 'form-field':
            case 'url':
                $temp_data = $simpleXML->xml_load_string( $this->import_data, 'array' );
                break;
            case 'file-upload':
            case 'server':
                $temp_data = $simpleXML->xml_load_file( $this->tempname, 'array' );
                break;
            default:
                // this should never happen
                $this->imported_table = array();
                $this->error = true;
                return;
        }

        if ( false == is_array( $temp_data ) || false == isset( $temp_data['row'] ) || empty( $temp_data['row'] ) ) {
            $this->imported_table = array();
            $this->error = true;
            return;
        }

        $data = $temp_data['row'];
        foreach ($data as $key => $value )
            $data[$key] = $value['col'];

        $table['data'] = $this->pad_array_to_max_cols( $data );
        $this->imported_table = $table;
    }

    // ###################################################################################################################
    function import_wp_table() {
        global $wpdb;
        $wpdb->golftable  = $wpdb->prefix . 'golftable';
        $wpdb->golfresult = $wpdb->prefix . 'golfresult';

        $table_id = $this->wp_table_id;

        $temp_data = array();

        if ( $wpdb->golftable == $wpdb->get_var( "show tables like '{$wpdb->golftable}'" ) && $wpdb->golfresult == $wpdb->get_var( "show tables like '{$wpdb->golfresult}'" ) ) {
        // wp-Table tables exist -> the plugin might be installed, so we might try an export

        // get information form database
    	$current_table = $wpdb->get_row( "SELECT * FROM $wpdb->golftable WHERE table_aid = {$table_id}" );
        $table['name'] = $current_table->table_name;
        $table['description'] = $current_table->description;
        $table['options']['print_name'] = ( 1 == $current_table->show_name ) ? true : false;
        $table['options']['print_description'] = ( 1 == $current_table->show_desc ) ? true : false;
        $table['options']['first_row_th'] = ( 1 == $current_table->head_bold ) ? true : false;
        $table['options']['alternating_row_colors'] = ( 1 == $current_table->alternative ) ? true : false;

        // wp-Table's config, contains information about tablesort
        $wptable_config = get_option( 'wptable' );
        if ( false !== $wptable_config )
            $table['options']['use_tablesorter'] = ( 1 == $wptable_config['use_sorting'] ) ? true : false;

        $row_ids = $wpdb->get_col( "SELECT row_id FROM {$wpdb->golfresult} WHERE table_id = '{$table_id}' GROUP BY row_id ORDER BY row_id ASC" );
        // load table data from database
        foreach ( $row_ids as $row_id ) {
            if ( $row_id > 0 ) { // $row_id = -1 and 0 contain width and alignment information, which we don't need
                $column = $wpdb->get_col( "SELECT value FROM {$wpdb->golfresult} WHERE table_id = '{$table_id}' AND row_id ='{$row_id}' ORDER BY result_aid ASC" );
                $temp_rows[] = $column; // new row content = array of columns (appended by [])
            }
        }

        // save table
        $table['data'] = $this->pad_array_to_max_cols( $temp_rows );
        $this->imported_table = $table;
        } else {
            // no tables from wp-Table found
            $this->imported_table = array();
            $this->error = true;
        }
    }

    // ###################################################################################################################
    function unlink_uploaded_file() {
        unlink( $this->tempname );
    }

    // ###################################################################################################################
    // make sure array is rectangular with $max_cols columns in every row
    function pad_array_to_max_cols( $array_to_pad ){
        $rows = count( $array_to_pad );
        $max_columns = $this->count_max_columns( $array_to_pad );
        $rows = ( 0 < $rows ) ? $rows : 1;
        $max_columns = ( 0 < $max_columns ) ? $max_columns : 1;
        // array_map wants arrays as additional parameters (so we create one with the max_cols to pad to and one with the value to use (empty string)
        $max_columns_array = array_fill( 1, $rows, $max_columns );
        $pad_values_array =  array_fill( 1, $rows, '' );
        return array_map( 'array_pad', $array_to_pad, $max_columns_array, $pad_values_array );
    }

    // ###################################################################################################################
    // find out how many cols the longest row has
    function count_max_columns( $array ){
        $max_cols = 0 ;
        if ( is_array( $array ) && 0 < count( $array ) ) {
            foreach ( $array as $row_idx => $row ) {
                $cols  = count( $row );
                $max_cols = ( $cols > $max_cols ) ? $cols : $max_cols;
            }
        }
        return 	$max_cols;
    }

    // ###################################################################################################################
    function get_table_meta() {
        $table['name'] = $this->filename;
        $table['description'] = $this->filename;
        $table['description'] .= ( false == empty( $this->mimetype ) ) ? ' (' . $this->mimetype . ')' : '';
        return $table;
    }

    // ###################################################################################################################
    function create_class_instance( $class, $file, $folder = 'classes' ) {
        if ( !class_exists( $class ) )
            include_once ( WP_TABLE_RELOADED_ABSPATH . $folder . '/' . $file );
        return new $class;
    }

    // ###################################################################################################################
    // fixes the encoding to UTF-8
    function fix_encoding( $string ) {
        return ( 'UTF-8' == mb_detect_encoding( $string ) && mb_check_encoding( $string, 'UTF-8' ) ) ? $string : utf8_encode( $string );
    }

    // ###################################################################################################################
    // fixes the encoding to UTF-8
    function fix_table_encoding() {
        $data = $this->imported_table['data'];
        if ( is_array( $data ) && 0 < count( $data ) ) {
            foreach ( $data as $row_idx => $row ) {
                $data[$row_idx] = array_map( array( &$this, 'fix_encoding' ), $row );
            }
            $this->imported_table['data'] = $data;
        }
    }

} // class WP_Table_Reloaded_Import

?>