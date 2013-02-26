<?php
/**
    * class extends WP_List_Table class, gets data from the table and creates a table with pagination according to the data.
    * 
    * 
    */
class WPRC_Repositories_List_Table extends WP_List_Table
{
    private $highlights=array();
    private $rtab='default';
    
    /**
    * method calls parent's construct with array parameters  
    * 
    */  
    function __construct() 
    {
          parent::__construct( array(
          'plural' => 'list_repositories', //plural label, also this well be one of the table css class
          'singular'=> 'list_repository', //Singular label
          'ajax'  => false //We won't support Ajax for this table
       ) );
        if (isset($_GET['rtab']) && $_GET['rtab']=='trash')
        {
            $this->rtab='trash';
        }
    }
    
    
    function no_items() {
        _e( 'No repositories were found.','installer' );
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

    function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
            return;

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) )
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        if ( ! empty( $_REQUEST['order'] ) )
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        ?>
        <p class="search-box">
            <span class="description"><?php _e( 'Repository search:', 'installer' ); ?></span> 
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <select name="search_type">

                <option <?php echo ( isset( $_POST['search_type'] ) && $_POST['search_type'] == '0' ) ? 'selected' : ''; ?> value="0"><?php _e( 'All repository types', 'installer' ); ?></option>
                <option <?php echo ( isset( $_POST['search_type'] ) && $_POST['search_type'] == '1' ) ? 'selected' : ''; ?> value="1"><?php _e( 'Plugins repositories', 'installer' ); ?></option>
                <option <?php echo ( isset( $_POST['search_type'] ) && $_POST['search_type'] == '2' ) ? 'selected' : ''; ?> value="2"><?php _e( 'Themes repositories', 'installer' ); ?></option>
            </select>
            <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
        </p>
        <?php
    }

    function display_tablenav( $which ) {
        if ( 'top' == $which )
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        ?>

           

            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <?php if ( 'top' == $which ): ?>
                    <form method="post">
                      <?php $this->search_box( __( 'Search', 'installer'), 'search_repos'); ?>
                    </form>
                <?php endif; ?>

                <div class="alignleft actions">
                    <?php $this->bulk_actions( $which ); ?>
                </div>
        <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
        ?>

                <br class="clear" />
            </div>
        <?php
    }
    
    /**
    * method gets data to be display inside the table sets pagination data and sets items fields of the parent class 
    * 
    */
    function prepare_items()
    {
        global $wpdb, $_wp_column_headers;
        
        if ( isset( $_POST['s']) ) {
            $search = sanitize_text_field($_POST['s']);
            $type_search = ( (int)$_POST['search_type'] );
        }

        if ($this->rtab!='trash')
        {
            $screen = get_current_screen();
         
            /* -- Preparing query -- */
            $query = "SELECT * FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=0";
            //$query2 = "SELECT count(*) FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=0";
            $query3 = "SELECT extension_type_id FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." WHERE repository_id=%d";

            if(isset($search)){ $query.=' AND repository_name LIKE "%'.$search.'%"'; }

            /* -- Ordering parameters -- */
                //Parameters that are going to be used to order the result
                $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : '';
                $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'ASC';
                if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

                
                /* -- Fetch the items -- */
                $results = $wpdb->get_results($query);

                foreach ( $results as $row ) {

                    $pq3 = $wpdb -> prepare($query3, $row -> id);
                    $q3_result = $wpdb -> get_results( $pq3, ARRAY_A );
                    if ( count($q3_result) == 2 ) {
                        $row -> themes = true;
                        $row -> plugins = true;
                    }
                    elseif ( count($q3_result) == 1 ) {
                        $row -> plugins = ( $q3_result[0]['extension_type_id'] == '1' ) ? true : false;
                        $row -> themes = ( $q3_result[0]['extension_type_id'] == '2' ) ? true : false;
                    }
                    else {
                        $row -> themes = false;
                        $row -> plugins = false;
                    }


                }
                if( isset($search) && $type_search != 0 ) {
                    $i = 0;
                    foreach ( $results as $row ) {
                        if ( $type_search == 1 && $row -> plugins == false )
                            unset($results[$i]);
                        elseif ( $type_search == 2 && $row -> themes == false )
                            unset($results[$i]);

                        $i++;
                    }
                }


               
            /* -- Pagination parameters -- */
                //Number of elements in your table?
                //$totalitems = $wpdb->get_var($query2); //return the total number of affected rows
                
                //How many to display per page?
                $perpage = 10;
                
                //Which page is this?
                $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
                
                //Page Number
                if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
                
                
                //The pagination links are automatically built according to those parameters
                
             /* — Register the Columns — */
                $columns = $this->get_columns();
                $hidden = array();
                $sortable = $this->get_sortable_columns();
                $this->_column_headers = array($columns, $hidden, $sortable);
                
                $totalitems = count($results);
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


                $this->items = $results;
                // get hightlighted repos
                $current = get_transient('wprc_update_repositories');
                if ($current!=false && isset($current) && is_object($current) && $current!='')
                {
                    foreach ($current->repos as $hightlightrepo)
                        $this->highlights[$hightlightrepo['url']]=1;
                }
            }
            else
            {
                $screen = get_current_screen();
             
                /* -- Preparing query -- */
                $query = "SELECT * FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=1";
                $query2 = "SELECT count(*) FROM {$wpdb->prefix}".WPRC_DB_TABLE_REPOSITORIES." WHERE repository_deleted=1";

                /* -- Ordering parameters -- */
                    //Parameters that are going to be used to order the result
                    $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : '';
                    $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'ASC';
                    if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
                   
                /* -- Pagination parameters -- */
                    //Number of elements in your table?
                    $totalitems = $wpdb->get_var($query2); //return the total number of affected rows
                    
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


        }



    function display() {

                extract( $this->_args );

                $this->display_tablenav( 'top' );

                                

                ?>
                <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
                    <div id="available-repositories">
                        <?php $this->display_rows_or_placeholder(); ?>
                    </div>
                </table>
                <?php
                $this->display_tablenav( 'bottom' );

    }

    /**
    * method forms the data output style 
    * 
    */
    function display_rows() 
    { 
        if ($this->rtab!='trash')
        {
            $path = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php';
            
            //Get the records registered in the prepare_items method
            $records = $this->items;
            
            //Get the columns registered in the get_columns and get_sortable_columns methods
            list( $columns, $hidden ) = $this->get_column_info();
            
            //Loop for each record
            if(empty($records))
            {
                return false;   
            }
            
            $nonce_login = wp_create_nonce('installer-login-link');
            $nonce_logout = wp_create_nonce('installer-clear-link');

            foreach($records as $rec)
            {

                //Open the line

                $login_url = false;
                $logout_url = false;
                $requires_login = $rec -> requires_login;
                if($rec->repository_endpoint_url!=WPRC_WP_PLUGINS_REPO && $rec->repository_endpoint_url!=WPRC_WP_THEMES_REPO){
                    $login_url = admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' . $rec->id.'&amp;_wpnonce='.$nonce_login);
                }
                if (!empty($rec->repository_username) && !empty($rec->repository_password) && $rec->repository_endpoint_url!=WPRC_WP_PLUGINS_REPO && $rec->repository_endpoint_url!=WPRC_WP_THEMES_REPO) {
                    $logout_url = admin_url('admin.php?wprc_c=repositories&amp;wprc_action=clearLoginInfo&amp;repository_id=' . $rec->id.'&amp;_wpnonce='.$nonce_logout);
                    $clear_link='<a onclick="return wprc.repositories.clearLoginInfo(this,\''.$rec->repository_name.'\');" class="button-secondary" href="'.admin_url('admin.php?wprc_c=repositories&amp;wprc_action=clearLoginInfo&amp;repository_id='.$rec->id.'&amp;_wpnonce='.$nonce_logout).'">'.__('Log out','installer').'</a>';
                }

                $delete_url = wp_nonce_url( $path."&action=delete&amp;id=$rec->id", 'delete-repository_'.$rec->id );
                $edit_url = $path.'&action=edit&amp;id='.$rec->id;

                if ( $rec -> plugins ) {
                    $search_plugins_url = admin_url('plugin-install.php?repos%5B%5D=' . $rec -> id . '&tab=search&plugin-search-input=Search+Plugins');
                }
                if ( $rec -> themes ) {
                    $search_themes_url = admin_url('theme-install.php?repos%5B%5D=' . $rec -> id . '&tab=search&search=Search');
                }

                $content = '<p>'. stripslashes($rec -> repository_description) . '</p>';

                
                ?>

                

                    <hr style="border-color:#e5e5e5"/>
                    <div class="available-repository">

                        <?php if ( !empty($rec -> repository_logo) ): ?>
                            <a href="<?php echo $rec->repository_website_url; ?>" class="screenshot">
                        <?php else: ?>
                            <div class="screenshot">
                        <?php endif; ?>
                        
                            <div class="logo-wrap">
                                <?php if ( !empty($rec -> repository_logo) ): ?>
                                    <img src="<?php echo $rec -> repository_logo; ?>" />
                                <?php endif; ?>
                            </div>
                            
                            <?php if ( $rec -> repository_website_url ): ?>
                                <div class="visit-link-wrap"><span class="visit-text"><?php _e('Visit', 'installer' ); ?></span> <span class="link-text"><?php echo $rec->repository_name; ?></span>  </div>
                            <?php endif; ?>

                        <?php if ( !empty($rec -> repository_logo) ): ?>
                            </a>
                        <?php else: ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h2><?php echo $rec->repository_name; ?></h2>  
                            <div class="repository-log-details">
                                <?php if ( $requires_login ): ?>
                                    <?php if ( $login_url && !$logout_url ): ?>
                                        <a href="<?php echo $login_url; ?>" class="button-primary thickbox"><?php _e('Log in','installer'); ?></a>
                                        <div class="logged-message" ><div class="img-not-logged-in"></div><span class="not-logged-in"><?php _e( 'Not logged in', 'installer' ); ?></span></div>
                                    <?php elseif ( $logout_url ): ?>
                                        <?php echo $clear_link; ?>
                                        <div class="logged-message" ><div class="img-logged-in"></div><span class="logged-in"><?php _e( 'Logged in', 'installer' ); ?></span></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="clear"></div>
                        </div>                              
                         <div>
                            
                            
                            <div class="repository-content">
                                <?php echo $content; ?>
                            </div>
                            <div class="repository-footer">
                                <div class="install-links">
                                    <?php if ( $rec -> plugins ): ?>
                                        <p><div class="plugin-install"></div><a href="<?php echo $search_plugins_url ?>"><?php printf( __( 'Install plugins from %s', 'installer' ), $rec->repository_name ); ?></a></p>
                                    <?php endif; ?>
                                    <?php if ( $rec -> themes ): ?>
                                        <p><div class="theme-install"></div><a href="<?php echo $search_themes_url ?>"><?php printf( __( 'Install themes from %s', 'installer' ), $rec->repository_name ); ?></a></p>
                                    <?php endif; ?>
                                </div>
                                <div class="edit-links"></p>
                                    <a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'installer' ); ?></a> <span>|</span> 
                                    <a class="submitdelete" onclick="if(confirm('<?php echo esc_js( sprintf( __( "Are you sure that you want to delete this repository '%s'?\n\n Click 'Cancel' to stop, 'OK' to delete.", 'installer' ), $rec->repository_name ) ); ?>')) { return true;}return false;" ) ) href="<?php echo $delete_url; ?>"><?php _e( 'Delete', 'installer' ); ?></a></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>   
                                                    
                    </div>

                    
                <?php
                
            }
        }
        else
        {
            $path = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php&rtab=trash';
            
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

                $undelete_url = wp_nonce_url( $path."&action=undelete&amp;id=$rec->id", 'undelete-repository_'.$rec->id );
                $edit_url = $path.'&action=edit&amp;id='.$rec->id;

                $content = '<p>'. stripslashes($rec -> repository_description) . '</p>';
                //Open the line
                
                ?>
                    
                    <hr style="border-color:#e5e5e5"/>
                    <div class="available-repository">

                        <?php if ( !empty($rec -> repository_logo) ): ?>
                            <a href="<?php echo $rec->repository_website_url; ?>" class="screenshot">
                        <?php else: ?>
                            <div class="screenshot">
                        <?php endif; ?>
                        
                            <div class="logo-wrap">
                                <?php if ( !empty($rec -> repository_logo) ): ?>
                                    <img src="<?php echo $rec -> repository_logo; ?>" />
                                <?php endif; ?>
                            </div>
                            
                            <?php if ( $rec -> repository_website_url ): ?>
                                <div class="visit-link-wrap"><span class="visit-text"><?php _e('Visit', 'installer' ); ?></span> <span class="link-text"><?php echo $rec->repository_name; ?></span>  </div>
                            <?php endif; ?>

                        <?php if ( !empty($rec -> repository_logo) ): ?>
                            </a>
                        <?php else: ?>
                            </div>
                        <?php endif; ?>


                        <div>
                            <h2><?php echo $rec->repository_name; ?></h2>  
                            <div class="repository-log-details">
                            </div>
                            <div class="clear"></div>
                        </div>

                        <div class="repository-content">
                            <?php echo $content; ?>
                        </div>
                        <div class="repository-footer">
                            <div class="install-links">
                            </div>
                            <div class="edit-links"></p>
                                <a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'installer' ); ?></a> <span>|</span> 
                                <a class="submitundelete" onclick="if(confirm('<?php echo esc_js( sprintf( __( "Are you sure that you want to undelete this repository '%s'?\n\n Click 'Cancel' to stop, 'OK' to delete.", 'installer' ), $rec->repository_name ) ); ?>')) { return true;}return false;" ) ) href="<?php echo $undelete_url; ?>"><?php _e( 'Undelete', 'installer' ); ?></a></span></p>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>

                    
                <?php
                
            }
        }
    
     }  
     

}

?>