<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<span id="cred-scaffold-button" class="cred-media-button">
    <a href='javascript:void(0)' id="cred-scaffold-button-button" class='cred-button' title='<?php _e('Auto-Generate Form','wp-cred'); ?>'><?php _e('Auto-Generate Form','wp-cred'); ?></a>
    <div id="cred-scaffold-box" class="cred-popup-box">
        <div class='cred-popup-heading'>
			<h3><?php _e('Scaffold Form Content','wp-cred'); ?></h3>
			<a href='javascript:void(0);' title='<?php _e('Close','wp-cred'); ?>' class='cred-close-button cred-cred-cancel-close'></a>
        </div>
        <div id="cred-scaffold-box-inner" class="cred-popup-inner">
        <textarea id="cred-scaffold-area" rows=8>
        </textarea>
        <p>
			<?php _e("This scaffold includes inputs for all the fields that belong to the form's content type.",'wp-cred'); ?>
			<?php _e("After inserting it, you can style and edit the form content using the editor.",'wp-cred'); ?>
        </p>
        <p>
			<strong><?php _e("Tip:",'wp-cred'); ?></strong>
			<?php _e("Make a selection in the editor and scaffold will replace it.",'wp-cred'); ?>
        </p>
        <ul>
        	<li>
        		<label class='cred-label'>
					<input type='checkbox' class='cred-checkbox-10' name='cred_include_captcha_scaffold' id='cred_include_captcha_scaffold' value='1' <?php if (isset($include_captcha_scaffold) && $include_captcha_scaffold) echo 'checked="checked"'; ?> />
					<span><?php _e('Include reCaptcha field','wp-cred'); ?></span>
				</label>
        	</li>
			<li>
				<label class='cred-label'>
					<input type='checkbox' class='cred-checkbox-10' name='cred_include_wpml_scaffold' id='cred_include_wpml_scaffold' value='1' <?php if (isset($include_wpml_scaffold) && $include_wpml_scaffold) echo 'checked="checked"'; ?> /><span class='cred-checkbox-replace'></span>
					<span><?php _e('Include WPML localization','wp-cred'); ?></span>
				</label>
			</li>
        </ul>
		<p class="cred-scaffold-buttons-holder cred-buttons-holder">
			<a id="cred-popup-cancel" class="button cred-cred-cancel-close" href="javascript:void(0);" title="<?php _e('Cancel','wp-cred'); ?>"><?php _e('Cancel','wp-cred'); ?></a>
			<a id="cred-scaffold-insert" class="button-primary" href="javascript:void(0);" title="<?php _e('Insert','wp-cred'); ?>"><?php _e('Insert','wp-cred'); ?></a>
		</p>
        </div>
        <a class='cred-help-link' href='<?php echo $help['scaffold_settings']['link']; ?>' target='<?php echo $help_target; ?>'  title="<?php echo $help['scaffold_settings']['text']; ?>"><?php echo $help['scaffold_settings']['text']; ?></a>




    </div>
</span>
<span style='display:inline-block' class='cred_ajax_loader_small'></span>

