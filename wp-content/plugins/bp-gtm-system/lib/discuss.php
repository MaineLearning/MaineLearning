<?php
class BP_GTM_Discussion {

   function get_posts($elem_id, $type){
      global $bp, $wpdb;

      if($type == 'tasks' || $type == 'task') {
         $for_elem = 'WHERE `task_id` = '.$elem_id;
      }elseif($type == 'projects' || $type == 'project') {
         $for_elem = 'WHERE `project_id` = '.$elem_id;
      }

      $posts = $wpdb->get_results($wpdb->prepare("
         SELECT * FROM {$bp->gtm->table_discuss}
         {$for_elem}
         ORDER BY `id` ASC
      "));

      return $posts;
   }

   function get_list($group_id = false, $filter = false, $limit = false){
      global $bp, $wpdb;

      if (is_numeric($group_id)){
         $in_group = ' WHERE d.`group_id` = ' . $group_id;
      }else{
         $in_group = '';
      }

      if ($filter == false || $filter == 'date'){
         $elem_id = '`task_id`';
         $with_filter = ' AND d.`project_id` = 0';
         $table = $bp->gtm->table_tasks;
      }elseif($filter == 'tasks'){
         $elem_id = '`task_id`';
         $with_filter = ' AND d.`project_id` = 0';
         $table = $bp->gtm->table_tasks;
      }elseif($filter == 'projects'){
         $elem_id = '`project_id`';
         $with_filter = ' AND d.`task_id` = 0';
         $table = $bp->gtm->table_projects;
      }

      if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }
      $sql = "SELECT d.{$elem_id} AS `elem_id`, d.`id`, d.`text`, d.`author_id`, d.`group_id`, d.`date_created`, el.`discuss_count`
         FROM {$bp->gtm->table_discuss} AS d, {$table} AS el
         {$in_group}
         {$with_filter}
         AND el.`id` = d.{$elem_id}
         AND d.`author_id` =
            (SELECT `author_id` FROM {$bp->gtm->table_discuss}
             WHERE d.{$elem_id} = {$elem_id}
             ORDER BY `date_created` DESC
             LIMIT 1)
         GROUP BY d.{$elem_id}
         ORDER BY d.`date_created` DESC
         {$with_limit}";

//         print_var($sql);

      $posts = $wpdb->get_results($wpdb->prepare($sql));

      return $posts;
   }

   function get_count($type ='tasks', $group_id = false){
      global $bp, $wpdb;

      if ($type == 'tasks'){
         $select = '`task_id`';
         $where = 'WHERE `project_id` = 0';
      }elseif($type == 'projects'){
         $select = '`project_id`';
         $where = 'WHERE `task_id` = 0';
      }

      if (is_numeric($group_id)){
         $in_group = 'AND `group_id` = '.$group_id;
      }else{
         $in_group = '';
      }

      $sql = "SELECT COUNT(DISTINCT {$select})
         FROM {$bp->gtm->table_discuss}
         {$where}
         {$in_group}";
//         print_var($sql);

      $count = $wpdb->get_var($wpdb->prepare($sql));

      return $count;
   }
}



// Process actions - edit or delete posts for tasks or projects
function bp_gtm_discuss_posts_actions(){
   global $bp, $wpdb;

   if ($_GET['action_type'] == 'edit'){
      $new_text = apply_filters('bp_gtm_discuss_text_content', nl2br($_GET['text']));
      $wpdb->query($wpdb->prepare("
                     UPDATE {$bp->gtm->table_discuss}
                     SET `text` = '{$new_text}'
                     WHERE `id` = {$_GET['post_id']}"));
      echo $new_text;
   }elseif($_GET['action_type'] == 'delete'){
      $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gtm->table_discuss} 
                        WHERE `id` = %d AND `group_id` = %d", $_GET['post_id'], $bp->groups->current_group->id));
      if ($_GET['elem_type'] == 'projects' || $_GET['elem_type'] == 'project'){
         $wpdb->query($wpdb->prepare("
                        UPDATE {$bp->gtm->table_projects}
                        SET `discuss_count` = `discuss_count` - 1
                        WHERE `id` = {$_GET['elem_id']}"));
      }elseif($_GET['elem_type'] == 'tasks' || $_GET['elem_type'] == 'task'){
         $wpdb->query($wpdb->prepare("
                        UPDATE {$bp->gtm->table_tasks}
                        SET `discuss_count` = `discuss_count` - 1
                        WHERE `id` = {$_GET['elem_id']}"));
      }
      echo 'deleted';
   }
}
add_action('wp_ajax_bp_gtm_discuss_posts_actions', 'bp_gtm_discuss_posts_actions');
