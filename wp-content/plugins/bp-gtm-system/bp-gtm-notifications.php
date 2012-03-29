<?php
//Create notifications about new/updated/done/delete tasks/projects that this user responsible for
function bp_gtm_format_notifications( $action, $elem_id, $group_id, $total_items ) {
    global $bp;

    $group = groups_get_group(array('group_id' => $group_id));
    $gtm_link = bp_get_group_permalink($group). $bp->gtm->slug;
    $element = explode('_', $action);

    switch ($action) {
        case 'task_created':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/tasks?action=created">' . __('Tasks you\'re now responsible for were created', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/tasks/view/'.$elem_id.'?action=created">' . __('Task you\'re now responsible for was created', 'bp_gtm') . '</a>';
            }
            break;
        case 'task_edited':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/tasks?action=edited">' . __('Tasks you\'re responsible for were updated', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/tasks/view/'.$elem_id.'?action=edited">' . __('Task you\'re responsible for was updated', 'bp_gtm') . '</a>';
            }
            break;
        case 'task_done':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/tasks?action=done">' . __('Tasks you\'re responsible for were done', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/tasks/view/'.$elem_id.'?action=done">' . __('Task you\'re responsible for was done', 'bp_gtm') . '</a>';
            }
            break;
        case 'task_undone':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/tasks?action=undone">' . __('Tasks you\'re responsible for were undone', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/tasks/view/'.$elem_id.'?action=undone">' . __('Task you\'re responsible for was undone', 'bp_gtm') . '</a>';
            }
            break;
        case 'task_deleted':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/tasks?action=deleted">' . __('Tasks you\'re responsible for were deleted', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/tasks?action=deleted">' . __('Task you\'re responsible for was deleted', 'bp_gtm') . '</a>';
            }
            break;
      
      // and now projects
        case 'project_created':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/projects?action=created">' . __('Projects you\'re now responsible for were created', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/projects/view/'.$elem_id.'?action=created">' . __('Project you\'re now responsible for was created', 'bp_gtm') . '</a>';
            }
            break;
        case 'project_edited':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/projects?action=edited">' . __('Projects you\'re responsible for were updated', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/projects/view/'.$elem_id.'?action=edited">' . __('Project you\'re responsible for was updated', 'bp_gtm') . '</a>';
            }
            break;
        case 'project_done':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/projects?action=done">' . __('Projects you\'re responsible for were done', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/projects/view/'.$elem_id.'?action=done">' . __('Project you\'re responsible for was done', 'bp_gtm') . '</a>';
            }
            break;
        case 'project_undone':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/projects?action=undone">' . __('Projects you\'re responsible for were undone', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/projects/view/'.$elem_id.'?action=undone">' . __('Project you\'re responsible for was undone', 'bp_gtm') . '</a>';
            }
            break;
        case 'project_deleted':
            if ( (int)$total_items > 1 ) {
                return '<a href="'.$gtm_link.'/projects?action=deleted">' . __('Projects you\'re responsible for were deleted', 'bp_gtm') . '</a>';
            }else{
                return '<a href="'.$gtm_link.'/projects/view/'.$elem_id.'?action=deleted">' . __('Project you\'re responsible for was deleted', 'bp_gtm') . '</a>';
            }
            break;
   }

    do_action( 'bp_gtm_format_notifications', $action, $elem_id, $group_id, $total_items );

    return false;
}

// And delete data after user saw this notification
function bp_gtm_remove_screen_notifications( $user_id, $elem_id, $action ) {
    global $wpdb, $bp;

    if($elem_id !='none') {
        $item_id = 'AND `item_id` = '.$elem_id;
    }else{
        $item_id = '';
    }

    return $wpdb->query( $wpdb->prepare( "
                DELETE FROM {$bp->core->table_name_notifications}
                WHERE `user_id` = %d $item_id AND `component_name` = %s AND `component_action` = %s", $user_id, $bp->gtm->slug, $action) );
}
add_action( 'bp_gtm_delete_view_notification', 'bp_gtm_remove_screen_notifications', 1, 3 );

/*
 * Make a record in group activity stream
 */
add_action('groups_register_activity_actions', 'bp_gtm_set_group_actions');
function bp_gtm_set_group_actions(){
    global $bp;

    if ( !function_exists( 'bp_activity_set_action' ) )
            return false;
    
    bp_activity_set_action( $bp->groups->id, 'created_task', __( 'Created the task', 'bp_gtm' ) );
    bp_activity_set_action( $bp->groups->id, 'created_project', __( 'Created the project', 'bp_gtm' ) );
    bp_activity_set_action( $bp->groups->id, 'created_discuss', __( 'Created the discussion post', 'bp_gtm' ) );
}

function bp_gtm_group_activity( $args = '' ){
    global $bp;

    $bp_gtm = get_option('bp_gtm');
    
    if($bp_gtm['display_activity'] == 'off')
        return false;
    
    $defaults = array(
            'content' => false,
            'user_id' => $bp->loggedin_user->id,
            'group_id' => $bp->groups->current_group->id,
            'elem_id' => false,
            'elem_type' => 'task',
            'elem_name' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );
    

    $bp->groups->current_group = new BP_Groups_Group( $group_id );

    /* Be sure the user is a member of the group before posting. */
    if ( !is_super_admin() && !groups_is_user_member( $user_id, $group_id ) )
            return false;
    
    if($elem_type == 'project'){
        $activity_action = sprintf( __( '%s created the project - %s', 'bp_gtm'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . $bp->gtm->slug . '/projects/view/'.$elem_id.'">' . bp_gtm_get_el_name_by_id( $elem_id, $elem_type ) . '</a>' );
        $activity_type = 'created_project';
    }elseif($elem_type == 'task'){
        $activity_action = sprintf( __( '%s created the task - %s', 'bp_gtm'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . $bp->gtm->slug  . '/tasks/view/'.$elem_id.'">' . bp_gtm_get_el_name_by_id( $elem_id, $elem_type ) . '</a>' );
        $activity_type = 'created_task';
    }else{
        if($bp_gtm['display_activity_discuss'] == 'off')
            return false;
        // $elem_type =  'discuss_tasks_' . $_POST['task_id'];
        $discuss = explode('_', $elem_type);
        if ( $discuss[1] == 'tasks' ){
            $activity_action = sprintf( __( '%s posted a comment to the task %s', 'bp_gtm'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . $bp->gtm->slug  . '/'. $discuss[1] .'/view/'.$discuss[2].'#post-' . $elem_id.'">' . bp_gtm_get_el_name_by_id( $discuss[2], $discuss[1] ) . '</a>' );
        }elseif( $discuss[1] == 'projects' ){
            $activity_action = sprintf( __( '%s posted a comment to the project %s', 'bp_gtm'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . $bp->gtm->slug  . '/'. $discuss[1] .'/view/'.$discuss[2].'#post-' . $elem_id.'">' . bp_gtm_get_el_name_by_id( $discuss[2], $discuss[1] ) . '</a>' );
        }
        $activity_type = 'created_discuss';
    }
    $hide_sitewide = $bp->groups->current_group->status != 'private' || $bp->groups->current_group->status != 'hidden'? TRUE: FALSE;

    /* Record this in activity streams */
    $activity_id = groups_record_activity( array(
            'user_id' => $user_id,
            'action' => apply_filters( 'bp_gtm_activity_new_elem_action', $activity_action ),
            'content' => apply_filters( 'bp_gtm_activity_new_elem_content', $activity_content ),
            'type' => $activity_type,
            'component' => $bp->groups->id,
            'item_id' => $group_id,
            'secondary_item_id' => $elem_id,
            'hide_sitewide' => $hide_sitewide,
    ) );
   
    /* Require the notifications code so email notifications can be set on the 'bp_activity_posted_update' action. */
    require_once( BP_PLUGIN_DIR . '/bp-groups/bp-groups-notifications.php' );

    groups_update_groupmeta( $group_id, 'last_activity', gmdate( "Y-m-d H:i:s" ) );
    
    do_action( 'bp_groups_posted_update', $content, $user_id, $group_id, $activity_id );

    return $activity_id;
    
}