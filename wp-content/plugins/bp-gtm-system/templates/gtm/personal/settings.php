<h4><?php _e( 'Personal Settings', 'bp_gtm' ); ?></h4>

<?php do_action( 'bp_gtm_before_personal_settings' ); ?>

<form action="<?php bp_gtm_p_form_action($bp->loggedin_user->id) ?>" id="gtm_form" class="standard-form" method="post" enctype="multipart/form-data">
   <label for="p_tasks_pp"><?php _e('How many tasks per page do you want to see on Personal Tasks page?', 'bp_gtm'); ?></label>
   <div class="text">
      <input type="text" name="p_tasks_pp" id="p_tasks_pp" value="<?php echo $bp_gtm_p_tasks_pp; ?>" />
   </div>

   <p>&nbsp;</p>

   <?php do_action( 'bp_gtm_after_personal_settings' ); ?>

   <input type="hidden" name="p_user_id" value="<?php echo $bp->loggedin_user->id ?>" />
   <input type="submit" value="<?php _e( 'Save Changes', 'bp_gtm' ) ?> &rarr;" id="save" name="saveSettings" />

   <?php wp_nonce_field( 'bp_gtm_personal_settings' ) ?>
</form>
