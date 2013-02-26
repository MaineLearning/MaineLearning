<?php if (!defined('ABSPATH'))  die('Security check'); ?>

<!-- Inline Templates -->
<script type='text/template-html' id='cred_image_dimensions_validation_template'>
	<div id="cred_image_dimensions_validation" class="additional_field_options_popup">
		<h3><?php _e('You can set extra width and height validation for this field (leave blank to disable)','wp-cred'); ?></h3>
		<div class="additional_field_options_popup_inner">
			<p>
				<label for="cred_max_width">
					<span><?php _e('Max. Width (px)','wp-cred'); ?></span>
					<input type='text' size='3' id='cred_max_width' value='' />
				</label>
			</p>
			<p>
				<label for="cred_max_height">
					<span><?php _e('Max. Height (px)','wp-cred'); ?></span>
					<input type='text' size='3' id='cred_max_height' value='' />
				</label>
			</p>
		</div>

		<p class="cred-buttons-holder">
			<input type='button' id='cred_image_dimensions_cancel_button' class='cred_extra_popup_button button' value='<?php _e('Cancel','wp-cred'); ?>' />
			<input type='button' id='cred_image_dimensions_validation_button' class='cred_extra_popup_button button button-primary' value='<?php _e('OK','wp-cred'); ?>' />
		</p>
	</div>
</script>

<script type='text/template-html' id='cred_text_extra_options_template'>
	<div id="cred_text_extra_options" class="additional_field_options_popup">
		<h3><?php _e('You can set extra options for this field','wp-cred'); ?> </h3>
		<div class="additional_field_options_popup_inner">
			<p>
				<label class="cred-label" for="cred_text_extra_readonly">
					<span><?php _e('Readonly?','wp-cred'); ?></span>
					<input type='checkbox' id='cred_text_extra_readonly' class='cred-checkbox-10' value='1' />
				</label>
			</p>
			<p>
				<label for="cred_text_extra_escape">
					<span><?php _e('HTML Escape?','wp-cred'); ?></span>
					<input type='checkbox' id='cred_text_extra_escape' class='cred-checkbox-10' value='1' />
				</label>
			</p>
			<p>
				<label for="cred_text_extra_placeholder">
					<span><?php _e('Placeholder','wp-cred'); ?></span>
					<input type='text' size='20' id='cred_text_extra_placeholder' value='' />
				</label>
			</p>
		</div>
		<p class="cred-buttons-holder">
			<input type='button' id='cred_text_extra_options_cancel_button' class='cred_extra_popup_button button' value='<?php _e( 'Cancel', 'wp-cred' ); ?>' />
			<input type='button' id='cred_text_extra_options_button' class='cred_extra_popup_button button button-primary' value='<?php _e( 'OK', 'wp-cred' ); ?>' />
		</p>
	</div>
</script>

<script type='text/template-html' id='cred_parent_field_settings_template'>
        <div id="cred_parent_field_settings" class="additional_field_options_popup">
            <h3><?php _e('You can set extra options for this field','wp-cred'); ?></h3>

			<div class="additional_field_options_popup_inner">
				<p>
					<label for="cred_parent_required">
						<span><?php _e('Required?','wp-cred'); ?></span>
						<input type='checkbox' id='cred_parent_required' class='cred-checkbox-10' value='1' />
					</label>
				</p>

				<div id='cred_parent_select_text_container'>
					<p>
						<label for="cred_parent_select_text">
							<span><?php _e('Select text','wp-cred'); ?></span>
							<input type='text' size='20' id='cred_parent_select_text' value='' />
						</label>
					</p>

					<p>
						<label for="cred_parent_validation_text">
							<span><?php _e('Validation text','wp-cred'); ?></span>
							<input type='text' size='20' id='cred_parent_validation_text' value='' />
						</label>
					</p>
				</div>

				<div id='cred_parent_no_parent_container'>
					<p>
						<label for="cred_parent_no_parent_text">
							<span><?php _e('No Parent text','wp-cred'); ?></span>
							<input type='text' size='20' id='cred_parent_no_parent_text' value='' />
						</label>
					</p>
				</div>

				<p>
					<label for="cred_parent_order_by"><?php _e('Order By','wp-cred'); ?></label>
					<select id='cred_parent_order_by'>
						<option value='date'><?php _e('Date','wp-cred'); ?></option>
						<option value='title'><?php _e('Title','wp-cred'); ?></option>
					</select>
				</p>

				<p>
					<label for="cred_parent_ordering"><?php _e('Ordering','wp-cred'); ?></label>
					<select id='cred_parent_ordering'>
						<option value='desc'><?php _e('Descending','wp-cred'); ?></option>
						<option value='asc'><?php _e('Ascending','wp-cred'); ?></option>
					</select>
				</p>

				<p>
					<label for="cred_parent_max_results">
						<span><?php _e('Number of entries (0=all)','wp-cred'); ?></span>
						<input type='text' size='3' id='cred_parent_max_results' value='0' />
					</label>
				</p>

				<p class="cred-buttons-holder">
					<input type='button' id='cred_parent_extra_cancel_button' class='cred_extra_popup_button button' value='<?php _e('Cancel','wp-cred'); ?>' />
					<input type='button' id='cred_parent_extra_button' class='cred_extra_popup_button button button-primary' value='<?php _e('OK','wp-cred'); ?>' />
				</p>
			</div>

        </div>
</script>
<!-- Inline Templates End -->

<span id="cred-shortcode-button" class="cred-media-button">
    <a href='javascript:void(0)' id="cred-shortcode-button-button" class='cred-button' title='<?php _e('Insert Post Fields','wp-cred'); ?>'><?php _e('Insert Post Fields','wp-cred'); ?></a>
    <div id="cred-shortcodes-box" class="cred-popup-box">
        <div class='cred-popup-heading'>
        <h3><?php _e('Post Fields (click to insert)','wp-cred'); ?></h3>
        <a href='javascript:void(0);' title='<?php _e('Close','wp-cred'); ?>' class='cred-close-button cred-cred-cancel-close'></a>
        </div>
        <div id="cred-shortcodes-box-inner" class="cred-popup-inner">
        </div>
        <a class='cred-help-link' href='<?php echo $help['fields_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['fields_settings']['text']; ?>"><?php echo $help['fields_settings']['text']; ?></a>
    </div>
</span>
