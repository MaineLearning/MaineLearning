<h4><?php _e('Update Task', 'bp_gtm') ?></h4>
<?php
$task = BP_GTM_Tasks::get_task_by_id($bp->action_variables[2]);
$resps = explode(' ', $task[0]->resp_id);
?>
<?php do_action('bp_before_gtm_task_create'); ?>

<label for="task_name"><?php _e('Task name', 'bp_gtm'); ?><span class="required">*</span></label>
<input type="text" name="task_name" id="task_name" value="<?php echo $task[0]->name ?>" />

<label for="task_desc">* <?php _e('Task description', 'bp_gtm') ?></label>
<?php
if (function_exists('wp_editor') && $bp_gtm['mce'] == 'on') {
    wp_editor(
            $task[0]->desc, // initial content
            'gtm_desc', // ID attribute value for the textarea
            array(
        'media_buttons' => false,
        'textarea_name' => 'task_desc',
            )
    );
} else {
    ?>
    <textarea name="task_desc" id="gtm_desc"><?php echo $task[0]->desc ?></textarea>
<?php } ?>

<label for="task_resp"><?php _e('Who is responsible for this task execution?', 'bp_gtm'); ?>
    <?php bp_gtm_filter_users($resps) ?>

    <?php do_action('bp_gtm_task_extra_fields_editable') ?>

    <?php bp_gtm_task_project(null, $task[0]->project_id); ?>

    <label for="task_deadline"><?php _e('Task Deadline', 'bp_gtm'); ?><span class="required">*</span></label>
    <input type="text" name="task_deadline" id="task_deadline" value="<?php echo date($date_format, strtotime($task[0]->deadline)) ?>" readonly="readonly"/>
    <?php do_action('bp_after_gtm_task_create', $bp_gtm); ?>
    <div class="clear"></div>
    <div id="tasks_tax">
        <div class="float">
            <label for="tags"><?php _e('Task Tags', 'bp_gtm');
    _e('(comma separated)', 'bp_gtm');
    ?></label>
            <p><?php _e('You can add tags to your task. If you want to add more tags, separate it with comma.', 'bp_gtm'); ?></p>
            <ul class="first acfb-holder">
                <div class="clear-both"></div>
                <li>
                    <input type="text" name="task_tags" class="tags" id="tags" /><input type="button" name="tags" value="<?php _e('Add tag', 'bp_gtm');?>"/>
                </li>
                <div class="clear-both"></div>
                <?php bp_gtm_term_task_edit_loop($task[0]->id, 'tag'); ?>
                <div class="paste-tags"></div>
            </ul>



        </div>
        <div class="right">
            <label for="cats"><?php _e('Task Categories', 'bp_gtm');
_e('(comma separated)', 'bp_gtm');
?></label>
            <p><?php _e('You can select or add categories for your task. If you want to add more categories, separate it with comma.', 'bp_gtm'); ?></p>
            <ul class="second acfb-holder">
                <div class="clear-both"></div>
                <li>
                    <input type="text" name="task_cat" class="tags" id="cats" /><input type="button" name="cats" value="<?php _e('Add cat', 'bp_gtm');?>"/>
                </li>
                <div class="clear-both"></div>
                <div class="paste-cats"></div>
                <?php bp_gtm_term_task_edit_loop($task[0]->id, 'cat'); ?>

            </ul>

        </div>
    </div>
    <input type="hidden" name="task_id" value="<?php echo $task[0]->id ?>" />
    <input type="hidden" name="task_group" value="<?php bp_current_group_id() ?>" />
    <input type="hidden" name="task_tag_names" id="tag_names" value="" class="<?php echo bp_gtm_get_task_cats($task[0]->id)?>" />
    <input type="hidden" name="task_cat_names" id="cat_names" value="" class="" />
    <p>&nbsp;</p><div class="clear-both"></div><p>&nbsp;</p>
    <p><input type="submit" value="<?php _e('Update Task', 'bp_gtm') ?> &rarr;" id="save" name="editTask" /></p>
<?php wp_nonce_field('bp_gtm_edit_task') ?>

