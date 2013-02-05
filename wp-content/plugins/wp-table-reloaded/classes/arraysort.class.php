<?php
/**
 * Array Sort Class for WP-Table Reloaded, used to sort tables on the "Edit Table" screen
 *
 * @package WP-Table Reloaded
 * @subpackage Classes
 * @author Tobias B&auml;thge
 * @since 1.2
 */

// should be included by WP_Table_Reloaded_Controller_Admin!
class arraysort {

    var $input_array = array();
    var $sorted_array = array();
    var $column = -1;
    var $order = 'ASC';
    var $error = false;

    function arraysort( $array = array(), $column = -1, $order = 'ASC' )
    {
        $this->input_array = $array;
        $this->column = $column;
        $this->order = $order;
        if ( !empty ($array) && -1 != $column )
            $this->sort();
    }

    function compare_rows( $a, $b )
    {
        if ( -1 == $this->column )
            return 0;

        return strnatcasecmp( $a[ $this->column ], $b[ $this->column ] );
    }
    
    function sort() {
        $array_to_sort = $this->input_array;
        if ( usort( $array_to_sort, array( &$this, 'compare_rows' ) ) ) {
            $this->sorted_array = ( 'DESC' == $this->order ) ? array_reverse( $array_to_sort ) : $array_to_sort;
        } else {
            $this->sorted_array = $this->input_array;
            $this->error = true;
        }
    }
}

?>