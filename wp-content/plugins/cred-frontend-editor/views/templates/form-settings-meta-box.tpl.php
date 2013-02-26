<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<fieldset class="cred-fieldset">
	<h4><?php _e('Basic Settings','wp-cred'); ?></h4>
	<p class='cred-explain-text'><?php _e('Forms can create new content or edit existing content. Choose what this form will do:','wp-cred'); ?></p>
	<?php wp_nonce_field('cred-admin-post-page-action','cred-admin-post-page-field'); ?>
	<select id="cred_form_type" name="cred_form_type">
	    <optgroup label="<?php _e('Please Select..','wp-cred'); ?>">
	        <option value='' disabled selected style='display:none;'><?php _e('Please Select..','wp-cred'); ?></option>
	        <option value="edit" <?php if ($form_type=='edit') echo 'selected="selected"'; ?>><?php _e('Edit content','wp-cred'); ?></option>
	        <option value="new" <?php if ($form_type=='new') echo 'selected="selected"'; ?>><?php _e('Create content','wp-cred'); ?></option>
	    </optgroup>
	</select>
	<p class='cred-explain-text'><?php _e('Choose what to do after visitors submit this form:','wp-cred'); ?></p>
	<select id="cred_form_success_action" name="cred_form_success_action">
	    <optgroup label="<?php _e('Please Select..','wp-cred'); ?>">
	        <option value="form" <?php if ($form_action=='form') echo 'selected="selected"'; ?>><?php _e('Keep displaying this form','wp-cred'); ?></option>
	        <option value="message" <?php if ($form_action=='message') echo 'selected="selected"'; ?>><?php _e('Display a message instead of the form...','wp-cred'); ?></option>
	        <option value="post" <?php if ($form_action=='post') echo 'selected="selected"'; ?>><?php _e('Display the post','wp-cred'); ?></option>
	        <option value="page" <?php if ($form_action=='page') echo 'selected="selected"'; ?>><?php _e('Go to a page...','wp-cred'); ?></option>
	    </optgroup>
	</select>
	<span class='cred_form_action_page_container'>
	<select id="cred_form_success_action_page" name="cred_form_success_action_page">
	    <optgroup label="<?php _e('Please Selec t&hellip;','wp-cred'); ?>">
	    <?php echo $form_action_pages; ?>
	    </optgroup>
	</select>
	</span>
	<span class='cred_form_action_delay_container'>
	<?php _e('Redirect delay (in seconds)','wp-cred'); ?>
	<input type='text' size='3' id='cred_form_redirect_delay' name='cred_form_redirect_delay' value='<?php if (isset($redirect_delay)) echo esc_attr($redirect_delay); else echo '0';?>' />
	</span>
	<div class='cred_form_action_message_container'>
	<p class='cred-explain-text'><?php _e('Enter the message to display instead of the form. You can use HTML and shortcodes.','wp-cred'); ?></p>
	<textarea id='cred_form_action_message' name='cred_form_action_message' rows=10>
	<?php if (isset($message)) echo esc_textarea($message); else echo '';?>
	</textarea>
	</div>
</fieldset>

<fieldset class="cred-fieldset">
	<h4><?php _e('Style Settings','wp-cred'); ?></h4>
	<p class='cred-explain-text'><?php _e('CRED can use a preselected theme per form.','wp-cred'); ?></p>
	<p class="cred-label-holder"><?php _e('Select the CSS Theme for this form:','wp-cred'); ?></p>
	<ul>
	<?php foreach ($cred_themes as $theme_code=>$theme_name) { ?>
	    <li>
	    	<label class='cred-label'>
	    	    <input type='radio' class='cred-radio-10' name='cred_theme_css' value='<?php echo $theme_code; ?>' <?php if ((!isset($use_custom_css) || !$use_custom_css) && $cred_theme_css==$theme_code) echo 'checked="checked"'; ?> />
	    	    <span><?php echo $theme_name; ?></span>
	    	</label>
	    </li>
	<?php } ?>
	</ul>
	<p class='cred-explain-text'><?php _e('CRED can hide the comments section in content that include forms.','wp-cred'); ?></p>
	<label class='cred-label'>
		<input type='checkbox' class='cred-checkbox-10' name='cred_form_hide_comments' id='cred_form_hide_comments' value='1' <?php if ($hide_comments) echo 'checked="checked"'; ?> />
		<span><?php _e('Hide comments when displaying this form','wp-cred'); ?></span>
	</label>
	<a class='cred-help-link' href='<?php echo $help['general_form_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['general_form_settings']['text']; ?>"><?php echo $help['general_form_settings']['text']; ?></a>
</fieldset>
