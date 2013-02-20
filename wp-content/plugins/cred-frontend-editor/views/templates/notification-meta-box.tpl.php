<?php if (!defined('ABSPATH'))  die('Security check'); ?>
        <!-- include our template here-->
        <script type="text/html-template" id="cred_notification_template">
            <div class='cred_notification_settings_panel'>
				<p class='cred-label-holder'>
					<?php _e('Select where to send e-mails:','wp-cred'); ?>
				</p>
				<ul>
					<li>
						<label class='cred-label'>
							<input type='radio' class='cred-radio-10' name='cred_mail_to_where_selector[]' value='wp_user' />
							<span><?php _e('Send notification to a WordPress user:','wp-cred'); ?></span>
						</label>
						<span class='cred_mail_to_container'>
							<select class="cred_mail_to_user" name='cred_mail_to_user[]'>
								<optgroup label="<?php _e('-- Choose user --','wp-cred'); ?>">
									<option value='' disabled selected style='display:none;'><?php _e('-- Choose user --','wp-cred'); ?></option>
							<?php
								foreach ($users as $user ) {
									//echo '<option value="'.$user->user_email.'">'. $user->display_name. '</option>';
									echo '<option value="'.$user->user_email.'">'. $user->user_email. '</option>';
								}
							?>
								</optgroup>
							</select>
						</span>
					</li>
					<li>
						<label class='cred-label'>
							<input type='radio' class='cred-radio-10' name='cred_mail_to_where_selector[]' value='mail_field' />
							<span><?php _e('Send notification to an email specified in a form field:','wp-cred'); ?></span>
						</label>
						<span class='cred_mail_to_container'>
							<select class="cred_mail_to_field" name='cred_mail_to_field[]'>
								<optgroup label="<?php _e('-- Choose email field --','wp-cred'); ?>">
									<option value='' disabled selected style='display:none;'><?php _e('-- Choose email field --','wp-cred'); ?></option>
								</optgroup>
							</select>
							<a href='javascript:void(0)' class='cred-refresh-button' title="<?php _e('Click to refresh (if settings changed)','wp-cred'); ?>"></a>
						</span>
					</li>
					<li>
						<label class='cred-label'><input type='radio' class='cred-radio-10' name='cred_mail_to_where_selector[]' value='specific_mail' />
							<span><?php _e('Send notification to a specific email address:','wp-cred'); ?></span>
						</label>
						<span class='cred_mail_to_container'>
							<input type="text" size="50" style='position:relative;' class="cred_mail_to_specific" name='cred_mail_to_specific[]' value="" />
						</span>
					</li>
				</ul>
                <p class='cred-label-holder'>
					<label for="cred_notification_subject_codes"><?php _e('Email subject:','wp-cred'); ?></label>
				</p>
                <select class="cred_notification_subject_codes" id="cred_notification_subject_codes">
                    <optgroup label="<?php _e('-- Choose code --','wp-cred'); ?>">
                        <option value='' disabled selected style='display:none;'><?php _e('-- Choose code --','wp-cred'); ?></option>

                        <option value='%%POST_ID%%'><?php _e('Post ID','wp-cred'); ?></option>
                        <option value='%%POST_TITLE%%'><?php _e('Post Title','wp-cred'); ?></option>
                        <option value='%%POST_PARENT_TITLE%%'><?php _e('Parent Title','wp-cred'); ?></option>
                        <option value='%%USER_LOGIN_NAME%%'><?php _e('User Login Name','wp-cred'); ?></option>
                        <option value='%%USER_DISPLAY_NAME%%'><?php _e('User Display Name','wp-cred'); ?></option>
                        <option value='%%FORM_NAME%%'><?php _e('Form Name','wp-cred'); ?></option>
                        <option value='%%DATE_TIME%%'><?php _e('Date/Time','wp-cred'); ?></option>
                    </optgroup>
                </select>
                <p class="cred-label-holder">
					<label for="cred-mail-subject"><?php _e('Notification mail subject:','wp-cred'); ?></label>
				</p>
                <input type="text" id="cred-mail-subject" style='position:relative;' name='cred_mail_subject[]' class="cred_mail_subject" value="" />
                <p class='cred-label-holder'>
					<label for="cred_notification_body_codes"><?php _e('Select body of emails (shortcodes allowed):','wp-cred'); ?></label>
				</p>
                <select class="cred_notification_body_codes" id="cred_notification_body_codes">
                    <optgroup label="<?php _e('-- Choose code --','wp-cred'); ?>">
                        <option value='' disabled selected style='display:none;'><?php _e('-- Choose code --','wp-cred'); ?></option>

                        <option value='%%POST_ID%%'><?php _e('Post ID','wp-cred'); ?></option>
                        <option value='%%POST_TITLE%%'><?php _e('Post Title','wp-cred'); ?></option>
                        <option value='%%POST_LINK%%'><?php _e('Post Link','wp-cred'); ?></option>
                        <option value='%%POST_PARENT_TITLE%%'><?php _e('Parent Title','wp-cred'); ?></option>
                        <option value='%%POST_PARENT_LINK%%'><?php _e('Parent Link','wp-cred'); ?></option>
                        <option value='%%POST_ADMIN_LINK%%'><?php _e('Post Admin Link','wp-cred'); ?></option>
                        <option value='%%USER_LOGIN_NAME%%'><?php _e('User Login Name','wp-cred'); ?></option>
                        <option value='%%USER_DISPLAY_NAME%%'><?php _e('User Display Name','wp-cred'); ?></option>
                        <option value='%%FORM_NAME%%'><?php _e('Form Name','wp-cred'); ?></option>
                        <option value='%%FORM_DATA%%'><?php _e('Form Data','wp-cred'); ?></option>
                        <option value='%%DATE_TIME%%'><?php _e('Date/Time','wp-cred'); ?></option>
                    </optgroup>
                </select>
                <p class="cred-label-holder">
					<label for="cred_mail_body"><?php _e('Notification mail body:','wp-cred'); ?></label>
				</p>
                <textarea rows='8' style="position:relative;width:100%;overflow-y:auto;" name='cred_mail_body[]' id="cred_mail_body" class="cred_mail_body"></textarea>

                <div class='cred-notification-remove-container'>
                    <a href='javascript:void(0)' class='cred-notification-remove-button button'><?php _e('Remove notification','wp-cred'); ?></a>
                </div>
            </div>
        </script>
        <!-- template end -->

        <p class='cred-explain-text'>
			<?php _e('Add notifications to automatically send email after submitting this form.','wp-cred'); ?>
		</p>
        <p>
			<label class='cred-label'>
				<input type='checkbox' class='cred_cred_input cred_cred_checkbox cred-checkbox-10' name='cred_notification_enable' id='cred_notification_enable' value='1' <?php echo esc_attr($enable); ?>/>
				<span><?php _e('Enable sending notifications for this form','wp-cred'); ?></span>
			</label>
		</p>
        <div class='cred-notification-add-container'>
            <a href='javascript:void(0)' id='cred-notification-add-button' class='cred-notification-add-button button'><?php _e('Add notification','wp-cred'); ?></a>
        </div>
        <a class='cred-help-link' style='position:absolute;top:5px;right:10px' href='<?php echo $help['notification_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['notification_settings']['text']; ?>"><?php echo $help['notification_settings']['text']; ?></a>
        <div id='cred_notification_settings_panel_container' class='cred_notification_settings_panel_container-<?php echo count($notifications); ?>'>
            <?php foreach ($notifications as $ii=>$notification) {

                   $mail_to_type=isset($notification['mail_to_type'])?esc_attr($notification['mail_to_type']):'';
                   $mail_to_user=isset($notification['mail_to_user'])?esc_attr($notification['mail_to_user']):'';
                   $mail_to_field=isset($notification['mail_to_field'])?esc_attr($notification['mail_to_field']):'';
                   $mail_to_specific=isset($notification['mail_to_specific'])?esc_attr($notification['mail_to_specific']):'';
                   $subject=isset($notification['subject'])?esc_attr($notification['subject']):'';
                   $body=isset($notification['body'])?/*esc_textarea*/($notification['body']):'';
            ?>
            <div class='cred_notification_settings_panel'>
                <p class='cred-label-holder'>
					<?php _e('Select where to send emails:','wp-cred'); ?>
				</p>
                <div>
                    <label class='cred-label'>
						<input type='radio' class='cred-radio-10' name='cred_mail_to_where_selector[<?php echo $ii; ?>]' value='wp_user' <?php if ($mail_to_type=='wp_user') echo 'checked="checked"'; ?> />
						<span><?php _e('Send notification to a WordPress user:','wp-cred'); ?></span>
					</label>
                    <span class='cred_mail_to_container'>
                        <select class="cred_mail_to_user" name="cred_mail_to_user[<?php echo $ii; ?>]">
                            <optgroup label="<?php _e('-- Choose user --','wp-cred'); ?>">
                                <option value='' disabled selected style='display:none;'><?php _e('-- Choose user --','wp-cred'); ?></option>
                        <?php
                            foreach ($users as $user ) {
                                //echo '<option value="'.$user->user_email.'">'. $user->display_name. '</option>';
                                if ($mail_to_user==$user->user_email)
                                    echo '<option value="'.$user->user_email.'" selected="selected">'. $user->user_email. '</option>';
                                else
                                    echo '<option value="'.$user->user_email.'">'. $user->user_email. '</option>';
                            }
                        ?>
                            </optgroup>
                        </select>
                    </span>
                </div>
                <div>
                    <label class='cred-label'>
						<input type='radio' class='cred-radio-10' name='cred_mail_to_where_selector[<?php echo $ii; ?>]' value='mail_field' <?php if ($mail_to_type=='mail_field') echo 'checked="checked"'; ?>/>
                    <span><?php _e('Send notification to an email specified in a form field:','wp-cred'); ?></span>
					</label>
                    <span class='cred_mail_to_container'>
                        <select class="cred_mail_to_field" name="cred_mail_to_field[<?php echo $ii; ?>]">
                            <optgroup label="<?php _e('-- Choose email field --','wp-cred'); ?>">
                                <option value='' disabled selected style='display:none;'><?php _e('-- Choose email field --','wp-cred'); ?></option>
                            </optgroup>
                        </select>
                        <a href='javascript:void(0)' class='cred-refresh-button' title="<?php _e('Click to refresh (if settings changed)','wp-cred'); ?>"><span class='cred-current-field-value' style='display:none;text-indent:-9999px;'><?php echo $mail_to_field; ?></span></a>
                    </span>
                </div>
                <div>
                    <label class='cred-label'><input type='radio' class='cred-radio-10' name='cred_mail_to_where_selector[<?php echo $ii; ?>]' value='specific_mail' <?php if ($mail_to_type=='specific_mail') echo 'checked="checked"'; ?>/>
                    <span><?php _e('Send notification to a specific email address:','wp-cred'); ?></span>
					</label>
                    <span class='cred_mail_to_container'>
                        <input type="text" size="50" style='position:relative;' class="cred_mail_to_specific" name='cred_mail_to_specific[<?php echo $ii; ?>]' value="<?php echo $mail_to_specific; ?>" />
                    </span>
                </div>
                <p class='cred-explain-text'><?php _e('Select subject of emails:','wp-cred'); ?></p>
                <select class="cred_notification_subject_codes">
                    <optgroup label="<?php _e('-- Choose code --','wp-cred'); ?>">
                        <option value='' disabled selected style='display:none;'><?php _e('-- Choose code --','wp-cred'); ?></option>

                        <option value='%%POST_ID%%'><?php _e('Post ID','wp-cred'); ?></option>
                        <option value='%%POST_TITLE%%'><?php _e('Post Title','wp-cred'); ?></option>
                        <option value='%%POST_PARENT_TITLE%%'><?php _e('Parent Title','wp-cred'); ?></option>
                        <option value='%%USER_LOGIN_NAME%%'><?php _e('User Login Name','wp-cred'); ?></option>
                        <option value='%%USER_DISPLAY_NAME%%'><?php _e('User Display Name','wp-cred'); ?></option>
                        <option value='%%FORM_NAME%%'><?php _e('Form Name','wp-cred'); ?></option>
                        <option value='%%DATE_TIME%%'><?php _e('Date/Time','wp-cred'); ?></option>
                    </optgroup>
                </select>

				<p class="cred-label-holder">
					<?php _e('Notification mail subject:','wp-cred'); ?>
				</p>
                <input type="text" size="200" style='position:relative;' name="cred_mail_subject[<?php echo $ii; ?>]" class="cred_mail_subject" value="<?php echo $subject; ?>" />
                <p class='cred-label-holder'>
					<?php _e('Select body of emails (shortcodes allowed):','wp-cred'); ?>
				</p>
                <select class="cred_notification_body_codes">
                    <optgroup label="<?php _e('-- Choose code --','wp-cred'); ?>">
                        <option value='' disabled selected style='display:none;'><?php _e('-- Choose code --','wp-cred'); ?></option>

                        <option value='%%POST_ID%%'><?php _e('Post ID','wp-cred'); ?></option>
                        <option value='%%POST_TITLE%%'><?php _e('Post Title','wp-cred'); ?></option>
                        <option value='%%POST_LINK%%'><?php _e('Post Link','wp-cred'); ?></option>
                        <option value='%%POST_PARENT_TITLE%%'><?php _e('Parent Title','wp-cred'); ?></option>
                        <option value='%%POST_PARENT_LINK%%'><?php _e('Parent Link','wp-cred'); ?></option>
                        <option value='%%POST_ADMIN_LINK%%'><?php _e('Post Admin Link','wp-cred'); ?></option>
                        <option value='%%USER_LOGIN_NAME%%'><?php _e('User Login Name','wp-cred'); ?></option>
                        <option value='%%USER_DISPLAY_NAME%%'><?php _e('User Display Name','wp-cred'); ?></option>
                        <option value='%%FORM_NAME%%'><?php _e('Form Name','wp-cred'); ?></option>
                        <option value='%%FORM_DATA%%'><?php _e('Form Data','wp-cred'); ?></option>
                        <option value='%%DATE_TIME%%'><?php _e('Date/Time','wp-cred'); ?></option>
                    </optgroup>
                </select>
                <p class="cred-label-holder">
					<label for="cred_mail_body"><?php _e('Notification mail body:','wp-cred'); ?></label>
				</p>
                <textarea rows='8' style="position:relative;width:100%;overflow-y:auto;" name="cred_mail_body[<?php echo $ii; ?>]" id="cred_mail_body" class="cred_mail_body"><?php echo $body; ?></textarea>

                <div class='cred-notification-remove-container'>
                    <a href='javascript:void(0)' class='cred-notification-remove-button button'><?php _e('Remove notification','wp-cred'); ?></a>
                </div>
            </div>
            <?php } ?>
        </div>
