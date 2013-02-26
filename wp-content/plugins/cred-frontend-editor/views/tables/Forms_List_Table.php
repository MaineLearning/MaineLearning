<?php
/**
    * class extends WP_List_Table class, gets data from the table and creates a table with pagination according to the data.
    * 
    * 
    */
class CRED_Forms_List_Table extends WP_List_Table
{
	/**
    * method calls parent's construct with array parameters  
    * 
    */  
    function __construct() 
    {
          parent::__construct( array(
	      'plural' => 'list_forms', //plural label, also this well be one of the table css class
          'singular'=> 'list_form', //Singular label
	      'ajax'  => false //We won't support Ajax for this table
       ) );
	}
    
    
 	function no_items() {
		_e( 'No forms were found.','wp-cred' );
	}
    
	function get_bulk_actions() {

		$actions = array();

        $actions['clone-selected'] = __( 'Clone', 'wp-cred' );
        $actions['export-selected'] = __( 'Export', 'wp-cred' );
        $actions['delete-selected'] = __( 'Delete', 'wp-cred' );

		return $actions;
	}
	
	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function bulk_actions() {
		$screen = get_current_screen();

		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			// This filter can currently only be used to remove actions.
			//$this->_actions = apply_filters( 'bulk_actions-cred' . $screen->id, $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<select name='action$two'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'wp-cred' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply', 'wp-cred' ), 'button-secondary action', false, false, array( 'id' => "doaction$two" ) );
		echo "\n";
        
        echo "<a style='margin-left:15px' class='button cred-export-all' href='".cred_route('/Forms/exportAll?all&_wpnonce='.wp_create_nonce('cred-export-all'))."' target='_blank' title='".__('Export All Forms','wp-cred')."'>".__('Export All Forms','wp-cred')."</a>";
	}
	
    /*function bulk_actions( $which ) {
		parent::bulk_actions( $which );
	}*/
    
    /**
    * method overwrites WP_List_Table::get_columns() method and sets the names of the table fields 
    * 
    */ 
    function get_columns() 
    {
	    return $columns= array(
        'cb'          => '<input type="checkbox" />',
        'col_form_name' => __('Name', 'wp-cred'),
        'col_form_type' => __('Type', 'wp-cred'),
        'col_post_type'=>__('Post Type', 'wp-cred'),
        //'col_form_id' => __('ID', 'wp-cred'),
        );
    }
    
    /**
    * method sets the names of the sortable fields 
    * 
    */ 
    function get_sortable_columns() 
    {
	    return $sortable = array(/*
	        'col_form_name'=>array('repository_name',true),*/
        'col_form_name' => array('post_title',false)
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
		
        // sorting
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'post_title';
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';        
        //How many to display per page?
        $perpage=10;
        if (isset($_REQUEST['wp_screen_options']))
        {
            if (isset($_REQUEST['wp_screen_options']['option']) && 'cred_per_page'==$_REQUEST['wp_screen_options']['option']
                && isset($_REQUEST['wp_screen_options']['value'])
            )
            $perpage=intval($_REQUEST['wp_screen_options']['value']);
        }
        elseif (isset($_REQUEST['per_page']))
            $perpage=intval($_REQUEST['per_page']);
            
        //Which page is this?
		$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
		//Page Number
		if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
		
		
        $totalitems=0;
        $this->items=array();
		$fm=CRED_Loader::get('MODEL/Forms');
	 /* -- Fetch the items -- */
		$totalitems=$fm->getFormsCount();//count($this->items);
        if (($paged-1)*$perpage>$totalitems)
            $paged=1;
		$this->items = $fm->getFormsForTable($paged,$perpage, $orderby, $order);
	/* -- Register the pagination -- */
		//How many pages do we have in total?
		$totalpages = ceil($totalitems/$perpage);
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
            "paged" => $paged
		) );
		//The pagination links are automatically built according to those parameters
		
	 /* — Register the Columns — */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
	}
    
   
    /**
    * method forms the data output style 
    * 
    */
    function display_rows() 
    { 
	    $path = admin_url('admin.php').'?page=CRED_Forms';
        $editpath = admin_url('post.php').'?action=edit';
        $exportpath=cred_route('/Forms/exportForm');
        
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
			$settings=isset($rec->meta)?maybe_unserialize($rec->meta):false;
			
			//Open the line
			echo '<tr id="record_'.$rec->ID.'">';
            $checkbox_id =  "checkbox_" . $rec->ID;
            $checkbox = "<input type='checkbox' name='checked[]' value='" . $rec->ID . "' id='" . $checkbox_id . "' /><label class='screen-reader-text' for='" . $checkbox_id . "' >" . __('Select') . " " . $rec->post_title . "</label>";

	        foreach ( $columns as $column_name => $column_display_name ) 
            {
	           //Style attributes for each col
	            $class = "class='$column_name column-$column_name'";
	            $style = "";
	            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
	            $attributes = $class.$style;
	           
	            //Display the cell
	            switch ( $column_name ) {   
	                case "cb":
                        echo "<th scope='row' class='check-column'>$checkbox</th>";
                        break;
                    /*case "col_form_id": 
                        $editlink = $editpath."&post=$rec->ID";
                        echo '<td '.$attributes.'><strong><a href="'.$editlink.'" title="Edit">'.stripslashes($rec->ID).'</a></strong>';
						echo '</td>';
                        break;*/
                    case "col_form_name": 
                        $editlink = $editpath."&post=$rec->ID";
                        $exportlink=$exportpath."?form=$rec->ID&_wpnonce=".wp_create_nonce('cred-export-'.$rec->ID);
                        $onclick_delete="if(confirm('".esc_js( sprintf( __( "Are you sure that you want to delete this form '%s'?\n\n Click [Cancel] to stop, [OK] to delete.", 'wp-cred' ), $rec->post_title ) ) . "' ) ) { return true;}return false;";
                        $onclick_clone="var cred_form_title=prompt('".__('Title of New Form ','wp-cred')."','".$rec->post_title.' Copy'."'); if (cred_form_title) {this.href+='&cred_form_title='+encodeURI(cred_form_title); return true;} else return false;";
                        
                        $actions = array();
                        $actions['edit'] = '<a class="submitedit" href="'.$editlink.'" title="'.__('Edit','wp-cred').'">'.__('Edit','wp-cred').'</a>';
						$actions['delete'] = "<a class='submitdelete' href='".wp_nonce_url( $path."&action=delete&amp;id=$rec->ID", 'delete-form_'.$rec->ID )."' onclick=\"".$onclick_delete."\">" . __( 'Delete', 'wp-cred') . "</a>";
						$actions['clone'] = "<a class='submitclone' href='".wp_nonce_url( $path."&action=clone&amp;id=$rec->ID", 'clone-form_'.$rec->ID )."' onclick=\"".$onclick_clone."\">" . __( 'Clone', 'wp-cred') . "</a>";
                        $actions['export'] = '<a class="submitexport" target="_blank" href="'.$exportlink.'" title="'.__('Export','wp-cred').'">'.__('Export','wp-cred').'</a>';
                        
                        echo '<td '.$attributes.'><strong><a href="'.$editlink.'" title="Edit">'.stripslashes($rec->post_title).'</a>&nbsp;&nbsp;(ID:&nbsp;'.$rec->ID.')</strong>';
						echo $this->row_actions( $actions );
						echo '</td>';
                        break;
                        
	                case "col_form_type": 
                        if ($settings && !empty($settings->form_type))
                        {
                            $_form_type=stripslashes($settings->form_type);
                            if ($_form_type=='new')
                                echo '<td '.$attributes.'>'.__('Create Content','wp-cred').'</td>'; 
                            else
                                echo '<td '.$attributes.'>'.__('Edit Content','wp-cred').'</td>'; 
                        }
                        else
                            echo '<td '.$attributes.'>'.__('Not Set','wp-cred').'</td>'; 
                        break;

	                case "col_post_type": 
                        if ($settings && !empty($settings->post_type))
                        echo '<td '.$attributes.'>'.stripslashes($settings->post_type).'</td>'; 
                        else
                            echo '<td '.$attributes.'>'.__('Not Set','wp-cred').'</td>'; 
                        break;
	            }
	        }
	        echo'</tr>';
            
	    }
    
     }  
     
	/**
	 * Get the current page number
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return int
	 */
	function get_pagenum() {
		//$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		$pagenum = isset( $this->_pagination_args['paged'] ) ? absint( $this->_pagination_args['paged'] ) : 0;
		
        if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

    /**
	 * Display the pagination.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function pagination( $which ) 
    {
		if ( empty( $this->_pagination_args ) )
			return;

		extract( $this->_pagination_args, EXTR_SKIP );

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );
		
        $current_url = remove_query_arg( array( 'per_page' ), $current_url );
        
        $current_url = add_query_arg( 'per_page', $per_page, $current_url );
        
		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='paged' value='%s' size='%d' />",
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) )
			$pagination_links_class = ' hide-if-js';
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages )
			$page_class = $total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';

		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
}
?>