<h4><?php _e( 'Settings', 'bp_gtm' ) ?></h4>

<?php do_action( 'bp_gtm_before_settings' ); ?>

<?php if (bp_gtm_check_access('settings_edit_base')){ ?>

    <label for="gtm-tasks-pp"><?php _e('How many tasks per page do you want to see on Tasks page?', 'bp_gtm'); ?></label>
    <div class="text">
        <input type="text" name="gtm-tasks-pp" id="gtm-tasks-pp" value="<?php echo $bp_gtm_group_settings['tasks_pp']; ?>" />
    </div>

    <p>&nbsp;</p>

    <label for="gtm-tasks-pp"><?php _e('How many elements per page do you want to see on Discussions page?', 'bp_gtm'); ?></label>
    <div class="text">
        <input type="text" name="gtm-discuss-pp" id="gtm-discuss-pp" value="<?php echo $bp_gtm_group_settings['discuss_pp']; ?>" />
    </div>

    <p>&nbsp;</p>
    <label for="tab-name"><?php _e('Tab name in group navigation menu', 'bp_gtm'); ?></label>
    <div class="text">
        <input type="text" name="tab-name" id="tab-name" value="<?php echo esc_html(!empty($bp_gtm_group_settings['tab-name'])?$bp_gtm_group_settings['tab-name']:''); ?>" />
    </div>

    <p>&nbsp;</p>

    <label for="gtm-discuss-status"><?php _e('Tasks/Projects discussion', 'bp_gtm'); ?></label>
    <div class="checkbox">
        <label>
            <input type="checkbox" name="gtm-discuss" id="gtm-discuss" value="on" <?php echo ($bp_gtm_group_settings['discuss'] == 'on') ? 'checked="checked" ' : ' '; ?>/>
            <?php _e( 'Enable tasks and projects discussion (disabling it will make impossible to discuss any tasks or projects).', 'bp_gtm' ); ?>
        </label>
    </div>


<?php } ?>
    
<?php if (bp_gtm_check_access('settings_edit_roles')){
    // here will be the code for adding/deleting/editing group own roles
} ?>
    
<?php do_action( 'bp_gtm_after_settings' ); ?>

<p>&nbsp;</p>

<input type="submit" value="<?php _e( 'Save Changes', 'bp_gtm' ) ?> &rarr;" id="save" name="saveSettings" />

<?php wp_nonce_field( 'bp_gtm_edit_settings' ) ?>
