<?php
$option = bp_gtm_task_list_settings($bp_gtm_group_settings);

do_action('bp_gtm_delete_view_notification', $bp->loggedin_user->id, $ids = 'none', $option['action']);
bp_gtm_create_button($gtm_link, 'task');
?>

<h4><?php echo $option['page_h4']; ?></h4>


<div class="group-navi">
    <div class="pagination no-ajax">
        <div id="post-count" class="pag-count">
            <?php bp_gtm_tasks_navi($option['filter'], $option['project_id'], $option['per_page']); ?>
            <!--<p><?php _e('List of tasks in this group. Use links in the right to filter them.', 'bp_gtm'); ?></p>-->
        </div>

        <div id="tasks-filter">
            <?php _e('Filter: ', 'bp_gtm'); ?>
            <a <?php if (!empty($option['alpha_style']))
                echo $option['alpha_style'] ?> href="<?php echo $gtm_link . 'tasks?filter=alpha'; ?>"><?php _e('By A/Z', 'bp_gtm'); ?></a> |
            <a <?php if (!empty($option['deadline_style']))
                    echo $option['deadline_style'] ?> href="<?php echo $gtm_link . 'tasks?filter=deadline'; ?>"><?php _e('By deadline', 'bp_gtm'); ?></a> |
            <a <?php if (!empty($option['by_proj_style']))
                    echo $option['by_proj_style'] ?> id="open" href="#"><?php _e('By project', 'bp_gtm'); ?></a> |
            <a <?php if (!empty($option['done_style']))
                    echo $option['done_style'] ?> href="<?php echo $gtm_link . 'tasks?filter=done'; ?>"><?php _e('Completed', 'bp_gtm'); ?></a>

            <?php bp_gtm_get_groups_filter_list(); ?>

        </div>
    </div>
</div>
<?php do_action('bp_before_gtm_tasks_list');

if (count($option['tasks']) > 0) { ?>

    <table class="item-list zebra forum task-list">
        <thead>
            <tr>
                <th id="th-title"><?php _e('Task Title', 'bp_gtm') ?></th>
                <th id="th-poster"><?php _e('Responsible', 'bp_gtm') ?></th>
                <th id="th-group"><?php _e('Project', 'bp_gtm') ?></th>
                <th id="th-postcount"><?php _e('Deadline', 'bp_gtm') ?></th>
                <th id="th-freshness"><?php _e('Actions', 'bp_gtm') ?></th>

                <?php do_action('bp_directory_tasks_extra_cell_head') ?>

            </tr>
        </thead>

        <tbody id="tasks-list" >
            <?php foreach ($option['tasks'] as $task) { ?>
                <tr id="<?php echo $task->id; ?>" class="<?php bp_gtm_task_check_date($task->id, $task->deadline); ?>">

                    <td class="td-title">
                        <span class="subtasks_<?php echo $task->id; ?>">
                            <span class="all_in_all" title="<?php _e('Current number of subtasks', 'bp_gtm'); ?>"><?php echo bp_gtm_get_subtasks_count($task->id) ?></span>&nbsp;
                            <?php
                            bp_get_create_subtask_link($task->id, $gtm_link);
                            ?>
                        </span>
                        <?php bp_gtm_view_link($task, $gtm_link, 'task') ?>
                        </a>
                    </td>
                    <td class="td-poster">
                        <?php bp_gtm_get_group_project_respossibles($task); ?>
                    </td>
                    <td class="td-group">
                        <?php bp_gtm_view_link($project, $gtm_link, 'project'); ?>
                    </td>
                    <td class="td-group">
                        <div class="object-name center"><?php bp_gtm_format_date($task->deadline); ?></div>
                    </td>
                    <td id="tasks" class="td-freshness center">

                        <?php
                            bp_gtm_edit_link($task->id, $gtm_link, 'tasks');
                            bp_gtm_delete_task_link($task->id);
                            bp_gtm_done_link($option['filter'], $task->id, $el_type = 'task'); ?>

                    </td>

                    <?php do_action('bp_directory_tasks_extra_cell') ?>
                </tr>
                <?php do_action('bp_directory_tasks_extra_row');
            } ?>
        </tbody>
    </table>
    <?php
} else {
    echo '<div id="message" class="info"><p>' . __('There are no tasks to display.', 'bp_gtm') . '</p></div>';
}
?>