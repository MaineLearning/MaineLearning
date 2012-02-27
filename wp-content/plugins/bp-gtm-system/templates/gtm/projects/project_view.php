<?php
$project = BP_GTM_Projects::get_project_by_id($bp->action_variables[2]);
if (empty($project)) {
    echo '<h4>' . __('Error - 404', 'bp_gtm') . '</h4>';
    echo '<p>' . __('There is no project in the database with such ID. Please go to the main projects list.', 'bp_gtm') . '</p>';
    bp_gtm_view_list_button($gtm_link);
}
?>

<?php //print_var($project);  ?>
<div id="message" class="error"></div>
<div id="topic-meta">
    <h3>#P<?php echo $project['0']->id ?> &rarr;<?php bp_gtm_view_link($project['0']->id, $project['0']->name, $gtm_link, 'project') ?> <?php if (!empty($project_done)) echo $project_done; ?></h3>
    <?php bp_gtm_view_list_button($gtm_link); ?>
    <?php bp_gtm_pending_complited_tasks($project['0']->id, $gtm_link); ?>

    <?php bp_gtm_edit_button($project['0']->id, $gtm_link, 'project'); ?>

    <div class="admin-links">
        <?php
        bp_gtm_get_responsibles($project['0']->resp_id);
        do_action('bp_gtm_project_view_resps');
        ?>
    </div>
</div>

<?php do_action('bp_before_project_view_content') ?>

<ul id="topic-post-list" class="item-list">
    <li id="post-0" class="">
        <div class="poster-meta">
            <?php bp_gtm_poster_meta($project['0']); ?>
        </div>

        <div class="post-content">
            <?php echo $project['0']->desc; ?>
        </div>

        <div class="admin-links">
            <?php do_action('bp_gtm_project_desc_meta'); ?>
            <a href="<?php echo $gtm_link . 'projects/view/' . $project['0']->id ?>" title="<?php _e('Permalink to project description', 'bp_gtm') ?>">#</a>
        </div>
    </li>
</ul>
<div class="projects-files">
    <?php do_action('bp_gtm_project_after_content', $project['0'], 'project', false); ?>
</div>
<div class="pagination no-ajax">
    <div id="post-count" class="pag-count">
        <?php bp_gtm_project_terms($project['0']->id, 'tag', true); ?> | <?php bp_gtm_project_terms($project['0']->id, 'cat', true); ?>
    </div>
    <div class="pagination-links" id="topic-pag">
        <p><?php _e('Deadline', 'bp_gtm');
        echo ': ' . bp_gtm_get_format_date($project['0']->deadline); ?> |
            <?php
            $count_tasks = BP_GTM_Projects::get_count_tasks($project['0']->id, $bp->groups->current_group->id);
            if ($count_tasks['all'] > 0) {
                $percent = round($count_tasks['done'] * 100 / $count_tasks['all'], 2) . '%';
                echo sprintf(_n('%d task left out of %d: %s done', '%d tasks left out of %d: %s done', $count_tasks['undone'], $count_tasks['all'], $percent, 'bp_gtm'), $count_tasks['undone'], $count_tasks['all'], $percent);
            } else {
                _e('There are no tasks yet.', 'bp_gtm');
            }
            ?>
        </p>
    </div>
</div>

    <?php if ($bp_gtm_group_settings['discuss'] == 'on' && bp_gtm_check_access('discuss_view')) { ?>
    <ul id="topic-post-list" class="item-list discussions">
        <?php bp_gtm_discussion_list($project[0]->id, 'project', $avatar); ?>

    <?php if (bp_gtm_check_access('discuss_create')) { ?>
            <div id="post-topic-reply">
                <p id="post-reply"></p>

        <?php do_action('bp_gtm_discuss_new_reply_before') ?>

                <h4 class="reply"><?php _e('Add a reply:', 'bp_gtm') ?></h4>

                <textarea name="discuss_text" id="discuss_text"></textarea>

                <input type="hidden" name="author_id" value="<?php echo $bp->loggedin_user->id ?>" />
                <input type="hidden" name="task_id" value="0" />
                <input type="hidden" name="project_id" value="<?php echo $project['0']->id ?>" />
                <input type="hidden" name="group_id" value="<?php echo $project['0']->group_id ?>" />
        <?php do_action('bp_gtm_discuss_new_reply_after', $bp_gtm) ?>
                <div class="submit">
                    <input type="submit" name="submitDiscuss" id="submit" value="<?php _e('Post Reply', 'bp_gtm') ?>" />
                </div>



            <?php wp_nonce_field('bp_gtm_discuss_new_reply') ?>
            </div><!-- #discussion-post-form -->
        <?php }
    }
    ?>
