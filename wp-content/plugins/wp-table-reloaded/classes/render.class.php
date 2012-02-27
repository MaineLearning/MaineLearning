<?php
/**
 * Table Rendering Class for WP-Table Reloaded, used to generate HTML output for tables on the frontend
 *
 * @package WP-Table Reloaded
 * @subpackage Classes
 * @author Tobias B&auml;thge
 * @since 1.6
 */

/**
 * Table Rendering class
 */
class WP_Table_Reloaded_Render {

    /**
     * Stores the table and the output options
     * @var array
     */
    var $table = array();
    var $output_options = array();

    /**
     * Trigger words for colspan, rowspan, or the combination of both
     * @var string
     */
    var $colspan_trigger = '#colspan#';
    var $rowspan_trigger = '#rowspan#';
    var $bothspan_trigger = '#span#';

    /**
     * Variables to temporarily store the counts of colspan/rowspan per column/row, initialized in render_table()
     * @var array
     */
    var $rowspan = array();
    var $colspan = array();

    /**
     * PHP4 class constructor
     */
    function WP_Table_Reloaded_Render() {
        // nothing to init here
    }

    /**
     * Returns the rendered HTML of a table
     */
    function render_table() {

        // get only relevant data to render
        $table = $this->get_render_data();

        // allow overwriting the colspan and rowspan trigger keywords, by table ID
        $this->colspan_trigger = apply_filters( 'wp_table_reloaded_colspan_trigger', $this->colspan_trigger, $table['id'] );
        $this->rowspan_trigger = apply_filters( 'wp_table_reloaded_rowspan_trigger', $this->rowspan_trigger, $table['id'] );
        $this->bothspan_trigger = apply_filters( 'wp_table_reloaded_bothspan_trigger', $this->bothspan_trigger, $table['id'] );

        // classes that will be added to <table class=...>, can be used for CSS styling
        $cssclasses = array( 'wp-table-reloaded', "wp-table-reloaded-id-{$table['id']}", stripslashes( $this->output_options['custom_css_class'] ) );
        $cssclasses = apply_filters( 'wp_table_reloaded_table_css_class', $cssclasses, $table['id'] );
        $cssclasses = trim( implode( ' ', $cssclasses ) );

        $rows = count( $table['data'] );
        $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;

        // make array $shortcode_atts['column_widths'] have $cols entries
        $this->output_options['column_widths'] = array_pad( $this->output_options['column_widths'], $cols, '' );

        $output = '';

        if ( 0 < $rows && 0 < $cols ) {

            if ( $this->output_options['print_name'] ) {
                $print_name_html_tag = apply_filters( 'wp_table_reloaded_print_name_html_tag', 'h2', $table['id'] );
                $print_name_css_class = apply_filters( 'wp_table_reloaded_print_name_css_class', "wp-table-reloaded-table-name-id-{$table['id']} wp-table-reloaded-table-name", $table['id'] );
                $name_html = "<{$print_name_html_tag} class=\"{$print_name_css_class}\">" . $this->safe_output( $table['name'] ) . "</{$print_name_html_tag}>\n";
                $print_name_position = $this->output_options['print_name_position'];
                $print_name_position = apply_filters( 'wp_table_reloaded_print_name_position', $print_name_position, $table['id'] );
                
                if ( 'above' == $print_name_position )
                    $output .= $name_html;
            }

            if ( $this->output_options['print_description'] ) {
                $print_description_html_tag = apply_filters( 'wp_table_reloaded_print_description_html_tag', 'span', $table['id'] );
                $print_description_css_class = apply_filters( 'wp_table_reloaded_print_description_css_class', "wp-table-reloaded-table-description-id-{$table['id']} wp-table-reloaded-table-description", $table['id'] );
                $description_html = "<{$print_description_html_tag} class=\"{$print_description_css_class}\">" . $this->safe_output( $table['description'] ) . "</{$print_description_html_tag}>\n";
                $print_description_position = $this->output_options['print_description_position'];
                $print_description_position = apply_filters( 'wp_table_reloaded_print_description_position', $print_description_position, $table['id'] );
                
                if ( 'above' == $print_description_position )
                    $output .= $description_html;
            }

            $output = apply_filters( 'wp_table_reloaded_pre_output_table', $output, $table['id'] );
            $output = apply_filters( 'wp_table_reloaded_pre_output_table_id-' . $table['id'], $output );

            $print_colgroup_tag = apply_filters( 'wp_table_reloaded_print_colgroup_tag', false, $table['id'] );
            $colgroup = '';
            if ( $print_colgroup_tag ) {
                for ( $col = 1; $col <= $cols; $col++ ) {
                    $attributes = "class=\"colgroup-col-{$col}\" ";
                    $attributes = apply_filters( 'wp_table_reloaded_colgroup_tag_attributes', $attributes, $table['id'], $col );
                    $colgroup .= "\t<col {$attributes}/>\n";
                }
            }

            $thead = '';
            $tbody = array();
            $tfoot = '';

            // span counters for rows and columns, init to 1 for each row and column
            $this->rowspan = array_fill( 0, $cols, 1 );
            $this->colspan = array_fill( 0, $rows, 1 );

            $last_row_idx = $rows - 1; // index of the last row
            // go through rows in reversed order, to search for rowspan
            for ( $row_idx = $last_row_idx; $row_idx >= 0; $row_idx-- )  {
                $row =  $table['data'][ $row_idx ];

                // last row, need to check for footer
                if ( $last_row_idx == $row_idx && $this->output_options['table_footer'] ) {
                    $tfoot = $this->render_row( $row, $row_idx, 'th' );
                    continue;
                }

                // first row, need to check for head
                if ( 0 == $row_idx && $this->output_options['first_row_th'] ) {
                    $thead = $this->render_row( $row, $row_idx, 'th' );
                    continue;
                }

                // neither first nor last row (with respective head/foot enabled)
                $tbody[] = $this->render_row( $row, $row_idx, 'td' );
            }

            $tbody_class = ( $this->output_options['row_hover'] ) ? ' class="row-hover"' : '';

            $caption = '';
            if ( !empty( $this->output_options['edit_table_url'] ) ) {
                $edit_table_link = "<a href=\"{$this->output_options['edit_table_url']}\" title=\"" . __( 'Edit', 'default' ) . "\">" . __( 'Edit', 'default' ) . "</a>";
                $caption = "<caption style=\"caption-side: bottom; text-align: left; border:none; background: none;\">{$edit_table_link}</caption>\n";
            }

            $colgroup = ( !empty( $colgroup ) ) ? "<colgroup>\n{$colgroup}</colgroup>\n" : '';
            $thead = ( !empty( $thead ) ) ? "<thead>\n{$thead}</thead>\n" : '';
            $tbody = array_reverse( $tbody ); // because we handle the rows in reversed order
            $tbody = "<tbody{$tbody_class}>\n" . implode( '', $tbody ) . "</tbody>\n";
            $tfoot = ( !empty( $tfoot ) ) ? "<tfoot>\n{$tfoot}</tfoot>\n" : '';

            $id = " id=\"{$this->output_options['html_id']}\"";
            $summary = apply_filters( 'wp_table_reloaded_table_summary_arg', '', $table['id'], $table );
            $summary = ( !empty( $summary ) ) ? " summary=\"" . esc_attr( $summary ) . "\"" : '';
            $class = ( !empty( $cssclasses ) ) ? " class=\"{$cssclasses}\"" : '';
            $cellspacing = ( false !== $this->output_options['cellspacing'] ) ? " cellspacing=\"{$this->output_options['cellspacing']}\"" : '';
            $cellpadding = ( false !== $this->output_options['cellpadding'] ) ? " cellpadding=\"{$this->output_options['cellpadding']}\"" : '';
            $border = ( false !== $this->output_options['border'] ) ? " border=\"{$this->output_options['border']}\"" : '';

            $output .= "\n<table{$id}{$summary}{$class}{$cellspacing}{$cellpadding}{$border}>\n";
            $output .= $caption . $colgroup . $thead . $tfoot . $tbody;
            $output .= "</table>\n";

            $output = apply_filters( 'wp_table_reloaded_post_output_table', $output, $table['id'] );
            $output = apply_filters( 'wp_table_reloaded_post_output_table_id-' . $table['id'], $output );

            // name/description below table (HTML already generated above
            if ( $this->output_options['print_name'] && 'below' == $print_name_position )
                    $output .= $name_html;
            if ( $this->output_options['print_description'] && 'below' == $print_description_position )
                    $output .= $description_html;

        } // endif rows and cols exist

        $output = apply_filters( 'wp_table_reloaded_output_table', $output , $table['id'] );
        $output = apply_filters( 'wp_table_reloaded_output_table_id-' . $table['id'], $output );

        return $output;
    }

    /**
     * Returns the rendered HTML of a row
     */
    function render_row( $row, $row_idx, $tag ) {
        $table_id = $this->table['id'];

        $row_cells = array();
        $last_col_idx = count( $row ) - 1;
        // go through cells in reversed order, to search for colspan
        for ( $col_idx = $last_col_idx; $col_idx >= 0; $col_idx-- )  {
            $cell_content = $row[ $col_idx ];

            $cell_content = do_shortcode( $this->safe_output( $cell_content ) );
            $cell_content = apply_filters( 'wp_table_reloaded_cell_content', $cell_content, $table_id, $row_idx + 1, $col_idx + 1 );

            if ( $this->bothspan_trigger == $cell_content ) { // there will be a combined col- and rowspan
                // check for #span# in first column or first row, which doesn't make sense
                if ( 0 != $col_idx && 0 != $row_idx ) {
                    continue;
                }
                $cell_content = '&nbsp;'; // in that case we set the cell content from '#span#' to a space
            } elseif ( $this->colspan_trigger == $cell_content ) { // there will be a colspan
                // check for #colspan# in first column, which doesn't make sense
                if ( 0 != $col_idx ) {
                    $this->colspan[ $row_idx ]++; // increase counter for colspan in this row
                    $this->rowspan[ $col_idx ] = 1; // reset counter for rowspan in this column, combined col- and rowspan might be happening
                    continue;
                }
                $cell_content = '&nbsp;'; // in that case we set the cell content from '#colspan#' to a space
            } elseif ( $this->rowspan_trigger == $cell_content ) { // there will be a rowspan
                // check for #rowspan# in first row, which doesn't make sense
                if ( 0 != $row_idx ) {
                    $this->rowspan[ $col_idx ]++; // increase counter for rowspan in this column
                    $this->colspan[ $row_idx ] = 1; // reset counter for colspan in this row, combined col- and rowspan might be happening
                    continue;
                }
                $cell_content = '&nbsp;'; // in that case we set the cell content from '#rowspan#' to a space
            }

            $span_attr = '';
            $col_class = 'column-' . ( $col_idx + 1 );
            if ( 1 < $this->colspan[ $row_idx ] ) { // we have colspaned columns
                $span_attr .= " colspan=\"{$this->colspan[ $row_idx ]}\"";
                $col_class .= " colspan-{$this->colspan[ $row_idx ]}";
            }
            if ( 1 < $this->rowspan[ $col_idx ] ) { // we have colspaned columns
                $span_attr .= " rowspan=\"{$this->rowspan[ $col_idx ]}\"";
                $col_class .= " rowspan-{$this->rowspan[ $col_idx ]}";
            }

            $col_class = apply_filters( 'wp_table_reloaded_cell_css_class', $col_class, $table_id, $row_idx + 1, $col_idx + 1, $this->colspan[ $row_idx ], $this->rowspan[ $col_idx ], $cell_content );
            $class_attr = ( !empty( $col_class ) ) ? " class=\"{$col_class}\"" : '';
            $style_attr = ( ( 0 == $row_idx ) && !empty( $this->output_options['column_widths'][$col_idx] ) ) ? " style=\"width:{$this->output_options['column_widths'][$col_idx]};\"" : '';

            if ( $this->output_options['first_column_th'] && 0 == $col_idx )
                $tag = 'th';

            $row_cells[] = "<{$tag}{$span_attr}{$class_attr}{$style_attr}>{$cell_content}</${tag}>";
            $this->colspan[ $row_idx ] = 1; // reset
            $this->rowspan[ $col_idx ] = 1; // reset
        }

        $row_class = 'row-' . ( $row_idx + 1 ) ;
        if ( $this->output_options['alternating_row_colors'] )
            $row_class = ( 1 == ($row_idx % 2) ) ? $row_class . ' even' : $row_class . ' odd';
        $row_class = apply_filters( 'wp_table_reloaded_row_css_class', $row_class, $table_id, $row_idx + 1 );
        if ( !empty( $row_class ) )
            $row_class = " class=\"{$row_class}\"";

        $row_cells = array_reverse( $row_cells ); // because we handle the cells in reversed order
        $row_cells = implode( '', $row_cells );

        $row_html = "\t<tr{$row_class}>\n\t\t{$row_cells}\n\t</tr>\n";
        return $row_html;
    }

    /**
     * Remove all cells that shall not be rendered, because they are hidden, from the data set
     */
    function get_render_data() {
        $table = $orig_table = $this->table;

        // if row_offset or row_count were given, we cut that part from the table and show just that
        // ATTENTION: MIGHT BE DROPPED IN FUTURE VERSIONS!
        if ( null === $this->output_options['row_count'] )
            $table['data'] = array_slice( $table['data'], $this->output_options['row_offset'] - 1 ); // -1 because we start from 1
        else
            $table['data'] = array_slice( $table['data'], $this->output_options['row_offset'] - 1, $this->output_options['row_count'] ); // -1 because we start from 1

        // load information about hidden rows and columns
        $hidden_rows = isset( $table['visibility']['rows'] ) ? array_keys( $table['visibility']['rows'], true ) : array();
        $hidden_rows = array_merge( $hidden_rows, $this->output_options['hide_rows'] );
        $hidden_rows = array_diff( $hidden_rows, $this->output_options['show_rows'] );
        $hidden_columns = isset( $table['visibility']['columns'] ) ? array_keys( $table['visibility']['columns'], true ) : array();
        $hidden_columns = array_merge( $hidden_columns, $this->output_options['hide_columns'] );
        $hidden_columns = array_merge( array_diff( $hidden_columns, $this->output_options['show_columns'] ) );

        // remove hidden rows and re-index
        foreach ( $hidden_rows as $row_idx ) {
            unset( $table['data'][$row_idx] );
        }
        $table['data'] = array_merge( $table['data'] );
        // remove hidden columns and re-index
        foreach ( $table['data'] as $row_idx => $row ) {
            foreach ( $hidden_columns as $col_idx ) {
                unset( $row[$col_idx] );
            }
            $table['data'][$row_idx] = array_merge( $row );
        }
        
        $table = apply_filters( 'wp_table_reloaded_render_table', $table, $orig_table, $this->output_options );
        
        return $table;
    }

    /**
     * Possibly escape the string, replace certain entities, replace newlines with HTML newlines
     *
     * @param string $string The string to escape
     * @return string The escaped string
     */
    function safe_output( $string ) {
        // replace any & with &amp; that is not already an encoded entity (from function htmlentities2 in WP 2.8)
        // complete htmlentities2() or htmlspecialchars() would encode <HTML> tags, which we don't want
        $string = preg_replace( "/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/", "&amp;", $string );
        // remove slashes, as strings are stored with slashes in DB
        $string = stripslashes( $string );
        // change line breaks, nl2br can be overwritten to false, if not wanted
        $apply_nl2br = apply_filters( 'wp_table_reloaded_apply_nl2br', true );
        if ( $apply_nl2br )
            $string = nl2br( $string );
        return $string;
    }

} // class WP_Table_Reloaded_Render

?>