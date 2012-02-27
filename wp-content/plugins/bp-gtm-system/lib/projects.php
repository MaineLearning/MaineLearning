<?php
class BP_GTM_Projects {
   // for vars and consts

   static function get_all($group_id = false) {
      global $wpdb, $bp;
      if ($group_id == false){
         $in_group = '';
      }elseif(is_numeric($group_id)){
         $in_group = ' WHERE `group_id` = ' . $group_id;
      }
      $projects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_projects . $in_group ) );
      return $projects;
   }

   static function get_done($done, $group_id = false) {
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

      $projects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_projects . " WHERE `done` = ". $done . $in_group ) );

      return $projects;
   }

   static function get_alpha($group_id = false) {
      global $wpdb, $bp;

      if ($group_id == false){
         $in_group = "";
      }elseif(is_numeric($group_id)){
         $in_group = " WHERE `group_id` = '" . $group_id . "' AND `done` = 0";
      }

      $projects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_projects . $in_group . " ORDER BY `name` ASC" ) );

      return $projects;
   }

   static function get_deadline($group_id = false) {
      global $wpdb, $bp;

      if ($group_id == false){
         $in_group = "";
      }elseif(is_numeric($group_id)){
         $in_group = " WHERE `group_id` = '" . $group_id . "' AND `done` = 0";
      }

      $projects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_projects . $in_group . " ORDER BY `deadline` ASC" ) );

      return $projects;
   }

   static function get_project_by_id($project_id) {
      global $wpdb, $bp;
      $project = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $bp->gtm->table_projects . " WHERE `id` = %d", $project_id ) );
      return $project;
   }

   static function get_count_tasks($project_id, $group_id = false){
      global $bp, $wpdb;

      if ($group_id == false){
         $in_group = '';
      }elseif(is_numeric($group_id)){
         $in_group = ' AND `group_id` = ' . $group_id;
      }

      $count['all'] = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `project_id` = ". $project_id . $in_group) );;
      $count['done'] = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(DISTINCT `id`) FROM " . $bp->gtm->table_tasks . " WHERE `done` = 1 AND `project_id` = ". $project_id . $in_group) );
      $count['undone'] = $count['all'] - $count['done'];

      return $count;
   }
}

// Filter projects by type: done | undone | alpha | group
function bp_gtm_get_projects($group_id = false, $filter = false) {

   if ( $filter == 'done' || $filter == 'undone' ) {
      $projects = BP_GTM_Projects::get_done($filter, $group_id);
   }elseif($filter == 'alpha'){
      $projects = BP_GTM_Projects::get_alpha($group_id);
   }elseif($filter == 'deadline'){
      $projects = BP_GTM_Projects::get_deadline($group_id);
   }elseif($filter == 'group'){
      $projects = BP_GTM_Projects::get_all($group_id);
   }

   return $projects;
}

function bp_gtm_project_check_date($project_id, $time){
   global $wpdb, $bp;

   $before_deadline = strtotime($time) - (60 * 60 * 24); // in sec - 24 hours before deadline
   $now = strtotime("now");
//   $deadline = strtotime($time);

   if ( $now > $before_deadline ) {
      $class = 'sticky';
   }else{
      $class = 'open';
   }
   if (!empty($_GET['filter']) && $_GET['filter'] == 'done') $class = 'open';

   echo $class;
}

// Rewrite it as bp_gtm_get_project_by_task_id
function bp_gtm_get_project_status($project_id){
   global $bp, $wpdb;

   $data = $wpdb->get_results( $wpdb->prepare( "SELECT `status` FROM {$bp->gtm->table_projects} WHERE `id` = {$project_id}"));
   $status = $data['0']->status;

   return $status;
}

function bp_gtm_get_project_by_task_id($task_id, $data = 'all'){
   global $bp, $wpdb;

   if ($data == 'all'){
      $project = $wpdb->get_results($wpdb->prepare("
         SELECT * FROM {$bp->gtm->table_projects}
         WHERE `id` IN
            (SELECT `project_id` AS `id` FROM {$bp->gtm->table_tasks}
            WHERE `id` = {$task_id})"));
   }else{
      $project = $wpdb->get_results($wpdb->prepare("
         SELECT `id`, {$data} FROM {$bp->gtm->table_projects}
         WHERE `id` IN
            (SELECT `project_id` AS `id` FROM {$bp->gtm->table_tasks}
            WHERE `id` = {$task_id})"));
   }

   return $project;
}