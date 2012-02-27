<?php
/**
 * Export Class for WP-Table Reloaded, used to export single tables on the "Export Table" screen
 *
 * @package WP-Table Reloaded
 * @subpackage Classes
 * @author Tobias B&auml;thge
 * @since 0.9.2
 */
 
// should be included by WP_Table_Reloaded_Controller_Admin!
class WP_Table_Reloaded_Export {

    // ###################################################################################################################
    var $export_formats = array();
    var $delimiters = array();
    
    var $export_format = '';
    var $delimiter = ';';
    var $table_to_export = array();
    var $exported_table = '';

    // ###################################################################################################################
    // constructor class
    function WP_Table_Reloaded_Export() {
        // initiate here, because function call __() not allowed outside function
        $this->export_formats = array(
            'csv' => __( 'CSV - Character-Separated Values', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'html' => __( 'HTML - Hypertext Markup Language', WP_TABLE_RELOADED_TEXTDOMAIN ),
            'xml' => __( 'XML - eXtended Markup Language', WP_TABLE_RELOADED_TEXTDOMAIN )
        );
        $this->delimiters = array(
            ';' => __( '; (semicolon)', WP_TABLE_RELOADED_TEXTDOMAIN ),
            ',' => __( ', (comma)', WP_TABLE_RELOADED_TEXTDOMAIN ),
            ':' => __( ': (colon)', WP_TABLE_RELOADED_TEXTDOMAIN ),
            '.' => __( '. (dot)', WP_TABLE_RELOADED_TEXTDOMAIN ),
            '|' => __( '| (pipe)', WP_TABLE_RELOADED_TEXTDOMAIN )
        );
    }

    // ###################################################################################################################
    function export_table() {
        $output = '';
        
        $data = $this->table_to_export['data'];
        
        $rows = count( $data );
        $cols = (0 < $rows) ? count( $data[0] ) : 0;
        
        switch( $this->export_format ) {
            case 'csv':
                if ( 0 < $rows && 0 < $cols) {
                    $parseCSV = $this->create_class_instance( 'parseCSV', 'parsecsv.class.php' );
                    $parseCSV->output_filename = null; // no download initiated by parseCSV class
                    // might need to do something about encoding here as well
                    $output = $parseCSV->output( null, $data, array(), $this->delimiter );
                }
                break;
            case 'xml':
                if ( 0 < $rows && 0 < $cols) {
                    $output .= "<?xml version=\"1.0\" encoding=\"" . get_option('blog_charset') . "\"?>\n";
                    $output .= "<table>\n";
                    foreach ( $data as $row_idx => $row ) {
                        $output .= "\t<row>\n";
                        $row = array_map( array( &$this, 'xml_wrap_and_escape' ), $row );
                        $output .= implode( '', $row );
                        $output .= "\t</row>\n";
                    }
                    $output .= '</table>';
                }
                break;
            case 'html':
                if ( 0 < $rows && 0 < $cols) {
                    $output .= "<table>\n";
                    foreach ( $data as $row_idx => $row ) {
                        $output .= "\t<tr>\n";
                        $row = array_map( array( &$this, 'html_wrap_and_escape' ), $row );
                        $output .= implode( '', $row );
                        $output .= "\t</tr>\n";
                    }
                    $output .= '</table>';
                }
                break;
            default:
        }
        $this->exported_table = $output;
    }

    // ###################################################################################################################
    function xml_wrap_and_escape( $string ) {
        $string = stripslashes( $string );
        if ( $string != htmlspecialchars( $string ) )
            $string = "<![CDATA[{$string}]]>";
        return "\t\t<col>" . $string . "</col>\n";
    }

    // ###################################################################################################################
    function html_wrap_and_escape( $string ) {
        $string = stripslashes( $string );
        return "\t\t<td>" . $string . "</td>\n";
    }

    // ###################################################################################################################
    function create_class_instance( $class, $file, $folder = 'classes' ) {
        if ( !class_exists( $class ) )
            include_once ( WP_TABLE_RELOADED_ABSPATH . $folder . '/' . $file );
        return new $class;
    }

} // class WP_Table_Reloaded_Export

?>