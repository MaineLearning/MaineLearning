<?php
$options  = bp_gtm_get_discussion_settings($bp_gtm_group_settings);


?>

<h4><?php echo $options['h4_title']; ?></h4>

<div class="pagination no-ajax">
    <div id="post-count" class="pag-count">
        <?php bp_gtm_discuss_navi($options['type'], $bp->groups->current_group->id, $bp_gtm_group_settings['discuss_pp']); ?>
    </div>
    <div id="discuss-filter">
        <?php _e('Show: ','bp_gtm'); ?>
        <a href="<?php echo $gtm_link.'discuss?filter=tasks'; ?>" class="<?php echo $options['styles_t_cur'] ?>"><?php _e('For Tasks','bp_gtm'); ?></a> |
        <a href="<?php echo $gtm_link.'discuss?filter=projects'; ?>" class="<?php echo $options['styles_p_cur'] ?>"><?php _e('For Projects','bp_gtm'); ?></a>
    </div>
</div>

<?php if (count($options['posts'])>0) { ?>
<table class="forum zebra disscussions">
   <thead>
      <tr>
         <th id="th-title"><?php echo $options['th_title'] ?></th>
         <th id="th-poster"><?php _e('Latest Poster', 'bp_gtm') ?></th>
         <th id="th-postcount"><?php _e('Posts', 'bp_gtm') ?></th>
         <th id="th-freshness"><?php _e('Freshness', 'bp_gtm') ?></th>
         <?php do_action('bp_gtm_discuss_extra_cell_head') ?>
      </tr>
   </thead>
   <tbody id="discuss-list">
      <?php
      foreach ((array)$options['posts'] as $post) { ?>
         <tr class="">
            <td class="td-title">
               <?php bp_gtm_view_disscuss_link($post->elem_id, $gtm_link, $options['type'])?>
            </td>
            <td class="td-poster">
            <?php bp_gtm_poster_discussion_meta($post->author_id); ?>
               
            </td>
            <td class="td-postcount">
               <?php echo $post->discuss_count; ?>
            </td>
            <td class="td-freshness">
            <div class="object-name center" ><?php bp_gtm_format_date($post->date_created);?></div>
            </td>

            <?php do_action('bp_gtm_discuss_extra_cell') ?>
         </tr>
      <?php
         do_action('bp_gtm_discuss_extra_row');
      }
      do_action('bp_gtm_discuss_extra_last_row'); ?>
   </tbody>
</table>
<?php 
}else{
   echo '<div id="message" class="info"><p>'.__('There are no discussion posts to display.','bp_gtm').'</p></div>';
}
?>