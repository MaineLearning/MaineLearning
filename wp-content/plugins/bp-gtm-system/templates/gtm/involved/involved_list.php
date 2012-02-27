<?php
$resps = BP_GTM_Resps::get_all(bp_get_current_group_id());
//print_var($resps);
?>

<h4><?php _e( 'Involved People', 'bp_gtm' ); ?></h4>

<div class="pagination no-ajax">
    <div id="post-count" class="pag-count">
        <p><?php _e( 'List of users that are responsible for tasks and/or projects in this group. Their tasks and projects are not completed', 'bp_gtm' ); ?></p>
    </div>
</div>
<?php if ( !empty($resps)) { ?>

<table class="forum zebra" id="involved">
    <thead>
        <tr>
            <th id="th-title"><?php _e( 'User', 'bp_gtm' ) ?></th>
            <?php if( $bp_gtm['groups'] == 'all' || array_key_exists(bp_get_current_group_id(), $bp_gtm['groups']) ){ ?>
                <th id="th-poster"><?php _e( 'Role', 'bp_gtm' ) ?></th>
            <?php } ?>
            <th id="th-poster"><?php _e( 'Tasks', 'bp_gtm' ) ?></th>
            <th id="th-group"><?php _e( 'Projects', 'bp_gtm' ) ?></th>
            <th id="th-freshness"><?php _e( 'Actions', 'bp_gtm' ) ?></th>
            
            <?php do_action( 'bp_directory_involved_extra_cell_head' ) ?>
            
        </tr>
    </thead>
    <tbody id="users-list" >
        <?php bp_gtm_resps_loop($resps);?>
    </tbody>
</table><!-- end_table-->

<?php
}else{
   echo '<div id="message" class="info"><p>'.__('There are no users to display yet.','bp_gtm').'</p></div>';
}
?>