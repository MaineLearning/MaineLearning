<h4><?php _e( 'Delete everything?', 'bp_gtm' ) ?></h4>

<?php
   $count = bp_gtm_get_allcount($bp->groups->current_group->id, 'all');
?>

<p><?php _e('Are you sure you want to delete all tags, categories, tasks, projects and discussions? This action cannot be undone, so be careful.','bp_gtm') ?></p>

<p><?php _e('You will delete only data, not tables. All in all there are:', 'bp_gtm'); ?></p>
<ul class="deleted">
   <li><?php echo sprintf(_n('%s task', '%s tasks', $count['tasks'], 'bp_gtm'), $count['tasks']); ?></li>
   <li><?php echo sprintf(_n('%s project', '%s projects', $count['projects'], 'bp_gtm'),  $count['projects']); ?></li>
   <li><?php echo sprintf(_n('%s tag', '%s tags', $count['tags'], 'bp_gtm'), $count['tags']); ?></li>
   <li><?php echo sprintf(_n('%s category', '%s categories', $count['cats'], 'bp_gtm'), $count['cats']); ?></li>
</ul>

<p><?php _e('Later you will have the ability to create new projects/tasks/tags/categories if site administrator doesn\'t disable GTM System for this group.','bp_gtm'); ?></p>

<?php if (bp_gtm_check_access('delete_all')) { ?>
    <input type="hidden" name="cur_group" value="<?php echo $bp->groups->current_group->id ?>"/>
    <p><input type="submit" name="deleteAll" value="<?php _e('Delete everything', 'bp_gtm'); ?>"/></p>
     <?php wp_nonce_field( 'bp_gtm_delete' ) ?>
 <?php } ?>