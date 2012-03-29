<?php
$task = BP_GTM_Tasks::get_task_by_id($bp->action_variables[2]);
if (count($task) == 0) {
    echo '<h4>' . __('Error - 404', 'bp_gtm') . '</h4>';
    echo '<p>' . __('There is no task in the database with such ID. Please go to the main tasks list.', 'bp_gtm') . '</p>';
    bp_gtm_view_list_button($gtm_link, 'tasks');
} else {
    if ($task['0']->done == 1) {
        $task_status = '<span id="task_status" style="color:green;font-size:14px">&rarr; ' . __('completed', 'bp_gtm') . '</span>';
    } else {
        $task_status = '<span id="task_status" style="display:none;font-size:14px"></span>';
    }
    ?>
    <div id="message" class="error"></div>
    <div id="topic-meta">
        <h3>#T<?php echo $task['0']->id ?> &rarr; <a href="<?php echo $gtm_link . 'tasks/view/' . $task['0']->id ?>"><?php echo $task['0']->name; ?></a> <?php echo $task_status; ?></h3>
        <?php bp_gtm_view_list_button($gtm_link, 'tasks'); ?>
        <?php
        if ($task['0']->parent_id == '0') {
            bp_gtm_create_subtask_button($task['0']->id, $gtm_link);
            bp_gtm_view_subtask_button($task['0']->id);
        }
        bp_gtm_view_parent_task_button($task['0']->parent_id, $gtm_link);
        ?>

        <?php bp_gtm_edit_button($task['0']->id, $gtm_link, 'task'); ?>

        <?php bp_gtm_complite_tast_button($task['0']->id, $task['0']->done); ?>

    <?php bp_gtm_delete_tast_button($task['0']->id); ?>

        <div class="admin-links">
            <?php
            bp_gtm_get_responsibles($task[0]->resp_id);
            do_action('bp_gtm_task_view_resps');
            ?>
        </div>
    </div>

    <?php do_action('bp_before_task_view_content') ?>

    <ul id="topic-post-list" class="item-list">
        <li id="post-0" class="">
            <div class="poster-meta">
    <?php bp_gtm_poster_meta($task['0'], 'task'); ?>
            </div>

            <div class="post-content" id="">
    <?php echo $task['0']->desc; ?>
            </div>

            <div class="admin-links">
    <?php do_action('bp_gtm_task_desc_meta'); ?>
                <a href="<?php echo $gtm_link . 'tasks/view/' . $task['0']->id ?>" title="<?php _e('Permalink to task description', 'bp_gtm') ?>">#</a>
            </div>
        </li>
    </ul>
    <div class="projects-files">
    <?php do_action('bp_gtm_task_after_content', $task['0'], 'task', false); ?>
    </div>
    <div class="pagination no-ajax">
        <div id="post-count" class="pag-count">
            <?php bp_gtm_task_terms($task['0']->id, 'tag', true); ?> | <?php bp_gtm_task_terms($task['0']->id, 'cat', true); ?>
            <?php
            if ($task['0']->project_id != '0') {
                echo ' | ';
                _e('Project: ', 'bp_gtm');
                bp_gtm_view_link($task['0']->project_id, bp_gtm_get_el_name_by_id($task['0']->project_id, 'project'), $gtm_link, 'project');
            }
            ?>
        </div>
        <div class="pagination-links" id="topic-pag">
            <p><?php _e('Deadline', 'bp_gtm');
            echo ': ' . bp_gtm_get_format_date($task['0']->deadline); ?></p>
        </div>
    </div>

    <div class="subtasks"></div>

    <?php
    if ($bp_gtm_group_settings['discuss'] == 'on' && bp_gtm_check_access('discuss_view')) {
        bp_gtm_discussion_list($task[0]->id, 'task', $avatar);
        ?>
        <!-- #discussion-post-list -->

            <?php if (bp_gtm_check_access('discuss_create')) { ?>
            <div id="post-topic-reply">
                <p id="post-reply"></p>

            <?php do_action('bp_gtm_discuss_new_reply_before') ?>

                <h4><?php _e('Add a reply:', 'bp_gtm') ?></h4>

                <textarea name="discuss_text" id="discuss_text"></textarea>

                <input type="hidden" name="author_id" value="<?php echo $bp->loggedin_user->id ?>" />
                <input type="hidden" name="task_id" value="<?php echo $task['0']->id ?>" />
                <input type="hidden" name="project_id" value="0" />
                <input type="hidden" name="files_project_id" value="<?php echo $task['0']->project_id ?>" />
                <input type="hidden" name="group_id" value="<?php echo $task['0']->group_id ?>" />

            <?php do_action('bp_gtm_discuss_new_reply_after', $bp_gtm) ?>

                <div class="submit">
                    <input type="submit" name="submitDiscuss" id="submit" value="<?php _e('Post Reply', 'bp_gtm') ?>" />
                </div>

            <?php wp_nonce_field('bp_gtm_discuss_new_reply') ?>
            </div><!-- #discussion-post-form -->
        <?php
        }
    }
}
?>
