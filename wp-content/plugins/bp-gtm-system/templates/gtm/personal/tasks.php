<?php
$options = bp_gtm_get_presonal_tasks_option($bp_gtm_p_tasks_pp);
?>

<h4><?php echo $options['page_h4']; ?></h4>
<div class="personal">
    <div class="pagination no-ajax">
        <div id="post-count" class="pag-count">
            <?php bp_gtm_personal_tasks_navi($options, $options['per_page']); ?>
        </div>

        <div class="resp-style" id="tasks-filter">
            <?php _e('Filter: ', 'bp_gtm'); ?>
            <a <?php echo $options['alpha_style'] ?> href="<?php echo 'tasks?filter=alpha'; ?>"><?php _e('By A/Z', 'bp_gtm'); ?></a> |
            <a <?php echo $options['deadline_style'] ?> href="<?php echo 'tasks?filter=deadline'; ?>"><?php _e('By deadline', 'bp_gtm'); ?></a> |
            <a <?php echo $options['by_proj_style'] ?> id="open_project" href="#"><?php _e('By project', 'bp_gtm'); ?></a> |
            <a <?php echo $options['by_group_style'] ?> id="open_group" href="#"><?php _e('By group', 'bp_gtm'); ?></a> |
            <a <?php echo $options['done_style'] ?> href="<?php echo 'tasks?filter=done'; ?>"><?php _e('Completed', 'bp_gtm'); ?></a>

            <?php bp_gtm_get_personal_filter_project_list(); ?>
            <?php bp_gtm_get_personal_filter_group_list(); ?>


        </div>

    
</div>
</div>
<?php do_action('bp_before_gtm_personal_tasks_list');

if (count($options['tasks']) > 0) { ?>
    <table class="item-list zebra" id="personal">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Task Title', 'bp_gtm') ?></th>
                <th id="th-group"><?php _e('Project', 'bp_gtm') ?></th>
                <th id="th-poster"><?php _e('Group', 'bp_gtm') ?></th>
                <th id="th-postcount"><?php _e('Deadline', 'bp_gtm') ?></th>
                <?php do_action('bp_gtm_personal_tasks_extra_cell_head') ?>
            </tr>
        </thead>

        <tbody id="tasks-list" >
            <?php bp_gtm_get_personal_task_list($options['tasks']);
            ?>
        </tbody>
    </table>
    <?php
    do_action('bp_after_gtm_personal_tasks_list');
} else {
    echo '<div id="message" class="info"><p>' . __('There are no tasks to display.', 'bp_gtm') . '</p></div>';
}

?>
