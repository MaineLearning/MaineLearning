<?php if (!defined('ABSPATH'))  die('Security check'); ?>
        <p class='cred-label-holder'>
			<label for="cred_post_type"><?php _e('Choose the type of content this form will create or modify:','wp-cred'); ?></label>
		</p>
		<select id="cred_post_type" name="cred_post_type" class='cred_ajax_change'>
            <optgroup label="<?php _e('Please Select &hellip;','wp-cred'); ?>">
                <option value='' disabled selected style='display:none;'><?php _e('Please Select..','wp-cred'); ?></option>
		<?php
			foreach ($post_types as $post_type1 ) {
			  if ($post_type==$post_type1)
				echo '<option value="'.$post_type1.'" selected="selected">'. $post_type1. '</option>';
			  else
				echo '<option value="'.$post_type1.'">'. $post_type1. '</option>';
			}
		?>
            </optgroup>
		</select>
        <p class="cred-label-holder">
			<label for="cred_post_status"><?php _e('Select the status of content created by this form:','wp-cred'); ?></label>
		</p>
		<select id="cred_post_status" name="cred_post_status" class='cred_ajax_change'>
            <optgroup label="<?php _e('Please Select &hellip;','wp-cred'); ?>">
                <option value='' disabled selected style='display:none;'><?php _e('Please Select..','wp-cred'); ?></option>
                <option value='original' <?php if ($post_status=='original') echo 'selected="selected"'; ?>><?php _e('Keep original status','wp-cred'); ?></option>
                <option value='draft' <?php if ($post_status=='draft') echo 'selected="selected"'; ?>><?php _e('Draft','wp-cred'); ?></option>
                <option value='pending' <?php if ($post_status=='pending') echo 'selected="selected"'; ?>><?php _e('Pending Review','wp-cred'); ?></option>
                <option value='private' <?php if ($post_status=='private') echo 'selected="selected"'; ?>><?php _e('Private','wp-cred'); ?></option>
                <option value='publish' <?php if ($post_status=='publish') echo 'selected="selected"'; ?>><?php _e('Published','wp-cred'); ?></option>
            </optgroup>
		</select>
        <p>
        	<label class='cred-label'>
        	    <input type='checkbox' class='cred-checkbox-10' name='cred_content_has_media_button' id='cred_content_has_media_button' value='1' <?php if (isset($has_media_button) && $has_media_button) echo 'checked="checked"'; ?> /><span class='cred-checkbox-replace'></span>
        	    <span><?php _e('Allow Media Insert button in Post Content Rich Text Editor','wp-cred'); ?></span>
        	</label>
        	<a class='cred-help-link' style='position:absolute;top:5px;right:10px' href='<?php echo $help['post_type_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['post_type_settings']['text']; ?>"><?php echo $help['post_type_settings']['text']; ?></a>
        </p>
