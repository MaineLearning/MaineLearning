<?php
/**
    * class extends WP_List_Table class, gets data from the table and creates a table with pagination according to the data.
    * 
    * 
    */
class WPRC_DeletedRepositories_List_Table extends WP_List_Table
{
    /**
    * method calls parent's construct with array parameters  
    * 
    */  
    function __construct() 
    {
          parent::__construct( array(
	      'plural' => 'list_deleted_repositories', //plural label, also this well be one of the table css class
          'singular'=> 'list_deleted_repository', //Singular label
	      'ajax'  => false //We won't support Ajax for this table
       ) );
	}
    
    
 	function no_items() {
		_e( 'No deleted repositories were found.','installer' );
	}
    
	/**
    * method overwrites WP_List_Table::get_columns() method and sets the names of the table fields 
    * 
    */ 
    function get_columns() 
    {
	    return $columns= array(
        'col_repository_name' => __('Name', 'installer'),
        'col_repository_endpoint_url' => __('End point url', 'installer'),
        'col_repository_enabled'=>__('Repository enabled', 'installer')
        );
    }
    
    /**
    * method sets the names of the sortable fields 
    * 
    */ 
    function get_sortable_columns() 
    {
	    return $sortable = array(
	        'col_repository_name'=>array('repository_name',true),
	        'col_repository_enabled'=>array('repository_enabled',true)
	    );
	}
    
    /**
    * method gets data to be display inside the table sets pagination data and sets items fields of the parent class 
    * 
    */
    function prepare_items()
    {
	    global $wpdb, $_wp_column_headers;
	    $screen = get_current_screen();
	 
	    /* -- Preparing query -- */
        $query = "SELECT * FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=1";

	    /* -- Ordering parameters -- */
	        //Parameters that are going to be used to order the result
	        $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : '';
	        $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'ASC';
	        if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
	       
	    /* -- Pagination parameters -- */
	        //Number of elements in your table?
	        $totalitems = $wpdb->query($query); //return the total number of affected rows
            
            //How many to display per page?
	        $perpage = 10;
            
	        //Which page is this?
	        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
            
	        //Page Number
	        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
            
	        //How many pages do we have in total?
	        $totalpages = ceil($totalitems/$perpage);
            
	        //adjust the query to take pagination into account
	        if(!empty($paged) && !empty($perpage)){
	            $offset=($paged-1)*$perpage;
	            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	        }
	 
	    /* -- Register the pagination -- */
	        $this->set_pagination_args( array(
	            "total_items" => $totalitems,
	            "total_pages" => $totalpages,
	            "per_page" => $perpage,
	        ) );
	        //The pagination links are automatically built according to those parameters
            
	     /* — Register the Columns — */
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);
            
	     /* -- Fetch the items -- */
	        $this->items = $wpdb->get_results($query);
        }
    
    /**
    * method forms the data output style 
    * 
    */
    function display_rows() 
    { 
	    $path = admin_url().'admin.php?page='.WPRC_PLUGIN_FOLDER.'/pages/deleted-repositories.php';
        
        //Get the records registered in the prepare_items method
	    $records = $this->items;
        
	    //Get the columns registered in the get_columns and get_sortable_columns methods
	    list( $columns, $hidden ) = $this->get_column_info();
        
	    //Loop for each record
	    if(empty($records))
        {
            return false;   
        }
        
        foreach($records as $rec)
        {
	        //Open the line
	        echo '<tr id="record_'.$rec->id.'">';
            
	        foreach ( $columns as $column_name => $column_display_name ) {
	           //Style attributes for each col
	            $class = "class='$column_name column-$column_name'";
	            $style = "";
	            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
	            $attributes = $class.$style;
	           
	            //Display the cell
	            switch ( $column_name ) {   
	                case "col_repository_name": 
                        echo '<td '.$attributes.'><strong>'.stripslashes($rec->repository_name).'</strong>';
                        $actions = array();
						$actions['undelete'] = "<a class='submitundelete' href='".wp_nonce_url( $path."&action=undelete&amp;id=$rec->id", 'undelete-repository_'.$rec->id )."' onclick=\"if(confirm('".esc_js( sprintf( __( "Are you sure that you want to undelete this repository '%s'?\n\n Click 'Cancel' to stop, 'OK' to undelete.", 'installer' ), $rec->repository_name ) ) . "' ) ) { return true;}return false;\">" . __( 'UnDelete', 'installer') . "</a>";
						echo $this->row_actions( $actions );
						echo '</td>';
                        break;
                        
	                case "col_repository_endpoint_url": 
                        echo '<td '.$attributes.'>'.stripslashes($rec->repository_endpoint_url).'</td>'; 
                        break;

	                case "col_repository_enabled": 
                        if($rec->repository_enabled)
                        {
                            $repository_enabled_caption = __('Yes', 'installer');
                        }
                        else
                        {
                            $repository_enabled_caption = __('No', 'installer');
                        }
                        echo '<td '.$attributes.'>'.$repository_enabled_caption.'</td>'; 
                        break;
	            }
	        }
	        echo'</tr>';
            
	    }
    
     }  
     

}

?>