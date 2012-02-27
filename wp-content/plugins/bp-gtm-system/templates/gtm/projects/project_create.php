<h4><?php _e( 'Create New Project', 'bp_gtm' ) ?></h4>

<?php do_action( 'bp_before_gtm_project_create' ); ?>

<label for="project_name">* <?php _e('Project name','bp_gtm'); ?></label>
<input type="text" name="project_name" id="project_name" value="" />

<label for="project_desc">* <?php _e( 'Project description', 'bp_gtm' ); ?></label>
<?php
if (function_exists('wp_editor') && $bp_gtm['mce'] == 'on'){
    wp_editor(
        '', // initial content
        'gtm_desc', // ID attribute value for the textarea
        array(
            'media_buttons' => false,
            'textarea_name' => 'project_desc',
        )
    );
}else{ ?>
    <textarea name="project_desc" id="gtm_desc"></textarea>
<?php 
} ?>

<label for="project_resp"><?php _e('Who is responsible for this project execution or has access if its hidden?','bp_gtm'); ?>
</label>
<?php bp_gtm_filter_users()?>
    
<?php do_action( 'bp_gtm_project_extra_fields_editable' ) ?>

<?php /*
<p>
    <label for="project_status">* <?php _e( 'Project status', 'bp_gtm' ); ?></label>
    <input type="radio" name="project_status" value="public" /> <?php _e( 'Public - for all logged in users', 'bp_gtm' ); ?><br>
    <input type="radio" name="project_status" value="private" checked="checked" /> <?php _e( 'Private - for this group members only', 'bp_gtm' ); ?><br>
   <input type="radio" name="project_status" value="hidden" /> <?php _e( 'Hidden - for creator, group admin and responsible people only', 'bp_gtm' ); ?>
</p>
*/ ?>

<label for="project_deadline">* <?php _e('Project Deadline','bp_gtm'); ?> [<?php echo $date_format;?>]</label>
<input type="text" name="project_deadline" id="project_deadline" value="" readonly="readonly"/>
<div id="projects_tax">
    <div class="float">
        <label for="project_tags"><?php _e('Project Tags', 'bp_gtm'); _e('(comma separated)', 'bp_gtm'); ?></label>
        <ul class="first acfb-holder">
            <div class="clear-both"></div>
            <li>
                <input type="text" name="project_tags" class="tags" id="tags" />
            </li>
        </ul>
    </div>
    <div class="right">
        <label for="project_cats"><?php _e('Project Categories', 'bp_gtm'); echo ' '; _e('(comma separated)', 'bp_gtm'); ?>
        <ul class="second acfb-holder">
            <div class="clear-both"></div>
            <li>
                <input type="text" name="project_cats" class="cats" id="cats" />
            </li>
        </ul>
    </div>
</div>
<div class="clear-both"></div>
<?php do_action( 'bp_after_gtm_project_create', $bp_gtm ); ?>

<input type="hidden" name="project_creator" value="<?php echo $bp->loggedin_user->id; ?>" />
<input type="hidden" name="project_group" value="<?php bp_current_group_id(); ?>" />
<input type="hidden" name="project_cat_names" id="cat_names" value="" class="" />
<input type="hidden" name="project_tag_names" id="tag_names" value="" class="" />

<p>&nbsp;</p><div class="clear"></div>
<p><input type="submit" value="<?php _e( 'Create Project', 'bp_gtm' ) ?> &rarr;" id="save" name="saveNewProject" /></p>
<?php wp_nonce_field( 'bp_gtm_new_project' ) ?>