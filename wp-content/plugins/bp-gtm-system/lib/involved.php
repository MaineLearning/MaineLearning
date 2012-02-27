<?php
class BP_GTM_Resps{

   function get_all($group_id){
      global $bp, $wpdb;

      $actions = ' , '. implode(', ', array_keys(bp_gtm_get_actions('involved', 'all')));
      
      $sql = "SELECT r.`resp_id` AS `id`, `role_id`, `role_name`, COUNT(DISTINCT r.`task_id`) AS `tasks`$actions
                FROM {$bp->gtm->table_resps} AS r
                LEFT JOIN {$bp->gtm->table_tasks} ON {$bp->gtm->table_tasks}.`group_id` = r.group_id
                LEFT JOIN {$bp->gtm->table_roles_caps} ON {$bp->gtm->table_roles_caps}.group_id = r.group_id
                LEFT JOIN {$bp->gtm->table_roles} ON {$bp->gtm->table_roles}.id = {$bp->gtm->table_roles_caps}.role_id
                WHERE ({$bp->gtm->table_tasks}.group_id = $group_id AND {$bp->gtm->table_tasks}.`done` = 0)
                GROUP BY r.`resp_id`
                UNION
                SELECT r.`resp_id` AS `id`, `role_id`, `role_name`, COUNT(DISTINCT r.`task_id`) AS `tasks`$actions
                FROM {$bp->gtm->table_resps} AS r
                LEFT JOIN {$bp->gtm->table_projects} ON {$bp->gtm->table_projects}.`group_id` = r.group_id 
                LEFT JOIN {$bp->gtm->table_roles_caps} ON {$bp->gtm->table_roles_caps}.group_id = {$bp->gtm->table_projects}.`group_id`
                LEFT JOIN {$bp->gtm->table_roles} ON {$bp->gtm->table_roles}.id = {$bp->gtm->table_roles_caps}.role_id
                WHERE ({$bp->gtm->table_projects}.group_id = $group_id AND {$bp->gtm->table_projects}.`done` = 0) AND r.`task_id`=0
                GROUP BY r.`resp_id`";
      $resps = $wpdb->get_results($wpdb->prepare($sql));
      

      return $resps;
   }

   function get_el_count($resp_id, $type, $group_id = false){
      global $bp, $wpdb;

      if ($type == 'tasks'){
         $select = '`task_id`';
         $data = 'AND `task_id` IN (SELECT `id` AS `task_id` FROM '.$bp->gtm->table_tasks.' WHERE `done` = 0)';
      }elseif ($type == 'projects'){
         $select = '`project_id`';
         $data = 'AND `task_id` = 0 AND `project_id` IN (SELECT `id` AS `project_id` FROM '.$bp->gtm->table_projects.' WHERE `done` = 0)';
      }

      if (is_numeric($group_id)) {
         $in_group = 'AND `group_id` = '.$group_id;
      }

      $count = $wpdb->get_var($wpdb->prepare("
         SELECT COUNT(DISTINCT $select) FROM {$bp->gtm->table_resps}
         WHERE `resp_id` = %d $data $in_group
         ", $resp_id));

      return $count;
   }
   
}

function bp_gtm_email_notify(){
   global $bp;

   // Get user data
   $reciever_name = bp_core_get_user_displayname( $_GET['resp_id'], false );
   $reciever_ud = get_userdata( $_GET['resp_id'] );
   $sender_name = bp_core_get_user_displayname( $bp->loggedin_user->id, false );
   $sender_link = bp_core_get_userlink($bp->loggedin_user->id, false, true);

   // Lunks to use in email
   $reciever_personal_tasks_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/'.$bp->gtm->slug.'/tasks' );
   $reciever_personal_projects_link = site_url( BP_MEMBERS_SLUG . '/' . $reciever_ud->user_login . '/'.$bp->gtm->slug.'/projects' );

	/* Set up and send the message */
	$to = $reciever_ud->user_email;
   $subject = get_blog_option( 1, 'blogname' ) . ': '.__('Assignments');
	$message = sprintf( __(
'Hello there %s,

%s (%s) reminds you, that pending tasks and projects you are responsible for need to be done.

To see your personal tasks click here: %s

To see your personal projects click here: %s

---------------------
%s. %s
', 'bp_gtm' ), $reciever_name, $sender_name, $sender_link, $reciever_personal_tasks_link, $reciever_personal_projects_link, get_blog_option( 1, 'blogname'), get_blog_option( 1, 'blogdescription') );

	// Send it!
	wp_mail( $to, $subject, $message );
   echo 'sent!';
}
add_action('wp_ajax_bp_gtm_email_notify','bp_gtm_email_notify');


/*
 * Template functions
 */



?>
