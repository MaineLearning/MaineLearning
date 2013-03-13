<?php

/**
 * Media popup functions and filters.
 */

if ( isset( $_GET['wpv-media-insert'] ) || isset( $_GET['wpv-media-edit'] ) ) { // Button "Add Media"
	// Remove unwanted tabs
	add_filter('media_upload_tabs', 'wpv_media_upload_tabs_filter');
	// Remove "Insert into post" button and add button-secondary class to Delete link
	add_action( 'admin_head-media-upload-popup', 'wpv_media_popup_changes' );
}

function wpv_media_popup_changes() { ?>
<script type="text/javascript">
        jQuery(document).ready(function(){
		var idview = window.parent.jQuery('#post_ID').val();
		jQuery('.savesend input').hide();
		jQuery('tr.align').hide();
		jQuery('tr.image-size').hide();
		jQuery('tr.url').hide();
		jQuery('.savesend a').addClass('button-secondary').css('color', '#333333');
		jQuery('.media-upload-form').append('<p><a class="wpv-save-media button button-primary">Save all changes</a></p>');
		jQuery('.savesend input.save').click(function(){
			wpv_view_media_manager(idview);
		});
		jQuery('.wpv-save-media').click(function(){
			wpv_view_media_manager(idview);
		});
        });
        function wpv_view_media_manager(idview) {
		window.parent.jQuery('.media-list').hide();
		var data = '&action=wpv_view_media_manager';
		data += '&_wpnonce=<?php echo wp_create_nonce('wpv_media_manager_callback'); ?>';
                data += '&view_id=' + idview;
		jQuery.post(ajaxurl, data, function(response) {
			if (response != '') {
				window.parent.jQuery('.wpv_table_attachments').html(response);
				window.parent.jQuery('.media-list').show();
			}
			self.parent.tb_remove();
		});
        
	}
</script>
<style type="text/css">
<?php if ( isset( $_GET['wpv-media-insert'] ) ) { // Button "Edit media items"
	?> 
	tr.submit { display: none; }
<?php } ?>
	#gallery-settings, .ml-submit {display:none !important;}
	</style>
<?php
}

function wpv_media_upload_tabs_filter($tabs) {
	unset($tabs['type_url']);
	unset( $tabs['library'] );
	if ( isset( $_GET['wpv-media-edit'] ) ) { // Button "Edit media items"
		unset( $tabs['type'] );
	}
	return $tabs;
}

/*
  
    Add controls to the admin page for specifying the filter_meta_html
    
*/

function wpv_filter_meta_html_admin($view_settings) {
    global $WP_Views, $wpv_wp_pointer;
    
    $defaults = array('filter_meta_html' => '',
                      'generated_filter_meta_html' => '');
    $view_settings = wp_parse_args($view_settings, $defaults);
    
    ?>
        <div id="wpv_filter_meta_html_admin">
	    <input type="hidden" name="_wpv_settings[filter_meta_html_state][html]" id="wpv_filter_meta_html_state" value="<?php echo isset($view_settings['filter_meta_html_state']['html']) ? $view_settings['filter_meta_html_state']['html'] : 'off'; ?>" />
            <div id="wpv_filter_meta_html_admin_show">
                <p><i><?php echo __('The pagination and filter control settings generate meta HTML. This meta HTML includes shortcodes and HTML, which you can edit, to fully customize the appearance of this View\'s filter section.', 'wpv-views'); ?></i></p>
                <input type="button" class="button-secondary" onclick="wpv_view_filter_meta_html()" value="<?php _e('View/Edit Meta HTML', 'wpv-views'); ?>" />
            </div>
            <div id="wpv_filter_meta_html_admin_edit" style="background:<?php echo WPV_EDIT_BACKGROUND;?>;display:none">
                <div style="margin:10px 10px 10px 10px;">
                    <p><?php _e('<strong>Meta HTML</strong> - This is used to add front end controls to a View. It gets generated from the View Query settings and can be modified to suit.', 'wpv-views'); ?></p>
                    <div id="wpv_filter_meta_html_content_error" class="wpv_form_errors" style="display:none;">
                        <p><?php _e("Changes can't be applied. It appears that you made manual modifications to the Meta HTML.", 'wpv-views'); ?></p>
                        <a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_filter_meta_html_generate_new()"><strong><?php echo __('Generate the new filter content', 'wpv-views'); ?></strong></a> <?php _e('(your edits will be displayed and you can apply them again)', 'wpv-views'); ?>
                    </div>
                    <div id="wpv_filter_control_meta_html_content_error" class="wpv_form_errors_dialog" style="display:none;">
                        <p><img src="<?php echo WPV_URL . '/res/img/alert.png'; ?>" /> <?php _e("It looks like you manually edited the meta-HTML for the filter. Please choose how to update:", 'wpv-views'); ?></p>
                        <ul style="margin-left:20px">
                            <li><label><input type="radio" name="wpv_filter_control_update" value="update" checked="checked" /><?php echo __('Keep my edits and update just the input elements (recommended)', 'wpv-views'); ?></label></li>
                            <li><label><input type="radio" name="wpv_filter_control_update" value="manual" /><?php echo __('I will apply the changes to the meta HTML manually', 'wpv-views'); ?></label></li>
                            <li><label><input type="radio" name="wpv_filter_control_update" value="generate" /><?php echo __('Overwrite all my edits and generate the filter from scratch', 'wpv-views'); ?></label></li>
                        </ul>
                        
                        <input type="button" class="button-primary" value="<?php echo __('Apply', 'wpv-views'); ?>" onclick="wpv_filter_control_apply_changes()" />
                    </div>

                    <?php echo $WP_Views->editor_addon->add_form_button('', 'wpv_filter_meta_html_content', true, true); ?>

                    <span style="position: relative; top:-5px;">
                        <?php echo apply_filters('wpv_meta_html_add_form_button', '', '#wpv_filter_meta_html_content'); ?>
                    </span>
                    
                    <div id="wpv-add-filter-controls-popup" style="display:none">
                        <div id="wpv-add-filter-controls-content">
                            
                        </div>
                    </div>
                    
                    <textarea name="_wpv_settings[filter_meta_html]" id="wpv_filter_meta_html_content" cols="40" rows="10" style="width:100%;margin-top:10px"><?php echo $view_settings['filter_meta_html']; ?></textarea>
                    <div id="wpv_filter_meta_html_content_old_div" style="display:none">
                        <div class="wpv_form_notice"><?php _e('<strong>Your edits are shown below:</strong>', 'wpv-views'); ?> <a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_filter_meta_html_old_dismiss()"><strong><?php echo __('dismiss', 'wpv-views'); ?></strong></a></div>
                        <textarea id="wpv_filter_meta_html_content_old" cols="40" rows="10" style="width:100%;margin-top:10px"></textarea>
                    </div>
                    <textarea name="_wpv_settings[generated_filter_meta_html]" id="wpv_generated_filter_meta_html_content" cols="40" rows="10" style="display:none"><?php echo $view_settings['generated_filter_meta_html']; ?></textarea>
                    <div id="wpv_filter_meta_html_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" onclick="wpv_view_filter_meta_html_close()"><strong><?php _e('Close', 'wpv-views'); ?></strong></a></p>
                </div>
            </div>
            <div id="wpv_filter_meta_html_extra_css" style="margin-top:15px;">
		<input type="hidden" name="_wpv_settings[filter_meta_html_state][css]" id="wpv_filter_meta_html_extra_css_state" value="<?php echo isset($view_settings['filter_meta_html_state']['css']) ? $view_settings['filter_meta_html_state']['css'] : 'off'; ?>" />
		<input type="button" class="button-secondary wpv_filter_meta_html_extra_css_edit" onclick="wpv_view_filter_meta_html_extra(this)" value="<?php _e('Edit CSS', 'wpv-views'); ?>" />
		<div id ="wpv_filter_meta_html_extra_css_edit" style="background:<?php echo WPV_EDIT_BACKGROUND; ?>">
		    <p><?php _e('<strong>CSS</strong> - This is used to add custom CSS to a View query.', 'wpv-views'); ?></p>
		    <textarea name="_wpv_settings[filter_meta_html_css]" id="wpv_filter_meta_html_css" cols="97" rows="10"><?php echo isset($view_settings['filter_meta_html_css']) ? $view_settings['filter_meta_html_css'] : ''; ?></textarea>
		    <div id="wpv_filter_meta_html_extra_css_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
		    <p><a style="cursor:pointer;margin-bottom:10px;" id="wpv_filter_meta_html_extra_css_close" onclick="wpv_view_filter_meta_html_extra_css_close()"><strong><?php _e('Close CSS editor', 'wpv-views'); ?></strong></a></p>
		</div>
            </div>
            <div id="wpv_filter_meta_html_extra_js" style="margin-top:15px;">
		<input type="hidden" name="_wpv_settings[filter_meta_html_state][js]" id="wpv_filter_meta_html_extra_js_state" value="<?php echo isset($view_settings['filter_meta_html_state']['js']) ? $view_settings['filter_meta_html_state']['js'] : 'off'; ?>" />
		<input type="button" class="button-secondary wpv_filter_meta_html_extra_js_edit" onclick="wpv_view_filter_meta_html_extra(this)" value="<?php _e('Edit JS', 'wpv-views'); ?>" />
		<div id ="wpv_filter_meta_html_extra_js_edit" style="background:<?php echo WPV_EDIT_BACKGROUND; ?>">
		    <p><?php _e('<strong>JS</strong> - This is used to add custom javascript to a View query.', 'wpv-views'); ?></p>
		    <textarea name="_wpv_settings[filter_meta_html_js]" id="wpv_filter_meta_html_js" cols="97" rows="10"><?php echo isset($view_settings['filter_meta_html_js']) ? $view_settings['filter_meta_html_js'] : ''; ?></textarea>
                    <div id="wpv_filter_meta_html_extra_js_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" id="wpv_filter_meta_html_extra_js_close" onclick="wpv_view_filter_meta_html_extra_js_close()"><strong><?php _e('Close JS editor', 'wpv-views'); ?></strong></a></p>
		</div>
            </div>
            <div id="wpv_filter_meta_html_extra_img" style="margin-top:15px;">
            <?php global $post; ?>
		<input type="hidden" name="_wpv_settings[filter_meta_html_state][img]" id="wpv_filter_meta_html_extra_img_state" value="<?php echo isset($view_settings['filter_meta_html_state']['img']) ? $view_settings['filter_meta_html_state']['img'] : 'off'; ?>" />
		<input type="button" class="button-secondary wpv_filter_meta_html_extra_img_edit" onclick="wpv_view_filter_meta_html_extra(this)" value="<?php _e('Manage Media', 'wpv-views'); ?>" />
		<div id ="wpv_filter_meta_html_extra_img_edit" style="background:<?php echo WPV_EDIT_BACKGROUND; ?>">
		    <p><?php _e('<strong>Media</strong> - This is used to add images to a View output.', 'wpv-views'); ?></p>
		    <input type="button" class="button-secondary wpv_filter_meta_html_extra_img_upload" onclick="tb_show('<?php _e('Upload images'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&wpv-media-insert=1&TB_iframe=true');return false;" value="<?php _e('Add Media', 'wpv-views'); ?>" />
		    <?php 
			$args = array(
				'post_type' => 'attachment',
				'numberposts' => null,
				'post_status' => null,
				'post_parent' => $post->ID
			); 
			$attachments = get_posts($args);
			if ($attachments) { ?>
			<div class="media-list">
				<p>Copy the links of the media items and paste into the meta HTML and CSS editors. You can use full URLs. When exporting and importing this View to another site, all URLs will be adjusted.</p>
				<table class="wpv_table_attachments widefat">
				<thead>
				<tr>
				<th><?php _e('Thumbnail', 'wpv-views'); ?></th>
				<th><?php _e('URL', 'wpv-views'); ?></th>
				</tr>
				</thead>
				<?php
					foreach ($attachments as $attachment) {
						$type = get_post_mime_type($attachment->ID);
						$icon = wp_mime_type_icon($type);
						if ( $type == 'image/gif' || $type == 'image/jpeg' || $type == 'image/png' ) {
							$thumb = '<img src="' .  $attachment->guid . '" alt="' . $attachment->post_title . '" width="60" height="60" />';
						} else {
							$thumb = '<img src="' . $icon . '" />';
						}
						?>
						<tr>
						<td><?php echo $thumb; ?></td>
						<td><a href="<?php echo $attachment->guid;?>"><?php echo $attachment->guid;?></a></td>
						</tr>
					<?php } ?>
				</table>
				<p><input type="button" class="button-secondary wpv_filter_meta_html_extra_img_edit_existing" onclick="tb_show('<?php _e('Edit media items'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&tab=gallery&wpv-media-edit=1&TB_iframe=true');return false;" value="<?php _e('Edit media items', 'wpv-views'); ?>" /></p>
			</div>
			<?php } else { ?>
				<div class="media-list" style="display:none;">
				<p>Copy the links of the media items and paste into the meta HTML and CSS editors. You can use full URLs. When exporting and importing this View to another site, all URLs will be adjusted.</p>
				<table class="wpv_table_attachments widefat"></table>
				<p><input type="button" class="button-secondary wpv_filter_meta_html_extra_img_edit_existing" onclick="tb_show('<?php _e('Edit media items'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&type=image&tab=gallery&wpv-media-edit=1&TB_iframe=true');return false;" value="<?php _e('Edit media items', 'wpv-views'); ?>" /></p>
				</div>			
			<?php } ?>
                    <div id="wpv_filter_meta_html_extra_img_notice" class="wpv_form_notice" style="display:none;"><?php _e('* These updates will take effect when you save the view.', 'wpv-views'); ?></div>
                    <p><a style="cursor:pointer;margin-bottom:10px;" id="wpv_filter_meta_html_extra_img_close" onclick="wpv_view_filter_meta_html_extra_img_close()"><strong><?php _e('Close Media manager', 'wpv-views'); ?></strong></a></p>
		</div>
            </div>
        </div>

    <?php

    ob_start();
    
    ?>
    <span class="wpv-filter-control-help"><i>
        <?php echo sprintf(__('Learn more about adding %sfilter controls%s', 'wpv-views'),
           '<a href="' . WPV_ADD_FILTER_CONTROLS_LINK . '" target="_blank">',
           ' &raquo;</a>'
           ); ?>
    </i></span>

    <?php
    $help_link = ob_get_clean(); // Note: $help_link is reused further down.
    
    // Add warning pointer tips.
    // These will be then called via javascript if conditions are meet.
    
    $wpv_wp_pointer->add_pointer(__('Submit button is hidden', 'wpv-views'),
                                 __('The filter submit button is hidden.', 'wpv-views') . '<br />' . sprintf(__('Change it from %s to %s', 'wpv-views'), 'hide="true"', 'hide="false"'),
                                 '#wpv_filter_meta_html_content',
                                 'bottom',
                                 'filter_submit_hidden_warning',
                                 'wpv_filter_submit_hidden_warning');

    
    $wpv_wp_pointer->add_pointer(__('Adding front end filter controls', 'wpv-views'),
                                 __('When filtering by URL paremeters you can add <strong>front end filter controls</strong> to the View. This allows the user to control what items are displayed.', 'wpv-views') . '<br /><br />' . str_replace("\n", '\n', $help_link),
                                 '',
                                 'top',
                                 'filter_url_hint',
                                 'wpv_filter_url_hint',
                                 true);
    
    $wpv_wp_pointer->add_show_hints_ui("jQuery('#wpv_views_help .inside')");
    
    ?>
    
    <?php // Add javascript variables for adding filter controls and other help ?>
    
	<script type="text/javascript">
            
        var wpv_insert_control_html = '<p><?php echo esc_js(__('Controls should be added between the [wpv-filter-controls] and [/wpv-filter-controls] shortcodes.', 'wpv-views')); ?><br />';
        wpv_insert_control_html += '<?php echo esc_js(__('For Types fields you can use the "Types auto style" and the appropriate control type will be used for the Types field.', 'wpv-views')); ?></p>';
        
        wpv_insert_control_html += '<?php echo str_replace("\n", '\n', $help_link); ?>';
        
        wpv_insert_control_html += '<h3><?php echo esc_js(__('Custom field controls', 'wpv-views')); ?></h3>'
        wpv_insert_control_html += '<table class="widefat fixed">';
        wpv_insert_control_html += '<thead><tr><th><?php echo __('Custom field', 'wpv-views'); ?></th><th><?php echo __('URL_PARAM', 'wpv-views'); ?></th><th><?php echo __('Type of control', 'wpv-views'); ?></th><th></th></tr></thead>';
        wpv_insert_control_html += '<tbody>';

        var wpv_types_auto_style = '<?php echo esc_js(__('Types auto style', 'wpv-views')); ?>';
        var wpv_radio_style = '<?php echo esc_js(__('Radio', 'wpv-views')); ?>';
        var wpv_checkbox_style = '<?php echo esc_js(__('Checkbox', 'wpv-views')); ?>';
        var wpv_checkboxes_style = '<?php echo esc_js(__('Checkboxes', 'wpv-views')); ?>';
        var wpv_select_style = '<?php echo esc_js(__('Select', 'wpv-views')); ?>';
        var wpv_text_field_style = '<?php echo esc_js(__('Text field', 'wpv-views')); ?>';

        var wpv_add_control_text = '<?php echo esc_js(__('Add control', 'wpv-views')); ?>';
        
        var wpv_insert_taxonomy_control_html = '<h3><?php echo esc_js(__('Taxonomy controls', 'wpv-views')); ?></h3>'
        wpv_insert_taxonomy_control_html += '<table class="widefat fixed">';
        wpv_insert_taxonomy_control_html += '<thead><tr><th><?php echo __('Taxonomy', 'wpv-views'); ?></th><th><?php echo __('URL_PARAM', 'wpv-views'); ?></th><th><?php echo __('Type of control', 'wpv-views'); ?></th><th></th></tr></thead>';
        wpv_insert_taxonomy_control_html += '<tbody>';
        
		var wpv_no_custom_fields_url_param = '<br /><div class="wpv_form_notice" style="width:98%;">';
		wpv_no_custom_fields_url_param += '<?php echo sprintf(esc_js(__('There are no custom field filters using URL_PARAM to filter the results. See %sPassing Arguments to Views &raquo;%s', 'wpv-views')), '<a href="http://wp-types.com/documentation/user-guides/passing-arguments-to-views/" target="_blank">', '</a>'); ?>';
		wpv_no_custom_fields_url_param += '</div>';

		var wpv_no_taxonomy_url_param = '<br /><div class="wpv_form_notice" style="width:98%;">';
		wpv_no_taxonomy_url_param += '<?php echo sprintf(esc_js(__('There are no taxonomy filters using URL_PARAM to filter the results. See %sPassing Arguments to Views &raquo;%s', 'wpv-views')), '<a href="http://wp-types.com/documentation/user-guides/passing-arguments-to-views/" target="_blank">', '</a>'); ?>';
		wpv_no_taxonomy_url_param += '</div>';

        var wpv_taxonomy_url_not_slug = '<?php echo esc_js(__('The taxonomy filter needs to be set to "Taxonomy slug" for taxonomy type "', 'wpv-views')); ?>';
    </script>
    
    <?php

}


