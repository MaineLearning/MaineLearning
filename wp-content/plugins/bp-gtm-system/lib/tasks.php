<?php

class BP_GTM_Tasks {

   static function get_all($group_id = false, $limit = false) {
      global $wpdb, $bp;

      if ($group_id == false){
         $in_group = '';
      }elseif(is_numeric($group_id)){
         $in_group = ' WHERE `group_id` = ' . $group_id;
      }

      if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }

      $no_parent = ' AND `parent_id` = 0';

      $tasks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_tasks . $in_group . $no_parent . $with_limit ) );

      return $tasks;
   }

   static function get_tasks_in_project($project_id, $done = false, $limit = false){
      global $bp, $wpdb;

      if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }

      if ($done == false) {
         $status = 'AND `done` = 0';
      }elseif($done == true) {
         $status = 'AND `done` = 1';
      }

      $no_parent = ' AND `parent_id` = 0';

      $tasks = $wpdb->get_results( $wpdb->prepare( "
         SELECT * FROM {$bp->gtm->table_tasks}
         WHERE `project_id` = {$project_id} {$status}
            {$no_parent}
         ORDER BY `deadline` ASC" . $with_limit ) );

      return $tasks;
   }

   static function get_done($done, $group_id = false, $limit = false) {
      global $wpdb, $bp;

      if ($group_id == false){
         $in_group = "";
      }elseif(is_numeric($group_id)){
         $in_group = " AND `group_id` = '" . $group_id . "'";
      }

      if ($done == 'done'){
         $done = '1';
      }elseif($done == 'undone'){
         $done = '0';
      }

      if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }

      $no_parent = ' AND `parent_id` = 0';

      $tasks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_tasks . " WHERE `done` = ". $done . $in_group . $no_parent . $with_limit ) );

      return $tasks;
   }

   static function get_alpha($group_id = false, $limit = false) {
      global $wpdb, $bp;

      if ($group_id == false){
         $in_group = "";
      }elseif(is_numeric($group_id)){
         $in_group = " WHERE `group_id` = '" . $group_id . "' AND `done` = 0";
      }

      if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }

      $no_parent = ' AND `parent_id` = 0';

      $tasks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_tasks . $in_group . $no_parent . " ORDER BY `name` ASC" . $with_limit ) );

      return $tasks;
   }

   static function get_deadline($group_id = false, $limit = false) {
      global $wpdb, $bp;

      if ($group_id == false){
         $in_group = "";
      }elseif(is_numeric($group_id)){
         $in_group = " WHERE `group_id` = '" . $group_id . "' AND `done` = 0";
      }

      if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }

      $no_parent = ' AND `parent_id` = 0';

      $tasks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_tasks . $in_group . $no_parent . " ORDER BY `deadline` ASC" . $with_limit ) );

      return $tasks;
   }

   static function get_task_by_id($task_id) {
      global $wpdb, $bp;
      $task = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_tasks . " WHERE `id` = %d", $task_id ) );
      return $task;
   }

   static function get_count($filter, $project_id = false, $group_id = false){
      global $bp, $wpdb;

      if ($group_id == false){
         $in_group = '';
      }elseif(is_numeric($group_id)){
         $in_group = ' AND `group_id` = ' . $group_id;
      }

      if (is_numeric($filter)) {
         $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `parent_id` = 0 AND `done` = 0 AND `project_id` = ". $filter . $in_group) );
      }elseif(!$filter || $filter == 'undone' || $filter == 'alpha' || $filter == 'deadline') {
         $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `parent_id` = 0 AND `done` = 0" . $in_group ) );
      }elseif($filter == 'done' && is_numeric($project_id)) {
         $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `parent_id` = 0 AND `done` = 1 AND `project_id` = '" . $in_group, $project_id ) );
      }elseif($filter == 'done' && $project_id == false) {
         $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `parent_id` = 0 AND `done` = 1" . $in_group ) );
      }elseif($filter == 'without' && $project_id == false) {
         $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `project_id` = 0 AND `done` = 0" . $in_group ) );
      }
      return $count;
   }
   
   static function get_task_whithout_project($group_id = false, $limit){
       global $bp, $wpdb;
       if (count($limit) == 2) {
         $with_limit = ' LIMIT '.$limit['miss'].' , '.$limit['per_page'];
      }else{
         $with_limit = '';
      }
      $tasks = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `{$bp->gtm->table_tasks}` WHERE `done` = 0 AND `project_id` = 0 AND `group_id`=$group_id". $with_limit));
      return $tasks; 
      
   }
}

// Filter tasks by type: done | undone | alpha | deadline | group | project
function bp_gtm_get_tasks($group_id = false, $filter = false, $project_id = false, $limit = false) {
    global $bp;
    if (!$group_id)
        $group_id = $bp->groups->current_group->id;

   if (( $filter == 'done' || $filter == 'undone' ) && is_numeric($group_id) && $project_id == false) {
      $tasks = BP_GTM_Tasks::get_done($filter, $group_id, $limit);
   }elseif($filter == 'done' && is_numeric($project_id)) {
      $tasks = BP_GTM_Tasks::get_tasks_in_project($project_id, $done = true, $limit);
   }elseif($filter == 'alpha' && is_numeric($group_id)){
      $tasks = BP_GTM_Tasks::get_alpha($group_id, $limit);
   }elseif($filter == 'deadline' && is_numeric($group_id)){
      $tasks = BP_GTM_Tasks::get_deadline($group_id, $limit);
   }elseif($filter == 'ingroup' && is_numeric($group_id)){
      $tasks = BP_GTM_Tasks::get_all($group_id, $limit);
   }elseif($filter == 'inproject' && is_numeric($project_id)){
      $tasks = BP_GTM_Tasks::get_tasks_in_project($project_id, $done = false, $limit);
   }elseif($filter == 'without'){
      $tasks = BP_GTM_Tasks::get_task_whithout_project($group_id, $limit);
   }else{
      $tasks = BP_GTM_Tasks::get_all($group_id, $limit);
   }

   return $tasks;
}

function bp_gtm_task_check_date($task_id, $time){

   $before_deadline = strtotime($time) - (60 * 60 * 24); // in sec - 24 hours before deadline
   $now = strtotime("now");
//   $deadline = strtotime($time);

   if ( $now > $before_deadline ) {
      $class = 'sticky';
   }else{
      $class = 'open';
   }
   if ($_GET['filter'] == 'done') $class = 'open';

   echo $class;
}

// Tasks pagination func
function bp_gtm_tasks_navi($filter, $project_id, $per_page = 20, $all = false){
   global $bp, $wpdb;

   $cur = 1;
   if ($filter == 'done' && is_numeric($project_id)){
      $tasks = BP_GTM_Tasks::get_count($filter, $project_id, $bp->groups->current_group->id);
   }else{
      $tasks = BP_GTM_Tasks::get_count($filter, false, $bp->groups->current_group->id);
   }
   $pages = ceil($tasks / $per_page);

   if ($pages > 1) {
      echo '<p class="navi" id="'.$tasks.'">';
      echo sprintf(__('<span id="cur_tasks">%d - %d</span> out of <span class="counter">%d</span>.&nbsp;', 'bp_gtm'), $cur, $per_page, $tasks);
      _e('Pagination:', 'bp_gtm');
      for ($i = 0; $i < $pages; $i++) {
         $c = $i+1;
         $cur = $cur - 1;
         if ($cur != $i) {
            echo '&nbsp;<a id="'.$i.'-'.$per_page.'" class="task_navi" href="#">'.$c.'</a>';
         }else{
            echo '&nbsp;<a id="'.$i.'-'.$per_page.'" class="task_navi current" href="#">'.$c.'</a>';
         }
      }
      echo '</p>';
   }else{
      echo sprintf(_n('There is only %s task in this group for now.', '%s tasks in this group. Use links in the right to filter them.', $tasks, 'bp_gtm'), $tasks);
   }

}



// Complete and Delete tasks action
add_action( 'wp_ajax_bp_gtm_do_task_actions', 'bp_gtm_do_task_actions' );
function bp_gtm_do_task_actions(){
   global $bp, $wpdb;

   if ($_GET['action_type'] == 'complete'){
      $wpdb->query($wpdb->prepare("
         UPDATE {$bp->gtm->table_tasks}
         SET `done` = 1
         WHERE `id` = {$_GET['task_id']} OR `parent_id` = {$_GET['task_id']}"));
      $task = BP_GTM_Tasks::get_task_by_id($_GET['task_id']);
      $resps = explode( ' ', $task['0']->resp_id);
      foreach ($resps as $resp) {
         if ($resp != '') {
            $resp_id = bp_core_get_userid($resp);
            if ($resp_id != $bp->loggedin_user->id)
               bp_core_add_notification( $_GET['task_id'], $resp_id, $bp->gtm->slug, 'task_done', $bp->groups->current_group->id);
         }
      }
      echo 'completed';
   }elseif($_GET['action_type'] == 'delete'){
      $wpdb->query( $wpdb->prepare("DELETE FROM {$bp->gtm->table_tasks} WHERE `id` = %d AND `group_id` = %d", $_GET['task_id'], $bp->groups->current_group->id));
      $wpdb->query( $wpdb->prepare("DELETE FROM {$bp->gtm->table_taxon} WHERE `task_id` = %d AND `group_id` = %d", $_GET['task_id'], $bp->groups->current_group->id));
      $wpdb->query( $wpdb->prepare("DELETE FROM {$bp->gtm->table_resps} WHERE `task_id` = %d AND `group_id` = %d", $_GET['task_id'], $bp->groups->current_group->id));
      $wpdb->query( $wpdb->prepare("DELETE FROM {$bp->gtm->table_discuss} WHERE `task_id` = %d AND `group_id` = %d", $_GET['task_id'], $bp->groups->current_group->id));
      $wpdb->query( $wpdb->prepare("DELETE FROM {$bp->core->table_name_notifications} WHERE `item_id` = %d AND `secondary_item_id` = %d AND `component_name` = {$bp->gtm->slug} AND `component_action` LIKE `task_%%`", $_GET['task_id'], $bp->groups->current_group->id));
      $task = BP_GTM_Tasks::get_task_by_id($_GET['task_id']);
      $resps = explode(' ', $task['0']->resp_id);
      foreach ($resps as $resp) {
         if ($resp != '') {
            $resp_id = bp_core_get_userid($resp);
            if ($resp_id != $bp->loggedin_user->id)
               bp_core_add_notification($_GET['task_id'], $resp_id, $bp->gtm->slug, 'task_deleted', $bp->groups->current_group->id);
         }
      }
      echo 'deleted';
   }
   die;
}

//Subtasks data
add_action('wp_ajax_bp_gtm_get_subtasks', 'bp_gtm_get_subtasks');
function bp_gtm_get_subtasks(){
    global $bp, $wpdb;
    $subtasks = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$bp->gtm->table_tasks}
        WHERE `parent_id` = {$_GET['parent_id']}"));

    if (count($subtasks) > 0) {
        echo '<ul class="subtasks">';
        $gtm_link = bp_get_group_permalink(groups_get_group((array('group_id' => $bp->groups->current_group->id))));
        foreach ((array) $subtasks as $subtask) {
            if ($subtask->done == '1') 
                $done_status = '<span id="task_status"> &rarr; '.__('completed','bp_gtm').'</span>';
            else
                $done_status = '';
            echo '<li><a href="' . $gtm_link. $bp->gtm->slug . '/tasks/view/' . $subtask->id . '">' . $subtask->name . '</a>' . $done_status . '</li>';
        }
        echo '</ul>';
    }
    die;
}
// Subtasks count
//add_action( 'wp_ajax_bp_gtm_get_subtasks_count', 'bp_gtm_get_subtasks_count' );
function bp_gtm_get_subtasks_count($task_id = false) {
    global $bp, $wpdb;

//   $task_id = $_GET['parent_id'];

    $subtasks = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM {$bp->gtm->table_tasks} WHERE `parent_id` = {$task_id} AND `done` = 0"));

    echo $subtasks;
}
