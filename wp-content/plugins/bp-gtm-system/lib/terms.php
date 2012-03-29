<?php

class BP_GTM_Taxon {

   function get_all_terms($taxon, $link = false) {
      global $wpdb, $bp;
      if ($link == true) $term_id = ', `id`';
      $data = $wpdb->get_results( $wpdb->prepare( "SELECT `name`$term_id FROM {$bp->gtm->table_terms} WHERE `taxon` = %s", $taxon ) );

      return $data;
   }

   function get_term_name_by_id($term_id) {
      global $wpdb, $bp;

      $data = $wpdb->get_results( $wpdb->prepare( "SELECT `name` FROM {$bp->gtm->table_terms} WHERE `id` = %d", $term_id ) );
      $term_name = $data['0']->name;

      return $term_name;
   }

   function get_term_id_by_name($term_name, $term_type) {
      global $wpdb, $bp;
      
      $data = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM {$bp->gtm->table_terms} WHERE `name` = %s AND taxon='$term_type'", $term_name ) );
      $term_id = $data['0']->id;

      return $term_id;
   }
   

   function search_terms($search_terms, $taxon = 'tag', $limit = 10) {
      global $wpdb, $bp;

      $search_terms = like_escape( $wpdb->escape( $search_terms ) );

      $data = $wpdb->get_results( $wpdb->prepare( "
         SELECT `id`, `name` FROM {$bp->gtm->table_terms}  WHERE `taxon` = %s AND `name` LIKE '%%$search_terms%%' LIMIT %d", $taxon, $limit ) );
      return $data;
   }

   function get_terms_in_group($group_id, $taxon){
      global $wpdb, $bp;
      $terms = array();
//      $sql = "SELECT t.`id`, t.`name`, u.`count`
//            FROM {$bp->gtm->table_terms} AS t, {$bp->gtm->table_taxon} AS u
//            WHERE `group_id` = {$group_id}
//               AND `taxon` = {$taxon}
//               AND u.`count` =
//                  (SELECT COUNT(DISTINCT `id`)
//                   FROM ".$bp->gtm->table_taxon."
//                   WHERE `term_id` = t.`id`
//                      AND `group_id` = {$group_id}
//                  )
//            ORDER BY u.`count` DESC";
//      echo $sql;

      $alldata = $wpdb->get_results($wpdb->prepare("
         SELECT DISTINCT `term_id` FROM {$bp->gtm->table_taxon} 
         WHERE `group_id` = %d AND `taxon` = %s
         ORDER BY `term_id` ASC", $group_id, $taxon));

      if (count($alldata)>0)
         foreach ($alldata as $temp){
            $data[$temp->term_id]['id'] = $temp->term_id;
         }

//      arsort($terms);

      $alldata2 = $wpdb->get_results($wpdb->prepare("
         SELECT `id`, `name` FROM {$bp->gtm->table_terms}
         WHERE `group_id` = %d AND `taxon` = %s
         ORDER BY `id` ASC", $group_id, $taxon));

      foreach ($alldata2 as $temp2){
         $terms[$temp2->id]['name'] = $temp2->name;
         $terms[$temp2->id]['id'] = $temp2->id;
         $terms[$temp2->id]['count'] = bp_gtm_count_term_usage($temp2->id, $group_id);
      }

      return $terms;
   }

   function get_elements_4term($term_id){
      global $bp, $wpdb;
      $tasks = $projects = array();
      $temp_tasks = $wpdb->get_results( $wpdb->prepare( "
         SELECT `task_id` FROM {$bp->gtm->table_taxon}
         WHERE `term_id` = %d AND `group_id` = %d", $term_id, $bp->groups->current_group->id ));

      foreach ($temp_tasks as $temp_task){
         if ($temp_task->task_id != null && $temp_task->task_id !='0' ) {
            $tasks[$temp_task->task_id] = $temp_task->task_id;
            $tasks[$temp_task->task_id] = bp_gtm_get_el_name_by_id($temp_task->task_id, 'task');
         }
      }

      $temp_projects = $wpdb->get_results( $wpdb->prepare( "
         SELECT `project_id` FROM {$bp->gtm->table_taxon}
         WHERE `term_id` = %d AND `group_id` = %d AND `task_id` = 0", $term_id, $bp->groups->current_group->id ));

      foreach ($temp_projects as $temp_project){
         if ($temp_project->project_id != null && $temp_project->project_id !='0') {
            $projects[$temp_project->project_id] = $temp_project->project_id;
            $projects[$temp_project->project_id] = bp_gtm_get_el_name_by_id($temp_project->project_id, 'project');
         }
      }

      $elements['tasks'] = $tasks;
      $elements['projects'] = $projects;

      return $elements;
   }

   function get_terms_4project($group_id, $project_id = false, $taxon = false){
      global $bp, $wpdb;

      // get all terms in this group
      $all_terms = $wpdb->get_results( $wpdb->prepare( "
         SELECT `id`, `name` FROM {$bp->gtm->table_terms}
         WHERE `group_id` = %d AND `taxon` = %s ORDER BY `name`", $group_id, $taxon ));

      // get used terms for this project
      $used_terms = $wpdb->get_results( $wpdb->prepare( "
         SELECT `term_id` FROM {$bp->gtm->table_taxon}
         WHERE `project_id` = %d AND `taxon` = %s", $project_id, $taxon ));

      // combine them to know used among all of them
      foreach ($all_terms as $term) {
         $terms[$term->id]['id'] = $term->id;
         $terms[$term->id]['name'] = $term->name;
         $terms[$term->id]['used'] = 0;
         foreach($used_terms as $used_term){
            if ($term->id == $used_term->term_id)
               $terms[$term->id]['used'] = 1;
         }
      }

      return $terms;
   }

   function get_terms_4task($group_id, $task_id = false, $taxon = false){
      global $bp, $wpdb;

      // get all terms in this group
      $all_terms = $wpdb->get_results( $wpdb->prepare( "
         SELECT `id`, `name` FROM {$bp->gtm->table_terms}
         WHERE `group_id` = %d AND `taxon` = %s ORDER BY `name`", $group_id, $taxon ));

      // get used terms for this project
      $used_terms = $wpdb->get_results( $wpdb->prepare( "
         SELECT `term_id` FROM {$bp->gtm->table_taxon}
         WHERE `task_id` = %d AND `taxon` = %s", $task_id, $taxon ));

      // combine them to know used among all of them
      foreach ($all_terms as $term) {
         $terms[$term->id]['id'] = $term->id;
         $terms[$term->id]['name'] = $term->name;
         $terms[$term->id]['used'] = 0;
         foreach($used_terms as $used_term){
            if ($term->id == $used_term->term_id)
               $terms[$term->id]['used'] = 1;
         }
      }

      return $terms;
   }

}

function bp_gtm_project_terms($project_id, $type = 'tag', $links = false){
   echo bp_gtm_get_project_terms($project_id, $type, $links);
}
   function bp_gtm_get_project_terms($project_id, $type = 'tag', $links = false){
      global $wpdb, $bp;

      if (!$type) $type = 'tag';
      if (!$type == 'tag') $type = 'cat';

      if ($links == true) $term_id = ', `id`';

      $data = $wpdb->get_results( $wpdb->prepare( "SELECT `term_id` FROM {$bp->gtm->table_taxon} WHERE `taxon` = '$type' AND `task_id` = 0 AND `project_id` = %d", $project_id ) );

      if (!$data) {
         if ($type == 'tag') $terms = __('No tags','bp_gtm');
         if ($type == 'cat') $terms = __('No categories','bp_gtm');
      }else{
         if ($type == 'tag') $terms = __('Tags: ','bp_gtm');
         if ($type == 'cat') $terms = __('Category: ','bp_gtm');
         $c = count($data);
         $i = 1;
         foreach ($data as $k) {
            $comma = '';
            if ($i<$c) $comma = ', ';
            $terms .= bp_gtm_get_term_name_by_id( $k->term_id, $links) . $comma;
            $i++;
         }
      }

      return $terms;
   }

function bp_gtm_task_terms($task_id, $type = 'tag', $links = false){
   echo bp_gtm_get_task_terms($task_id, $type, $links);
}
   function bp_gtm_get_task_terms($task_id, $type = 'tag', $links = false){
      global $wpdb, $bp;

      if (!$type) $type = 'tag';
      if (!$type == 'tag') $type = 'cat';

      if ($links == true) $term_id = ', `id`';

      $data = $wpdb->get_results( $wpdb->prepare( "SELECT `term_id` FROM {$bp->gtm->table_taxon} WHERE `taxon` = '$type' AND `task_id` = %d", $task_id ) );

      if (!$data) {
         if ($type == 'tag') $terms = __('No tags','bp_gtm');
         if ($type == 'cat') $terms = __('No categories','bp_gtm');
      }else{
         if ($type == 'tag') $terms = __('Tags: ','bp_gtm');
         if ($type == 'cat') $terms = __('Category: ','bp_gtm');
         $c = count($data);
         $i = 1;
         foreach ($data as $k) {
            $comma = '';
            if ($i<$c) $comma = ', ';
            $terms .= bp_gtm_get_term_name_by_id( $k->term_id, $links) . $comma;
            $i++; 
         }
      }

      return $terms;
   }

function bp_gtm_term_name_by_id ($term_id, $link = false) {
   echo bp_gtm_get_term_name_by_id($term_id, $link);
}
   function bp_gtm_get_term_name_by_id($term_id, $link = false, $count = false) {
      global $bp;

      $term_name = BP_GTM_Taxon::get_term_name_by_id($term_id);

      if($link == true && $count == true) {
         $pre_term = '<a href="'.bp_get_group_permalink(). $bp->gtm->slug . '/terms/view/'.$term_id.'" title="'.sprintf(_n('%d element', '%d elements', bp_gtm_count_term_usage($term_id, $bp->groups->current_group->id), 'bp_gtm'), bp_gtm_count_term_usage($term_id, $bp->groups->current_group->id)).'">';
         $post_term = '</a>';
      }elseif($link == false && $count == true){
         $pre_term = '<span title="'.bp_gtm_count_term_usage($term_id, $bp->groups->current_group->id).'"';
         $post_term = '</span>';
      }elseif($link == true && $count == false){
         $pre_term = '<a href="'.bp_get_group_permalink(). $bp->gtm->slug . '/terms/view/'.$term_id.'" title="'.__('Tasks and projects with this term','bp_gtm').'">';
         $post_term = '</a>';
      }elseif($link == false && $count == false){
         $pre_term = '';
         $post_term = '';
      }

      $term = $pre_term . $term_name . $post_term;
      return $term;
   }

function bp_gtm_count_term_usage($term_id, $group_id = false){
   global $bp, $wpdb;
   $in_group = '';

   if ($group_id != false && is_numeric($group_id)) {
      $in_group = "AND `group_id` = $group_id";
   }

   $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT `id`)
      FROM ".$bp->gtm->table_taxon." WHERE `term_id` = %d $in_group", $term_id ));

   return $count;
}

?>
