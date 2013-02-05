<?php
/**
 * Helper Class for WP-Table Reloaded, with additional functions for the admin controller
 *
 * @package WP-Table Reloaded
 * @subpackage Classes
 * @author Tobias B&auml;thge
 * @since 1.5
 */

// should be included by WP_Table_Reloaded_Controller_Admin!
class WP_Table_Reloaded_Helper {

    // ###################################################################################################################
    // constructor class
    function WP_Table_Reloaded_Helper() {
        // nothing to init here
    }

    // ###################################################################################################################
    function print_header_message( $text ) {
        echo "<div id='message' class='updated fade'><p style='line-height:1.2;'><strong>{$text}</strong></p></div>";
    }

    // ###################################################################################################################
    function print_page_header( $text = 'WP-Table Reloaded' ) {
        echo '<div class="wrap">';
        if ( function_exists( 'screen_icon' ) ) // it does not for our pseudo-AJAX requests
            screen_icon( 'wp-table-reloaded' );
        echo "<h2>{$text}</h2>";
        echo '<div id="poststuff">';
    }

    // ###################################################################################################################
    function print_page_footer() {
        echo "</div></div>";
    }
    // ###################################################################################################################
    function get_contextual_help_string() {
        $help = '<p>' . sprintf( __( 'More information about WP-Table Reloaded can be found on the <a href="%s">plugin\'s website</a> or on its page in the <a href="%s">WordPress Plugin Directory</a>.', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/website/', 'http://wordpress.org/extend/plugins/wp-table-reloaded/' );
        $help .= ' ' . sprintf( __( 'For technical information, see the <a href="%s">documentation</a>.', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/documentation/' );
        $help .= '<br/>' . sprintf( __( '<a href="%s">Support</a> is provided through the <a href="%s">WordPress Support Forums</a>.', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/support/', 'http://www.wordpress.org/support/' );
        $help .= ' ' . sprintf( __( 'Before asking for support, please carefully read the <a href="%s">Frequently Asked Questions</a> where you will find answered to the most common questions, and search through the forums.', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/faq/' );
        $help .= '<br/>' . sprintf( __( 'If you like the plugin, <a href="%s"><strong>a donation</strong></a> is recommended.', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/go/wp-table-reloaded/donate/' ) . '</p>';
        return $help;
    }

    // ###################################################################################################################
    function safe_output( $string ) {
        $string = stripslashes( $string ); // because $string is add_slashed before WP stores it in DB
        return _wp_specialchars( $string, ENT_QUOTES, false, true );
    }

    // ###################################################################################################################
    // create new two-dimensional array with $num_rows rows and $num_cols columns, each cell filled with $default_cell_content
    function create_empty_table( $num_rows = 1, $num_cols = 1, $default_cell_content = '' ) {
        return array_fill( 0, $num_rows, array_fill( 0, $num_cols, $default_cell_content ) );
    }

    // ###################################################################################################################
    // need to clean this up and find out what's really necessary
    function prepare_download( $filename, $filesize, $filetype ) {
        @ob_end_clean();
        //header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"');
        header( 'Content-Length: ' . $filesize );
        //header( 'Content-type: ' . $filetype. '; charset=' . get_option('blog_charset') );
    }

    // ###################################################################################################################
    // add admin footer text
    function add_admin_footer_text( $content ) {
        $content .= ' &bull; ' . __( 'Thank you for using <a href="http://tobias.baethge.com/wordpress-plugins/wp-table-reloaded-english/">WP-Table Reloaded</a>.', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . sprintf( __( 'Support the plugin with your <a href="%s">donation</a>!', WP_TABLE_RELOADED_TEXTDOMAIN ), 'http://tobias.baethge.com/donate-message/' );
        return $content;
    }

    // ###################################################################################################################
    // state of the postbox, can be filtered
    function postbox_closed( $postbox_name, $postbox_closed ) {
        $postbox_closed = apply_filters( 'wp_table_reloaded_admin_postbox_closed', $postbox_closed, $postbox_name );
        $output = ( $postbox_closed ) ? ' closed' : '';
        return $output;
    }

    // ###################################################################################################################
    // retrieve the update message from the development server to notify user of what changes there are in this update, in his language
    function retrieve_plugin_update_message( $current_version, $new_version ) {
        $message = '';
        $wp_locale = get_locale();
        $update_message = wp_remote_fopen( "http://dev.tobias.baethge.com/plugin/update/wp-table-reloaded/{$current_version}/{$new_version}/{$wp_locale}/" );
        if ( false !== $update_message ) {
            if ( 1 == preg_match( '/<info>(.*?)<\/info>/is', $update_message, $matches ) )
                $message = $matches[1];
        }
        return $message;
    }

    // ###################################################################################################################
    // help buttons
    function help_button( $id ) {
        $help = array(
            'colspan' => array(
                    'text' => __( 'Table cells can span across more than one column or row.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'Combining consecutive cells within the same row is called "colspanning".', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'To combine cells, add the keyword #colspan# to the cell to the right of the one with the content for the combined cell by using the corresponding button.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'Repeat this to add the keyword to all cells that shall be connected.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'Be aware that the JavaScript libraries will not work on tables which have combined cells.', WP_TABLE_RELOADED_TEXTDOMAIN ),
                    'height' => 200,
                    'width' => 300
                ),
            'rowspan' => array(
                    'text' => __( 'Table cells can span across more than one column or row.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'Combining consecutive cells within the same column is called "rowspanning".', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'To combine cells, add the keyword #rowspan# to the cell below the one with the content for the combined cell by using the corresponding button.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'Repeat this to add the keyword to all cells that shall be connected.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/>' . __( 'Be aware that the JavaScript libraries will not work on tables which have combined cells.', WP_TABLE_RELOADED_TEXTDOMAIN ),
                    'height' => 200,
                    'width' => 300
                )
        );

        if ( !isset( $help[ $id ] ) )
            return '';

        $title = __( 'Help', WP_TABLE_RELOADED_TEXTDOMAIN );
        $text = $help[ $id ]['text'];
        $height = $help[ $id ]['height'];
        $width = $help[ $id ]['width'];
        return "<a href=\"#TB_inline?height={$height}&width={$width}&inlineId=help-{$id}\" class=\"help-link button-secondary\" title=\"{$title}\">?</a><div id=\"help-{$id}\" style=\"display:none;\"><p>{$text}</p></div>";
    }

} // class WP_Table_Reloaded_Helper

?>