<?php
// All filters

function bp_gtm_filter_short_links($content){
   global $bp;

   $gtm_link = bp_get_group_permalink(). $bp->gtm->slug . '/';
   $i = $j = 0;
   if (preg_match_all('~([#]+([TP])(\d+))~', $content, $data, PREG_SET_ORDER)) {
       $content = strip_tags($content, '<p>'); // prevent duplicate <a> tags in comments
       $content = nl2br($content);
      //print_var($data);
      foreach((array)$data as $match){
         if ($match[2] == 'T'){
            $name = bp_gtm_get_el_name_by_id($match[3], 'task');
            if(!$name) {
               $name = __('doesn\'t exist','bp_gtm');
               $color = 'color-red';
            }
            if(!$i)/// prevent duplicate <a> tags in comments
            $content = str_replace($match[1], '<a class="'.$color.'" href="'.$gtm_link.'tasks/view/'.$match[3].'" title="'.__('Task:','bp_gtm').' '.$name.'">'.$match[1].'</a>', $content);
            $i++;
       }elseif($match[2] == 'P'){
            $name = bp_gtm_get_el_name_by_id($match[3], 'project');
            if(!$name) {
               $name = __('doesn\'t exist','bp_gtm');
               $color = 'color-red';
            }
            if(!$j)/// prevent duplicate <a> tags in comments
            $content = str_replace($match[1], '<a class="'.$color.'" href="'.$gtm_link.'projects/view/'.$match[3].'" title="'.__('Project:','bp_gtm').' '.$name.'">'.$match[1].'</a>', $content);
            $j++;
         }
      }
   }

   return $content;
}

//add_filter('bp_gtm_project_desc_content', 'wp_filter_kses', 1);
add_filter('bp_gtm_project_desc_content', 'wptexturize');
add_filter('bp_gtm_project_desc_content', 'convert_chars');
add_filter('bp_gtm_project_desc_content', 'wpautop');
add_filter('bp_gtm_project_desc_content', 'stripslashes_deep');
add_filter('bp_gtm_project_desc_content', 'bp_gtm_filter_short_links');

//add_filter('bp_gtm_task_desc_content', 'wp_filter_kses', 1);
add_filter('bp_gtm_task_desc_content', 'wptexturize');
add_filter('bp_gtm_task_desc_content', 'convert_chars');
add_filter('bp_gtm_task_desc_content', 'wpautop');
add_filter('bp_gtm_task_desc_content', 'stripslashes_deep');
add_filter('bp_gtm_task_desc_content', 'bp_gtm_filter_short_links');

add_filter('bp_gtm_discuss_text_content', 'wp_filter_kses', 1);
add_filter('bp_gtm_discuss_text_content', 'wptexturize');
add_filter('bp_gtm_discuss_text_content', 'convert_chars');
add_filter('bp_gtm_discuss_text_content', 'wpautop');
add_filter('bp_gtm_discuss_text_content', 'stripslashes_deep');
add_filter('bp_gtm_discuss_text_content', 'bp_gtm_filter_short_links');

add_filter('bp_gtm_term_name_content', 'wp_filter_kses', 1);
add_filter('bp_gtm_term_name_content', 'convert_chars');
add_filter('bp_gtm_term_name_content', 'stripslashes_deep');

add_filter('bp_gtm_project_name_content', 'wp_filter_kses', 1);
add_filter('bp_gtm_project_name_content', 'convert_chars');
add_filter('bp_gtm_project_name_content', 'stripslashes_deep');

add_filter('bp_gtm_task_name_content', 'wp_filter_kses', 1);
add_filter('bp_gtm_task_name_content', 'convert_chars');
add_filter('bp_gtm_task_name_content', 'stripslashes_deep');

add_filter('bp_gtm_role_name', 'wp_filter_kses', 1);
add_filter('bp_gtm_role_name', 'convert_chars');
add_filter('bp_gtm_role_name', 'stripslashes_deep');

add_filter('bp_gtm_labes', 'wp_filter_kses', 1);
add_filter('bp_gtm_labes', 'convert_chars');

// Allow shortcodes - for code excerpts in future
add_filter('bp_gtm_project_desc_content', 'do_shortcode');
add_filter('bp_gtm_task_desc_content', 'do_shortcode');

// Mention @slaFFik is back
add_filter('bp_gtm_discuss_text_content', 'bp_activity_at_name_filter');
add_filter('bp_gtm_task_desc_content', 'bp_activity_at_name_filter');
add_filter('bp_gtm_project_desc_content', 'bp_activity_at_name_filter');

