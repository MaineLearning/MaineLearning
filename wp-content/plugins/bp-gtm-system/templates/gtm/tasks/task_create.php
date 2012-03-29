<?php
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$parent_task = parse_url($url, PHP_URL_QUERY);
if (is_numeric(parse_url($url, PHP_URL_QUERY)) && parse_url($url, PHP_URL_QUERY) > 0) {
    $parent_task = parse_url($url, PHP_URL_QUERY);
    $parent_task = BP_GTM_Tasks::get_task_by_id($parent_task);
    $h4_title = __('Create New SubTask for', 'bp_gtm') . ' ' . bp_gtm_get_parent_task_link($parent_task, $gtm_link) . '';
} else {
    $parent_task = 0;
    $h4_title = __('Create New Task', 'bp_gtm');
}
?>

<h4><?php echo $h4_title ?></h4>

<?php do_action('bp_before_gtm_task_create'); ?>

<p>
    <label for="task_name"><?php _e('Task name', 'bp_gtm'); ?><span class="required">*</span></label>
    <input type="text" name="task_name" id="task_name" value="" />
</p>

<p>
    <label for="task_desc"><?php _e('Task description', 'bp_gtm') ?></label>
    <?php
    if (function_exists('wp_editor') && $bp_gtm['mce'] == 'on') {
        wp_editor(
                '', // initial content
                'gtm_desc', // ID attribute value for the textarea
                array(
            'media_buttons' => false,
            'textarea_name' => 'task_desc',
                )
        );
    } else {
        ?>
        <textarea name="task_desc" id="gtm_desc"></textarea>
    <?php } ?>
</p>

<label for="task_resp"><?php _e('Who is responsible for this task execution?', 'bp_gtm'); ?></label>
<?php bp_gtm_filter_users($bp_gtm['theme']) ?>
<?php do_action('bp_gtm_task_extra_fields_editable') ?>

<?php bp_gtm_task_project($parent_task); ?>

<label for="task_deadline"><?php _e('Task Deadline', 'bp_gtm'); ?><span class="required">*</span></label>
<input type="text" name="task_deadline" id="task_deadline" value="" readonly="readonly"/>
<?php do_action('bp_after_gtm_task_create', $bp_gtm); ?>
<div class="clear-both"></div>
<div id="tasks_tax">
    <div class="float">
        <label for="tags"><?php _e('Task Tags', 'bp_gtm'); _e('(comma separated)', 'bp_gtm'); ?></label>
        <p><?php _e('You can add tags to your task. If you want to add more tags, separate it with comma.', 'bp_gtm');?></p>
        <ul class="first acfb-holder">
            
            <div class="clear-both"></div>
            <li>
                <input type="text" name="task_tags" class="tags" id="tags" /><input type="button" name="tags" value="<?php _e('Add tag', 'bp_gtm');?>"/>
            </li>
            <div class="paste-tags"></div>
        </ul>
    </div>
    <div class="right">
        <label for="cats"><?php _e('Task Categories', 'bp_gtm');_e('(comma separated)', 'bp_gtm'); ?></label>
        <p><?php _e('You can select or add categories for your task. If you want to add more categories, separate it with comma.', 'bp_gtm');?></p>
        <ul class="second acfb-holder">
            
            <div class="clear-both"></div>
            <li>
                <input type="text" name="task_cat" class="cats" id="cats" /><input type="button" name="cats" value="<?php _e('Add cat', 'bp_gtm');?>"/>
            </li>
            <div class="clear-both"></div>
            <div class="paste-cats"></div>
            <?php bp_gtm_get_cats_for_group();?>
        </ul>
    </div>
</div>  
<input type="hidden" name="task_creator" value="<?php echo $bp->loggedin_user->id; ?>" />
<input type="hidden" name="task_parent" value="<?php echo $parent_task ?>" />
<input type="hidden" name="task_group" value="<?php bp_current_group_id() ?>" />
<input type="hidden" name="task_tag_names" id="tag_names" value="" class="" />
<input type="hidden" name="task_cat_names" id="cat_names" value="" class="" />

<p>&nbsp;</p><div class="clear-both"></div>
<p><input type="submit" value="<?php _e('Create Task', 'bp_gtm') ?> &rarr;" id="save" name="saveNewTask" /></p>
<?php wp_nonce_field('bp_gtm_new_task') ?>
