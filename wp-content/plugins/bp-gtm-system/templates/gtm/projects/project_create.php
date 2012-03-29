<h4><?php _e('Create New Project', 'bp_gtm') ?></h4>

<?php do_action('bp_before_gtm_project_create'); ?>

<label for="project_name"><?php _e('Project name', 'bp_gtm'); ?> <span class="required">*</span></label>
<input type="text" name="project_name" id="project_name" value="" />

<label for="project_desc"><?php _e('Project description', 'bp_gtm'); ?></label>
<?php
if (function_exists('wp_editor') && $bp_gtm['mce'] == 'on') {
    wp_editor(
            '', // initial content
            'gtm_desc', // ID attribute value for the textarea
            array(
        'media_buttons' => false,
        'textarea_name' => 'project_desc',
            )
    );
} else {
    ?>
    <textarea name="project_desc" id="gtm_desc"></textarea>
    <?php }
?>

<label for="project_resp"><?php _e('Responsible Users', 'bp_gtm'); ?>
</label>
<p><?php _e('Please select one or more responsible users. You can select them by clicking them', 'bp_gtm') ?></p>
<?php bp_gtm_filter_users() ?>

<?php do_action('bp_gtm_project_extra_fields_editable') ?>

<label for="project_deadline"><?php _e('Project Deadline', 'bp_gtm'); ?><span class="required">*</span></label>
<p><?php _e('Select a date for the project when it should be finished.', 'bp_gtm') ?></p>
<input type="text" name="project_deadline" id="project_deadline" value="" readonly="readonly"/>
<?php do_action('bp_after_gtm_project_create', $bp_gtm); ?>
<div id="projects_tax">
    <div class="float">
        <label for="project_tags"><?php _e('Project Tags', 'bp_gtm');
_e('(comma separated)', 'bp_gtm'); ?></label>
        <p><?php _e('You can add tags to your project. If you want to add more tags, separate it with comma.', 'bp_gtm'); ?></p>
        <ul class="first acfb-holder">
            <div class="clear-both"></div>
            <li>
                <input type="text" name="project_tags" class="tags" id="tags" /><input type="button" name="tags" value="<?php _e('Add tag', 'bp_gtm');?>"/>
            </li>
            <div class="paste-tags"></div>

        </ul>
    </div>
    <div class="right">
        <label for="project_cats"><?php _e('Project Categories', 'bp_gtm');
echo ' ';
_e('(comma separated)', 'bp_gtm'); ?></label>
        <p><?php _e('You can select or add categories for your project. If you want to add more categories, separate it with comma.', 'bp_gtm'); ?></p>
        <ul class="second acfb-holder">
            <div class="clear-both"></div>
            <li>
                <input type="text" name="project_cat" class="cats" id="cats" /><input type="button" name="cats" value="<?php _e('Add cat', 'bp_gtm');?>"/>
            </li>
            <div class="paste-cats"></div>
            <?php bp_gtm_get_cats_for_group();?>
        </ul>
    </div>
</div>
<div class="clear-both"></div>


<input type="hidden" name="project_creator" value="<?php echo $bp->loggedin_user->id; ?>" />
<input type="hidden" name="project_group" value="<?php bp_current_group_id(); ?>" />
<input type="hidden" name="project_cat_names" id="cat_names" value="" class="" />
<input type="hidden" name="project_tag_names" id="tag_names" value="" class="" />

<p>&nbsp;</p><div class="clear"></div>
<p><input type="submit" value="<?php _e('Create Project', 'bp_gtm') ?> &rarr;" id="save" name="saveNewProject" /></p>
<?php wp_nonce_field('bp_gtm_new_project') ?>