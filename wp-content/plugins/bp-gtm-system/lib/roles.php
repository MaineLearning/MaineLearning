<?php
// old code. left for history
function bp_gtm_get_access($user_id = false, $group_id = false, $action = false){
    global $wpdb, $bp;

    if (!$user_id) $user_id = $bp->loggedin_user->id;
    if(!$bp->groups->current_group->id)
        return false;
    if (!$group_id) $group_id = $bp->groups->current_group->id;

    if (groups_is_user_admin( $bp->loggedin_user->id, $bp->groups->current_group->id ) || is_super_admin()  || is_network_admin())
        return true;

    $sql = "SELECT `{$bp->gtm->table_roles}`. *
                    FROM `{$bp->gtm->table_roles}`
                    INNER JOIN `{$bp->gtm->table_roles_caps}` ON {$bp->gtm->table_roles_caps}.`role_id` = `{$bp->gtm->table_roles}`.`id`
                    WHERE `{$bp->gtm->table_roles_caps}`.`user_id` = {$user_id}
                        AND `{$bp->gtm->table_roles_caps}`.`group_id` = {$group_id}
                        AND (
                            `{$bp->gtm->table_roles}`.`group_id` = {$group_id}
                            OR `{$bp->gtm->table_roles}`.`group_id` = '0'
                        )
                    LIMIT 1";

    $result = $wpdb->get_results($wpdb->prepare($sql));

    if (empty ($result))
        return false;

    if ($result[0]->$action == 1)
        return true;

    return false;
}

// Get actions and their names
function bp_gtm_get_actions($type = 'all', $slug = 'all'){
    $actions = array();

    $actions['project']['project_view'] = __('View Project', 'bp_gtm');
    $actions['project']['project_create'] = __('Create Project', 'bp_gtm');
    $actions['project']['project_edit'] = __('Edit Project', 'bp_gtm');
    $actions['project']['project_delete'] = __('Delete Project', 'bp_gtm');
    $actions['project']['project_done'] = __('Mark Project as Done', 'bp_gtm');
    $actions['project']['project_undone'] = __('Mark Project as Undone', 'bp_gtm');
    $actions['project']['project_comment'] = __('Comment Project', 'bp_gtm');
    $actions['project']['project_up_files'] = __('Upload Files to Project', 'bp_gtm');
    $actions['project']['project_del_files'] = __('Delete Files from Project', 'bp_gtm');

    $actions['task']['task_view'] = __('View Task', 'bp_gtm');
    $actions['task']['task_create'] = __('Create Task', 'bp_gtm');
    $actions['task']['task_edit'] = __('Edit Task', 'bp_gtm');
    $actions['task']['task_delete'] = __('Delete Task', 'bp_gtm');
    $actions['task']['task_done'] = __('Mark Task as Done', 'bp_gtm');
    $actions['task']['task_undone'] = __('Mark Task as Undone', 'bp_gtm');
    $actions['task']['task_comment'] = __('Comment Task', 'bp_gtm');
    $actions['task']['task_up_files'] = __('Upload Files to Task', 'bp_gtm');
    $actions['task']['task_del_files'] = __('Delete Files from Task', 'bp_gtm');

    $actions['taxon']['taxon_view'] = __('View Terms', 'bp_gtm');
    $actions['taxon']['taxon_create'] = __('Create Terms', 'bp_gtm');
    $actions['taxon']['taxon_edit'] = __('Edit Terms', 'bp_gtm');
    $actions['taxon']['taxon_delete'] = __('Delete Terms', 'bp_gtm');

    $actions['involved']['involved_view'] = __('View Involved', 'bp_gtm');
    $actions['involved']['involved_pm'] = __('Action: Send a PM to Involved', 'bp_gtm');
    $actions['involved']['involved_mention'] = __('Action: Mention Involved', 'bp_gtm');
    $actions['involved']['involved_email'] = __('Action: Send an Email to Involved', 'bp_gtm');
    $actions['involved']['involved_roles'] = __('Change User Roles', 'bp_gtm');

    $actions['discuss']['discuss_view'] = __('View Discussions', 'bp_gtm');
    $actions['discuss']['discuss_create'] = __('Create Discussion Posts', 'bp_gtm');
    $actions['discuss']['discuss_edit'] = __('Edit Any Discussion Posts', 'bp_gtm');
    $actions['discuss']['discuss_delete'] = __('Delete Any Discussion Posts', 'bp_gtm');
    $actions['discuss']['discuss_up_files'] = __('Upload files to Discussion Posts', 'bp_gtm');
    $actions['discuss']['discuss_del_files'] = __('Delete files from Discussion Posts', 'bp_gtm');

    $actions['settings']['settings_view'] = __('View All Settings', 'bp_gtm');
    $actions['settings']['settings_edit_base'] = __('Edit Base Settings', 'bp_gtm');
    $actions['settings']['settings_edit_roles'] = __('Edit Group Roles', 'bp_gtm');

    $actions['delete']['delete_all'] = __('Delete all GTM group data', 'bp_gtm');

    if($type == 'all'){
        return $actions;
    }else{
        if($slug != 'all')
            return $actions[$type][$slug];
        else
            return $actions[$type];
    }
}

function bp_gtm_get_user_roles(){
    global $bp, $wpdb;

    if(empty($bp->groups->current_group->id))
            return null;

    $sql = "SELECT `{$bp->gtm->table_roles}`. *
                    FROM `{$bp->gtm->table_roles_caps}`
                    INNER JOIN `{$bp->gtm->table_roles}` ON {$bp->gtm->table_roles}.`id` = `{$bp->gtm->table_roles_caps}`.`role_id`
                    WHERE `{$bp->gtm->table_roles_caps}`.`user_id` = {$bp->loggedin_user->id}
                        AND `{$bp->gtm->table_roles_caps}`.`group_id` = {$bp->groups->current_group->id}
                        AND (`{$bp->gtm->table_roles}`.`group_id` = {$bp->groups->current_group->id}
                                OR `{$bp->gtm->table_roles}`.`group_id` = '0'
                                )
                    LIMIT 1";

    $result = $wpdb->get_results($wpdb->prepare($sql));

    if (!empty ($result)){
        $result[0]->group_id = $bp->groups->current_group->id;
        return $result[0];
    }

    return null;
}

// Does this user in this group have access to that functionality?
function bp_gtm_check_access($action = false){
    global $bp;
    if ((!empty($bp->loggedin_user->gtm_access->$action) && $bp->loggedin_user->gtm_access->$action == 1) || is_super_admin() || is_network_admin()){
        return true;
    }
    return false;
}

// Save role actions
function bp_gtm_save_roles_actions($data){
    global $wpdb, $bp;
    if(!empty($data)){
        $role_matrix = array(
            'project_view','project_create','project_edit','project_delete','project_done','project_undone','project_comment','project_up_files','project_del_files',
            'task_view','task_create','task_edit','task_delete','task_done','task_undone','task_comment','task_up_files','task_del_files',
            'taxon_view','taxon_create','taxon_edit','taxon_delete',
            'involved_view','involved_pm','involved_mention','involved_email','involved_roles',
            'discuss_view','discuss_create','discuss_edit','discuss_delete','discuss_up_files','discuss_del_files',
            'settings_view','settings_edit_base','settings_edit_roles',
            'delete_all'
        );
        $role_defaults = array_fill_keys($role_matrix, '0');

        foreach($data as $role_id => $role_values)
            $roles[$role_id] = array_merge($role_defaults,$role_values);

        foreach($roles as $role_id => $role_values){
            $temp = '';
            foreach($role_values as $role_action => $role_value){
                if($role_action == 'role_id') continue;
                $temp .= "`$role_action` = '$role_value',";
            }
            // create queries for each role
            $sql[$role_id] .= 'UPDATE '.$bp->gtm->table_roles;
            $sql[$role_id] .= ' SET '.substr($temp,0,-1);
            $sql[$role_id] .= ' WHERE `group_id` = 0 AND `id` = '.$role_id;
        }

        // process queries
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function bp_gtm_get_role_for_user($user_id = false){
    global $wpdb, $bp, $members_template;

    if(!$user_id)
        $user_id = $members_template->member->user_id;

    $roles = $wpdb->get_results("
            SELECT *
            FROM `{$bp->gtm->table_roles_caps}`
            INNER JOIN `{$bp->gtm->table_roles}` ON {$bp->gtm->table_roles}.`id` = `{$bp->gtm->table_roles_caps}`.`role_id`
            WHERE `{$bp->gtm->table_roles_caps}`.`user_id` = {$user_id}
            AND `{$bp->gtm->table_roles_caps}`.`group_id` =  {$bp->groups->current_group->id}
        ");

    if(!empty($roles))
        return $roles[0];
    else
        return false;
}

// Assign user to default group role
add_action('groups_join_group', 'bp_gtm_def_add_as_guest', 10, 2);
function bp_gtm_def_add_as_guest($group_id, $user_id){
    global $wpdb, $bp;
    $bp_gtm = get_option('bp_gtm');

    $wpdb->query($wpdb->prepare("
            INSERT INTO {$bp->gtm->table_roles_caps}
            (`user_id`, `group_id`, `role_id`)
            VALUES ($user_id, $group_id, {$bp_gtm['def_g_role']})"));

    return true;
}


function bp_gtm_save_admin_role($group){
    global $wpdb, $bp;
    $bp_gtm = get_option('bp_gtm');

    $wpdb->query($wpdb->prepare("
            INSERT INTO {$bp->gtm->table_roles_caps}
            (`user_id`, `group_id`, `role_id`)
            VALUES ($group->creator_id, $group->id, {$bp_gtm['def_admin_g_role']})"));

    return true;
}

add_action('groups_group_before_save', 'bp_gtm_def_add_admin');
function bp_gtm_def_add_admin($group){
    if(!$group->id)
            add_action('groups_group_after_save', 'bp_gtm_save_admin_role');
}


// Delete roles in admin area
function bp_gtm_delete_def_roles(){
   global $bp, $wpdb;

   $deleted = $wpdb->query("DELETE FROM {$bp->gtm->table_roles} WHERE `id` = {$_GET['deleteID']}");

   echo $deleted;
   die;
}
add_action( 'wp_ajax_bp_gtm_delete_def_roles', 'bp_gtm_delete_def_roles' );



function bp_gtm_add_def_role(){
    global $bp, $wpdb;
    $role_name = apply_filters('bp_gtm_role_name', $_GET['role_name']);
    if(trim($role_name) == '')
        die('<div class="error"><p>'.__('Some error occured while creating a default role','bp_gtm').'</p></div>');
    $result = $wpdb->query($wpdb->prepare("INSERT INTO {$bp->gtm->table_roles} (`group_id`,`role_name`) VALUES ('0', '$role_name')"));
    if($result){
        $role->id = $wpdb->insert_id;
        $role->role_name = $role_name;
        bp_gtm_role_actions($role);
    }else{
        echo '<div class="error"><p>'.__('Some error occured while creating a default role','bp_gtm').'</p></div>';
    }
    die;
}
add_action( 'wp_ajax_bp_gtm_add_def_role', 'bp_gtm_add_def_role' );

function bp_gtm_roles_list(){
    global $bp, $wpdb;

    $roles = $wpdb->get_results("
            SELECT `id`, `role_name`
            FROM `{$bp->gtm->table_roles}`
            WHERE `group_id` = '0'
        ");

    return $roles;
}

function bp_gtm_change_user_role($user_id, $role_id, $group_id, $importer = false){
    global $bp, $wpdb;

    $exist = $wpdb->query($wpdb->prepare("
            SELECT id
            FROM {$bp->gtm->table_roles_caps}
            WHERE `group_id` = $group_id AND `user_id` = $user_id"));

     if($exist && $importer){
         //$sql;
     }elseif(!$exist && $importer){
         $sql;
     }

     if(!$importer){
         if($exist){
            $result = $wpdb->query($wpdb->prepare("
                UPDATE {$bp->gtm->table_roles_caps}
                SET `role_id` = $role_id
                WHERE `user_id` = $user_id
                AND `group_id` = $group_id"));
         }else{
            $result = $wpdb->query($wpdb->prepare("
                INSERT INTO {$bp->gtm->table_roles_caps}
                (`user_id`,`group_id`, `role_id`)
                VALUES ($user_id, $group_id, $role_id)"));
         }
    }else{
        if(!$exist){
            $result = $wpdb->query($wpdb->prepare("
                INSERT INTO {$bp->gtm->table_roles_caps}
                (`user_id`,`group_id`, `role_id`)
                VALUES ($user_id, $group_id, $role_id)"));
        }
    }

    if ($result)
        return true;

    return false;
}

